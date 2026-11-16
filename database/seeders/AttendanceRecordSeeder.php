<?php

namespace Database\Seeders;

//use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class AttendanceRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
            'user_id' => 1,
            'date' => '2026-11-16',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('attendance_records')->insert($param);
    }
}
