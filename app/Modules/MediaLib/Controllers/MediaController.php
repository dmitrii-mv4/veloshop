<?php

namespace App\Modules\MediaLib\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Core\Controllers\Controller;
use App\Modules\MediaLib\Models\Media;
use App\Modules\MediaLib\Models\MediaFolder;
use App\Modules\MediaLib\Requests\MediaStoreRequest;
use App\Modules\MediaLib\Requests\FolderCreateRequest;
use App\Modules\MediaLib\Requests\FolderUpdateRequest;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $currentFolderId = $request->get('folder_id');
        $currentFolder = null;
        
        if ($currentFolderId) {
            $currentFolder = MediaFolder::findOrFail($currentFolderId);
        }

        // Получаем файлы и папки текущей директории
        $mediaFiles = Media::where('folder_id', $currentFolderId)->latest()->get();
        $folders = MediaFolder::where('parent_id', $currentFolderId)->latest()->get();

        // Хлебные крошки
        $breadcrumbs = $this->getBreadcrumbs($currentFolder);

        return view('media::index', compact(
            'mediaFiles', 
            'folders', 
            'currentFolder', 
            'breadcrumbs'
        ));
    }

    public function store(MediaStoreRequest $request)
    {
        $uploadedFiles = [];
        $currentFolderId = $request->folder_id;

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                try {
                    $originalName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $filename = Str::random(40) . '.' . $extension;
                    
                    // Определяем путь для загрузки
                    $path = $this->getUploadPath($currentFolderId);
                    
                    // Сохраняем файл
                    $file->storeAs($path, $filename, 'public');
                    
                    // Создаем запись в БД
                    $media = Media::create([
                        'name' => $originalName,
                        'filename' => $filename,
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'disk' => 'public',
                        'folder_id' => $currentFolderId,
                        'user_id' => auth()->id(),
                    ]);

                    $uploadedFiles[] = $media;

                } catch (\Exception $e) {
                    Log::error('File upload error: ' . $e->getMessage());
                    return redirect()->back()
                        ->with('error', 'Ошибка при загрузке файла: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('admin.media', ['folder_id' => $currentFolderId])
            ->with('success', 'Файлы успешно загружены');
    }

    public function createFolder(FolderCreateRequest $request)
    {
        try {
            $folderName = trim($request->folder_name);
            $parentId = $request->parent_id;
            
            Log::info('Creating folder', [
                'name' => $folderName,
                'parent_id' => $parentId,
                'user_id' => auth()->id()
            ]);

            // Проверяем, существует ли папка с таким именем
            $existingFolder = MediaFolder::where('parent_id', $parentId)
                ->where('name', $folderName)
                ->first();

            if ($existingFolder) {
                Log::warning('Folder already exists', [
                    'name' => $folderName,
                    'parent_id' => $parentId
                ]);
                return redirect()->back()
                    ->with('error', 'Папка с таким именем уже существует в этой директории');
            }
            
            // Определяем путь для новой папки
            $basePath = $this->getUploadPath($parentId);
            $newFolderPath = $basePath . '/' . $folderName;
            
            Log::info('Folder paths', [
                'base_path' => $basePath,
                'new_folder_path' => $newFolderPath
            ]);
            
            // Создаем физическую директорию
            $created = Storage::disk('public')->makeDirectory($newFolderPath);
            
            if (!$created) {
                Log::error('Failed to create physical directory', [
                    'path' => $newFolderPath
                ]);
                return redirect()->back()
                    ->with('error', 'Не удалось создать физическую директорию');
            }

            // Создаем запись в БД
            $folder = MediaFolder::create([
                'name' => $folderName,
                'path' => $newFolderPath,
                'parent_id' => $parentId,
                'user_id' => auth()->id(),
            ]);

            Log::info('Folder created successfully', [
                'folder_id' => $folder->id,
                'path' => $newFolderPath
            ]);

            return redirect()->back()
                ->with('success', 'Папка "' . $folderName . '" успешно создана');

        } catch (\Exception $e) {
            Log::error('Folder creation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'Ошибка при создании папки: ' . $e->getMessage());
        }
    }

    public function updateFolder(FolderUpdateRequest $request, $id)
    {
        try {
            $folder = MediaFolder::findOrFail($id);
            $newName = $request->folder_name;

            // Проверяем, существует ли папка с таким именем в текущей директории
            $existingFolder = MediaFolder::where('parent_id', $folder->parent_id)
                ->where('name', $newName)
                ->where('id', '!=', $id)
                ->first();

            if ($existingFolder) {
                return redirect()->back()
                    ->with('error', 'Папка с таким именем уже существует в этой директории');
            }

            $oldPath = $folder->path;
            $parentPath = dirname($oldPath);
            $newPath = $parentPath . '/' . $newName;

            // Проверяем, не пытаемся ли переименовать в тот же путь
            if ($oldPath === $newPath) {
                return redirect()->back()
                    ->with('info', 'Имя папки не изменилось');
            }

            // Переименовываем физическую папку
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->move($oldPath, $newPath);
            }

            // Обновляем папку и все вложенные пути
            $this->updateFolderPath($folder, $oldPath, $newPath);

            return redirect()->back()
                ->with('success', 'Папка успешно переименована');

        } catch (\Exception $e) {
            Log::error('Folder edit error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Ошибка при переименовании папки: ' . $e->getMessage());
        }
    }

    public function destroyFolder($id)
    {
        try {
            $folder = MediaFolder::findOrFail($id);
            
            // Проверяем, есть ли у пользователя права на удаление
            if ($folder->user_id !== auth()->id()) {
                return redirect()->back()
                    ->with('error', 'У вас нет прав для удаления этой папки');
            }
            
            Log::info('Deleting folder', [
                'folder_id' => $id,
                'folder_name' => $folder->name,
                'path' => $folder->path
            ]);
            
            // Рекурсивно удаляем папку и содержимое
            $this->deleteFolderRecursive($folder);

            return redirect()->back()
                ->with('success', 'Папка "' . $folder->name . '" успешно удалена');

        } catch (\Exception $e) {
            Log::error('Folder deletion error: ' . $e->getMessage(), [
                'folder_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'Ошибка при удалении папки: ' . $e->getMessage());
        }
    }

    public function destroyFile($id)
    {
        try {
            $media = Media::findOrFail($id);
            
            // Удаляем файл
            Storage::disk($media->disk)->delete($media->path . '/' . $media->filename);
            $media->delete();

            return redirect()->back()
                ->with('success', 'Файл успешно удален');

        } catch (\Exception $e) {
            Log::error('File deletion error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Ошибка при удалении файла: ' . $e->getMessage());
        }
    }

    private function deleteFolderRecursive(MediaFolder $folder)
    {
        // Удаляем все файлы в этой папке
        foreach ($folder->mediaFiles as $file) {
            Storage::disk($file->disk)->delete($file->path . '/' . $file->filename);
            $file->delete();
        }

        // Рекурсивно удаляем подпапки
        foreach ($folder->children as $childFolder) {
            $this->deleteFolderRecursive($childFolder);
        }

        // Удаляем саму папку
        Storage::disk('public')->deleteDirectory($folder->path);
        $folder->delete();
    }

    private function updateFolderPath(MediaFolder $folder, string $oldPath, string $newPath)
    {
        // Обновляем путь у текущей папки
        $folder->update([
            'name' => basename($newPath),
            'path' => $newPath,
        ]);

        // Обновляем пути у всех файлов в этой папке
        Media::where('folder_id', $folder->id)->update(['path' => $newPath]);

        // Обновляем пути у всех подпапок
        foreach ($folder->children as $childFolder) {
            $childOldPath = $childFolder->path;
            $childNewPath = str_replace($oldPath, $newPath, $childOldPath);
            $this->updateFolderPath($childFolder, $childOldPath, $childNewPath);
        }
    }

    private function getUploadPath($folderId = null): string
    {
        if ($folderId) {
            $folder = MediaFolder::find($folderId);
            return $folder ? $folder->path : 'media';
        }
        
        // Для корневой папки возвращаем просто 'media'
        return 'media';
    }

    private function getBreadcrumbs(?MediaFolder $currentFolder): array
    {
        $breadcrumbs = [
            ['name' => 'Медиабиблиотека', 'url' => route('admin.media')] // Исправлено с 'media.index' на 'admin.media'
        ];

        if ($currentFolder) {
            $parents = [];
            $folder = $currentFolder;
            
            while ($folder) {
                $parents[] = [
                    'name' => $folder->name,
                    'url' => route('admin.media', ['folder_id' => $folder->id])
                ];
                $folder = $folder->parent;
            }

            $breadcrumbs = array_merge($breadcrumbs, array_reverse($parents));
        }

        return $breadcrumbs;
    }

    public function showFile($id)
    {
        try {
            $media = Media::with('user')->findOrFail($id);
            
            $publicUrl = Storage::disk($media->disk)->url($media->path . '/' . $media->filename);
            
            return response()->json([
                'success' => true,
                'media' => [
                    'id' => $media->id,
                    'name' => $media->name,
                    'filename' => $media->filename,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                    'size_formatted' => $this->formatFileSize($media->size),
                    'created_at' => $media->created_at->format('d.m.Y H:i'),
                    'user_name' => $media->user->name ?? 'Неизвестно',
                    'public_url' => $publicUrl,
                    'is_image' => str_starts_with($media->mime_type, 'image/'),
                    'preview_url' => $publicUrl,
                    'extension' => pathinfo($media->filename, PATHINFO_EXTENSION),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('File show error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Файл не найден'
            ], 404);
        }
    }

    // Добавьте этот метод в контроллер
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function getFileUrl($id)
    {
        try {
            $media = Media::findOrFail($id);
            $url = Storage::disk($media->disk)->url($media->path . '/' . $media->filename);
            
            return response()->json([
                'success' => true,
                'url' => $url
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка получения ссылки'
            ], 404);
        }
    }
}