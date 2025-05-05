<?php
class WPAL_Settings {
    public static function init()  type="code"
<?php
class WPAL_Settings {
    public static function init() {
        // Register settings
        register_setting('wpal_settings', 'wpal_retention_days', ['type' => 'integer', 'default' => 30]);
        register_setting('wpal_settings', 'wpal_log_storage', ['type' => 'string', 'default' => 'both']);
        register_setting('wpal_settings', 'wpal_notification_email', ['type' => 'string', 'default' => get_option('admin_email')]);
        register_setting('wpal_settings', 'wpal_notification_events', ['type' => 'array', 'default' => ['login_failed', 'plugin_activated', 'plugin_deactivated']]);
        register_setting('wpal_settings', 'wpal_daily_report', ['type' => 'boolean', 'default' => true]);
        register_setting('wpal_settings', 'wpal_webhook_url', ['type' => 'string', 'default' => '']);
        register_setting('wpal_settings', 'wpal_severity_colors', ['type' => 'array']);
        register_setting('wpal_settings', 'wpal_push_enabled', ['type' => 'boolean', 'default' => false]);
        register_setting('wpal_settings', 'wpal_slack_webhook', ['type' => 'string', 'default' => '']);
        register_setting('wpal_settings', 'wpal_discord_webhook', ['type' => 'string', 'default' => '']);
        register_setting('wpal_settings', 'wpal_telegram_bot_token', ['type' => 'string', 'default' => '']);
        register_setting('wpal_settings', 'wpal_telegram_chat_id', ['type' => 'string', 'default' => '']);
        
        // Add settings sections
        add_settings_section(
            'wpal_general_section',
            'General Settings',
            [__CLASS__, 'render_general_section'],
            'wpal_settings'
        );
        
        add_settings_section(
            'wpal_notifications_section',
            'Notification Settings',
            [__CLASS__, 'render_notifications_section'],
            'wpal_settings'
        );
        
        add_settings_section(
            'wpal_integrations_section',
            'Integrations',
            [__CLASS__, 'render_integrations_section'],
            'wpal_settings'
        );
        
        add_settings_section(
            'wpal_advanced_section',
            'Advanced Settings',
            [__CLASS__, 'render_advanced_section'],
            'wpal_settings'
        );
        
        // Add settings fields
        // General Section
        add_settings_field(
            'wpal_log_storage',
            'Log Storage Method',
            [__CLASS__, 'render_log_storage_field'],
            'wpal_settings',
            'wpal_general_section'
        );
        
        add_settings_field(
            'wpal_retention_days',
            'Log Retention Period (days)',
            [__CLASS__, 'render_retention_days_field'],
            'wpal_settings',
            'wpal_general_section'
        );
        
        // Notifications Section
        add_settings_field(
            'wpal_notification_email',
            'Notification Email',
            [__CLASS__, 'render_notification_email_field'],
            'wpal_settings',
            'wpal_notifications_section'
        );
        
        add_settings_field(
            'wpal_notification_events',
            'Events to Notify',
            [__CLASS__, 'render_notification_events_field'],
            'wpal_settings',
            'wpal_notifications_section'
        );
        
        add_settings_field(
            'wpal_daily_report',
            'Daily Summary Report',
            [__CLASS__, 'render_daily_report_field'],
            'wpal_settings',
            'wpal_notifications_section'
        );
        
        // Integrations Section
        add_settings_field(
            'wpal_webhook_url',
            'Webhook URL',
            [__CLASS__, 'render_webhook_url_field'],
            'wpal_settings',
            'wpal_integrations_section'
        );
        
        add_settings_field(
            'wpal_slack_webhook',
            'Slack Webhook URL',
            [__CLASS__, 'render_slack_webhook_field'],
            'wpal_settings',
            'wpal_integrations_section'
        );
        
        add_settings_field(
            'wpal_discord_webhook',
            'Discord Webhook URL',
            [__CLASS__, 'render_discord_webhook_field'],
            'wpal_settings',
            'wpal_integrations_section'
        );
        
        add_settings_field(
            'wpal_telegram_settings',
            'Telegram Integration',
            [__CLASS__, 'render_telegram_fields'],
            'wpal_settings',
            'wpal_integrations_section'
        );
        
        // Advanced Section
        add_settings_field(
            'wpal_push_enabled',
            'Real-time Push Notifications',
            [__CLASS__, 'render_push_enabled_field'],
            'wpal_settings',
            'wpal_advanced_section'
        );
        
        add_settings_field(
            'wpal_severity_colors',
            'Severity Colors',
            [__CLASS__, 'render_severity_colors_field'],
            'wpal_settings',
            'wpal_advanced_section'
        );
    }
    
    public static function render_general_section() {
        echo '<p>Configure general settings for the Activity Logger.</p>';
    }
    
    public static function render_notifications_section() {
        echo '<p>Configure email notifications and reports.</p>';
    }
    
    public static function render_integrations_section() {
        echo '<p>Integrate with external services to receive notifications.</p>';
    }
    
    public static function render_advanced_section() {
        echo '<p>Advanced settings for the Activity Logger.</p>';
    }
    
    public static function render_log_storage_field() {
        $value = get_option('wpal_log_storage', 'both');
        ?>
        <select name="wpal_log_storage" id="wpal_log_storage">
            <option value="csv" <?php selected($value, 'csv'); ?>>CSV Only</option>
            <option value="db" <?php selected($value, 'db'); ?>>Database Only</option>
            <option value="both" <?php selected($value, 'both'); ?>>Both CSV and Database</option>
        </select>
        <p class="description">Choose where to store activity logs. Using both methods provides redundancy but uses more storage.</p>
        <?php
    }
    
