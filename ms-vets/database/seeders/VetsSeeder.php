<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Datos iniciales de ms-vets, tomados de spring-petclinic/src/main/resources/db/mysql/data.sql
 * conservando los ids originales. Esperado: 6 vets, 3 specialties, 5 vet_specialties.
 */
class VetsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('vets')->insertOrIgnore([
            ['id' => 1, 'first_name' => 'James', 'last_name' => 'Carter'],
            ['id' => 2, 'first_name' => 'Helen', 'last_name' => 'Leary'],
            ['id' => 3, 'first_name' => 'Linda', 'last_name' => 'Douglas'],
            ['id' => 4, 'first_name' => 'Rafael', 'last_name' => 'Ortega'],
            ['id' => 5, 'first_name' => 'Henry', 'last_name' => 'Stevens'],
            ['id' => 6, 'first_name' => 'Sharon', 'last_name' => 'Jenkins'],
        ]);

        DB::table('specialties')->insertOrIgnore([
            ['id' => 1, 'name' => 'radiology'],
            ['id' => 2, 'name' => 'surgery'],
            ['id' => 3, 'name' => 'dentistry'],
        ]);

        DB::table('vet_specialties')->insertOrIgnore([
            ['vet_id' => 2, 'specialty_id' => 1],
            ['vet_id' => 3, 'specialty_id' => 2],
            ['vet_id' => 3, 'specialty_id' => 3],
            ['vet_id' => 4, 'specialty_id' => 2],
            ['vet_id' => 5, 'specialty_id' => 1],
        ]);
    }
}
