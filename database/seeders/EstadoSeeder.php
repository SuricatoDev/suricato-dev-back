<?php

namespace Database\Seeders;

use App\Models\Estado;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EstadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estados = [
            ['Acre', 'AC'],
            ['Alagoas', 'AL'],
            ['Amazonas', 'AM'],
            ['Amapá', 'AP'],
            ['Bahia', 'BA'],
            ['Ceará', 'CE'],
            ['Distrito Federal', 'DF'],
            ['Espírito Santo', 'ES'],
            ['Goiás', 'GO'],
            ['Maranhão', 'MA'],
            ['Minas Gerais', 'MG'],
            ['Mato Grosso do Sul', 'MS'],
            ['Mato Grosso', 'MT'],
            ['Pará', 'PA'],
            ['Paraíba', 'PB'],
            ['Pernambuco', 'PE'],
            ['Piauí', 'PI'],
            ['Paraná', 'PR'],
            ['Rio de Janeiro', 'RJ'],
            ['Rio Grande do Norte', 'RN'],
            ['Rondônia', 'RO'],
            ['Roraima', 'RR'],
            ['Rio Grande do Sul', 'RS'],
            ['Santa Catarina', 'SC'],
            ['Sergipe', 'SE'],
            ['São Paulo', 'SP'],
            ['Tocantins', 'TO'],
        ];

        foreach ($estados as $estado) {
            Estado::firstOrCreate([
                'nome' => $estado[0],
                'sigla' => $estado[1],
            ]);
        }
    }
}
