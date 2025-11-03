<?php
// Adjust the route to your local environment.
define( 'ABSPATH', dirname( __DIR__, 4 ) . '/' );

define( 'DB_NAME', 'wordpress_test' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', 'localhost' );
$table_prefix = 'wptests_';

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL',  'admin@example.org' );
define( 'WP_TESTS_TITLE',  'WP Tests' );

if ( ! defined( 'WP_PHP_BINARY' ) ) {
  define( 'WP_PHP_BINARY', 'php' );
}

define( 'WP_DEBUG', true );
