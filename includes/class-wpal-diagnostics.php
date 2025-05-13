<?php
/**
 * WP Activity Logger Diagnostics
 *
 * @package WP Activity Logger
 * @since 1.1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit();
}

class WPAL_Diagnostics {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_wpal_run_diagnostics', array($this, 'ajax_run_diagnostics'));
    }

    /**
     * AJAX run diagnostics
     */
    public function ajax_run_diagnostics() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpal_nonce')) {
            wp_send_json_error(array('message' => __('Invalid security token.', 'wp-activity-logger-pro')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'wp-activity-logger-pro')));
        }
        
        // Run diagnostics
        $results = $this->run_diagnostics();
        
        wp_send_json_success($results);
    }
    
    /**
     * Run diagnostics
     */
    public function run_diagnostics() {
        global $wpdb;
        
        $results = array(
            'plugin_status' => array(
                'title' => __('Plugin Status', 'wp-activity-logger-pro'),
                'items' => array()
            ),
            'database_status' => array(
                'title' => __('Database Status', 'wp-activity-logger-pro'),
                'items' => array()
            ),
            'hooks_status' => array(
                'title' => __('Hooks Status', 'wp-activity-logger-pro'),
                'items' => array()
            ),
            'server_status' => array(
                'title' => __('Server Status', 'wp-activity-logger-pro'),
                'items' => array()
            )
        );
        
        // Check if plugin is active
        $results['plugin_status']['items'][] = array(
            'name' => __('Plugin Active', 'wp-activity-logger-pro'),
            'status' => is_plugin_active(WPAL_PLUGIN_BASENAME) ? 'success' : 'error',
            'message' => is_plugin_active(WPAL_PLUGIN_BASENAME) ? 
                __('Plugin is active.', 'wp-activity-logger-pro') : 
                __('Plugin is not active. Please activate the plugin.', 'wp-activity-logger-pro')
        );
        
        // Check plugin version
        $results['plugin_status']['items'][] = array(
            'name' => __('Plugin Version', 'wp-activity-logger-pro'),
            'status' => 'info',
            'message' => sprintf(__('Plugin version: %s', 'wp-activity-logger-pro'), WPAL_VERSION)
        );
        
        // Check if tables exist
        WPAL_Helpers::init();
        $table_name = WPAL_Helpers::$db_table;
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        $results['database_status']['items'][] = array(
            'name' => __('Logs Table', 'wp-activity-logger-pro'),
            'status' => $table_exists ? 'success' : 'error',
            'message' => $table_exists ? 
                __('Logs table exists.', 'wp-activity-logger-pro') : 
                __('Logs table does not exist. Please deactivate and reactivate the plugin.', 'wp-activity-logger-pro')
        );
        
        // Check if threats table exists
        $threats_table = $wpdb->prefix . 'wpal_threats';
        $threats_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$threats_table'") == $threats_table;
        
        $results['database_status']['items'][] = array(
            'name' => __('Threats Table', 'wp-activity-logger-pro'),
            'status' => $threats_table_exists ? 'success' : 'error',
            'message' => $threats_table_exists ? 
                __('Threats table exists.', 'wp-activity-logger-pro') : 
                __('Threats table does not exist. Please deactivate and reactivate the plugin.', 'wp-activity-logger-pro')
        );
        
        // Check admin menu hook
        $results['hooks_status']['items'][] = array(
            'name' => __('Admin Menu Hook', 'wp-activity-logger-pro'),
            'status' => has_action('admin_menu', array(wp_activity_logger_pro()->dashboard, 'add_admin_menu')) ? 'success' : 'error',
            'message' => has_action('admin_menu', array(wp_activity_logger_pro()->dashboard, 'add_admin_menu')) ? 
                __('Admin menu hook is registered.', 'wp-activity-logger-pro') : 
                __('Admin menu hook is not registered. This is why the menu is not showing.', 'wp-activity-logger-pro')
        );
        
        // Check if user has required capability
        $results['hooks_status']['items'][] = array(
            'name' => __('User Capability', 'wp-activity-logger-pro'),
            'status' => current_user_can('manage_options') ? 'success' : 'error',
            'message' => current_user_can('manage_options') ? 
                __('User has the required capability.', 'wp-activity-logger-pro') : 
                __('User does not have the required capability (manage_options).', 'wp-activity-logger-pro')
        );
        
        // Check PHP version
        $php_version = phpversion();
        $php_version_ok = version_compare($php_version, '7.0', '>=');
        
        $results['server_status']['items'][] = array(
            'name' => __('PHP Version', 'wp-activity-logger-pro'),
            'status' => $php_version_ok ? 'success' : 'error',
            'message' => $php_version_ok ? 
                sprintf(__('PHP version %s is compatible.', 'wp-activity-logger-pro'), $php_version) : 
                sprintf(__('PHP version %s is not compatible. Minimum required version is 7.0.', 'wp-activity-logger-pro'), $php_version)
        );
        
        // Check WordPress version
        global $wp_version;
        $wp_version_ok = version_compare($wp_version, '5.0', '>=');
        
        $results['server_status']['items'][] = array(
            'name' => __('WordPress Version', 'wp-activity-logger-pro'),
            'status' => $wp_version_ok ? 'success' : 'error',
            'message' => $wp_version_ok ? 
                sprintf(__('WordPress version %s is compatible.', 'wp-activity-logger-pro'), $wp_version) : 
                sprintf(__('WordPress version %s is not compatible. Minimum required version is 5.0.', 'wp-activity-logger-pro'), $wp_version)
        );
        
        // Check for PHP errors
        $error_log = ini_get('error_log');
        $error_log_readable = file_exists($error_log) && is_readable($error_log);
        
        $results['server_status']['items'][] = array(
            'name' => __('PHP Error Log', 'wp-activity-logger-pro'),
            'status' => 'info',
            'message' => $error_log_readable ? 
                sprintf(__('PHP error log is at %s.', 'wp-activity-logger-pro'), $error_log) : 
                __('PHP error log is not readable or does not exist.', 'wp-activity-logger-pro')
        );
        
        return $results;
    }
    
    /**
     * Fix common issues
     */
    public function fix_common_issues() {
        // Re-register admin menu
        if (method_exists(wp_activity_logger_pro()->dashboard, 'add_admin_menu')) {
            add_action('admin_menu', array(wp_activity_logger_pro()->dashboard, 'add_admin_menu'));
        }
        
        // Recreate tables if needed
        WPAL_Helpers::create_tables();
        
        // Create threats table if needed
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpal_threats';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            time datetime NOT NULL,
            type varchar(50) NOT NULL,
            severity varchar(20) NOT NULL,
            description text NOT NULL,
            context longtext,
            status varchar(20) NOT NULL DEFAULT 'new',
            PRIMARY KEY  (id),
            KEY time (time),
            KEY type (type),
            KEY severity (severity),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        return true;
    }
}
