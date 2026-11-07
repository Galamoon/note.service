<?php

// Additional autoloading for CLI and other sub-namespaces
spl_autoload_register(function ($class) {
    // CLI commands
    if (str_starts_with($class, 'Galamoon\\Notes\\Cli\\')) {
        $file = __DIR__ . '/cli/' . str_replace('\\', '/', substr($class, strlen('Galamoon\Notes\Cli\\'))) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
});
