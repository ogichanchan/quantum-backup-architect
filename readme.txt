=== Quantum Backup Architect ===
Contributors: ogichanchan
Tags: wordpress, plugin, tool, admin, backup, database, files, utility, management, simple
Requires at least: 6.2
Tested up to: 7.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Quantum Backup Architect is a unique, PHP-only WordPress utility designed for simple and efficient site backups. This plugin provides a single-file solution to safeguard your WordPress installation by creating comprehensive backups of both your database and critical files.

It captures your entire WordPress database by generating SQL INSERT statements for all tables within your site's prefix. For files, it archives your `wp-content` directory (including themes, plugins, and uploads), `wp-config.php`, and `.htaccess` file into a `.zip` archive.

Backups are securely stored in a dedicated directory within your `wp-content/uploads` folder (`wp-content/uploads/quantum-backups`). The plugin features an intuitive admin page under 'Tools' where you can easily initiate new backups, view a list of existing backups, download them, or delete them. It adheres to WordPress best practices, utilizing `WP_Filesystem` for file operations and nonces for security.

Focused on minimalism and performance, Quantum Backup Architect offers a straightforward approach to managing your WordPress backups directly from your dashboard.

This plugin is open source. Report bugs at: https://github.com/ogichanchan/quantum-backup-architect

== Installation ==
1. Upload to /wp-content/plugins/
2. Activate

== Changelog ==
= 1.0.0 =
* Initial release.