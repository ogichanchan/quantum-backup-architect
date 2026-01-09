<?php
/**
 * Plugin Name: Quantum Backup Architect
 * Plugin URI: https://github.com/ogichanchan/quantum-backup-architect
 * Description: A unique PHP-only WordPress utility. A quantum style backup plugin acting as a architect. Focused on simplicity and efficiency.
 * Version: 1.0.0
 * Author: ogichanchan
 * Author URI: https://github.com/ogichanchan
 * License: GPLv2 or later
 * Text Domain: quantum-backup-architect
 */

// Deny direct access to the file.
defined( 'ABSPATH' ) || exit;

/**
 * Quantum_Backup_Architect Class.
 *
 * Manages the backup functionality, admin pages, and settings.
 * This class provides a simple, single-file solution for database and file backups.
 */
class Quantum_Backup_Architect {

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	const SLUG = 'quantum-backup-architect';

	/**
	 * Text domain for translations.
	 *
	 * @var string
	 */
	const TEXT_DOMAIN = 'quantum-backup-architect';

	/**
	 * Directory name for backups within wp-content/uploads.
	 *
	 * @var string
	 */
	const BACKUP_DIR_NAME = 'quantum-backups';

	/**
	 * Constructor.
	 * Sets up hooks for admin menu, form submissions, and plugin activation.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_post_qba_initiate_backup', array( $this, 'handle_backup_action' ) );
		add_action( 'admin_post_qba_delete_backup', array( $this, 'handle_delete_action' ) );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
	}

	/**
	 * Plugin activation hook.
	 * Ensures the backup directory exists upon plugin activation.
	 */
	public function activate() {
		$upload_dir  = wp_upload_dir();
		$backup_path = trailingslashit( $upload_dir['basedir'] ) . self::BACKUP_DIR_NAME;

		// Create the backup directory if it doesn't exist.
		if ( ! file_exists( $backup_path ) ) {
			wp_mkdir_p( $backup_path );
		}
	}

