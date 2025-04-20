<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SuporteRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta solicitação.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'titulo'    => 'required|string|max:255',
            'descricao' => 'required|string|min:10',
        ];
    }

    public function messages()
    {
        return [
            'titulo.required'    => 'O título é obrigatório.',
            'descricao.required' => 'A descrição do problema é obrigatória.',
            'descricao.min'      => 'A descrição deve ter no mínimo 10 caracteres.',
        ];
    }
}
