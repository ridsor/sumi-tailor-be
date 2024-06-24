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

        $backupPath = 'storage/app/backups';

        File::ensureDirectoryExists($backupPath);
        $backupFilePath = $backupPath . '/';
        $nameFile = date('Y-m-d_H-i-s') . '_backup.sql';
        $backupFilePath .= $nameFile;
        $command = "mysqldump -u ".$username." --password=".$password." $database > $backupFilePath";
        exec($command);

        Storage::disk('google')->put($nameFile, Storage::disk('local')->get('backups/'.$nameFile));
    }
}
