<?php

namespace App\Modules\News\Services;

use App\Modules\News\Models\News;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NewsImageService
{
    /**
     * Директория для хранения изображений новостей (относительно disk 'public').
     */
    protected string $storagePath = 'news';

    /**
     * Загружает изображение для новости.
     *
     * @param UploadedFile $file Загруженный файл
     * @param News|null $news Опционально, если передан, будет удалено старое изображение
     * @return string Относительный путь к сохраненному файлу (для сохранения в БД)
     * @throws \Exception
     */
    public function upload(UploadedFile $file, ?News $news = null): string
    {
        // Если передан объект новости, удаляем старое изображение
        if ($news && $news->image) {
            $this->delete($news);
        }

        // Генерируем уникальное имя файла
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Сохраняем файл в storage/app/public/news
        $path = $file->storeAs($this->storagePath, $filename, 'public');

        if (!$path) {
            Log::error('NewsImageService: не удалось сохранить файл', ['filename' => $filename]);
            throw new \Exception('Не удалось загрузить изображение');
        }

        Log::info('NewsImageService: файл загружен', ['path' => $path]);

        return $path;
    }

    /**
     * Удаляет изображение новости.
     *
     * @param News $news
     * @return bool
     */
    public function delete(News $news): bool
    {
        if (!$news->image) {
            return false;
        }

        if (Storage::disk('public')->exists($news->image)) {
            $deleted = Storage::disk('public')->delete($news->image);
            if ($deleted) {
                Log::info('NewsImageService: файл удален', ['path' => $news->image]);
            } else {
                Log::error('NewsImageService: ошибка удаления файла', ['path' => $news->image]);
            }
            return $deleted;
        }

        Log::warning('NewsImageService: файл не найден при удалении', ['path' => $news->image]);
        return false;
    }
}