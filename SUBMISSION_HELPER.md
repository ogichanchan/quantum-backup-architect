1.  **Plugin Name:** Quantum Backup Architect
2.  **Short Description:** A unique PHP-only WordPress utility. A quantum style backup plugin acting as an architect. Focused on simplicity and efficiency.
3.  **Detailed Description:**
    Quantum Backup Architect is a lightweight, single-file WordPress plugin designed to provide simple and efficient full site backups. Emphasizing a "PHP-only" approach, it requires no external libraries beyond standard PHP extensions (like ZipArchive for file backups) and core WordPress APIs.

    **Key Features:**
    *   **Comprehensive Backups:** Creates full backups encompassing both your WordPress database (as a `.sql` file) and essential WordPress files.
    *   **File Backup Scope:** Includes the entire `wp-content` directory (themes, plugins, uploads) along with crucial root files like `wp-config.php` and `.htaccess`.
    *   **Manual Trigger:** Initiate backups on demand directly from a dedicated admin page.
    *   **Admin Interface:** A user-friendly administration page located under 'Tools' provides options to perform a new backup and manage existing ones.
    *   **Backup Management:** Easily view a list of recent backups, download them individually, or securely delete them from your server.
    *   **Secure Storage:** All backup files are stored within a dedicated `quantum-backups` directory inside your `wp-content/uploads` folder, which is created automatically upon plugin activation.
    *   **Security Focused:** Implements robust nonce and capability checks to ensure only authorized users can perform backup and deletion actions.
    *   **WP_Filesystem API:** Leverages WordPress's built-in `WP_Filesystem` API for safe and reliable file operations.
    *   **Debug Logging:** Integrates with `WP_DEBUG_LOG` to provide detailed logs of backup processes and potential issues.

    This plugin is ideal for users looking for a straightforward, no-frills backup solution that keeps all functionality within WordPress itself, offering transparent control over your site's data.

4.  **GitHub URL:** https://github.com/ogichanchan/quantum-backup-architect