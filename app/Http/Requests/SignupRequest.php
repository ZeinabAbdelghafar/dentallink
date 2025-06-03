<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

class SignupRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => [
                'required',
                'email',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('email', $this->email);
                }),
            ],
            'username' => [
                'required',
                'min:3',
                'alpha_num',
                'regex:/^\S*$/u',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('username', $this->username);
                }),
            ],
            'password' => 'required|min:6',
            'gender' => 'required|in:male,female',
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'Email is already registered',
            'username.unique' => 'Username is already registered',
            'username.regex' => 'Username must not contain spaces',
        ];
    }
}