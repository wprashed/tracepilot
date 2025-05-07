<?php
/**
 * Helper functions for WP Activity Logger Pro
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WPAL_Helpers {
    /**
     * Database table name
     */
    public static $db_table;

    /**
     * Initialize helpers
     */
    public static function init() {
        global $wpdb;
        self::$db_table = $wpdb->prefix . 'activity_logs';
    }

    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $table_name = $wpdb->prefix . 'activity_logs';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            time datetime NOT NULL,
            user_id bigint(20) NOT NULL,
            username varchar(60) NOT NULL,
            user_role varchar(255) NOT NULL,
            action varchar(255) NOT NULL,
            object_type varchar(255) NOT NULL,
            object_id bigint(20) NOT NULL,
            object_name varchar(255) NOT NULL,
            context longtext NOT NULL,
            ip varchar(45) NOT NULL,
            browser varchar(255) NOT NULL,
            severity varchar(20) NOT NULL,
            PRIMARY KEY  (id),
            KEY time (time),
            KEY user_id (user_id),
            KEY action (action),
            KEY severity (severity)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Log activity
     */
    public static function log_activity($action, $message = '', $severity = 'info', $object_type = '', $object_id = 0, $object_name = '', $context = array()) {
        global $wpdb;
        
        // Initialize table name
        self::init();
        
        // Get current user
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $username = $current_user->user_login;
        $user_role = !empty($current_user->roles) ? implode(', ', $current_user->roles) : 'guest';
        
        // If no user is logged in
        if ($user_id === 0) {
            $username = 'Guest';
            $user_role = 'guest';
        }
        
        // Get IP address
        $ip = self::get_ip_address();
        
        // Get browser
        $browser = self::get_browser();
        
        // Prepare context
        $context_json = !empty($context) ? json_encode($context) : '{}';
        
        // Insert log
        $wpdb->insert(
            self::$db_table,
            array(
                'time' => current_time('mysql'),
                'user_id' => $user_id,
                'username' => $username,
                'user_role' => $user_role,
                'action' => $action,
                'object_type' => $object_type,
                'object_id' => $object_id,
                'object_name' => $object_name,
                'context' => $context_json,
                'ip' => $ip,
                'browser' => $browser,
                'severity' => $severity
            ),
            array(
                '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s'
            )
        );
        
        // Trigger notification for errors
        if ($severity === 'error') {
            do_action('wpal_error_logged', $action, $message, $context);
        }
        
        return $wpdb->insert_id;
    }

    /**
     * Get IP address
     */
    public static function get_ip_address() {
        $ip = '';
        
        // Check for shared internet/ISP IP
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && self::validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        
        // Check for IPs passing through proxies
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Check if multiple IPs exist in HTTP_X_FORWARDED_FOR
            $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($iplist as $ip) {
                if (self::validate_ip($ip)) {
                    break;
                }
            }
        }
        
        // Check for the remote address
        elseif (!empty($_SERVER['REMOTE_ADDR']) && self::validate_ip($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        // Fallback
        if (empty($ip)) {
            $ip = '0.0.0.0';
        }
        
        return $ip;
    }

    /**
     * Validate IP address
     */
    public static function validate_ip($ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }
        return true;
    }

    /**
     * Get browser
     */
    public static function get_browser() {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        
        if (empty($user_agent)) {
            return 'Unknown';
        }
        
        $browser = 'Unknown';
        
        $browsers = array(
            'Edge' => 'Edge',
            'Firefox' => 'Firefox',
            'Chrome' => 'Chrome',
            'Opera Mini' => 'Opera Mini',
            'Opera' => 'Opera',
            'Safari' => 'Safari',
            'MSIE' => 'Internet Explorer',
            'Trident/7.0' => 'Internet Explorer'
        );
        
        foreach ($browsers as $key => $value) {
            if (strpos($user_agent, $key) !== false) {
                $browser = $value;
                break;
            }
        }
        
        return $browser;
    }

    /**
     * Format datetime
     */
    public static function format_datetime($datetime) {
        $timestamp = strtotime($datetime);
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);
    }

    /**
     * Clean old logs
     */
    public static function clean_old_logs() {
        global $wpdb;
        
        // Initialize table name
        self::init();
        
        // Get retention period
        $settings = get_option('wpal_settings', array());
        $retention_period = isset($settings['retention_period']) ? intval($settings['retention_period']) : 30;
        
        // If retention period is 0, keep logs indefinitely
        if ($retention_period <= 0) {
            return;
        }
        
        // Calculate cutoff date
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-$retention_period days"));
        
        // Delete old logs
        $wpdb->query($wpdb->prepare(
            "DELETE FROM " . self::$db_table . " WHERE time < %s",
            $cutoff_date
        ));
    }
}