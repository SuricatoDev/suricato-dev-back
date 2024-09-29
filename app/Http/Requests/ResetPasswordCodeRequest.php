<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ResetPasswordCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Manipular falha de validação e retornar uma resposta JSON com os erros de validação
     * 
     * @param Validator $validator O objeto de validação que falhou
     * @throws HttpResponseException
     */

     protected function failedValidation(Validator $validator)
     {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'erros' => $validator->errors(),

        ], 422));
     }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'code' => ['required'],
            'password' => ['required', 'min:6'],
        ];
    }
}
