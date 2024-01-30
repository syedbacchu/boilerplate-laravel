<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileUpdateApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $userId = $this->edit_id ? $this->edit_id : Auth::id();
        $rule = [
            'name' => 'required|string|max:255',
            'email' => ['required','email',Rule::unique('users')->ignore($userId,'id')],
        ];
        
        if($this->photo) {
            $rule['photo'] = 'image|mimes:jpeg,png,jpg|max:2048';
        }
        if($this->phone) {
            $rule['phone'] = ['numeric',Rule::unique('users')->ignore($userId,'id')];
        }
        return $rule;
    }

    public function messages()
    {
        return [
            'photo.required' => __("Photo is required"),
            'photo.image' => __("Invalid image file"),
            'photo.mimes' => __("Supported image file are jpeg,png,jpg"),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->header('accept') == "application/json") {
            $errors = [];
            if ($validator->fails()) {
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
            }
            $json = [
                'success'=>false,
                'message' => $errors[0],
            ];
            $response = new JsonResponse($json, 200);

            throw (new ValidationException($validator, $response))->errorBag($this->errorBag)->redirectTo($this->getRedirectUrl());
        } else {
            throw (new ValidationException($validator))
                ->errorBag($this->errorBag)
                ->redirectTo($this->getRedirectUrl());
        }
    }
}
