<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FolhaPontosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = 1; // User ID for whom you want to generate data
        $startDate = now()->startOfMonth()->setDate(2023, 10, 20);
        $endDate = now()->endOfMonth()->setDate(2023, 11, 20);

        $currentDate = $startDate;

        while ($currentDate <= $endDate) {
            if(!Carbon::parse($currentDate)->isWeekend())
            {
                DB::table('folha_pontos')->insert([
                    'user_id' => $userId,
                    'entry_date' => $currentDate,
                    'exit_date' => $currentDate,
                    'entry_hour' => $this->generateRandomTime(8, 9),
                    'exit_hour' => $this->generateRandomTime(17, 18),
                    'break_entry' => '12:00:00', // Fixed break entry time
                    'break_exit' => '13:00:00', // Random break exit time between 12:00 and 13:00
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);


            }else
            {
                DB::table('folha_pontos')->insert([
                    'user_id' => $userId,
                    'entry_date' => $currentDate,
                    'exit_date' => $currentDate,
                    'entry_hour' => "00:00:00",
                    'exit_hour' => "00:00:00",
                    'break_entry' => "00:00:00",
                    'break_exit' => "00:00:00",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $currentDate->addDay(); // Move to the next day
        }
    }

    /**
     * Generate a random time within the specified hour range.
     *
     * @param int $startHour
     * @param int $endHour
     * @return string
     */
    private function generateRandomTime($startHour, $endHour)
    {
        $hour = rand($startHour, $endHour);
        $minute = rand(0, 12);

        return sprintf('%02d:%02d:00', $hour, $minute);
    }
}
