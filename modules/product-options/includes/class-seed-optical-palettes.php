<?php
/**
 * Seed / refresh four professional optical ready-made palettes.
 *
 * @package EzLens_Secure_Login
 */
if (!defined('ABSPATH')) {
	exit;
}

class EzLens_PO_Seed_Optical_Palettes {

	const OPTION = 'ezlens_po_optical_seed_v4';

	public static function maybe_seed() {
		$sf = dirname(__FILE__) . '/class-template-file-storage.php';
		if (is_readable($sf)) {
			require_once $sf;
		}
		$pf = dirname(__FILE__) . '/class-preset-code-pack.php';
		if (is_readable($pf)) {
			require_once $pf;
		}

		if (!class_exists('EzLens_Product_Options_Template_Manager')) {
			return;
		}

		$manager = EzLens_Product_Options_Template_Manager::get_instance();
		$slugs = array(
			'glasses-prescription',
			'sunglasses',
			'contact-lens-prescription',
			'medical-contact-lens',
		);

		// Always try to refresh code on existing preset-flagged templates
		self::refresh_existing_codes($slugs);

		if (get_option(self::OPTION)) {
			return;
		}

		foreach ($slugs as $slug) {
			if (method_exists($manager, 'create_from_preset')) {
				$manager->create_from_preset($slug, '');
			}
		}
		update_option(self::OPTION, time(), false);
	}

	/**
	 * Push latest professional code into templates that already exist from presets.
	 */
	private static function refresh_existing_codes(array $slugs) {
		if (!class_exists('EzLens_PO_Preset_Code_Pack') || !class_exists('EzLens_PO_Template_File_Storage')) {
			return;
		}
		if (!class_exists('EzLens_Product_Options_Template_Manager')) {
			return;
		}
		$manager = EzLens_Product_Options_Template_Manager::get_instance();
		if (!method_exists($manager, 'get_list')) {
			return;
		}
		$list = $manager->get_list(array('per_page' => 200));
		$items = array();
		if (is_array($list)) {
			if (isset($list['items']) && is_array($list['items'])) {
				$items = $list['items'];
			} elseif (isset($list[0])) {
				$items = $list;
			}
		}
		foreach ($items as $row) {
			if (!is_array($row)) {
				continue;
			}
			$id = isset($row['id']) ? (int) $row['id'] : 0;
			if ($id < 1) {
				continue;
			}
			$flag = get_option('ezlens_po_preset_flag_' . $id, array());
			$slug = is_array($flag) && !empty($flag['preset_slug']) ? (string) $flag['preset_slug'] : '';
			if ($slug === '' || !in_array($slug, $slugs, true)) {
				continue;
			}
			$pack = EzLens_PO_Preset_Code_Pack::for_slug($slug);
			if (!is_array($pack)) {
				continue;
			}
			$tpl = method_exists($manager, 'get') ? $manager->get($id) : null;
			$fields = is_array($tpl) && isset($tpl['fields']) && is_array($tpl['fields']) ? $tpl['fields'] : array();
			$fields['_code_html'] = (string) ($pack['html'] ?? '');
			$fields['_code_css']  = (string) ($pack['css'] ?? '');
			$fields['_code_js']   = (string) ($pack['js'] ?? '');
			$code = $fields['_code_html'] . "\n\n/**CSS**/\n" . $fields['_code_css'] . "\n\n/**JS**/\n" . $fields['_code_js'];
			$data = array(
				'title'       => is_array($tpl) && isset($tpl['title']) ? $tpl['title'] : $slug,
				'description' => is_array($tpl) && isset($tpl['description']) ? $tpl['description'] : '',
				'status'      => is_array($tpl) && isset($tpl['status']) ? $tpl['status'] : 'active',
				'fields'      => $fields,
				'code'        => $code,
				'is_preset'   => 1,
				'preset_slug' => $slug,
			);
			if (method_exists($manager, 'update')) {
				$manager->update($id, $data);
			}
			EzLens_PO_Template_File_Storage::write($id, $data);
		}
	}
}
