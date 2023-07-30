<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\User;

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
            'name' => 'required|string|max:100',
            'description' => 'max:500|nullable',
            'price' => 'max:11|nullable',
        ]);

        if($validator->fails()) return response()->json([
            'status' => 'fail',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ],400);

        // Retrieve a portion of the validated input...
        $validated = $validator->safe()->only(['name', 'description', 'price']);

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
            'status' => 'success',
            'message' => 'Order data not found'
        ],404);

        $rules = [
            'name' => 'required|string|max:100',
            'description' => 'max:500|nullable',
            'price' => 'max:100|nullable',
        ];

        $validator = Validator::make($request->all(),$rules);

        if($validator->fails()) return response()->json([
            'status' => 'fail',
            'message' => 'Valdidation failed',
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

    public function isFinished($id) {
        if(!Gate::allows('is-super-or-admin')) return Response('',403);

        $order = Order::where('id',$id)->first();
        if(!$order->finished) {
            $order->update([
                'finished' => true
            ]);
        } else {
            $order->update([
                'finished' => false
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'The user has successfully edited the order',
        ]);
    }
}
