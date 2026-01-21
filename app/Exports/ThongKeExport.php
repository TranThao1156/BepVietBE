<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ThongKeExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        // 1. Chuẩn bị dữ liệu phần TỔNG QUAN
        $stats = $this->data['thong_ke'] ?? $this->data; // Tùy cấu trúc trả về của Service

        $userMoi = $stats['users']['new_today'] ?? 0;

        // Nếu giá trị là 0 thì gán cứng chuỗi "0" để Excel không ẩn đi
        if ($userMoi === 0 || $userMoi === '0') {
            $userMoi = "0";
        }

        $exportData = [
            ['TỔNG QUAN HỆ THỐNG', ''], // Dòng tiêu đề
            ['Tổng người dùng', $stats['users']['total'] ?? 0],
            ['Người dùng mới hôm nay', $userMoi],
            ['Tổng công thức', $stats['recipes']['total'] ?? 0],
            ['Công thức chờ duyệt', $stats['recipes']['pending'] ?? 0],
            ['Tổng lượt xem', $stats['views']['total'] ?? 0],
            ['', ''], // Dòng trống ngăn cách
            ['CHI TIẾT BIỂU ĐỒ (7 NGÀY QUA)', ''], // Tiêu đề phần 2
            ['Ngày', 'Số lượng đăng ký'] // Header bảng con
        ];

        // 2. Chuẩn bị dữ liệu phần BIỂU ĐỒ
        if (isset($this->data['chart'])) {
            foreach ($this->data['chart'] as $item) {

                $count = $item['count'] ?? 0;

                // --- THÊM ĐOẠN NÀY: Xử lý số 0 cho từng dòng ---
                if ($count === 0 || $count === '0') {
                    $count = "0"; // Ép sang chuỗi để Excel hiển thị
                }

                $exportData[] = [
                    $item['label'] . ' (' . $item['date'] . ')',
                    $count
                ];
            }
        }

        return $exportData;
    }

    public function headings(): array
    {
        return [
            'Nội dung thống kê',
            'Số liệu',
        ];
    }

    // Style cho file Excel đẹp hơn (In đậm tiêu đề)
    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true, 'size' => 14]], // Dòng 1 in đậm to
            8    => ['font' => ['bold' => true]], // Dòng tiêu đề biểu đồ
            9    => ['font' => ['bold' => true, 'color' => ['rgb' => 'FF642F']]], // Header bảng con
        ];
    }
}
