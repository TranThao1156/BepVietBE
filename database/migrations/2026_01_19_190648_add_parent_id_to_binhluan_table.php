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
    Schema::table('binhluan', function (Blueprint $table) {
        // Thêm cột Parent_ID kiểu số nguyên lớn, cho phép NULL
        // unsignedBigInteger để khớp với kiểu dữ liệu của khóa chính Ma_BL
        $table->integer('Parent_ID')->nullable()->after('Ma_ND');

        // Tạo liên kết khóa ngoại (Foreign Key) trỏ ngược lại chính bảng binhluan
        // Khi xóa bình luận cha, các bình luận con (reply) sẽ tự động bị xóa theo (cascade)
        $table->foreign('Parent_ID')->references('Ma_BL')->on('binhluan')->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('binhluan', function (Blueprint $table) {
        $table->dropForeign(['Parent_ID']);
        $table->dropColumn('Parent_ID');
    });
}
};
