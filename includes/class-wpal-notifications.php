<?php
/**
 * Notifications functionality for WP Activity Logger Pro
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WPAL_Notifications {
    /**
     * Initialize the class
     */
    public static function init() {
        // Hook into log creation to send notifications
        add_action('wpal_log_created', [__CLASS__, 'process_notification'], 10, 1);
        
        // Schedule daily report if enabled
        if (get_option('wpal_daily_report', false) && !wp_next_scheduled('wpal_daily_report')) {
            wp_schedule_event(time(), 'daily', 'wpal_daily_report');
        }
    }
    
    /**
     * Process notification for a log entry
     */
    public static function process_notification($log) {
        // Check if notifications are enabled for this event type
        $notification_events = get_option('wpal_notification_events', []);
        $should_notify = false;
        
        // Determine if this log should trigger a notification
        if ($log->severity === 'error') {
            $should_notify = true;
        } elseif ($log->severity === 'warning' && in_array('warning', $notification_events)) {
            $should_notify = true;
        } else {
            // Check specific events
            if (strpos($log->action, 'Login failed') !== false && in_array('login_failed', $notification_events)) {
                $should_notify = true;
            } elseif (strpos($log->action, 'Plugin activated') !== false && in_array('plugin_activated', $notification_events)) {
                $should_notify = true;
            } elseif (strpos($log->action, 'Plugin deactivated') !== false && in_array('plugin_deactivated', $notification_events)) {
                $should_notify = true;
            } elseif (strpos($log->action, 'Theme switched') !== false && in_array('theme_switched', $notification_events)) {
                $should_notify = true;
            } elseif (strpos($log->action, 'User registered') !== false && in_array('user_registered', $notification_events)) {
                $should_notify = true;
            }
        }
        
        if ($should_notify) {
            // Send email notification
            self::send_email_notification($log);
            
            // Send webhook notification
            self::send_webhook_notification($log);
            
            // Send push notification via API
            if (class_exists('WPAL_API')) {
                WPAL_API::send_push_notification($log);
            }
        }
    }
    
    /**
     * Send email notification
     */
    private static function send_email_notification($log) {
        $notification_email = get_option('wpal_notification_email', get_option('admin_email'));
        
        if (empty($notification_email)) {
            return;
        }
        
        $subject = sprintf('[%s] Activity Log: %s', get_bloginfo('name'), $log->action);
        
        $message = sprintf(
            "Activity Log Entry\n\n" .
            "Action: %s\n" .
            "User: %s\n" .
            "Role: %s\n" .
            "IP: %s\n" .
            "Browser: %s\n" .
            "Time: %s\n" .
            "Severity: %s\n\n" .
            "View all logs: %s",
            $log->action,
            $log->username,
            $log->user_role,
            $log->ip,
            $log->browser,
            $log->time,
            strtoupper($log->severity),
            admin_url('admin.php?page=wpal-logs')
        );
        
        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        
        wp_mail($notification_email, $subject, $message, $headers);
    }
    
    /**
     * Send webhook notification
     */
    private static function send_webhook_notification($log) {
        $webhook_url = get_option('wpal_webhook_url', '');
        
        if (empty($webhook_url)) {
            return;
        }
        
        $payload = [
            'action' => $log->action,
            'user' => $log->username,
            'user_role' => $log->user_role,
            'ip' => $log->ip,
            'browser' => $log->browser,
            'time' => $log->time,
            'severity' => $log->severity,
            'context' => json_decode($log->context),
        ];
        
        wp_remote_post($webhook_url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($payload),
        ]);
    }
    
    /**
     * Send daily report
     */
    public static function send_daily_report() {
        global $wpdb;
        WPAL_Helpers::init();
        $table_name = WPAL_Helpers::$db_table;
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        if (!$table_exists) {
            return;
        }
        
        $notification_email = get_option('wpal_notification_email', get_option('admin_email'));
        
        if (empty($notification_email)) {
            return;
        }
        
        // Get yesterday's date
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        // Get logs from yesterday
        $logs = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE DATE(time) = %s ORDER BY time DESC",
                $yesterday
            )
        );
        
        if (empty($logs)) {
            return; // No logs for yesterday
        }
        
        // Count by severity
        $info_count = 0;
        $warning_count = 0;
        $error_count = 0;
        
        foreach ($logs as $log) {
            if ($log->severity === 'info') {
                $info_count++;
            } elseif ($log->severity === 'warning') {
                $warning_count++;
            } elseif ($log->severity === 'error') {
                $error_count++;
            }
        }
        
        // Get unique users
        $unique_users = [];
        foreach ($logs as $log) {
            if (!in_array($log->username, $unique_users)) {
                $unique_users[] = $log->username;
            }
        }
        
        // Build email
        $subject = sprintf('[%s] Daily Activity Report - %s', get_bloginfo('name'), $yesterday);
        
        $message = sprintf(
            "Daily Activity Report for %s\n\n" .
            "Total Logs: %d\n" .
            "Info: %d\n" .
            "Warning: %d\n" .
            "Error: %d\n" .
            "Unique Users: %d\n\n",
            $yesterday,
            count($logs),
            $info_count,
            $warning_count,
            $error_count,
            count($unique_users)
        );
        
        // Add error logs (if any)
        if ($error_count > 0) {
            $message .= "Error Logs:\n";
            
            foreach ($logs as $log) {
                if ($log->severity === 'error') {
                    $message .= sprintf(
                        "- %s: %s (User: %s, IP: %s)\n",
                        $log->time,
                        $log->action,
                        $log->username,
                        $log->ip
                    );
                }
            }
            
            $message .= "\n";
        }
        
        // Add warning logs (if any)
        if ($warning_count > 0) {
            $message .= "Warning Logs:\n";
            
            foreach ($logs as $log) {
                if ($log->severity === 'warning') {
                    $message .= sprintf(
                        "- %s: %s (User: %s, IP: %s)\n",
                        $log->time,
                        $log->action,
                        $log->username,
                        $log->ip
                    );
                }
            }
            
            $message .= "\n";
        }
        
        $message .= sprintf("View all logs: %s", admin_url('admin.php?page=wpal-logs'));
        
        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        
        wp_mail($notification_email, $subject, $message, $headers);
    }
    
    /**
     * Send HTML email
     */
    public static function send_html_email($to, $subject, $html_content) {
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
        ];
        
        wp_mail($to, $subject, $html_content, $headers);
    }
    
    /**
     * Format log entry for email
     */
    public static function format_log_for_email($log) {
        $severity_colors = [
            'info' => '#28a745',
            'warning' => '#ffc107',
            'error' => '#dc3545',
        ];
        
        $color = isset($severity_colors[$log->severity]) ? $severity_colors[$log->severity] : $severity_colors['info'];
        
        $html = '<div style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">';
        $html .= '<div style="margin-bottom: 5px;"><strong>Action:</strong> ' . esc_html($log->action) . '</div>';
        $html .= '<div style="margin-bottom: 5px;"><strong>User:</strong> ' . esc_html($log->username) . '</div>';
        $html .= '<div style="margin-bottom: 5px;"><strong>Role:</strong> ' . esc_html($log->user_role) . '</div>';
        $html .= '<div style="margin-bottom: 5px;"><strong>IP:</strong> ' . esc_html($log->ip) . '</div>';
        $html .= '<div style="margin-bottom: 5px;"><strong>Browser:</strong> ' . esc_html($log->browser) . '</div>';
        $html .= '<div style="margin-bottom: 5px;"><strong>Time:</strong> ' . esc_html($log->time) . '</div>';
        $html .= '<div><strong>Severity:</strong> <span style="display: inline-block; padding: 2px 6px; background-color: ' . $color . '; color: #fff; border-radius: 3px;">' . strtoupper($log->severity) . '</span></div>';
        $html .= '</div>';
        
        return $html;
    }
}