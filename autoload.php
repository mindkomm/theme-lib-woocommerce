<?php

use Theme\Plugin\WooCommerce\Yoast;

if ( function_exists( 'add_action' ) && class_exists( 'WooCommerce' ) ) {
	$yoast = new Yoast();
	$yoast->init();
}
