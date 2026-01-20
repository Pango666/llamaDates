<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('appointments:mark-non-attendance --chunk=500')
    ->everyMinute()
    ->withoutOverlapping();
