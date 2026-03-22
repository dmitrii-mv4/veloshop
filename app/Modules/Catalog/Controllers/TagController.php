<?php

namespace App\Modules\Catalog\Controllers;

use App\Modules\Catalog\Models\Tag;
use App\Modules\Catalog\Requests\Tags\CreateTagRequest;
use App\Modules\Catalog\Requests\Tags\UpdateTagRequest;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Контроллер для управления тегами в административной панели
 * Обеспечивает CRUD операции для тегов
 */
class TagController
{
    /**
     * Отображение списка тегов с фильтрацией и пагинацией
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        Log::info('Отображение списка тегов');

        // Получаем параметры фильтрации
        $search = $request->get('search', '');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $perPage = $request->get('per_page', 25);

        // Строим запрос
        $query = Tag::query();

        // Применяем поиск
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
            Log::info('Применен поиск по тегам: ' . $search);
        }

        // Применяем сортировку
        $validSortColumns = ['name', 'slug', 'created_at', 'updated_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'created_at';
        $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? $sortOrder : 'desc';

        $query->orderBy($sortBy, $sortOrder);
        Log::info('Применена сортировка по полю: ' . $sortBy . ' в порядке: ' . $sortOrder);

        // Получаем теги с пагинацией
        $tags = $query->paginate($perPage);
        $totalTags = $tags->total();

        Log::info('Получено ' . $tags->count() . ' тегов из ' . $totalTags);

        return view('catalog::tags.index', compact(
            'tags',
            'search',
            'sortBy',
            'sortOrder',
            'perPage',
            'totalTags'
        ));
    }

    /**
     * Отображение формы создания нового тега
     *
     * @return View
     */
    public function create(): View
    {
        Log::info('Отображение формы создания нового тега');
        return view('catalog::tags.create');
    }

    /**
     * Сохранение нового тега
     *
     * @param CreateTagRequest $request
     * @return RedirectResponse
     */
    public function store(CreateTagRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            // Генерируем slug, если он не передан
            if (empty($validated['slug'])) {
                $validated['slug'] = $this->generateSlug($validated['name']);
            }

            $tag = Tag::create($validated);

            Log::info('Тег успешно создан', [
                'tag_id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug
            ]);

            return redirect()->route('catalog.tags.index')
                ->with('success', 'Тег успешно создан');
        } catch (Exception $e) {
            Log::error('Ошибка при создании тега', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return back()->withInput()
                ->with('error', 'Ошибка при создании тега: ' . $e->getMessage());
        }
    }

    /**
     * Отображение формы редактирования тега
     *
     * @param int $id
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $tag = Tag::findOrFail($id);

            Log::info('Отображение формы редактирования тега', [
                'tag_id' => $tag->id,
                'name' => $tag->name
            ]);

            return view('catalog::tags.edit', compact('tag'));
        } catch (Exception $e) {
            Log::error('Ошибка при загрузке формы редактирования тега', [
                'error' => $e->getMessage(),
                'tag_id' => $id
            ]);

            return redirect()->route('catalog.tags.index')
                ->with('error', 'Тег не найден');
        }
    }

    /**
     * Обновление тега
     *
     * @param UpdateTagRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(UpdateTagRequest $request, int $id): RedirectResponse
    {
        try {
            $tag = Tag::findOrFail($id);

            $validated = $request->validated();

            // Генерируем slug, если он не передан
            if (empty($validated['slug'])) {
                $validated['slug'] = $this->generateSlug($validated['name']);
            }

            $tag->update($validated);

            Log::info('Тег успешно обновлен', [
                'tag_id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug
            ]);

            return redirect()->route('catalog.tags.index')
                ->with('success', 'Тег успешно обновлен');
        } catch (Exception $e) {
            Log::error('Ошибка при обновлении тега', [
                'error' => $e->getMessage(),
                'tag_id' => $id
            ]);

            return back()->withInput()
                ->with('error', 'Ошибка при обновлении тега: ' . $e->getMessage());
        }
    }

    /**
     * Удаление тега
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $tag = Tag::findOrFail($id);

            // Проверяем, используется ли тег
            $productsCount = $tag->products()->count();
            $offersCount = $tag->offers()->count();

            if ($productsCount > 0 || $offersCount > 0) {
                return back()->with('error', 'Невозможно удалить тег, так как он используется в ' . 
                    ($productsCount + $offersCount) . ' записях (товаров: ' . $productsCount . ', предложений: ' . $offersCount . ')');
            }

            $tagName = $tag->name;
            $tag->delete();

            Log::info('Тег успешно удален', [
                'tag_id' => $id,
                'name' => $tagName
            ]);

            return redirect()->route('catalog.tags.index')
                ->with('success', 'Тег успешно удален');
        } catch (Exception $e) {
            Log::error('Ошибка при удалении тега', [
                'error' => $e->getMessage(),
                'tag_id' => $id
            ]);

            return back()->with('error', 'Ошибка при удалении тега: ' . $e->getMessage());
        }
    }

    /**
     * Генерация уникального slug
     *
     * @param string $name
     * @return string
     */
    private function generateSlug(string $name): string
    {
        $slug = \Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (Tag::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
