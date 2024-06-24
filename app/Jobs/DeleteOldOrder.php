<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DeleteOldOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $orders = DB::table('order_history')->select('image')->whereRaw("updated_at <= NOW() - INTERVAL 7 MONTH");
        $data = $orders->get();
        $orders->delete();
        foreach($data as $order) {
            $file = public_path("order-images\\".$order->image);
            if(File::exists($file)) {
                unlink($file);
            }
        }
    }
}
