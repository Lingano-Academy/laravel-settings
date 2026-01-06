<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

return new class extends Migration {
    public function up(): void
    {
        $tableName = Config::get('vocabia_settings.table_name', 'settings');

        Schema::create($tableName, function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->string('group')
                ->default('general')
                ->index()
                ->comment('Category of the setting (e.g., payment, system, notification)');

            $table->string('key')
                ->unique()
                ->comment('Unique key for retrieving the setting');

            $table->text('value')
                ->nullable()
                ->comment('Stores simple values (string, int, bool, encrypted)');

            $table->jsonb('json_value')
                ->nullable()
                ->comment('Stores structured data (array, object) when type is json/array');

            $table->string('type')
                ->default('string')
                ->comment('Data type hint: string, integer, boolean, array, encrypted');

            $table->string('description')
                ->nullable()
                ->comment('Human readable description for Admin Panel UI');

            $table->boolean('is_locked')
                ->default(false)
                ->comment('If true, this setting cannot be deleted or modified by users');

            $table->timestamps();

            $table->unique(['group', 'key']);
        });
    }

    public function down(): void
    {
        $tableName = Config::get('settings.table_name', 'settings');
        Schema::dropIfExists($tableName);
    }
};