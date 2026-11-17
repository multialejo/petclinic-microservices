<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Datos iniciales de ms-customers, tomados de spring-petclinic/src/main/resources/db/mysql/data.sql
 * conservando los ids originales (docs/fase2 §7). Esperado: 6 types, 10 owners, 13 pets.
 */
class CustomersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('types')->insertOrIgnore([
            ['id' => 1, 'name' => 'cat'],
            ['id' => 2, 'name' => 'dog'],
            ['id' => 3, 'name' => 'lizard'],
            ['id' => 4, 'name' => 'snake'],
            ['id' => 5, 'name' => 'bird'],
            ['id' => 6, 'name' => 'hamster'],
        ]);

        $owners = [
            [1, 'George', 'Franklin', '110 W. Liberty St.', 'Madison', '6085551023'],
            [2, 'Betty', 'Davis', '638 Cardinal Ave.', 'Sun Prairie', '6085551749'],
            [3, 'Eduardo', 'Rodriquez', '2693 Commerce St.', 'McFarland', '6085558763'],
            [4, 'Harold', 'Davis', '563 Friendly St.', 'Windsor', '6085553198'],
            [5, 'Peter', 'McTavish', '2387 S. Fair Way', 'Madison', '6085552765'],
            [6, 'Jean', 'Coleman', '105 N. Lake St.', 'Monona', '6085552654'],
            [7, 'Jeff', 'Black', '1450 Oak Blvd.', 'Monona', '6085555387'],
            [8, 'Maria', 'Escobito', '345 Maple St.', 'Madison', '6085557683'],
            [9, 'David', 'Schroeder', '2749 Blackhawk Trail', 'Madison', '6085559435'],
            [10, 'Carlos', 'Estaban', '2335 Independence La.', 'Waunakee', '6085555487'],
        ];
        DB::table('owners')->insertOrIgnore(array_map(fn ($o) => array_combine(
            ['id', 'first_name', 'last_name', 'address', 'city', 'telephone'], $o
        ), $owners));

        $pets = [
            [1, 'Leo', '2000-09-07', 1, 1],
            [2, 'Basil', '2002-08-06', 6, 2],
            [3, 'Rosy', '2001-04-17', 2, 3],
            [4, 'Jewel', '2000-03-07', 2, 3],
            [5, 'Iggy', '2000-11-30', 3, 4],
            [6, 'George', '2000-01-20', 4, 5],
            [7, 'Samantha', '1995-09-04', 1, 6],
            [8, 'Max', '1995-09-04', 1, 6],
            [9, 'Lucky', '1999-08-06', 5, 7],
            [10, 'Mulligan', '1997-02-24', 2, 8],
            [11, 'Freddy', '2000-03-09', 5, 9],
            [12, 'Lucky', '2000-06-24', 2, 10],
            [13, 'Sly', '2002-06-08', 1, 10],
        ];
        DB::table('pets')->insertOrIgnore(array_map(fn ($p) => array_combine(
            ['id', 'name', 'birth_date', 'type_id', 'owner_id'], $p
        ), $pets));
    }
}
