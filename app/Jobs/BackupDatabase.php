<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Storage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use File as LFile;

class BackupDatabase implements ShouldQueue
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
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');

        $backupPath = storage_path('app\backups');

        File::ensureDirectoryExists($backupPath."/sql");
        File::ensureDirectoryExists($backupPath."/image");
        $backupFilePath = $backupPath . "/sql/";
        $nameFile = date('Y-m-d_H-i-s') . '_backup.sql';
        $backupFilePath .= $nameFile;
        $command = "mysqldump -u ".$username." --password=".$password." $database > $backupFilePath";
        exec($command);

        $orders = DB::table('order_history')->select('image')->whereRaw("updated_at >= NOW() - INTERVAL 1 WEEK")->get();
        $nameFileZip = date('Y-m-d_H-i-s').'_order_images.zip';
        
        $destinationFileZip = $backupPath."\\image\\".$nameFileZip;
        $sourceFolder = public_path('order-images');
        $zip = new ZipArchive;
        
        if ($zip->open($destinationFileZip, ZipArchive::CREATE) === TRUE)
        {
            $files = LFile::files($sourceFolder);
            
            foreach ($files as $key => $value) {
                $relativeNameInZipFile = basename($value);

                foreach($orders as $order) {
                    if($order->image == $relativeNameInZipFile) {
                        $zip->addFile($value, $relativeNameInZipFile);
                    }
                }
            }
             
            $zip->close();
        }

        Storage::disk('google')->put($nameFile, Storage::disk('local')->get('backups/sql/'.$nameFile));
        Storage::disk('google')->put($nameFileZip, Storage::disk('local')->get('backups/image/'.$nameFileZip));
        Storage::disk('local')->delete('backups/image/'.$nameFileZip);
    }
}
