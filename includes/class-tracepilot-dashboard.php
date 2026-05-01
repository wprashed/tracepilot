<?php
/**
 * TracePilot dashboard class.
 *
 * @package TracePilot
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TracePilot_Dashboard {
    /**
     * Constructor.
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('network_admin_menu', array($this, 'add_admin_menu'));
    }

    /**
     * Register admin menu.
     */
    public function add_admin_menu() {
        $menu_icon = 'dashicons-shield-alt';
        $capability = TracePilot_Helpers::get_admin_capability();

        add_menu_page(
            __('TracePilot', 'tracepilot'),
            __('TracePilot', 'tracepilot'),
            $capability,
            'tracepilot',
            array($this, 'render_dashboard_page'),
            $menu_icon,
            30
        );

        add_submenu_page(
            'tracepilot',
            __('Dashboard', 'tracepilot'),
            __('Dashboard', 'tracepilot'),
            $capability,
            'tracepilot',
            array($this, 'render_dashboard_page')
        );

        add_submenu_page(
            'tracepilot',
            __('Activity Logs', 'tracepilot'),
            __('Activity Logs', 'tracepilot'),
            $capability,
            'tracepilot-logs',
            array($this, 'render_logs_page')
        );

        add_submenu_page(
            'tracepilot',
            __('Analytics', 'tracepilot'),
            __('Analytics', 'tracepilot'),
            $capability,
            'tracepilot-analytics',
            array($this, 'render_analytics_page')
        );

        add_submenu_page(
            'tracepilot',
            __('Threat Detection', 'tracepilot'),
            __('Threat Detection', 'tracepilot'),
            $capability,
            'tracepilot-threat-detection',
            array($this, 'render_threat_detection_page')
        );

        add_submenu_page(
            'tracepilot',
            __('Server Recommendations', 'tracepilot'),
            __('Server Recommendations', 'tracepilot'),
            $capability,
            'tracepilot-server',
            array($this, 'render_server_recommendations_page')
        );

        add_submenu_page(
            'tracepilot',
            __('Diagnostics', 'tracepilot'),
            __('Diagnostics', 'tracepilot'),
            $capability,
            'tracepilot-diagnostics',
            array($this, 'render_diagnostics_page')
        );

        add_submenu_page(
            'tracepilot',
            __('Search Console', 'tracepilot'),
            __('Search Console', 'tracepilot'),
            $capability,
            'tracepilot-search-console',
            array($this, 'render_search_console_page')
        );
    }

    /**
     * Render dashboard page.
     */
    public function render_dashboard_page() {
        include TracePilot_PLUGIN_DIR . 'templates/tracepilot-dashboard.php';
    }

    /**
     * Render logs page.
     */
    public function render_logs_page() {
        include TracePilot_PLUGIN_DIR . 'templates/tracepilot-logs.php';
    }

    /**
     * Render analytics page.
     */
    public function render_analytics_page() {
        include TracePilot_PLUGIN_DIR . 'templates/tracepilot-analytics.php';
    }

    /**
     * Render threat detection page.
     */
    public function render_threat_detection_page() {
        include TracePilot_PLUGIN_DIR . 'templates/tracepilot-threat-detection.php';
    }

    /**
     * Render server recommendations page.
     */
    public function render_server_recommendations_page() {
        include TracePilot_PLUGIN_DIR . 'templates/tracepilot-server-recommendations.php';
    }

    /**
     * Render search console page.
     */
    public function render_search_console_page() {
        include TracePilot_PLUGIN_DIR . 'templates/tracepilot-search-console.php';
    }

    /**
     * Render diagnostics page.
     */
    public function render_diagnostics_page() {
        include TracePilot_PLUGIN_DIR . 'templates/tracepilot-diagnostics.php';
    }
}
