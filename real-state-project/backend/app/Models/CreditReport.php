<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditReport extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'credit_reports';

    protected $fillable = [
        'user_id',
        'credit_report_pdf',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'date_of_birth',
        'identity_number',
        'marital_status',
        'signature',
        'documents_identity_document',
        'documents_photo',
        'report_date',
        'data',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
