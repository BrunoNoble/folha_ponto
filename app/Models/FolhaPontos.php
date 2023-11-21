<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;


class FolhaPontos extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }



    public function getHours()
    {
        // Convert time values to Carbon instances for proper calculation
        $breakExit = Carbon::parse($this->break_exit);

        $breakEntry = Carbon::parse($this->break_entry);
        $exitHour = Carbon::parse($this->exit_hour);
        $entryHour = Carbon::parse($this->entry_hour);

        // Calculate break duration in minutes
        $breakDuration = $breakExit->diffInMinutes($breakEntry);

        // Calculate working hours in minutes
        $workingHours = $exitHour->diffInMinutes($entryHour);

        // Subtract break duration from working hours
        $netWorkingHours = $workingHours - $breakDuration;

        // Convert net working hours back to hours and minutes
        $hours = floor($netWorkingHours / 60);
        $minutes = $netWorkingHours % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }
    public function getOverTime()
    {
        $total = Carbon::parse($this->getHours());
        if ($total->greaterThan(Carbon::parse('8:00'))) {
            $overTime = $total->subHour(8);
           //dd($overTime);
            return $overTime->format('H:i');
        }else return '00:00';


    }
}
