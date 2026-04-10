# TracePilot for WordPress - Frequently Asked Questions

[Back to README](../readme.md)

## General Questions

### What is TracePilot for WordPress?

TracePilot for WordPress is an admin-focused activity log, diagnostics, and threat-review toolkit. It records useful site events, helps you investigate conflicts safely, and supports exports/alerts and privacy workflows.

### How is this different from other logging plugins?

TracePilot for WordPress offers several advantages:
- 🧾 A modern log stream focused on “what changed?” investigations
- 🧪 Diagnostics + conflict signals + admin-only safe mode debugging
- 🔔 Real alert channels (Email, webhooks, Slack, Discord, Telegram)
- 🔐 Privacy guardrails (IP anonymization/masking, context redaction keys, per-user export/delete tools)

### Will this plugin slow down my site?

The plugin is designed to be lightweight and efficient. It uses optimized database queries and indexes to minimize performance impact. In most cases, users won't notice any slowdown.

For high-traffic sites, we recommend:
- Setting a reasonable log retention period
- Using excluded actions and suppressed severities to reduce noise
- Using the database cleanup tools regularly

## Features and Functionality

### What activities does the plugin track?

TracePilot tracks key signals across:

**User Activities:**
- Logins and login attempts
- Logouts
- Failed login attempts
- Profile updates and role changes (when triggered)

**Content Activities:**
- Post/page publish, update, unpublish, trash/restore, and delete
- Comment activities

**System Activities:**
- Plugin activation/deactivation and update/install/delete signals (when WordPress reports them via the upgrader)
- Theme changes
- Theme update/install/delete signals (when reported)
- WordPress core updates

### Can I track custom events from my own plugins?

Yes, we provide a developer API that allows you to log custom events. See our [Developer Guide](developer-guide.md) for details.

### How long are logs kept?

By default, logs are kept for 30 days. You can adjust this in the Settings page to keep logs for a shorter or longer period, depending on your needs.

### Can I export the logs?

Yes, you can export logs in several formats:
- CSV (for spreadsheet analysis)
- JSON (for data processing)
- XML (for integrations)
- Plain-text report (for incident notes)

### Does the plugin support notifications?

Yes. You can route alerts to Email, generic webhooks, Slack, Discord, and Telegram. Start with high-signal events like failed logins, role changes, and file integrity changes.

## Technical Questions

### Is this plugin compatible with multisite?

Yes, TracePilot for WordPress is fully compatible with WordPress multisite installations. It can track activities across all sites in your network.

### Does it work with page builders?

Yes, the plugin is compatible with popular page builders like Elementor, Beaver Builder, Divi, and others. It will track content changes made through these builders.

### Can I migrate logs from another logging plugin?

Currently, we don't provide an automated migration tool. However, if you need to migrate from a specific plugin, contact our support team for assistance.

### Is the plugin GDPR compliant?

Yes, the plugin is designed with privacy in mind. It includes:
- Tools to manage and export user data
- Options to anonymize IP addresses
- Clear data retention policies
- Data export capabilities

### How does IP geolocation work?

If enabled, TracePilot can enrich logs with basic IP context. When GDPR guardrails are enabled, it will default to safer behavior like IP anonymization and UI masking.

## Troubleshooting

### Some activities aren't being logged. Why?

Check the following:
1. Go to `TracePilot -> Settings -> Privacy` and confirm your role is not excluded under “Exclude roles from logging”
2. Go to `TracePilot -> Settings -> Retention` and confirm severities/actions are not suppressed/excluded
3. Perform a high-signal event (failed login, plugin activation) and refresh Activity Logs

### The plugin is using too much database space. What can I do?

To reduce database usage:
1. Decrease the log retention period
2. Exclude noisy actions (heartbeat/autosave) and suppress low-signal severities
3. Export logs for offline retention and keep shorter on-site retention

### I'm not receiving email notifications. How can I fix this?

If notifications aren't working:
1. Check your spam folder
2. Verify the settings in `TracePilot -> Settings -> Notifications`
3. Test your WordPress email functionality with another plugin
4. Consider using an SMTP plugin to improve email deliverability

### How can I see who deleted a specific post?

Look for log entries with the action "post_deleted" or similar. The log will show:
- Who performed the deletion
- When it happened
- Details about the deleted post (if available)

## Advanced Usage

### Can I create custom reports?

While the plugin doesn't include a report builder, you can:
1. Use the export feature to get the data you need
2. Filter logs before exporting to focus on specific activities
3. Use the exported data with your preferred reporting tools

### Can I integrate with third-party services?

Yes, you can use webhooks to send log data to external services. This allows integration with:
- Slack for team notifications
- Custom dashboards
- Security monitoring services
- Data analytics platforms

## Getting Started

### What should I do after installing the plugin?

After installation, we recommend:
1. Reviewing the default settings
2. Setting up notifications for critical events
3. Exploring the dashboard to understand the available information
4. Checking the logs regularly to establish a baseline of normal activity

### How can I get the most out of this plugin?

To maximize the value of TracePilot for WordPress:
1. Configure it to track the events most relevant to your site
2. Set up notifications for critical security events
3. Review logs regularly to understand normal patterns
4. Use the export feature for compliance documentation
5. Integrate with your existing security practices

If you have any other questions not covered here, please don't hesitate to contact our support team.
