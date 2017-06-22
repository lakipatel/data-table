<?php
Route::get('/lakipatel/data-table/{object}', function($object) {
    try {
        $object = decrypt($object);
        return response()->json(['status' => true, 'data' => $object::toJSON()]);
    } catch (\Exception $e) {
        return response()->json(['status' => false, 'message' => $e->getMessage()]);
    }
})->name('dataTableJSON');