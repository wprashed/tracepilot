# TracePilot for WordPress - Installation Guide

[Back to README](../readme.md)

This guide walks you through installing TracePilot and verifying that activity logs are recording correctly.

## System Requirements

Before installing, ensure your system meets the following requirements:

- WordPress 6.0 or higher
- PHP 7.4 or higher
- MySQL 5.6+ (or MariaDB equivalent)
- PHP extensions: `json`, `mysqli`
- WordPress user with administrator privileges

## Installation Methods

### Method 1: Install via WordPress Admin (Recommended)

1. Log in to your WordPress admin dashboard
2. Navigate to **Plugins > Add New**
3. Click the **Upload Plugin** button at the top of the page
4. Click **Choose File** and select the plugin ZIP file
5. Click **Install Now**
6. After installation completes, click **Activate Plugin**

### Method 2: Install via FTP

1. Extract the plugin ZIP file on your computer
2. Connect to your website using an FTP client
3. Navigate to the `/wp-content/plugins/` directory
4. Upload the plugin folder to this directory
5. Log in to your WordPress admin dashboard
6. Navigate to **Plugins**
7. Find "TracePilot for WordPress" and click **Activate**

## Post-Installation Setup

After activating the plugin, follow these steps to complete the setup:

### 1. Verify the log table exists

The plugin creates its log tables on activation and also re-checks the schema in wp-admin.

If you don’t see logs after doing real activity, jump to the troubleshooting section below.

### 2. Quick privacy defaults (recommended)

1. Navigate to **TracePilot > Settings**
2. Open the **Privacy** tab
3. If you need safer defaults, enable **GDPR guardrails**
4. Optional: enable IP anonymization and context redaction keys

### 3. Set up real alerts (optional)

If you want to receive notifications for certain events:

1. Navigate to **TracePilot > Settings**
2. Open the **Notifications** tab
3. Enable notifications
4. Configure one or more channels:
   - Email
   - Generic webhook
   - Slack webhook
   - Discord webhook
   - Telegram bot token + chat ID

### 4. Reduce noise (optional)

1. Navigate to **TracePilot > Settings**
2. Open **Retention** / **Security** tabs
3. Add excluded actions, suppress low-signal severities, and configure retention

## Verifying Installation

To verify that the plugin is working correctly:

1. Navigate to **TracePilot > Dashboard**
2. Navigate to **TracePilot > Activity Logs**
3. Perform one of these actions and refresh the log list:
   - Update a page or post
   - Activate or deactivate a plugin
   - Trigger a failed login attempt

If you see new entries, installation is good.

## Troubleshooting

### Common Installation Issues

#### Plugin Menu Not Appearing

If the TracePilot menu doesn't appear:

1. Clear your browser cache
2. Log out and log back in to WordPress
3. Check that your user has administrator privileges

#### Logs Not Being Recorded

If activities are not being logged:

1. Navigate to **TracePilot > Settings**
2. In **Privacy**, confirm you are not excluding your role under **Exclude roles from logging**
3. In **Retention/Suppression**, confirm severities are not suppressed and actions are not excluded
4. Try a high-signal event (failed login, plugin activation) and refresh **Activity Logs**

## Upgrading

When upgrading from a previous version:

1. Back up your WordPress database
2. Install the new version over the old one
3. The plugin will automatically check/upgrade tables in wp-admin

## Uninstallation

If you need to uninstall the plugin:

1. Navigate to **Plugins**
2. Deactivate "TracePilot for WordPress"
3. Click **Delete**

Note: WordPress core “Delete” removes plugin files. Database cleanup behavior depends on your site policy and whether you want to retain audit history.

## Getting Help

If you encounter any issues during installation:

- Review the [FAQ](faq.md)

## Next Steps

Now that you've installed TracePilot for WordPress, you might want to:

- [Use the plugin day-to-day](user-guide.md)
- [Configure privacy, alerts, and suppression](user-guide.md#settings)
- [Log custom events from your code](developer-guide.md#logging-custom-events)
