<?php
spl_autoload_register('listar_plugin_autoloader');

function listar_plugin_autoloader($class)
{
    $namespace = 'ListarWP\Plugin';

    if (strpos($class, $namespace) !== 0) {
        return;
    }

    $class = str_replace($namespace, '', $class);

    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    $path = LISTAR_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $class;

    if (file_exists($path)) {

        require_once($path);
    } else {

    }
}
