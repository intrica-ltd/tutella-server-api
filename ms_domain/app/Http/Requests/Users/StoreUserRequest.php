<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'email'         =>  'required|email',
            'role'          =>  'required|string|max:255',
            'image'        =>  'nullable',
        ];

        $role = $this->get('role');
        
        return $rules;
    }
}
