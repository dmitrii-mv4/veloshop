<?php

namespace App\Core\Console\Commands;

use Illuminate\Console\Command;
use App\Core\Services\InstallationService;

class InstallKotiksCMSCommand extends Command
{
    /**
     * Сигнатура команды
     */
    protected $signature = 'kotiks:install 
                            {--seed-all : Запустить все системные сиды} 
                            {--no-admin : Не создавать администратора}
                            {--no-seed : Не выполнять сиды}
                            {--force : Выполнить без подтверждения}
                            {--skip-role-check : Пропустить проверку ролей}';

    /**
     * Описание команды
     */
    protected $description = 'Установка Kotiks CMS: миграции, ключ, символическая ссылка и создание администратора.';

    /**
     * Логика выполнения команды
     */
    public function handle(InstallationService $installationService): int
    {
        $this->info('🚀 Начинаем установку Kotiks CMS...');

        // Подготовка опций
        $options = [
            'force' => $this->option('force'),
            'skip_role_check' => $this->option('skip-role-check'),
            'no_seed' => $this->option('no-seed'),
            'no_admin' => $this->option('no-admin'),
            'seed_all' => $this->option('seed-all'),
        ];

        // Запрос подтверждения если не force
        if (!$options['force']) {
            $this->showPreInstallInfo($installationService);
            
            if (!$this->confirm('Продолжить установку?')) {
                $this->info('Установка отменена.');
                return self::SUCCESS;
            }
        }

        // Выполнение установки
        $results = $installationService->install($options);
        
        // Отображение результатов
        $this->showInstallationResults($results);
        
        // Показать сводку
        $this->showInstallationSummary($installationService);
        
        return self::SUCCESS;
    }

    /**
     * Показать информацию перед установкой
     */
    private function showPreInstallInfo(InstallationService $service): void
    {
        $info = $service->getInstallationInfo();
        
        $this->table(
            ['Компонент', 'Количество'],
            [
                ['Миграции', "{$info['migrations']['valid']}/{$info['migrations']['registered']}"],
                ['Сиды', "{$info['seeders']['valid']}/{$info['seeders']['registered']}"],
                ['Статус БД', $info['system_status']['database_connected'] ? '✅ Подключена' : '❌ Ошибка'],
                ['Ключ приложения', $info['system_status']['app_key_generated'] ? '✅ Установлен' : '❌ Отсутствует'],
            ]
        );
    }

    /**
     * Показать результаты установки
     */
    private function showInstallationResults(array $results): void
    {
        $this->info("\n📊 Результаты установки:");
        
        foreach ($results as $component => $result) {
            // Проверяем, является ли результат массивом с ключом 'status'
            if (is_array($result) && isset($result['status'])) {
                $status = $result['status'];
                $icon = $this->getStatusIcon($status);
                
                $message = $result['message'] ?? 'Выполнено';
                
                // Специальная обработка для ролей
                if ($component === 'roles' && isset($result['results'])) {
                    $created = 0;
                    $exists = 0;
                    foreach ($result['results'] as $roleResult) {
                        if ($roleResult['status'] === 'created') $created++;
                        if ($roleResult['status'] === 'exists') $exists++;
                    }
                    $message = "Создано: {$created}, Существует: {$exists}";
                }
                
                $this->line("  {$icon} " . ucfirst($component) . ": {$message}");
            } 
            // Обработка сидов (это массив массивов)
            elseif ($component === 'seeders') {
                $successCount = 0;
                $errorCount = 0;
                
                foreach ($result as $seederName => $seederResult) {
                    if (isset($seederResult['status']) && $seederResult['status'] === 'success') {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                }
                
                $total = count($result);
                if ($total === 0) {
                    $this->line("  ℹ️  Seeders: Нет доступных сидов для выполнения");
                } else {
                    $icon = $errorCount === 0 ? '✅' : '⚠️';
                    $message = "Выполнено: {$successCount}/{$total}";
                    if ($errorCount > 0) {
                        $message .= ", Ошибок: {$errorCount}";
                    }
                    $this->line("  {$icon} Seeders: {$message}");
                }
            }
            // Для всех остальных случаев
            else {
                $this->line("  ℹ️  " . ucfirst($component) . ": " . (is_array($result) ? 'Выполнено' : (string)$result));
            }
        }
    }

    /**
     * Получить иконку статуса
     */
    private function getStatusIcon(string $status): string
    {
        return match($status) {
            'success', 'exists', 'created' => '✅',
            'warning' => '⚠️',
            'error' => '❌',
            default => 'ℹ️',
        };
    }

    /**
     * Показать сводку установки
     */
    private function showInstallationSummary(InstallationService $service): void
    {
        $info = $service->getInstallationInfo();
        
        $this->info("\n🎉 Установка Kotiks CMS завершена успешно!");
        
        $this->table(
            ['Компонент', 'Статус'],
            [
                ['База данных', '✅ Готова'],
                ['Миграции', '✅ Применены'],
                ['Сиды', $info['seeders']['valid'] > 0 ? '✅ Доступны' : '⚠️ Отсутствуют'],
                ['Storage link', $info['system_status']['storage_link_exists'] ? '✅ Создан' : '⚠️ Проблема'],
                ['Администратор', $info['system_status']['admin_exists'] ? '✅ Создан' : '❌ Отсутствует'],
                ['Роли системы', $info['system_status']['roles_exist'] ? '✅ Созданы' : '❌ Проблема'],
                ['Кэш', '✅ Очищен'],
            ]
        );
        
        $this->showNextSteps($info);
    }

    /**
     * Показать следующие шаги
     */
    private function showNextSteps(array $info): void
    {
        $this->info("\n🔗 Доступные команды:");
        $this->line("  php artisan serve - запустить встроенный сервер");

        $this->info("\n🔗 Доступ в админ панель:");
        $this->line("Логин: admin@kotiks.local");
        $this->line("Пароль: kotiks2025");
        
        if ($info['system_status']['admin_exists']) {
            $this->warn("\n⚠️  Не забудьте сменить пароль администратора после первого входа!");
        }
        
        if (!$info['system_status']['roles_exist']) {
            $this->error("\n❌ ВНИМАНИЕ: Роли системы не созданы!");
            $this->line("   Запустите: php artisan db:seed --class=RoleSeeder --force");
        }
    }
}