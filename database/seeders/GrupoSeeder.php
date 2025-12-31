<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GrupoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $grupos = [
            ['número' => 301],
            ['número' => 201],
            ['número' => 401],
            ['número' => 501],
            ['número' => 601],
            ['número' => 402],
        ];

        $id = 1;
        foreach ($grupos as $grupo) {
            DB::table('grupos')->updateOrInsert(
                ['número' => $grupo['número']],
                [
                    'id' => $id,
                    'número' => $grupo['número'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $id++;
        }

        $this->command->info(count($grupos) . ' grupos creados exitosamente.');
    }
}