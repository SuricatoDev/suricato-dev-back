<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ForgotPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Retorna os erros de validação
     * 
     * @param Validator $validator O objeto de validação
     * 
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
     * Retorna as regras de validação para os dados do usuário
     * @return array<string, \Illuminate\Contracts\ValidationRule|array|<mixed>|string>
     */

     public function rules(): array
     {
        return [
            'email' => 'required|email',
        ];
     }

     /**
      * Retorna as menssagens personalizadas para as regras de validação
      * @return array
      */

      public function messages(): array
      {
        return [
            'email.required' => 'O campo de e-mail é obrigatório',
            'email.email' => 'Formato de e-mail inválido',
        ];
      }
}
