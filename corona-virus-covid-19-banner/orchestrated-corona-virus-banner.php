<?php
/*
 * Plugin Name: Simple Website Banner
 * Version: 1.8.0.4
 * Description: This is a very simple plugin with a sole purpose of allowing you to inform your visitors of an upcoming event, updated store hours, or other important message you want to display.
 * Author: Orchestrated
 * Author URI: http://www.orchestrated.ca
 * Requires at least: 5.1
 * Tested up to: 6.4.3
 *
 * Text Domain: corona-virus-covid-19-banner
 * Domain Path: /lang
 *
 * @package WordPress
 * @author Orchestrated
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/orchestrated-corona-virus-banner.php' );
require_once( 'includes/orchestrated-corona-virus-banner-settings.php' );

/**
 * Returns the main instance of Orchestrated_Corona_Virus_Banner to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Orchestrated_Corona_Virus_Banner
 */
function Orchestrated_Corona_Virus_Banner () {
	$instance = Orchestrated_Corona_Virus_Banner::instance( __FILE__, '1.8.0.4' );
	if ( is_null( $instance->settings ) ) {
		$instance->settings = Orchestrated_Corona_Virus_Banner_Settings::instance( $instance );
	}

	return $instance;
}

Orchestrated_Corona_Virus_Banner();
