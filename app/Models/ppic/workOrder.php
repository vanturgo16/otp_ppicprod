<?php

namespace App\Models\ppic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class workOrder extends Model
{
    use HasFactory;
    protected $table = 'work_orders';
    protected $guarded = [
        'id'
    ];
}
