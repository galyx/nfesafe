<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocXml extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'doc_key',
        'nsu',
        'event_type',
        'event_sequence',
        'note_status',
        'reason',
        'protocol_number',
        'document_template',
        'grade_series',
        'note_number',
        'issue_date',
        'receipt_date',
        'organ_code',
        'issuer_cnpj',
        'issuer_name',
        'issuer_state',
        'recipient_cnpj',
        'recipient_name',
        'recipient_state',
        'app_version',
        'event_description',
        'amount',
        'event_time_date',
        'xml_received',
    ];
}
