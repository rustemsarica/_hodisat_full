<?php
namespace App\Services;

use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\Product;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Models\Shippingkey;
use App\Models\Address;
use App\Models\City;
use App\Models\State;
use App\Utility\NotificationUtility;
use App\Utility\SmsUtility;

use RicorocksDigitalAgency\Soap\Facades\Soap;
use Illuminate\Support\Facades\DB;

class OrderService{

    public function handle_delivery_status(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->delivery_viewed = '0';
        $order->delivery_status = $request->status;
        $order->save();

        if ($request->status == 'cancelled' && $order->payment_type == 'wallet') {
            $user = User::where('id', $order->user_id)->first();
            $user->shop->admin_to_pay += $order->grand_total;
            $user->save();
        }

        if($request->status=='confirmed'){
            calculateCommissionAffilationClubPoint($order);
        }

        foreach ($order->orderDetails as $key => $orderDetail) {

            $orderDetail->delivery_status = $request->status;
            $orderDetail->save();

            if ($request->status == 'cancelled') {
                $product = Product::where('id', $orderDetail->product_id)->first();

                    if ($product != null) {
                        $product->current_stock += $orderDetail->quantity;
                        $product->save();
                    }
            }

            if (addon_is_activated('affiliate_system') && auth()->user()->user_type == 'admin') {
                if (($request->status == 'delivered' || $request->status == 'cancelled') &&
                    $orderDetail->product_referral_code
                ) {

                    $no_of_delivered = 0;
                    $no_of_canceled = 0;

                    if ($request->status == 'delivered') {
                        $no_of_delivered = $orderDetail->quantity;
                    }
                    if ($request->status == 'cancelled') {
                        $no_of_canceled = $orderDetail->quantity;
                    }

                    $referred_by_user = User::where('referral_code', $orderDetail->product_referral_code)->first();

                    $affiliateController = new AffiliateController;
                    $affiliateController->processAffiliateStats($referred_by_user->id, 0, 0, $no_of_delivered, $no_of_canceled);
                }
            }
        }

        if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'delivery_status_change')->first()->status == 1) {
            try {
                SmsUtility::delivery_status_change(json_decode($order->shipping_address)->phone, $order);
            } catch (\Exception $e) {

            }
        }

        //sends Notifications to user
        NotificationUtility::sendNotification($order, $request->status);
        if (get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request->device_token = $order->user->device_token;
            $status = translate(str_replace("_", " ", $order->delivery_status));
            $request->title = "Siparişin {$status}";
            $request->text = "{$order->code} numaralı siparişin {$status}";

            $request->type = "order";
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            NotificationUtility::sendFirebaseNotification($request);
        }

    }

    public function handle_payment_status(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->payment_status_viewed = '0';
        $order->save();

        if (auth()->user()->user_type == 'seller') {
            foreach ($order->orderDetails->where('seller_id', auth()->user()->id) as $key => $orderDetail) {
                $orderDetail->payment_status = $request->status;
                $orderDetail->save();
            }
        } else {
            foreach ($order->orderDetails as $key => $orderDetail) {
                $orderDetail->payment_status = $request->status;
                $orderDetail->save();
            }
        }

        $status = 'paid';
        foreach ($order->orderDetails as $key => $orderDetail) {
            if ($orderDetail->payment_status != 'paid') {
                $status = 'unpaid';
            }
        }
        $order->payment_status = $status;
        $order->save();


        if ($order->payment_status == 'paid' && $order->commission_calculated == 0) {
            calculateCommissionAffilationClubPoint($order);
        }

        //sends Notifications to user
        NotificationUtility::sendNotification($order, $request->status);
        if (get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request->device_token = $order->user->device_token;
            $request->title = "Order updated !";
            $status = str_replace("_", "", $order->payment_status);
            $request->text = " Your order {$order->code} has been {$status}";

            $request->type = "order";
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            NotificationUtility::sendFirebaseNotification($request);
        }


        if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'payment_status_change')->first()->status == 1) {
            try {
                SmsUtility::payment_status_change(json_decode($order->shipping_address)->phone, $order);
            } catch (\Exception $e) {

            }
        }
        return 1;

    }

    public function update_tracking_code(Request $request) {
        $order = Order::findOrFail($request->order_id);
        $order->tracking_code = $request->tracking_code;
        $order->save();

        return 1;
   }

    public function generateUniqueCode(){
        do {
            $shipping_key = random_int(100000000000, 999999999999);
        } while (Shippingkey::where("shipping_key", $shipping_key)->first());

        return $shipping_key;
    }

    public function create_shipping_code($id) {

        $order = Order::findOrFail($id);
        $seller=User::find($order->seller_id);

        $shipping_address = json_decode($order->shipping_address, true);

        $state=State::where('name',$shipping_address['state'])->first();

        $seller_city=City::where('id',$seller->city)->first();
        $seller_state=State::where('id',$seller->state)->first();
        $shipping_key=$this->generateUniqueCode();

        //return redirect('/orders')->with('status', 'Gönderi kodu oluşturuldu!'.$shipping_key);
        try{

            $istek = Soap::to('https://ws.yurticikargo.com/KOPSWebServices/NgiShipmentInterfaceServices?wsdl');


			$shipmentData=[
				'ngiDocumentKey' 		=> $shipping_key,
				'cargoType' 			=> 1,
				'totalCargoCount' 		=> 1,
				'totalDesi' 			=> 1,
				'totalWeight' 			=> null,
				'personGiver' 			=> 'DEPO OPERASYON SORUMLUSU',
				'description' 			=> 'ENTEGRASYON TEST KAYDI',
				'selectedArrivalUnitId' => null,
				'selectedArrivalTransferUnitId' => null,
				'productCode' 			=> 'STA',
				'complementaryProductDataArray' => array('complementaryProductCode' =>null),
				'docCargoDataArray' => array(
					'ngiCargoKey' =>$shipping_key,
					'cargoType' =>1,
					'cargoDesi' =>1,
					'cargoWeight' =>null,
					'cargoCount' =>1,
					'length' =>null,
					'width' =>null,
					'height' =>null,
					'docCargoSpecialFieldDataArray' =>null,
				),
				'specialFieldDataArray' => array(
					'specialFieldName' => '54',
					'specialFieldValue' => $shipping_key,
				),
				'codData' => array(
					'ttInvoiceAmount' =>null,
					'dcSelectedCredit' =>null,
				),
			];

			$XSenderCustAddress=[
				'senderCustName'		=> $seller->name,
				'senderAddress'			=> $seller->address.' '.$seller_city->name.'/'.$seller_state->name,
				'cityId'				=> $seller->state,
				'townName'				=> $seller_city->name,
				'senderMobilePhone'		=> str_replace(['+',' '],'',$seller->phone),
			];

			$XConsigneeCustAddress=[
				'consigneeCustName'		=> $shipping_address['name'],
				'consigneeAddress'		=> $shipping_address['address'].' '.$shipping_address['city'].'/'.$shipping_address['state'],
				'cityId'				=> $state->id,
				'townName'				=> $shipping_address['city'],
				'consigneeMobilePhone'	=> $shipping_address['phone'],
			];

			$XPayerCustData=[
				'invCustId'				=> 909344613,
				'invAddressId'			=> null,
			];

			$data=[
				'wsUserName'        	=> 'CIZGITURIZMYENI',
				'wsPassword'        	=> '02v1d1pp3dmn7d15',
				'wsUserLanguage'      	=> 'TR',
				'shipmentData'			=> $shipmentData,
				'XSenderCustAddress'	=> $XSenderCustAddress,
				'XConsigneeCustAddress'	=> $XConsigneeCustAddress,
				'payerCustData'			=> $XPayerCustData,
			];

            $response = $istek->createNgiShipmentWithAddress($data);
            if($response->XShipmentDataResponse->outFlag==0){
                Shippingkey::insert(['shipping_key'=>$shipping_key]);
				$order->shipping_comp = "yurtici_kargo";
				$order->shipping_code = $shipping_key;
                $order->save();
                return $shipping_key;
            }elseif($response->XShipmentDataResponse->outFlag==2){
                DB::table('logs')->insert(['text'=>json_encode($response,JSON_UNESCAPED_UNICODE)]);
                return false;
            }

        }catch(Exception $e){
            DB::table('logs')->insert(['text'=>$e->getMessage()]);
			return false;
        }


    }

    public function cancel_shipping_code($id) {

        $order = Order::findOrFail($id);
        $key=$order->shipping_code;
            $istek = Soap::to('https://ws.yurticikargo.com/KOPSWebServices/NgiShipmentInterfaceServices?wsdl');
            $data=[
                    'wsUserName'        		=> 'CIZGITURIZMYENI',
                    'wsPassword'        		=> '02v1d1pp3dmn7d15',
                    'wsUserLanguage'      		=> 'TR',
                    'ngiCargoKey'               => $key,
                    'ngiDocumentKey'            => $key,
                    'cancellationDescription'   => 'İPTAL İŞLEMİ MÜŞTERİ İSTEĞİ İLE',

                    ];
            $response = $istek->cancelNgiShipment($data);
            if($response->XCancelShipmentResponse->outFlag==0){
                return true;
            }else{
                return false;
            }

    }

    public function get_tracking_code($id)
    {
        $order = Order::find($id);
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
			   $tracking_number=$response->ShippingDataResponseVO->shippingDataDetailVOArray->docId; //kargo takip numarası
			   $tracking_url=$response->ShippingDataResponseVO->shippingDataDetailVOArray->trackingUrl; //kargo takip linki
               $order->tracking_code = $tracking_number;
               $order->tracking_url = $tracking_url;
               $order->delivery_status = 'picked_up';
               $order->save();

		   }else{
            DB::table('logs')->insert(['text'=>json_encode($response,JSON_UNESCAPED_UNICODE)]);
		   }
    }

}
