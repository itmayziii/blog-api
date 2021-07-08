<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunSqlCommand extends Command
{
    protected $signature = "db:sql {file : The name of the SQL file to execute}";

    protected $description = 'Execute an SQL file';

    public function handle()
    {
        $sqlFileToExecute = $this->argument('file');
        $fullSqlFilePath = database_path("sql/$sqlFileToExecute");
        if (!file_exists($fullSqlFilePath)) {
            $this->error("$fullSqlFilePath does not exist");
            return;
        }

        $rawSql = file_get_contents($fullSqlFilePath);
        if ($rawSql === false) {
            $this->error("Could not read contents of file $fullSqlFilePath");
            return;
        }

        DB::unprepared($rawSql);
    }
}
