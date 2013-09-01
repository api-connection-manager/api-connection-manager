<?php

define( 'WP_TESTS_DIR', getenv( 'WP_TESTS_DIR' ) );
if( !file_exists( WP_TESTS_DIR  ) )
	throw new Exception( 'Unable to find tests directory. Please set the WP_TESTS_DIR environment variable');

require_once getenv( 'WP_TESTS_DIR' ) . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( __FILE__ ) . '/../index.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';