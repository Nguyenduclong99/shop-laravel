<?php

namespace App\Repositories\Eloquents;

use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Eloquents\BaseRepository;
use Illuminate\Support\Facades\DB;

use Auth;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function model()
    {
        return app(Order::class);
    }

    public function findOrder($id)
    {
        return $this->model()->where('id', $id)->first();
    }

    public function getOrdersFollowUser()
    {
        return User::findOrFail(Auth::user()->id)
            ->orders()
            ->with('products')
            ->get()
            ->sortByDesc('created_at');
    }

    public function getOrders($status)
    {
        return $this->model()->with('user')
            ->where('status', $status)
            ->get()
            ->sortByDesc('created_at');
    }

    public function updateStatusOrder($id)
    {
        $order = DB::table('orders')->where([['id', $id],['status', 1]])->get();
        if($order) {
                $list = DB::table('order_product')->where('order_id', $id)->get();
            // dd($list);
                foreach($list as $key => $item) {
                    DB::table('products')->where('id', $item->product_id)->decrement('quantity', $item->qty);
                }
        }
        return $this->model()->where('id', $id)->increment('status');
    }

    public function cancelOrder($id)
    {
        return $this->model()->where('id', $id)->where('status', 0)->update(['status' => Order::CANCELED]);
    }

    public function deleteOrder($id)
    {
        return $this->model()->findOrFail($id)->delete();
    }
}
