<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisitionsPrice extends Model
{
    use HasFactory;
    protected $table = 'purchase_requisitions_price';
    protected $guarded=[
        'id'
    ];
}
