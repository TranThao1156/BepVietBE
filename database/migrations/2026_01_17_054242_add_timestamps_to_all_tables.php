<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();

        foreach ($tables as $table) {
            $tableName = $table->{"Tables_in_$dbName"};

            // Bỏ qua bảng migrations
            if ($tableName === 'migrations') continue;

            Schema::table($tableName, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                if (!Schema::hasColumn($table->getTable(), 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('all_tables', function (Blueprint $table) {
            //
        });
    }
};
