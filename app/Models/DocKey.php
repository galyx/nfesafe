<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'doc_key',
        'issuer_cnpj',
        'issuer_name',
        'issuer_state',
        'recipient_cnpj',
        'recipient_name',
        'recipient_state',
        'document_template',
        'grade_series',
        'note_number',
        'issue_date',
        'amount',
    ];
}
