<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BankStatements extends Model
{
    protected $table = 'bank_statement';
    protected $fillable = [
        'school_id',
        'invoice_id',
        'name',
        'type',
        'path',
        'size',
        'owner_id'
    ];
}