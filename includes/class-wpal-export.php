<?php
class WPAL_Export {
    public static function rest_export($request) {
        if (!current_user_can('manage_options')) {
            return new WP_Error('permission_denied', 'You do not have permission to export logs', ['status' => 403]);
        }
        
        $params = $request->get_params();
        $format = isset($params['format']) ? sanitize_text_field($params['format']) : 'csv';
        
        // Get logs based on filters
        $logs = WPAL_Helpers::get_filtered_logs($params);
        
        // Set appropriate headers based on format
        switch ($format) {
            case 'json':
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="activity_logs_' . date('Y-m-d') . '.json"');
                echo json_encode($logs);
                break;
                
            case 'xml':
                header('Content-Type: application/xml');
                header('Content-Disposition: attachment; filename="activity_logs_' . date('Y-m-d') . '.xml"');
                echo self::logs_to_xml($logs);
                break;
                
            case 'csv':
            default:
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="activity_logs_' . date('Y-m-d') . '.csv"');
                
                // Output CSV
                $output = fopen('php://output', 'w');
                
                // Determine headers based on first log entry
                if (!empty($logs)) {
                    $headers = array_keys($logs[0]);
                    fputcsv($output, $headers);
                    
                    foreach ($logs as $log) {
                        fputcsv($output, $log);
                    }
                }
                
                fclose($output);
                break;
        }
        
        exit;
    }
    
    public static function logs_to_xml($logs) {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><logs></logs>');
        
        foreach ($logs as $log) {
            $log_entry = $xml->addChild('log');
            
            foreach ($log as $key => $value) {
                // Handle special characters in XML
                $log_entry->addChild($key, htmlspecialchars($value));
            }
        }
        
        return $xml->asXML();
    }
    
    public static function schedule_export($email, $frequency, $format, $filters) {
        $scheduled_exports = get_option('wpal_scheduled_exports', []);
        
        $id = uniqid('export_');
        $scheduled_exports[$id] = [
            'email' => $email,
            'frequency' => $frequency,
            'format' => $format,
            'filters' => $filters,
            'last_sent' => null,
            'created' => current_time('mysql'),
        ];
        
        update_option('wpal_scheduled_exports', $scheduled_exports);
        
        return $id;
    }
    
    public static function delete_scheduled_export($id) {
        $scheduled_exports = get_option('wpal_scheduled_exports', []);
        
        if (isset($scheduled_exports[$id])) {
            unset($scheduled_exports[$id]);
            update_option('wpal_scheduled_exports', $scheduled_exports);
            return true;
        }
        
        return false;
    }
    
    public static function process_scheduled_exports() {
        $scheduled_exports = get_option('wpal_scheduled_exports', []);
        
        foreach ($scheduled_exports as $id => $export) {
            $should_send = false;
            $now = current_time('timestamp');
            $last_sent = $export['last_sent'] ? strtotime($export['last_sent']) : 0;
            
            switch ($export['frequency']) {
                case 'daily':
                    $should_send = ($now - $last_sent) >= DAY_IN_SECONDS;
                    break;
                    
                case 'weekly':
                    $should_send = ($now - $last_sent) >= WEEK_IN_SECONDS;
                    break;
                    
                case 'monthly':
                    $should_send = ($now - $last_sent) >= 30 * DAY_IN_SECONDS;
                    break;
            }
            
            if ($should_send) {
                self::send_scheduled_export($id, $export);
                
                // Update last sent time
                $scheduled_exports[$id]['last_sent'] = current_time('mysql');
                update_option('wpal_scheduled_exports', $scheduled_exports);
            }
        }
    }
    
    public static function send_scheduled_export($id, $export) {
        // Get logs based on filters
        $logs = WPAL_Helpers::get_filtered_logs($export['filters']);
        
        // Generate export file
        $filename = 'activity_logs_' . date('Y-m-d') . '.' . $export['format'];
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/' . $filename;
        
        switch ($export['format']) {
            case 'json':
                file_put_contents($file_path, json_encode($logs));
                break;
                
            case 'xml':
                file_put_contents($file_path, self::logs_to_xml($logs));
                break;
                
            case 'csv':
            default:
                $output = fopen($file_path, 'w');
                
                if (!empty($logs)) {
                    $headers = array_keys($logs[0]);
                    fputcsv($output, $headers);
                    
                    foreach ($logs as $log) {
                        fputcsv($output, $log);
                    }
                }
                
                fclose($output);
                break;
        }
        
        // Send email with attachment
        $to = $export['email'];
        $subject = 'Activity Logs Export - ' . date('Y-m-d');
        $message = 'Please find attached the activity logs export from your WordPress site.';
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        wp_mail($to, $subject, $message, $headers, [$file_path]);
        
        // Delete the temporary file
        @unlink($file_path);
    }
}

// Schedule daily check for exports
add_action('wpal_daily_maintenance', ['WPAL_Export', 'process_scheduled_exports']);