<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionReqSparepartAuxiliaries extends Model
{
    use HasFactory;
	protected $table = 'request_tool_auxiliaries';
    protected $guarded=[
        'id'
    ];
}
