<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\StudentPromotionService;

class AutoPromoteStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:auto-promote {--min-score=50 : เกณฑ์คะแนนความประพฤติขั้นต่ำสำหรับผ่าน}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ประมวลผลเลื่อนชั้นปีการศึกษาของนักเรียนอัตโนมัติ โดยใช้คะแนนความประพฤติเป็นเกณฑ์หลัก';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $minScore = (int) $this->option('min-score');
        $this->info("กำลังประมวลผลเลื่อนชั้นนักเรียนอัตโนมัติ (เกณฑ์คะแนนพฤติกรรม >= {$minScore} คะแนน)...");

        $result = StudentPromotionService::autoPromoteByBehaviorScore($minScore);

        if (!$result['success']) {
            $this->error("เกิดข้อผิดพลาด: " . ($result['error'] ?? 'ไม่ทราบสาเหตุ'));
            return Command::FAILURE;
        }

        $this->info("ประมวลผลสำเร็จเรียบร้อยแล้ว!");
        $this->table(
            ['รายการ', 'จำนวน (คน)'],
            [
                ['เลื่อนชั้น (ม.1 - ม.5)', $result['promoted_count']],
                ['สำเร็จการศึกษา (ม.6)', $result['graduated_count']],
                ['ซ้ำชั้น (คะแนนไม่ผ่านเกณฑ์)', $result['retained_count']],
                ['รวมนักเรียนทั้งหมดที่ประมวลผล', $result['total']],
            ]
        );

        return Command::SUCCESS;
    }
}
