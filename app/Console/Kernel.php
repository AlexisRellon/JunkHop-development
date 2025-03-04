<?php
protected function schedule(Schedule $schedule)
{
    $schedule->command('dashboard:update-statistics')->dailyAt('00:00');
}
