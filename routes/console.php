<?php


use Illuminate\Support\Facades\Schedule;

Schedule::command('app:procesar-tracking-aeropost')->everyFiveMinutes();
