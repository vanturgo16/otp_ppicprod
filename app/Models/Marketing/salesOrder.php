<?php

namespace App\Models\Marketing;

use App\Models\marketing\salesOrderDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class salesOrder extends Model
{
    use HasFactory;
    protected $table = 'sales_orders';
    protected $guarded = [
        'id'
    ];

    // Definisikan relasi one-to-many ke tabel po_customer_details
    public function salesOrderDetails()
    {
        return $this->hasMany(salesOrderDetail::class, 'id_sales_orders', 'so_number');
    }

    // Definisikan relasi many-to-one ke tabel master_units
    public function masterUnit()
    {
        return $this->belongsTo(\App\Models\MstUnits::class, 'id_master_units', 'id');
    }
}
