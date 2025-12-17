<?php

namespace App\Modules\Integrator\Controllers;

use App\Core\Controllers\Controller;

class IntegratorController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        dd('IntegratorController');
    }
}