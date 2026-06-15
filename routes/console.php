<?php
use Illuminate\Support\Facades\Schedule;

Schedule::command('devices:sync-traffic')
    ->everyMinute()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/devices-sync-traffic.log'));
