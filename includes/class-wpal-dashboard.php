<?php
class WPAL_Dashboard {
    public static function init() {
        add_menu_page(
            'WP Activity Logger Pro', 
            'Activity Logs', 
            'manage_options', 
            'wpal-logs', 
            [__CLASS__, 'render_logs_page'], 
            'dashicons-chart-line'
        );
        
        add_submenu_page(
            'wpal-logs',
            'Activity Logs',
            'View Logs',
            'manage_options',
            'wpal-logs'
        );
        
        add_submenu_page(
            'wpal-logs',
            'Activity Dashboard',
            'Dashboard',
            'manage_options',
            'wpal-dashboard',
            [__CLASS__, 'render_dashboard_page']
        );
        
        add_submenu_page(
            'wpal-logs',
            'Export Logs',
            'Export',
            'manage_options',
            'wpal-export',
            [__CLASS__, 'render_export_page']
        );
        
        add_submenu_page(
            'wpal-logs',
            'Settings',
            'Settings',
            'manage_options',
            'wpal-settings',
            [__CLASS__, 'render_settings_page']
        );
    }

    public static function render_logs_page() {
        ?>
        <div class="wrap wpal-wrap">
            <h1>Activity Logs</h1>
            
            <div class="wpal-filters card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="wpal-date-range">Date Range</label>
                            <input type="text" id="wpal-date-range" class="form-control" placeholder="Select date range">
                        </div>
                        <div class="col-md-3">
                            <label for="wpal-user-filter">User</label>
                            <select id="wpal-user-filter" class="form-control">
                                <option value="">All Users</option>
                                <?php
                                $users = get_users(['fields' => ['ID', 'user_login']]);
                                foreach ($users as $user) {
                                    echo '<option value="' . esc_attr($user->user_login) . '">' . esc_html($user->user_login) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="wpal-action-filter">Action Type</label>
                            <select id="wpal-action-filter" class="form-control">
                                <option value="">All Actions</option>
                                <option value="Logged">Login/Logout</option>
                                <option value="Created">Created</option>
                                <option value="Updated">Updated</option>
                                <option value="Deleted">Deleted</option>
                                <option value="Order">Orders</option>
                                <option value="Plugin">Plugins</option>
                                <option value="Theme">Themes</option>
                                <option value="Comment">Comments</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="wpal-severity-filter">Severity</label>
                            <select id="wpal-severity-filter" class="form-control">
                                <option value="">All Severities</option>
                                <option value="info">Info</option>
                                <option value="warning">Warning</option>
                                <option value="error">Error</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button id="wpal-apply-filters" class="button button-primary">Apply Filters</button>
                            <button id="wpal-reset-filters" class="button">Reset Filters</button>
                            <button id="wpal-export-filtered" class="button button-secondary float-end">Export Filtered Results</button>
                            <button id="wpal-clear-logs" class="button button-link-delete float-end me-3">Clear All Logs</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="wpal-logs-table-container mt-4">
                <table id="wpal-logs-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Action</th>
                            <th>IP</th>
                            <th>Browser</th>
                            <th>Severity</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody id="wpal-logs-body">
                        <tr>
                            <td colspan="8" class="text-center">Loading logs...</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="wpal-pagination mt-3">
                    <button id="wpal-load-more" class="button">Load More</button>
                    <span id="wpal-showing-count" class="ms-3">Showing 0 of 0 logs</span>
                </div>
            </div>
            
            <!-- Log Details Modal -->
            <div class="modal fade" id="wpal-log-details-modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Log Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="wpal-log-details-content">
                            <!-- Details will be loaded here -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="button" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    public static function render_dashboard_page() {
        ?>
        <div class="wrap wpal-wrap">
            <h1>Activity Dashboard</h1>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Activity Overview</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="wpal-daily-activity-chart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Top Users</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="wpal-user-activity-chart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Action Types</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="wpal-action-types-chart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Live Activity Feed</h5>
                        </div>
                        <div class="card-body">
                            <div id="wpal-live-feed" class="wpal-live-feed">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p>Loading live feed...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    public static function render_export_page() {
        ?>
        <div class="wrap wpal-wrap">
            <h1>Export Logs</h1>
            
            <div class="card">
                <div class="card-body">
                    <form id="wpal-export-form" method="post" action="<?php echo esc_url(rest_url('wpal/v1/export')); ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="wpal-export-date-range" class="form-label">Date Range</label>
                                    <input type="text" id="wpal-export-date-range" class="form-control" placeholder="Select date range">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="wpal-export-user" class="form-label">User</label>
                                    <select id="wpal-export-user" class="form-control">
                                        <option value="">All Users</option>
                                        <?php
                                        $users = get_users(['fields' => ['ID', 'user_login']]);
                                        foreach ($users as $user) {
                                            echo '<option value="' . esc_attr($user->user_login) . '">' . esc_html($user->user_login) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="wpal-export-action" class="form-label">Action Type</label>
                                    <select id="wpal-export-action" class="form-control">
                                        <option value="">All Actions</option>
                                        <option value="Logged">Login/Logout</option>
                                        <option value="Created">Created</option>
                                        <option value="Updated">Updated</option>
                                        <option value="Deleted">Deleted</option>
                                        <option value="Order">Orders</option>
                                        <option value="Plugin">Plugins</option>
                                        <option value="Theme">Themes</option>
                                        <option value="Comment">Comments</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="wpal-export-severity" class="form-label">Severity</label>
                                    <select id="wpal-export-severity" class="form-control">
                                        <option value="">All Severities</option>
                                        <option value="info">Info</option>
                                        <option value="warning">Warning</option>
                                        <option value="error">Error</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="wpal-export-format" class="form-label">Export Format</label>
                                    <select id="wpal-export-format" class="form-control">
                                        <option value="csv">CSV</option>
                                        <option value="json">JSON</option>
                                        <option value="xml">XML</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="wpal-export-limit" class="form-label">Limit</label>
                                    <input type="number" id="wpal-export-limit" class="form-control" value="1000" min="1">
                                    <small class="form-text text-muted">Maximum number of logs to export. Use 0 for all logs.</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="button button-primary">Export Logs</button>
                        </div>
                        
                        <input type="hidden" id="wpal-export-from" name="from" value="">
                        <input type="hidden" id="wpal-export-to" name="to" value="">
                        <input type="hidden" id="wpal-export-user-value" name="user" value="">
                        <input type="hidden" id="wpal-export-action-value" name="action_type" value="">
                        <input type="hidden" id="wpal-export-severity-value" name="severity" value="">
                        <input type="hidden" id="wpal-export-format-value" name="format" value="csv">
                        <input type="hidden" id="wpal-export-limit-value" name="limit" value="1000">
                        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('wp_rest'); ?>">
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Scheduled Exports</h5>
                </div>
                <div class="card-body">
                    <p>Set up automatic exports to be sent to your email on a schedule.</p>
                    
                    <form id="wpal-scheduled-export-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="wpal-schedule-email" class="form-label">Email Address</label>
                                    <input type="email" id="wpal-schedule-email" class="form-control" value="<?php echo esc_attr(get_option('admin_email')); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="wpal-schedule-frequency" class="form-label">Frequency</label>
                                    <select id="wpal-schedule-frequency" class="form-control">
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="wpal-schedule-format" class="form-label">Export Format</label>
                                    <select id="wpal-schedule-format" class="form-control">
                                        <option value="csv">CSV</option>
                                        <option value="json">JSON</option>
                                        <option value="xml">XML</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="wpal-schedule-filter" class="form-label">Apply Filters</label>
                                    <select id="wpal-schedule-filter" class="form-control">
                                        <option value="all">All Logs</option>
                                        <option value="errors">Errors Only</option>
                                        <option value="warnings">Warnings & Errors</option>
                                        <option value="custom">Custom Filter</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div id="wpal-schedule-custom-filters" class="d-none">
                            <!-- Custom filters will be shown here when "Custom Filter" is selected -->
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="button button-primary">Save Scheduled Export</button>
                        </div>
                    </form>
                    
                    <hr>
                    
                    <h6>Current Scheduled Exports</h6>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Frequency</th>
                                <th>Format</th>
                                <th>Filter</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="wpal-scheduled-exports-list">
                            <tr>
                                <td colspan="5" class="text-center">No scheduled exports configured</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
    
    public static function render_settings_page() {
        // This will be handled by the Settings API in class-wpal-settings.php
        ?>
        <div class="wrap wpal-wrap">
            <h1>Activity Logger Settings</h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('wpal_settings');
                do_settings_sections('wpal_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    public static function ajax_live_feed() {
        check_ajax_referer('wpal_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $logs = WPAL_Helpers::get_filtered_logs([
            'limit' => 10,
        ]);
        
        wp_send_json_success($logs);
    }
}