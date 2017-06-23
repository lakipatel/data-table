# Laravel Data Table
Laravel Data Table will allow you to easily create Listing, Searching, Sorting and Download CSV for Laravel.


# Install
composer require lakipatel/data-table

Add bellow lines to config/app.php under providers

Maatwebsite\Excel\ExcelServiceProvider::class,
Lakipatel\DataTable\DataTableServiceProvider::class


# CLI - Create Data Table Object
run this commnad to generate data table **php artisan data-table:create**

Above command will create a file under app/DataTables/ directory.

