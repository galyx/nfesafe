<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LastNsu extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'total_download',
        'last_nsu_nfe',
        'last_nsu_cte',
    ];
}
