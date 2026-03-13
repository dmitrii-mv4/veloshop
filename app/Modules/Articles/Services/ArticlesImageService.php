<?php

namespace App\Modules\Articles\Services;

use App\Modules\Articles\Models\Articles;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ArticlesImageService
{
    /**
     * Директория для хранения изображений статьи (относительно disk 'public').
     */
    protected string $storagePath = 'articles';

    /**
     * Загружает изображение для статьи.
     *
     * @param UploadedFile $file Загруженный файл
     * @param Articles|null $articles Опционально, если передан, будет удалено старое изображение
     * @return string Относительный путь к сохраненному файлу (для сохранения в БД)
     * @throws \Exception
     */
    public function upload(UploadedFile $file, ?Articles $articles = null): string
    {
        // Если передан объект статьи, удаляем старое изображение
        if ($articles && $articles->image) {
            $this->delete($articles);
        }

        // Генерируем уникальное имя файла
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Сохраняем файл в storage/app/public/articles
        $path = $file->storeAs($this->storagePath, $filename, 'public');

        if (!$path) {
            Log::error('ArticlesImageService: не удалось сохранить файл', ['filename' => $filename]);
            throw new \Exception('Не удалось загрузить изображение');
        }

        Log::info('ArticlesImageService: файл загружен', ['path' => $path]);

        return $path;
    }

    /**
     * Удаляет изображение статьи.
     *
     * @param Articles $articles
     * @return bool
     */
    public function delete(Articles $articles): bool
    {
        if (!$articles->image) {
            return false;
        }

        if (Storage::disk('public')->exists($articles->image)) {
            $deleted = Storage::disk('public')->delete($articles->image);
            if ($deleted) {
                Log::info('ArticlesImageService: файл удален', ['path' => $articles->image]);
            } else {
                Log::error('ArticlesImageService: ошибка удаления файла', ['path' => $articles->image]);
            }
            return $deleted;
        }

        Log::warning('ArticlesImageService: файл не найден при удалении', ['path' => $articles->image]);
        return false;
    }
}