<?php

namespace App\Http\Controllers\barcode;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TracabelityController extends Controller
{
        public function index()
    {
		$data = DB::table("report_blows.*", "b.wo_number", "c.name", "d.work_center_code", "d.work_center")
				->leftJoin('work_orders AS b', 'report_blows.id_work_orders', '=', 'b.id')
				->leftJoin('master_customers AS c', 'report_blows.id_master_customers', '=', 'c.id')
				->leftJoin('master_work_centers AS d', 'report_blows.id_master_work_centers', '=', 'd.id')
				->whereRaw( "sha1(report_blows.id) = '$response_id'")
                ->get();
        return view('barcode.table');
    }
}
