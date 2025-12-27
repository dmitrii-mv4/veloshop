<?php
/**
 * Контроллер для управления страницами.
 * Включает функционал корзины, логирование и полный CRUD.
 */
namespace App\Modules\Page\Controllers;

use App\Core\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Modules\Page\Models\Page;
use App\Admin\Models\Settings;
use App\Modules\Page\Requests\PageCreateRequest;
use App\Modules\Page\Requests\PageEditRequest;

class PageController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Отображает список страниц с фильтрацией и сортировкой.
     */
    public function index(Request $request)
    {
        try {
            // Параметры запроса
            $search = $request->get('search', '');
            $status = $request->get('status', 'all');
            $perPage = $request->get('per_page', 10);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            // Основной запрос
            $pages = Page::withoutTrashed()
                ->when($search, function ($query, $search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                          ->orWhere('slug', 'like', "%{$search}%")
                          ->orWhere('content', 'like', "%{$search}%");
                    });
                })
                ->when($status !== 'all', function ($query) use ($status) {
                    return $query->where('status', $status);
                })
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage);
            
            // Статистика
            $totalPages = Page::withoutTrashed()->count();
            $publishedPages = Page::withoutTrashed()->where('status', 'published')->count();
            $draftPages = Page::withoutTrashed()->where('status', 'draft')->count();
            $trashedPages = Page::onlyTrashed()->count();
            
            return view('page::index', compact(
                'pages',
                'totalPages',
                'publishedPages',
                'draftPages',
                'trashedPages',
                'search',
                'status',
                'perPage',
                'sortBy',
                'sortOrder'
            ));
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch pages list', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка загрузки страниц',
                'message' => 'Произошла ошибка при загрузке списка страниц. Пожалуйста, попробуйте снова.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Отображает форму создания страницы.
     */
    public function create()
    {
        try {
            $parentPages = Page::withoutTrashed()
                ->whereNull('parent_id')
                ->where('status', 'published')
                ->orderBy('title')
                ->get();
            
            return view('page::create', compact('parentPages'));
        } catch (\Exception $e) {
            Log::error('Failed to load page create form', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка загрузки формы',
                'message' => 'Не удалось загрузить форму создания страницы.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Сохраняет новую страницу.
     */
    public function store(PageCreateRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            $validated['author_id'] = auth()->id();
            $validated['published_at'] = $validated['status'] === 'published' 
                ? ($validated['published_at'] ?? now()) 
                : null;
            
            // Обработка загрузки изображения
            if ($request->hasFile('featured_image')) {
                $image = $request->file('featured_image');
                $path = $image->store('pages/images', 'public');
                $validated['featured_image'] = $path;
                
                Log::info('Page image uploaded', [
                    'path' => $path,
                    'size' => $image->getSize(),
                    'mime_type' => $image->getMimeType()
                ]);
            }
            
            $page = Page::create($validated);
            
            DB::commit();
            
            Log::info('Page created successfully', [
                'page_id' => $page->id,
                'page_title' => $page->title,
                'created_by' => auth()->id(),
                'slug' => $page->slug
            ]);
            
            return redirect()
                ->route('admin.page.index')
                ->with('success', 'Страница успешно создана.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Page creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['_token', 'content'])
            ]);
            
            return back()
                ->withInput()
                ->with('error', [
                    'title' => 'Ошибка создания страницы',
                    'message' => 'Произошла ошибка при сохранении страницы. Пожалуйста, попробуйте снова.',
                    'technical' => config('app.debug') ? $e->getMessage() : null
                ]);
        }
    }

    /**
     * Отображает форму редактирования страницы.
     */
    public function edit(Page $page)
    {
        $urlSite = Settings::All();
        $urlSite = $urlSite[0]['url_site'];

        try {
            $parentPages = Page::withoutTrashed()
                ->whereNull('parent_id')
                ->where('status', 'published')
                ->where('id', '!=', $page->id)
                ->orderBy('title')
                ->get();
            
            return view('page::edit', compact('page', 'parentPages', 'urlSite'));
        } catch (\Exception $e) {
            Log::error('Failed to load page edit form', [
                'page_id' => $page->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка загрузки формы',
                'message' => 'Не удалось загрузить форму редактирования страницы.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Обновляет страницу.
     */
    public function update(Page $page, PageEditRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            
            // Если статус изменился на опубликованный, устанавливаем дату публикации
            if ($validated['status'] === 'published' && $page->status !== 'published') {
                $validated['published_at'] = $validated['published_at'] ?? now();
            }
            
            // Обработка удаления изображения
            if ($request->has('remove_image') && $request->remove_image == 1) {
                if ($page->featured_image) {
                    Storage::disk('public')->delete($page->featured_image);
                    Log::info('Page image removed', ['page_id' => $page->id, 'image' => $page->featured_image]);
                }
                $validated['featured_image'] = null;
            }
            
            // Обработка загрузки нового изображения
            if ($request->hasFile('featured_image')) {
                // Удаляем старое изображение, если есть
                if ($page->featured_image) {
                    Storage::disk('public')->delete($page->featured_image);
                }
                
                $image = $request->file('featured_image');
                $path = $image->store('pages/images', 'public');
                $validated['featured_image'] = $path;
                
                Log::info('Page image updated', [
                    'page_id' => $page->id,
                    'path' => $path,
                    'size' => $image->getSize()
                ]);
            }
            
            $page->update($validated);
            
            DB::commit();
            
            Log::info('Page updated successfully', [
                'page_id' => $page->id,
                'page_title' => $page->title,
                'updated_by' => auth()->id(),
                'changes' => $page->getChanges()
            ]);
            
            return redirect()
                ->route('admin.page.index')
                ->with('success', 'Страница успешно обновлена.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Page update failed', [
                'page_id' => $page->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['_token', 'content'])
            ]);
            
            return back()
                ->withInput()
                ->with('error', [
                    'title' => 'Ошибка обновления страницы',
                    'message' => 'Произошла ошибка при обновлении страницы. Пожалуйста, проверьте данные и попробуйте снова.',
                    'technical' => config('app.debug') ? $e->getMessage() : null
                ]);
        }
    }

    /**
     * Перемещает страницу в корзину.
     */
    public function destroy(Page $page)
    {
        try {
            $pageId = $page->id;
            $pageTitle = $page->title;
            
            $page->delete();
            
            Log::info('Page moved to trash', [
                'page_id' => $pageId,
                'page_title' => $pageTitle,
                'deleted_by' => auth()->id(),
                'deleted_at' => now()
            ]);
            
            return redirect()
                ->route('admin.page.index')
                ->with('success', 'Страница перемещена в корзину.');
                
        } catch (\Exception $e) {
            Log::error('Failed to move page to trash', [
                'page_id' => $page->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка удаления',
                'message' => 'Не удалось переместить страницу в корзину. Пожалуйста, попробуйте снова.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Отображает страницы в корзине.
     */
    public function trash(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $perPage = $request->get('per_page', 10);
            
            $pages = Page::onlyTrashed()
                ->when($search, function ($query, $search) {
                    return $query->where('title', 'like', "%{$search}%")
                                 ->orWhere('slug', 'like', "%{$search}%");
                })
                ->orderBy('deleted_at', 'desc')
                ->paginate($perPage);
            
            $trashedCount = Page::onlyTrashed()->count();
            
            return view('page::trash', compact('pages', 'trashedCount', 'search', 'perPage'));
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch trashed pages', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка загрузки корзины',
                'message' => 'Не удалось загрузить страницы из корзины.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Восстанавливает страницу из корзины.
     */
    public function restore($id)
    {
        try {
            $page = Page::onlyTrashed()->findOrFail($id);
            
            if ($page->restoreWithLog(auth()->id())) {
                return redirect()
                    ->route('admin.page.trash')
                    ->with('success', 'Страница успешно восстановлена.');
            } else {
                throw new \Exception('Failed to restore page');
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to restore page from trash', [
                'page_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка восстановления',
                'message' => 'Не удалось восстановить страницу из корзины.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Полностью удаляет страницу.
     */
    public function forceDelete($id)
    {
        try {
            $page = Page::onlyTrashed()->findOrFail($id);
            
            if ($page->forceDeleteWithLog(auth()->id())) {
                return redirect()
                    ->route('admin.page.trash')
                    ->with('success', 'Страница полностью удалена.');
            } else {
                throw new \Exception('Failed to permanently delete page');
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to permanently delete page', [
                'page_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка удаления',
                'message' => 'Не удалось полностью удалить страницу.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Очищает всю корзину.
     */
    public function emptyTrash()
    {
        DB::beginTransaction();
        
        try {
            $pages = Page::onlyTrashed()->get();
            $deletedCount = 0;
            
            foreach ($pages as $page) {
                if ($page->forceDeleteWithLog(auth()->id())) {
                    $deletedCount++;
                }
            }
            
            DB::commit();
            
            Log::info('Trash emptied', [
                'deleted_count' => $deletedCount,
                'emptied_by' => auth()->id()
            ]);
            
            return redirect()
                ->route('admin.page.trash')
                ->with('success', "Корзина очищена. Удалено страниц: {$deletedCount}.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to empty trash', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка очистки корзины',
                'message' => 'Не удалось очистить корзину.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }
}