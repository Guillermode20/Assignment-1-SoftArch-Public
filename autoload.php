<?php
declare(strict_types=1);

namespace Api;

/**
 * Registers an autoloader function to load classes in the 'Api' namespace.
 *
 * This autoloader dynamically includes PHP files based on the class name,
 * ensuring that the class is loaded when it's first used.
 */
spl_autoload_register(function (string $class): void {
    // Construct the file path based on the class name and namespace.
    $file = __DIR__ . '/' . strtolower(str_replace('\\', '/', $class)) . '.php';

    // Check if the file exists before attempting to include it.
    if (file_exists($file)) {
        require_once $file; // Include the class file.
    }
});