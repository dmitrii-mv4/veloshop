<?php

use Illuminate\Support\Facades\Route;
use App\Modules\IBlock\Controllers\ApiController;

Route::get('/', [ApiController::class, 'index']);