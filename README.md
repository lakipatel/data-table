# Laravel Data Table
Laravel Data Table will allow you to easily create Listing, Searching, Sorting and Download CSV for Laravel.


# Install
```
composer require lakipatel/data-table
```

Add bellow lines in config/app.php under providers
```
Maatwebsite\Excel\ExcelServiceProvider::class,
Lakipatel\DataTable\DataTableServiceProvider::class
```


# CLI - Create Data Table Object
run this commnad to generate data table **php artisan data-table:create**

Above command will create a file under app/DataTables/ directory.


Change your controller like

```php
namespace App\Http\Controllers;

use App\DataTables\UserDataTable;

class UsersController
{

    public function index()
    {
        $dataTableHTML = UserDataTable::toHTML();
        return view('users.index', compact('dataTableHTML'));
    }

}

```


Edit resources/views/users/index.blade.php in putt bellow line where you want to display data tabel.

{!! $dataTableHTML !!}





