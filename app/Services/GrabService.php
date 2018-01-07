<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ArticleHrefNotFoundException;
use App\Exceptions\ArticleNotFoundException;
use App\Exceptions\ArticlesNotFoundException;
use App\Exceptions\DateNotFoundException;
use App\Exceptions\DateWrongFormatException;
use App\Exceptions\DescriptionNotFoundException;
use App\Exceptions\HtmlNotFoundException;
use App\Exceptions\ImageNotFoundException;
use App\Exceptions\NotAllowedArticleId;
use App\Exceptions\TitleNotFoundException;
use App\Jobs\GrabArticle;
use App\Models\Article;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Helper\ProgressBar;

final class GrabService
{
	CONST HTTP_CODE_SUCCESS = 200;
	CONST RESOURCE_URL = 'http://www.tert.am/am/news/';
	CONST RESOURCE_IMAGE_URL = 'http://www.tert.am';
	CONST ARTICLE_COUNT = 1000;

	/**
	 * @var ProgressBar
	 */
	private $progressBar;

	/**
	 * @var ConsoleOutput
	 */
	private $output;


	public function refreshArticles(): void
	{
		$this->initOutput();
		$this->output->writeln('<info>Starting queueing new articles</info>');
		$newestArticleIdInDb = Article::getNewestArticleId();
		$this->initProgressBar($newestArticleIdInDb);

		$page = 1;
		$countOfQueuedArticles = 0;

		while (true) {
			try {
				$html = $this->getHtml(self::RESOURCE_URL . $page);
				$dOMXPath = $this->getDom($html);
				$articles = $this->getArticles($dOMXPath);
				foreach ($articles as $article) {

					$articleLink = $this->getArticleLink($article);
					$articleId = $this->getArticleId($article);
					$countOfQueuedArticles++;

					if ($this->stopRefreshing($articleId, $newestArticleIdInDb, $countOfQueuedArticles)) {
						break 2;
					}

					$this->progressBar->advance();
					$this->sendToQueue($articleId, $articleLink);
				}
			} catch (HtmlNotFoundException |  ArticlesNotFoundException | ArticleHrefNotFoundException | NotAllowedArticleId $e) {
				$this->output->writeln('<error>Skipping this article, Reason: ' . PHP_EOL .  $e->getMessage() .' </error>');
			} catch (\Throwable $e) {
				$this->output->writeln( '<error>Unexpected Error' . PHP_EOL .
					'Stopping Scrapping, Reason:' . PHP_EOL .
					$e->getMessage().' </error>');
				break;
			}
			$page++;
		};
		$this->progressBar->finish();
		$this->output->writeln(PHP_EOL . '<info>Queueing new articles has finished successfully</info>');
	}

	private function sendToQueue(int $articleId, string $articleLink)
	{
		Bus::dispatch(new GrabArticle($articleId, $articleLink));
	}

	private function initOutput(): void
	{
		$this->output = new ConsoleOutput();
		$this->output->setFormatter(new OutputFormatter(true));
	}

	private function initProgressBar(?int $newestArticleIdInDb): void
	{
		$max = 0;
		if (empty($newestArticleIdInDb)) {
			$max = self::ARTICLE_COUNT;
		}
		$this->progressBar = new ProgressBar($this->output,$max);
		$this->progressBar->setBarCharacter('<fg=green>=</>');
		$this->progressBar->setEmptyBarCharacter("<fg=red>-</>");
		$this->progressBar->setProgressCharacter("<fg=green>></>");
		$this->progressBar->start();
	}

	private function stopRefreshing(int $currentArticleId, ?int $newestArticleIdInDb, int $countOfQueuedArticles): bool
	{
		if (empty($newestArticleIdInDb)) {
			return $countOfQueuedArticles > self::ARTICLE_COUNT;
		}

		return $currentArticleId == $newestArticleIdInDb;
	}

	private function getArticleLink(\DOMElement $DOMElement): string
	{
		$article = $DOMElement->getElementsByTagName('a')->item(0);
		if (empty($article)) {
			throw new ArticleHrefNotFoundException();
		}
		return $article->getAttribute("href");
	}

	private function getArticleId(\DOMElement $DOMElement): int
	{
		$articleHref = $this->getArticleLink($DOMElement);
		$tmp = explode('/', $articleHref);
		$id = end($tmp);
		if (!is_numeric($id)) {
			throw new NotAllowedArticleId();
		}
		return (int)$id;
	}

	private function getArticlePublishingDateTime(\DOMElement $DOMElement): \DateTimeImmutable
	{
		$dateP = $DOMElement->getElementsByTagName('p')->item(0);
		if (empty($dateP)) {
			throw new DateNotFoundException();
		}

		$dateTimeInSourceFormat = $dateP->nodeValue;
		return $this->getDateTimeFromSourceFormat($dateTimeInSourceFormat);
	}

