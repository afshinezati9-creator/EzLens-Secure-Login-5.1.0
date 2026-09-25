<?php
/**
 * Product Options palette file storage (like purchase-process/storage).
 *
 * Primary artifact: {id}-{slug}.php  (meta + fields + code snapshot)
 * Code artifact:    {id}-{slug}.code.html  (exact code-editor contents)
 *
 * @package EzLens_Secure_Login
 */
if (!defined('ABSPATH')) {
	exit;
}

class EzLens_PO_Template_File_Storage {

	public static function dir() {
		$dir = trailingslashit(EZLAUTH_MODULES_DIR) . 'product-options/storage/';
		if (!is_dir($dir)) {
			wp_mkdir_p($dir);
		}
		$ht = $dir . '.htaccess';
		if (!file_exists($ht)) {
			@file_put_contents($ht, "Deny from all\n");
		}
		$idx = $dir . 'index.php';
		if (!file_exists($idx)) {
			@file_put_contents($idx, "<?php\n// Silence.\n");
		}
		return $dir;
	}

	public static function slug_from_title($title) {
		$slug = sanitize_title((string) $title);
		if ($slug === '') {
			$slug = 'palette';
		}
		return preg_replace('/[^a-z0-9\-]/', '', $slug);
	}

	public static function basename_for($id, $title) {
		return absint($id) . '-' . self::slug_from_title($title);
	}

	/**
	 * Write meta PHP + raw code file. $data['code'] = full editor string.
	 *
	 * @param int   $id
	 * @param array $data
	 * @return string|false Base name without extension on success
	 */
	public static function write($id, $data) {
		$id = absint($id);
		if ($id < 1 || !is_array($data)) {
			return false;
		}

		$title = isset($data['title']) ? (string) $data['title'] : ('palette-' . $id);
		$code  = isset($data['code']) ? (string) $data['code'] : '';

		// Prefer explicit code; else rebuild from field code keys.
		if ($code === '' && !empty($data['fields']) && is_array($data['fields'])) {
			$f = $data['fields'];
			$html = isset($f['_code_html']) ? (string) $f['_code_html'] : '';
			$css  = isset($f['_code_css']) ? (string) $f['_code_css'] : '';
			$js   = isset($f['_code_js']) ? (string) $f['_code_js'] : '';
			if ($html !== '' || $css !== '' || $js !== '') {
				$code = $html . "\n\n/**CSS**/\n" . $css . "\n\n/**JS**/\n" . $js;
			}
		}

		self::delete($id);

		$base = self::basename_for($id, $title);
		$dir  = self::dir();
		$meta_path = $dir . $base . '.php';
		$code_path = $dir . $base . '.code.html';

		$payload = array(
			'id'          => $id,
			'title'       => $title,
			'description' => isset($data['description']) ? (string) $data['description'] : '',
			'status'      => isset($data['status']) ? (string) $data['status'] : 'active',
			'fields'      => isset($data['fields']) ? $data['fields'] : array(),
			'code'        => $code,
			'code_file'   => $base . '.code.html',
			'is_preset'   => !empty($data['is_preset']) ? 1 : 0,
			'preset_slug' => isset($data['preset_slug']) ? (string) $data['preset_slug'] : '',
			'updated_at'  => current_time('mysql'),
		);

		$body  = "<?php\n";
		$body .= "/**\n * EzLens Product Options palette #{$id}\n * Meta + fields. Raw editor code also in {$base}.code.html\n */\n";
		$body .= "if (!defined('ABSPATH')) { exit; }\n\n";
		$body .= 'return ' . var_export($payload, true) . ";\n";

		$ok_meta = false !== @file_put_contents($meta_path, $body, LOCK_EX);
		$ok_code = false !== @file_put_contents($code_path, $code, LOCK_EX);

		return ($ok_meta || $ok_code) ? $base : false;
	}

	/**
	 * Read raw code-editor contents for a template id.
	 *
	 * @param int $id
	 * @return string
	 */
	public static function read_code($id) {
		$id = absint($id);
		if ($id < 1) {
			return '';
		}
		$dir = self::dir();
		$files = glob($dir . $id . '-*.code.html') ?: array();
		if ($files) {
			// Prefer newest mtime
			usort($files, function ($a, $b) {
				return filemtime($b) - filemtime($a);
			});
			$raw = @file_get_contents($files[0]);
			if (false !== $raw) {
				return (string) $raw;
			}
		}
		// Fallback: meta php payload
		$metas = glob($dir . $id . '-*.php') ?: array();
		foreach ($metas as $m) {
			if (substr($m, -9) === '.code.html') {
				continue;
			}
			$data = include $m;
			if (is_array($data) && isset($data['code'])) {
				return (string) $data['code'];
			}
		}
		return '';
	}

	/**
	 * @param int $id
	 * @return array|null
	 */
	public static function read_meta($id) {
		$id = absint($id);
		$dir = self::dir();
		$metas = glob($dir . $id . '-*.php') ?: array();
		foreach ($metas as $m) {
			if (strpos($m, '.code.html') !== false) {
				continue;
			}
			$data = include $m;
			if (is_array($data)) {
				return $data;
			}
		}
		return null;
	}

	public static function delete($id) {
		$id = absint($id);
		$dir = self::dir();
		foreach (glob($dir . $id . '-*') ?: array() as $f) {
			@unlink($f);
		}
		foreach (array($dir . $id . '.php', $dir . $id . '.code.html') as $plain) {
			if (is_file($plain)) {
				@unlink($plain);
			}
		}
	}
}
