<?php


namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Http\Request;

use App\Models\Order;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

use RicorocksDigitalAgency\Soap\Facades\Soap;
use Illuminate\Support\Facades\DB;

class OrderDelivered extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'orderDelivered';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Order Delivered';


	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{

        $orders = Order::where('payment_status', 'paid')->where('delivery_status',  'on_delivery')->get();

        foreach($orders as $order){

                $order = Order::find($order->id);
                $istek = Soap::to('https://pttws.ptt.gov.tr/GonderiTakipV2/services/Sorgu?wsdl');
		        $data=[
				   'kullanici'      => '904875811',
				   'referansNo'     => $order->shipping_code,
				   'sifre'      	=> 'jSr1hVrJyJoLNr7nNqMPYw',
				   ];
		        $response = $istek->gonderiSorgu_referansNo(['input'=>$data]);
                if($response->response->return->TESALAN!="" && $response->response->return->TESALAN!=NULL && $response->response->return->TESALAN!=" "){
                    $request = new Request();
                    $request->order_id = $order->id;
                    $request->status = 'delivered';
                    (new OrderService)->handle_delivery_status($request);
                }
                if(DB::table('logs')->where(['title'=>'order tracking cron','text'=>json_encode($response,JSON_UNESCAPED_UNICODE)])->count()==0){
                    DB::table('logs')->insert(['title'=>'order tracking cron','text'=>json_encode($response,JSON_UNESCAPED_UNICODE)]);
                }

        }

	}


}
