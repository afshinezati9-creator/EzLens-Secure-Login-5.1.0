<?php
/**
 * خواندن/نوشتن فایل‌های HTML/CSS/JS صفحات ورود
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Login_Pages_Storage {

	public static function dir( $slug = '' ) {
		$base = trailingslashit( EZLAUTH_MODULES_DIR . 'login-pages/storage' );
		if ( $slug === '' ) {
			return $base;
		}
		return $base . sanitize_key( $slug ) . '/';
	}

	public static function ensure_dir( $slug ) {
		$dir = self::dir( $slug );
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		return $dir;
	}

	public static function path( $slug, $type ) {
		$type = in_array( $type, array( 'html', 'css', 'js' ), true ) ? $type : 'html';
		return self::dir( $slug ) . 'content.' . $type;
	}

	public static function read( $slug, $type ) {
		$path = self::path( $slug, $type );
		if ( is_readable( $path ) ) {
			return (string) file_get_contents( $path );
		}
		// fallback defaults
		$fallback = EZLAUTH_TEMPLATES_DIR . 'defaults/' . $slug . '.' . $type;
		if ( is_readable( $fallback ) ) {
			return (string) file_get_contents( $fallback );
		}
		return '';
	}

	public static function write( $slug, $type, $content ) {
		if ( ! EzLens_Login_Pages_Registry::is_valid( $slug ) ) {
			return new WP_Error( 'invalid', 'شناسه صفحه نامعتبر است.' );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'cap', 'دسترسی غیرمجاز' );
		}
		$type = in_array( $type, array( 'html', 'css', 'js' ), true ) ? $type : '';
		if ( $type === '' ) {
			return new WP_Error( 'type', 'نوع فایل نامعتبر' );
		}
		$dir = self::ensure_dir( $slug );
		$path = $dir . 'content.' . $type;
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		$ok = file_put_contents( $path, (string) $content );
		if ( false === $ok ) {
			return new WP_Error( 'write', 'نوشتن فایل ممکن نشد.' );
		}
		return true;
	}

	public static function seed_from_defaults( $slug ) {
		self::ensure_dir( $slug );
		foreach ( array( 'html', 'css', 'js' ) as $type ) {
			$dest = self::path( $slug, $type );
			if ( is_readable( $dest ) && filesize( $dest ) > 0 ) {
				continue;
			}
			$src = EZLAUTH_TEMPLATES_DIR . 'defaults/' . $slug . '.' . $type;
			if ( is_readable( $src ) ) {
				copy( $src, $dest );
			} else {
				file_put_contents( $dest, '' );
			}
		}
	}

	/** مهاجرت از option قدیمی ezlens_auth_codes_{slug} */
	public static function maybe_migrate_options( $slug ) {
		$opt = get_option( 'ezlens_auth_codes_' . $slug, null );
		if ( ! is_array( $opt ) ) {
			return;
		}
		foreach ( array( 'html', 'css', 'js' ) as $type ) {
			$path = self::path( $slug, $type );
			if ( is_readable( $path ) && filesize( $path ) > 10 ) {
				continue;
			}
			if ( ! empty( $opt[ $type ] ) ) {
				self::write( $slug, $type, $opt[ $type ] );
			}
		}
	}
}
