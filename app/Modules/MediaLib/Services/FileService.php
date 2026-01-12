<?php

namespace App\Modules\MediaLib\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Сервис для работы с файлами
 * Обрабатывает загрузку, валидацию, создание миниатюр
 */
class FileService
{
    protected $imageManager;
    protected $disk;
    protected $uploadDirectory;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
        $this->disk = config('medialib.upload.disk', 'public');
        $this->uploadDirectory = config('medialib.upload.directory', 'medialib');
    }

    /**
     * Загружает файл в указанную папку
     */
    public function upload(UploadedFile $file, ?string $folderPath = null): array
    {
        // Валидация файла
        $this->validateFile($file);

        // Создаем уникальное имя файла
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filename = Str::slug($originalName) . '_' . time() . '.' . $extension;

        // Определяем путь для сохранения
        $path = $folderPath ? trim($folderPath, '/') . '/' . $filename : $filename;
        $fullPath = $this->uploadDirectory . '/' . $path;

        // Сохраняем файл
        $file->storeAs($this->uploadDirectory . '/' . ($folderPath ?? ''), $filename, $this->disk);

        // Создаем миниатюры для изображений
        if (strpos($file->getMimeType(), 'image/') === 0) {
            $this->createThumbnails($path, $fullPath);
        }

        return [
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * Создает папку в хранилище
     */
    public function createFolder(string $folderName, ?string $parentPath = null): string
    {
        $path = $parentPath 
            ? $this->uploadDirectory . '/' . trim($parentPath, '/') . '/' . $folderName
            : $this->uploadDirectory . '/' . $folderName;

        if (!Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->makeDirectory($path);
        }

        return $path;
    }

    /**
     * Удаляет файл из хранилища
     */
    public function deleteFile(string $path): bool
    {
        $fullPath = $this->uploadDirectory . '/' . $path;
        
        // Удаляем основной файл
        Storage::disk($this->disk)->delete($fullPath);
        
        // Удаляем миниатюры для изображений
        if (strpos($path, '/') !== false) {
            $this->deleteThumbnails($path);
        }

        return true;
    }

    /**
     * Удаляет папку из хранилища
     */
    public function deleteFolder(string $path): bool
    {
        $fullPath = $this->uploadDirectory . '/' . $path;
        return Storage::disk($this->disk)->deleteDirectory($fullPath);
    }

    /**
     * Переименовывает файл или папку
     */
    public function rename(string $oldPath, string $newName): bool
    {
        $fullOldPath = $this->uploadDirectory . '/' . $oldPath;
        $fullNewPath = dirname($fullOldPath) . '/' . $newName;

        if (Storage::disk($this->disk)->exists($fullNewPath)) {
            throw new \Exception('Файл или папка с таким именем уже существует');
        }

        return Storage::disk($this->disk)->move($fullOldPath, $fullNewPath);
    }

    /**
     * Валидирует файл перед загрузкой
     */
    protected function validateFile(UploadedFile $file): void
    {
        $allowedMimes = config('medialib.upload.allowed_mimes', []);
        $maxSize = config('medialib.upload.max_file_size', 10240) * 1024; // Конвертируем в байты

        // Проверка MIME-типа
        $extension = $file->getClientOriginalExtension();
        if (!in_array(strtolower($extension), $allowedMimes)) {
            throw new \Exception(
                'Недопустимый формат файла. Разрешены: ' . implode(', ', $allowedMimes)
            );
        }

        // Проверка размера файла
        if ($file->getSize() > $maxSize) {
            throw new \Exception(
                'Файл слишком большой. Максимальный размер: ' . 
                config('medialib.upload.max_file_size') . 'KB'
            );
        }
    }

    /**
     * Создает миниатюры для изображений
     */
    protected function createThumbnails(string $relativePath, string $fullPath): void
    {
        $sizes = config('medialib.images', []);
        
        foreach ($sizes as $sizeName => $dimensions) {
            if (!isset($dimensions['width']) || !isset($dimensions['height'])) {
                continue;
            }

            $thumbnailPath = $this->getThumbnailPath($relativePath, $sizeName);
            
            $image = $this->imageManager->read(Storage::disk($this->disk)->get($fullPath));
            $image->scale($dimensions['width'], $dimensions['height']);
            
            $encoded = $image->encodeByExtension(pathinfo($fullPath, PATHINFO_EXTENSION));
            Storage::disk($this->disk)->put($thumbnailPath, $encoded);
        }
    }

    /**
     * Удаляет миниатюры изображения
     */
    protected function deleteThumbnails(string $relativePath): void
    {
        $sizes = config('medialib.images', []);
        
        foreach (array_keys($sizes) as $sizeName) {
            $thumbnailPath = $this->getThumbnailPath($relativePath, $sizeName);
            if (Storage::disk($this->disk)->exists($thumbnailPath)) {
                Storage::disk($this->disk)->delete($thumbnailPath);
            }
        }
    }

    /**
     * Генерирует путь для миниатюры
     */
    protected function getThumbnailPath(string $relativePath, string $sizeName): string
    {
        $pathInfo = pathinfo($relativePath);
        return $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $sizeName . '.' . $pathInfo['extension'];
    }
}