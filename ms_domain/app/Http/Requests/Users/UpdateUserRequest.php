<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'first_name'    =>  'required|string|max:255',
            'last_name'     =>  'required|string|max:255',
            'username'      =>  'required|string|max:255'
        ];

        $role = $this->get('role');

        $password = $this->get('password');
        if(isset($password)) {
            $rules['password'] = 'required|confirmed';
        }

        return $rules;
    }
}
