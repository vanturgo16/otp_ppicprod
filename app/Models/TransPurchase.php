<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransPurchase extends Model
{
    use HasFactory;
    protected $table = 'trans_purchase';
    protected $guarded=[
        'id'
    ];
}