    public static function render_retention_days_field() {
        $value = get_option('wpal_retention_days', 30);
        ?>
        <input type="number" name="wpal_retention_days" id="wpal_retention_days" value="<?php echo esc_attr($value); ?>" min="1" max="365">
        <p class="description">Number of days to keep logs before automatic cleanup. Use 0 to keep logs indefinitely (not recommended).</p>
        <?php
    }
    
    public static function render_notification_email_field() {
        $value = get_option('wpal_notification_email', get_option('admin_email'));
        ?>
        <input type="email" name="wpal_notification_email" id="wpal_notification_email" value="<?php echo esc_attr($value); ?>" class="regular-text">
        <p class="description">Email address to receive notifications. Multiple emails can be separated by commas.</p>
        <?php
    }
    
    public static function render_notification_events_field() {
        $events = get_option('wpal_notification_events', ['login_failed', 'plugin_activated', 'plugin_deactivated']);
        $available_events = [
            'login_failed' => 'Failed Login Attempts',
            'plugin_activated' => 'Plugin Activations',
            'plugin_deactivated' => 'Plugin Deactivations',
            'user_register' => 'New User Registrations',
            'user_deleted' => 'User Deletions',
            'post_deleted' => 'Post Deletions',
            'order_status' => 'WooCommerce Order Status Changes',
            'payment_complete' => 'WooCommerce Payment Completions',
        ];
        
        foreach ($available_events as $event => $label) {
            $checked = in_array($event, $events) ? 'checked' : '';
            echo '<label><input type="checkbox" name="wpal_notification_events[]" value="' . esc_attr($event) . '" ' . $checked . '> ' . esc_html($label) . '</label><br>';
        }
        
        echo '<p class="description">Select events that should trigger email notifications.</p>';
    }
    
    public static function render_daily_report_field() {
        $value = get_option('wpal_daily_report', true);
        ?>
        <label>
            <input type="checkbox" name="wpal_daily_report" value="1" <?php checked($value, true); ?>>
            Send daily summary report
        </label>
        <p class="description">Receive a daily email with a summary of all activity.</p>
        <?php
    }
    
    public static function render_webhook_url_field() {
        $value = get_option('wpal_webhook_url', '');
        ?>
        <input type="url" name="wpal_webhook_url" id="wpal_webhook_url" value="<?php echo esc_attr($value); ?>" class="regular-text">
        <p class="description">URL to send webhook notifications for new log entries. Leave empty to disable.</p>
        <?php
    }
    
    public static function render_slack_webhook_field() {
        $value = get_option('wpal_slack_webhook', '');
        ?>
        <input type="url" name="wpal_slack_webhook" id="wpal_slack_webhook" value="<?php echo esc_attr($value); ?>" class="regular-text">
        <p class="description">Slack Incoming Webhook URL to receive notifications. Leave empty to disable.</p>
        <?php
    }
    
    public static function render_discord_webhook_field() {
        $value = get_option('wpal_discord_webhook', '');
        ?>
        <input type="url" name="wpal_discord_webhook" id="wpal_discord_webhook" value="<?php echo esc_attr($value); ?>" class="regular-text">
        <p class="description">Discord Webhook URL to receive notifications. Leave empty to disable.</p>
        <?php
    }
    
    public static function render_telegram_fields() {
        $bot_token = get_option('wpal_telegram_bot_token', '');
        $chat_id = get_option('wpal_telegram_chat_id', '');
        ?>
        <p>
            <label for="wpal_telegram_bot_token">Bot Token:</label>
            <input type="text" name="wpal_telegram_bot_token" id="wpal_telegram_bot_token" value="<?php echo esc_attr($bot_token); ?>" class="regular-text">
        </p>
        <p>
            <label for="wpal_telegram_chat_id">Chat ID:</label>
            <input type="text" name="wpal_telegram_chat_id" id="wpal_telegram_chat_id" value="<?php echo esc_attr($chat_id); ?>" class="regular-text">
        </p>
        <p class="description">Configure Telegram bot to receive notifications. Leave empty to disable.</p>
        <?php
    }
    
    public static function render_push_enabled_field() {
        $value = get_option('wpal_push_enabled', false);
        ?>
        <label>
            <input type="checkbox" name="wpal_push_enabled" value="1" <?php checked($value, true); ?>>
            Enable real-time push notifications in admin dashboard
        </label>
        <p class="description">Show real-time notifications in the admin dashboard when new activities occur.</p>
        <?php
    }
    
    public static function render_severity_colors_field() {
        $colors = get_option('wpal_severity_colors', [
            'info' => '#28a745',
            'warning' => '#ffc107',
            'error' => '#dc3545',
        ]);
        ?>
        <p>
            <label for="wpal_severity_colors_info">Info:</label>
            <input type="color" name="wpal_severity_colors[info]" id="wpal_severity_colors_info" value="<?php echo esc_attr($colors['info']); ?>">
        </p>
        <p>
            <label for="wpal_severity_colors_warning">Warning:</label>
            <input type="color" name="wpal_severity_colors[warning]" id="wpal_severity_colors_warning" value="<?php echo esc_attr($colors['warning']); ?>">
        </p>
        <p>
            <label for="wpal_severity_colors_error">Error:</label>
            <input type="color" name="wpal_severity_colors[error]" id="wpal_severity_colors_error" value="<?php echo esc_attr($colors['error']); ?>">
        </p>
        <p class="description">Customize colors for different severity levels in the logs display.</p>
        <?php
    }
}