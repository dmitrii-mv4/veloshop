<?php

namespace App\Modules\Catalog\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Modules\Catalog\Models\CatalogSetting;

/**
 * Сервис для взаимодействия с API 1С
 * Обеспечивает получение данных о товарах из 1С
 */
class Api1CService
{
    /**
     * Настройки модуля
     *
     * @var CatalogSetting
     */
    protected CatalogSetting $settings;

    /**
     * Конструктор сервиса
     *
     * @param CatalogSetting|null $settings
     */
    public function __construct(?CatalogSetting $settings = null)
    {
        $this->settings = $settings ?? CatalogSetting::getSettings();
    }

    /**
     * Проверка соединения с API 1С
     *
     * @return array
     */
    public function checkConnection(): array
    {
        try {
            $startTime = microtime(true);
            $response = Http::timeout(30)->get($this->settings->api_url, [
                'deep' => 0,
                'shop' => $this->settings->default_shop,
            ]);
            $endTime = microtime(true);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Соединение с 1С установлено успешно',
                    'response_time' => round(($endTime - $startTime) * 1000) . ' мс',
                    'status_code' => $response->status(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Ошибка соединения с 1С: ' . $response->status(),
                'status_code' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка проверки соединения с 1С: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Ошибка соединения: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Получение всех товаров из 1С
     *
     * @return array|null
     */
    public function getAllProducts(): ?array
    {
        try {
            $params = [
                'deep' => $this->settings->sync_depth,
                'shop' => $this->settings->default_shop,
                'type' => 'json',
            ];

            // Добавляем фильтры из настроек
            if ($this->settings->warehouses) {
                $params['sklad'] = $this->settings->warehouses;
            }

            if ($this->settings->price_type) {
                $params['cena'] = $this->settings->price_type;
            }

            if ($this->settings->price_type2) {
                $params['cena2'] = $this->settings->price_type2;
            }

            if ($this->settings->show_only_in_stock) {
                $params['instore'] = 1;
            }

            if ($this->settings->show_only_with_price) {
                $params['f-price'] = 1;
            }

            if ($this->settings->show_without_price) {
                $params['z-price'] = 1;
            }

            if ($this->settings->include_partner_stocks) {
                $params['updater'] = 1;
            }

            if (!$this->settings->show_properties) {
                $params['noprops'] = 1;
            }

            if ($this->settings->show_only_simple) {
                $params['simple'] = 1;
            }

            Log::info('Запрос к 1С API с параметрами: ' . json_encode($params));

            $response = Http::timeout(300)->get($this->settings->api_url, $params);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['root'])) {
                    return $data['root'];
                }
                
                return $data;
            }

            Log::error('Ошибка при получении товаров из 1С: ' . $response->status());
            return null;
        } catch (\Exception $e) {
            Log::error('Исключение при получении товаров из 1С: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Получение конкретного товара по коду
     *
     * @param string $code
     * @return array|null
     */
    public function getProductByCode(string $code): ?array
    {
        try {
            $response = Http::get($this->settings->api_url, [
                'code' => $code,
                'shop' => $this->settings->default_shop,
                'type' => 'json',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['root']) && count($data['root']) > 0) {
                    return $data['root'][0];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Ошибка при получении товара из 1С: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Обработка данных товара из 1С
     *
     * @param array $productData
     * @return array
     */
    public function processProductData(array $productData): array
    {
        return [
            'code' => $productData['code'] ?? null,
            'ncode' => $productData['ncode'] ?? null,
            'price' => $productData['price'] ?? null,
            'price2' => $productData['price2'] ?? null,
            'stock' => $productData['stock'] ?? 0,
            'warehouses' => $productData['warehouses'] ?? [],
            'group_code' => $productData['group'] ?? null,
            'group_name' => $productData['group_name'] ?? null,
            'properties' => $productData['properties'] ?? [],
            'has_image' => !empty($productData['image']),
        ];
    }

    /**
     * Получение перевода товара из данных 1С
     *
     * @param array $productData
     * @param string $locale
     * @return array
     */
    public function processProductTranslation(array $productData, string $locale = 'ru'): array
    {
        return [
            'locale' => $locale,
            'shop_code' => $this->settings->default_shop,
            'name' => $productData['name'] ?? 'Без названия',
            'description' => $productData['description'] ?? null,
            'short_description' => $productData['short_description'] ?? null,
        ];
    }

    /**
     * Получение URL API
     *
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->settings->api_url;
    }
}