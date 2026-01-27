<?php

use Illuminate\Support\Facades\Route;
use App\Admin\Controllers\Api\AppController;


Route::get('/', [AppController::class, 'index']);