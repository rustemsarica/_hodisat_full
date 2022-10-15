<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PurchaseHistoryItemsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {

                $refund_section = false;
                $refund_button = false;
                $refund_label = "";
                $refund_request_status = 99;
                if (addon_is_activated('refund_request')) {
                    $refund_section = true;
                    $no_of_max_day = get_setting('refund_request_time');
                    $last_refund_date = $data->created_at->addDays($no_of_max_day);
                    $today_date = \Carbon\Carbon::now();
                    if ($data->product != null &&
                        $data->product->refundable != 0 &&
                        $data->refund_request == null &&
                        $today_date <= $last_refund_date &&
                        $data->payment_status == 'paid' &&
                        $data->delivery_status == 'delivered') {
                        $refund_button = true;
                    } else if ($data->refund_request != null && $data->refund_request->refund_status == 0) {
                        $refund_label = "Pending";
                        $refund_request_status = $data->refund_request->refund_status;
                    } else if ($data->refund_request != null && $data->refund_request->refund_status == 2) {
                        $refund_label = "Rejected";
                        $refund_request_status = $data->refund_request->refund_status;
                    } else if ($data->refund_request != null && $data->refund_request->refund_status == 1) {
                        $refund_label = "Approved";
                        $refund_request_status = $data->refund_request->refund_status;
                    } else if ($data->product->refundable != 0) {
                        $refund_label = "N/A";
                    } else {
                        $refund_label = "Non-refundable";
                    }
                }
                return [

                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
