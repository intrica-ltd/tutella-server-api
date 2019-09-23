<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class GroupMembers extends Model
{
    protected $table = 'group_members';

    protected $fillable = [
        'school_id',
        'user_id'
    ];

    public static function addToGroups($user_id, $groups, $role) {
        $groupsForUser = GroupMembers::where('user_id', $user_id)->pluck('group_id')->toArray();
        if(count($groups) > 0) {
            $new_groups = array_diff($groups, $groupsForUser);
            $members = [];
            foreach($new_groups as $group) {
                $members[] = [
                    'group_id'  => $group,
                    'user_id'   => $user_id,
                    'user_role' => $role
                ];

                $updateGroup = Group::where('id', $group)->first();
                if($role == 'student') {
                    $nstudents = $updateGroup->nstudents;
                    $updateGroup->nstudents = $nstudents + 1;
                } else if($role == 'leader') {
                    $nleaders = $updateGroup->nleaders;
                    $updateGroup->nleaders = $nleaders + 1;
                }
                $updateGroup->save();
            }
            DB::table('group_members')->insert($members);

            $removedGroups = array_diff($groupsForUser, $groups);
            if(count($removedGroups) > 0) {
                GroupMembers::where('user_id', $user_id)->whereIn('group_id', $removedGroups)->delete();
                foreach($removedGroups as $group) {
                    $updateGroup = Group::where('id', $group)->first();
                    if($role == 'student') {
                        $nstudents = $updateGroup->nstudents;
                        $updateGroup->nstudents = $nstudents - 1;
                    } else if($role == 'leader') {
                        $nleaders = $updateGroup->nleaders;
                        $updateGroup->nleaders = $nleaders - 1;
                    }
                    $updateGroup->save();
                }
            }
        } else {
            GroupMembers::where('user_id', $user_id)->delete();
            if($role == 'student') {
                foreach($groupsForUser as $group) {
                    $updateGroup = Group::where('id', $group)->first();
                    $nstudents = $updateGroup->nstudents;
                    $updateGroup->nstudents = $nstudents - 1;
                    $updateGroup->save();
                }
            } else if($role == 'leader') {
                foreach($groupsForUser as $group) {
                    $updateGroup = Group::where('id', $group)->first();
                    $nleaders = $updateGroup->nleaders;
                    $updateGroup->nleaders = $nleaders - 1;
                    $updateGroup->save();
                }
            }
        }
    }
}
