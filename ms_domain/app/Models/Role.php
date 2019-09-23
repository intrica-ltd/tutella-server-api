<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = ['name', 'display_name'];
    public $timestamps = false;

    public static function get($id)
    {
        return Role::where('id', '=', $id)->first();
    }
    
}
