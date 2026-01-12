<?php

namespace App\Modules\MediaLib\Services;

use App\Modules\MediaLib\Models\MediaFile;
use App\Modules\MediaLib\Models\MediaFolder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для бизнес-логики медиабиблиотеки
 * Управляет файлами и папками, включая корзину
 */
class MediaService
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Создает новую папку
     */
    public function createFolder(array $data, int $userId): MediaFolder
    {
        DB::beginTransaction();

        try {
            // Создаем slug для папки
            $slug = $this->generateSlug($data['name'], MediaFolder::class);
            
            // Определяем путь для файловой системы
            $folderPath = '';
            if (!empty($data['parent_id'])) {
                $parent = MediaFolder::findOrFail($data['parent_id']);
                $folderPath = $parent->getFullPath();
            }

            // Создаем папку в файловой системе
            $this->fileService->createFolder($data['name'], $folderPath);

            // Создаем запись в БД
            $folder = MediaFolder::create([
                'name' => $data['name'],
                'slug' => $slug,
                'parent_id' => $data['parent_id'] ?? null,
                'created_by' => $userId,
            ]);

            DB::commit();

            Log::info('Папка создана', [
                'folder_id' => $folder->id,
                'folder_name' => $folder->name,
                'created_by' => $userId,
            ]);

            return $folder;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка создания папки', [
                'error' => $e->getMessage(),
                'data' => $data,
                'user_id' => $userId,
            ]);
            throw $e;
        }
    }

    /**
     * Загружает файл
     */
    public function uploadFile(array $data, int $userId, $file): MediaFile
    {
        DB::beginTransaction();

        try {
            // Определяем путь для загрузки
            $folderPath = '';
            if (!empty($data['folder_id'])) {
                $folder = MediaFolder::findOrFail($data['folder_id']);
                $folderPath = $folder->getFullPath();
            }

            // Загружаем файл
            $uploadData = $this->fileService->upload($file, $folderPath);

            // Создаем запись в БД
            $mediaFile = MediaFile::create([
                'filename' => $uploadData['filename'],
                'original_name' => $uploadData['original_name'],
                'path' => $uploadData['path'],
                'mime_type' => $uploadData['mime_type'],
                'size' => $uploadData['size'],
                'folder_id' => $data['folder_id'] ?? null,
                'created_by' => $userId,
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'alt' => $data['alt'] ?? null,
                'meta' => $data['meta'] ?? null,
            ]);

            // Создаем переводы, если указаны
            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $translation) {
                    $mediaFile->translations()->create([
                        'locale' => $locale,
                        'title' => $translation['title'] ?? null,
                        'description' => $translation['description'] ?? null,
                        'alt' => $translation['alt'] ?? null,
                        'meta' => $translation['meta'] ?? null,
                    ]);
                }
            }

            DB::commit();

            Log::info('Файл загружен', [
                'file_id' => $mediaFile->id,
                'filename' => $mediaFile->filename,
                'size' => $mediaFile->size,
                'uploaded_by' => $userId,
            ]);

            return $mediaFile;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка загрузки файла', [
                'error' => $e->getMessage(),
                'data' => $data,
                'user_id' => $userId,
            ]);
            throw $e;
        }
    }

    /**
     * Перемещает файл или папку в корзину
     */
    public function moveToTrash($model): bool
    {
        DB::beginTransaction();

        try {
            if ($model instanceof MediaFile) {
                $model->delete();
                Log::info('Файл перемещен в корзину', [
                    'file_id' => $model->id,
                    'filename' => $model->filename,
                ]);
            } elseif ($model instanceof MediaFolder) {
                // Помечаем папку как удаленную
                $model->delete();
                
                // Помечаем все файлы в папке как удаленные
                $model->files()->delete();
                
                // Рекурсивно помечаем подпапки
                foreach ($model->children as $child) {
                    $this->moveToTrash($child);
                }

                Log::info('Папка перемещена в корзину', [
                    'folder_id' => $model->id,
                    'folder_name' => $model->name,
                ]);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка перемещения в корзину', [
                'error' => $e->getMessage(),
                'model' => $model ? get_class($model) : 'null',
            ]);
            throw $e;
        }
    }

    /**
     * Восстанавливает файл или папку из корзины
     */
    public function restoreFromTrash($model): bool
    {
        DB::beginTransaction();

        try {
            if ($model instanceof MediaFile) {
                $model->restore();
                Log::info('Файл восстановлен из корзины', [
                    'file_id' => $model->id,
                    'filename' => $model->filename,
                ]);
            } elseif ($model instanceof MediaFolder) {
                // Восстанавливаем папку
                $model->restore();
                
                // Восстанавливаем файлы в папке
                $model->files()->restore();
                
                // Рекурсивно восстанавливаем подпапки
                foreach ($model->children()->onlyTrashed()->get() as $child) {
                    $this->restoreFromTrash($child);
                }

                Log::info('Папка восстановлена из корзины', [
                    'folder_id' => $model->id,
                    'folder_name' => $model->name,
                ]);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка восстановления из корзины', [
                'error' => $e->getMessage(),
                'model' => $model ? get_class($model) : 'null',
            ]);
            throw $e;
        }
    }

    /**
     * Полностью удаляет файл или папку
     */
    public function forceDelete($model): bool
    {
        DB::beginTransaction();

        try {
            if ($model instanceof MediaFile) {
                // Удаляем файл из хранилища
                $this->fileService->deleteFile($model->path);
                
                // Удаляем записи из БД
                $model->translations()->delete();
                $model->forceDelete();

                Log::info('Файл полностью удален', [
                    'file_id' => $model->id,
                    'filename' => $model->filename,
                ]);

            } elseif ($model instanceof MediaFolder) {
                // Рекурсивно удаляем все вложенные элементы
                foreach ($model->files()->withTrashed()->get() as $file) {
                    $this->forceDelete($file);
                }

                foreach ($model->children()->withTrashed()->get() as $child) {
                    $this->forceDelete($child);
                }

                // Удаляем саму папку
                $this->fileService->deleteFolder($model->getFullPath());
                $model->forceDelete();

                Log::info('Папка полностью удалена', [
                    'folder_id' => $model->id,
                    'folder_name' => $model->name,
                ]);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка полного удаления', [
                'error' => $e->getMessage(),
                'model' => $model ? get_class($model) : 'null',
            ]);
            throw $e;
        }
    }

    /**
     * Переименовывает файл или папку
     */
    public function rename($model, string $newName): bool
    {
        DB::beginTransaction();

        try {
            if ($model instanceof MediaFile) {
                // Переименовываем файл в хранилище
                $newPath = dirname($model->path) . '/' . $newName;
                $this->fileService->rename($model->path, $newName);
                
                // Обновляем запись в БД
                $model->update([
                    'filename' => $newName,
                    'path' => $newPath,
                ]);

                Log::info('Файл переименован', [
                    'file_id' => $model->id,
                    'old_name' => $model->original_name,
                    'new_name' => $newName,
                ]);

            } elseif ($model instanceof MediaFolder) {
                // Переименовываем папку в хранилище
                $oldPath = $model->getFullPath();
                $this->fileService->rename($oldPath, $newName);
                
                // Обновляем запись в БД
                $model->update([
                    'name' => $newName,
                    'slug' => $this->generateSlug($newName, MediaFolder::class, $model->id),
                ]);

                Log::info('Папка переименована', [
                    'folder_id' => $model->id,
                    'old_name' => $model->name,
                    'new_name' => $newName,
                ]);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка переименования', [
                'error' => $e->getMessage(),
                'model' => $model ? get_class($model) : 'null',
                'new_name' => $newName,
            ]);
            throw $e;
        }
    }

    /**
     * Генерирует уникальный slug
     */
    protected function generateSlug(string $name, string $modelClass, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $counter = 1;
        $originalSlug = $slug;

        while (true) {
            $query = $modelClass::where('slug', $slug);
            
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            if (!$query->exists()) {
                break;
            }

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Получает публичную ссылку на файл
     */
    public function getPublicUrl(MediaFile $file): string
    {
        return config('medialib.storage.public_url') . '/' . $file->path;
    }

    /**
     * Очищает корзину от старых файлов
     */
    public function cleanupTrash(): int
    {
        $days = config('medialib.trash.retention_days', 30);
        $threshold = now()->subDays($days);
        
        $deletedCount = 0;

        // Удаляем старые файлы
        $oldFiles = MediaFile::onlyTrashed()
            ->where('deleted_at', '<', $threshold)
            ->get();

        foreach ($oldFiles as $file) {
            $this->forceDelete($file);
            $deletedCount++;
        }

        // Удаляем старые папки
        $oldFolders = MediaFolder::onlyTrashed()
            ->where('deleted_at', '<', $threshold)
            ->get();

        foreach ($oldFolders as $folder) {
            $this->forceDelete($folder);
            $deletedCount++;
        }

        Log::info('Корзина очищена', [
            'deleted_count' => $deletedCount,
            'threshold' => $threshold,
        ]);

        return $deletedCount;
    }
}