<?php

return [
    App\Core\Providers\AppServiceProvider::class,
    App\Core\Providers\AuthServiceProvider::class,
    App\Modules\Page\Providers\ViewsProvider::class,
    App\Modules\MediaLib\Providers\ViewsProvider::class,
    App\Modules\Role\Providers\ViewsProvider::class,
    App\Modules\User\Providers\ViewsProvider::class,
    App\Modules\InfoBlock\Providers\ViewsProvider::class,
    App\Modules\ModuleGenerator\Providers\ViewsProvider::class,
    App\Modules\ModuleGenerator\Providers\ModuleMiddlewareServiceProvider::class,
    App\Admin\Providers\ViewsProvider::class,
    App\Modules\Integrator\Providers\ViewsProvider::class,
];
