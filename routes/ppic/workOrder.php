<?php

use App\Http\Controllers\Ppic\workOrderController;


Route::group(
  ['prefix' => 'ppic/workOrder'],
  function () {
    Route::controller(workOrderController::class)->group(function () {
      Route::get('/', 'index')->name('ppic.workOrder.index');
      Route::get('/create', 'create')->name('ppic.workOrder.create');
      // Route::get('/get-data', 'getData')->name('ppic.workOrder.getData');
      // Route::get('/get-customers', 'getCustomers')->name('ppic.workOrder.getCustomers');
      Route::get('/get-order-detail', 'getOrderDetail')->name('ppic.workOrder.getOrderDetail');
      Route::get('/generate-wo-number', 'generateWONumber')->name('ppic.workOrder.generateWONumber');
      Route::get('/get-data-product', 'getDataProduct')->name('ppic.workOrder.getDataProduct');
      Route::get('/get-product-detail', 'getProductDetail')->name('ppic.workOrder.getProductDetail');
      Route::get('/get-all-unit', 'getAllUnit')->name('ppic.workOrder.getAllUnit');
      Route::post('/', 'store')->name('ppic.workOrder.store');
      Route::get('/edit/{encryptedSONumber}', 'edit')->name('ppic.workOrder.edit');
      Route::get('/get-data-sales-order', 'getDataSalesOrder')->name('ppic.workOrder.getDataSalesOrder');
      // Route::put('/', 'update')->name('ppic.workOrder.update');
      Route::get('/show/{encryptedSONumber}', 'show')->name('ppic.workOrder.view');
      // Route::post('/bulk-posted', 'bulkPosted')->name('ppic.workOrder.bulkPosted');
      // Route::post('/bulk-unposted', 'bulkUnPosted')->name('ppic.workOrder.bulkUnPosted');
      // Route::post('/bulk-deleted', 'bulkDeleted')->name('ppic.workOrder.bulkDeleted');
      // Route::get('/preview/{encryptedSONumber}', 'preview')->name('ppic.workOrder.preview');
      Route::get('/print/{encryptedSONumber}', 'print')->name('ppic.workOrder.print');
    });
  }
);
