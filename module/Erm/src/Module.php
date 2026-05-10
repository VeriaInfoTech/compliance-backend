<?php

namespace Erm;

use function date_default_timezone_set;

class Module
{
    public function getConfig(): array
    {
        date_default_timezone_set('Asia/Tehran');

        return include realpath(__DIR__ . '/../config/module.config.php');
    }
}