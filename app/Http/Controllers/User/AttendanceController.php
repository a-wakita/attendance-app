<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceRecord;

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
                
        $user->attendance_status = $status;
        $formattedDate = now()->format('Y年n月j日');
        $formattedTime = now()->format('H:i');
        return view('user.attendance-register', compact(
            'user',
            'status',
            'formattedDate',
            'formattedTime'
        ));
    }

    public function store(Request $request)
    {
        $action = $request->input('action');

        $attendance = AttendanceRecord::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->first();

        if ($action === 'clock_in' && !$attendance) {
            AttendanceRecord::create([
                'user_id' => auth()->id(),
                'date' => today()->toDateString(),
                'clock_in' => now()->format('H:i:s'),
            ]);
        }

        if ($action === 'break_in' && $attendance && !$attendance->clock_out) {
            $isOnBreak = $attendance->breakTimes()
                ->whereNull('break_end')
                ->exists();

            if (!$isOnBreak) {
                $attendance->breakTimes()->create([
                    'break_start' => now()->format('H:i:s'),
                ]);
            }
        }

        if ($action === 'break_out' && $attendance && !$attendance->clock_out) {
            $break = $attendance->breakTimes()
                ->whereNull('break_end')
                ->latest('id')
                ->first();

            if ($break) {
                $break->update([
                    'break_end' => now()->format('H:i:s'),
                ]);
            }
        }
        if ($action === 'clock_out' && $attendance && !$attendance->clock_out) {
            $isOnBreak = $attendance->breakTimes()
                ->whereNull('break_end')
                ->exists();

            if(!$isOnBreak) {
                $attendance->update([
                    'clock_out' => now()->format('H:i:s'),
                ]);
            }
        }

        return redirect('/attendance');
    }
}
