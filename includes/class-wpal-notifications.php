<?php
/**
 * Notifications class for WP Activity Logger Pro
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WPAL_Notifications {
    /**
     * Constructor
     */
    public function __construct() {
        // Add notification hooks
        add_action('wpal_error_logged', array($this, 'notify_error'), 10, 3);
    }

    /**
     * Notify on error
     */
    public function notify_error($action, $message, $context) {
        // Get settings
        $settings = get_option('wpal_settings', array());
        
        // Check if notifications are enabled for errors
        if (!isset($settings['notification_events']) || !in_array('error', $settings['notification_events'])) {
            return;
        }
        
        // Get notification email
        $email = isset($settings['notification_email']) ? $settings['notification_email'] : get_option('admin_email');
        
        // Send notification
        $this->send_email_notification($email, $action, $message, $context);
    }

    /**
     * Send email notification
     */
    private function send_email_notification($email, $action, $message, $context) {
        // Get site info
        $site_name = get_bloginfo('name');
        $site_url = get_bloginfo('url');
        
        // Build email subject
        $subject = sprintf(__('[%s] Activity Logger Error: %s', 'wp-activity-logger-pro'), $site_name, $action);
        
        // Build email body
        $body = sprintf(__('An error has been logged on your WordPress site: %s', 'wp-activity-logger-pro'), $site_url) . "\n\n";
        $body .= sprintf(__('Action: %s', 'wp-activity-logger-pro'), $action) . "\n";
        $body .= sprintf(__('Message: %s', 'wp-activity-logger-pro'), $message) . "\n";
        
        // Add context if available
        if (!empty($context)) {
            $body .= __('Context:', 'wp-activity-logger-pro') . "\n";
            foreach ($context as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                $body .= sprintf('%s: %s', $key, $value) . "\n";
            }
        }
        
        // Add link to logs
        $body .= "\n" . sprintf(__('View all logs: %s', 'wp-activity-logger-pro'), admin_url('admin.php?page=wp-activity-logger-pro')) . "\n";
        
        // Set headers
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        // Send email
        wp_mail($email, $subject, $body, $headers);
    }

    /**
     * Send push notification
     */
    public function send_push_notification($action, $message, $context) {
        // Get settings
        $settings = get_option('wpal_settings', array());
        
        // Check if push notifications are enabled
        if (!isset($settings['push_notifications']) || $settings['push_notifications'] !== 'on') {
            return;
        }
        
        // Get push notification URL
        $push_url = isset($settings['push_url']) ? $settings['push_url'] : '';
        
        // If no URL, return
        if (empty($push_url)) {
            return;
        }
        
        // Prepare data
        $data = array(
            'action' => $action,
            'message' => $message,
            'context' => $context,
            'site' => get_bloginfo('name'),
            'url' => get_bloginfo('url'),
            'time' => current_time('mysql')
        );
        
        // Send push notification
        wp_remote_post($push_url, array(
            'body' => json_encode($data),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 5
        ));
    }
}