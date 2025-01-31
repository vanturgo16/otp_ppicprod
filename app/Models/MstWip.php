<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstWip extends Model
{
    use HasFactory;
    protected $table = 'master_wips';
    protected $guarded = [
        'id'
    ];
}
