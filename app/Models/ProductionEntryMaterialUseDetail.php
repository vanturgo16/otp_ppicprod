<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionEntryMaterialUseDetail extends Model
{
    use HasFactory;
	protected $table = 'report_material_use_details';
    protected $guarded=[
        'id'
    ];
}
