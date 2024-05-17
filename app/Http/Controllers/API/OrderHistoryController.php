<?php

namespace App\Http\Controllers;
use App\Models\OrderHistory;

use Illuminate\Http\Request;


class OrderHistoryController extends Controller
{
    public function index(Request $request)
    {
        if(!Gate::forUser(getAuthUser())->allows('is-super-or-admin')) return Response('',403);
        
        $page = $request->query('page') ? $request->query('page') : 1;
        $limit = $request->query('limit') ? $request->query('limit') : 5;
        $status = $request->query('status') ? $request->query('status') : 'isProcess';
        $search = $request->query('search') ? $request->query('search') : '';
    
        $orders = Order::orderByDesc('updated_at')->where('name','like','%'.$search.'%')->where('status',$status)->limit($limit)->offset(($page - 1) * $limit)->get();
        $total = Order::where('status',$status)->where('name','like','%'.$search.'%')->count();

        return response()->json([
            'status' => 'success',
            'message'=> 'Successfully fetched order data',
            'data' => $orders,
            'page'=> $page,
            'limit' => $limit,
            'total' => $total,
        ]);
    }
}
