<?php

require_once '/var/www/dev.site03.loc/vendor/autoload.php';

$app = require_once '/var/www/dev.site03.loc/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
