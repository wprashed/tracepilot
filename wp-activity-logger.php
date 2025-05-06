<?php
/**
* Plugin Name: WP Activity Logger Pro
* Plugin URI: https://example.com/wp-activity-logger-pro
* Description: Advanced activity logging for WordPress
* Version: 1.2.0
* Author: Your Name
* Author URI: https://example.com
* Text Domain: wp-activity-logger-pro
* Domain Path: /languages
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WP_Activity_Logger_Pro {
    /**
     * Plugin instance
     *
     * @var WP_Activity_Logger_Pro
     */
    private static $instance;

    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '1.2.0';

    /**
     * Plugin constructor
     */
    public function __construct() {
        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        // Initialize the plugin
        add_action('plugins_loaded', array($this, 'init'));
        
        // Register activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));

        // Register deactivation hook
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    /**
     * Get plugin instance
     *
     * @return WP_Activity_Logger_Pro
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Define constants
     */
    private function define_constants() {
        define('WPAL_VERSION', $this->version);
        define('WPAL_PLUGIN_FILE', __FILE__);
        define('WPAL_PLUGIN_DIR', plugin_dir_path(__FILE__));
        define('WPAL_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('WPAL_PLUGIN_BASENAME', plugin_basename(__FILE__));
    }

    /**
     * Include required files
     */
    private function includes() {
        // Include helper functions
        require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-helpers.php';
        
        // Include dashboard class - Make sure this file exists
        require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-dashboard.php';
        
        // Include API class
        require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-api.php';
        
        // Include notifications class
        require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-notifications.php';
        
        // Include export class
        require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-export.php';
        
        // Include tracker class
        require_once WPAL_PLUGIN_DIR . 'includes/class-wpal-tracker.php';
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Load textdomain - MOVED HERE from constructor to fix the "too early" notice
        $this->load_textdomain();
        
        // Make sure the WPAL_Dashboard class exists before trying to use it
        if (class_exists('WPAL_Dashboard')) {
            // Initialize dashboard
            WPAL_Dashboard::init();
        }
        
        // Initialize API
        if (class_exists('WPAL_API')) {
            WPAL_API::init();
        }
        
        // Initialize notifications
        if (class_exists('WPAL_Notifications')) {
            WPAL_Notifications::init();
        }
        
        // Initialize tracker
        if (class_exists('WPAL_Tracker')) {
            WPAL_Tracker::init();
        }
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Add admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Add admin notices
        add_action('admin_notices', array($this, 'admin_notices'));
    }

    /**
     * Load textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('wp-activity-logger-pro', false, dirname(WPAL_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create database table
        $this->create_table();
        
        // Create log directory
        $this->create_log_directory();
        
        // Set default options
        $this->set_default_options();
        
        // Add activation timestamp
        update_option('wpal_activated', time());
        
        // Redirect to dashboard
        set_transient('wpal_activation_redirect', true, 30);
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled hooks
        wp_clear_scheduled_hook('wpal_daily_cleanup');
        wp_clear_scheduled_hook('wpal_daily_report');
        wp_clear_scheduled_hook('wpal_scheduled_export');
    }

    /**
     * Create database table
     */
    private function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wpal_logs';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            username varchar(60) NOT NULL,
            user_role varchar(60) NOT NULL,
            action varchar(255) NOT NULL,
            severity varchar(20) NOT NULL,
            ip varchar(45) NOT NULL,
            browser varchar(255) NOT NULL,
            context longtext NOT NULL,
            time datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY severity (severity),
            KEY time (time)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create log directory
     */
    private function create_log_directory() {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/wpal-logs';
        
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
        
        // Create .htaccess file to protect logs
        $htaccess_file = $log_dir . '/.htaccess';
        
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "# Deny access to all files
<Files *>
    Order Allow,Deny
    Deny from all
</Files>";
            
            file_put_contents($htaccess_file, $htaccess_content);
        }
        
        // Create index.php file
        $index_file = $log_dir . '/index.php';
        
        if (!file_exists($index_file)) {
            $index_content = "<?php
// Silence is golden.";
            file_put_contents($index_file, $index_content);
        }
    }

    /**
     * Set default options
     */
    private function set_default_options() {
        // General settings
        add_option('wpal_retention_days', 30);
        add_option('wpal_log_storage', 'database');
        add_option('wpal_track_404_errors', 1);
        add_option('wpal_track_api_requests', 0);
        
        // Notification settings
        add_option('wpal_notification_email', get_option('admin_email'));
        add_option('wpal_notification_events', array('failed_login', 'plugin_activation', 'plugin_deactivation'));
        add_option('wpal_daily_report', 0);
        
        // Schedule daily cleanup
        if (!wp_next_scheduled('wpal_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'wpal_daily_cleanup');
        }
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Activity Logs', 'wp-activity-logger-pro'),
            __('Activity Logs', 'wp-activity-logger-pro'),
            'manage_options',
            'wp-activity-logger-pro',
            array($this, 'logs_page'),
            'dashicons-list-view',
            30
        );
        
        // Logs submenu
        add_submenu_page(
            'wp-activity-logger-pro',
            __('View Logs', 'wp-activity-logger-pro'),
            __('View Logs', 'wp-activity-logger-pro'),
            'manage_options',
            'wp-activity-logger-pro',
            array($this, 'logs_page')
        );
        
        // Dashboard submenu
        add_submenu_page(
            'wp-activity-logger-pro',
            __('Dashboard', 'wp-activity-logger-pro'),
            __('Dashboard', 'wp-activity-logger-pro'),
            'manage_options',
            'wp-activity-logger-pro-dashboard',
            array($this, 'dashboard_page')
        );
        
        // Export submenu
        add_submenu_page(
            'wp-activity-logger-pro',
            __('Export', 'wp-activity-logger-pro'),
            __('Export', 'wp-activity-logger-pro'),
            'manage_options',
            'wp-activity-logger-pro-export',
            array($this, 'export_page')
        );
        
        // Settings submenu
        add_submenu_page(
            'wp-activity-logger-pro',
            __('Settings', 'wp-activity-logger-pro'),
            __('Settings', 'wp-activity-logger-pro'),
            'manage_options',
            'wp-activity-logger-pro-settings',
            array($this, 'settings_page')
        );
        
        // Diagnostics submenu
        add_submenu_page(
            'wp-activity-logger-pro',
            __('Diagnostics', 'wp-activity-logger-pro'),
            __('Diagnostics', 'wp-activity-logger-pro'),
            'manage_options',
            'wp-activity-logger-pro-diagnostics',
            array($this, 'diagnostics_page')
        );
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'wp-activity-logger-pro') === false) {
            return;
        }
        
        // Enqueue styles
        wp_enqueue_style('wpal-admin', WPAL_PLUGIN_URL . 'assets/css/wpal-admin.css', array(), WPAL_VERSION);
        
        // Enqueue scripts
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js', array(), '3.7.1', true);
        wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.1.3', true);
        wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js', array('jquery'), '1.11.5', true);
        wp_enqueue_script('wpal-admin', WPAL_PLUGIN_URL . 'assets/js/wpal-admin.js', array('jquery', 'chart-js', 'bootstrap-js', 'datatables-js'), WPAL_VERSION, true);
        
        // Localize script
        wp_localize_script('wpal-admin', 'wpal_admin_vars', array(
            'nonce' => wp_create_nonce('wpal_nonce'),
            'delete_nonce' => wp_create_nonce('wpal_delete_nonce'),
            'settings_nonce' => wp_create_nonce('wpal_settings_nonce'),
            'confirm_delete' => __('Are you sure you want to delete this log entry?', 'wp-activity-logger-pro'),
            'confirm_delete_all' => __('Are you sure you want to delete all logs? This action cannot be undone.', 'wp-activity-logger-pro')
        ));
    }

    /**
     * Admin notices
     */
    public function admin_notices() {
        // Check for activation redirect
        if (get_transient('wpal_activation_redirect')) {
            delete_transient('wpal_activation_redirect');
            
            // Only redirect if not already on the dashboard page
            if (!isset($_GET['page']) || $_GET['page'] !== 'wp-activity-logger-pro-dashboard') {
                wp_redirect(admin_url('admin.php?page=wp-activity-logger-pro-dashboard'));
                exit;
            }
        }
    }

    /**
     * Logs page
     */
    public function logs_page() {
        require_once WPAL_PLUGIN_DIR . 'templates/logs.php';
    }

    /**
     * Dashboard page
     */
    public function dashboard_page() {
        require_once WPAL_PLUGIN_DIR . 'templates/dashboard.php';
    }

    /**
     * Export page
     */
    public function export_page() {
        require_once WPAL_PLUGIN_DIR . 'templates/export.php';
    }

    /**
     * Settings page
     */
    public function settings_page() {
        require_once WPAL_PLUGIN_DIR . 'templates/settings.php';
    }

    /**
     * Diagnostics page
     */
    public function diagnostics_page() {
        require_once WPAL_PLUGIN_DIR . 'templates/diagnostics.php';
    }
}

// Initialize the plugin
function wpal_init() {
    return WP_Activity_Logger_Pro::get_instance();
}

// Start the plugin
wpal_init();