<?php

namespace App\Modules\Catalog\Controllers;

use App\Core\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Modules\Catalog\Models\Goods;
use App\Modules\Catalog\Models\Section;
use App\Modules\Catalog\Requests\Goods\GoodsCreateRequest;
use App\Modules\Catalog\Requests\Goods\GoodsEditRequest;

/**
 * Контроллер для управления товарами в каталоге
 */
class GoodsController extends Controller
{   
    /**
     * Отображение списка товаров с фильтрацией, сортировкой и пагинацией
     */
    public function index(Request $request)
    {
        try {
            // Параметры запроса
            $search = $request->get('search', '');
            $perPage = $request->get('per_page', 10);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            // Основной запрос с фильтрацией
            $goods = Goods::withoutTrashed()
                ->when($search, function ($query, $search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                          ->orWhere('articul', 'like', "%{$search}%");
                    });
                })
                ->with(['author', 'section'])
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage);
            
            // Статистика
            $totalGoods = Goods::withoutTrashed()->count();
            $trashedGoods = Goods::onlyTrashed()->count();
            
            Log::info('Список товаров загружен успешно', [
                'total' => $goods->total(),
                'user_id' => auth()->id(),
                'filters' => compact('search', 'sortBy', 'sortOrder')
            ]);
            
            return view('catalog::goods.index', compact(
                'goods',
                'totalGoods',
                'trashedGoods',
                'search',
                'perPage',
                'sortBy',
                'sortOrder'
            ));
            
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке списка товаров', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка загрузки товаров',
                'message' => 'Произошла ошибка при загрузке списка товаров. Пожалуйста, попробуйте снова.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Отображение формы создания товара
     */
    public function create()
    {
        try {
            $sections = $this->getSectionsForSelect();
            
            Log::info('Загрузка формы создания товара', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
                'sections_count' => $sections->count()
            ]);
            
            return view('catalog::goods.create', compact('sections'));
            
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке формы создания товара', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка загрузки формы',
                'message' => 'Не удалось загрузить форму создания товара.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Сохранение нового товара
     */
    public function store(GoodsCreateRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            $validated['author_id'] = auth()->id();
            
            // Если section_id пустой, устанавливаем null
            if (empty($validated['section_id'])) {
                $validated['section_id'] = null;
            }
            
            $goods = Goods::create($validated);
            
            DB::commit();
            
            Log::info('Товар успешно создан', [
                'goods_id' => $goods->id,
                'goods_title' => $goods->title,
                'goods_articul' => $goods->articul,
                'section_id' => $goods->section_id,
                'author_id' => $goods->author_id,
                'created_by' => auth()->id(),
                'created_by_name' => auth()->user()->name,
                'created_at' => now()->toDateTimeString()
            ]);
            
            return redirect()
                ->route('catalog.goods.index')
                ->with('success', 'Товар успешно создан.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании товара', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['_token']),
                'user_id' => auth()->id()
            ]);
            
            return back()
                ->withInput()
                ->with('error', [
                    'title' => 'Ошибка создания товара',
                    'message' => 'Произошла ошибка при сохранении товара. Пожалуйста, попробуйте снова.',
                    'technical' => config('app.debug') ? $e->getMessage() : null
                ]);
        }
    }

    /**
     * Отображение формы редактирования товара
     */
    public function edit($id)
    {
        try {
            $good = Goods::with(['author', 'section'])->findOrFail($id);
            $sections = $this->getSectionsForSelect();
            
            Log::info('Загрузка формы редактирования товара', [
                'goods_id' => $good->id,
                'goods_title' => $good->title,
                'section_id' => $good->section_id,
                'user_id' => auth()->id(),
                'sections_count' => $sections->count()
            ]);
            
            return view('catalog::goods.edit', compact('good', 'sections'));
            
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке формы редактирования товара', [
                'goods_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка загрузки формы',
                'message' => 'Не удалось загрузить форму редактирования товара.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Обновление товара
     */
    public function update(GoodsEditRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $good = Goods::findOrFail($id);
            $oldData = [
                'title' => $good->title,
                'articul' => $good->articul,
                'section_id' => $good->section_id
            ];
            
            $validated = $request->validated();
            
            // Если section_id пустой, устанавливаем null
            if (empty($validated['section_id'])) {
                $validated['section_id'] = null;
            }
            
            $good->update($validated);
            
            DB::commit();
            
            // Логирование изменений
            $changes = [];
            foreach ($oldData as $field => $oldValue) {
                if ($good->$field != $oldValue) {
                    $changes[$field] = [
                        'old' => $oldValue,
                        'new' => $good->$field
                    ];
                }
            }
            
            Log::info('Товар успешно обновлен', [
                'goods_id' => $good->id,
                'goods_title' => $good->title,
                'author_id' => $good->author_id,
                'section_id' => $good->section_id,
                'updated_by' => auth()->id(),
                'updated_by_name' => auth()->user()->name,
                'changes' => $changes,
                'updated_at' => now()->toDateTimeString()
            ]);
            
            return redirect()
                ->route('catalog.goods.index')
                ->with('success', 'Товар успешно обновлен.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении товара', [
                'goods_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['_token']),
                'user_id' => auth()->id()
            ]);
            
            return back()
                ->withInput()
                ->with('error', [
                    'title' => 'Ошибка обновления товара',
                    'message' => 'Произошла ошибка при обновлении товара. Пожалуйста, проверьте данные и попробуйте снова.',
                    'technical' => config('app.debug') ? $e->getMessage() : null
                ]);
        }
    }

    /**
     * Перемещение товара в корзину (мягкое удаление)
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $good = Goods::findOrFail($id);
            $goodId = $good->id;
            $goodTitle = $good->title;
            $authorId = $good->author_id;
            
            $good->delete();
            
            DB::commit();
            
            Log::info('Товар перемещен в корзину', [
                'goods_id' => $goodId,
                'goods_title' => $goodTitle,
                'author_id' => $authorId,
                'deleted_by' => auth()->id(),
                'deleted_by_name' => auth()->user()->name,
                'deleted_at' => now()->toDateTimeString()
            ]);
            
            return redirect()
                ->route('catalog.goods.index')
                ->with('success', 'Товар перемещен в корзину.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при перемещении товара в корзину', [
                'goods_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка удаления',
                'message' => 'Не удалось переместить товар в корзину. Пожалуйста, попробуйте снова.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Отображает товары в корзине
     */
    public function trash(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $perPage = $request->get('per_page', 10);
            $sortBy = $request->get('sort_by', 'deleted_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $goods = Goods::onlyTrashed()
                ->when($search, function ($query, $search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                          ->orWhere('articul', 'like', "%{$search}%");
                    });
                })
                ->with('author')
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage);
            
            $trashedGoods = Goods::onlyTrashed()->count();
            $totalGoods = Goods::withoutTrashed()->count();
            
            Log::info('Корзина товаров загружена', [
                'trashed_count' => $trashedGoods,
                'user_id' => auth()->id()
            ]);
            
            return view('catalog::goods.trash', compact(
                'goods',
                'totalGoods',
                'trashedGoods',
                'search',
                'perPage',
                'sortBy',
                'sortOrder'
            ));
            
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке корзины товаров', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка загрузки корзины',
                'message' => 'Не удалось загрузить товары из корзины.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Восстанавливает товар из корзины
     */
    public function restore($id)
    {
        DB::beginTransaction();
        
        try {
            $good = Goods::onlyTrashed()->findOrFail($id);
            $goodId = $good->id;
            $goodTitle = $good->title;
            
            $good->restore();
            
            DB::commit();
            
            Log::info('Товар восстановлен из корзины', [
                'goods_id' => $goodId,
                'goods_title' => $goodTitle,
                'restored_by' => auth()->id(),
                'restored_by_name' => auth()->user()->name,
                'restored_at' => now()->toDateTimeString()
            ]);
            
            return redirect()
                ->route('catalog.goods.trash')
                ->with('success', 'Товар успешно восстановлен.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при восстановлении товара из корзины', [
                'goods_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка восстановления',
                'message' => 'Не удалось восстановить товар из корзины.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Полностью удаляет товар из корзины
     */
    public function forceDelete($id)
    {
        DB::beginTransaction();
        
        try {
            $good = Goods::onlyTrashed()->findOrFail($id);
            $goodId = $good->id;
            $goodTitle = $good->title;
            $goodArticul = $good->articul;
            $authorId = $good->author_id;
            
            $good->forceDelete();
            
            DB::commit();
            
            Log::info('Товар полностью удален из корзины', [
                'goods_id' => $goodId,
                'goods_title' => $goodTitle,
                'goods_articul' => $goodArticul,
                'author_id' => $authorId,
                'deleted_by' => auth()->id(),
                'deleted_by_name' => auth()->user()->name,
                'deleted_at' => now()->toDateTimeString()
            ]);
            
            return redirect()
                ->route('catalog.goods.trash')
                ->with('success', 'Товар полностью удален.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при полном удалении товара из корзины', [
                'goods_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка удаления',
                'message' => 'Не удалось полностью удалить товар из корзины.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Очищает всю корзину товаров
     */
    public function emptyTrash()
    {
        DB::beginTransaction();
        
        try {
            $goods = Goods::onlyTrashed()->get();
            $deletedCount = 0;
            $deletedItems = [];
            
            foreach ($goods as $good) {
                $deletedItems[] = [
                    'id' => $good->id,
                    'title' => $good->title,
                    'articul' => $good->articul
                ];
                
                $good->forceDelete();
                $deletedCount++;
            }
            
            DB::commit();
            
            Log::info('Корзина товаров очищена', [
                'deleted_count' => $deletedCount,
                'deleted_items' => $deletedItems,
                'emptied_by' => auth()->id(),
                'emptied_by_name' => auth()->user()->name,
                'emptied_at' => now()->toDateTimeString()
            ]);
            
            return redirect()
                ->route('catalog.goods.trash')
                ->with('success', "Корзина очищена. Удалено товаров: {$deletedCount}.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при очистке корзины товаров', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка очистки корзины',
                'message' => 'Не удалось очистить корзину товаров.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Получение иерархического списка разделов для select
     *
     * @return \Illuminate\Support\Collection
     */
    private function getSectionsForSelect()
    {
        try {
            $sections = Section::active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
            
            $flatSections = collect();
            
            $buildTree = function ($items, $parentId = null, $depth = 0) use (&$buildTree, &$flatSections) {
                $prefix = str_repeat('— ', $depth);
                
                foreach ($items as $section) {
                    if ($section->parent_id === $parentId) {
                        $section->name = $prefix . $section->name;
                        $flatSections->put($section->id, $section->name);
                        
                        // Рекурсивно добавляем дочерние разделы
                        $children = $items->filter(function ($child) use ($section) {
                            return $child->parent_id === $section->id;
                        });
                        
                        if ($children->count() > 0) {
                            $buildTree($items, $section->id, $depth + 1);
                        }
                    }
                }
            };
            
            $buildTree($sections);
            
            Log::info('Загружен список разделов для выбора', [
                'total_sections' => $flatSections->count(),
                'user_id' => auth()->id()
            ]);
            
            return $flatSections;
            
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке списка разделов', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return collect();
        }
    }
}