	/**
	 * Adds the plugin's admin menu page under 'Tools'.
	 */
	public function add_admin_menu() {
		add_management_page(
			esc_html__( 'Quantum Backup Architect', 'quantum-backup-architect' ),
			esc_html__( 'Quantum Backups', 'quantum-backup-architect' ),
			'manage_options',
			self::SLUG,
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Renders the plugin's admin page with options to initiate backups and view recent ones.
	 */
	public function render_admin_page() {
		// Verify user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'quantum-backup-architect' ) );
		}

		// Display transient messages (success/error notices).
		$message      = get_transient( 'qba_admin_message' );
		$message_type = get_transient( 'qba_admin_message_type' );
		if ( $message && $message_type ) {
			echo '<div class="notice notice-' . esc_attr( $message_type ) . ' is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
			delete_transient( 'qba_admin_message' );
			delete_transient( 'qba_admin_message_type' );
		}

		// Get the full path to the backup directory.
		$upload_dir  = wp_upload_dir();
		$backup_path = trailingslashit( $upload_dir['basedir'] ) . self::BACKUP_DIR_NAME;

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Quantum Backup Architect', 'quantum-backup-architect' ); ?></h1>

			<style>
				/* Inline CSS for the admin page - adheres to "PHP Only" and "No External Files" rules. */
				.qba-container {
					max-width: 960px;
					margin-top: 20px;
					background: #fff;
					border: 1px solid #c3c4c7;
					box-shadow: 0 1px 1px rgba(0,0,0,.04);
					padding: 20px;
				}
				.qba-section {
					margin-bottom: 30px;
					padding-bottom: 20px;
					border-bottom: 1px solid #eee;
				}
				.qba-section:last-child {
					border-bottom: none;
					margin-bottom: 0;
					padding-bottom: 0;
				}
				.qba-section h2 {
					margin-top: 0;
					margin-bottom: 15px;
					font-size: 1.5em;
					border-bottom: 1px solid #eee;
					padding-bottom: 10px;
				}
				.qba-section p {
					font-size: 14px;
					line-height: 1.6;
				}
				.qba-backup-button {
					background-color: #007cba;
					color: #fff;
					border-color: #007cba;
					box-shadow: none;
					text-shadow: none;
					font-size: 16px;
					padding: 8px 16px;
					height: auto;
					line-height: 1.5;
					cursor: pointer;
				}
				.qba-backup-button:hover {
					background-color: #006ba1;
					border-color: #006ba1;
					color: #fff;
				}
				.qba-backup-list table {
					width: 100%;
					border-collapse: collapse;
					margin-top: 15px;
				}
				.qba-backup-list th, .qba-backup-list td {
					border: 1px solid #ddd;
					padding: 8px;
					text-align: left;
				}
				.qba-backup-list th {
					background-color: #f3f3f3;
				}
				.qba-delete-link {
					color: #a00;
					text-decoration: none;
				}
				.qba-delete-link:hover {
					color: #dc3232;
				}
				.qba-no-backups {
					font-style: italic;
					color: #555;
				}
				.qba-info-text {
					margin-bottom: 15px;
					padding: 10px 15px;
					background-color: #e5f5ff;
					border-left: 4px solid #007cba;
				}
				.qba-info-text strong {
					display: block;
					margin-bottom: 5px;
				}
			</style>

			<div class="qba-container">
				<div class="qba-section">
					<h2><?php esc_html_e( 'Initiate a New Backup', 'quantum-backup-architect' ); ?></h2>
					<p class="qba-info-text">
						<strong><?php esc_html_e( 'Important Note:', 'quantum-backup-architect' ); ?></strong>
						<?php esc_html_e( 'A full backup includes both your WordPress database and essential WordPress content files (plugins, themes, uploads, wp-config.php, .htaccess).', 'quantum-backup-architect' ); ?>
						<?php esc_html_e( 'Backups are securely stored in:', 'quantum-backup-architect' ); ?>
						<code><?php echo esc_html( $backup_path ); ?></code>
					</p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="qba_initiate_backup">
						<?php wp_nonce_field( 'qba_initiate_backup_nonce', 'qba_nonce' ); ?>
						<button type="submit" class="button button-primary qba-backup-button">
							<?php esc_html_e( 'Perform Quantum Backup Now', 'quantum-backup-architect' ); ?>
						</button>
					</form>
				</div>

				<div class="qba-section qba-backup-list">
					<h2><?php esc_html_e( 'Recent Backups', 'quantum-backup-architect' ); ?></h2>
					<?php $this->display_backup_list(); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Displays a table of existing backup files with options to download or delete them.
	 */
	private function display_backup_list() {
		$upload_dir  = wp_upload_dir();
		$backup_path = trailingslashit( $upload_dir['basedir'] ) . self::BACKUP_DIR_NAME;

		$backups = array();
		if ( file_exists( $backup_path ) && is_dir( $backup_path ) ) {
			$files = scandir( $backup_path );
			foreach ( $files as $file ) {
				if ( '.' === $file || '..' === $file || ! is_file( $backup_path . $file ) ) {
					continue;
				}
				$file_path = $backup_path . $file;
				$backups[] = array(
					'name' => $file,
					'size' => size_format( filesize( $file_path ), 2 ),
					'date' => date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), filemtime( $file_path ) ),
					'path' => $file_path,
					'url'  => trailingslashit( $upload_dir['baseurl'] ) . self::BACKUP_DIR_NAME . '/' . $file,
				);
			}
		}

		if ( empty( $backups ) ) {
			echo '<p class="qba-no-backups">' . esc_html__( 'No backups found yet.', 'quantum-backup-architect' ) . '</p>';
		} else {
			// Sort backups by modification date, newest first.
			usort( $backups, function( $a, $b ) {
				return filemtime( $b['path'] ) - filemtime( $a['path'] );
			} );

			echo '<table class="wp-list-table widefat fixed striped">';
			echo '<thead><tr>';
			echo '<th scope="col">' . esc_html__( 'Filename', 'quantum-backup-architect' ) . '</th>';
			echo '<th scope="col">' . esc_html__( 'Size', 'quantum-backup-architect' ) . '</th>';
			echo '<th scope="col">' . esc_html__( 'Date Created', 'quantum-backup-architect' ) . '</th>';
			echo '<th scope="col">' . esc_html__( 'Actions', 'quantum-backup-architect' ) . '</th>';
			echo '</tr></thead>';
			echo '<tbody>';
			foreach ( $backups as $backup ) {
				echo '<tr>';
				echo '<td>' . esc_html( $backup['name'] ) . '</td>';
				echo '<td>' . esc_html( $backup['size'] ) . '</td>';
				echo '<td>' . esc_html( $backup['date'] ) . '</td>';
				echo '<td>';
				echo '<a href="' . esc_url( $backup['url'] ) . '" class="button button-secondary" download>' . esc_html__( 'Download', 'quantum-backup-architect' ) . '</a> ';
				echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block; margin-left: 5px;">';
				echo '<input type="hidden" name="action" value="qba_delete_backup">';
				echo '<input type="hidden" name="backup_name" value="' . esc_attr( $backup['name'] ) . '">';
				wp_nonce_field( 'qba_delete_backup_nonce', 'qba_nonce' );
				// Inline JavaScript for confirmation, adhering to "PHP Only" rule.
				echo '<button type="submit" class="button button-link-delete qba-delete-link" onclick="return confirm(\'' . esc_js( __( 'Are you sure you want to delete this backup? This action cannot be undone.', 'quantum-backup-architect' ) ) . '\');">' . esc_html__( 'Delete', 'quantum-backup-architect' ) . '</button>';
				echo '</form>';
				echo '</td>';
				echo '</tr>';
			}
			echo '</tbody>';
			echo '</table>';
		}
	}

