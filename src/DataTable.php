<?php
namespace Lakipatel\DataTable;

use Maatwebsite\Excel\Facades\Excel;

abstract class DataTable
{

    /**
     * Define unique table id
     * @var mixed $rowPerPage
     */
    public $uniqueID;


    /**
     * Define how many rows you want to display on a page
     * @var int $rowPerPage
     */
    public $rowPerPage;


    /**
     * Define row per page dropdown options
     * @var array $rowPerPageOptions
     */
    public $rowPerPageOptions;


    /**
     * Define mysql column name which you want to default on sorting
     * @var string $defaultSortBy
     */
    public $sortBy;

    /**
     * Define default soring direction
     * Example: ASC | DESC
     * @var string $defaultSortBy
     */
    public $sortByDirection;

    /**
     * Set debug true to get mysql query in XHR request for json
     * @var mixed $rowPerPage
     */
    public $debug;

    /**
     * Get HTML of Data Table
     */
    public final static function toHTML()
    {
        $self = new static();
        $uniqueID = $self->uniqueID ?: 'unique_id_'.time();
        $ajaxDataURI = route( 'dataTableJSON', encrypt(get_class($self)) );
        $columns = $self->columns();
        $sortableColumns = $self->sortableColumns();
        $searchableColumns = $self->searchableColumns();
        $filters = $self->filters();
        $downloadOptions = $self->downloadableColumns();

        $rowPerPage = $self->rowPerPage ?: config('data-table.row_per_page');
        $rowPerPageOptions = $self->rowPerPageOptions ?: config('data-table.row_per_page_options');

        if(!in_array($rowPerPage, $rowPerPageOptions)) {
            $rowPerPageOptions[] = $rowPerPage;
        }

        $orderBy = ['sort' => $self->sortBy ?: '', 'sort_direction' => $self->sortBy ? ($self->sortByDirection ?: 'desc') : ''];

        return view('data-table::layout', compact('uniqueID', 'ajaxDataURI', 'columns', 'sortableColumns', 'searchableColumns', 'filters', 'downloadOptions', 'rowPerPage', 'rowPerPageOptions', 'orderBy'));
    }
    /**
     * Get JSON of Data Table
     */
    public final static function toJSON()
    {
        $returnData = ['total_pages' => 1, 'current_page' => 1, 'from' => 0, 'to' => 0, 'total_records' => 0, 'data' => []];

        request()->merge([
           'page' => ( !is_numeric(request()->get('page')) || request()->get('page') < 1 ) ? 1 : request()->get('page'),
           'limit' => ( !is_numeric(request()->get('limit')) || request()->get('limit') < 1 ) ? 10 : request()->get('limit')
        ]);

        $self = new static();
        $queryBuilder = $self->resource();

        if(request()->get('sort_by')) {
            $queryBuilder->orderBy($self->getSqlColumn(request()->get('sort_by')), ( request()->get('sort_by_direction') == 'desc' ? 'desc' : 'asc') );
        }

        if(request()->get('action') == 'search-columns' && !empty(request()->get('filters', []))) {
            $self->ajaxSearchColumns($queryBuilder);
        } elseif(request()->get('action') == 'search' && !empty(request()->get('filters', ''))) {
            $self->ajaxGlobalSearch($queryBuilder);
        }

        return $self->response($queryBuilder, $returnData);
    }

    public function response(&$queryBuilder, &$returnData)
    {
        if(request()->get('download') == 'csv') {

            $downloadColumns = $this->downloadableColumns();

            $data = $queryBuilder->get();
            if($data && !is_array($data)) {
                $data = $data->toArray();
            }

            $returnData = [];
            foreach($downloadColumns as $downloadColumnKey => $downloadColumnVal) {
                $returnData[0][$downloadColumnKey] = $downloadColumnVal;
            }

            if(!empty($data)) {
                foreach($data as $row) {
                    $row = (array) $row;
                    $tmpArray = [];
                    foreach($downloadColumns as $downloadColumnKey => $downloadColumnVal) {
                        if(method_exists($this, "getColumn".studly_case($downloadColumnKey))) {
                            $val = call_user_func_array(array($this, "getColumn".studly_case($downloadColumnKey)), array($row, 'download'));
                        } else if( isset($row[$downloadColumnKey]) ) {
                            $val = $row[$downloadColumnKey];
                        } else {
                            $val = "";
                        }
                        $tmpArray[$downloadColumnKey] = $val;
                    }
                    $returnData[] = $tmpArray;
                }
            }

            Excel::create('download_' . time(), function($excel) use ($returnData) {
                $excel->sheet('Sheet 1', function($sheet) use ($returnData) {
                    $sheet->setAllBorders('thin');
                    $sheet->fromArray($returnData, null, 'A1', false, false);
                    $sheet->setAutoSize(true);
                });
            })->export('csv');

        } else {
            $data = $queryBuilder->paginate( request()->get('limit') )->toArray();
            if($this->debug == true) {
                $returnData['sql'] = $queryBuilder->toSql();
            }
            $returnData['data'] = $this->processRows($data['data']);
            $returnData['total_pages'] = isset($data['last_page']) ? $data['last_page'] : 1;
            $returnData['current_page'] = isset($data['current_page']) ? $data['current_page'] : 1;
            $returnData['from'] = isset($data['from']) ? $data['from'] : 0;
            $returnData['to'] = isset($data['to']) ? $data['to'] : 0;
            $returnData['total_records'] = isset($data['total']) ? $data['total'] : 0;
        }

        return $returnData;
    }

