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
use ZipArchive;
use File as LFile;

class BackupImage implements ShouldQueue
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
        $backupPath = storage_path('app/backups');

        File::ensureDirectoryExists($backupPath."/image");

        $nameFileZip = (env('APP_ENV') == 'production') ? 'order_images.zip':'tes_order_images.zip';
        $pathFileZip = (env('APP_ENV') == 'production') ? '14YUiRWNdprZBSK1PBPHkKgCnZ52vyus2':'13BRbtlwjhiKmkVSvyE3brYShWCm3EXMU';
        
        $destinationFileZip = $backupPath."/image/".$nameFileZip;
        $sourceFolder = (env('APP_ENV') == 'production') ? "/home/rids8499/public_html/api.sumitailor.ridsor.my.id/order-images": public_path('order-images');
         
        $zip = new ZipArchive();
        if ($zip->open($destinationFileZip, ZipArchive::CREATE) === TRUE)
        {
            $files = LFile::files($sourceFolder);
            
            foreach ($files as $key => $value) {
                $relativeNameInZipFile = basename($value);
                $zip->addFile($value, $relativeNameInZipFile);
            }
             
            $zip->close();
        }
        Storage::disk('google')->put($pathFileZip, Storage::disk('local')->get('backups/image/'.$nameFileZip));
    }
}