	/**
	 * Handles the initiation of a new backup.
	 */
	public function handle_backup_action() {
		// Verify user capabilities and nonce.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to perform this action.', 'quantum-backup-architect' ) );
		}
		check_admin_referer( 'qba_initiate_backup_nonce', 'qba_nonce' );

		$this->log_message( esc_html__( 'Starting quantum backup...', 'quantum-backup-architect' ) );

		$db_backup_success   = $this->backup_database();
		$file_backup_success = $this->backup_files();

		if ( $db_backup_success && $file_backup_success ) {
			$this->set_admin_message( esc_html__( 'Quantum backup completed successfully!', 'quantum-backup-architect' ), 'success' );
		} else {
			$this->set_admin_message( esc_html__( 'Quantum backup completed with some errors. Please check debug log for details.', 'quantum-backup-architect' ), 'error' );
		}

		wp_safe_redirect( admin_url( 'tools.php?page=' . self::SLUG ) );
		exit;
	}

	/**
	 * Handles the deletion of a backup file.
	 */
	public function handle_delete_action() {
		// Verify user capabilities and nonce.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to perform this action.', 'quantum-backup-architect' ) );
		}
		check_admin_referer( 'qba_delete_backup_nonce', 'qba_nonce' );

		$backup_name = isset( $_POST['backup_name'] ) ? sanitize_file_name( wp_unslash( $_POST['backup_name'] ) ) : '';

		if ( empty( $backup_name ) ) {
			$this->set_admin_message( esc_html__( 'Invalid backup name provided for deletion.', 'quantum-backup-architect' ), 'error' );
			wp_safe_redirect( admin_url( 'tools.php?page=' . self::SLUG ) );
			exit;
		}

		$upload_dir             = wp_upload_dir();
		$backup_target_dir      = trailingslashit( $upload_dir['basedir'] ) . self::BACKUP_DIR_NAME;
		$backup_filepath        = $backup_target_dir . $backup_name;

		// Security check: Ensure the file to be deleted is strictly within our backup directory.
		// Prevents directory traversal attacks.
		$real_path              = realpath( $backup_filepath );
		$expected_parent_path   = realpath( $backup_target_dir );

		if ( false === $real_path || false === $expected_parent_path || strpos( $real_path, $expected_parent_path ) !== 0 ) {
			$this->set_admin_message( esc_html__( 'Security warning: Attempted to delete a file outside the designated backup directory. Operation blocked.', 'quantum-backup-architect' ), 'error' );
			$this->log_message( sprintf( esc_html__( 'Security warning: Attempt to delete file outside backup directory. Path: %s', 'quantum-backup-architect' ), $backup_filepath ), 'error' );
		} elseif ( file_exists( $backup_filepath ) ) {
			if ( unlink( $backup_filepath ) ) {
				$this->set_admin_message( sprintf( esc_html__( 'Backup "%s" deleted successfully.', 'quantum-backup-architect' ), $backup_name ), 'success' );
			} else {
				$this->set_admin_message( sprintf( esc_html__( 'Failed to delete backup "%s". Check file permissions.', 'quantum-backup-architect' ), $backup_name ), 'error' );
				$this->log_message( sprintf( esc_html__( 'Failed to delete file: %s', 'quantum-backup-architect' ), $backup_filepath ), 'error' );
			}
		} else {
			$this->set_admin_message( sprintf( esc_html__( 'Backup "%s" not found.', 'quantum-backup-architect' ), $backup_name ), 'warning' );
		}

		wp_safe_redirect( admin_url( 'tools.php?page=' . self::SLUG ) );
		exit;
	}

	/**
	 * Backs up the WordPress database by generating SQL INSERT statements.
	 *
	 * @return bool True on success, false on failure.
	 */
	private function backup_database() {
		global $wpdb;

		$tables          = $wpdb->get_results( 'SHOW TABLES', ARRAY_N );
		$backup_content  = '';
		$charset_collate = $wpdb->get_charset_collate();

		// Add SQL header.
		$backup_content .= "-- Quantum Backup Architect Database Backup\n";
		$backup_content .= "-- Host: " . DB_HOST . "\n";
		$backup_content .= "-- Database: " . DB_NAME . "\n";
		$backup_content .= "-- Generation Time: " . date( 'Y-m-d H:i:s' ) . "\n\n";
		$backup_content .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
		$backup_content .= "SET time_zone = \"+00:00\";\n\n";
		$backup_content .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
		$backup_content .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
		$backup_content .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
		$backup_content .= "/*!40101 SET NAMES " . esc_sql( $wpdb->charset ) . " */;\n\n"; // Use database charset.

		foreach ( $tables as $table ) {
			$table_name = $table[0];

			// Only backup tables belonging to the current WordPress installation prefix.
			if ( strpos( $table_name, $wpdb->prefix ) !== 0 ) {
				$this->log_message( sprintf( esc_html__( 'Skipping table %s (not matching database prefix).', 'quantum-backup-architect' ), $table_name ), 'info' );
				continue;
			}

			// Add DROP TABLE IF EXISTS statement.
			$backup_content .= "DROP TABLE IF EXISTS `" . esc_sql( $table_name ) . "`;\n";

			// Get CREATE TABLE statement.
			$create_table = $wpdb->get_row( 'SHOW CREATE TABLE `' . esc_sql( $table_name ) . '`', ARRAY_N );
			if ( null !== $create_table ) {
				$backup_content .= $create_table[1] . ";\n\n";
			}

			// Get table data and generate INSERT statements.
			$rows = $wpdb->get_results( 'SELECT * FROM `' . esc_sql( $table_name ) . '`', ARRAY_A );
			if ( ! empty( $rows ) ) {
				foreach ( $rows as $row ) {
					$cols = array_map( 'esc_sql', array_keys( $row ) );
					$vals = array_map(
						function( $value ) use ( $wpdb ) {
							return is_null( $value ) ? 'NULL' : "'" . esc_sql( $value ) . "'";
						},
						array_values( $row )
					);
					$backup_content .= "INSERT INTO `" . esc_sql( $table_name ) . "` (`" . implode( '`, `', $cols ) . "`) VALUES (" . implode( ', ', $vals ) . ");\n";
				}
				$backup_content .= "\n";
			}
		}

		// Add SQL footer.
		$backup_content .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
		$backup_content .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
		$backup_content .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";

		$filename        = 'db-' . date( 'Ymd-His' ) . '-' . wp_hash( microtime() ) . '.sql';
		$upload_dir      = wp_upload_dir();
		$backup_filepath = trailingslashit( $upload_dir['basedir'] ) . self::BACKUP_DIR_NAME . '/' . $filename;

		// Use WP_Filesystem API for safe file operations.
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		global $wp_filesystem;
		WP_Filesystem();

		if ( $wp_filesystem->put_contents( $backup_filepath, $backup_content, FS_CHMOD_FILE ) ) {
			$this->log_message( sprintf( esc_html__( 'Database backup successful: %s', 'quantum-backup-architect' ), $filename ) );
			return true;
		} else {
			$this->log_message( sprintf( esc_html__( 'Failed to write database backup file: %s', 'quantum-backup-architect' ), $filename ), 'error' );
			if ( is_wp_error( $wp_filesystem->errors ) ) {
				foreach ( $wp_filesystem->errors->get_error_messages() as $message ) {
					$this->log_message( 'WP_Filesystem error: ' . $message, 'error' );
				}
			}
			return false;
		}
	}

	/**
	 * Backs up WordPress core content files (wp-content, wp-config.php, .htaccess) using ZipArchive.
	 * Requires the PHP ZipArchive extension to be enabled.
	 *
	 * @return bool True on success, false on failure.
	 */
	private function backup_files() {
		if ( ! class_exists( 'ZipArchive' ) ) {
			$this->log_message( esc_html__( 'PHP ZipArchive extension is not available. File backup skipped.', 'quantum-backup-architect' ), 'error' );
			return false;
		}

		$filename        = 'files-' . date( 'Ymd-His' ) . '-' . wp_hash( microtime() ) . '.zip';
		$upload_dir      = wp_upload_dir();
		$backup_filepath = trailingslashit( $upload_dir['basedir'] ) . self::BACKUP_DIR_NAME . '/' . $filename;

		$zip = new ZipArchive();
		if ( $zip->open( $backup_filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE ) === true ) {
			// Path to wp-content.
			$wp_content_path = WP_CONTENT_DIR;
			// Root path for correct relative paths in zip (e.g., /var/www/html/ for wp-content).
			$root_path       = dirname( ABSPATH );

			// Add wp-content directory recursively.
			if ( file_exists( $wp_content_path ) ) {
				$files = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator( $wp_content_path, RecursiveDirectoryIterator::SKIP_DOTS ),
					RecursiveIteratorIterator::LEAVES_ONLY
				);

				foreach ( $files as $name => $file ) {
					// Get real and relative path for current file.
					$file_path     = $file->getRealPath();
					$relative_path = 'wp-content/' . substr( $file_path, strlen( $wp_content_path ) + 1 );

					if ( ! $zip->addFile( $file_path, $relative_path ) ) {
						$this->log_message( sprintf( esc_html__( 'Failed to add file to zip: %s', 'quantum-backup-architect' ), $file_path ), 'error' );
						$zip->close();
						return false;
					}
				}
			} else {
				$this->log_message( esc_html__( 'wp-content directory not found.', 'quantum-backup-architect' ), 'warning' );
			}

			// Add important root files (wp-config.php, .htaccess).
			$root_files_to_backup = array(
				ABSPATH . 'wp-config.php',
				ABSPATH . '.htaccess', // Often crucial for permalinks.
			);

			foreach ( $root_files_to_backup as $file ) {
				if ( file_exists( $file ) ) {
					if ( ! $zip->addFile( $file, basename( $file ) ) ) {
						$this->log_message( sprintf( esc_html__( 'Failed to add root file to zip: %s', 'quantum-backup-architect' ), $file ), 'warning' );
					}
				}
			}

			$zip->close();
			$this->log_message( sprintf( esc_html__( 'Files backup successful: %s', 'quantum-backup-architect' ), $filename ) );
			return true;
		} else {
			$this->log_message( sprintf( esc_html__( 'Failed to create zip archive: %s (Error code: %d)', 'quantum-backup-architect' ), $backup_filepath, $zip->status ), 'error' );
			return false;
		}
	}

	/**
	 * Sets a temporary admin message to be displayed on the next page load.
	 *
	 * @param string $message The message content.
	 * @param string $type    The message type (e.g., 'success', 'error', 'warning', 'info').
	 */
	private function set_admin_message( $message, $type = 'info' ) {
		set_transient( 'qba_admin_message', $message, 30 ); // Keep for 30 seconds.
		set_transient( 'qba_admin_message_type', $type, 30 );
	}

	/**
	 * Logs messages to the debug log if WP_DEBUG_LOG is enabled.
	 *
	 * @param string $message The message to log.
	 * @param string $level   The log level (e.g., 'info', 'warning', 'error').
	 */
	private function log_message( $message, $level = 'info' ) {
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG === true ) {
			error_log( sprintf( '[%s] Quantum Backup Architect (%s): %s', strtoupper( $level ), self::SLUG, $message ) );
		}
	}
}

// Initialize the plugin when WordPress is loaded.
new Quantum_Backup_Architect();