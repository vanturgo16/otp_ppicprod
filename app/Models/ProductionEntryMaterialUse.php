<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionEntryMaterialUse extends Model
{
    use HasFactory;
	protected $table = 'work_orders';
    protected $guarded=[
        'id'
    ];
}
