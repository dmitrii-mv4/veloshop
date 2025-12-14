<?php

namespace App\Modules\InfoBlock\Controllers;

use App\Core\Controllers\Controller;
use App\Modules\InfoBlock\Models\InfoBlock;

class InfoBlockController extends Controller
{
    public function index()
    {
        $items = InfoBlock::latest()->get();

        return view('info_block::index', compact('items'));
    }

    public function create()
    {
        return view('info_block::create');
    }

    public function store(InfoBlocCreateRequest $request)
    {
        $validated = $request->validated();

        dd('store');
        //return view('info_block::index');
    }
}