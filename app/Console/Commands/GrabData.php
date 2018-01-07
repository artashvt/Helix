<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GrabService;

class GrabData extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'grab:data';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Grab the Data from www.tert.am';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle(GrabService $grabService): void
	{
		$grabService->refreshArticles();
	}
}
