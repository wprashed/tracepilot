<?php
/**
 * Helper functions for WP Activity Logger Pro
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WPAL_Helpers {
    public static $db_table;
    
    /**
     * Initialize the class
     */
    public static function init() {
        global $wpdb;
        self::$db_table = $wpdb->prefix . 'wpal_logs';
    }
    
    /**
     * Format datetime
     *
     * @param string $datetime
     * @return string
     */
    public static function format_datetime($datetime) {
        $timestamp = strtotime($datetime);
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);
    }
    
    /**
     * Get client IP address
     *
     * @return string
     */
    public static function get_client_ip() {
        // Check for shared internet/ISP IP
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && self::validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        
        // Check for IPs passing through proxies
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Check if multiple IPs exist in HTTP_X_FORWARDED_FOR
            $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($iplist as $ip) {
                if (self::validate_ip(trim($ip))) {
                    return trim($ip);
                }
            }
        }
        
        // Check for the remote address
        if (!empty($_SERVER['REMOTE_ADDR']) && self::validate_ip($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        
        // Fallback to a default IP if none found
        return '127.0.0.1';
    }
    
    /**
     * Validate IP address
     *
     * @param string $ip
     * @return bool
     */
    public static function validate_ip($ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }
        return true;
    }
    
    /**
     * Get user browser
     *
     * @return string
     */
    public static function get_browser() {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        
        if (empty($user_agent)) {
            return 'Unknown';
        }
        
        $browser = 'Unknown';
        
        if (preg_match('/MSIE/i', $user_agent) && !preg_match('/Opera/i', $user_agent)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Trident/i', $user_agent)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Edge/i', $user_agent)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/Firefox/i', $user_agent)) {
            $browser = 'Mozilla Firefox';
        } elseif (preg_match('/Chrome/i', $user_agent) && !preg_match('/Edge/i', $user_agent)) {
            $browser = 'Google Chrome';
        } elseif (preg_match('/Safari/i', $user_agent) && !preg_match('/Chrome/i', $user_agent)) {
            $browser = 'Apple Safari';
        } elseif (preg_match('/Opera/i', $user_agent)) {
            $browser = 'Opera';
        } elseif (preg_match('/Netscape/i', $user_agent)) {
            $browser = 'Netscape';
        }
        
        return $browser;
    }
    
    /**
     * Get user OS
     *
     * @return string
     */
    public static function get_os() {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        
        if (empty($user_agent)) {
            return 'Unknown';
        }
        
        $os_platform = 'Unknown';
        
        $os_array = [
            '/windows nt 10/i'      => 'Windows 10',
            '/windows nt 6.3/i'     => 'Windows 8.1',
            '/windows nt 6.2/i'     => 'Windows 8',
            '/windows nt 6.1/i'     => 'Windows 7',
            '/windows nt 6.0/i'     => 'Windows Vista',
            '/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     => 'Windows XP',
            '/windows xp/i'         => 'Windows XP',
            '/windows nt 5.0/i'     => 'Windows 2000',
            '/windows me/i'         => 'Windows ME',
            '/win98/i'              => 'Windows 98',
            '/win95/i'              => 'Windows 95',
            '/win16/i'              => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i'        => 'Mac OS 9',
            '/linux/i'              => 'Linux',
            '/ubuntu/i'             => 'Ubuntu',
            '/iphone/i'             => 'iPhone',
            '/ipod/i'               => 'iPod',
            '/ipad/i'               => 'iPad',
            '/android/i'            => 'Android',
            '/blackberry/i'         => 'BlackBerry',
            '/webos/i'              => 'Mobile'
        ];
        
        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $os_platform = $value;
                break;
            }
        }
        
        return $os_platform;
    }
    
    /**
     * Get current user info
     *
     * @return array
     */
    public static function get_current_user_info() {
        $current_user = wp_get_current_user();
        
        if (empty($current_user) || !$current_user->exists()) {
            return [
                'id' => 0,
                'username' => 'Guest',
                'role' => 'Guest'
            ];
        }
        
        $user_roles = $current_user->roles;
        $user_role = !empty($user_roles) ? ucfirst($user_roles[0]) : 'Unknown';
        
        return [
            'id' => $current_user->ID,
            'username' => $current_user->user_login,
            'role' => $user_role
        ];
    }
    
    /**
     * Log activity
     *
     * @param string $action
     * @param string $severity
     * @param array $context
     * @return bool
     */
    public static function log_activity($action, $severity = 'info', $context = []) {
        global $wpdb;
        
        // Initialize if not already
        if (empty(self::$db_table)) {
            self::init();
        }
        
        // Get user info
        $user_info = self::get_current_user_info();
        
        // Get IP and browser
        $ip = self::get_client_ip();
        $browser = self::get_browser();
        
        // Prepare context
        $context_json = !empty($context) ? json_encode($context) : '{}';
        
        // Insert log
        $result = $wpdb->insert(
            self::$db_table,
            [
                'user_id' => $user_info['id'],
                'username' => $user_info['username'],
                'user_role' => $user_info['role'],
                'action' => $action,
                'severity' => $severity,
                'ip' => $ip,
                'browser' => $browser,
                'context' => $context_json,
                'time' => current_time('mysql')
            ],
            [
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            ]
        );
        
        return $result !== false;
    }
    
    /**
     * Get log by ID
     *
     * @param int $id
     * @return object|null
     */
    public static function get_log($id) {
        global $wpdb;
        
        // Initialize if not already
        if (empty(self::$db_table)) {
            self::init();
        }
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM " . self::$db_table . " WHERE id = %d", $id));
    }
    
    /**
     * Delete log by ID
     *
     * @param int $id
     * @return bool
     */
    public static function delete_log($id) {
        global $wpdb;
        
        // Initialize if not already
        if (empty(self::$db_table)) {
            self::init();
        }
        
        $result = $wpdb->delete(
            self::$db_table,
            ['id' => $id],
            ['%d']
        );
        
        return $result !== false;
    }
    
    /**
     * Delete all logs
     *
     * @return bool
     */
    public static function delete_all_logs() {
        global $wpdb;
        
        // Initialize if not already
        if (empty(self::$db_table)) {
            self::init();
        }
        
        $result = $wpdb->query("TRUNCATE TABLE " . self::$db_table);
        
        return $result !== false;
    }
    
    /**
     * Delete old logs
     *
     * @param int $days
     * @return bool
     */
    public static function delete_old_logs($days) {
        global $wpdb;
        
        // Initialize if not already
        if (empty(self::$db_table)) {
            self::init();
        }
        
        $date = date('Y-m-d H:i:s', strtotime('-' . $days . ' days'));
        
        $result = $wpdb->query($wpdb->prepare("DELETE FROM " . self::$db_table . " WHERE time < %s", $date));
        
        return $result !== false;
    }
    
    /**
     * Get logs count
     *
     * @return int
     */
    public static function get_logs_count() {
        global $wpdb;
        
        // Initialize if not already
        if (empty(self::$db_table)) {
            self::init();
        }
        
        return $wpdb->get_var("SELECT COUNT(*) FROM " . self::$db_table);
    }
    
    /**
     * Get logs by user
     *
     * @param int $user_id
     * @param int $limit
     * @return array
     */
    public static function get_logs_by_user($user_id, $limit = 10) {
        global $wpdb;
        
        // Initialize if not already
        if (empty(self::$db_table)) {
            self::init();
        }
        
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM " . self::$db_table . " WHERE user_id = %d ORDER BY time DESC LIMIT %d", $user_id, $limit));
    }
    
    /**
     * Get logs by severity
     *
     * @param string $severity
     * @param int $limit
     * @return array
     */
    public static function get_logs_by_severity($severity, $limit = 10) {
        global $wpdb;
        
        // Initialize if not already
        if (empty(self::$db_table)) {
            self::init();
        }
        
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM " . self::$db_table . " WHERE severity = %s ORDER BY time DESC LIMIT %d", $severity, $limit));
    }
    
    /**
     * Get logs by action
     *
     * @param string $action
     * @param int $limit
     * @return array
     */
    public static function get_logs_by_action($action, $limit = 10) {
        global $wpdb;
        
        // Initialize if not already
        if (empty(self::$db_table)) {
            self::init();
        }
        
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM " . self::$db_table . " WHERE action = %s ORDER BY time DESC LIMIT %d", $action, $limit));
    }
    
    /**
     * Get logs by IP
     *
     * @param string $ip
     * @param int $limit
     * @return array
     */
    public static function get_logs_by_ip($ip, $limit = 10) {
        global $wpdb;
        
        // Initialize if not already
        if (empty(self::$db_table)) {
            self::init();
        }
        
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM " . self::$db_table . " WHERE ip = %s ORDER BY time DESC LIMIT %d", $ip, $limit));
    }
    
    /**
     * Get top users
     *
     * @param int $limit
     * @return array
     */
    public static function get_top_users($limit = 5) {
        global $wpdb;
        
        // Initialize if not already
        if (empty(self::$db_table)) {
            self::init();
        }
        
        return $wpdb->get_results($wpdb->prepare("SELECT username, COUNT(*) as count FROM " . self::$db_table . " GROUP BY username ORDER BY count DESC LIMIT %d", $limit));
    }
    
    /**
     * Get severity breakdown
     *
     * @return array
     */
    public static function get_severity_breakdown() {
        global $wpdb;
        
        // Initialize if not already
        if (empty(self::$db_table)) {
            self::init();
        }
        
        return $wpdb->get_results("SELECT severity, COUNT(*) as count FROM " . self::$db_table . " GROUP BY severity ORDER BY count DESC");
    }
    
    /**
     * Get activity over time
     *
     * @param int $days
     * @return array
     */
    public static function get_activity_over_time($days = 7) {
        global $wpdb;
        
        // Initialize if not already
        if (empty(self::$db_table)) {
            self::init();
        }
        
        $results = $wpdb->get_results($wpdb->prepare("SELECT DATE(time) as date, COUNT(*) as count FROM " . self::$db_table . " WHERE time >= DATE_SUB(NOW(), INTERVAL %d DAY) GROUP BY DATE(time) ORDER BY date ASC", $days));
        
        // Fill in missing dates
        $data = [];
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        
        $current_date = $start_date;
        while ($current_date <= $end_date) {
            $data[$current_date] = 0;
            $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
        }
        
        foreach ($results as $result) {
            $data[$result->date] = (int) $result->count;
        }
        
        return $data;
    }
}