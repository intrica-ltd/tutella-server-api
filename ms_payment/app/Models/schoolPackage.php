<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class schoolPackage extends Model
{
    protected $table = 'schoolPackage';
    protected $fillable = ['billing_package_id'];

    public function package() {
        return $this->belongsTo('App\Models\BillingPackages', 'billing_package_id');
    }
}