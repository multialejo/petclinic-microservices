<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Datos iniciales de ms-visits, tomados de spring-petclinic/src/main/resources/db/mysql/data.sql
 * conservando los ids originales. Esperado: 4 visits (pet_id 7, 8, 8, 7).
 * No se valida contra ms-customers: los pet_id provienen del mismo script que CustomersSeeder.
 */
class VisitsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('visits')->insertOrIgnore([
            ['id' => 1, 'pet_id' => 7, 'visit_date' => '2010-03-04', 'description' => 'rabies shot'],
            ['id' => 2, 'pet_id' => 8, 'visit_date' => '2011-03-04', 'description' => 'rabies shot'],
            ['id' => 3, 'pet_id' => 8, 'visit_date' => '2009-06-04', 'description' => 'neutered'],
            ['id' => 4, 'pet_id' => 7, 'visit_date' => '2008-09-04', 'description' => 'spayed'],
        ]);
    }
}