    public function ajaxGlobalSearch(&$queryBuilder)
    {
        $queryBuilder->where(function($query){
            foreach($this->searchableColumns() as $key => $keyType) {
                if($keyType == 'string') {
                    $query->orWhere($this->getSqlColumn($key), 'like', '%'.request()->get('filters').'%');
                } else if($keyType == 'date') {
                    if($dt = strtotime(request()->get('filters'))) {
                        $dt = date('Y-m-d', $dt);
                        $query->orWhereRaw("DATE(".$this->getSqlColumn($key).") = DATE('{$dt}')");
                    }
                } else {
                    $query->orWhere($this->getSqlColumn($key), '=', request()->get('filters'));
                }
            }
        });
    }

    public function ajaxSearchColumns(&$queryBuilder)
    {
        $searchableColumns = $this->searchableColumns();
        $queryBuilder->where(function($query) use ($searchableColumns) {
            foreach(request()->get('filters', []) as $key => $val) {
                if(isset($searchableColumns[$key])) {
                    if($searchableColumns[$key] == 'date') {
                        if($dt = strtotime($val)) {
                            $dt = date('Y-m-d', $dt);
                            $query->whereRaw("DATE(".$this->getSqlColumn($key).") = DATE('{$dt}')");
                        }
                    } else {
                        $query->where($this->getSqlColumn($key), '=', $val);
                    }
                }
            }
        });
    }


    /**
     * Get ColumnID to sql field name
     * @param $column
     * @return mixed
     */
    public function getSqlColumn($column) {
        if(method_exists($this, "mapDBColumns")) {
            $sqlMap = $this->mapDBColumns();
            if(isset($sqlMap[$column])) {
                return $sqlMap[$column];
            }
        }
        return $column;
    }


    /**
     * Process Each Result row
     * @param array $data
     * @return array
     */
    public function processRows($data = array())
    {
        $returnData = [];
        $columns = $this->columns();

        if( empty($columns) ) {
            return [];
        }

        if( !empty($data) ) {
            foreach($data as $row) {
                $row = (array) $row;
                $tmpArray = [];
                foreach($columns as $key => $title) {
                    if(method_exists($this, "getColumn".studly_case($key))) {
                        $val = call_user_func_array(array($this, "getColumn".studly_case($key)), array($row, request()->get('action')));
                    } else if( isset($row[$key]) ) {
                        $val = $row[$key];
                    } else {
                        $val = "";
                    }
                    $tmpArray[$key] = $val;
                }
                $returnData[] = $tmpArray;
            }
        }
        return $returnData;
    }


    /**
     * return Illuminate Database Query Builder
     * @return cursor
     */
    abstract public function resource();


    /**
     * Return Columns to Display on Table
     * @return array ['id' => 'title']
     */
    abstract public function columns();


    /**
     * Return columns id which you want to allow on sorting
     * @return array
     */
    public function sortableColumns()
    {
        return [];
    }


    /**
     * Return columns id which you want to allow on searching
     * @return array
     */
    public function searchableColumns()
    {
        return [];
    }


    /**
     * Return columns id with filter options
     * @return array
     */
    public function filters()
    {
        return ['id' => ['label' => 'ID', 'data' => ['key' => 'val']]];
    }


    /**
     * Return columns id with label which you want to allow on download
     * @return array
     */
    public function downloadableColumns()
    {
        return [];
    }
}