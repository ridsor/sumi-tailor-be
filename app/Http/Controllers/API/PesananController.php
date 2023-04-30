<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use App\Models\Pesanan;
use App\Models\User;

class PesananController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pesanan = Pesanan::orderByDesc('id')->get();

        return response()->json([
            'status' => 'success',
            'message'=> 'The user has successfully retrieved the order data',
            'pesanan' => $pesanan,
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
        $validator = Validator::make($request->all(),[
            'nama' => 'required|string|max:100',
            'deskripsi' => 'max:500|nullable',
            'harga' => 'max:100|nullable',
        ]);

        if($validator->fails()) return response()->json([
            'status' => 'fail',
            'message' => 'Valdidation failed',
            'errors' => $validator->errors(),
        ],400);

        // Retrieve a portion of the validated input...
        $validated = $validator->safe()->only(['nama', 'deskripsi', 'harga']);

        $pesanan = Pesanan::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'The user has successfully placed an order',
            'pesanan' => $pesanan
        ],201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Pesanan $pesanan)
    {
        return response()->json([
            'status' => 'success',
            'message'=> 'The user has successfully retrieved order data based on id',
            'pesanan' => $pesanan,
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {   
        if(!Gate::forUser(getContentJWT())->allows('is-admin-super')) return Response('',403);

        Pesanan::destroy($id);

        return response()->json([
            'status' => 'success',
            'message' => 'The user has successfully deleted the order'
        ]);
    }
}
