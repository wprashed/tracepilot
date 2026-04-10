<?php
/**
 * Settings template.
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = TracePilot_Helpers::get_settings();
$roles = wp_roles();
$provider_labels = array(
    'wordfence' => __('Wordfence', 'tracepilot'),
    'patchstack' => __('Patchstack', 'tracepilot'),
    'wpscan'    => __('WPScan', 'tracepilot'),
);
$severity_labels = array(
    'info' => __('Info', 'tracepilot'),
    'warning' => __('Warning', 'tracepilot'),
    'error' => __('Error', 'tracepilot'),
);
$redacted_keys_count = $settings['redact_context_keys'] ? count(array_filter(array_map('trim', preg_split('/[\r\n,]+/', (string) $settings['redact_context_keys'])))) : 0;
$notification_channels_count = count(array_filter(array($settings['notification_email'], $settings['webhook_url'], $settings['slack_webhook_url'], $settings['discord_webhook_url'], $settings['telegram_bot_token'])));
$threat_rule_count = (int) $settings['monitor_failed_logins'] + (int) $settings['monitor_unusual_logins'] + (int) $settings['monitor_file_changes'] + (int) $settings['monitor_privilege_escalation'];
$gdpr_mode_enabled = !empty($settings['gdpr_mode']);
?>

<div class="wrap tracepilot-wrap">
    <section class="tracepilot-hero tracepilot-hero-compact">
        <div>
            <p class="tracepilot-eyebrow"><?php esc_html_e('Controls', 'tracepilot'); ?></p>
            <h1 class="tracepilot-page-title"><?php esc_html_e('Settings', 'tracepilot'); ?></h1>
            <p class="tracepilot-hero-copy"><?php esc_html_e('Configure alert routing, privacy rules, suppression filters, export defaults, timeline behavior, and GDPR safety controls from one place.', 'tracepilot'); ?></p>
        </div>
        <div class="tracepilot-hero-actions">
            <span class="tracepilot-pill"><?php echo !empty($settings['enable_notifications']) ? esc_html__('Alerts enabled', 'tracepilot') : esc_html__('Alerts paused', 'tracepilot'); ?></span>
            <span class="tracepilot-pill"><?php echo !empty($settings['anonymize_ip']) ? esc_html__('IP anonymized', 'tracepilot') : esc_html__('Full IP logging', 'tracepilot'); ?></span>
            <span class="tracepilot-pill"><?php echo !empty($settings['daily_summary_enabled']) ? esc_html__('Daily summaries on', 'tracepilot') : esc_html__('Daily summaries off', 'tracepilot'); ?></span>
            <span class="tracepilot-pill"><?php echo $gdpr_mode_enabled ? esc_html__('GDPR mode on', 'tracepilot') : esc_html__('GDPR mode off', 'tracepilot'); ?></span>
        </div>
    </section>

    <section class="tracepilot-stats-grid">
        <article class="tracepilot-stat-card">
            <span class="tracepilot-stat-label"><?php esc_html_e('Retention Window', 'tracepilot'); ?></span>
            <strong class="tracepilot-stat-value"><?php echo esc_html((int) $settings['log_retention']); ?></strong>
            <span class="tracepilot-stat-meta"><?php esc_html_e('Days of baseline log retention', 'tracepilot'); ?></span>
        </article>
        <article class="tracepilot-stat-card">
            <span class="tracepilot-stat-label"><?php esc_html_e('Notification Channels', 'tracepilot'); ?></span>
            <strong class="tracepilot-stat-value"><?php echo esc_html($notification_channels_count); ?></strong>
            <span class="tracepilot-stat-meta"><?php esc_html_e('Configured delivery paths', 'tracepilot'); ?></span>
        </article>
        <article class="tracepilot-stat-card">
            <span class="tracepilot-stat-label"><?php esc_html_e('Redacted Keys', 'tracepilot'); ?></span>
            <strong class="tracepilot-stat-value"><?php echo esc_html($redacted_keys_count); ?></strong>
            <span class="tracepilot-stat-meta"><?php esc_html_e('Sensitive context fields hidden', 'tracepilot'); ?></span>
        </article>
        <article class="tracepilot-stat-card">
            <span class="tracepilot-stat-label"><?php esc_html_e('Threat Rules', 'tracepilot'); ?></span>
            <strong class="tracepilot-stat-value"><?php echo esc_html($threat_rule_count); ?></strong>
            <span class="tracepilot-stat-meta"><?php esc_html_e('Active detection checks', 'tracepilot'); ?></span>
        </article>
    </section>

    <?php if (isset($_GET['tracepilot_settings_status']) && 'saved' === sanitize_key(wp_unslash($_GET['tracepilot_settings_status']))) : ?>
        <div class="tracepilot-note tracepilot-note-success">
            <?php esc_html_e('Settings saved successfully.', 'tracepilot'); ?>
        </div>
    <?php endif; ?>

    <form id="tracepilot-settings-form" class="tracepilot-form-shell" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('tracepilot_save_settings', 'tracepilot_settings_nonce'); ?>
        <input type="hidden" name="action" value="tracepilot_save_settings_form">
        <input type="hidden" name="tracepilot_active_tab" id="tracepilot-active-tab" value="<?php echo isset($_GET['tracepilot_active_tab']) ? esc_attr(sanitize_key(wp_unslash($_GET['tracepilot_active_tab']))) : 'privacy'; ?>">
        <section class="tracepilot-panel">
            <div class="tracepilot-panel-tabs" data-tracepilot-tabs>
                <button type="button" class="tracepilot-panel-tab is-active" data-tab-target="privacy"><?php esc_html_e('Privacy', 'tracepilot'); ?></button>
                <button type="button" class="tracepilot-panel-tab" data-tab-target="notifications"><?php esc_html_e('Notifications', 'tracepilot'); ?></button>
                <button type="button" class="tracepilot-panel-tab" data-tab-target="security"><?php esc_html_e('Security', 'tracepilot'); ?></button>
                <button type="button" class="tracepilot-panel-tab" data-tab-target="retention"><?php esc_html_e('Retention', 'tracepilot'); ?></button>
                <button type="button" class="tracepilot-panel-tab" data-tab-target="tools"><?php esc_html_e('Tools', 'tracepilot'); ?></button>
            </div>

            <div class="tracepilot-tab-panel is-active" data-tab-panel="privacy">
                <div class="tracepilot-grid tracepilot-grid-2">
                    <section class="tracepilot-panel">
            <div class="tracepilot-panel-head">
                <div>
                    <h2><?php esc_html_e('Retention & Privacy', 'tracepilot'); ?></h2>
                    <p><?php esc_html_e('Decide how long data lives and how much personal data is stored.', 'tracepilot'); ?></p>
                </div>
            </div>
            <div class="tracepilot-toolbar-pills">
                <span class="tracepilot-pill"><?php printf(esc_html__('%d day retention', 'tracepilot'), (int) $settings['log_retention']); ?></span>
                <span class="tracepilot-pill"><?php echo !empty($settings['anonymize_ip']) ? esc_html__('IP anonymization on', 'tracepilot') : esc_html__('Full IP capture', 'tracepilot'); ?></span>
                <span class="tracepilot-pill"><?php printf(esc_html__('%d redacted key(s)', 'tracepilot'), $redacted_keys_count); ?></span>
                <span class="tracepilot-pill"><?php echo $gdpr_mode_enabled ? esc_html__('GDPR guardrails enabled', 'tracepilot') : esc_html__('Manual privacy controls', 'tracepilot'); ?></span>
            </div>
            <div class="tracepilot-form-stack">
                <div class="tracepilot-note tracepilot-gdpr-note">
                    <strong><?php esc_html_e('GDPR mode', 'tracepilot'); ?></strong>
                    <p><?php esc_html_e('When enabled, TracePilot enforces safer defaults: IP anonymization on, geolocation off, stricter retention, and automatic redaction of common personal-data keys.', 'tracepilot'); ?></p>
                    <label class="tracepilot-check">
                        <input type="checkbox" name="wpal_options[gdpr_mode]" value="1" <?php checked($settings['gdpr_mode'], 1); ?>>
                        <span><?php esc_html_e('Enable GDPR privacy guardrails', 'tracepilot'); ?></span>
                    </label>
                    <label class="tracepilot-check">
                        <input type="checkbox" name="wpal_options[mask_ip_in_ui]" value="1" <?php checked($settings['mask_ip_in_ui'], 1); ?> <?php disabled($gdpr_mode_enabled); ?>>
                        <span><?php esc_html_e('Mask IP addresses in admin UI', 'tracepilot'); ?></span>
                    </label>
                    <?php if ($gdpr_mode_enabled) : ?>
                        <p class="tracepilot-list-subtext"><?php esc_html_e('IP masking is locked while GDPR mode is enabled.', 'tracepilot'); ?></p>
                    <?php endif; ?>
                </div>
                <label>
                    <span><?php esc_html_e('Log retention (days)', 'tracepilot'); ?></span>
                    <input class="tracepilot-input" type="number" min="0" name="wpal_options[log_retention]" value="<?php echo esc_attr($settings['log_retention']); ?>">
                </label>
                <label class="tracepilot-check">
                    <input type="checkbox" name="wpal_options[anonymize_ip]" value="1" <?php checked($settings['anonymize_ip'], 1); ?>>
                    <span><?php esc_html_e('Anonymize stored IP addresses', 'tracepilot'); ?></span>
                </label>
                <label>
                    <span><?php esc_html_e('Redact context keys', 'tracepilot'); ?></span>
                    <textarea class="tracepilot-input" rows="3" name="wpal_options[redact_context_keys]" placeholder="<?php esc_attr_e('password,token,email', 'tracepilot'); ?>"><?php echo esc_textarea($settings['redact_context_keys']); ?></textarea>
                </label>
                <label>
                    <span><?php esc_html_e('Timeline window (hours)', 'tracepilot'); ?></span>
                    <input class="tracepilot-input" type="number" min="1" name="wpal_options[timeline_window_hours]" value="<?php echo esc_attr($settings['timeline_window_hours']); ?>">
                </label>
                <div>
                    <span class="tracepilot-section-label"><?php esc_html_e('Exclude roles from logging', 'tracepilot'); ?></span>
                    <div class="tracepilot-check-grid">
                        <?php foreach ($roles->roles as $role_key => $role) : ?>
                            <label class="tracepilot-check-card tracepilot-check-card-compact">
                                <input type="checkbox" name="wpal_options[exclude_roles][]" value="<?php echo esc_attr($role_key); ?>" <?php checked(in_array($role_key, (array) $settings['exclude_roles'], true)); ?>>
                                <span>
                                    <strong><?php echo esc_html($role['name']); ?></strong>
                                    <small><?php echo esc_html($role_key); ?></small>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
                    </section>

                    <section class="tracepilot-panel">
                        <div class="tracepilot-panel-head">
                            <div>
                                <h2><?php esc_html_e('Privacy Tools', 'tracepilot'); ?></h2>
                                <p><?php esc_html_e('Export or delete a specific user’s log history when handling privacy requests.', 'tracepilot'); ?></p>
                            </div>
                        </div>
                        <div class="tracepilot-toolbar-pills">
                            <span class="tracepilot-pill"><?php echo !empty($settings['anonymize_ip']) ? esc_html__('Anonymized storage', 'tracepilot') : esc_html__('Full IP storage', 'tracepilot'); ?></span>
                            <span class="tracepilot-pill"><?php printf(esc_html__('%d redaction rule(s)', 'tracepilot'), $redacted_keys_count); ?></span>
                        </div>
                        <div class="tracepilot-inline-actions tracepilot-inline-actions-tools">
                            <label class="tracepilot-inline-field">
                                <span><?php esc_html_e('User ID', 'tracepilot'); ?></span>
                                <input class="tracepilot-input tracepilot-input-inline tracepilot-privacy-user-id-input" type="number" min="1" placeholder="<?php esc_attr_e('User ID', 'tracepilot'); ?>">
                            </label>
                            <button type="button" class="tracepilot-btn tracepilot-btn-secondary tracepilot-export-user-logs-trigger"><?php esc_html_e('Export User Logs', 'tracepilot'); ?></button>
                            <button type="button" class="tracepilot-btn tracepilot-btn-danger tracepilot-delete-user-logs-trigger"><?php esc_html_e('Delete User Logs', 'tracepilot'); ?></button>
                        </div>
                        <div class="tracepilot-note">
                            <?php esc_html_e('Use these tools when responding to privacy requests without affecting the wider log database.', 'tracepilot'); ?>
                        </div>
                    </section>
                </div>
            </div>

            <div class="tracepilot-tab-panel" data-tab-panel="notifications">
                <div class="tracepilot-grid tracepilot-grid-2">
                    <section class="tracepilot-panel">
            <div class="tracepilot-panel-head">
                <div>
                    <h2><?php esc_html_e('Notification Routing', 'tracepilot'); ?></h2>
                    <p><?php esc_html_e('Send alerts to email, generic webhooks, Slack, and Discord.', 'tracepilot'); ?></p>
                </div>
            </div>
            <div class="tracepilot-toolbar-pills">
                <span class="tracepilot-pill"><?php echo !empty($settings['enable_notifications']) ? esc_html__('Notifications live', 'tracepilot') : esc_html__('Notifications paused', 'tracepilot'); ?></span>
                <span class="tracepilot-pill"><?php printf(esc_html__('%d channel(s) configured', 'tracepilot'), $notification_channels_count); ?></span>
            </div>
            <div class="tracepilot-form-stack">
                <label class="tracepilot-check">
                    <input type="checkbox" name="wpal_options[enable_notifications]" value="1" <?php checked($settings['enable_notifications'], 1); ?>>
                    <span><?php esc_html_e('Enable notifications', 'tracepilot'); ?></span>
                </label>
                <label>
                    <span><?php esc_html_e('Alert email', 'tracepilot'); ?></span>
                    <input class="tracepilot-input" type="email" name="wpal_options[notification_email]" value="<?php echo esc_attr($settings['notification_email']); ?>">
                </label>
                <label>
                    <span><?php esc_html_e('Generic webhook URL', 'tracepilot'); ?></span>
                    <input class="tracepilot-input" type="url" name="wpal_options[webhook_url]" value="<?php echo esc_attr($settings['webhook_url']); ?>">
                </label>
                <label>
                    <span><?php esc_html_e('Slack webhook URL', 'tracepilot'); ?></span>
                    <input class="tracepilot-input" type="url" name="wpal_options[slack_webhook_url]" value="<?php echo esc_attr($settings['slack_webhook_url']); ?>">
                </label>
                <label>
                    <span><?php esc_html_e('Discord webhook URL', 'tracepilot'); ?></span>
                    <input class="tracepilot-input" type="url" name="wpal_options[discord_webhook_url]" value="<?php echo esc_attr($settings['discord_webhook_url']); ?>">
                </label>
                <label>
                    <span><?php esc_html_e('Telegram bot token', 'tracepilot'); ?></span>
                    <input class="tracepilot-input" type="text" name="wpal_options[telegram_bot_token]" value="<?php echo esc_attr($settings['telegram_bot_token']); ?>">
                </label>
                <label>
                    <span><?php esc_html_e('Telegram chat ID', 'tracepilot'); ?></span>
                    <input class="tracepilot-input" type="text" name="wpal_options[telegram_chat_id]" value="<?php echo esc_attr($settings['telegram_chat_id']); ?>">
                </label>
            </div>
                    </section>

                    <section class="tracepilot-panel">
            <div class="tracepilot-panel-head">
                <div>
                    <h2><?php esc_html_e('Vulnerability Intelligence', 'tracepilot'); ?></h2>
                    <p><?php esc_html_e('Connect Wordfence, Patchstack, and WPScan so installed plugins, themes, and WordPress core can be checked against known vulnerabilities.', 'tracepilot'); ?></p>
                </div>
            </div>
            <div class="tracepilot-form-stack">
                <label class="tracepilot-check">
                    <input type="checkbox" name="wpal_options[enable_vulnerability_scanner]" value="1" <?php checked($settings['enable_vulnerability_scanner'], 1); ?>>
                    <span><?php esc_html_e('Enable software vulnerability scanning', 'tracepilot'); ?></span>
                </label>
                <label class="tracepilot-check">
                    <input type="checkbox" name="wpal_options[vulnerability_auto_scan]" value="1" <?php checked($settings['vulnerability_auto_scan'], 1); ?>>
                    <span><?php esc_html_e('Run lightweight weekly scheduled scans', 'tracepilot'); ?></span>
                </label>
                <div>
                    <span class="tracepilot-section-label"><?php esc_html_e('Providers', 'tracepilot'); ?></span>
                    <div class="tracepilot-check-grid">
                        <?php foreach ($provider_labels as $provider_key => $provider_label) : ?>
                            <label class="tracepilot-check-card tracepilot-check-card-compact">
                                <input type="checkbox" name="wpal_options[vulnerability_sources][]" value="<?php echo esc_attr($provider_key); ?>" <?php checked(in_array($provider_key, (array) $settings['vulnerability_sources'], true)); ?>>
                                <span><strong><?php echo esc_html($provider_label); ?></strong></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div>
                    <span class="tracepilot-section-label"><?php esc_html_e('Software scope', 'tracepilot'); ?></span>
                    <div class="tracepilot-check-grid">
                        <label class="tracepilot-check-card tracepilot-check-card-compact">
                            <input type="checkbox" name="wpal_options[vulnerability_scan_plugins]" value="1" <?php checked($settings['vulnerability_scan_plugins'], 1); ?>>
                            <span><strong><?php esc_html_e('Plugins', 'tracepilot'); ?></strong></span>
                        </label>
                        <label class="tracepilot-check-card tracepilot-check-card-compact">
                            <input type="checkbox" name="wpal_options[vulnerability_scan_themes]" value="1" <?php checked($settings['vulnerability_scan_themes'], 1); ?>>
                            <span><strong><?php esc_html_e('Themes', 'tracepilot'); ?></strong></span>
                        </label>
                        <label class="tracepilot-check-card tracepilot-check-card-compact">
                            <input type="checkbox" name="wpal_options[vulnerability_scan_core]" value="1" <?php checked($settings['vulnerability_scan_core'], 1); ?>>
                            <span><strong><?php esc_html_e('WordPress core', 'tracepilot'); ?></strong></span>
                        </label>
                    </div>
                </div>
                <label class="tracepilot-check">
                    <input type="checkbox" name="wpal_options[vulnerability_include_file_integrity]" value="1" <?php checked($settings['vulnerability_include_file_integrity'], 1); ?>>
                    <span><?php esc_html_e('Blend file-integrity changes into the software report', 'tracepilot'); ?></span>
                </label>
                <label>
                    <span><?php esc_html_e('Wordfence API key', 'tracepilot'); ?></span>
                    <input class="tracepilot-input" type="text" name="wpal_options[wordfence_api_key]" value="<?php echo esc_attr($settings['wordfence_api_key']); ?>">
                </label>
                <label>
                    <span><?php esc_html_e('Patchstack API key', 'tracepilot'); ?></span>
                    <input class="tracepilot-input" type="text" name="wpal_options[patchstack_api_key]" value="<?php echo esc_attr($settings['patchstack_api_key']); ?>">
                </label>
                <label>
                    <span><?php esc_html_e('WPScan API token', 'tracepilot'); ?></span>
                    <input class="tracepilot-input" type="text" name="wpal_options[wpscan_api_token]" value="<?php echo esc_attr($settings['wpscan_api_token']); ?>">
                </label>
            </div>
                    </section>
                </div>
            </div>

            <div class="tracepilot-tab-panel" data-tab-panel="security">
                <div class="tracepilot-grid tracepilot-grid-2">
                    <section class="tracepilot-panel">
                        <div class="tracepilot-panel-head">
                            <div>
                                <h2><?php esc_html_e('Alert Filters', 'tracepilot'); ?></h2>
                                <p><?php esc_html_e('Choose which actions and severities trigger alerts.', 'tracepilot'); ?></p>
                            </div>
                        </div>
                        <div class="tracepilot-form-stack">
                            <div>
                                <span class="tracepilot-section-label"><?php esc_html_e('Alert severities', 'tracepilot'); ?></span>
                                <div class="tracepilot-check-grid">
                                    <?php foreach (array('info', 'warning', 'error') as $severity) : ?>
                                        <label class="tracepilot-check-card tracepilot-check-card-compact">
                                            <input type="checkbox" name="wpal_options[notification_severities][]" value="<?php echo esc_attr($severity); ?>" <?php checked(in_array($severity, (array) $settings['notification_severities'], true)); ?>>
                                            <span><strong><?php echo esc_html($severity_labels[ $severity ]); ?></strong></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div>
                                <span class="tracepilot-section-label"><?php esc_html_e('Alert event keys', 'tracepilot'); ?></span>
                                <div class="tracepilot-check-grid">
                                    <?php foreach (array('login_failed', 'plugin_activated', 'plugin_deactivated', 'theme_switched', 'user_role_changed', 'settings_updated') as $event_key) : ?>
                                        <label class="tracepilot-check-card tracepilot-check-card-compact">
                                            <input type="checkbox" name="wpal_options[notification_events][]" value="<?php echo esc_attr($event_key); ?>" <?php checked(in_array($event_key, (array) $settings['notification_events'], true)); ?>>
                                            <span><strong><?php echo esc_html($event_key); ?></strong></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <label class="tracepilot-check">
                                <input type="checkbox" name="wpal_options[enable_webhook_notifications]" value="1" <?php checked($settings['enable_webhook_notifications'], 1); ?>>
                                <span><?php esc_html_e('Enable generic webhook delivery', 'tracepilot'); ?></span>
                            </label>
                            <label>
                                <span><?php esc_html_e('Severity override rules', 'tracepilot'); ?></span>
                                <textarea class="tracepilot-input" rows="4" name="wpal_options[severity_rules]" placeholder="<?php esc_attr_e("login_failed=error\nsettings_updated=warning", 'tracepilot'); ?>"><?php echo esc_textarea($settings['severity_rules']); ?></textarea>
                            </label>
                        </div>
                    </section>

                    <section class="tracepilot-panel">
                        <div class="tracepilot-panel-head">
                            <div>
                                <h2><?php esc_html_e('Threat Controls', 'tracepilot'); ?></h2>
                                <p><?php esc_html_e('Control what the plugin suppresses, protects, and reports as part of security workflows.', 'tracepilot'); ?></p>
                            </div>
                        </div>
                        <div class="tracepilot-toolbar-pills">
                            <span class="tracepilot-pill"><?php printf(esc_html__('%d threat rule(s)', 'tracepilot'), $threat_rule_count); ?></span>
                            <span class="tracepilot-pill"><?php echo !empty($settings['plugin_changes_locked']) ? esc_html__('Plugin changes locked', 'tracepilot') : esc_html__('Plugin changes allowed', 'tracepilot'); ?></span>
                        </div>
                        <div class="tracepilot-form-stack">
                            <label class="tracepilot-check">
                                <input type="checkbox" name="wpal_options[plugin_changes_locked]" value="1" <?php checked($settings['plugin_changes_locked'], 1); ?>>
                                <span><?php esc_html_e('Disable plugin change capability', 'tracepilot'); ?></span>
                            </label>
                            <div class="tracepilot-note">
                                <?php esc_html_e('This only blocks plugin-changing actions. The native Plugins screen itself remains accessible.', 'tracepilot'); ?>
                            </div>
                            <label>
                                <span><?php esc_html_e('Default export format', 'tracepilot'); ?></span>
                                <select class="tracepilot-input" name="wpal_options[default_export_format]">
                                    <?php foreach (array('csv', 'json', 'xml', 'pdf') as $format) : ?>
                                        <option value="<?php echo esc_attr($format); ?>" <?php selected($settings['default_export_format'], $format); ?>><?php echo esc_html(strtoupper($format)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                    </section>
                </div>
            </div>

            <div class="tracepilot-tab-panel" data-tab-panel="retention">
                <div class="tracepilot-grid tracepilot-grid-2">
                    <section class="tracepilot-panel">
            <div class="tracepilot-panel-head">
                <div>
                    <h2><?php esc_html_e('Suppression & Summaries', 'tracepilot'); ?></h2>
                    <p><?php esc_html_e('Reduce noise, tune what gets recorded, and enable scheduled summaries.', 'tracepilot'); ?></p>
                </div>
            </div>
            <div class="tracepilot-form-stack">
                <label>
                    <span><?php esc_html_e('Excluded actions', 'tracepilot'); ?></span>
                    <textarea class="tracepilot-input" rows="4" name="wpal_options[excluded_actions]" placeholder="<?php esc_attr_e('heartbeat_received, autosave', 'tracepilot'); ?>"><?php echo esc_textarea($settings['excluded_actions']); ?></textarea>
                </label>
                <div>
                    <span class="tracepilot-section-label"><?php esc_html_e('Suppressed severities', 'tracepilot'); ?></span>
                    <div class="tracepilot-check-grid">
                        <?php foreach (array('info', 'warning', 'error') as $severity) : ?>
                            <label class="tracepilot-check-card tracepilot-check-card-compact">
                                <input type="checkbox" name="wpal_options[suppressed_severities][]" value="<?php echo esc_attr($severity); ?>" <?php checked(in_array($severity, (array) $settings['suppressed_severities'], true)); ?>>
                                <span><strong><?php echo esc_html($severity_labels[ $severity ]); ?></strong></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <label class="tracepilot-check">
                    <input type="checkbox" name="wpal_options[daily_summary_enabled]" value="1" <?php checked($settings['daily_summary_enabled'], 1); ?>>
                    <span><?php esc_html_e('Send daily summary report', 'tracepilot'); ?></span>
                </label>
                <label>
                    <span><?php esc_html_e('Summary email', 'tracepilot'); ?></span>
                    <input class="tracepilot-input" type="email" name="wpal_options[daily_summary_email]" value="<?php echo esc_attr($settings['daily_summary_email']); ?>">
                </label>
                <label class="tracepilot-check">
                    <input type="checkbox" name="wpal_options[weekly_summary_enabled]" value="1" <?php checked($settings['weekly_summary_enabled'], 1); ?>>
                    <span><?php esc_html_e('Send weekly summary report', 'tracepilot'); ?></span>
                </label>
                <label>
                    <span><?php esc_html_e('Weekly summary email', 'tracepilot'); ?></span>
                    <input class="tracepilot-input" type="email" name="wpal_options[weekly_summary_email]" value="<?php echo esc_attr($settings['weekly_summary_email']); ?>">
                </label>
                <div class="tracepilot-compact-grid">
                    <label>
                        <span><?php esc_html_e('Info retention days', 'tracepilot'); ?></span>
                        <input class="tracepilot-input" type="number" min="0" name="wpal_options[retention_info_days]" value="<?php echo esc_attr($settings['retention_info_days']); ?>">
                    </label>
                    <label>
                        <span><?php esc_html_e('Warning retention days', 'tracepilot'); ?></span>
                        <input class="tracepilot-input" type="number" min="0" name="wpal_options[retention_warning_days]" value="<?php echo esc_attr($settings['retention_warning_days']); ?>">
                    </label>
                    <label>
                        <span><?php esc_html_e('Error retention days', 'tracepilot'); ?></span>
                        <input class="tracepilot-input" type="number" min="0" name="wpal_options[retention_error_days]" value="<?php echo esc_attr($settings['retention_error_days']); ?>">
                    </label>
                </div>
                <label>
                    <span><?php esc_html_e('Action retention rules', 'tracepilot'); ?></span>
                    <textarea class="tracepilot-input" rows="4" name="wpal_options[retention_action_rules]" placeholder="<?php esc_attr_e("login_failed=7\nplugin_activated=180", 'tracepilot'); ?>"><?php echo esc_textarea($settings['retention_action_rules']); ?></textarea>
                </label>
            </div>
                    </section>

                    <section class="tracepilot-panel">
                        <div class="tracepilot-panel-head">
                            <div>
                                <h2><?php esc_html_e('Retention Defaults', 'tracepilot'); ?></h2>
                                <p><?php esc_html_e('Set the baseline storage policy that summaries, exports, and diagnostics will follow.', 'tracepilot'); ?></p>
                            </div>
                        </div>
                        <div class="tracepilot-toolbar-pills">
                            <span class="tracepilot-pill"><?php printf(esc_html__('%d day baseline', 'tracepilot'), (int) $settings['log_retention']); ?></span>
                            <span class="tracepilot-pill"><?php echo !empty($settings['daily_summary_enabled']) ? esc_html__('Daily summary on', 'tracepilot') : esc_html__('Daily summary off', 'tracepilot'); ?></span>
                            <span class="tracepilot-pill"><?php echo !empty($settings['weekly_summary_enabled']) ? esc_html__('Weekly summary on', 'tracepilot') : esc_html__('Weekly summary off', 'tracepilot'); ?></span>
                        </div>
                        <div class="tracepilot-note">
                            <?php esc_html_e('Use per-severity and per-action retention rules when you want security events to live longer than routine informational logs.', 'tracepilot'); ?>
                        </div>
                    </section>
                </div>
            </div>

            <div class="tracepilot-tab-panel" data-tab-panel="tools">
                <div class="tracepilot-grid tracepilot-grid-2">
                    <section class="tracepilot-panel">
                        <div class="tracepilot-panel-head">
                            <div>
                                <h2><?php esc_html_e('Privacy Tools', 'tracepilot'); ?></h2>
                                <p><?php esc_html_e('Export or delete a specific user’s log history when handling privacy requests.', 'tracepilot'); ?></p>
                            </div>
                        </div>
                        <div class="tracepilot-inline-actions tracepilot-inline-actions-tools">
                            <label class="tracepilot-inline-field">
                                <span><?php esc_html_e('User ID', 'tracepilot'); ?></span>
                                <input class="tracepilot-input tracepilot-input-inline tracepilot-privacy-user-id-input" type="number" min="1" placeholder="<?php esc_attr_e('User ID', 'tracepilot'); ?>">
                            </label>
                            <button type="button" class="tracepilot-btn tracepilot-btn-secondary tracepilot-export-user-logs-trigger"><?php esc_html_e('Export User Logs', 'tracepilot'); ?></button>
                            <button type="button" class="tracepilot-btn tracepilot-btn-danger tracepilot-delete-user-logs-trigger"><?php esc_html_e('Delete User Logs', 'tracepilot'); ?></button>
                        </div>
                        <div class="tracepilot-note">
                            <?php esc_html_e('Changes here affect how events are stored, filtered, and delivered. Save once after editing multiple sections so the full configuration stays in sync.', 'tracepilot'); ?>
                        </div>
                    </section>

                    <section class="tracepilot-panel">
                        <div class="tracepilot-panel-head">
                            <div>
                                <h2><?php esc_html_e('Save & Reset', 'tracepilot'); ?></h2>
                                <p><?php esc_html_e('Apply all configuration changes or restore the plugin to a clean baseline.', 'tracepilot'); ?></p>
                            </div>
                        </div>
                        <div class="tracepilot-note">
                            <?php esc_html_e('Save once after editing multiple tabs so notification, retention, security, and privacy settings stay aligned.', 'tracepilot'); ?>
                        </div>
                        <div class="tracepilot-inline-actions">
                <button type="submit" class="tracepilot-btn tracepilot-btn-primary"><?php esc_html_e('Save Settings', 'tracepilot'); ?></button>
                <button type="button" id="tracepilot-reset-settings" class="tracepilot-btn tracepilot-btn-danger"><?php esc_html_e('Reset to Defaults', 'tracepilot'); ?></button>
                <span id="tracepilot-settings-feedback" class="tracepilot-form-feedback" aria-live="polite"></span>
                        </div>
                    </section>
                </div>
            </div>
        </section>
    </form>
</div>