	private function getDateTimeFromSourceFormat(string $dateTimeInSourceFormat): \DateTimeImmutable
	{
		$tmp = [];
		if (!preg_match('/[0-2][0-9]:[0-5][0-9] • [0-9][0-9].[0-9][0-9].[0-9][0-9]/', $dateTimeInSourceFormat, $tmp)) {
			throw new DateWrongFormatException();
		}
		$tmp = $tmp[0];
		$tmp = explode(' • ', $tmp);

		$dateArr = explode('.', $tmp[1]);
		$hourAndMinute = explode(':', $tmp[0]);
		$day = (int)$dateArr[0];
		$month = (int)$dateArr[1];
		$year = $dateArr[2] + 2000;
		$hour = (int)$hourAndMinute[0];
		$minute = (int)$hourAndMinute[1];
		$dateTimeObj = new \DateTime();
		$dateTimeObj->setDate($year, $month, $day)->setTime($hour, $minute);

		return \DateTimeImmutable::createFromMutable($dateTimeObj);
	}

	private function getArticles(\DOMXPath $dOMXPath): \DOMNodeList
	{
		$className = "news-blocks";
		$articles = $dOMXPath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]");
		if (empty($articles)) {
			throw new ArticlesNotFoundException();
		}
		return $articles;
	}

	private function getHtml(string $url): string
	{
		$client = new \GuzzleHttp\Client();
		$res = $client->get($url);
		if ($res->getStatusCode() != self::HTTP_CODE_SUCCESS) {
			throw new HtmlNotFoundException();
		}
		return $res->getBody()->getContents();
	}

	private function getDom(string $html): DOMXPath
	{
		libxml_use_internal_errors(true);
		$dom = new domDocument;
		$dom->loadHTML($html);
		$dom->preserveWhiteSpace = false;
		$result = new DOMXPath($dom);
		return $result;
	}

	public function grabArticle(int $articleId, string $articleLink): void
	{
		try {
			DB::beginTransaction();

			if (Article::find($articleId)) {
				echo 'Article already exists' . PHP_EOL;
				return;
			}

			$htmlArticle = $this->getHtml($articleLink);
			$articleDOM = $this->getDom($htmlArticle);
			$article = $this->getArticle($articleDOM);

			$data = [];
			$data['id'] = $articleId;
			$data['title'] = $this->getTitle($article);
			$data['description'] = $this->getDescription($article);
			$data['url'] = $articleLink;
			$data['date'] = $this->getArticlePublishingDateTime($article)->format('Y-m-d H:i:s');
			$data['original_image'] = $this->getImage($article);
			$data['image'] = str_replace(self::RESOURCE_IMAGE_URL, '', $data['original_image']);

			$this->removeLastArticleIfNeeded();
			Article::create($data);

			DB::commit();
		} catch (\Throwable $e) {
			DB::rollback();
			echo 'Error occurred while saving the article ' . $articleId . ', reason: ' . PHP_EOL . $e->getMessage() . PHP_EOL;
			throw $e; //throwing the exception again, to increase queue attempt
		}

	}

	private function getArticle(\DOMXPath $dOMXPath): \DOMElement
	{
		$id = 'item';
		$item = $dOMXPath->query("//*[@id='$id']")->item(0);
		if (empty($item)) {
			throw new ArticleNotFoundException();
		}
		return $item;
	}

	private function getTitle(\DOMElement $dOMElement): string
	{
		$node = $dOMElement->getElementsByTagName('h1')->item(0);
		if (empty($node)) {
			throw new TitleNotFoundException();
		}
		return $node->nodeValue;
	}

	private function getDescription(\DOMElement $dOMElement): string
	{
		$content = $dOMElement->getElementsByTagName('div')->item(8);
		if (empty($content)) {
			throw new DescriptionNotFoundException();
		}
		$descriptions = $content->getElementsByTagName('p');
		$description = '';
		foreach ($descriptions as $key => $val) {
			$description .= $val->nodeValue;
		}
		return $description;
	}

	private function getImage(\DOMElement $dOMElement): string
	{
		$imgDiv = $dOMElement->getElementsByTagName('div')->item(8);
		if (empty($imgDiv)) {
			throw new ImageNotFoundException();
		}
		$imgSrc = $imgDiv->getElementsByTagName('img')->item(0);
		if (empty($imgSrc)) {
			throw new ImageNotFoundException();
		}
		$imgSrc = $imgSrc->getAttribute("src");
		$filename = basename($imgSrc);
		$path = str_replace(self::RESOURCE_IMAGE_URL, '', $imgSrc);
		$path = str_replace($filename, '', $path);
		$this->createFolder($path);
		Image::make($imgSrc)->save(public_path($path . $filename));
		return $imgSrc;
	}

	private function createFolder(string $path): void
	{
		if (!File::exists(public_path($path))) {
			File::makeDirectory(public_path($path), $mode = 0777, true, true);
		}
	}

	private function removeLastArticleIfNeeded(): void
	{
		$articlesCount = Article::count();
		if ($articlesCount < self::ARTICLE_COUNT) {
			return;
		}
		Article::orderBy('date')->first()->delete();
	}
}