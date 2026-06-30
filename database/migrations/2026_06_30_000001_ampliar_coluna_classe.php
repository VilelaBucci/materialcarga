<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE materiais MODIFY classe VARCHAR(200) NULL');
        DB::statement('ALTER TABLE materiais_backup MODIFY classe VARCHAR(200) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE materiais MODIFY classe VARCHAR(50) NULL');
        DB::statement('ALTER TABLE materiais_backup MODIFY classe VARCHAR(50) NULL');
    }
};
