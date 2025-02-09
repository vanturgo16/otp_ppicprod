<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstToolAux extends Model
{
    use HasFactory;
    protected $table = 'master_tool_auxiliaries';
    protected $guarded = [
        'id'
    ];
}
