<?php
use Illuminate\Support\Facades\Schedule;

Schedule::command('devices:sync-traffic')
    ->everyMinute()
    ->withoutOverlapping();
