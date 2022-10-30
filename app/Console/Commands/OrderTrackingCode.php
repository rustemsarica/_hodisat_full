<?php


namespace App\Console\Commands;


use App\Models\Order;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class OrderTrackingCode extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'orderTrackingCode';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Get Order Tracking Code';


	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{



	}


}
