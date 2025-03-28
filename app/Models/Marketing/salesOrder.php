<?php

namespace App\Models\Marketing;

use App\Models\Marketing\salesOrderDetail;
use App\Models\ppic\workOrder;
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

    // Definisikan relasi many-to-one ke tabel master_customers
    public function masterCustomer()
    {
        return $this->belongsTo(\App\Models\MstCustomers::class, 'id_master_customers', 'id');
    }

    // Definisikan relasi many-to-one ke tabel master_salesman
    public function masterSalesman()
    {
        return $this->belongsTo(\App\Models\MstSalesmans::class, 'id_master_salesmen', 'id');
    }

    public function workOrder()
    {
        return $this->hasMany(workOrder::class, 'id_sales_orders', 'id');
    }
}
