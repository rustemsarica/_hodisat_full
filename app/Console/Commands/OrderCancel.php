<?php


namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Http\Request;

use App\Models\Order;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class OrderCancel extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'orderCancel';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Cancel order';


    private $unconfirmedOrderExpiration = 7; // Confirm the uncorfirmed orders after this expiration


	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
        $today = Carbon::now(config('app.timezone'));

        $orders = Order::where('delivery_status','pending')->get();
        foreach($orders as $order){
            if($today->diffInDays($order->updated_at) >= $this->unconfirmedOrderExpiration)
            {
                $request = new Request();
                $request->order_id = $order->id;
                $request->status = 'cancelled';
                (new OrderService)->handle_delivery_status($request);
                return;
            }
        }


	}


}
