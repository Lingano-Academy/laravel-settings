<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

return new class extends Migration {
    public function up(): void
    {
        $tableName = Config::get('settings.table_name', 'settings');

        Schema::create($tableName, function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('key')->unique()->index();
            $table->text('value')->nullable();
            $table->jsonb('json_value')->nullable();
            $table->string('description')->nullable();
            $table->string('type')->default('string');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tableName = Config::get('settings.table_name', 'settings');
        Schema::dropIfExists($tableName);
    }
};