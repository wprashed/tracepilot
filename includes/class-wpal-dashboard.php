<?php
/**
 * Dashboard class for WP Activity Logger Pro
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WPAL_Dashboard {
    /**
     * Initialize the dashboard
     */
    public static function init() {
        // Register AJAX handlers for dashboard widgets
        add_action('wp_ajax_wpal_get_recent_logs', array(__CLASS__, 'ajax_get_recent_logs'));
        add_action('wp_ajax_wpal_get_activity_chart', array(__CLASS__, 'ajax_get_activity_chart'));
        add_action('wp_ajax_wpal_get_top_users', array(__CLASS__, 'ajax_get_top_users'));
        add_action('wp_ajax_wpal_get_severity_breakdown', array(__CLASS__, 'ajax_get_severity_breakdown'));
    }
    
    /**
     * AJAX handler for recent logs widget
     */
    public static function ajax_get_recent_logs() {
        // Check nonce
        check_ajax_referer('wpal_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'wp-activity-logger-pro'));
        }
        
        global $wpdb;
        WPAL_Helpers::init();
        
        // Get recent logs
        $recent_logs = $wpdb->get_results("SELECT * FROM " . WPAL_Helpers::$db_table . " ORDER BY time DESC LIMIT 10");
        
        ob_start();
        
        if (empty($recent_logs)) {
            ?>
            <div class="wpal-alert wpal-alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <?php _e('No recent logs found.', 'wp-activity-logger-pro'); ?>
            </div>
            <?php
        } else {
            ?>
            <div class="wpal-table-responsive">
                <table class="wpal-table">
                    <thead>
                        <tr>
                            <th><?php _e('Time', 'wp-activity-logger-pro'); ?></th>
                            <th><?php _e('User', 'wp-activity-logger-pro'); ?></th>
                            <th><?php _e('Action', 'wp-activity-logger-pro'); ?></th>
                            <th><?php _e('Severity', 'wp-activity-logger-pro'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_logs as $log) : ?>
                            <tr>
                                <td><?php echo WPAL_Helpers::format_datetime($log->time); ?></td>
                                <td><?php echo esc_html($log->username); ?></td>
                                <td><?php echo esc_html($log->action); ?></td>
                                <td>
                                    <?php 
                                    $severity_class = '';
                                    switch ($log->severity) {
                                        case 'info':
                                            $severity_class = 'success';
                                            break;
                                        case 'warning':
                                            $severity_class = 'warning';
                                            break;
                                        case 'error':
                                            $severity_class = 'danger';
                                            break;
                                        default:
                                            $severity_class = 'info';
                                    }
                                    ?>
                                    <span class="wpal-badge wpal-badge-<?php echo $severity_class; ?>"><?php echo esc_html(ucfirst($log->severity)); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="wpal-widget-footer">
                <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro'); ?>" class="wpal-btn wpal-btn-outline wpal-btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-list"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                    <?php _e('View All Logs', 'wp-activity-logger-pro'); ?>
                </a>
            </div>
            <?php
        }
        
        $html = ob_get_clean();
        echo $html;
        wp_die();
    }
    
    /**
     * AJAX handler for activity chart widget
     */
    public static function ajax_get_activity_chart() {
        // Check nonce
        check_ajax_referer('wpal_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'wp-activity-logger-pro'));
        }
        
        WPAL_Helpers::init();
        
        // Get activity over time
        $activity_data = WPAL_Helpers::get_activity_over_time(7);
        
        // Prepare chart data
        $chart_labels = array_keys($activity_data);
        $chart_data = array_values($activity_data);
        
        $chart_json = json_encode([
            'labels' => $chart_labels,
            'data' => $chart_data
        ]);
        
        ob_start();
        ?>
        <div class="wpal-chart-container">
            <canvas id="wpal-activity-chart" data-chart='<?php echo esc_attr($chart_json); ?>'></canvas>
        </div>
        <?php
        $html = ob_get_clean();
        echo $html;
        wp_die();
    }
    
    /**
     * AJAX handler for top users widget
     */
    public static function ajax_get_top_users() {
        // Check nonce
        check_ajax_referer('wpal_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'wp-activity-logger-pro'));
        }
        
        WPAL_Helpers::init();
        
        // Get top users
        $top_users = WPAL_Helpers::get_top_users(5);
        
        ob_start();
        
        if (empty($top_users)) {
            ?>
            <div class="wpal-alert wpal-alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <?php _e('No user activity found.', 'wp-activity-logger-pro'); ?>
            </div>
            <?php
        } else {
            ?>
            <div class="wpal-table-responsive">
                <table class="wpal-table">
                    <thead>
                        <tr>
                            <th><?php _e('User', 'wp-activity-logger-pro'); ?></th>
                            <th class="wpal-text-right"><?php _e('Activity Count', 'wp-activity-logger-pro'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_users as $user) : ?>
                            <tr>
                                <td><?php echo esc_html($user->username); ?></td>
                                <td class="wpal-text-right"><?php echo number_format($user->count); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
        
        $html = ob_get_clean();
        echo $html;
        wp_die();
    }
    
    /**
     * AJAX handler for severity breakdown widget
     */
    public static function ajax_get_severity_breakdown() {
        // Check nonce
        check_ajax_referer('wpal_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'wp-activity-logger-pro'));
        }
        
        WPAL_Helpers::init();
        
        // Get severity breakdown
        $severity_breakdown = WPAL_Helpers::get_severity_breakdown();
        
        // Prepare severity chart data
        $severity_labels = [];
        $severity_data = [];
        
        foreach ($severity_breakdown as $severity) {
            $severity_labels[] = ucfirst($severity->severity);
            $severity_data[] = (int) $severity->count;
        }
        
        $severity_chart_json = json_encode([
            'labels' => $severity_labels,
            'data' => $severity_data
        ]);
        
        ob_start();
        
        if (empty($severity_breakdown)) {
            ?>
            <div class="wpal-alert wpal-alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <?php _e('No severity data found.', 'wp-activity-logger-pro'); ?>
            </div>
            <?php
        } else {
            ?>
            <div class="wpal-chart-container">
                <canvas id="wpal-severity-chart" data-chart='<?php echo esc_attr($severity_chart_json); ?>'></canvas>
            </div>
            <?php
        }
        
        $html = ob_get_clean();
        echo $html;
        wp_die();
    }
}