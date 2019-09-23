<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class InviteUserRequest extends FormRequest
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
        return [
            'first_name'        => 'required|max:255',
            'last_name'         => 'required|max:255',
            'username'          => 'required',
            'role'              => 'required',
        ];
    }
}
