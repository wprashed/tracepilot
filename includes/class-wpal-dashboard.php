<?php
/**
 * Dashboard functionality for WP Activity Logger Pro
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WPAL_Dashboard {
    /**
     * Initialize the class
     */
    public static function init() {
        // Add AJAX handlers for dashboard widgets
        add_action('wp_ajax_wpal_get_recent_logs', [__CLASS__, 'ajax_get_recent_logs']);
        add_action('wp_ajax_wpal_get_activity_chart', [__CLASS__, 'ajax_get_activity_chart']);
        add_action('wp_ajax_wpal_get_top_users', [__CLASS__, 'ajax_get_top_users']);
        add_action('wp_ajax_wpal_get_severity_breakdown', [__CLASS__, 'ajax_get_severity_breakdown']);
        
        // Add AJAX handlers for diagnostics
        add_action('wp_ajax_wpal_run_diagnostics', [__CLASS__, 'ajax_run_diagnostics']);
        add_action('wp_ajax_wpal_repair_database', [__CLASS__, 'ajax_repair_database']);
        add_action('wp_ajax_wpal_test_rest_api', [__CLASS__, 'ajax_test_rest_api']);
        add_action('wp_ajax_wpal_clear_logs', [__CLASS__, 'ajax_clear_logs']);
    }
    
    /**
     * Render dashboard page
     */
    public static function render_dashboard_page() {
        // Check if database exists
        WPAL_Helpers::init();
        $db_exists = WPAL_Helpers::check_and_fix_database();
        
        ?>
        <div class="wrap wpal-wrap">
            <h1>Activity Logger Dashboard</h1>
            
            <?php if (!$db_exists): ?>
            <div class="notice notice-warning">
                <p>The database table was missing and has been created. You should start seeing activity logs now.</p>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="m-0">Recent Activity</h5>
                            <button type="button" class="button wpal-refresh-widget" data-widget="recent-logs">
                                <span class="dashicons dashicons-update"></span>
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="wpal-recent-logs-widget" class="wpal-widget-content">
                                <div class="wpal-loading">
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="m-0">Activity Over Time</h5>
                            <button type="button" class="button wpal-refresh-widget" data-widget="activity-chart">
                                <span class="dashicons dashicons-update"></span>
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="wpal-activity-chart-widget" class="wpal-widget-content">
                                <div class="wpal-loading">
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="m-0">Top Users</h5>
                            <button type="button" class="button wpal-refresh-widget" data-widget="top-users">
                                <span class="dashicons dashicons-update"></span>
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="wpal-top-users-widget" class="wpal-widget-content">
                                <div class="wpal-loading">
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="m-0">Severity Breakdown</h5>
                            <button type="button" class="button wpal-refresh-widget" data-widget="severity-breakdown">
                                <span class="dashicons dashicons-update"></span>
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="wpal-severity-breakdown-widget" class="wpal-widget-content">
                                <div class="wpal-loading">
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="m-0">System Diagnostics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Plugin Information</h6>
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <td>Version:</td>
                                                <td><?php echo WPAL_VERSION; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Database Table:</td>
                                                <td><?php echo WPAL_Helpers::$db_table; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Log Storage:</td>
                                                <td><?php echo ucfirst(get_option('wpal_log_storage', 'both')); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Log Retention:</td>
                                                <td><?php echo get_option('wpal_log_retention', '30') . ' days'; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6>System Information</h6>
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <td>WordPress Version:</td>
                                                <td><?php echo get_bloginfo('version'); ?></td>
                                            </tr>
                                            <tr>
                                                <td>PHP Version:</td>
                                                <td><?php echo phpversion(); ?></td>
                                            </tr>
                                            <tr>
                                                <td>MySQL Version:</td>
                                                <td><?php 
                                                    global $wpdb;
                                                    echo $wpdb->db_version();
                                                ?></td>
                                            </tr>
                                            <tr>
                                                <td>Server:</td>
                                                <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <h6>Diagnostic Tools</h6>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="button button-primary" id="wpal-run-diagnostics">Run Diagnostics</button>
                                        <button type="button" class="button" id="wpal-repair-database">Repair Database</button>
                                        <button type="button" class="button" id="wpal-test-rest-api">Test REST API</button>
                                        <button type="button" class="button button-danger" id="wpal-clear-logs">Clear All Logs</button>
                                    </div>
                                    
                                    <div id="wpal-diagnostics-results" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX handler for recent logs widget
     */
    public static function ajax_get_recent_logs() {
        check_ajax_referer('wpal_dashboard_nonce', 'nonce');
        
        global $wpdb;
        WPAL_Helpers::init();
        
        $logs = $wpdb->get_results(
            "SELECT * FROM " . WPAL_Helpers::$db_table . " 
            ORDER BY time DESC 
            LIMIT 10"
        );
        
        if (empty($logs)) {
            echo '<div class="alert alert-info">No activity logs found.</div>';
            wp_die();
        }
        
        ?>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Severity</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo WPAL_Helpers::format_datetime($log->time); ?></td>
                    <td><?php echo esc_html($log->username); ?></td>
                    <td><?php echo esc_html($log->action); ?></td>
                    <td><?php echo WPAL_Helpers::get_severity_badge($log->severity); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="text-center mt-2">
            <a href="<?php echo admin_url('admin.php?page=wp-activity-logger-pro'); ?>" class="button">View All Logs</a>
        </div>
        <?php
        
        wp_die();
    }
    
    /**
     * AJAX handler for activity chart widget
     */
    public static function ajax_get_activity_chart() {
        check_ajax_referer('wpal_dashboard_nonce', 'nonce');
        
        global $wpdb;
        WPAL_Helpers::init();
        
        // Get activity for the last 7 days
        $results = $wpdb->get_results(
            "SELECT 
                DATE(time) as date, 
                COUNT(*) as count 
            FROM " . WPAL_Helpers::$db_table . " 
            WHERE time >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
            GROUP BY DATE(time) 
            ORDER BY date ASC"
        );
        
        // Format data for chart
        $dates = [];
        $counts = [];
        
        // Fill in missing dates
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime('-6 days'));
        
        $current_date = $start_date;
        $data_by_date = [];
        
        foreach ($results as $row) {
            $data_by_date[$row->date] = $row->count;
        }
        
        while ($current_date <= $end_date) {
            $dates[] = date('M j', strtotime($current_date));
            $counts[] = isset($data_by_date[$current_date]) ? $data_by_date[$current_date] : 0;
            $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
        }
        
        // Check if we have data
        if (empty($results)) {
            echo '<div class="alert alert-info">No activity data available for the last 7 days.</div>';
            wp_die();
        }
        
        ?>
        <canvas id="wpal-activity-chart" height="250"></canvas>
        <script>
            jQuery(document).ready(function($) {
                var ctx = document.getElementById('wpal-activity-chart').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($dates); ?>,
                        datasets: [{
                            label: 'Activity Count',
                            data: <?php echo json_encode($counts); ?>,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 2,
                            pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            });
        </script>
        <?php
        
        wp_die();
    }
    
    /**
     * AJAX handler for top users widget
     */
    public static function ajax_get_top_users() {
        check_ajax_referer('wpal_dashboard_nonce', 'nonce');
        
        global $wpdb;
        WPAL_Helpers::init();
        
        $users = $wpdb->get_results(
            "SELECT 
                user_id, 
                username, 
                COUNT(*) as count 
            FROM " . WPAL_Helpers::$db_table . " 
            WHERE user_id > 0 
            GROUP BY user_id 
            ORDER BY count DESC 
            LIMIT 5"
        );
        
        if (empty($users)) {
            echo '<div class="alert alert-info">No user activity data available.</div>';
            wp_die();
        }
        
        ?>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Activity Count</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <?php if ($user->user_id > 0): ?>
                            <a href="<?php echo admin_url('user-edit.php?user_id=' . $user->user_id); ?>">
                                <?php echo esc_html($user->username); ?>
                            </a>
                        <?php else: ?>
                            <?php echo esc_html($user->username); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $user->count; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        
        wp_die();
    }
    
    /**
     * AJAX handler for severity breakdown widget
     */
    public static function ajax_get_severity_breakdown() {
        check_ajax_referer('wpal_dashboard_nonce', 'nonce');
        
        global $wpdb;
        WPAL_Helpers::init();
        
        $severities = $wpdb->get_results(
            "SELECT 
                severity, 
                COUNT(*) as count 
            FROM " . WPAL_Helpers::$db_table . " 
            GROUP BY severity 
            ORDER BY count DESC"
        );
        
        if (empty($severities)) {
            echo '<div class="alert alert-info">No severity data available.</div>';
            wp_die();
        }
        
        // Prepare data for chart
        $labels = [];
        $data = [];
        $colors = [
            'info' => 'rgba(54, 162, 235, 0.8)',
            'warning' => 'rgba(255, 159, 64, 0.8)',
            'error' => 'rgba(255, 99, 132, 0.8)',
            'success' => 'rgba(75, 192, 192, 0.8)',
        ];
        $backgroundColors = [];
        
        foreach ($severities as $severity) {
            $labels[] = ucfirst($severity->severity);
            $data[] = $severity->count;
            $backgroundColors[] = isset($colors[$severity->severity]) ? $colors[$severity->severity] : 'rgba(201, 203, 207, 0.8)';
        }
        
        ?>
        <canvas id="wpal-severity-chart" height="250"></canvas>
        <script>
            jQuery(document).ready(function($) {
                var ctx = document.getElementById('wpal-severity-chart').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode($labels); ?>,
                        datasets: [{
                            data: <?php echo json_encode($data); ?>,
                            backgroundColor: <?php echo json_encode($backgroundColors); ?>,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            });
        </script>
        <?php
        
        wp_die();
    }
    
    /**
     * AJAX handler for running diagnostics
     */
    public static function ajax_run_diagnostics() {
        check_ajax_referer('wpal_dashboard_nonce', 'nonce');
        
        global $wpdb;
        WPAL_Helpers::init();
        
        $results = [
            'status' => 'success',
            'message' => '',
            'details' => []
        ];
        
        // Check database table
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '" . WPAL_Helpers::$db_table . "'") === WPAL_Helpers::$db_table;
        $results['details'][] = [
            'test' => 'Database Table',
            'status' => $table_exists ? 'pass' : 'fail',
            'message' => $table_exists ? 'Database table exists.' : 'Database table does not exist.'
        ];
        
        // Check log directory
        $log_dir = WPAL_PATH . 'logs/';
        $log_dir_exists = file_exists($log_dir);
        $log_dir_writable = $log_dir_exists && is_writable($log_dir);
        $results['details'][] = [
            'test' => 'Log Directory',
            'status' => $log_dir_writable ? 'pass' : 'fail',
            'message' => $log_dir_writable ? 'Log directory exists and is writable.' : ($log_dir_exists ? 'Log directory exists but is not writable.' : 'Log directory does not exist.')
        ];
        
        // Check log file
        $log_file = WPAL_PATH . 'logs/activity.csv';
        $log_file_exists = file_exists($log_file);
        $log_file_writable = $log_file_exists && is_writable($log_file);
        $results['details'][] = [
            'test' => 'Log File',
            'status' => $log_file_exists ? ($log_file_writable ? 'pass' : 'warning') : 'info',
            'message' => $log_file_exists ? ($log_file_writable ? 'Log file exists and is writable.' : 'Log file exists but is not writable.') : 'Log file does not exist yet. It will be created when needed.'
        ];
        
        // Check database records
        $record_count = $wpdb->get_var("SELECT COUNT(*) FROM " . WPAL_Helpers::$db_table);
        $results['details'][] = [
            'test' => 'Database Records',
            'status' => 'info',
            'message' => 'There are ' . $record_count . ' records in the database.'
        ];
        
        // Check PHP version
        $php_version = phpversion();
        $php_version_ok = version_compare($php_version, '7.0', '>=');
        $results['details'][] = [
            'test' => 'PHP Version',
            'status' => $php_version_ok ? 'pass' : 'warning',
            'message' => 'PHP version: ' . $php_version . ($php_version_ok ? ' (OK)' : ' (Recommended: 7.0 or higher)')
        ];
        
        // Check WordPress version
        $wp_version = get_bloginfo('version');
        $wp_version_ok = version_compare($wp_version, '5.0', '>=');
        $results['details'][] = [
            'test' => 'WordPress Version',
            'status' => $wp_version_ok ? 'pass' : 'warning',
            'message' => 'WordPress version: ' . $wp_version . ($wp_version_ok ? ' (OK)' : ' (Recommended: 5.0 or higher)')
        ];
        
        // Check MySQL version
        $mysql_version = $wpdb->db_version();
        $mysql_version_ok = version_compare($mysql_version, '5.6', '>=');
        $results['details'][] = [
            'test' => 'MySQL Version',
            'status' => $mysql_version_ok ? 'pass' : 'warning',
            'message' => 'MySQL version: ' . $mysql_version . ($mysql_version_ok ? ' (OK)' : ' (Recommended: 5.6 or higher)')
        ];
        
        // Overall status
        $has_fail = false;
        foreach ($results['details'] as $detail) {
            if ($detail['status'] === 'fail') {
                $has_fail = true;
                break;
            }
        }
        
        $results['status'] = $has_fail ? 'error' : 'success';
        $results['message'] = $has_fail ? 'Some tests failed. Please check the details below.' : 'All tests passed successfully.';
        
        wp_send_json($results);
    }
    
    /**
     * AJAX handler for repairing database
     */
    public static function ajax_repair_database() {
        check_ajax_referer('wpal_dashboard_nonce', 'nonce');
        
        WPAL_Helpers::init();
        WPAL_Helpers::create_db_table();
        
        $results = [
            'status' => 'success',
            'message' => 'Database table has been repaired successfully.'
        ];
        
        wp_send_json($results);
    }
    
    /**
     * AJAX handler for testing REST API
     */
    public static function ajax_test_rest_api() {
        check_ajax_referer('wpal_dashboard_nonce', 'nonce');
        
        $api_url = rest_url('wp/v2/users/me');
        $response = wp_remote_get($api_url, [
            'headers' => [
                'X-WP-Nonce' => wp_create_nonce('wp_rest')
            ]
        ]);
        
        $results = [
            'status' => 'success',
            'message' => 'REST API is working correctly.',
            'details' => []
        ];
        
        if (is_wp_error($response)) {
            $results['status'] = 'error';
            $results['message'] = 'REST API test failed: ' . $response->get_error_message();
        } else {
            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            
            if ($code !== 200) {
                $results['status'] = 'error';
                $results['message'] = 'REST API returned an unexpected status code: ' . $code;
            }
            
            $results['details'] = [
                'url' => $api_url,
                'status_code' => $code,
                'response' => json_decode($body, true)
            ];
        }
        
        wp_send_json($results);
    }
    
    /**
     * AJAX handler for clearing logs
     */
    public static function ajax_clear_logs() {
        check_ajax_referer('wpal_dashboard_nonce', 'nonce');
        
        global $wpdb;
        WPAL_Helpers::init();
        
        // Clear database
        $wpdb->query("TRUNCATE TABLE " . WPAL_Helpers::$db_table);
        
        // Clear CSV file
        $csv_file = WPAL_PATH . 'logs/activity.csv';
        if (file_exists($csv_file)) {
            // Keep the header
            $header = "Time,User,Action,IP,UserRole,Browser,Severity\n";
            file_put_contents($csv_file, $header);
        }
        
        $results = [
            'status' => 'success',
            'message' => 'All logs have been cleared successfully.'
        ];
        
        wp_send_json($results);
    }
}