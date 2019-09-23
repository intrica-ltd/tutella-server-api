<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EnrollmentCode extends Model
{
    protected $table = 'enrollment_codes';
    protected $fillable = ['code', 'user_id', 'leader_id', 'school_admin_id', 'expiary_date'];
}
