<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\User;
use App\Models\MonthlyTemp;
use App\Models\Temp;
use Illuminate\Support\Carbon;
use Firebase\JWT\JWT;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $order = Order::orderByDesc('id')->get();

        return response()->json([
            'status' => 'success',
            'message'=> 'Successfully fetched order data',
            'data' => $order,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!Gate::allows('is-super-or-admin')) return Response('',403);

        $validator = Validator::make($request->all(),[
            'name' => 'required|max:100',
            'email' => 'required|email|unique:orders|max:100',
            'no_hp' => 'required|numeric|unique:orders|max:100',
            'address' => 'required|max:100',
            'description' => 'required|max:500',
            'price' => 'max:11|nullable|numeric',
        ]);

        if($validator->fails()) return response()->json([
            'status' => 'fail',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ],400);

        // Retrieve a portion of the validated input...
        $validated = $validator->safe()->only(['name', 'description', 'price', 'email', 'no_hp', 'address']);

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
            'data' => $order
        ],201);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::where('id',$id)->first();

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
        if(!Gate::allows('is-super-or-admin')) return Response('',403);

        $order = Order::where('id',$id)->first();
        
        if(!$order) return Response([
            'status' => 'fail',
            'message' => 'Order data not found'
        ],404);

        $rules = [
            'name' => 'required|max:100',
            'email' => 'required|email|unique:orders|max:100',
            'no_hp' => 'required|numeric|unique:orders|max:100',
            'address' => 'required|max:100',
            'description' => 'required|max:500',
            'price' => 'max:11|nullable|numeric',
        ];

        $validator = Validator::make($request->all(),$rules);

        if($validator->fails()) return response()->json([
            'status' => 'fail',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ],400);

        // Retrieve a portion of the validated input...
        $validated = $validator->safe()->only(['name', 'description', 'price']);

        Order::where('id',$id)->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully edited the order',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {   
        if(!Gate::allows('is-admin-super')) return Response('',403);

        $order = Order::where('id',$id)->first();

        if(!$order) return Response([
            'status' => 'fail',
            'message' => 'Order data not found',
        ],404);

        Order::destroy($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Managed to delete the order'
        ]);
    }

    public function status($id) {
        if(!Gate::allows('is-super-or-admin')) return Response('',403);
        
        $order = Order::where('id',$id)->first();

        if(!$order) return Response([
            'status' => 'fail',
            'message' => 'Order data not found',
        ],404);

        if($order->status == 'isProcess') {
            $order->update([
                'status' => 'isCompleted'
            ]);
        } else {
            $order->update([
                'status' => 'isProcess'
            ]);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'The user has successfully edited the order',
        ]);
    }

    public function confirm($id) {
        if(!Gate::allows('is-super-or-admin')) return Response('',403);
        
        $order = Order::where('id',$id)->first();
        
        if(!$order) return Response([
            'status' => 'fail',
            'message' => 'Order data not found',
        ],404);
        
        $record = MonthlyTemp::latest()->first();
        $today = Carbon::now();
        $latestRecord = Carbon::parse($record->updated_at);

        if($today->month == $latestRecord->month && $today->year == $latestRecord->year) {
            MonthlyTemp::where('id',$record->id)->update([
                'order_total' => $record->order_total + 1,
                'total_income' => $record->total_income + $order->price,
            ]);
        } else {
            MonthlyTemp::create([
                'order_total' => $record->order_total + 1,
                'total_income' => $record->total_income + $order->price,
            ]);
        }

        Order::destroy($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Order successfully confirmed'
        ]);
    }

    public function register_order() {
        if(!Gate::allows('is-super-or-admin')) return Response('',403);

        $user = decodeJWT(getJWT(), env('JWT_SECRET'));
        if(!$user) return Response('',403);

        $key = env('REGISTER_ORDER_JWT_SECRET');
        $tokenTime = env('REGISTER_ORDER_TOKEN_TIME_TO_LIVE');
        $requestTime = now()->timestamp;
        $requestExpired = $requestTime + $tokenTime;
        $payload = [
            'user_id' => $user->user_id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'iat' => $requestTime,
            'exp' => $requestExpired
        ];
        
        $token = JWT::encode($payload, $key, 'HS256');
        
        Temp::latest()->first()->update([
            'register_order_token' => $token
        ]);

        return Response([
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'data' => [
                'token' => $token
            ]
        ]);
    }
}
