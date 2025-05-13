<?php

/**
 * WP Activity Logger Pro Dashboard Class
 *
 * @package WP_Activity_Logger_Pro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WPAL_Dashboard {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_menu_styles' ) );
	}

	/**
	 * Add custom menu styles
	 */
	public function enqueue_menu_styles() {
		wp_enqueue_style( 'wpal-menu-styles', WPAL_PLUGIN_URL . 'assets/css/wpal-menu.css', array(), WPAL_VERSION );
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		// Custom SVG icon for better quality
		$menu_icon = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v18"/><rect x="4" y="4" width="16" height="6" rx="2"/><rect x="4" y="14" width="16" height="6" rx="2"/></svg>');
		
		// Main menu
		add_menu_page(
			__('Activity Logger', 'wp-activity-logger-pro'),
			__('Activity Logger', 'wp-activity-logger-pro'),
			'manage_options',
			'wp-activity-logger-pro',
			array($this, 'render_dashboard_page'),
			$menu_icon,
			30
		);
		
		// Organize menu items into logical groups
		// Group 1: Core Features
		add_submenu_page(
			'wp-activity-logger-pro',
			__('Dashboard', 'wp-activity-logger-pro'),
			__('Dashboard', 'wp-activity-logger-pro'),
			'manage_options',
			'wp-activity-logger-pro',
			array($this, 'render_dashboard_page')
		);
		
		add_submenu_page(
			'wp-activity-logger-pro',
			__('Activity Logs', 'wp-activity-logger-pro'),
			__('Activity Logs', 'wp-activity-logger-pro'),
			'manage_options',
			'wp-activity-logger-pro-logs',
			array($this, 'render_logs_page')
		);
		
		// Group 2: Analytics & Insights
		add_submenu_page(
			'wp-activity-logger-pro',
			__('Analytics', 'wp-activity-logger-pro'),
			'<span class="wpal-menu-group">Analytics</span>',
			'manage_options',
			'wp-activity-logger-pro-analytics',
			array($this, 'render_analytics_page')
		);
		
		add_submenu_page(
			'wp-activity-logger-pro',
			__('Search Console', 'wp-activity-logger-pro'),
			'— ' . __('Search Console', 'wp-activity-logger-pro'),
			'manage_options',
			'wp-activity-logger-pro-search-console',
			array($this, 'render_search_console_page')
		);
		
		// Group 3: Security
		add_submenu_page(
			'wp-activity-logger-pro',
			__('Threat Detection', 'wp-activity-logger-pro'),
			'<span class="wpal-menu-group">Security</span>',
			'manage_options',
			'wp-activity-logger-pro-threat-detection',
			array($this, 'render_threat_detection_page')
		);
		
		// Group 4: System
		add_submenu_page(
			'wp-activity-logger-pro',
			__('Server Recommendations', 'wp-activity-logger-pro'),
			'<span class="wpal-menu-group">System</span>',
			'manage_options',
			'wp-activity-logger-pro-server',
			array($this, 'render_server_recommendations_page')
		);
		
		add_submenu_page(
			'wp-activity-logger-pro',
			__('Settings', 'wp-activity-logger-pro'),
			'— ' . __('Settings', 'wp-activity-logger-pro'),
			'manage_options',
			'wp-activity-logger-pro-settings',
			array($this, 'render_settings_page')
		);
		
		add_submenu_page(
			'wp-activity-logger-pro',
			__('Diagnostics', 'wp-activity-logger-pro'),
			'— ' . __('Diagnostics', 'wp-activity-logger-pro'),
			'manage_options',
			'wp-activity-logger-pro-diagnostics',
			array($this, 'render_diagnostics_page')
		);
		
		do_action('wpal_admin_menu');
	}

	/**
	 * Render dashboard page
	 */
	public function render_dashboard_page() {
		include_once WPAL_PLUGIN_DIR . 'templates/dashboard.php';
	}

	/**
	 * Render logs page
	 */
	public function render_logs_page() {
		include_once WPAL_PLUGIN_DIR . 'templates/logs.php';
	}

	/**
	 * Render analytics page
	 */
	public function render_analytics_page() {
		include_once WPAL_PLUGIN_DIR . 'templates/analytics.php';
	}

	/**
	 * Render threat detection page
	 */
	public function render_threat_detection_page() {
		include_once WPAL_PLUGIN_DIR . 'templates/threat-detection.php';
	}

	/**
	 * Render server recommendations page
	 */
	public function render_server_recommendations_page() {
		include_once WPAL_PLUGIN_DIR . 'templates/server-recommendations.php';
	}

	/**
	 * Render search console page
	 */
	public function render_search_console_page() {
		include_once WPAL_PLUGIN_DIR . 'templates/search-console.php';
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		include_once WPAL_PLUGIN_DIR . 'templates/settings.php';
	}

	/**
	 * Render diagnostics page
	 */
	public function render_diagnostics_page() {
		include_once WPAL_PLUGIN_DIR . 'templates/diagnostics.php';
	}
}
