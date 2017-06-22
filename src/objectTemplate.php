<?php
return <<<HTML
<?php
namespace {$rootNameSpace}DataTables;

use Lakipatel\DataTable\DataTable;
use DB;

class {$className} extends DataTable
{

    /**
     * Define unique table id
     * @var mixed \$uniqueID
     */
    public \$uniqueID = '{$tableName}';


    /**
     * Define how many rows you want to display on a page
     * @var int \$rowPerPage
     */
    public \$rowPerPage;


    /**
     * Define row per page dropdown options
     * @var array \$rowPerPageOptions
     */
    public \$rowPerPageOptions;


    /**
     * Define mysql column name which you want to default on sorting
     * @var string \$sortBy
     */
    public \$sortBy;

    /**
     * Define default soring direction
     * Example: ASC | DESC
     * @var string \$sortByDirection
     */
    public \$sortByDirection;

    /**
     * Get Resource of Query Builder
     */
    public function resource()
    {
        return DB::table('{$tableName}');
    }

    /**
     * Get Columns with key value
     */
    public function columns()
    {
        return {$this->arrayToString($columns, true)};
    }

    /**
     * Return columns id which you want to allow on sorting
     * @return array
     */
    public function sortableColumns()
    {
        return {$this->arrayToString($columns)};
    }

    /**
     * Return columns id with label which you want to allow on download
     * @return array
     */
    public function downloadableColumns()
    {
        return {$this->arrayToString($columns, true)};
    }

    /**
     * Return columns id with data type which you want to allow on searching
     * @return array
     */
    public function searchableColumns()
    {
        return {$this->arrayToString($searchable_columns, true)};
    }
}
HTML;
