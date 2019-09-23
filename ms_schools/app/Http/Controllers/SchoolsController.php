<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;

class SchoolsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $response = School::createSchoolData($request->all());
        
        if(isset($response['success']))
            return ['success' => 1, 'data'=> ['school_id' => $response['school']->id] ];
        else
            return $response;
    }

    public function list()
    {
        $schools = School::select('id', 'school_name', 'address', 'email', 'phone', 'logo_id', 'active')->get();
        $new_schools = School::where('created_at', '>=', date('Y-m-01 00:00:00'))->count();
        $active_schools = School::where('active', 1)->count();

        return ['success' => 1, 'data'=> ['schools' => $schools, 'new_schools' => $new_schools, 'active_schools' => $active_schools, 'total_schools' => $schools->count()]];

    }

    public function activate(Request $request)
    {
        $school = School::where('id', $request->get('school_id'))->first();
        if(!$school)
            return ['error'=>1, 'errors' => [['School does not exist']]];

        $school->active = 1;
        $school->save();

        return ['success' =>1, 'data'=>['user'=>$school]];
    }

    public function deactivate(Request $request)
    {
        $school = School::where('id', $request->get('school_id'))->first();
        if(!$school)
            return ['error'=>1, 'errors' => ['School does not exist']];

        $school->active = 0;
        $school->save();

        return ['success' =>1, 'data'=>['school'=>$school]];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $school = School::where('id', $id)->first();

        if($school)
            return ['success' =>1, 'data'=>['school'=>$school]];

        return ['error'=>1, 'errors' => ['School does not exist']];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $school = School::where('id', $request->get('school_id'))->first();
        if(!$school)
            return ['error'=>1, 'errors' => ['School not exsist']];

        if($request->has('school_name'))
            $school->school_name = $request->get('school_name');
        if($request->has('address'))
            $school->address = $request->get('address');
        if($request->has('email'))
            $school->email = $request->get('email');
        if($request->has('address_lat'))
            $school->address_lat = $request->get('address_lat');
        if($request->has('address_lng'))
            $school->address_lng = $request->get('address_lng');
        if($request->has('phone'))
            $school->phone = $request->get('phone');
        if($request->has('logo'))
            $school->logo = $request->get('logo');

        $school->save();
        return ['success'=>1, 'data'=>['school'=>$school]];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
	    $school = School::where('id', $id)->first();
	    if(!$school)
		    return ['error'=>1, 'errors' => ['School not exsist']];

	    $school->active = -1;
	    $school->cancelled_at = date('Y-m-d H:i:s');
	    $school->save();

	    return ['success' => 1];
    }

    public function facebookPage(Request $request)
    {
        $school = School::where('id', $request->get('school_id'))->first();
        if(!$school)
            return ['error'=>1, 'errors' => ['School not exsist']];

        $school->fb_access_token = $request->get('token');
        $school->token_created_at = date('Y-m-d H:i:s');
        $school->fb_page_id = $request->get('page_id');
        $school->fb_page_url = $request->get('page_url');
        $school->save();

        return ['success' => 1];
    }

    public function getFbPage($school_id)
    {
        $school = School::where('id', $school_id)->first();
        if(!$school)
            return ['error'=>1, 'errors' => ['School not exsist']];

        return ['success' => 1, 'data' => ['fb_access_token' => $school->fb_access_token, 'fb_page_id' => $school->fb_page_id, 'logo_id' => $school->logo_id]];
    }

    public function changeLogo(Request $request, $id)
    {
        $school = School::where('id', $id)->first();
        if(!$school)
            return ['error'=>1, 'errors' => ['School not exsist']];

        $school->logo = $request->get('logo');
        $school->logo_id = $request->get('logo_id');
        $school->save();
        return ['success' => 1];
    }

    public function socialConnections($id)
    {
        $school = School::where('id', $id)->first();
        if(!$school)
            return ['error'=>1, 'errors' => ['School not exsist']];

        if($school->fb_access_token != null && $school->fb_page_url != null)
            return ['success' => 1, 'data' => ['connected' => 1, 'fb_page_url' => $school->fb_page_url]];

        return ['success' => 1, 'data' => ['connected' => 0, 'fb_page_url' => null]];
    }

    public function enrollmentCode(Request $request)
    {
        $school = School::where('id', $request->get('school_id'))->first();

        if(!$school)
            return ['error' => 1];

        $school->enrollment_code = $request->get('code');
        $school->poster_id = $request->get('poster_id');
        $school->save();

        return ['success' => 1];
    }
}
