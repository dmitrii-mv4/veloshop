<?php

namespace App\Modules\Page\Controllers;

use App\Core\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Modules\Page\Models\Page;
use App\Modules\Page\Requests\PageCreateRequest;
use App\Modules\Page\Requests\PageEditRequest;

/**
 * Контроллер для редактирования страниц на front-end части
 * 
 */

class PageController extends Controller
{
    public function __construct()
    {
        // Проверяем что у пользователя роль admin
        $this->middleware('admin');
    }

    public function index()
    {
        $pages = Page::latest()->get();

        return view('page::index', compact('pages'));
    }

    public function create()
    {
        return view('page::create');
    }

    public function store(PageCreateRequest $request)
    {
        try {
            $validated = $request->validated();

            Page::create($validated);

            return redirect()->route('admin.page.index')->with('Страница создана.');

        } catch (\Exception $e) {
            DB::rollBack();
        
            \Log::error('Page creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except('_token', 'password', 'api_token')
            ]);
            
            return back()
                ->withInput()
                ->with('error', [
                    'title' => 'Ошибка создания страницы',
                    'message' => 'Произошла ошибка при сохранении страницы. Пожалуйста, попробуйте снова или обратитесь к администратору.',
                    'technical' => config('app.debug') ? $e->getMessage() : null
                ]);
        }
    }

    public function edit(Page $page)
    {
        return view('page::edit', compact('page'));
    }

    public function update(Page $page, PageEditRequest $request)
    {
        try {
            $validated = $request->validated();

            $page->update($validated);

            return redirect()->route('admin.page.index')->with('Страница обновлена.');

        } catch (\Exception $e) {
            DB::rollBack();
        
            // Логирование ошибки
            \Log::error('Page update failed', [
                'page_id' => $page->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except('_token', 'password', 'api_token')
            ]);
            
            // Возврат с ошибкой
            return back()
                ->withInput()
                ->with('error', [
                    'title' => 'Ошибка обновления страницы',
                    'message' => 'Произошла ошибка при обновлении страницы. Пожалуйста, проверьте данные и попробуйте снова.',
                    'technical' => config('app.debug') ? $e->getMessage() : null
                ]);
        }
    }
}