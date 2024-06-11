<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GrnController;
use App\Http\Controllers\MstAccountCodesController;
use App\Http\Controllers\MstAccountTypesController;
use App\Http\Controllers\TransDataBankController;
use App\Http\Controllers\TransDataKasController;
use App\Http\Controllers\barcode\BarcodeController;
use App\Http\Controllers\warehouse\WarehouseController;

//PRODUCTION
use App\Http\Controllers\ProductionController;

//Route Login
Route::get('/', [AuthController::class, 'login'])->name('login');
Route::post('auth/login', [AuthController::class, 'postlogin'])->name('postlogin')->middleware("throttle:5,2");

//Route Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {

    //Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    //AccountType
    Route::get('/accounttype', [MstAccountTypesController::class, 'index'])->name('accounttype.index');
    Route::post('/accounttype', [MstAccountTypesController::class, 'index'])->name('accounttype.index');
    Route::post('accounttype/create', [MstAccountTypesController::class, 'store'])->name('accounttype.store');
    Route::post('accounttype/update/{id}', [MstAccountTypesController::class, 'update'])->name('accounttype.update');
    Route::post('accounttype/activate/{id}', [MstAccountTypesController::class, 'activate'])->name('accounttype.activate');
    Route::post('accounttype/deactivate/{id}', [MstAccountTypesController::class, 'deactivate'])->name('accounttype.deactivate');

    //AccountCode
    Route::get('/accountcode', [MstAccountCodesController::class, 'index'])->name('accountcode.index');
    Route::post('/accountcode', [MstAccountCodesController::class, 'index'])->name('accountcode.index');
    Route::post('accountcode/create', [MstAccountCodesController::class, 'store'])->name('accountcode.store');
    Route::post('accountcode/update/{id}', [MstAccountCodesController::class, 'update'])->name('accountcode.update');
    Route::post('accountcode/activate/{id}', [MstAccountCodesController::class, 'activate'])->name('accountcode.activate');
    Route::post('accountcode/deactivate/{id}', [MstAccountCodesController::class, 'deactivate'])->name('accountcode.deactivate');

    //TransDataKas
    Route::get('/transdatakas', [TransDataKasController::class, 'index'])->name('transdatakas.index');
    Route::post('/transdatakas', [TransDataKasController::class, 'index'])->name('transdatakas.index');
    Route::post('transdatakas/create', [TransDataKasController::class, 'store'])->name('transdatakas.store');
    Route::post('transdatakas/update/{id}', [TransDataKasController::class, 'update'])->name('transdatakas.update');
    Route::post('transdatakas/delete/{id}', [TransDataKasController::class, 'delete'])->name('transdatakas.delete');

    //TransDataBank
    Route::get('/transdatabank', [TransDataBankController::class, 'index'])->name('transdatabank.index');
    Route::post('/transdatabank', [TransDataBankController::class, 'index'])->name('transdatabank.index');
    Route::post('transdatabank/create', [TransDataBankController::class, 'store'])->name('transdatabank.store');
    Route::post('transdatabank/update/{id}', [TransDataBankController::class, 'update'])->name('transdatabank.update');
    Route::post('transdatabank/delete/{id}', [TransDataBankController::class, 'delete'])->name('transdatabank.delete');

    Route::get('/good-receipt-note', [GrnController::class, 'index'])->name('index');
    Route::get('/grn-pr-add', [GrnController::class, 'grn_pr_add'])->name('grn_pr_add');
    Route::get('/grn-po-add', [GrnController::class, 'grn_po_add'])->name('grn_po_add');
    Route::get('/get-data', [GrnController::class, 'get_data'])->name('get_data');
    Route::post('/simpan_pr_grn', [GrnController::class, 'simpan_pr_grn'])->name('simpan_pr_grn');
    Route::post('/simpan_po_grn', [GrnController::class, 'simpan_po_grn'])->name('simpan_po_grn');
    Route::get('/detail-grn-po/{id}', [GrnController::class, 'detail_grn_po'])->name('detail_grn_po');
    Route::get('/detail-grn-pr/{id}', [GrnController::class, 'detail_grn_pr'])->name('detail_grn_pr');
    Route::delete('/hapus_grn_detail/{id}/{idx}', [GrnController::class, 'hapus_grn_detail'])->name('hapus_grn_detail');
    Route::delete('/hapus_grn_detail_po/{id}/{idx}', [GrnController::class, 'hapus_grn_detail_po'])->name('hapus_grn_detail_po');
    Route::delete('/hapus_grn/{id}', [GrnController::class, 'hapus_grn'])->name('hapus_grn');
    Route::post('/simpan_detail_grn/{id}', [GrnController::class, 'simpan_detail_grn'])->name('simpan_detail_grn');
    Route::post('/simpan_detail_grn_po/{id}', [GrnController::class, 'simpan_detail_grn_po'])->name('simpan_detail_grn_po');
    Route::get('/get-edit-grn-pr/{id}', [GrnController::class, 'get_edit_grn_pr'])->name('get_edit_grn_pr');
    Route::get('/print-grn/{receipt_number}', [GrnController::class, 'print_grn'])->name('print_grn');
    Route::put('/posted_grn/{id}', [GrnController::class, 'posted_grn'])->name('posted_grn');
    Route::put('/unposted_grn/{id}', [GrnController::class, 'unposted_grn'])->name('unposted_grn');
    Route::get('/edit-grn/{id}', [GrnController::class, 'edit_grn'])->name('edit_grn');
    Route::post('/simpan_detail_po_fix', [GrnController::class, 'simpan_detail_po_fix'])->name('simpan_detail_po_fix');

    Route::get('/good-lote-number', [GrnController::class, 'good_lote_number'])->name('good_lote_number');
    Route::get('/generate-code', [GrnController::class, 'generateCode'])->name('generateCode');
    Route::put('/update_lot_number', [GrnController::class, 'update_lot_number'])->name('update_lot_number');
    Route::get('/good-lote-number-detail/{id}', [GrnController::class, 'good_lote_number_detail'])->name('good_lote_number_detail');
    Route::get('/generateBarcode/{lot_number}', [GrnController::class, 'generateBarcode'])->name('generateBarcode');
    Route::get('/grn-qc', [GrnController::class, 'grn_qc'])->name('grn_qc');
    Route::put('/qc_passed/{id}', [GrnController::class, 'qc_passed'])->name('qc_passed');
    Route::put('/un_qc_passed/{id}', [GrnController::class, 'un_qc_passed'])->name('un_qc_passed');
    Route::get('/external-no-lot', [GrnController::class, 'external_no_lot'])->name('external_no_lot');
    Route::put('/update_ext_lot_number', [GrnController::class, 'update_ext_lot_number'])->name('update_ext_lot_number');
    Route::get('/detail-external-no-lot/{lot_number}', [GrnController::class, 'detail_external_no_lot'])->name('detail_external_no_lot');
    include __DIR__ . '/ppic/workOrder.php';



    // mengawas uts
    Route::controller(BarcodeController::class)->group(function () {
        Route::get('/barcode', 'index')->name('barcode');
        Route::get('/create-barcode', 'create')->name('barcode.create');
        Route::post('/store-barcode', 'store')->name('post.create');
        Route::get('/cange-barcode-so/{id}', 'cange')->name('barcode.cange');
        Route::get('/print-standar-barcode/{id}', 'print_standar')->name('print_standar');
        Route::get('/print-broker-barcode/{id}', 'print_broker')->name('print_broker');
        Route::get('/print-cbc-barcode/{id}', 'print_cbc')->name('print_cbc');
        Route::get('/table', 'table_print')->name('table_print');
    });

    Route::controller(WarehouseController::class)->group(function () {
        Route::get('/packing-list', 'index')->name('packing-list');
        Route::post('/api/save-location', 'lokasi');
        Route::get('/show', 'show');
    });
});
