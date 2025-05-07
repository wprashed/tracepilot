=== WP Activity Logger Pro ===
Contributors: wprashed
Donate link: https://example.com/donate
Tags: activity log, security, audit log, user tracking, monitoring, user activity, admin, logger
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Monitor and log all activity on your WordPress site with detailed reports, real-time notifications, and advanced filtering.

== Description ==

WP Activity Logger Pro provides comprehensive activity tracking for your WordPress site. Monitor user actions, system changes, and security events with an intuitive dashboard and detailed logs.

= Key Features =

* **Comprehensive Activity Tracking** - Log user logins, content changes, plugin/theme updates, and more
* **Real-time Dashboard** - View recent activities and trends at a glance
* **Detailed Activity Logs** - See who did what and when with comprehensive context
* **Advanced Filtering** - Filter logs by user, action type, date range, and severity
* **Export Capabilities** - Export logs to CSV, JSON, or PDF formats
* **Notification System** - Get email alerts for critical events
* **User-friendly Interface** - Intuitive admin interface with modern design
* **Role-based Access Control** - Control who can view and manage logs
* **IP Geolocation** - See where activities originate from
* **Custom Event Tracking** - Track custom events in your themes or plugins

= Use Cases =

* **Security Monitoring** - Keep track of login attempts, user registrations, and role changes
* **Content Change Tracking** - Monitor who creates, edits, or deletes content
* **Troubleshooting** - Identify the cause of issues by reviewing recent activities
* **User Training** - Understand how users interact with your site to provide better training
* **Compliance** - Maintain detailed logs for compliance with regulations like GDPR

= Pro Features =

* Real-time email notifications for critical events
* Advanced export options (CSV, JSON, PDF)
* IP geolocation tracking
* Extended log retention
* Custom event tracking API
* Role-based access control
* Dashboard widgets with activity trends

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wp-activity-logger-pro` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the 'Activity Logger' menu item to access the plugin's features

== Frequently Asked Questions ==

= How far back does the plugin keep logs? =

By default, logs are kept for 30 days, but this can be configured in the Settings page to match your requirements.

= Can I get notified when specific events occur? =

Yes, you can configure email notifications for specific events in the Notifications settings.

= Is this plugin compatible with multisite? =

Yes, WP Activity Logger Pro works with WordPress multisite installations.

= Can I track custom events from my theme or plugins? =

Yes, developers can use our API to log custom events. See the Developer Documentation for details.

= What information is logged for each activity? =

For each activity, we log:
* Date and time
* User (username, ID, and role)
* IP address
* Browser/user agent
* Action performed
* Affected content (if applicable)
* Additional context data

= Is the plugin GDPR compliant? =

Yes, the plugin is designed with privacy in mind. It includes tools to manage and export user data in compliance with GDPR requirements.

== Screenshots ==

1. Dashboard Overview - Get a quick overview of site activity
2. Detailed Activity Logs - View comprehensive logs with filtering options
3. Log Details - See detailed information about each activity
4. Export Options - Export logs in various formats
5. Settings Panel - Configure the plugin to suit your needs

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release of WP Activity Logger Pro.

== Developer Documentation ==

= Logging Custom Events =

Developers can log custom events using the following code:

`
// Make sure the helper class is initialized
WPAL_Helpers::init();

// Log a simple activity
WPAL_Helpers::log_activity(
    'custom_action_name',    // Action identifier
    'Description of action', // Human-readable description
    'info'                   // Severity: 'info', 'warning', or 'error'
);
`

For more detailed documentation, please visit our [Developer Guide](https://example.com/developer-guide).

== Support ==

If you need help with the plugin, please contact our support team at support@example.com or visit our [support portal](https://example.com/support).