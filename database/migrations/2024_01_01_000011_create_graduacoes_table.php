<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('graduacoes', function (Blueprint $table) {
            $table->string('codigo', 10)->primary();
            $table->string('nome', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('graduacoes');
    }
};
