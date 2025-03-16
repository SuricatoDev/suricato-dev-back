<?php

namespace Database\Seeders;

use App\Models\Evento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Inserindo dados na tabela 'eventos'
        $eventos = [
            'Cafés',
            'Carnaval',
            'Cidades',
            'Chácaras',
            'Fazendas',
            'Festivais',
            'Jogos',
            'Montanhas',
            'Museus',
            'Parques Aquáticos',
            'Parques de Diversão',
            'Parques Naturais',
            'Praias',
            'Resortes',
            'Shows',
            'Trilhas',
        ];

        foreach ($eventos as $evento) {
            DB::table('eventos')->insert([
                'descricao' => $evento,
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
