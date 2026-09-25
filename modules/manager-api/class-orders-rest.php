<?php
/**
 * Manager Orders extras: prescriptions, product options on order, notify customer.
 * Namespace: ezlens/v1
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
	exit;
}

class EzLens_Manager_Orders_REST {

	const NS = 'ezlens/v1';

	public static function init() {
		add_action('rest_api_init', array(__CLASS__, 'register_routes'));
	}

	public static function register_routes() {
		register_rest_route(self::NS, '/manager/orders/(?P<id>\d+)/context', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array(__CLASS__, 'order_context'),
			'permission_callback' => array(__CLASS__, 'can_manage'),
		));

		register_rest_route(self::NS, '/manager/orders/(?P<id>\d+)/notify', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array(__CLASS__, 'notify_customer'),
			'permission_callback' => array(__CLASS__, 'can_manage'),
		));

		register_rest_route(self::NS, '/manager/customers/(?P<id>\d+)/prescriptions', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array(__CLASS__, 'customer_prescriptions'),
			'permission_callback' => array(__CLASS__, 'can_manage'),
		));
	}

	public static function can_manage() {
		return current_user_can('manage_woocommerce')
			|| current_user_can('manage_options')
			|| current_user_can('edit_shop_orders');
	}

	/**
	 * Combined context for one order: product options + patient prescriptions.
	 */
	public static function order_context(WP_REST_Request $request) {
		$order_id = (int) $request['id'];
		if (!function_exists('wc_get_order')) {
			return new WP_Error('no_wc', 'WooCommerce در دسترس نیست', array('status' => 500));
		}
		$order = wc_get_order($order_id);
		if (!$order) {
			return new WP_Error('not_found', 'سفارش یافت نشد', array('status' => 404));
		}

		$product_options = self::extract_line_options($order);
		$customer_id     = (int) $order->get_customer_id();
		$prescriptions   = $customer_id > 0
			? self::load_prescriptions_for_user($customer_id)
			: array();

		// Also include order-level meta that looks like prescription
		$order_rx = self::extract_order_rx_meta($order);
		if (!empty($order_rx)) {
			$prescriptions = array_merge(
				array(
					array(
						'id'         => 'order-' . $order_id,
						'source'     => 'order',
						'title'      => 'اطلاعات ثبت‌شده روی این سفارش',
						'fields'     => $order_rx,
						'created_at' => $order->get_date_created() ? $order->get_date_created()->date('Y-m-d H:i:s') : '',
					),
				),
				$prescriptions
			);
		}

		return rest_ensure_response(array(
			'order_id'            => $order_id,
			'customer_id'         => $customer_id,
			'product_options'     => $product_options,
			'prescriptions'       => $prescriptions,
			'billing_email'       => $order->get_billing_email(),
			'billing_phone'       => $order->get_billing_phone(),
			'status'              => $order->get_status(),
			'status_label'        => wc_get_order_status_name($order->get_status()),
		));
	}

	private static function extract_line_options(WC_Order $order) {
		$out = array();
		foreach ($order->get_items() as $item_id => $item) {
			$meta_rows = array();
			foreach ($item->get_meta_data() as $meta) {
				$data = $meta->get_data();
				$key  = isset($data['key']) ? (string) $data['key'] : '';
				$val  = isset($data['value']) ? $data['value'] : '';
				if ($key === '' || strpos($key, '_') === 0) {
					// skip internal WC keys mostly; keep if useful
					if (!in_array($key, array('_ezlens_options', 'ezlens_options'), true)) {
						continue;
					}
				}
				if (is_array($val)) {
					$val = wp_json_encode($val, JSON_UNESCAPED_UNICODE);
				}
				$meta_rows[] = array(
					'key'   => $key,
					'label' => self::human_label($key),
					'value' => is_scalar($val) ? (string) $val : wp_json_encode($val, JSON_UNESCAPED_UNICODE),
				);
			}

			// Flatten serialized ezlens_options blob if present
			$raw = $item->get_meta('ezlens_options', true);
			if (empty($raw)) {
				$raw = $item->get_meta('_ezlens_options', true);
			}
			if (is_string($raw) && $raw !== '') {
				$maybe = json_decode($raw, true);
				if (is_array($maybe)) {
					$raw = $maybe;
				}
			}
			if (is_array($raw)) {
				foreach ($raw as $k => $v) {
					if (is_array($v)) {
						$v = implode('، ', array_map('strval', $v));
					}
					$meta_rows[] = array(
						'key'   => (string) $k,
						'label' => self::human_label((string) $k),
						'value' => (string) $v,
					);
				}
			}

			$out[] = array(
				'item_id'    => (int) $item_id,
				'product_id' => (int) $item->get_product_id(),
				'name'       => $item->get_name(),
				'quantity'   => (int) $item->get_quantity(),
				'options'    => $meta_rows,
			);
		}
		return $out;
	}

	private static function extract_order_rx_meta(WC_Order $order) {
		$fields = array();
		$keys_interest = array('od_', 'os_', 'pd', 'sph', 'cyl', 'axis', 'add', 'bc', 'dia', 'rx_', 'prescription', 'doctor', 'نسخه');
		foreach ($order->get_meta_data() as $meta) {
			$data = $meta->get_data();
			$key  = isset($data['key']) ? (string) $data['key'] : '';
			$val  = isset($data['value']) ? $data['value'] : '';
			if ($key === '' || strpos($key, '_') === 0 && strpos($key, '_ezlens') !== 0) {
				continue;
			}
			$lk = strtolower($key);
			$hit = false;
			foreach ($keys_interest as $needle) {
				if (strpos($lk, strtolower($needle)) !== false) {
					$hit = true;
					break;
				}
			}
			if (!$hit) {
				continue;
			}
			if (is_array($val)) {
				$val = wp_json_encode($val, JSON_UNESCAPED_UNICODE);
			}
			$fields[] = array(
				'key'   => $key,
				'label' => self::human_label($key),
				'value' => (string) $val,
			);
		}
		return $fields;
	}

	private static function load_prescriptions_for_user($user_id) {
		$user_id = (int) $user_id;
		$out     = array();

		// Common CD / EzLens meta keys (flexible)
		$meta_keys = array(
			'ezlens_prescriptions',
			'ezlens_rx_list',
			'ezcd_prescriptions',
			'ezlens_patient_records',
			'_ezlens_prescriptions',
		);
		foreach ($meta_keys as $mk) {
			$raw = get_user_meta($user_id, $mk, true);
			if (empty($raw)) {
				continue;
			}
			if (is_string($raw)) {
				$decoded = json_decode($raw, true);
				if (is_array($decoded)) {
					$raw = $decoded;
				}
			}
			if (!is_array($raw)) {
				continue;
			}
			// list of records
			if (isset($raw[0]) || empty($raw)) {
				foreach ($raw as $i => $rec) {
					if (!is_array($rec)) {
						continue;
					}
					$fields = array();
					foreach ($rec as $k => $v) {
						if (is_array($v)) {
							$v = wp_json_encode($v, JSON_UNESCAPED_UNICODE);
						}
						$fields[] = array(
							'key'   => (string) $k,
							'label' => self::human_label((string) $k),
							'value' => (string) $v,
						);
					}
					$out[] = array(
						'id'         => $mk . '-' . $i,
						'source'     => 'user_meta',
						'title'      => isset($rec['title']) ? (string) $rec['title'] : ('نسخه ' . ((int) $i + 1)),
						'fields'     => $fields,
						'created_at' => isset($rec['created_at']) ? (string) $rec['created_at'] : '',
					);
				}
			} else {
				// single associative record
				$fields = array();
				foreach ($raw as $k => $v) {
					if (is_array($v)) {
						$v = wp_json_encode($v, JSON_UNESCAPED_UNICODE);
					}
					$fields[] = array(
						'key'   => (string) $k,
						'label' => self::human_label((string) $k),
						'value' => (string) $v,
					);
				}
				$out[] = array(
					'id'         => $mk,
					'source'     => 'user_meta',
					'title'      => 'پرونده بیمار',
					'fields'     => $fields,
					'created_at' => '',
				);
			}
		}

		// Scan individual user meta with rx-like keys
		$all = get_user_meta($user_id);
		if (is_array($all)) {
			$extra = array();
			foreach ($all as $key => $values) {
				$lk = strtolower((string) $key);
				if (strpos($lk, 'rx') === false && strpos($lk, 'prescription') === false && strpos($lk, 'ezlens_od') === false && strpos($lk, 'ezlens_os') === false) {
					continue;
				}
				if (in_array($key, $meta_keys, true)) {
					continue;
				}
				$val = is_array($values) ? (isset($values[0]) ? $values[0] : '') : $values;
				if (is_array($val)) {
					$val = wp_json_encode($val, JSON_UNESCAPED_UNICODE);
				}
				$extra[] = array(
					'key'   => (string) $key,
					'label' => self::human_label((string) $key),
					'value' => (string) $val,
				);
			}
			if (!empty($extra)) {
				$out[] = array(
					'id'         => 'user-rx-flat',
					'source'     => 'user_meta',
					'title'      => 'فیلدهای نسخه کاربر',
					'fields'     => $extra,
					'created_at' => '',
				);
			}
		}

		return $out;
	}

	public static function customer_prescriptions(WP_REST_Request $request) {
		$user_id = (int) $request['id'];
		return rest_ensure_response(array(
			'customer_id'    => $user_id,
			'prescriptions'  => self::load_prescriptions_for_user($user_id),
		));
	}

	/**
	 * Notify customer via email and/or SMS about new order status.
	 */
	public static function notify_customer(WP_REST_Request $request) {
		$order_id = (int) $request['id'];
		if (!function_exists('wc_get_order')) {
			return new WP_Error('no_wc', 'WooCommerce در دسترس نیست', array('status' => 500));
		}
		$order = wc_get_order($order_id);
		if (!$order) {
			return new WP_Error('not_found', 'سفارش یافت نشد', array('status' => 404));
		}

		$channels = $request->get_param('channels');
		if (!is_array($channels) || empty($channels)) {
			$channels = array('email');
		}
		$channels = array_map('sanitize_key', $channels);
		$custom_message = sanitize_textarea_field((string) $request->get_param('message'));
		$status         = $order->get_status();
		$status_label   = wc_get_order_status_name($status);
		$order_number   = $order->get_order_number();
		$name           = trim($order->get_formatted_billing_full_name());
		if ($name === '') {
			$name = 'مشتری گرامی';
		}

		$default_message = sprintf(
			'%s عزیز، وضعیت سفارش #%s به «%s» تغییر کرد.',
			$name,
			$order_number,
			$status_label
		);
		$body = $custom_message !== '' ? $custom_message : $default_message;

		$results = array(
			'email' => null,
			'sms'   => null,
		);

		if (in_array('email', $channels, true)) {
			$email = $order->get_billing_email();
			if ($email && is_email($email)) {
				$subject = sprintf('به‌روزرسانی سفارش #%s — %s', $order_number, $status_label);
				$headers = array('Content-Type: text/plain; charset=UTF-8');
				$sent    = wp_mail($email, $subject, $body, $headers);
				// Also add as customer note (WC may email again depending on settings)
				$order->add_order_note($body, true, false);
				$results['email'] = array(
					'success' => (bool) $sent,
					'to'      => $email,
				);
			} else {
				$results['email'] = array(
					'success' => false,
					'reason'  => 'ایمیل صورتحساب معتبر نیست',
				);
			}
		}

		if (in_array('sms', $channels, true)) {
			$phone = $order->get_billing_phone();
			$sms_ok = false;
			$sms_reason = 'سرویس پیامک در دسترس نیست';

			if ($phone) {
				// Prefer EzLens messaging module if present
				if (class_exists('EzLens_Messaging') && method_exists('EzLens_Messaging', 'send_sms')) {
					$r = EzLens_Messaging::send_sms($phone, $body);
					$sms_ok = !is_wp_error($r) && $r;
					if (is_wp_error($r)) {
						$sms_reason = $r->get_error_message();
					}
				} elseif (function_exists('ezlens_send_sms')) {
					$r = ezlens_send_sms($phone, $body);
					$sms_ok = !is_wp_error($r) && $r;
					if (is_wp_error($r)) {
						$sms_reason = $r->get_error_message();
					}
				} else {
					/**
					 * Fallback hook — site can implement.
					 * @param string $phone
					 * @param string $body
					 * @param WC_Order $order
					 */
					$sms_ok = (bool) apply_filters('ezlens_manager_send_sms', false, $phone, $body, $order);
					if (!$sms_ok) {
						$sms_reason = 'هیچ ارائه‌دهنده پیامکی متصل نیست (از فیلتر ezlens_manager_send_sms استفاده کنید)';
					}
				}
			} else {
				$sms_reason = 'شماره موبایل صورتحساب خالی است';
			}

			$results['sms'] = array(
				'success' => $sms_ok,
				'to'      => $phone,
				'reason'  => $sms_ok ? null : $sms_reason,
			);
		}

		$any = (!empty($results['email']['success'])) || (!empty($results['sms']['success']));
		return rest_ensure_response(array(
			'success' => $any,
			'message' => $any ? 'اطلاع‌رسانی ارسال شد' : 'ارسال ناموفق بود',
			'body'    => $body,
			'results' => $results,
		));
	}

	private static function human_label($key) {
		$map = array(
			'od_sph' => 'SPH راست',
			'os_sph' => 'SPH چپ',
			'od_cyl' => 'CYL راست',
			'os_cyl' => 'CYL چپ',
			'od_axis' => 'AXIS راست',
			'os_axis' => 'AXIS چپ',
			'od_add' => 'ADD راست',
			'os_add' => 'ADD چپ',
			'pd' => 'PD',
			'pd_od' => 'PD راست',
			'pd_os' => 'PD چپ',
			'pd_type' => 'نوع PD',
			'bc' => 'BC',
			'dia' => 'DIA',
			'doctor_name' => 'نام پزشک',
			'rx_date' => 'تاریخ نسخه',
			'use_type' => 'کاربرد عینک',
			'lens_index' => 'ضریب شکست',
			'prescription_note' => 'یادداشت نسخه',
		);
		$k = strtolower($key);
		if (isset($map[$k])) {
			return $map[$k];
		}
		return ucwords(str_replace(array('_', '-'), ' ', $key));
	}
}

EzLens_Manager_Orders_REST::init();
