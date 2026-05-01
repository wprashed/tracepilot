# TracePilot - User Guide

[Back to README](../readme.md)

## Introduction

This guide explains how to use TracePilot day-to-day: reviewing activity, exporting audit trails, investigating conflicts, and tuning privacy/retention.

## Getting Started

After activating the plugin, you'll find a new menu item "TracePilot" in your WordPress admin menu. This is your gateway to all the plugin's features.

## Included admin areas

- **Dashboard**: at-a-glance summaries and recent activity
- **Activity Logs**: searchable log stream and event details
- **Analytics**: chart views for common activity breakdowns
- **Threat Detection**: scan recent logs for suspicious patterns
- **Server Recommendations**: simple environment + sizing guidance
- **Diagnostics**: system scanner, conflict signals, safe mode debugging
- **Search Console**: optional Google Search Console connection flow
- **Archive**: review archived log entries
- **Export**: download filtered logs as CSV/JSON/XML/plain-text report
- **Settings**: privacy, notifications, security, retention, tools

## Dashboard

The Dashboard provides an overview of recent activities on your site. Here you'll find:

- **Activity Summary**: Quick stats on total activities, users, and severity levels
- **Recent Activities**: The most recent actions logged on your site
- **Activity Trends**: Charts showing patterns over time

You can refresh individual widgets using the refresh icon in the top-right corner of each widget.

## Activity Logs

The Activity Logs page displays a comprehensive list of all logged activities. Features include:

### Viewing Logs

- **Searching**: Use the search box to find specific logs
- **Pagination**: Navigate through multiple pages of logs

### Filtering Logs

Use the filters at the top of the page to narrow down logs by:

- Date range
- Role
- Action key
- Severity

### Log Details

Click the "View" button (eye icon) on any log entry to see detailed information, including:

- Complete user information
- Detailed action description
- Context data (additional information specific to the action)
- Browser and device information
- IP address information

If privacy guardrails are enabled, IP addresses may be masked/anonymized in the UI.

### Managing Logs

- **Delete**: Remove individual log entries using the delete button
- **Archive**: Move an entry to Archive for later reference (when available in your view)

## Export

The Export page allows you to download logs for offline analysis or record-keeping.

### Export Options

- **Format**: Choose from CSV, JSON, XML, or a plain-text report
- **Date Range**: Select the time period for the logs
- **Filters**: Apply the same role/action/severity filters you use in Activity Logs

## Settings

The Settings page allows you to configure how the plugin works.

TracePilot settings are organized into tabs.

### Privacy tab

- **Log Retention**: How long to keep logs (days)
- **GDPR guardrails**: Safer defaults like IP anonymization and stricter retention limits
- **IP anonymization and UI masking**: Store/visualize IPs in a privacy-friendly way
- **Context redaction keys**: Automatically redact common sensitive keys (tokens/emails/etc)
- **Exclude roles from logging**: Useful for ignoring noisy roles (avoid excluding *all* roles)
- **Privacy tools**: Export/delete a specific user’s logs for privacy requests

### Notifications tab

The Notifications page allows you to set up alerts for specific events.

You can route alerts to:

- Email
- Generic webhooks
- Slack webhooks
- Discord webhooks
- Telegram bot + chat ID

Tip: start with high-signal events (failed logins, privilege changes, file integrity changes) and “warning/error” severities.

### Security tab

- Threat detection rules (failed logins, unusual logins, file changes, privilege escalation)
- Vulnerability intelligence provider configuration (Wordfence, Patchstack, WPScan)
- Alert filters (which severities/events should alert)

### Retention tab

- Excluded actions (noise suppression)
- Suppressed severities
- Daily/weekly summaries (email)
- Per-severity retention and per-action retention rules

### Tools tab

- Per-user export/delete tools
- Quick utilities used during investigations

## Troubleshooting

### Common Issues

- **Missing Logs**: Check `Settings -> Privacy` for excluded roles and `Settings -> Retention` for suppression/exclusions
- **Database Size**: If your database is growing too large, reduce the log retention period
- **Performance Issues**: Use excluded actions and retention rules to reduce noise

### Diagnostics

The Diagnostics page provides information about your system and the plugin's status:

- Health score (0-100)
- Issue list (Critical/Warning/Info)
- Conflict signals and suggested next steps
- Admin-only Safe Mode debugging
- Timeline and change-correlation views

This information is valuable when seeking support.

## Conclusion

TracePilot provides comprehensive activity tracking for your WordPress site. By regularly reviewing the logs and setting up appropriate notifications, you can maintain better security and gain valuable insights into how your site is being used.
