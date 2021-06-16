<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UnitTransaction;

class Member extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'username'
    ];

    public function last_transaction()
    {
        return $this->hasOne(UnitTransaction::class)->latest();
    }

    public function transactions()
    {
        return $this->hasMany(UnitTransaction::class);
    }
}
