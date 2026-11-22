<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AttendanceClockTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_user_can_clock_in_only_once_per_day(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->post('/attendance',[
            'action' => 'clock_in',
        ]);

        $this->post('/attendance',[
            'action' => 'clock_in',
        ]);

        $this->assertDatabaseCount('attendance_records', 1);
    }
    //ここまで出勤テスト

    //ここから退勤テスト
    public function test_user_can_clock_out_only_once_per_day(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->post('/attendance',[
            'action' => 'clock_in',
        ]);

        $this->post('/attendance',[
            'action' => 'clock_out',
        ]);
        
        $this->assertDatabaseHas('attendance_records',[
            'user_id' => $user->id,
            'date' => today()->toDateString(),
        ]);

        
        
        $attendance = \App\Models\AttendanceRecord::where('user_id' , $user->id)->first();

        $this->assertNotNull($attendance->clock_out);

        $firstClockout = $attendance->clock_out;
//テスト上の時間を進める
        $this->travel(1)->minutes();
//2回目の退勤
        $this->post('/attendance',[
            'action' => 'clock_out',
        ]);
        
        $attendance->refresh();

        $this->assertEquals($firstClockout, $attendance->clock_out);

    }
//ここまで退勤テスト

//ここから休憩テスト

    public function test_user_can_take_multiple_breaks_per_day(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        //出勤
        $this->post('/attendance',[
            'action' => 'clock_in',
        ]);

        //１回目の休憩

        $this->post('/attendance',[
            'action' => 'break_in',
        ]);

        //1回目の休憩から戻る

        $this->post('/attendance',[
            'action' => 'break_out'
        ]);

        //２回目の休憩

        $this->post('/attendance',[
            'action' => 'break_in',
        ]);

        //２回目の休憩から戻る

        $this->post('/attendance',[
            'action' => 'break_out',
        ]);

        //休憩が２件記録されて保存されているか確認する
        $this->assertDatabaseCount('break_times', 2);
    }
}
//AttendanceClockTestクラス全体の終わり

