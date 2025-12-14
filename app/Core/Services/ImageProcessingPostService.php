<?php

namespace App\Core\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

/**
 * Сервис для обработки изображений из Post запроса
 * 
 * @param  array<string, UploadedFile>  $images Массив, где ключ - имя поля, значение - файл
 * @return array<string, string> Массив, где ключ - имя поля, значение - путь к сохранённому файлу
 */

class ImageProcessingPostService
{
    public function getImagePost(array $images, string $moduleName): array
    {
        $processed = [];
        
        foreach ($images as $fieldName => $image)
        {
            // Генерация уникального имени, сохранение, ресайз и т.д.
            $path = 'storage/' . $this->saveAndProcessImage($image, $moduleName);
            $processed[$fieldName] = $path;
        }
        
        return $processed;
    }

    protected function saveAndProcessImage(UploadedFile $image, $moduleName): string
    {
        // Проверяем, что файл действительно загружен
        if (!$image->isValid()) {
            Log::error('Invalid uploaded file', [
                'error' => $image->getErrorMessage(),
                'original_name' => $image->getClientOriginalName()
            ]);
            throw new \Exception('Невалидный файл: ' . $image->getErrorMessage());
        }
        
        // Генерируем имя файла
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $originalExtension = $image->getClientOriginalExtension();
        
        $safeName = Str::slug($originalName);
        $uniqueName = time() . '_' . uniqid() . '_' . $safeName . '.' . $originalExtension;
        
        // Указываем явно диск 'public'
        $storagePath = $moduleName; // Путь относительно диска 'public'
        
        // Сохраняем с явным указанием диска
        $path = $image->storeAs($storagePath, $uniqueName, 'public');
        
        // Проверяем результат
        if ($path === false) {
            Log::error('Failed to save image', [
                'storagePath' => $storagePath,
                'uniqueName' => $uniqueName,
                'originalName' => $image->getClientOriginalName()
            ]);
            throw new \Exception('Не удалось сохранить файл: ' . $image->getClientOriginalName());
        }
        
        // Логируем детали
        Log::info('Image saved successfully', [
            'module' => $moduleName,
            'original_name' => $image->getClientOriginalName(),
            'saved_as' => $uniqueName,
            'storage_path' => $storagePath,
            'full_disk_path' => $path,
            'public_path' => 'storage/' . $path, // Путь для использования в HTML
            'size' => $image->getSize(),
            'mime_type' => $image->getMimeType()
        ]);
        
        // Возвращаем путь относительно storage/app/public
        return $path;
    }

    /**
     * Удаляет изображение с учетом разных форматов путей
     * 
     * @param string $imagePath Путь к изображению в любом формате
     * @return bool Успешно ли удаление
     */
    public function deleteImage(string $imagePath): bool
    {
        Log::info('Удаление изображения, исходный путь: ' . $imagePath);
        
        if (empty($imagePath)) {
            Log::warning('Передан пустой путь для удаления изображения');
            return false;
        }
        
        // Определяем, какой тип пути у нас
        $pathType = $this->determinePathType($imagePath);
        Log::info('Определен тип пути: ' . $pathType);
        
        // Конвертируем путь в относительный путь для Storage
        $relativePath = $this->convertToRelativePath($imagePath, $pathType);
        
        if (empty($relativePath)) {
            Log::error('Не удалось преобразовать путь: ' . $imagePath);
            return false;
        }
        
        Log::info('Преобразованный путь для удаления: ' . $relativePath);
        
        try {
            // Способ 1: Используем Storage фасад (предпочтительно)
            if (Storage::disk('public')->exists($relativePath)) {
                $result = Storage::disk('public')->delete($relativePath);
                
                Log::info('Удаление через Storage:', [
                    'relative_path' => $relativePath,
                    'success' => $result
                ]);
                
                if ($result) {
                    return true;
                }
            }
            
            // Способ 2: Прямое удаление через unlink (резервный вариант)
            $fullPaths = $this->getPossibleFullPaths($relativePath);
            
            foreach ($fullPaths as $fullPath) {
                if (file_exists($fullPath)) {
                    $result = unlink($fullPath);
                    
                    Log::info('Прямое удаление файла:', [
                        'full_path' => $fullPath,
                        'success' => $result
                    ]);
                    
                    if ($result) {
                        return true;
                    }
                }
            }
            
            // Если файл не найден ни одним из способов
            Log::warning('Файл не найден ни в одном из возможных мест:', [
                'original_path' => $imagePath,
                'relative_path' => $relativePath,
                'possible_full_paths' => $fullPaths
            ]);
            
            return false;
            
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении изображения:', [
                'path' => $imagePath,
                'relative_path' => $relativePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    /**
     * Определяет тип переданного пути
     */
    private function determinePathType(string $path): string
    {
        // Проверяем, является ли это URL
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return 'url';
        }
        
        // Проверяем, является ли это абсолютным путем
        if (strpos($path, '/') === 0 || strpos($path, ':\\') === 1) {
            // Unix или Windows абсолютный путь
            return 'absolute';
        }
        
        // Проверяем, начинается ли с 'storage/'
        if (strpos($path, 'storage/') === 0) {
            return 'storage_relative';
        }
        
        // Иначе считаем относительным путем
        return 'relative';
    }

    /**
     * Конвертирует путь в относительный путь для Storage
     */
    private function convertToRelativePath(string $path, string $pathType): ?string
    {
        switch ($pathType) {
            case 'url':
                // Из URL извлекаем путь после /storage/
                // Пример: http://site.com/storage/news/image.jpg -> news/image.jpg
                if (preg_match('/\/storage\/(.+)$/', $path, $matches)) {
                    return $matches[1];
                }
                return null;
                
            case 'absolute':
                // Из абсолютного пути извлекаем часть после storage/app/public/
                // Пример: /var/www/storage/app/public/news/image.jpg -> news/image.jpg
                $patterns = [
                    '/storage\/app\/public\/(.+)$/',
                    '/public\/storage\/(.+)$/',
                ];
                
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $path, $matches)) {
                        return $matches[1];
                    }
                }
                return null;
                
