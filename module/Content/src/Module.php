<?php

namespace Content;

class Module
{
    public function getConfig(): array
    {
        return include realpath(__DIR__ . '/../config/module.config.php');
    }
}
