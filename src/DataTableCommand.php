<?php
namespace Lakipatel\DataTable;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use DB;

class DataTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-table:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a data table object';

    protected $files;

    /**
     * Create a new command instance.
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        try {

            $rootNameSpace = $this->laravel->getNamespace();

            $tableName = $this->ask('Which table you want to use from database?');
            if(!Schema::hasTable($tableName)) {
                throw new \Exception("Table does not exist in database.");
            }

            $className = studly_case(str_singular($tableName)).'DataTable';
            $dataTableFile = app_path("DataTables/{$className}.php");

            if(file_exists($dataTableFile)) {
                throw new \Exception("Data table #{$className} is already exist.");
            }

            $columns = [];
            $tblColumns = Schema::getColumnListing($tableName);
            foreach($tblColumns as $tblColumns) {
                $useThisColumn = $this->confirm('Are you want to use '.$tblColumns.' columns.', true);
                if($useThisColumn) {
                    $columns[$tblColumns] = ucwords(str_replace('_', ' ', $tblColumns));
                }
            }

            if(count($columns) < 1) {
                throw new \Exception("Minimum one column should be used in data table.");
            }

            if (! $this->files->isDirectory(dirname($dataTableFile))) {
                $this->files->makeDirectory(dirname($dataTableFile), 0777, true, true);
            }

            $searchable_columns = [];
            foreach($columns as $k => $v) {
                $searchable_columns[$k] = 'string';
            }

            $file = include __DIR__.'/objectTemplate.php';
            $this->files->put($dataTableFile, $file);

            $this->info('Data table created successfully.');

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

    }

    protected function arrayToString( $arr, $isMultiDimensionArray = false) {
        $string = '[';
        foreach($arr as $k => $v) {
            if($isMultiDimensionArray) {
                $string .= "'{$k}' => '{$v}', ";
            } else {
                $string .= "'{$k}', ";
            }
        }
        $string = rtrim($string, ', ');
        $string .= ']';

        return $string;
    }
}