<?php

namespace App\Modules\Catalog\Controllers;

use App\Core\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Modules\Catalog\Models\Section;
use App\Modules\Catalog\Requests\Section\SectionCreateRequest;
use App\Modules\Catalog\Requests\Section\SectionEditRequest;

/**
 * Контроллер для управления разделами каталога
 * Обеспечивает CRUD операции для разделов с поддержкой иерархии и SEO
 */
class SectionController extends Controller
{   
    /**
     * Отображение списка разделов с древовидной структурой
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            // Параметры запроса
            $search = $request->get('search', '');
            $perPage = $request->get('per_page', 20);
            $sortBy = $request->get('sort_by', 'sort_order');
            $sortOrder = $request->get('sort_order', 'asc');
            $status = $request->get('status', 'all');
            
            // Основной запрос с фильтрацией
            $sections = Section::withoutTrashed()
                ->when($search, function ($query, $search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('slug', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                    });
                })
                ->when($status !== 'all', function ($query) use ($status) {
                    return $query->where('is_active', $status == 'active');
                })
                ->with(['parent', 'author', 'children' => function($query) {
                    $query->orderBy('sort_order')->orderBy('name');
                }])
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage);
            
            // Получаем дерево разделов для выбора родителя
            $sectionTree = Section::getTree();
            
            // Статистика
            $totalSections = Section::withoutTrashed()->count();
            $activeSections = Section::active()->count();
            $trashedSections = Section::onlyTrashed()->count();
            
            Log::info('Список разделов каталога загружен успешно', [
                'total' => $sections->total(),
                'user_id' => auth()->id(),
                'filters' => compact('search', 'sortBy', 'sortOrder', 'status')
            ]);
            
            return view('catalog::sections.index', compact(
                'sections',
                'sectionTree',
                'totalSections',
                'activeSections',
                'trashedSections',
                'search',
                'perPage',
                'sortBy',
                'sortOrder',
                'status'
            ));
            
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке списка разделов каталога', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка загрузки разделов',
                'message' => 'Произошла ошибка при загрузке списка разделов. Пожалуйста, попробуйте снова.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Отображение формы создания раздела
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            $sectionTree = Section::getTree();
            
            Log::info('Загрузка формы создания раздела каталога', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name
            ]);
            
            return view('catalog::sections.create', compact('sectionTree'));
            
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке формы создания раздела', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка загрузки формы',
                'message' => 'Не удалось загрузить форму создания раздела.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Сохранение нового раздела
     *
     * @param \App\Modules\Catalog\Requests\Section\SectionCreateRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(SectionCreateRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            $validated['author_id'] = auth()->id();
            
            $section = Section::create($validated);
            
            DB::commit();
            
            Log::info('Раздел каталога успешно создан', [
                'section_id' => $section->id,
                'section_name' => $section->name,
                'parent_id' => $section->parent_id,
                'author_id' => $section->author_id,
                'created_by' => auth()->id(),
                'created_by_name' => auth()->user()->name,
                'created_at' => now()->toDateTimeString()
            ]);
            
            return redirect()
                ->route('catalog.sections.index')
                ->with('success', 'Раздел успешно создан.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании раздела каталога', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['_token']),
                'user_id' => auth()->id()
            ]);
            
            return back()
                ->withInput()
                ->with('error', [
                    'title' => 'Ошибка создания раздела',
                    'message' => 'Произошла ошибка при сохранении раздела. Пожалуйста, попробуйте снова.',
                    'technical' => config('app.debug') ? $e->getMessage() : null
                ]);
        }
    }

    /**
     * Отображение формы редактирования раздела
     *
     * @param int $id Идентификатор раздела
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            $section = Section::with(['parent', 'author'])->findOrFail($id);
            
            // Исключаем текущий раздел и все его дочерние разделы из выбора родителя
            $excludeIds = [$section->id];
            foreach ($section->children as $child) {
                $excludeIds[] = $child->id;
            }
            
            $sectionTree = Section::getTree(null, $excludeIds);
            
            Log::info('Загрузка формы редактирования раздела каталога', [
                'section_id' => $section->id,
                'section_name' => $section->name,
                'user_id' => auth()->id()
            ]);
            
            return view('catalog::sections.edit', compact('section', 'sectionTree'));
            
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке формы редактирования раздела', [
                'section_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка загрузки формы',
                'message' => 'Не удалось загрузить форму редактирования раздела.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Обновление раздела
     *
     * @param \App\Modules\Catalog\Requests\Section\SectionEditRequest $request
     * @param int $id Идентификатор раздела
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(SectionEditRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $section = Section::findOrFail($id);
            $oldData = [
                'name' => $section->name,
                'slug' => $section->slug,
                'parent_id' => $section->parent_id,
                'is_active' => $section->is_active
            ];
            
            $validated = $request->validated();
            $section->update($validated);
            
            DB::commit();
            
            // Логирование изменений
            $changes = [];
            foreach ($oldData as $field => $oldValue) {
                if ($section->$field != $oldValue) {
                    $changes[$field] = [
                        'old' => $oldValue,
                        'new' => $section->$field
                    ];
                }
            }
            
            Log::info('Раздел каталога успешно обновлен', [
                'section_id' => $section->id,
                'section_name' => $section->name,
                'author_id' => $section->author_id,
                'updated_by' => auth()->id(),
                'updated_by_name' => auth()->user()->name,
                'changes' => $changes,
                'updated_at' => now()->toDateTimeString()
            ]);
            
            return redirect()
                ->route('catalog.sections.index')
                ->with('success', 'Раздел успешно обновлен.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении раздела каталога', [
                'section_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['_token']),
                'user_id' => auth()->id()
            ]);
            
            return back()
                ->withInput()
                ->with('error', [
                    'title' => 'Ошибка обновления раздела',
                    'message' => 'Произошла ошибка при обновлении раздела. Пожалуйста, проверьте данные и попробуйте снова.',
                    'technical' => config('app.debug') ? $e->getMessage() : null
                ]);
        }
    }

    /**
     * Перемещение раздела в корзину (мягкое удаление)
     *
     * @param int $id Идентификатор раздела
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $section = Section::findOrFail($id);
            $sectionId = $section->id;
            $sectionName = $section->name;
            $authorId = $section->author_id;
            
            // Проверяем, есть ли товары в разделе
            $goodsCount = $section->goods()->count();
            if ($goodsCount > 0) {
                return back()->with('error', [
                    'title' => 'Невозможно удалить раздел',
                    'message' => "В разделе есть товары ({$goodsCount} шт.). Переместите или удалите товары перед удалением раздела."
                ]);
            }
            
            // Проверяем, есть ли дочерние разделы
            $childrenCount = $section->children()->count();
            if ($childrenCount > 0) {
                return back()->with('error', [
                    'title' => 'Невозможно удалить раздел',
                    'message' => "В разделе есть подразделы ({$childrenCount} шт.). Удалите или переместите подразделы перед удалением."
                ]);
            }
            
            $section->delete();
            
            DB::commit();
            
            Log::info('Раздел перемещен в корзину', [
                'section_id' => $sectionId,
                'section_name' => $sectionName,
                'author_id' => $authorId,
                'deleted_by' => auth()->id(),
                'deleted_by_name' => auth()->user()->name,
                'deleted_at' => now()->toDateTimeString()
            ]);
            
            return redirect()
                ->route('catalog.sections.index')
                ->with('success', 'Раздел перемещен в корзину.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при перемещении раздела в корзину', [
                'section_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка удаления',
                'message' => 'Не удалось переместить раздел в корзину. Пожалуйста, попробуйте снова.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Отображает разделы в корзине
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function trash(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $perPage = $request->get('per_page', 20);
            $sortBy = $request->get('sort_by', 'deleted_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $sections = Section::onlyTrashed()
                ->when($search, function ($query, $search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('slug', 'like', "%{$search}%");
                    });
                })
                ->with(['parent', 'author'])
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage);
            
            $trashedSections = Section::onlyTrashed()->count();
            $totalSections = Section::withoutTrashed()->count();
            $activeSections = Section::active()->count();
            
            Log::info('Корзина разделов каталога загружена', [
                'trashed_count' => $trashedSections,
                'user_id' => auth()->id()
            ]);
            
            return view('catalog::sections.trash', compact(
                'sections',
                'totalSections',
                'activeSections',
                'trashedSections',
                'search',
                'perPage',
                'sortBy',
                'sortOrder'
            ));
            
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке корзины разделов каталога', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка загрузки корзины',
                'message' => 'Не удалось загрузить разделы из корзины.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Восстанавливает раздел из корзины
     *
     * @param int $id Идентификатор раздела
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        DB::beginTransaction();
        
        try {
            $section = Section::onlyTrashed()->findOrFail($id);
            $sectionId = $section->id;
            $sectionName = $section->name;
            
            // Проверяем, существует ли родительский раздел
            if ($section->parent_id) {
                $parentExists = Section::where('id', $section->parent_id)->exists();
                if (!$parentExists) {
                    $section->parent_id = null;
                }
            }
            
            $section->restore();
            
            DB::commit();
            
            Log::info('Раздел восстановлен из корзины', [
                'section_id' => $sectionId,
                'section_name' => $sectionName,
                'restored_by' => auth()->id(),
                'restored_by_name' => auth()->user()->name,
                'restored_at' => now()->toDateTimeString()
            ]);
            
            return redirect()
                ->route('catalog.sections.trash.index')
                ->with('success', 'Раздел успешно восстановлен.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при восстановлении раздела из корзины', [
                'section_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка восстановления',
                'message' => 'Не удалось восстановить раздел из корзины.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Полностью удаляет раздел из корзины
     *
     * @param int $id Идентификатор раздела
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        DB::beginTransaction();
        
        try {
            $section = Section::onlyTrashed()->findOrFail($id);
            $sectionId = $section->id;
            $sectionName = $section->name;
            $sectionSlug = $section->slug;
            $authorId = $section->author_id;
            
            $section->forceDelete();
            
            DB::commit();
            
            Log::info('Раздел полностью удален из корзины', [
                'section_id' => $sectionId,
                'section_name' => $sectionName,
                'section_slug' => $sectionSlug,
                'author_id' => $authorId,
                'deleted_by' => auth()->id(),
                'deleted_by_name' => auth()->user()->name,
                'deleted_at' => now()->toDateTimeString()
            ]);
            
            return redirect()
                ->route('catalog.sections.trash.index')
                ->with('success', 'Раздел полностью удален.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при полном удалении раздела из корзины', [
                'section_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка удаления',
                'message' => 'Не удалось полностью удалить раздел из корзины.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Очищает всю корзину разделов
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function emptyTrash()
    {
        DB::beginTransaction();
        
        try {
            $sections = Section::onlyTrashed()->get();
            $deletedCount = 0;
            $deletedItems = [];
            
            foreach ($sections as $section) {
                $deletedItems[] = [
                    'id' => $section->id,
                    'name' => $section->name,
                    'slug' => $section->slug
                ];
                
                $section->forceDelete();
                $deletedCount++;
            }
            
            DB::commit();
            
            Log::info('Корзина разделов каталога очищена', [
                'deleted_count' => $deletedCount,
                'deleted_items' => $deletedItems,
                'emptied_by' => auth()->id(),
                'emptied_by_name' => auth()->user()->name,
                'emptied_at' => now()->toDateTimeString()
            ]);
            
            return redirect()
                ->route('catalog.sections.trash.index')
                ->with('success', "Корзина очищена. Удалено разделов: {$deletedCount}.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при очистке корзины разделов каталога', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка очистки корзины',
                'message' => 'Не удалось очистить корзину разделов.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Быстрое изменение статуса активности раздела
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id Идентификатор раздела
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $section = Section::findOrFail($id);
            $oldStatus = $section->is_active;
            $newStatus = !$oldStatus;
            
            $section->is_active = $newStatus;
            $section->save();
            
            Log::info('Статус активности раздела изменен', [
                'section_id' => $section->id,
                'section_name' => $section->name,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => auth()->id(),
                'changed_by_name' => auth()->user()->name,
                'changed_at' => now()->toDateTimeString()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Статус раздела успешно изменен',
                'data' => [
                    'id' => $section->id,
                    'is_active' => $section->is_active,
                    'status_text' => $section->is_active ? 'Активен' : 'Неактивен',
                    'status_class' => $section->is_active ? 'badge bg-success' : 'badge bg-secondary'
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при изменении статуса активности раздела', [
                'section_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при изменении статуса раздела'
            ], 500);
        }
    }
}