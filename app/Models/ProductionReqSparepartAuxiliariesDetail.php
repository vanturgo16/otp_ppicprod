<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionReqSparepartAuxiliariesDetail extends Model
{
    use HasFactory; 
	protected $table = 'request_tool_auxiliaries_details';
    protected $guarded=[
        'id'
    ];
}
