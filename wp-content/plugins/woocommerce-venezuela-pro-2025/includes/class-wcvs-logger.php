<?php

/**
 * WooCommerce Venezuela Suite 2025 - Logger
 *
 * Sistema de logging avanzado para debugging
 * y monitoreo del plugin.
 *
 * @package WooCommerce_Venezuela_Suite_2025
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logger class
 */
class WCVS_Logger {

	/**
	 * Log levels
	 */
	const EMERGENCY = 'emergency';
	const ALERT = 'alert';
	const CRITICAL = 'critical';
	const ERROR = 'error';
	const WARNING = 'warning';
	const NOTICE = 'notice';
	const INFO = 'info';
	const DEBUG = 'debug';

	/**
	 * Log file path
	 *
	 * @var string
	 */
	private $log_file;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->log_file = WP_CONTENT_DIR . '/uploads/wcvs-logs/wcvs.log';
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'ensure_log_directory' ) );
	}

	/**
	 * Ensure log directory exists
	 */
	public function ensure_log_directory() {
		$log_dir = dirname( $this->log_file );
		if ( ! file_exists( $log_dir ) ) {
			wp_mkdir_p( $log_dir );
		}
	}

	/**
	 * Log a message
	 *
	 * @param string $message
	 * @param string $level
	 * @param array  $context
	 */
	public function log( $message, $level = self::INFO, $context = array() ) {
		if ( ! $this->should_log( $level ) ) {
			return;
		}

		$log_entry = $this->format_log_entry( $message, $level, $context );
		$this->write_to_file( $log_entry );
	}

	/**
	 * Log emergency message
	 *
	 * @param string $message
	 * @param array  $context
	 */
	public function emergency( $message, $context = array() ) {
		$this->log( $message, self::EMERGENCY, $context );
	}

	/**
	 * Log alert message
	 *
	 * @param string $message
	 * @param array  $context
	 */
	public function alert( $message, $context = array() ) {
		$this->log( $message, self::ALERT, $context );
	}

	/**
	 * Log critical message
	 *
	 * @param string $message
	 * @param array  $context
	 */
	public function critical( $message, $context = array() ) {
		$this->log( $message, self::CRITICAL, $context );
	}

	/**
	 * Log error message
	 *
	 * @param string $message
	 * @param array  $context
	 */
	public function error( $message, $context = array() ) {
		$this->log( $message, self::ERROR, $context );
	}

	/**
	 * Log warning message
	 *
	 * @param string $message
	 * @param array  $context
	 */
	public function warning( $message, $context = array() ) {
		$this->log( $message, self::WARNING, $context );
	}

	/**
	 * Log notice message
	 *
	 * @param string $message
	 * @param array  $context
	 */
	public function notice( $message, $context = array() ) {
		$this->log( $message, self::NOTICE, $context );
	}

	/**
	 * Log info message
	 *
	 * @param string $message
	 * @param array  $context
	 */
	public function info( $message, $context = array() ) {
		$this->log( $message, self::INFO, $context );
	}

	/**
	 * Log debug message
	 *
	 * @param string $message
	 * @param array  $context
	 */
	public function debug( $message, $context = array() ) {
		$this->log( $message, self::DEBUG, $context );
	}

	/**
	 * Check if should log based on level
	 *
	 * @param string $level
	 * @return bool
	 */
	private function should_log( $level ) {
		$log_levels = array(
			self::EMERGENCY => 0,
			self::ALERT => 1,
			self::CRITICAL => 2,
			self::ERROR => 3,
			self::WARNING => 4,
			self::NOTICE => 5,
			self::INFO => 6,
			self::DEBUG => 7
		);

		$current_level = get_option( 'wcvs_log_level', self::INFO );
		$current_level_value = isset( $log_levels[ $current_level ] ) ? $log_levels[ $current_level ] : 6;
		$message_level_value = isset( $log_levels[ $level ] ) ? $log_levels[ $level ] : 6;

		return $message_level_value <= $current_level_value;
	}

	/**
	 * Format log entry
	 *
	 * @param string $message
	 * @param string $level
	 * @param array  $context
	 * @return string
	 */
	private function format_log_entry( $message, $level, $context ) {
		$timestamp = current_time( 'Y-m-d H:i:s' );
		$context_string = ! empty( $context ) ? ' ' . wp_json_encode( $context ) : '';
		
		return sprintf( '[%s] %s: %s%s' . PHP_EOL, $timestamp, strtoupper( $level ), $message, $context_string );
	}

	/**
	 * Write to log file
	 *
	 * @param string $log_entry
	 */
	private function write_to_file( $log_entry ) {
		// Rotate log file if it's too large
		$this->maybe_rotate_log_file();

		// Write to file
		file_put_contents( $this->log_file, $log_entry, FILE_APPEND | LOCK_EX );
	}

	/**
	 * Maybe rotate log file
	 */
	private function maybe_rotate_log_file() {
		if ( ! file_exists( $this->log_file ) ) {
			return;
		}

		$max_size = 5 * 1024 * 1024; // 5MB
		if ( filesize( $this->log_file ) > $max_size ) {
			$backup_file = $this->log_file . '.' . time();
			rename( $this->log_file, $backup_file );
			
			// Keep only last 5 backup files
			$this->cleanup_old_logs();
		}
	}

	/**
	 * Cleanup old log files
	 */
	private function cleanup_old_logs() {
		$log_dir = dirname( $this->log_file );
		$log_files = glob( $log_dir . '/wcvs.log.*' );
		
		if ( count( $log_files ) > 5 ) {
			// Sort by modification time
			usort( $log_files, function( $a, $b ) {
				return filemtime( $a ) - filemtime( $b );
			});

			// Remove oldest files
			$files_to_remove = array_slice( $log_files, 0, count( $log_files ) - 5 );
			foreach ( $files_to_remove as $file ) {
				unlink( $file );
			}
		}
	}

	/**
	 * Get recent log entries
	 *
	 * @param int $lines
	 * @return array
	 */
	public function get_recent_logs( $lines = 100 ) {
		if ( ! file_exists( $this->log_file ) ) {
			return array();
		}

		$log_content = file_get_contents( $this->log_file );
		$log_lines = explode( PHP_EOL, $log_content );
		$log_lines = array_filter( $log_lines );
		
		return array_slice( $log_lines, -$lines );
	}

	/**
	 * Clear log file
	 */
	public function clear_logs() {
		if ( file_exists( $this->log_file ) ) {
			file_put_contents( $this->log_file, '' );
		}
	}
}
