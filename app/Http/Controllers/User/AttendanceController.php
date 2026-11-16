<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $attendance = $user->attendanceRecords()
            ->whereDate('date', today())
            ->first();

            if (!$attendance) {
                $status = '勤務外';
            } elseif ($attendance->clock_out) {
                $status = '退勤済';
            } else {
                $isOnBreak = $attendance->breakTimes()
                    ->whereNull('break_end')
                    ->exists();

                $status = $isOnBreak ? '休憩中' : '出勤中';
        }
                
            
        $formattedDate = now()->format('Y年n月j日');
        $formattedTime = now()->format('H:i');
        return view('user.attendance-register', compact(
            'user',
            'status',
            'formattedDate',
            'formattedTime'
        ));
    }
}
