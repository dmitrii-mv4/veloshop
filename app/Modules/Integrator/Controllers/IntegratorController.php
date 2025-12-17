<?php

namespace App\Modules\Integrator\Controllers;

use App\Core\Controllers\Controller;
use App\Modules\ModuleGenerator\Models\Module;
use App\Core\Services\Database\DatabaseColumnTypeService;
use Illuminate\Http\Request;

class IntegratorController extends Controller
{
    public function __construct(DatabaseColumnTypeService $columnTypeService)
    {
        $this->middleware('admin');
        $this->columnTypeService = $columnTypeService;
    }

    public function index()
    {
        return view('integrator::index');
    }

    public function create()
    {
        $modules = Module::get();

        // Вытаскиваем SQL столбцы
        $sqlColumn = $this->columnTypeService->getColumnTypesDetailed('news');

        //dd($sqlColumn);

        return view('integrator::create', compact('modules'));
    }

    public function store()
    {
        
    }
}