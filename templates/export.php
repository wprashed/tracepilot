<?php
/**
* Template for the export page
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
   exit;
}

global $wpdb;
WPAL_Helpers::init();
?>

<div class="wrap wpal-wrap">
   <h1 class="wp-heading-inline"><?php _e('Export Logs', 'wp-activity-logger-pro'); ?></h1>
   
   <div class="wpal-card">
       <div class="wpal-card-header">
           <h2 class="wpal-card-title">
               <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
               <?php _e('Export Options', 'wp-activity-logger-pro'); ?>
           </h2>
       </div>
       <div class="wpal-card-body">
           <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
               <input type="hidden" name="action" value="wpal_export_logs">
               <?php wp_nonce_field('wpal_export', 'wpal_export_nonce'); ?>
               
               <div class="wpal-filter-section">
                   <div class="wpal-form-group">
                       <label for="export_format" class="wpal-form-label"><?php _e('Export Format', 'wp-activity-logger-pro'); ?></label>
                       <select id="export_format" name="export_format" class="wpal-form-select">
                           <option value="csv"><?php _e('CSV', 'wp-activity-logger-pro'); ?></option>
                           <option value="json"><?php _e('JSON', 'wp-activity-logger-pro'); ?></option>
                           <option value="xml"><?php _e('XML', 'wp-activity-logger-pro'); ?></option>
                           <option value="pdf"><?php _e('PDF', 'wp-activity-logger-pro'); ?></option>
                       </select>
                   </div>
                   
                   <div class="wpal-form-group">
                       <label for="date_range" class="wpal-form-label"><?php _e('Date Range', 'wp-activity-logger-pro'); ?></label>
                       <select id="date_range" name="date_range" class="wpal-form-select">
                           <option value="all"><?php _e('All Time', 'wp-activity-logger-pro'); ?></option>
                           <option value="today"><?php _e('Today', 'wp-activity-logger-pro'); ?></option>
                           <option value="yesterday"><?php _e('Yesterday', 'wp-activity-logger-pro'); ?></option>
                           <option value="this_week"><?php _e('This Week', 'wp-activity-logger-pro'); ?></option>
                           <option value="last_week"><?php _e('Last Week', 'wp-activity-logger-pro'); ?></option>
                           <option value="this_month"><?php _e('This Month', 'wp-activity-logger-pro'); ?></option>
                           <option value="last_month"><?php _e('Last Month', 'wp-activity-logger-pro'); ?></option>
                           <option value="custom"><?php _e('Custom Range', 'wp-activity-logger-pro'); ?></option>
                       </select>
                   </div>
                   
                   <div class="wpal-form-group">
                       <label for="user_filter" class="wpal-form-label"><?php _e('User Filter', 'wp-activity-logger-pro'); ?></label>
                       <select id="user_filter" name="user_filter" class="wpal-form-select">
                           <option value=""><?php _e('All Users', 'wp-activity-logger-pro'); ?></option>
                           <?php
                           $users = $wpdb->get_col("SELECT DISTINCT username FROM " . WPAL_Helpers::$db_table . " ORDER BY username ASC");
                           foreach ($users as $user) {
                               echo '<option value="' . esc_attr($user) . '">' . esc_html($user) . '</option>';
                           }
                           ?>
                       </select>
                   </div>
                   
                   <div class="wpal-form-group">
                       <label for="severity_filter" class="wpal-form-label"><?php _e('Severity Filter', 'wp-activity-logger-pro'); ?></label>
                       <select id="severity_filter" name="severity_filter" class="wpal-form-select">
                           <option value=""><?php _e('All Severities', 'wp-activity-logger-pro'); ?></option>
                           <?php
                           $severities = $wpdb->get_col("SELECT DISTINCT severity FROM " . WPAL_Helpers::$db_table . " ORDER BY severity ASC");
                           foreach ($severities as $severity) {
                               echo '<option value="' . esc_attr($severity) . '">' . esc_html(ucfirst($severity)) . '</option>';
                           }
                           ?>
                       </select>
                   </div>
                   
                   <div class="wpal-form-group">
                       <label for="action_filter" class="wpal-form-label"><?php _e('Action Filter', 'wp-activity-logger-pro'); ?></label>
                       <input type="text" id="action_filter" name="action_filter" class="wpal-form-control" placeholder="<?php _e('Filter by action (leave empty for all)', 'wp-activity-logger-pro'); ?>">
                   </div>
               </div>
               
               <div id="custom_date_range" class="wpal-mt-3" style="display: none;">
                   <div class="wpal-filter-section">
                       <div class="wpal-form-group">
                           <label for="date_from" class="wpal-form-label"><?php _e('From', 'wp-activity-logger-pro'); ?></label>
                           <input type="date" id="date_from" name="date_from" class="wpal-form-control">
                       </div>
                       <div class="wpal-form-group">
                           <label for="date_to" class="wpal-form-label"><?php _e('To', 'wp-activity-logger-pro'); ?></label>
                           <input type="date" id="date_to" name="date_to" class="wpal-form-control">
                       </div>
                   </div>
               </div>
               
               <div class="wpal-form-group wpal-mt-4">
                   <button type="submit" class="wpal-btn wpal-btn-primary">
                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                       <?php _e('Export Logs', 'wp-activity-logger-pro'); ?>
                   </button>
               </div>
           </form>
       </div>
   </div>
   
   <div class="wpal-card wpal-mt-4">
       <div class="wpal-card-header">
           <h2 class="wpal-card-title">
               <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
               <?php _e('Scheduled Exports', 'wp-activity-logger-pro'); ?>
           </h2>
       </div>
       <div class="wpal-card-body">
           <div class="wpal-alert wpal-alert-info wpal-mb-4">
               <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
               <?php _e('Set up automatic exports of your activity logs on a schedule.', 'wp-activity-logger-pro'); ?>
           </div>
           
           <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
               <input type="hidden" name="action" value="wpal_save_scheduled_export">
               <?php wp_nonce_field('wpal_scheduled_export', 'wpal_scheduled_export_nonce'); ?>
               
               <div class="wpal-form-group wpal-mb-4">
                   <div class="wpal-form-check">
                       <input class="wpal-form-check-input" type="checkbox" id="enable_scheduled_export" name="enable_scheduled_export" value="1" <?php checked(get_option('wpal_enable_scheduled_export', false)); ?>>
                       <label class="wpal-form-check-label" for="enable_scheduled_export">
                           <?php _e('Enable Scheduled Exports', 'wp-activity-logger-pro'); ?>
                       </label>
                   </div>
               </div>
               
               <div id="scheduled_export_options" style="<?php echo get_option('wpal_enable_scheduled_export', false) ? '' : 'display: none;'; ?>">
                   <div class="wpal-filter-section">
                       <div class="wpal-form-group">
                           <label for="scheduled_export_frequency" class="wpal-form-label"><?php _e('Frequency', 'wp-activity-logger-pro'); ?></label>
                           <select id="scheduled_export_frequency" name="scheduled_export_frequency" class="wpal-form-select">
                               <option value="daily" <?php selected(get_option('wpal_scheduled_export_frequency', 'weekly'), 'daily'); ?>><?php _e('Daily', 'wp-activity-logger-pro'); ?></option>
                               <option value="weekly" <?php selected(get_option('wpal_scheduled_export_frequency', 'weekly'), 'weekly'); ?>><?php _e('Weekly', 'wp-activity-logger-pro'); ?></option>
                               <option value="monthly" <?php selected(get_option('wpal_scheduled_export_frequency', 'weekly'), 'monthly'); ?>><?php _e('Monthly', 'wp-activity-logger-pro'); ?></option>
                           </select>
                       </div>
                       
                       <div class="wpal-form-group">
                           <label for="scheduled_export_format" class="wpal-form-label"><?php _e('Format', 'wp-activity-logger-pro'); ?></label>
                           <select id="scheduled_export_format" name="scheduled_export_format" class="wpal-form-select">
                               <option value="csv" <?php selected(get_option('wpal_scheduled_export_format', 'csv'), 'csv'); ?>><?php _e('CSV', 'wp-activity-logger-pro'); ?></option>
                               <option value="json" <?php selected(get_option('wpal_scheduled_export_format', 'csv'), 'json'); ?>><?php _e('JSON', 'wp-activity-logger-pro'); ?></option>
                               <option value="xml" <?php selected(get_option('wpal_scheduled_export_format', 'csv'), 'xml'); ?>><?php _e('XML', 'wp-activity-logger-pro'); ?></option>
                           </select>
                       </div>
                       
                       <div class="wpal-form-group">
                           <label for="scheduled_export_email" class="wpal-form-label"><?php _e('Email To', 'wp-activity-logger-pro'); ?></label>
                           <input type="email" id="scheduled_export_email" name="scheduled_export_email" class="wpal-form-control" value="<?php echo esc_attr(get_option('wpal_scheduled_export_email', get_option('admin_email'))); ?>">
                       </div>
                   </div>
               </div>
               
               <div class="wpal-form-group wpal-mt-4">
                   <button type="submit" class="wpal-btn wpal-btn-primary">
                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-save"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                       <?php _e('Save Scheduled Export Settings', 'wp-activity-logger-pro'); ?>
                   </button>
               </div>
           </form>
       </div>
   </div>
</div>

<script>
   jQuery(document).ready(function($) {
       // Toggle custom date range with smooth animation
       $('#date_range').on('change', function() {
           if ($(this).val() === 'custom') {
               $('#custom_date_range').slideDown(300);
           } else {
               $('#custom_date_range').slideUp(300);
           }
       });
       
       // Toggle scheduled export options with smooth animation
       $('#enable_scheduled_export').on('change', function() {
           if ($(this).is(':checked')) {
               $('#scheduled_export_options').slideDown(300);
           } else {
               $('#scheduled_export_options').slideUp(300);
           }
       });
   });
</script>