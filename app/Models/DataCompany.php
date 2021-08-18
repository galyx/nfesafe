<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cnpj',
        'corporate_name',
        'fantasy_name',
        'address',
        'number',
        'adrres2',
        'city',
        'state',
        'post_code',
        'ie',
    ];
}
