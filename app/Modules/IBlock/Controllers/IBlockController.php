<?php

namespace App\Modules\IBlock\Controllers;

use App\Core\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Modules\IBlock\Models\IBlock;
use App\Modules\IBlock\Requests\IBlockCreateRequest;
use App\Modules\IBlock\Requests\IBlockEditRequest;

/**
 * Контроллер для управления информационными блоками.
 * Включает функционал корзины, логирование и полный CRUD.
 */
class IBlockController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Отображает список информационных блоков с фильтрацией и сортировкой.
     */
    public function index(Request $request)
    {
        try {
            // Параметры запроса
            $search = $request->get('search', '');
            $perPage = $request->get('per_page', 10);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            // Основной запрос
            $iblocks = IBlock::withoutTrashed()
                ->when($search, function ($query, $search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                          ->orWhere('content', 'like', "%{$search}%");
                    });
                })
                ->with('author')
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage);
            
            // Статистика
            $totalIBlocks = IBlock::withoutTrashed()->count();
            $trashedIBlocks = IBlock::onlyTrashed()->count();
            
            return view('iblock::index', compact(
                'iblocks',
                'totalIBlocks',
                'trashedIBlocks',
                'search',
                'perPage',
                'sortBy',
                'sortOrder'
            ));
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch iblock list', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка загрузки информационных блоков',
                'message' => 'Произошла ошибка при загрузке списка информационных блоков. Пожалуйста, попробуйте снова.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Отображает форму создания информационного блока.
     */
    public function create()
    {
        try {
            return view('iblock::create');
        } catch (\Exception $e) {
            Log::error('Failed to load iblock create form', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка загрузки формы',
                'message' => 'Не удалось загрузить форму создания информационного блока.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Сохраняет новый информационный блок.
     */
    public function store(IBlockCreateRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            $validated['author_id'] = auth()->id();
            
            $iblock = IBlock::create($validated);
            
            DB::commit();
            
            Log::info('IBlock created successfully', [
                'iblock_id' => $iblock->id,
                'iblock_title' => $iblock->title,
                'created_by' => auth()->id()
            ]);
            
            return redirect()
                ->route('admin.iblock.index')
                ->with('success', 'Информационный блок успешно создан.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('IBlock creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['_token', 'content'])
            ]);
            
            return back()
                ->withInput()
                ->with('error', [
                    'title' => 'Ошибка создания информационного блока',
                    'message' => 'Произошла ошибка при сохранении информационного блока. Пожалуйста, попробуйте снова.',
                    'technical' => config('app.debug') ? $e->getMessage() : null
                ]);
        }
    }

    /**
     * Отображает форму редактирования информационного блока.
     */
    public function edit(IBlock $iblock)
    {
        try {
            return view('iblock::edit', compact('iblock'));
        } catch (\Exception $e) {
            Log::error('Failed to load iblock edit form', [
                'iblock_id' => $iblock->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка загрузки формы',
                'message' => 'Не удалось загрузить форму редактирования информационного блока.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Обновляет информационный блок.
     */
    public function update(IBlock $iblock, IBlockEditRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            
            $iblock->update($validated);
            
            DB::commit();
            
            Log::info('IBlock updated successfully', [
                'iblock_id' => $iblock->id,
                'iblock_title' => $iblock->title,
                'updated_by' => auth()->id(),
                'changes' => $iblock->getChanges()
            ]);
            
            return redirect()
                ->route('admin.iblock.index')
                ->with('success', 'Информационный блок успешно обновлен.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('IBlock update failed', [
                'iblock_id' => $iblock->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['_token', 'content'])
            ]);
            
            return back()
                ->withInput()
                ->with('error', [
                    'title' => 'Ошибка обновления информационного блока',
                    'message' => 'Произошла ошибка при обновлении информационного блока. Пожалуйста, проверьте данные и попробуйте снова.',
                    'technical' => config('app.debug') ? $e->getMessage() : null
                ]);
        }
    }

    /**
     * Перемещает информационный блок в корзину.
     */
    public function destroy(IBlock $iblock)
    {
        try {
            $iblockId = $iblock->id;
            $iblockTitle = $iblock->title;
            
            $iblock->delete();
            
            Log::info('IBlock moved to trash', [
                'iblock_id' => $iblockId,
                'iblock_title' => $iblockTitle,
                'deleted_by' => auth()->id(),
                'deleted_at' => now()
            ]);
            
            return redirect()
                ->route('admin.iblock.index')
                ->with('success', 'Информационный блок перемещен в корзину.');
                
        } catch (\Exception $e) {
            Log::error('Failed to move iblock to trash', [
                'iblock_id' => $iblock->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка удаления',
                'message' => 'Не удалось переместить информационный блок в корзину. Пожалуйста, попробуйте снова.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Отображает информационные блоки в корзине.
     */
    public function trash(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $perPage = $request->get('per_page', 10);
            
            $iblocks = IBlock::onlyTrashed()
                ->when($search, function ($query, $search) {
                    return $query->where('title', 'like', "%{$search}%")
                                 ->orWhere('content', 'like', "%{$search}%");
                })
                ->with('author')
                ->orderBy('deleted_at', 'desc')
                ->paginate($perPage);
            
            $trashedCount = IBlock::onlyTrashed()->count();
            
            return view('iblock::trash', compact('iblocks', 'trashedCount', 'search', 'perPage'));
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch trashed iblocks', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка загрузки корзины',
                'message' => 'Не удалось загрузить информационные блоки из корзины.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Восстанавливает информационный блок из корзины.
     */
    public function restore($id)
    {
        try {
            $iblock = IBlock::onlyTrashed()->findOrFail($id);
            
            if ($iblock->restoreWithLog(auth()->id())) {
                return redirect()
                    ->route('admin.iblock.trash')
                    ->with('success', 'Информационный блок успешно восстановлен.');
            } else {
                throw new \Exception('Failed to restore iblock');
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to restore iblock from trash', [
                'iblock_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка восстановления',
                'message' => 'Не удалось восстановить информационный блок из корзины.',
                'technical' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Полностью удаляет информационный блок.
     */
    public function forceDelete($id)
    {
        try {
            $iblock = IBlock::onlyTrashed()->findOrFail($id);
            
            if ($iblock->forceDeleteWithLog(auth()->id())) {
                return redirect()
                    ->route('admin.iblock.trash')
                    ->with('success', 'Информационный блок полностью удален.');
            } else {
                throw new \Exception('Failed to permanently delete iblock');
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to permanently delete iblock', [
                'iblock_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', [
                'title' => 'Ошибка удаления',
                'message' => 'Не удалось полностью удалить информационный блок.',
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
            $iblocks = IBlock::onlyTrashed()->get();
            $deletedCount = 0;
            
            foreach ($iblocks as $iblock) {
                if ($iblock->forceDeleteWithLog(auth()->id())) {
                    $deletedCount++;
                }
            }
            
            DB::commit();
            
            Log::info('IBlock trash emptied', [
                'deleted_count' => $deletedCount,
                'emptied_by' => auth()->id()
            ]);
            
            return redirect()
                ->route('admin.iblock.trash')
                ->with('success', "Корзина очищена. Удалено информационных блоков: {$deletedCount}.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to empty iblock trash', [
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