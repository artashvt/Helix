<?php

namespace App\Jobs;

use App\Services\GrabService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GrabArticle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    /**
     * @var int
     */
    private $articleId;

    /**
     * @var string
     */
    private $articleLink;

    public function __construct(int $articleId, string $articleLink)
    {
        $this->articleId = $articleId;
        $this->articleLink = $articleLink;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(GrabService $grabService)
    {
        $grabService->grabArticle($this->articleId, $this->articleLink);
    }
}
