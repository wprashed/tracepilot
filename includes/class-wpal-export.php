<?php
/**
 * Export functionality for WP Activity Logger Pro
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WPAL_Export {
    /**
     * Initialize the class
     */
    public static function init() {
        // Add export action
        add_action('admin_post_wpal_export_logs', [__CLASS__, 'handle_export']);
        
        // Add scheduled export if enabled
        if (get_option('wpal_scheduled_export', false) && !wp_next_scheduled('wpal_scheduled_export')) {
            wp_schedule_event(time(), 'weekly', 'wpal_scheduled_export');
        }
        
        // Handle scheduled export
        add_action('wpal_scheduled_export', [__CLASS__, 'handle_scheduled_export']);
    }
    
    /**
     * Handle export request
     */
    public static function handle_export() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpal_export')) {
            wp_die('Security check failed. Please try again.');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to export logs.');
        }
        
        // Get export parameters
        $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'csv';
        $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
        $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
        $severity = isset($_POST['severity']) ? sanitize_text_field($_POST['severity']) : '';
        $user = isset($_POST['user']) ? sanitize_text_field($_POST['user']) : '';
        
        // Get logs
        $logs = self::get_logs_for_export($date_from, $date_to, $severity, $user);
        
        // Export logs
        switch ($format) {
            case 'csv':
                self::export_csv($logs);
                break;
                
            case 'json':
                self::export_json($logs);
                break;
                
            case 'xml':
                self::export_xml($logs);
                break;
                
            case 'html':
                self::export_html($logs);
                break;
                
            case 'pdf':
                self::export_pdf($logs);
                break;
                
            default:
                wp_die('Invalid export format.');
        }
        
        exit;
    }
    
    /**
     * Get logs for export
     */
    private static function get_logs_for_export($date_from = '', $date_to = '', $severity = '', $user = '') {
        global $wpdb;
        WPAL_Helpers::init();
        $table_name = WPAL_Helpers::$db_table;
        
        // Build query
        $query = "SELECT * FROM $table_name WHERE 1=1";
        $query_args = [];
        
        // Filter by date range
        if (!empty($date_from)) {
            $query .= " AND time >= %s";
            $query_args[] = $date_from . ' 00:00:00';
        }
        
        if (!empty($date_to)) {
            $query .= " AND time <= %s";
            $query_args[] = $date_to . ' 23:59:59';
        }
        
        // Filter by severity
        if (!empty($severity) && $severity !== 'all') {
            $query .= " AND severity = %s";
            $query_args[] = $severity;
        }
        
        // Filter by user
        if (!empty($user)) {
            $query .= " AND username = %s";
            $query_args[] = $user;
        }
        
        // Add order
        $query .= " ORDER BY time DESC";
        
        // Get logs
        if (!empty($query_args)) {
            $logs = $wpdb->get_results(
                $wpdb->prepare($query, $query_args)
            );
        } else {
            $logs = $wpdb->get_results($query);
        }
        
        return $logs;
    }
    
    /**
     * Export logs as CSV
     */
    private static function export_csv($logs) {
        // Set headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=activity-logs-' . date('Y-m-d') . '.csv');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel UTF-8 compatibility
        fputs($output, "\xEF\xBB\xBF");
        
        // Add headers
        fputcsv($output, ['ID', 'Time', 'User ID', 'Username', 'User Role', 'Action', 'IP', 'Browser', 'Severity', 'Context']);
        
        // Add data
        foreach ($logs as $log) {
            fputcsv($output, [
                $log->id,
                $log->time,
                $log->user_id,
                $log->username,
                $log->user_role,
                $log->action,
                $log->ip,
                $log->browser,
                $log->severity,
                $log->context,
            ]);
        }
        
        // Close output stream
        fclose($output);
        exit;
    }
    
    /**
     * Export logs as JSON
     */
    private static function export_json($logs) {
        // Set headers
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=activity-logs-' . date('Y-m-d') . '.json');
        
        // Parse context JSON
        foreach ($logs as $log) {
            if (!empty($log->context)) {
                $log->context = json_decode($log->context);
            }
        }
        
        // Output JSON
        echo json_encode($logs, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Export logs as XML
     */
    private static function export_xml($logs) {
        // Set headers
        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: attachment; filename=activity-logs-' . date('Y-m-d') . '.xml');
        
        // Create XML document
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><logs></logs>');
        
        // Add logs
        foreach ($logs as $log) {
            $log_xml = $xml->addChild('log');
            $log_xml->addChild('id', $log->id);
            $log_xml->addChild('time', $log->time);
            $log_xml->addChild('user_id', $log->user_id);
            $log_xml->addChild('username', htmlspecialchars($log->username));
            $log_xml->addChild('user_role', htmlspecialchars($log->user_role));
            $log_xml->addChild('action', htmlspecialchars($log->action));
            $log_xml->addChild('ip', $log->ip);
            $log_xml->addChild('browser', htmlspecialchars($log->browser));
            $log_xml->addChild('severity', $log->severity);
            
            // Add context as CDATA
            $context = $log_xml->addChild('context');
            $context_node = dom_import_simplexml($context);
            $context_owner = $context_node->ownerDocument;
            $context_node->appendChild($context_owner->createCDATASection($log->context));
        }
        
        // Output XML
        echo $xml->asXML();
        exit;
    }
    
    /**
     * Export logs as HTML
     */
    private static function export_html($logs) {
        // Set headers
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename=activity-logs-' . date('Y-m-d') . '.html');
        
        // Start HTML
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Activity Logs - ' . date('Y-m-d') . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .info { color: #28a745; }
        .warning { color: #ffc107; }
        .error { color: #dc3545; }
    </style>
</head>
<body>
    <h1>Activity Logs - ' . date('Y-m-d') . '</h1>
    <p>Exported from ' . get_bloginfo('name') . '</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Time</th>
                <th>Username</th>
                <th>User Role</th>
                <th>Action</th>
                <th>IP</th>
                <th>Browser</th>
                <th>Severity</th>
            </tr>
        </thead>
        <tbody>';
        
        // Add logs
        foreach ($logs as $log) {
            $html .= '
            <tr>
                <td>' . $log->id . '</td>
                <td>' . $log->time . '</td>
                <td>' . esc_html($log->username) . '</td>
                <td>' . esc_html($log->user_role) . '</td>
                <td>' . esc_html($log->action) . '</td>
                <td>' . $log->ip . '</td>
                <td>' . esc_html($log->browser) . '</td>
                <td class="' . $log->severity . '">' . strtoupper($log->severity) . '</td>
            </tr>';
        }
        
        // End HTML
        $html .= '
        </tbody>
    </table>
</body>
</html>';
        
        // Output HTML
        echo $html;
        exit;
    }
    
    /**
     * Export logs as PDF
     */
    private static function export_pdf($logs) {
        // Check if TCPDF is available
        if (!class_exists('TCPDF')) {
            // Fallback to HTML export
            self::export_html($logs);
            return;
        }
        
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(get_bloginfo('name'));
        $pdf->SetTitle('Activity Logs - ' . date('Y-m-d'));
        $pdf->SetSubject('Activity Logs Export');
        
        // Set default header data
        $pdf->SetHeaderData('', 0, 'Activity Logs - ' . date('Y-m-d'), get_bloginfo('name'));
        
        // Set header and footer fonts
        $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
        
        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        
        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // Add a page
        $pdf->AddPage();
        
        // Create table header
        $html = '
        <table border="1" cellpadding="5">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th>ID</th>
                    <th>Time</th>
                    <th>Username</th>
                    <th>Action</th>
                    <th>IP</th>
                    <th>Severity</th>
                </tr>
            </thead>
            <tbody>';
        
        // Add logs
        foreach ($logs as $log) {
            $severity_color = '#28a745'; // info
            
            if ($log->severity === 'warning') {
                $severity_color = '#ffc107';
            } elseif ($log->severity === 'error') {
                $severity_color = '#dc3545';
            }
            
            $html .= '
                <tr>
                    <td>' . $log->id . '</td>
                    <td>' . $log->time . '</td>
                    <td>' . esc_html($log->username) . '</td>
                    <td>' . esc_html($log->action) . '</td>
                    <td>' . $log->ip . '</td>
                    <td style="color: ' . $severity_color . ';">' . strtoupper($log->severity) . '</td>
                </tr>';
        }
        
        // Close table
        $html .= '
            </tbody>
        </table>';
        
        // Output HTML
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Close and output PDF
        $pdf->Output('activity-logs-' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }
    
    /**
     * Handle scheduled export
     */
    public static function handle_scheduled_export() {
        // Check if scheduled export is enabled
        $scheduled_export = get_option('wpal_scheduled_export', false);
        
        if (!$scheduled_export) {
            return;
        }
        
        // Get export settings
        $export_format = get_option('wpal_scheduled_export_format', 'csv');
        $export_email = get_option('wpal_scheduled_export_email', get_option('admin_email'));
        $retention_days = get_option('wpal_retention_days', 30);
        
        // Get logs from the last X days
        $date_from = date('Y-m-d', strtotime('-' . $retention_days . ' days'));
        $date_to = date('Y-m-d');
        
        $logs = self::get_logs_for_export($date_from, $date_to);
        
        if (empty($logs)) {
            return; // No logs to export
        }
        
        // Create export file
        $filename = 'activity-logs-' . date('Y-m-d') . '.' . $export_format;
        $filepath = WPAL_PATH . 'logs/' . $filename;
        
        // Ensure logs directory exists
        $logs_dir = WPAL_PATH . 'logs/';
        if (!file_exists($logs_dir)) {
            mkdir($logs_dir, 0755, true);
        }
        
        // Create export file based on format
        switch ($export_format) {
            case 'csv':
                self::create_csv_file($logs, $filepath);
                break;
                
            case 'json':
                self::create_json_file($logs, $filepath);
                break;
                
            case 'xml':
                self::create_xml_file($logs, $filepath);
                break;
                
            case 'html':
                self::create_html_file($logs, $filepath);
                break;
                
            default:
                return; // Invalid format
        }
        
        // Send email with attachment
        if (file_exists($filepath)) {
            self::send_export_email($export_email, $filepath, $filename);
        }
    }
    
    /**
     * Create CSV file
     */
    private static function create_csv_file($logs, $filepath) {
        $file = fopen($filepath, 'w');
        
        // Add BOM for Excel UTF-8 compatibility
        fputs($file, "\xEF\xBB\xBF");
        
        // Add headers
        fputcsv($file, ['ID', 'Time', 'User ID', 'Username', 'User Role', 'Action', 'IP', 'Browser', 'Severity', 'Context']);
        
        // Add data
        foreach ($logs as $log) {
            fputcsv($file, [
                $log->id,
                $log->time,
                $log->user_id,
                $log->username,
                $log->user_role,
                $log->action,
                $log->ip,
                $log->browser,
                $log->severity,
                $log->context,
            ]);
        }
        
        fclose($file);
    }
    
    /**
     * Create JSON file
     */
    private static function create_json_file($logs, $filepath) {
        // Parse context JSON
        foreach ($logs as $log) {
            if (!empty($log->context)) {
                $log->context = json_decode($log->context);
            }
        }
        
        file_put_contents($filepath, json_encode($logs, JSON_PRETTY_PRINT));
    }
    
    /**
     * Create XML file
     */
    private static function create_xml_file($logs, $filepath) {
        // Create XML document
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><logs></logs>');
        
        // Add logs
        foreach ($logs as $log) {
            $log_xml = $xml->addChild('log');
            $log_xml->addChild('id', $log->id);
            $log_xml->addChild('time', $log->time);
            $log_xml->addChild('user_id', $log->user_id);
            $log_xml->addChild('username', htmlspecialchars($log->username));
            $log_xml->addChild('user_role', htmlspecialchars($log->user_role));
            $log_xml->addChild('action', htmlspecialchars($log->action));
            $log_xml->addChild('ip', $log->ip);
            $log_xml->addChild('browser', htmlspecialchars($log->browser));
            $log_xml->addChild('severity', $log->severity);
            
            // Add context as CDATA
            $context = $log_xml->addChild('context');
            $context_node = dom_import_simplexml($context);
            $context_owner = $context_node->ownerDocument;
            $context_node->appendChild($context_owner->createCDATASection($log->context));
        }
        
        $xml->asXML($filepath);
    }
    
    /**
     * Create HTML file
     */
    private static function create_html_file($logs, $filepath) {
        // Start HTML
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Activity Logs - ' . date('Y-m-d') . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .info { color: #28a745; }
        .warning { color: #ffc107; }
        .error { color: #dc3545; }
    </style>
</head>
<body>
    <h1>Activity Logs - ' . date('Y-m-d') . '</h1>
    <p>Exported from ' . get_bloginfo('name') . '</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Time</th>
                <th>Username</th>
                <th>User Role</th>
                <th>Action</th>
                <th>IP</th>
                <th>Browser</th>
                <th>Severity</th>
            </tr>
        </thead>
        <tbody>';
        
        // Add logs
        foreach ($logs as $log) {
            $html .= '
            <tr>
                <td>' . $log->id . '</td>
                <td>' . $log->time . '</td>
                <td>' . esc_html($log->username) . '</td>
                <td>' . esc_html($log->user_role) . '</td>
                <td>' . esc_html($log->action) . '</td>
                <td>' . $log->ip . '</td>
                <td>' . esc_html($log->browser) . '</td>
                <td class="' . $log->severity . '">' . strtoupper($log->severity) . '</td>
            </tr>';
        }
        
        // End HTML
        $html .= '
        </tbody>
    </table>
</body>
</html>';
        
        file_put_contents($filepath, $html);
    }
    
    /**
     * Send export email
     */
    private static function send_export_email($to, $filepath, $filename) {
        $subject = 'Activity Logs Export - ' . date('Y-m-d');
        
        $message = 'Please find attached the activity logs export for ' . get_bloginfo('name') . '.';
        
        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
        ];
        
        // Attach file
        $attachments = [$filepath];
        
        // Send email
        wp_mail($to, $subject, $message, $headers, $attachments);
        
        // Delete file after sending
        @unlink($filepath);
    }
    
    /**
     * Render export page
     */
    public static function render_export_page() {
        // Get users for dropdown
        global $wpdb;
        WPAL_Helpers::init();
        $table_name = WPAL_Helpers::$db_table;
        
        $users = $wpdb->get_results("SELECT DISTINCT username FROM $table_name ORDER BY username ASC");
        
        ?>
        <div class="wrap wpal-wrap">
            <h1>Export Activity Logs</h1>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Export Options</h5>
                </div>
                <div class="card-body">
                    <form id="wpal-export-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                        <input type="hidden" name="action" value="wpal_export_logs">
                        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('wpal_export'); ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="export-format">Export Format</label>
                                <select id="export-format" name="format" class="form-control">
                                    <option value="csv">CSV</option>
                                    <option value="json">JSON</option>
                                    <option value="xml">XML</option>
                                    <option value="html">HTML</option>
                                    <?php if (class_exists('TCPDF')): ?>
                                    <option value="pdf">PDF</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="export-severity">Severity</label>
                                <select id="export-severity" name="severity" class="form-control">
                                    <option value="all">All</option>
                                    <option value="info">Info</option>
                                    <option value="warning">Warning</option>
                                    <option value="error">Error</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date-from">Date From</label>
                                <input type="date" id="date-from" name="date_from" class="form-control">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="date-to">Date To</label>
                                <input type="date" id="date-to" name="date_to" class="form-control">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="export-user">User</label>
                                <select id="export-user" name="user" class="form-control">
                                    <option value="">All Users</option>
                                    <?php foreach ($users as $user): ?>
                                    <option value="<?php echo esc_attr($user->username); ?>"><?php echo esc_html($user->username); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="button button-primary">Export Logs</button>
                    </form>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Scheduled Exports</h5>
                </div>
                <div class="card-body">
                    <form id="wpal-scheduled-export-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="scheduled-export">Enable Scheduled Export</label>
                                <div>
                                    <label>
                                        <input type="checkbox" id="scheduled-export" name="scheduled-export" <?php checked(get_option('wpal_scheduled_export', false)); ?>> Send weekly export via email
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="scheduled-export-format">Export Format</label>
                                <select id="scheduled-export-format" name="scheduled-export-format" class="form-control">
                                    <option value="csv" <?php selected(get_option('wpal_scheduled_export_format', 'csv'), 'csv'); ?>>CSV</option>
                                    <option value="json" <?php selected(get_option('wpal_scheduled_export_format', 'csv'), 'json'); ?>>JSON</option>
                                    <option value="xml" <?php selected(get_option('wpal_scheduled_export_format', 'csv'), 'xml'); ?>>XML</option>
                                    <option value="html" <?php selected(get_option('wpal_scheduled_export_format', 'csv'), 'html'); ?>>HTML</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="scheduled-export-email">Email Address</label>
                                <input type="email" id="scheduled-export-email" name="scheduled-export-email" class="form-control" value="<?php echo esc_attr(get_option('wpal_scheduled_export_email', get_option('admin_email'))); ?>">
                            </div>
                        </div>
                        
                        <button type="submit" class="button button-primary">Save Scheduled Export Settings</button>
                    </form>
                    
                    <div id="scheduled-export-message" class="mt-3"></div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Save scheduled export settings
            $('#wpal-scheduled-export-form').on('submit', function(e) {
                e.preventDefault();
                
                const settings = {
                    scheduled_export: $('#scheduled-export').is(':checked'),
                    scheduled_export_format: $('#scheduled-export-format').val(),
                    scheduled_export_email: $('#scheduled-export-email').val()
                };
                
                $.ajax({
                    url: WPAL.ajax_url,
                    method: 'POST',
                    data: {
                        action: 'wpal_save_settings',
                        nonce: WPAL.settings_nonce,
                        settings: settings
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#scheduled-export-message').html('<div class="alert alert-success">' + response.data + '</div>');
                        } else {
                            $('#scheduled-export-message').html('<div class="alert alert-danger">' + response.data + '</div>');
                        }
                    },
                    error: function() {
                        $('#scheduled-export-message').html('<div class="alert alert-danger">An error occurred while saving settings.</div>');
                    }
                });
            });
        });
        </script>
        <?php
    }
}