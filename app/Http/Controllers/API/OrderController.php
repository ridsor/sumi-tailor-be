<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\User;
use App\Models\MonthlyTemp;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        $page = $request->query('page') ? $request->query('page') : 1;
        $limit = $request->query('limit') ? $request->query('limit') : 5;
        $status = $request->query('status') ? $request->query('status') : 'isProcess';
        $search = $request->query('search') ? $request->query('search') : '';
    
        
        if(!Gate::forUser(getAuthUser())->allows('is-super-or-admin')) {
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
        } else {
            $orders = Order::orderByDesc('updated_at')->select('item_code','name','price','image','updated_at','created_at')->where('name','like','%'.$search.'%')->where('status',$status)->limit($limit)->offset(($page - 1) * $limit)->get();
            $total = Order::where('status',$status)->where('name','like','%'.$search.'%')->count();

            return response()->json([
                'status' => 'success',
                'message'=> 'Successfully fetched order data',
                'data' => $orders,
                'page'=> $page,
                'limit' => $limit,
                'total' => $total,
            ]);
        };
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!Gate::forUser(getAuthUser())->allows('is-super-or-admin')) return Response('',403);

        $messages = [
            'no_hp.unique' => 'No Handphone sudah ada',
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|max:100',
            'no_hp' => 'required|numeric|unique:orders',
            'address' => 'required|max:1000',
            'note' => 'required|max:1000',
            'price' => 'nullable|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5048'
        ], $messages);

        if($validator->fails()) return response()->json([
            'status' => 'fail',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ],400);

        // Retrieve a portion of the validated input...
        $validated = $validator->safe()->only(['name', 'note', 'price', 'no_hp', 'address','image']);
        $today = Carbon::now();
        $item_code = 'ST' . random_int(100,999) . $today->day . $today->month;
        $validated['item_code'] = $item_code;
        
        $validated['image'] = Str::random(10) . time() . '.' . $request->image->extension(); 
        $request->image->move(public_path('order-images'), $validated['image']);
        
        $order = Order::create($validated);

        if(!$order) {
                return Response([
                    'status' => 'fail',
                    'message' => 'Failed to add order',
            ],500);
        }
        
        return Response([
            'status' => 'success',
            'message' => 'Successfully added order',
            'data' => $order,
        ],201);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $item_code)
    {
        if(!Gate::forUser(getAuthUser())->allows('is-super-or-admin')) return Response('',403);
        
        $order = Order::where('item_code',$item_code)->first();

        if(!$order) return Response([
            'status' => 'fail',
            'message' => 'Order data not found'
        ],404);

        return Response([
            'status' => 'success',
            'message'=> 'Successfully retrieve order data based on id',
            'data' => $order,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(!Gate::forUser(getAuthUser())->allows('is-super-or-admin')) return Response('',403);

        $order = Order::where('item_code',$id)->first();
        
        if(!$order) return Response([
            'status' => 'fail',
            'message' => 'Order data not found'
        ],404);

        $rules = [
            'name' => 'required|max:100',
            'address' => 'required|max:1000',
            'note' => 'required|max:1000',
            'price' => 'nullable|numeric',
        ];

        $messages = [
            'no_hp.unique' => 'No Handphone sudah ada',
        ];

        if($order->no_hp != $request->input('no_hp')) {
            $rules['no_hp'] = 'required|numeric|unique:orders';
        }
        if($request->input('image')) {
            $rules['image'] = 'required|image|mimes:jpeg,png,jpg|max:5048';
        }

        $validator = Validator::make($request->all(),$rules, $messages);
        if($validator->fails()) return response()->json([
            'status' => 'fail',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ],400);
        $validated = $validator->safe()->only(['name', 'note', 'price', 'no_hp', 'address']);
        
        if($request->image) {
            $validated['image'] = Str::random(10) . time() . '.' . $request->image->extension(); 
            $request->image->move(public_path('order-images'), $validated['image']);
            
            if($order->image) {
                $file = public_path("order-images\\".$order->image);
                if(File::exists($file)) {
                    unlink($file);
                }
            }
        }
        Order::where('item_code',$id)->update($validated);
        $order = Order::where('item_code',$id)->first();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully edited the order',
            'data' => $order
        ]);
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {   
        $order = Order::where('item_code',$id)->first();
        
        if(!$order) return Response([
            'status' => 'fail',
            'message' => 'Order data not found',
        ],404);

        $description = 'Pesanan dihapus';
        
        OrderHistory::create([
            'item_code' => $order->item_code,
            'name' => $order->name,
            'no_hp' => $order->no_hp,
            'address' => $order->address,
            'price' => $order->price,
            'note' => $order->note,
            'description' => $description,
            'image' => $order->image
        ]); 

        Order::where('item_code',$id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Managed to delete the order'
        ]);
    }

    public function status(Request $request, $id) {
        if(!Gate::forUser(getAuthUser())->allows('is-super-or-admin')) return Response('',403);
        
        $order = Order::where('item_code',$id)->first();
        
        if(!$order) return Response([
            'status' => 'fail',
            'message' => 'Order data not found',
        ],404);
        
        if($order->status == 'isProcess') {
            Order::where('item_code',$id)->update([
                'status' => 'isFinished'
            ]);
        } else {
            Order::where('item_code',$id)->update([
                'status' => 'isProcess'
            ]);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'The user has successfully edited the order',
        ]);
    }

    public function confirm($id) {
        if(!Gate::forUser(getAuthUser())->allows('is-super-or-admin')) return Response('',403);
        
        $order = Order::where('item_code',$id)->first();
        
        if(!$order) return Response([
            'status' => 'fail',
            'message' => 'Order data not found',
        ],404);
        
        $record = MonthlyTemp::latest()->first();
        $today = Carbon::now('Asia/Jayapura');
        $latestRecord = Carbon::parse($record->updated_at);
        
        if($today->month == $latestRecord->month && $today->year == $latestRecord->year) {
            MonthlyTemp::where('id',$record->id)->update([
                'order_total' => $record->order_total + 1,
                'total_income' => $record->total_income + $order->price,
            ]);
        } else {
            MonthlyTemp::create([
                'order_total' => 1,
                'total_income' => $order->price,
            ]);
        }

        $description = "Pesanan berhasil";
        
        OrderHistory::create([
            'item_code' => $order->item_code,
            'name' => $order->name,
            'no_hp' => $order->no_hp,
            'address' => $order->address,
            'price' => $order->price,
            'note' => $order->note,
            'description' => $description,
            'image' => $order->image
        ]);

        Order::where('item_code',$id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Order successfully confirmed'
        ]);
    }
}
