<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use \App\Group;
use Ixudra\Curl\Facades\Curl;
use \App\GroupMembers;

class GroupsController extends Controller
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
        $result = Group::createGroupData($request->all());

        if(isset($result['success'])) 
            return ['success' => 1, 'data'=> ['group' => $result['group']] ];
        else
            return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $group = Group::where('id', $id)->first();
        $group_members = GroupMembers::where('group_id', $id)->pluck('user_id')->toArray();

        if(!$group)
            return ['error'=>1, 'errors' => ['Group does not exist']];

        return ['success' =>1, 'data'=>['group'=>$group, 'members'=>$group_members]];
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
        $result = Group::updateGroupData($request->all());
        
        if(isset($result['success'])) 
            return ['success' => 1];
        else
            return $response;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function list(Request $request)
    {
        if($request->get('user_role') == 'leader')
            $groups = Group::join('group_members', 'group_members.group_id', 'groups.id')
                        ->where('school_id', $request->get('school_id'))
                        ->where('group_members.user_id', $request->get('user_id'))
                        ->select('groups.id', 'groups.name', 'groups.nstudents', 'groups.nleaders', 'groups.created_at')
                        ->orderBy('id', 'desc')
                        ->get()->toArray();
        else
            $groups = Group::where('school_id', $request->get('school_id'))
                        ->select('id', 'name', 'nstudents', 'nleaders', 'created_at')
                        ->orderBy('id', 'desc')
                        ->get()->toArray();


        return ['success' => 1, 'data'=> ['groups' => $groups] ];
    }

    public function delete(Request $request)
    {
        Group::where('id', $request->get('group_id'))->delete();
        GroupMembers::where('group_id', $request->get('group_id'))->delete();

        return ['success' => 1];
    }

    public function showForUser($user_id) {
        $groups = GroupMembers::join('groups', 'group_members.group_id', 'groups.id')
                    ->where('group_members.user_id', $user_id)
                    ->select('group_members.group_id', 'groups.name')
                    ->get();

        return ['success' => 1, 'groups' => $groups];
    }

    public function addUserToGroups(Request $request)
    {
        $res = GroupMembers::addToGroups($request->get('user_id'), $request->get('groups'), $request->get('role'));
        return ['success' => 1];
    }

    public function groupUsers($school_id)
    {
        $groups = Group::where('school_id', $school_id)
                    ->pluck('id');

        $result = [];
        foreach($groups as $group) {
            $result[$group] = GroupMembers::where('group_id', $group)
                            ->pluck('user_id');
        }

        return ['success' => 1, 'data' => $result];
    }

    public function dashboardDetails($user_id)
    {
        $count_groups = GroupMembers::where('user_id', $user_id)->count();

        return ['success' => 1, 'count_groups' => $count_groups];
    }

    public function getUsers(Request $request)
    {
        $users = GroupMembers::whereIn('group_id', $request->get('groups'))->distinct('user_id')->pluck('user_id')->toArray();
        return ['success' => 1, 'users' => $users];
    }

    public function getForUser($user_id)
    {
        $groups = GroupMembers::join('groups', 'group_members.group_id', 'groups.id')
                    ->where('group_members.user_id', $user_id)
                    ->select('group_members.group_id', 'groups.name', 'groups.nstudents', 'groups.nleaders')
                    ->get();

        return ['success' => 1, 'data' => ['groups' => $groups, 'total_groups' => $groups->count()]];
    }

    public function total($school_id)
    {
        $count_groups = Group::where('school_id', $school_id)->count();

        return ['success' => 1, 'count_groups' => $count_groups];
    }

    public function removeMember($user_id)
    {
        GroupMembers::where('user_id', $user_id)->delete();
        return ['success' => 1];
    }

    public function getGroupData(Request $request)
    {
        $group_ids = explode(',', $request->get('group_ids'));
        if($request->get('user_role') == 'leader') {
            $assigned_groups = Group::join('group_members', 'group_members.group_id', 'groups.id')
                        ->whereIn('groups.id', $group_ids)
                        ->where('school_id', $request->get('school_id'))
                        ->where('group_members.user_id', $request->get('user_id'))
                        ->select('groups.id', 'groups.name', 'groups.nstudents', 'groups.nleaders', 'groups.created_at')
                        ->orderBy('id', 'desc')
                        ->get()->toArray();

            $groups = Group::join('group_members', 'group_members.group_id', 'groups.id')
                        ->whereNotIn('groups.id', $group_ids)
                        ->where('school_id', $request->get('school_id'))
                        ->where('group_members.user_id', $request->get('user_id'))
                        ->select('groups.id', 'groups.name', 'groups.nstudents', 'groups.nleaders', 'groups.created_at')
                        ->orderBy('id', 'desc')
                        ->get()->toArray();

        } else {
            $assigned_groups = Group::where('school_id', $request->get('school_id'))
                        ->whereIn('groups.id', $group_ids)
                        ->select('id', 'name', 'nstudents', 'nleaders', 'created_at')
                        ->orderBy('id', 'desc')
                        ->get()->toArray();

            $groups = Group::where('school_id', $request->get('school_id'))
                        ->whereNotIn('groups.id', $group_ids)
                        ->select('id', 'name', 'nstudents', 'nleaders', 'created_at')
                        ->orderBy('id', 'desc')
                        ->get()->toArray();

        }
        return ['success' => 1, 'data'=> ['groups' => $groups, 'assigned_groups' => $assigned_groups] ];
    }
}