            case 'storage_relative':
                // Убираем 'storage/' с начала
                // Пример: storage/news/image.jpg -> news/image.jpg
                return substr($path, 8);
                
            case 'relative':
                // Уже относительный путь
                // Пример: news/image.jpg
                return $path;
                
            default:
                return null;
        }
    }

    /**
     * Возвращает возможные полные пути на диске для относительного пути
     */
    private function getPossibleFullPaths(string $relativePath): array
    {
        $possiblePaths = [];
        
        // 1. Стандартный путь Laravel
        $possiblePaths[] = storage_path('app/public/' . $relativePath);
        
        // 2. Альтернативный путь (если используется symlink)
        $possiblePaths[] = public_path('storage/' . $relativePath);
        
        // 3. Путь для Windows (если нужно)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $possiblePaths[] = base_path('storage\app\public\\' . str_replace('/', '\\', $relativePath));
        }
        
        return $possiblePaths;
    }

    /**
     * Удаляет несколько изображений
     * 
     * @param array $imagePaths Массив путей к изображениям
     * @param bool $continueOnError Продолжать при ошибках
     * @return array Результаты удаления
     */
    public function deleteImages(array $imagePaths, bool $continueOnError = true): array
    {
        $results = [];
        
        foreach ($imagePaths as $key => $imagePath) {
            try {
                if (!empty($imagePath)) {
                    $results[$key] = [
                        'path' => $imagePath,
                        'success' => $this->deleteImage($imagePath)
                    ];
                } else {
                    $results[$key] = [
                        'path' => $imagePath,
                        'success' => false,
                        'error' => 'Empty path'
                    ];
                }
            } catch (\Exception $e) {
                $results[$key] = [
                    'path' => $imagePath,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
                
                if (!$continueOnError) {
                    throw $e;
                }
            }
        }
        
        return $results;
    }

    /**
     * Проверяет, существует ли изображение
     */
    public function imageExists(string $imagePath): bool
    {
        if (empty($imagePath)) {
            return false;
        }
        
        try {
            $pathType = $this->determinePathType($imagePath);
            $relativePath = $this->convertToRelativePath($imagePath, $pathType);
            
            if (empty($relativePath)) {
                return false;
            }
            
            // Проверяем через Storage
            if (Storage::disk('public')->exists($relativePath)) {
                return true;
            }
            
            // Проверяем полные пути
            $fullPaths = $this->getPossibleFullPaths($relativePath);
            foreach ($fullPaths as $fullPath) {
                if (file_exists($fullPath)) {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('Ошибка при проверке существования изображения:', [
                'path' => $imagePath,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
}