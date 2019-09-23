<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Group extends Model
{
    protected $table = 'groups';

    protected $fillable = [
        'name',
        'school_id',
        'created_by'
    ];

    public static function createGroupData($data)
    {
        $group = new Group();
        $group->name = $data['group_name'];
        $group->school_id = $data['school_id'];
        $group->created_by = $data['created_by'];
        $group->nstudents = (isset($data['students']) && count($data['students'] > 0)) ? count($data['students']) : 0;
        $group->nleaders = (isset($data['leaders']) && count($data['leaders'] > 0)) ? count($data['leaders']) : 0;
        $group->save();

        if(isset($data['students']) && count($data['students'] > 0)) {
            $students = [];
            foreach($data['students'] as $user) {
                $students[] = [
                    'group_id'  => $group['id'],
                    'user_id'   => $user,
                    'user_role' => 'student'
                ];
            }
            DB::table('group_members')->insert($students);
        }

        if(isset($data['leaders']) && count($data['leaders'] > 0)) {
            $leaders = [];
            foreach($data['leaders'] as $user) {
                $leaders[] = [
                    'group_id'  => $group['id'],
                    'user_id'   => $user,
                    'user_role' => 'leader'
                ];
            }
            DB::table('group_members')->insert($leaders);
        }

        return ['success' => 1, 'group' => $group];
    }

    public static function updateGroupData($data)
    {
        $group = Group::where('id', $data['group_id'])->first();
        if(isset($data['group_name'])) {
            $group->name = $data['group_name'];
        }
        
        $members_students = DB::table('group_members')
                                ->where('group_id', $data['group_id'])
                                ->where('user_role', 'student')
                                ->pluck('user_id')
                                ->toArray();
                                
        $nstudents = $group->nstudents;
        if(isset($data['students']) && count($data['students'] > 0)) {
            $students = [];
            $new_students = array_diff($data['students'], $members_students);
            foreach($new_students as $user) {
                $students[] = [
                    'group_id'  => $data['group_id'],
                    'user_id'   => $user,
                    'user_role' => 'student'
                ];
                $nstudents++;
            }
            DB::table('group_members')->insert($students);

            $remove_students = array_diff($members_students, $data['students']);
            if(count($remove_students) > 0) {
                GroupMembers::whereIn('user_id', $remove_students)->delete();
                $nstudents -= count($remove_students);
            }
        } else {
            GroupMembers::where('group_id', $data['group_id'])->where('user_role', 'student')->delete();
            $nstudents = 0;
        }

        $members_leaders = DB::table('group_members')
                                ->where('group_id', $data['group_id'])
                                ->where('user_role', 'leader')
                                ->pluck('user_id')
                                ->toArray();

        $nleaders = $group->nleaders;
        if(isset($data['leaders']) && count($data['leaders'] > 0)) {
            $leaders = [];
            $new_leaders = array_diff($data['leaders'], $members_leaders);
            foreach($new_leaders as $user) {
                $leaders[] = [
                    'group_id'  => $data['group_id'],
                    'user_id'   => $user,
                    'user_role' => 'leader'
                ];
                $nleaders++;
            }
            DB::table('group_members')->insert($leaders);

            $remove_leaders = array_diff($members_leaders, $data['leaders']);
            if(count($remove_leaders) > 0) {
                GroupMembers::whereIn('user_id', $remove_leaders)->delete();
                $nleaders -= count($remove_leaders);
            }
        } else {
            GroupMembers::where('group_id', $data['group_id'])->where('user_role', 'leader')->delete();
            $nleaders = 0;
        }

        $group->nstudents = $nstudents;
        $group->nleaders = $nleaders;
        $group->save();

        return ['success' => 1];
    }
}
