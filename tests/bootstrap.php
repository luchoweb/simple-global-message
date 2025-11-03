<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

$tests_dir = getenv('WP_PHPUNIT__DIR');

if (! $tests_dir) {
    $tests_dir = dirname(__DIR__) . '/vendor/wp-phpunit/wp-phpunit';
} elseif (! str_starts_with($tests_dir, DIRECTORY_SEPARATOR)) {
    $tests_dir = realpath(dirname(__DIR__) . '/' . $tests_dir) ?: $tests_dir;
}

$functions = $tests_dir . '/includes/functions.php';
$bootstrap = $tests_dir . '/includes/bootstrap.php';

if (! file_exists($functions) || ! file_exists($bootstrap)) {
    fwrite(STDERR, "Error: WordPress tests not found.\n");
    fwrite(STDERR, "Looked in: {$tests_dir}\n");
    fwrite(STDERR, "Expected: {$functions}\n");
    fwrite(STDERR, "Tip: run 'php composer.phar install' and ensure 'wp-phpunit/wp-phpunit' exists in vendor/.\n");
    exit(1);
}

require_once $functions;

tests_add_filter('muplugins_loaded', function () {
    require dirname(__DIR__) . '/simple-custom-plugin.php';
});

require_once $bootstrap;
