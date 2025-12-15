<?php

namespace App\Modules\ModuleGenerator\Controllers;

use App\Core\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\ModuleGenerator\Models\ModuleGeneratorModel;
use App\Modules\ModuleGenerator\Requests\CreateRequest;

class ModuleGeneratorController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $modules = ModuleGeneratorModel::paginate(15);

        return view('module_generator::index', compact('modules'));
    }

    public function create()
    {
        return view('module_generator::create');
    }

    public function store(CreateRequest $request)
    {
        $validated = $request->validated();
        
        dd($validated);
    }
}