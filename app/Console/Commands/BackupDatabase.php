<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use File as LFile;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup Database';
    private $namefilesql;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');

        $backupPath = storage_path('app/backups');

        File::ensureDirectoryExists($backupPath."/sql");
        $backupFilePathSQL = $backupPath . "/sql/";
        $this->namefilesql = date('Y-m-d_H-i-s') . '_backup.sql';
        $backupFilePathSQL .= $this->namefilesql;
        $command = "mysqldump -u ".$username." --password=".$password." $database > $backupFilePathSQL";

        $this->process =  Process::fromShellCommandline($command);
    }
    
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): void
    {
        $this->process->run(null, ['MESSAGE' => 'Something to output']);
        
        Storage::disk('google')->put($this->namefilesql, Storage::disk('local')->get('backups/sql/'.$this->namefilesql));
    }
}
