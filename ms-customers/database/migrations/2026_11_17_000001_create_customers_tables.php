<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Esquema de ms-customers (bounded context Clientes). Paridad con
 * spring-petclinic/src/main/resources/db/mysql/schema.sql: sin timestamps,
 * FK solo dentro del propio dominio y UNIQUE(owner_id, name) en pets (docs/fase2 §2).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 80)->index();
        });

        Schema::create('owners', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name', 30);
            $table->string('last_name', 30)->index();
            $table->string('address', 255);
            $table->string('city', 80);
            $table->string('telephone', 20);
        });

        Schema::create('pets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 30)->index();
            $table->date('birth_date');
            $table->unsignedInteger('type_id');
            $table->unsignedInteger('owner_id');
            $table->foreign('type_id')->references('id')->on('types');
            $table->foreign('owner_id')->references('id')->on('owners');
            $table->unique(['owner_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pets');
        Schema::dropIfExists('owners');
        Schema::dropIfExists('types');
    }
};
