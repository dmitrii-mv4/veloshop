<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Page\Controllers\ApiController;

Route::get('/', [ApiController::class, 'index']);