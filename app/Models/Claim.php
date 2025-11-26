<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    protected $fillable = [
        'email',
        'service_type',
        'first_name',
        'last_name',
        'document_number',
        'phone',
        'subject',
        'description',
        'file_path',
        'status',
    ];
}
