<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\PurchaseHistoryMiniCollection;
use App\Http\Resources\V2\PurchaseHistoryCollection;
use App\Http\Resources\V2\PurchaseHistoryItemsCollection;
use App\Http\Resources\V2\ProductMiniCollection;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Shop;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class PurchaseHistoryController extends Controller
{
    public function index(Request $request)
    {
        $order_query = Order::query();

        $order_query->where('payment_status', 'paid');

        if ($request->delivery_status != "" || $request->delivery_status != null) {
            $delivery_status = $request->delivery_status;
            $order_query->whereIn("id", function ($query) use ($delivery_status) {
                $query->select('order_id')
                    ->from('order_details')
                    ->where('delivery_status', $delivery_status);
            });
        }
        return new PurchaseHistoryMiniCollection($order_query->where('user_id', auth()->user()->id)->latest()->paginate(5));

    }

    public function details($id)
    {
        $order_detail = Order::where('id', $id)->where('user_id', auth()->user()->id)->get();
        // $order_query = auth()->user()->orders->where('id', $id);

        // return new PurchaseHistoryCollection($order_query->get());
        return new PurchaseHistoryCollection($order_detail);
    }

    public function items($id)
    {
        $order_query = OrderDetail::where('order_id', $id);
        return new PurchaseHistoryItemsCollection($order_query->get());
    }

    public function purchased($id)
    {
        $shop = Shop::findOrFail($id);
        $orders = Order::where('user_id', $shop->user_id)->pluck('id')->toArray();
        $product_ids=OrderDetail::whereIn('order_id', $orders)->pluck('product_id')->toArray();
        $existing_product_ids = Product::whereIn('id', $product_ids);

        return new ProductMiniCollection($existing_product_ids->latest()->paginate(10));
    }
}
