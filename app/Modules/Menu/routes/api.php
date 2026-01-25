<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Menu\Controllers\ApiController;

Route::get('/', [ApiController::class, 'index']);