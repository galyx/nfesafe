<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cnpj',
        'corporate_name',
        'fantasy_name',
        'address',
        'number',
        'address2',
        'complement',
        'city',
        'state',
        'post_code',
        'phone1',
        'phone2',
        'certificate',
        'validate_certificate',
        'password',
        'active',
    ];
}
