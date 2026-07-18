<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'portfolio')
    ->name('portfolio.home');
