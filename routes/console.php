<?php

use Illuminate\Support\Facades\Schedule;

// Hitung ulang busyness_score + tandai target — setiap hari jam 03:00
Schedule::command('places:score --mark-targets --min-score=5')
    ->dailyAt('03:00')
    ->withoutOverlapping();

// Normalisasi kategori — setiap Senin jam 03:30
Schedule::command('places:normalize-categories')
    ->weeklyOn(1, '03:30')
    ->withoutOverlapping();
