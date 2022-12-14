<?php


namespace App\Console\Commands;

use App\Services\OrderService;

use App\Models\Order;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

use RicorocksDigitalAgency\Soap\Facades\Soap;
use Illuminate\Support\Facades\DB;

class OrderTracking extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'orderTracking';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Order Tracking';


	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{

        $orders = Order::where('payment_status', 'paid')->where('delivery_status', '!=', 'confirmed')->where('delivery_status', '!=', 'delivered')->get();

        foreach($orders as $order){
            if($order->delivery_status=='pending'){
                (new OrderService)->get_tracking_code($order->id);
            }else{
                $order = Order::find($id);
                $istek = Soap::to('https://pttws.ptt.gov.tr/GonderiTakipVTest2/services/Sorgu?wsdl');

                    $data=[
                        'kullanici'      => '904875811',
                        'referansNo'     => $order->shipping_code,
                        'sifre'      	 => 'jSr1hVrJyJoLNr7nNqMPYw',
                    ];

                    $response = $istek->gonderiSorgu_referansNo(['input'=>$data]);
                    if(DB::table('logs')->where('text',json_encode($response,JSON_UNESCAPED_UNICODE))->count()==0){
                        DB::table('logs')->insert(['title'=>'order tracking cron','text'=>json_encode($response,JSON_UNESCAPED_UNICODE)]);
                    }

                /*
                $istek = Soap::to('https://ws.yurticikargo.com/KOPSWebServices/WsReportWithReferenceServices?wsdl');
                   $data=[
                           'userName'        		=> 'CIZGITURIZMYENI',
                           'password'        		=> '02v1d1pp3dmn7d15',
                           'language'      		=> 'TR',
                           'custParamsVO'				=> array(
                               'invCustIdArray' => "909344613" ),
                           'fieldName'      		=> 54,
                           'fieldValue'      		=> $order->shipping_code,
                           'startDate'      		=> null,
                           'endDate'      			=> null,
                           'dateParamType'      	=> null,
                           'withCargoLifeCycle'    => 0,
                           ];
                   $response = $istek->listInvDocumentInterfaceByReference($data);
                   if($response->ShippingDataResponseVO->outFlag==0){
                        try{
                            if($response->ShippingDataResponseVO->shippingDataDetailVOArray->cargoEventExplanation=='Teslim Edildi'){
                                $request = new Request();
                                $request->order_id = $order->id;
                                $request->status = 'delivered';
                                (new OrderService)->handle_delivery_status($request);
                            }

                            elseif($response->ShippingDataResponseVO->shippingDataDetailVOArray->cargoEventExplanation=='Yolda'){
                                $request = new Request();
                                $request->order_id = $order->id;
                                $request->status = 'on_the_way';
                                (new OrderService)->handle_delivery_status($request);
                            }

                            elseif($response->ShippingDataResponseVO->shippingDataDetailVOArray->cargoEventExplanation=='Dağıtımda'){
                                $request = new Request();
                                $request->order_id = $order->id;
                                $request->status = 'on_delivery';
                                (new OrderService)->handle_delivery_status($request);
                            }
                        }
                        catch(\Exception $e){

                        }

                    }
                    DB::table('logs')->insert(['title'=>'order tracking cron','text'=>json_encode($response,JSON_UNESCAPED_UNICODE)]);
                    */
            }
        }

	}


}
