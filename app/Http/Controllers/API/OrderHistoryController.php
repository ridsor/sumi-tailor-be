<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\OrderHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderHistoryController extends Controller
{
    public function index(Request $request)
    {
        if(!Gate::forUser(getAuthUser())->allows('is-super-or-admin')) return Response('',403);
        $page = $request->query('page') ? $request->query('page') : 1;
        $limit = $request->query('limit') ? $request->query('limit') : 5;
        $search = $request->query('search') ? $request->query('search') : '';
        
        $orders = OrderHistory::orderByDesc('updated_at')->where('name','like','%'.$search.'%')->limit($limit)->offset(($page - 1) * $limit)->get();
        $total = OrderHistory::where('name','like','%'.$search.'%')->count();

        return response()->json([
            'status' => 'success',
            'message'=> 'Successfully fetched order history data',
            'data' => $orders,
            'page'=> $page,
            'limit' => $limit,
            'total' => $total,
        ]);
    }
}
