<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GrnController;
use App\Http\Controllers\MstAccountCodesController;
use App\Http\Controllers\MstAccountTypesController;
use App\Http\Controllers\TransDataBankController;
use App\Http\Controllers\TransDataKasController;

//PRODUCTION
use App\Http\Controllers\ProductionController;

//Route Login
Route::get('/', [AuthController::class, 'login'])->name('login');
Route::post('auth/login', [AuthController::class, 'postlogin'])->name('postlogin')->middleware("throttle:5,2");

//Route Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
	//Production
	//START REQUEST SPAREPART AND AUXILIARIES
	Route::get('/production-req-sparepart-auxiliaries', [ProductionController::class, 'production_req_sparepart_auxiliaries'])->name('production_req_sparepart_auxiliaries');
	Route::get('/production-req-sparepart-auxiliaries-add', [ProductionController::class, 'production_req_sparepart_auxiliaries_add'])->name('production_req_sparepart_auxiliaries_add');
	Route::post('/production-req-sparepart-auxiliaries-save', [ProductionController::class, 'production_req_sparepart_auxiliaries_save'])->name('production_req_sparepart_auxiliaries_save');	
	Route::post('/production-req-sparepart-auxiliaries-hold', [ProductionController::class, 'production_req_sparepart_auxiliaries_hold'])->name('production_req_sparepart_auxiliaries_hold');	
	Route::post('/production-req-sparepart-auxiliaries-approve', [ProductionController::class, 'production_req_sparepart_auxiliaries_approve'])->name('production_req_sparepart_auxiliaries_approve');	
	Route::post('/production-req-sparepart-auxiliaries-delete', [ProductionController::class, 'production_req_sparepart_auxiliaries_delete'])->name('production_req_sparepart_auxiliaries_delete');	
    Route::get('/production-req-sparepart-auxiliaries-detail/{id}', [ProductionController::class, 'production_req_sparepart_auxiliaries_detail'])->name('production_req_sparepart_auxiliaries_detail');
    Route::post('/production-req-sparepart-auxiliaries-detail-update', [ProductionController::class, 'production_req_sparepart_auxiliaries_detail_update'])->name('production_req_sparepart_auxiliaries_detail_update');	
    Route::post('/production-req-sparepart-auxiliaries-detail-add', [ProductionController::class, 'production_req_sparepart_auxiliaries_detail_add'])->name('production_req_sparepart_auxiliaries_detail_add');	
	Route::get('/production-req-sparepart-auxiliaries-detail-edit-get/{id}', [ProductionController::class, 'production_req_sparepart_auxiliaries_detail_edit_get'])->name('production_req_sparepart_auxiliaries_detail_edit_get');
	Route::put('/production-req-sparepart-auxiliaries-detail-edit-save/{id}', [ProductionController::class, 'production_req_sparepart_auxiliaries_detail_edit_save'])->name('production_req_sparepart_auxiliaries_detail_edit_save');
	Route::post('/production-req-sparepart-auxiliaries-detail-delete', [ProductionController::class, 'production_req_sparepart_auxiliaries_detail_delete'])->name('production_req_sparepart_auxiliaries_detail_delete');
	//END REQUEST SPAREPART AND AUXILIARIES
	//START ENTRY MATERIAL USE
	Route::get('/production-ent-material-use', [ProductionController::class, 'production_entry_material_use'])->name('production_entry_material_use');
	Route::get('/production-ent-material-use-json', [ProductionController::class, 'production_entry_material_use_json'])->name('production_entry_material_use_json');
	Route::get('/production-ent-material-use-add', [ProductionController::class, 'production_entry_material_use_add'])->name('production_entry_material_use_add');
	Route::get('/json_get_work_center', [ProductionController::class, 'jsonGetWorkCenter'])->name('jsonGetWorkCenter');
	Route::get('/json_get_regu', [ProductionController::class, 'jsonGetRegu'])->name('jsonGetRegu');
	Route::post('/production-ent-material-use-save', [ProductionController::class, 'production_entry_material_use_save'])->name('production_entry_material_use_save');
	//END ENTRY MATERIAL USE
	//START REPORT BLOW
	//Route::get('/production-ent-report-blow', [ProductionController::class, 'production_entry_report_blow'])->name('production_entry_report_blow');
	//END REPORT BLOW
	
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
});