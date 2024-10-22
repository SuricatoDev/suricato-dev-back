<?php

namespace App\Http\Controllers;

use App\Models\PessoaFisica;
use App\Models\PessoaJuridica;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        // Validações básicas e específicas para PF ou PJ
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'tipo' => 'required|in:PF,PJ', // Validação para tipo de usuário (PF ou PJ)

            // Validações gerais
            'endereco' => 'required',
            'cep' => 'required',
            'cidade_id' => 'required|exists:cidades,id',
            'telefone' => 'required',

            // Validações específicas para PF
            'nome' => 'required_if:tipo,PF',
            'cpf' => 'required_if:tipo,PF|unique:pessoa_fisica,cpf',
            'rg' => 'required_if:tipo,PF',
            'data_nascimento' => 'required_if:tipo,PF|date',

            // Validações específicas para PJ
            'razao_social' => 'required_if:tipo,PJ',
            'cnpj' => 'required_if:tipo,PJ|unique:pessoa_juridica,cnpj',
            'inscricao_estadual' => 'nullable',
            'inscricao_municipal' => 'nullable',
        ]);

        // Cria o usuário básico com todos os campos necessários
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'endereco' => $request->endereco,
            'cep' => $request->cep,
            'cidade_id' => $request->cidade_id,
            'telefone' => $request->telefone,
            'tipo' => $request->tipo,
            'ativo' => true,
        ]);

        // Verifica o tipo de usuário para criar o registro correspondente
        if ($request->tipo == 'PF') {
            // Criação de pessoa física
            PessoaFisica::create([
                'user_id' => $user->id,
                'nome' => $request->nome,
                'cpf' => $request->cpf,
                'rg' => $request->rg,
                'data_nascimento' => $request->data_nascimento,
            ]);
        } elseif ($request->tipo == 'PJ') {
            // Criação de pessoa jurídica
            PessoaJuridica::create([
                'user_id' => $user->id,
                'razao_social' => $request->razao_social,
                'cnpj' => $request->cnpj,
                'inscricao_estadual' => $request->inscricao_estadual,
                'inscricao_municipal' => $request->inscricao_municipal,
            ]);
        }

        return response()->json([
            'message' => 'Usuário registrado com sucesso!',
            'user' => $user,
        ], 201);
    }
}
