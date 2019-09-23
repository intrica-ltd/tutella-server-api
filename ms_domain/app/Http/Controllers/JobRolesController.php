<?php

namespace App\Http\Controllers;

use App\Models\JobRole;

class JobRolesController extends Controller
{
    public function index()
    {
        $jobRoles = JobRole::all();

        return response()->json($jobRoles)->setStatusCode(200, 'success');
    }
}