<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MonthlyTemp;

class DashboardController extends Controller
{
    public function index()
    {
        $data = MonthlyTemp::latest()->limit(6)->get();
        
        $result = [];
        for($i = 5; $i >= 0; $i--) {
            $order_total =  (isset($data[$i]->order_total)) ? $data[$i]->order_total : 0;
            $total_income =  (isset($data[$i]->total_income)) ? $data[$i]->total_income : 0;
            $result[] = [
                'order_total' => $order_total,
                'total_income' => $total_income,
            ];
        }

        return Response([
            'status' => 'success',
            'message' => 'Dashboard data has been successfully retrieved',
            'data' => $result
        ]);
    }
}
