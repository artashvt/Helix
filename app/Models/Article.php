<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use App\Services\GrabService as GrabService;

class Article extends Model
{
	protected $table = 'articles';

	protected $fillable = [
		'id', 'title', 'description', 'original_image', 'image', 'date', 'url'
	];

	public function delete()
	{
		if ($this->attributes['image']) {
			$file = str_replace(GrabService::RESOURCE_IMAGE_URL, '', $this->attributes['image']);
			if (File::exists(public_path($file))) {
				$file = str_replace(basename($file), '', $file);
				File::deleteDirectory(public_path($file));
			}
		}
		parent::delete();
	}


	public static function getNewestArticleId(): ?int
	{
		$newestArticle = static::orderBy('date', 'desc')->first();
		return empty($newestArticle) ? null : (int)$newestArticle->id;
	}
}
