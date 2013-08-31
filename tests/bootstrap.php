<?php
session_start();

$path = "/var/lib/jenkins/jobs/wordpress-tests";
define( 'WP_TESTS_DIR', $path );
if( !file_exists( WP_TESTS_DIR  ) )
	throw new Exception( 'Unable to find tests directory. Please set the WP_TESTS_DIR environment variable');
require_once $path . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( __FILE__ ) . '/../index.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $path . '/includes/bootstrap.php';
