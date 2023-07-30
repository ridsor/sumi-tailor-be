<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use App\Models\Message;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $message = Message::orderByDesc('id')->get();
        
        return Response([
            'status' => 'success',
            'message' => 'Successfully fetched message data',
            'data' => $message
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|unique:messages|max:100',
            'message' => 'required|max:500',
        ]);

        if($validator->fails()) return Response([
            'status' => 'fail',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ],400);

        $validated = $validator->safe()->only(['full_name','email','message']);

        $message = Message::create($validated);

        if(!$message) return Response([
            'status' => 'fail',
            'message' => 'Failed to add message',
        ],500);

        return Response([
            'status' => 'success',
            'message' => 'Successfully added message',
            'data' => $message,
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
        $message = Message::where('id',$id)->first();

        if(!$message) return Response([
            'status' => 'fail',
            'message' => 'Message data not found'
        ],404);   

        return Response([
            'status' => 'success',
            'message' => 'Successfully retrieve message data based on id',
            'data' => $message
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
        if(!Gate::allows('is-admin-super')) return Response('',403);

        $message = Message::where('id',$id)->first();

        if(!$message) return Response([
            'status' => 'fail',
            'message' => 'Message data not found'
        ],404);

        $rules = [
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|unique:messages|max:100',
            'message' => 'required|max:500'
        ];

        $validator = Validator::make($request->all(),$rules);

        if($validator->fails()) return Response([
            'status' => 'fail',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ],400);

        $validated = $validator->safe()->only(['full_name','email','message']);

        Message::where('id',$id)->update($validated);

        return Response([
            'status' => 'success',
            'message' => 'Successfully edited the message'
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

        $message = Message::where('id',$id)->first();

        if(!$message) return Response([
            'status' => 'fail',
            'message' => 'Message data not found'
        ],404);

        Message::destroy($id);

        return Response([
            'status' => 'success',
            'message' => 'Managed to delete the message'
        ]);
    }
}
