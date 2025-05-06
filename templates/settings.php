<?php
/**
* Template for the settings page
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$retention_days = get_option('wpal_retention_days', 30);
$log_storage = get_option('wpal_log_storage', 'database');
$track_404_errors = get_option('wpal_track_404_errors', 1);
$track_api_requests = get_option('wpal_track_api_requests', 0);
$notification_email = get_option('wpal_notification_email', get_option('admin_email'));
$notification_events = get_option('wpal_notification_events', array('failed_login', 'plugin_activation', 'plugin_deactivation'));
$daily_report = get_option('wpal_daily_report', 0);

// Get user roles
$roles = wp_roles()->get_names();

// Check if form was submitted
$settings_updated = false;
if (isset($_POST['wpal_save_settings']) && check_admin_referer('wpal_settings_nonce')) {
    // Update settings
    update_option('wpal_retention_days', intval($_POST['wpal_retention_days']));
    update_option('wpal_log_storage', sanitize_text_field($_POST['wpal_log_storage']));
    update_option('wpal_track_404_errors', isset($_POST['wpal_track_404_errors']) ? 1 : 0);
    update_option('wpal_track_api_requests', isset($_POST['wpal_track_api_requests']) ? 1 : 0);
    
    // Update notification settings
    update_option('wpal_notification_email', sanitize_email($_POST['wpal_notification_email']));
    update_option('wpal_notification_events', isset($_POST['wpal_notification_events']) ? $_POST['wpal_notification_events'] : array());
    update_option('wpal_daily_report', isset($_POST['wpal_daily_report']) ? 1 : 0);
    
    $settings_updated = true;
}
?>

<div class="wrap">
    <h1><?php _e('Activity Logger Settings', 'wp-activity-logger-pro'); ?></h1>
    
    <?php if ($settings_updated) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Settings saved successfully.', 'wp-activity-logger-pro'); ?></p>
        </div>
    <?php endif; ?>
    
    <form method="post" action="">
        <?php wp_nonce_field('wpal_settings_nonce'); ?>
        
        <div class="metabox-holder">
            <!-- General Settings -->
            <div class="postbox">
                <h2 class="hndle"><span><?php _e('General Settings', 'wp-activity-logger-pro'); ?></span></h2>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wpal_retention_days"><?php _e('Log Retention (days)', 'wp-activity-logger-pro'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="wpal_retention_days" name="wpal_retention_days" value="<?php echo esc_attr($retention_days); ?>" min="1" max="365" class="small-text">
                                <p class="description"><?php _e('Logs older than this will be automatically deleted.', 'wp-activity-logger-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wpal_log_storage"><?php _e('Log Storage', 'wp-activity-logger-pro'); ?></label>
                            </th>
                            <td>
                                <select id="wpal_log_storage" name="wpal_log_storage">
                                    <option value="database" <?php selected($log_storage, 'database'); ?>><?php _e('Database', 'wp-activity-logger-pro'); ?></option>
                                    <option value="file" <?php selected($log_storage, 'file'); ?>><?php _e('File', 'wp-activity-logger-pro'); ?></option>
                                    <option value="both" <?php selected($log_storage, 'both'); ?>><?php _e('Both Database and File', 'wp-activity-logger-pro'); ?></option>
                                </select>
                                <p class="description"><?php _e('Where to store activity logs.', 'wp-activity-logger-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Tracking Options', 'wp-activity-logger-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e('Tracking Options', 'wp-activity-logger-pro'); ?></span></legend>
                                    <label for="wpal_track_404_errors">
                                        <input type="checkbox" id="wpal_track_404_errors" name="wpal_track_404_errors" value="1" <?php checked($track_404_errors, 1); ?>>
                                        <?php _e('Track 404 Errors', 'wp-activity-logger-pro'); ?>
                                    </label>
                                    <p class="description"><?php _e('Log 404 (Page Not Found) errors.', 'wp-activity-logger-pro'); ?></p>
                                    
                                    <br>
                                    
                                    <label for="wpal_track_api_requests">
                                        <input type="checkbox" id="wpal_track_api_requests" name="wpal_track_api_requests" value="1" <?php checked($track_api_requests, 1); ?>>
                                        <?php _e('Track API Requests', 'wp-activity-logger-pro'); ?>
                                    </label>
                                    <p class="description"><?php _e('Log REST API, AJAX, and XML-RPC requests.', 'wp-activity-logger-pro'); ?></p>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('User Roles to Track', 'wp-activity-logger-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e('User Roles to Track', 'wp-activity-logger-pro'); ?></span></legend>
                                    <?php foreach ($roles as $role_key => $role_name) : ?>
                                        <label for="wpal_track_role_<?php echo esc_attr($role_key); ?>">
                                            <input type="checkbox" id="wpal_track_role_<?php echo esc_attr($role_key); ?>" name="wpal_track_roles[]" value="<?php echo esc_attr($role_key); ?>" checked>
                                            <?php echo esc_html($role_name); ?>
                                        </label><br>
                                    <?php endforeach; ?>
                                </fieldset>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" name="wpal_save_settings" class="button button-primary" value="<?php _e('Save Settings', 'wp-activity-logger-pro'); ?>">
                        <input type="button" name="wpal_reset_settings" class="button" value="<?php _e('Reset to Default', 'wp-activity-logger-pro'); ?>" onclick="if(confirm('<?php _e('Are you sure you want to reset all settings to default?', 'wp-activity-logger-pro'); ?>')) { window.location.href = '<?php echo admin_url('admin.php?page=wp-activity-logger-pro-settings&reset=1'); ?>'; }">
                    </p>
                </div>
            </div>
            
            <!-- Notification Settings -->
            <div class="postbox">
                <h2 class="hndle"><span><?php _e('Notification Settings', 'wp-activity-logger-pro'); ?></span></h2>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wpal_notification_email"><?php _e('Notification Email', 'wp-activity-logger-pro'); ?></label>
                            </th>
                            <td>
                                <input type="email" id="wpal_notification_email" name="wpal_notification_email" value="<?php echo esc_attr($notification_email); ?>" class="regular-text">
                                <p class="description"><?php _e('Email address to receive notifications.', 'wp-activity-logger-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Notification Events', 'wp-activity-logger-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e('Notification Events', 'wp-activity-logger-pro'); ?></span></legend>
                                    <label for="wpal_notify_failed_login">
                                        <input type="checkbox" id="wpal_notify_failed_login" name="wpal_notification_events[]" value="failed_login" <?php checked(in_array('failed_login', $notification_events), true); ?>>
                                        <?php _e('Failed Login Attempts', 'wp-activity-logger-pro'); ?>
                                    </label><br>
                                    
                                    <label for="wpal_notify_plugin_activation">
                                        <input type="checkbox" id="wpal_notify_plugin_activation" name="wpal_notification_events[]" value="plugin_activation" <?php checked(in_array('plugin_activation', $notification_events), true); ?>>
                                        <?php _e('Plugin Activation', 'wp-activity-logger-pro'); ?>
                                    </label><br>
                                    
                                    <label for="wpal_notify_plugin_deactivation">
                                        <input type="checkbox" id="wpal_notify_plugin_deactivation" name="wpal_notification_events[]" value="plugin_deactivation" <?php checked(in_array('plugin_deactivation', $notification_events), true); ?>>
                                        <?php _e('Plugin Deactivation', 'wp-activity-logger-pro'); ?>
                                    </label><br>
                                    
                                    <label for="wpal_notify_theme_switch">
                                        <input type="checkbox" id="wpal_notify_theme_switch" name="wpal_notification_events[]" value="theme_switch" <?php checked(in_array('theme_switch', $notification_events), true); ?>>
                                        <?php _e('Theme Switch', 'wp-activity-logger-pro'); ?>
                                    </label><br>
                                    
                                    <label for="wpal_notify_user_registration">
                                        <input type="checkbox" id="wpal_notify_user_registration" name="wpal_notification_events[]" value="user_registration" <?php checked(in_array('user_registration', $notification_events), true); ?>>
                                        <?php _e('User Registration', 'wp-activity-logger-pro'); ?>
                                    </label><br>
                                    
                                    <label for="wpal_notify_password_reset">
                                        <input type="checkbox" id="wpal_notify_password_reset" name="wpal_notification_events[]" value="password_reset" <?php checked(in_array('password_reset', $notification_events), true); ?>>
                                        <?php _e('Password Reset', 'wp-activity-logger-pro'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Daily Report', 'wp-activity-logger-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e('Daily Report', 'wp-activity-logger-pro'); ?></span></legend>
                                    <label for="wpal_daily_report">
                                        <input type="checkbox" id="wpal_daily_report" name="wpal_daily_report" value="1" <?php checked($daily_report, 1); ?>>
                                        <?php _e('Send Daily Activity Report', 'wp-activity-logger-pro'); ?>
                                    </label>
                                    <p class="description"><?php _e('Receive a daily summary of all activity.', 'wp-activity-logger-pro'); ?></p>
                                </fieldset>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" name="wpal_save_settings" class="button button-primary" value="<?php _e('Save Notification Settings', 'wp-activity-logger-pro'); ?>">
                    </p>
                </div>
            </div>
            
            <!-- Integration Settings -->
            <div class="postbox">
                <h2 class="hndle"><span><?php _e('Integration Settings', 'wp-activity-logger-pro'); ?></span></h2>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="wpal_slack_webhook"><?php _e('Slack Webhook URL', 'wp-activity-logger-pro'); ?></label>
                            </th>
                            <td>
                                <input type="url" id="wpal_slack_webhook" name="wpal_slack_webhook" value="" class="regular-text">
                                <p class="description"><?php _e('Send log notifications to Slack.', 'wp-activity-logger-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wpal_discord_webhook"><?php _e('Discord Webhook URL', 'wp-activity-logger-pro'); ?></label>
                            </th>
                            <td>
                                <input type="url" id="wpal_discord_webhook" name="wpal_discord_webhook" value="" class="regular-text">
                                <p class="description"><?php _e('Send log notifications to Discord.', 'wp-activity-logger-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wpal_telegram_bot_token"><?php _e('Telegram Bot Token', 'wp-activity-logger-pro'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="wpal_telegram_bot_token" name="wpal_telegram_bot_token" value="" class="regular-text">
                                <p class="description"><?php _e('Your Telegram Bot Token.', 'wp-activity-logger-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wpal_telegram_chat_id"><?php _e('Telegram Chat ID', 'wp-activity-logger-pro'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="wpal_telegram_chat_id" name="wpal_telegram_chat_id" value="" class="regular-text">
                                <p class="description"><?php _e('Telegram Chat ID to send notifications to.', 'wp-activity-logger-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Browser Push Notifications', 'wp-activity-logger-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e('Browser Push Notifications', 'wp-activity-logger-pro'); ?></span></legend>
                                    <label for="wpal_browser_push">
                                        <input type="checkbox" id="wpal_browser_push" name="wpal_browser_push" value="1">
                                        <?php _e('Enable Browser Push Notifications', 'wp-activity-logger-pro'); ?>
                                    </label>
                                    <p class="description"  ?>
                                    </label>
                                    <p class="description"><?php _e('Send push notifications to browser.', 'wp-activity-logger-pro'); ?></p>
                                </fieldset>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" name="wpal_save_settings" class="button button-primary" value="<?php _e('Save Integration Settings', 'wp-activity-logger-pro'); ?>">
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Toggle settings sections
    $('.hndle').click(function() {
        $(this).parent().toggleClass('closed');
    });
});
</script>

<?php
// Add footer text
$plugin_data = get_plugin_data(WPAL_PLUGIN_FILE);
$plugin_version = $plugin_data['Version'];
?>
<div class="wpal-footer">
    <p><?php printf(__('Thank you for creating with %s.', 'wp-activity-logger-pro'), '<a href="https://wordpress.org/">WordPress</a>'); ?> <span class="wpal-version"><?php printf(__('Version %s', 'wp-activity-logger-pro'), $plugin_version); ?></span></p>
</div>