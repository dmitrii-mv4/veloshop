<?php
namespace App\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class InstallKotiksCMSCommand extends Command
{
    /**
     * Сигнатура команды (то, что вы будете писать в консоли).
     * @var string
     */
    protected $signature = 'kotiks:install';

    /**
     * Описание команды, которое показывается в php artisan list.
     * @var string
     */
    protected $description = 'Установка Kotiks CMS: миграции, ключ, символическая ссылка и создание администратора.';

    /**
     * Логика выполнения команды.
     */
    public function handle(): int
    {
        $this->info('🚀 Начинаем установку Kotiks CMS...');

        // 1. Генерация ключа приложения
        $this->info('🔑 Генерируем ключ приложения...');
        if (empty(config('app.key'))) {
            Artisan::call('key:generate', ['--force' => true]);
            $this->info('✅ Ключ приложения сгенерирован.');
        } else {
            $this->info('ℹ️ Ключ приложения уже существует, пропускаем.');
        }

        // 2. Запуск миграций (включая миграции ролей из app/Role/database/migrations)
        $this->info('📦 Выполняем миграции базы данных...');
        try {
            // Все миграции будут выполнены, включая зарегистрированные в AppServiceProvider
            Artisan::call('migrate', ['--force' => true]);
            $this->info('✅ Миграции выполнены успешно.');
        } catch (\Exception $e) {
            $this->error('❌ Ошибка при выполнении миграций: ' . $e->getMessage());
            return self::FAILURE;
        }

        // 3. ОБЯЗАТЕЛЬНО: Создание базовых ролей через сидеры
        $this->info('👑 Создаем базовые роли и разрешения...');
        try {
            // Проверяем существование классов сидов
            $roleSeederClass = 'App\Modules\Role\database\seeders\RoleSeeder';
            $permissionSeederClass = 'App\Modules\Role\database\seeders\RolePermissionSeeder';
            
            if (!class_exists($roleSeederClass)) {
                throw new \Exception("Класс {$roleSeederClass} не найден. Проверьте путь и namespace.");
            }
            
            if (!class_exists($permissionSeederClass)) {
                throw new \Exception("Класс {$permissionSeederClass} не найден. Проверьте путь и namespace.");
            }
            
            // Сначала создаем роли
            Artisan::call('db:seed', [
                '--class' => $roleSeederClass,
                '--force' => true
            ]);
            
            // Затем назначаем разрешения ролям
            Artisan::call('db:seed', [
                '--class' => $permissionSeederClass,
                '--force' => true
            ]);
            
            $this->info('✅ Роли и разрешения созданы успешно.');
        } catch (\Exception $e) {
            $this->error('❌ Ошибка при создании ролей: ' . $e->getMessage());
            return self::FAILURE;
        }

        // 4. Создание символьной ссылки storage
        $this->info('🔗 Создаем символьную ссылку storage...');
        try {
            Artisan::call('storage:link');
            $this->info('✅ Символьная ссылка создана.');
        } catch (\Exception $e) {
            $this->warn('⚠️ Не удалось создать символьную ссылку: ' . $e->getMessage());
        }

        // 5. Интерактивное создание администратора (ТЕПЕРЬ роль уже существует)
        $this->info('👤 Создаем администратора...');
        
        if (User::where('email', 'admin@kotiks.local')->exists()) {
            $this->info('ℹ️ Учетная запись администратора уже существует.');
        } else {
            if ($this->confirm('Создать администратора с данными по умолчанию?', true)) {
                $password = 'kotiks2025';
                User::create([
                    'name' => 'Администратор',
                    'email' => 'admin@kotiks.local',
                    'email_verified_at' => now(),
                    'role_id' => 1, // Теперь роль с ID=1 точно существует
                    'password' => Hash::make($password),
                    'is_system' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->info('✅ Администратор создан.');
                $this->warn("   Логин: admin@kotiks.local");
                $this->warn("   Пароль: {$password}");
                $this->warn('⚠️  Смените пароль после первого входа!');
            } else {
                // Интерактивный ввод данных
                $name = $this->ask('Введите имя администратора', 'Администратор');
                $email = $this->ask('Введите email', 'admin@kotiks.local');
                $password = $this->secret('Введите пароль (не менее 8 символов)');
                
                while (strlen($password) < 8) {
                    $this->error('Пароль должен содержать минимум 8 символов');
                    $password = $this->secret('Введите пароль еще раз:');
                }
                
                $passwordConfirm = $this->secret('Повторите пароль:');
                if ($password !== $passwordConfirm) {
                    $this->error('Пароли не совпадают!');
                    return self::FAILURE;
                }
                
                User::create([
                    'name' => $name,
                    'email' => $email,
                    'email_verified_at' => now(),
                    'role_id' => 1,
                    'password' => Hash::make($password),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->info("✅ Администратор {$name} создан.");
            }
        }

        // 6. Опционально: дополнительные сидеры
        if ($this->confirm('Запустить другие начальные заполнения базы данных (seeders)?', false)) {
            $this->info('🌱 Выполняем остальные seeders...');
            Artisan::call('db:seed', ['--force' => true]);
            $this->info('✅ Seeders выполнены.');
        }

        $this->newLine();
        $this->info('🎉 Установка Kotiks CMS завершена успешно!');
        
        return self::SUCCESS;
    }
}