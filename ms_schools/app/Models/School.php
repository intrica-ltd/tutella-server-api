<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\CurlHelper;
use Carbon\Carbon;


class School extends Model
{
    protected $table = 'schools';

    protected $fillable   = [
        'user_id',
        'school_name',
        'address',
        'address_lat',
        'address_lng',
        'email',
        'phone',
        'logo'
    ];

    public static function createSchoolData($data)
    {
        $school = new School();
        $school->user_id = $data['user_id'];
        $school->school_name = $data['school_name'];
        $school->address = $data['address'];
        $school->email = $data['email'];
        
        if(isset($data['address_lat']))
            $school->address_lat = $data['address_lat'];

        if(isset($data['address_lng']))
            $school->address_lng = $data['address_lng'];

        if(isset($data['phone']))
            $school->phone = $data['phone'];

        if(isset($data['logo']))
            $school->logo = $data['logo'];

        if(isset($data['logo_id']))
            $school->logo_id = $data['logo_id'];

        $school->save();

        return ['success' => 1, 'school' => $school];   
        
    }
    
}