<?php

namespace App\Modules\MediaLib\Controllers;

use App\Core\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Modules\MediaLib\Models\MediaFile;
use App\Modules\MediaLib\Models\MediaFolder;
use App\Modules\MediaLib\Requests\MediaFolderCreateRequest;
use App\Modules\MediaLib\Requests\MediaFileUploadRequest;
use App\Modules\MediaLib\Requests\MediaRenameRequest;
use App\Modules\MediaLib\Requests\MediaUpdateRequest;
use App\Modules\MediaLib\Services\MediaService;

/**
 * Контроллер для управления медиабиблиотекой
 * Включает функционал работы с файлами, папками и корзиной
 */
class MediaLibController extends Controller
{
    protected $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->middleware('admin');
        $this->mediaService = $mediaService;
    }

    /**
     * Отображает главную страницу медиабиблиотеки
     */
    public function index(Request $request)
    {
        try {
            // Получаем текущую папку
            $currentFolder = null;
            if ($request->has('folder_id')) {
                $currentFolder = MediaFolder::find($request->folder_id);
            }

            // Получаем хлебные крошки
            $breadcrumbs = $this->getBreadcrumbs($currentFolder);

            // Получаем папки и файлы для текущей папки
            $folders = MediaFolder::where('parent_id', $currentFolder ? $currentFolder->id : null)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->paginate(20, ['*'], 'folders_page');

            $files = MediaFile::where('folder_id', $currentFolder ? $currentFolder->id : null)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->paginate(30, ['*'], 'files_page');

            // Статистика
            $totalFiles = MediaFile::count();
            $totalFolders = MediaFolder::count();
            $trashedFiles = MediaFile::onlyTrashed()->count();
            $trashedFolders = MediaFolder::onlyTrashed()->count();

            return view('medialib::index', compact(
                'currentFolder',
                'folders',
                'files',
                'breadcrumbs',
                'totalFiles',
                'totalFolders',
                'trashedFiles',
                'trashedFolders'
            ));
        } catch (\Exception $e) {
            Log::error('Ошибка загрузки медиабиблиотеки', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', [
                'title' => 'Ошибка загрузки медиабиблиотеки',
                'message' => 'Произошла ошибка при загрузке медиабиблиотеки. Пожалуйста, попробуйте снова.',
                'technical' => config('app.debug') ? $e->getMessage() : null,
            ]);
        }
    }

    /**
     * Создает новую папку
     */
    public function createFolder(MediaFolderCreateRequest $request)
    {
        try {
            $validated = $request->validated();
            
            $folder = $this->mediaService->createFolder(
                $validated,
                auth()->id()
            );

            return redirect()
                ->route('admin.medialib.index', ['folder_id' => $validated['parent_id'] ?? null])
                ->with('success', 'Папка успешно создана.');
        } catch (\Exception $e) {
            Log::error('Ошибка создания папки', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
            ]);

            return back()
                ->withInput()
                ->with('error', [
                    'title' => 'Ошибка создания папки',
                    'message' => $e->getMessage(),
                ]);
        }
    }

    /**
     * Загружает файл
     */
    public function uploadFile(MediaFileUploadRequest $request)
    {
        try {
            $validated = $request->validated();
            
            $file = $this->mediaService->uploadFile(
                $validated,
                auth()->id(),
                $request->file('file')
            );

            return redirect()
                ->route('admin.medialib.index', ['folder_id' => $validated['folder_id'] ?? null])
                ->with('success', 'Файл успешно загружен.');
        } catch (\Exception $e) {
            Log::error('Ошибка загрузки файла', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except('file'),
            ]);

            return back()
                ->withInput()
                ->with('error', [
                    'title' => 'Ошибка загрузки файла',
                    'message' => $e->getMessage(),
                ]);
        }
    }

    /**
     * Показывает информацию о файле
     */
    public function show(MediaFile $file)
    {
        try {
            $translations = $file->translations->keyBy('locale');
            $publicUrl = $this->mediaService->getPublicUrl($file);

            return view('medialib::show', compact('file', 'translations', 'publicUrl'));
        } catch (\Exception $e) {
            Log::error('Ошибка просмотра файла', [
                'file_id' => $file->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', [
                'title' => 'Ошибка просмотра файла',
                'message' => 'Не удалось загрузить информацию о файле.',
                'technical' => config('app.debug') ? $e->getMessage() : null,
            ]);
        }
    }

    /**
     * Обновляет метаданные файла
     */
    public function update(MediaFile $file, MediaUpdateRequest $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();
            
            // Обновляем основные данные
            $file->update([
                'title' => $validated['title'] ?? $file->title,
                'description' => $validated['description'] ?? $file->description,
                'alt' => $validated['alt'] ?? $file->alt,
                'meta' => $validated['meta'] ?? $file->meta,
            ]);

            // Обновляем или создаем переводы
            if (!empty($validated['translations'])) {
                foreach ($validated['translations'] as $locale => $translation) {
                    $file->translations()->updateOrCreate(
                        ['locale' => $locale],
                        [
                            'title' => $translation['title'] ?? null,
                            'description' => $translation['description'] ?? null,
                            'alt' => $translation['alt'] ?? null,
                            'meta' => $translation['meta'] ?? null,
                        ]
                    );
                }
            }

            DB::commit();

            Log::info('Метаданные файла обновлены', [
                'file_id' => $file->id,
                'updated_by' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.medialib.show', $file)
                ->with('success', 'Метаданные успешно обновлены.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Ошибка обновления метаданных файла', [
                'file_id' => $file->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
            ]);

            return back()
                ->withInput()
                ->with('error', [
                    'title' => 'Ошибка обновления',
                    'message' => 'Не удалось обновить метаданные файла.',
                    'technical' => config('app.debug') ? $e->getMessage() : null,
                ]);
        }
    }

    /**
     * Переименовывает файл
     */
    public function renameFile(MediaFile $file, MediaRenameRequest $request)
    {
        try {
            $validated = $request->validated();
            
            $this->mediaService->rename($file, $validated['name']);

            return redirect()
                ->route('admin.medialib.index', ['folder_id' => $file->folder_id])
                ->with('success', 'Файл успешно переименован.');
        } catch (\Exception $e) {
            Log::error('Ошибка переименования файла', [
                'file_id' => $file->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'new_name' => $request->name,
            ]);

            return back()->with('error', [
                'title' => 'Ошибка переименования',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Переименовывает папку
     */
    public function renameFolder(MediaFolder $folder, MediaRenameRequest $request)
    {
        try {
            $validated = $request->validated();
            
            $this->mediaService->rename($folder, $validated['name']);

            return redirect()
                ->route('admin.medialib.index', ['folder_id' => $folder->parent_id])
                ->with('success', 'Папка успешно переименована.');
        } catch (\Exception $e) {
            Log::error('Ошибка переименования папки', [
                'folder_id' => $folder->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'new_name' => $request->name,
            ]);

            return back()->with('error', [
                'title' => 'Ошибка переименования',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Перемещает файл в корзину
     */
    public function destroyFile(MediaFile $file)
    {
        try {
            $this->mediaService->moveToTrash($file);

            return redirect()
                ->route('admin.medialib.index', ['folder_id' => $file->folder_id])
                ->with('success', 'Файл перемещен в корзину.');
        } catch (\Exception $e) {
            Log::error('Ошибка перемещения файла в корзину', [
                'file_id' => $file->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', [
                'title' => 'Ошибка удаления',
                'message' => 'Не удалось переместить файл в корзину.',
                'technical' => config('app.debug') ? $e->getMessage() : null,
            ]);
        }
    }

    /**
     * Перемещает папку в корзину
     */
    public function destroyFolder(MediaFolder $folder)
    {
        try {
            $this->mediaService->moveToTrash($folder);

            return redirect()
                ->route('admin.medialib.index', ['folder_id' => $folder->parent_id])
                ->with('success', 'Папка перемещена в корзину.');
        } catch (\Exception $e) {
            Log::error('Ошибка перемещения папки в корзину', [
                'folder_id' => $folder->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', [
                'title' => 'Ошибка удаления',
                'message' => 'Не удалось переместить папку в корзину.',
                'technical' => config('app.debug') ? $e->getMessage() : null,
            ]);
        }
    }

    /**
     * Отображает корзину
     */
    public function trash(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $perPage = $request->get('per_page', 20);

            // Получаем удаленные файлы
            $files = MediaFile::onlyTrashed()
                ->when($search, function ($query, $search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('filename', 'like', "%{$search}%")
                          ->orWhere('original_name', 'like', "%{$search}%")
                          ->orWhere('title', 'like', "%{$search}%");
                    });
                })
                ->with(['creator', 'folder'])
                ->orderBy('deleted_at', 'desc')
                ->paginate($perPage);

            // Получаем удаленные папки
            $folders = MediaFolder::onlyTrashed()
                ->when($search, function ($query, $search) {
                    return $query->where('name', 'like', "%{$search}%");
                })
                ->with(['creator', 'parent'])
                ->orderBy('deleted_at', 'desc')
                ->paginate($perPage, ['*'], 'folders_page');

            // Статистика корзины
            $trashedCount = MediaFile::onlyTrashed()->count() + MediaFolder::onlyTrashed()->count();
            $autoDeleteDays = config('medialib.trash.retention_days', 30);

            return view('medialib::trash', compact(
                'files',
                'folders',
                'trashedCount',
                'search',
                'perPage',
                'autoDeleteDays'
            ));
        } catch (\Exception $e) {
            Log::error('Ошибка загрузки корзины', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', [
                'title' => 'Ошибка загрузки корзины',
                'message' => 'Не удалось загрузить корзину.',
                'technical' => config('app.debug') ? $e->getMessage() : null,
            ]);
        }
    }

    /**
     * Восстанавливает файл из корзины
     */
    public function restoreFile($id)
    {
        try {
            $file = MediaFile::onlyTrashed()->findOrFail($id);
            
            $this->mediaService->restoreFromTrash($file);

            return redirect()
                ->route('admin.medialib.trash')
                ->with('success', 'Файл успешно восстановлен.');
        } catch (\Exception $e) {
            Log::error('Ошибка восстановления файла', [
                'file_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', [
                'title' => 'Ошибка восстановления',
                'message' => 'Не удалось восстановить файл из корзины.',
                'technical' => config('app.debug') ? $e->getMessage() : null,
            ]);
        }
    }

    /**
     * Восстанавливает папку из корзины
     */
    public function restoreFolder($id)
    {
        try {
            $folder = MediaFolder::onlyTrashed()->findOrFail($id);
            
            $this->mediaService->restoreFromTrash($folder);

            return redirect()
                ->route('admin.medialib.trash')
                ->with('success', 'Папка успешно восстановлена.');
        } catch (\Exception $e) {
            Log::error('Ошибка восстановления папки', [
                'folder_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', [
                'title' => 'Ошибка восстановления',
                'message' => 'Не удалось восстановить папку из корзины.',
                'technical' => config('app.debug') ? $e->getMessage() : null,
            ]);
        }
    }

    /**
     * Полностью удаляет файл
     */
    public function forceDeleteFile($id)
    {
        try {
            $file = MediaFile::onlyTrashed()->findOrFail($id);
            
            $this->mediaService->forceDelete($file);

            return redirect()
                ->route('admin.medialib.trash')
                ->with('success', 'Файл полностью удален.');
        } catch (\Exception $e) {
            Log::error('Ошибка полного удаления файла', [
                'file_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', [
                'title' => 'Ошибка удаления',
                'message' => 'Не удалось полностью удалить файл.',
                'technical' => config('app.debug') ? $e->getMessage() : null,
            ]);
        }
    }

    /**
     * Полностью удаляет папку
     */
    public function forceDeleteFolder($id)
    {
        try {
            $folder = MediaFolder::onlyTrashed()->findOrFail($id);
            
            $this->mediaService->forceDelete($folder);

            return redirect()
                ->route('admin.medialib.trash')
                ->with('success', 'Папка полностью удалена.');
        } catch (\Exception $e) {
            Log::error('Ошибка полного удаления папки', [
                'folder_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', [
                'title' => 'Ошибка удаления',
                'message' => 'Не удалось полностью удалить папку.',
                'technical' => config('app.debug') ? $e->getMessage() : null,
            ]);
        }
    }

    /**
     * Очищает всю корзину
     */
    public function emptyTrash()
    {
        DB::beginTransaction();

        try {
            $deletedCount = 0;

            // Удаляем все файлы
            $files = MediaFile::onlyTrashed()->get();
            foreach ($files as $file) {
                $this->mediaService->forceDelete($file);
                $deletedCount++;
            }

            // Удаляем все папки
            $folders = MediaFolder::onlyTrashed()->get();
            foreach ($folders as $folder) {
                $this->mediaService->forceDelete($folder);
                $deletedCount++;
            }

            DB::commit();

            Log::info('Корзина очищена', [
                'deleted_count' => $deletedCount,
                'emptied_by' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.medialib.trash')
                ->with('success', "Корзина очищена. Удалено объектов: {$deletedCount}.");
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Ошибка очистки корзины', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', [
                'title' => 'Ошибка очистки корзины',
                'message' => 'Не удалось очистить корзину.',
                'technical' => config('app.debug') ? $e->getMessage() : null,
            ]);
        }
    }

    /**
     * Получает хлебные крошки для текущей папки
     */
    private function getBreadcrumbs(?MediaFolder $folder): array
    {
        $breadcrumbs = [
            [
                'title' => 'Медиабиблиотека',
                'url' => route('admin.medialib.index'),
            ],
        ];

        if ($folder) {
            $parents = [];
            $current = $folder;

            while ($current) {
                $parents[] = [
                    'title' => $current->name,
                    'url' => route('admin.medialib.index', ['folder_id' => $current->id]),
                ];
                $current = $current->parent;
            }

            $breadcrumbs = array_merge($breadcrumbs, array_reverse($parents));
        }

        return $breadcrumbs;
    }
}