<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('congthuc', function (Blueprint $table) {
        // Thêm cột lưu số sao trung bình, mặc định là 0
        $table->float('TrungBinhSao')->default(0)->after('MoTa'); 
    });
}

public function down()
{
    Schema::table('congthuc', function (Blueprint $table) {
        $table->dropColumn('TrungBinhSao');
    });
}
};
