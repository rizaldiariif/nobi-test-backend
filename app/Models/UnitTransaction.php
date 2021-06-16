<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitTransaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'member_id',
        'type',
        'amount_rupiah',
        'amount_unit',
        'total_amount_rupiah',
        'total_amount_unit'
    ];
}
