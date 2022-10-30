<?php


namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Http\Request;

use App\Models\Order;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class OrderConfirm extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'orderConfirm';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Check order delivery status';


    private $unconfirmedOrderExpiration = 3; // Confirm the uncorfirmed orders after this expiration


	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
        $today = Carbon::now();

        $orders = Order::where('delivery_status','delivered')->get();
        foreach($orders as $order){
            if($today->diffInDays($order->updated_at) >= $this->unconfirmedOrderExpiration)
            {
                $request = new Request();
                $request->setMethod('POST');
                $request->replace(['order_id' => $order->id]);
                $request->replace(['status' => 'confirmed']);
                // $request->order_id = $order->id;
                // $request->status = 'confirmed';
                (new OrderService)->handle_delivery_status($request);
                return;
            }
        }


	}


}
