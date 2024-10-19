<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cidade;
use App\Models\Estado;

class CidadeController extends Controller
{
    public function __construct(Request $request, Estado $estado, Cidade $cidade)
    {
        $this->request  = $request;
        $this->estado   = $estado;
        $this->cidade   = $cidade;
    }

    public function comboCidades($idEstado)
    {
        $estado = $this->estado->find($idEstado);
        $cidades = $estado->cidades;

        return response()->json($cidades);
    }
}
