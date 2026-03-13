<?php

namespace App\Modules\Stock\Services;

use App\Modules\Stock\Models\Stock;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StockImageService
{
    /**
     * Директория для хранения изображений акций (относительно disk 'public').
     */
    protected string $storagePath = 'stock';

    /**
     * Загружает изображение для акции.
     *
     * @param UploadedFile $file Загруженный файл
     * @param Stock|null $stock Опционально, если передан, будет удалено старое изображение
     * @return string Относительный путь к сохраненному файлу (для сохранения в БД)
     * @throws \Exception
     */
    public function upload(UploadedFile $file, ?Stock $stock = null): string
    {
        // Если передан объект акции, удаляем старое изображение
        if ($stock && $stock->image) {
            $this->delete($stock);
        }

        // Генерируем уникальное имя файла
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Сохраняем файл в storage/app/public/stock
        $path = $file->storeAs($this->storagePath, $filename, 'public');

        if (!$path) {
            Log::error('StockImageService: не удалось сохранить файл', ['filename' => $filename]);
            throw new \Exception('Не удалось загрузить изображение');
        }

        Log::info('StockImageService: файл загружен', ['path' => $path]);

        return $path;
    }

    /**
     * Удаляет изображение акции.
     *
     * @param Stock $stock
     * @return bool
     */
    public function delete(Stock $stock): bool
    {
        if (!$stock->image) {
            return false;
        }

        if (Storage::disk('public')->exists($stock->image)) {
            $deleted = Storage::disk('public')->delete($stock->image);
            if ($deleted) {
                Log::info('StockImageService: файл удален', ['path' => $stock->image]);
            } else {
                Log::error('StockImageService: ошибка удаления файла', ['path' => $stock->image]);
            }
            return $deleted;
        }

        Log::warning('StockImageService: файл не найден при удалении', ['path' => $stock->image]);
        return false;
    }
}