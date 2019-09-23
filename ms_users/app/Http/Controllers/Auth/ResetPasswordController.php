<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use App\User;
use App\RoleUser;
use Hash;
use App\Http\Requests\Users\ChangePasswordForUserRequest;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function askResetPassword(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email'     => 'required|email',
        ]);

        if ($validator->fails()) {
            return ['error'=>1, 'errors' => $validator->errors()->all()];
        }
        $email = $request->get('email');

        $user = User::where('email',$email)->first();
        $hash = md5(time().$email);
        $user->reset_password_hash  = $hash;
        $user->reset_password_asked = date('Y-m-d H:i:s');
        $user->reset_password_valid = date('Y-m-d H:i:s',strtotime("+7 day", time()));
        $user->save();

        $data['email'] = $email;

        $role = RoleUser::where('user_id', $user->id)->first();

        return ['success'=>1, 'data'=> ['hash'=> $user->reset_password_hash]];
    }

    public function checkHashForResetPassword($email,$hash)
    {
        $user = User::where('email',$email)->where('reset_password_hash',$hash)->first();
        if(!$user)
           return ['error'=>1, 'errors' => [['Wrong reset password hash']]];

        if($user->reset_password_valid < date('Y-m-d H:i:s'))
           return ['error'=>1, 'errors' => [['Reset password hash expiered']]];

        return ['success'=>1];
    }

    public function storeNewPassword(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email'     => 'required|email',
            'password'  => 'required|confirmed'
        ]);

        if ($validator->fails()) {
            return ['error'=>1, 'errors' => $validator->errors()->all()];
        }

        $user = User::where('email',$request->get('email'))->first();
        $user->reset_password_hash = md5($user->id);
        $user->reset_password_valid = date('Y-m-d H:i:s');
        $user->password = Hash::make($request->get('password')); 
        $user->save();
        return ['success'=>1];
    }

    public function resetForUser(Request $request)
    {
        if($request->get('autogenerate') == 1)
            $password = Hash::make(str_random(8));
        else
            $password = $request->get('password');

        $user = User::where('id', $request->get('user_id'))->first();
        $user->password = Hash::make($password);
        $user->save();

        $data['code'] = $password;
        $data['email'] = $user->email;
        \Mail::send('emails.resetPassForUser', $data, function ($m) use ($data) {
            $m->from('no-reply@tutella.com', '');
            $m->to($data['email'], '')->subject('Your password has been changed!');
        });

        return ['success'=>1];
    }

    public function changePassword(Request $request)
    {
        $user = User::where('id', $request->get('user_id'))->first();
        $user->password = Hash::make($request->get('password'));
        $user->save();

        return ['success'=>1];
    }
}
