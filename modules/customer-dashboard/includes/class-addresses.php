<?php
/**
 * Customer address book (multi) + WC billing/shipping sync
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Addresses {

	const META = 'ezcd_address_book';

	/**
	 * @return array[]
	 */
	public static function book( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$list    = get_user_meta( $user_id, self::META, true );
		if ( ! is_array( $list ) ) {
			$list = array();
		}
		// Seed from WC if empty
		if ( empty( $list ) ) {
			$fmt = self::wc_pair( $user_id );
			foreach ( array( 'billing', 'shipping' ) as $type ) {
				$a = $fmt[ $type ];
				if ( ! empty( $a['address_1'] ) || ! empty( $a['city'] ) ) {
					$a['id']    = $type;
					$a['label'] = 'billing' === $type ? 'صورتحساب' : 'ارسال';
					$list[]     = $a;
				}
			}
		}
		return array_values( $list );
	}

	public static function wc_pair( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id || ! function_exists( 'WC' ) ) {
			return array( 'billing' => array(), 'shipping' => array() );
		}
		$c = new WC_Customer( $user_id );
		$out = array();
		foreach ( array( 'billing', 'shipping' ) as $type ) {
			$out[ $type ] = array(
				'first_name' => $c->{"get_{$type}_first_name"}(),
				'last_name'  => $c->{"get_{$type}_last_name"}(),
				'address_1'  => $c->{"get_{$type}_address_1"}(),
				'address_2'  => $c->{"get_{$type}_address_2"}(),
				'city'       => $c->{"get_{$type}_city"}(),
				'state'      => $c->{"get_{$type}_state"}(),
				'postcode'   => $c->{"get_{$type}_postcode"}(),
				'country'    => $c->{"get_{$type}_country"}() ?: 'IR',
				'phone'      => 'billing' === $type ? $c->get_billing_phone() : get_user_meta( $user_id, 'shipping_phone', true ),
				'lat'        => get_user_meta( $user_id, "_{$type}_lat", true ),
				'lng'        => get_user_meta( $user_id, "_{$type}_lng", true ),
				'plaque'     => get_user_meta( $user_id, "_{$type}_plaque", true ),
				'note'       => get_user_meta( $user_id, "_{$type}_note", true ),
			);
		}
		return $out;
	}

	/**
	 * Backward-compatible formatted for old templates.
	 */
	public static function get_formatted( $user_id = 0 ) {
		return self::wc_pair( $user_id );
	}

	public static function line( $addr ) {
		$parts = array_filter(
			array(
				trim( ( $addr['first_name'] ?? '' ) . ' ' . ( $addr['last_name'] ?? '' ) ),
				$addr['address_1'] ?? '',
				! empty( $addr['plaque'] ) ? ( 'پلاک ' . $addr['plaque'] ) : '',
				$addr['address_2'] ?? '',
				$addr['city'] ?? '',
				$addr['state'] ?? '',
			)
		);
		return implode( '، ', $parts );
	}

	/**
	 * Save one address into book + optionally sync to WC billing/shipping.
	 *
	 * @return true|WP_Error
	 */
	public static function save_book_item( $data, $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error( 'auth', 'وارد شوید' );
		}
		$id = isset( $data['id'] ) ? sanitize_key( $data['id'] ) : '';
		if ( ! $id ) {
			$id = 'a' . wp_generate_password( 8, false, false );
		}
		$item = array(
			'id'         => $id,
			'label'      => isset( $data['label'] ) ? sanitize_text_field( wp_unslash( $data['label'] ) ) : 'آدرس',
			'first_name' => isset( $data['first_name'] ) ? sanitize_text_field( wp_unslash( $data['first_name'] ) ) : '',
			'last_name'  => isset( $data['last_name'] ) ? sanitize_text_field( wp_unslash( $data['last_name'] ) ) : '',
			'address_1'  => isset( $data['address_1'] ) ? sanitize_text_field( wp_unslash( $data['address_1'] ) ) : '',
			'address_2'  => isset( $data['address_2'] ) ? sanitize_text_field( wp_unslash( $data['address_2'] ) ) : '',
			'plaque'     => isset( $data['plaque'] ) ? sanitize_text_field( wp_unslash( $data['plaque'] ) ) : '',
			'note'       => isset( $data['note'] ) ? sanitize_textarea_field( wp_unslash( $data['note'] ) ) : '',
			'city'       => isset( $data['city'] ) ? sanitize_text_field( wp_unslash( $data['city'] ) ) : '',
			'state'      => isset( $data['state'] ) ? sanitize_text_field( wp_unslash( $data['state'] ) ) : '',
			'postcode'   => isset( $data['postcode'] ) ? sanitize_text_field( wp_unslash( $data['postcode'] ) ) : '',
			'country'    => isset( $data['country'] ) ? sanitize_text_field( wp_unslash( $data['country'] ) ) : 'IR',
			'phone'      => isset( $data['phone'] ) ? sanitize_text_field( wp_unslash( $data['phone'] ) ) : '',
			'lat'        => isset( $data['lat'] ) ? sanitize_text_field( $data['lat'] ) : '',
			'lng'        => isset( $data['lng'] ) ? sanitize_text_field( $data['lng'] ) : '',
		);
		if ( $item['address_1'] === '' && $item['city'] === '' ) {
			return new WP_Error( 'empty', 'آدرس یا شهر را وارد کنید' );
		}
		$list = self::book( $user_id );
		$found = false;
		foreach ( $list as $i => $row ) {
			if ( ( $row['id'] ?? '' ) === $id ) {
				$list[ $i ] = $item;
				$found      = true;
				break;
			}
		}
		if ( ! $found ) {
			$list[] = $item;
		}
		update_user_meta( $user_id, self::META, $list );

		// Sync first address / explicit sync flag to WC for checkout
		$sync = isset( $data['sync_wc'] ) ? (int) $data['sync_wc'] : 1;
		if ( $sync ) {
			self::apply_to_wc( $item, $user_id, isset( $data['wc_type'] ) ? $data['wc_type'] : 'both' );
		}
		return true;
	}

	public static function delete_book_item( $id, $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$list    = self::book( $user_id );
		$list    = array_values(
			array_filter(
				$list,
				function ( $row ) use ( $id ) {
					return ( $row['id'] ?? '' ) !== $id;
				}
			)
		);
		update_user_meta( $user_id, self::META, $list );
		return true;
	}

	public static function apply_to_wc( $item, $user_id, $type = 'both' ) {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}
		$c = new WC_Customer( $user_id );
		$types = ( 'shipping' === $type ) ? array( 'shipping' ) : ( ( 'billing' === $type ) ? array( 'billing' ) : array( 'billing', 'shipping' ) );
		foreach ( $types as $t ) {
			foreach ( array( 'first_name', 'last_name', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country' ) as $f ) {
				$method = "set_{$t}_{$f}";
				if ( method_exists( $c, $method ) ) {
					$c->$method( $item[ $f ] ?? '' );
				}
			}
			if ( 'billing' === $t && ! empty( $item['phone'] ) ) {
				$c->set_billing_phone( $item['phone'] );
			}
			update_user_meta( $user_id, "_{$t}_lat", $item['lat'] ?? '' );
			update_user_meta( $user_id, "_{$t}_lng", $item['lng'] ?? '' );
			update_user_meta( $user_id, "_{$t}_plaque", $item['plaque'] ?? '' );
			update_user_meta( $user_id, "_{$t}_note", $item['note'] ?? '' );
		}
		$c->save();
	}

	/**
	 * Legacy save used by ajax.
	 */
	public static function save( $type, $data, $user_id = 0 ) {
		$data['wc_type'] = $type;
		$data['id']      = isset( $data['id'] ) ? $data['id'] : $type;
		$data['label']   = isset( $data['label'] ) ? $data['label'] : ( 'billing' === $type ? 'صورتحساب' : 'ارسال' );
		$data['sync_wc'] = 1;
		return self::save_book_item( $data, $user_id );
	}

	/**
	 * Checkout: inject saved addresses selector.
	 */
	public static function bootstrap_checkout() {
		add_action( 'woocommerce_before_checkout_billing_form', array( __CLASS__, 'checkout_picker' ), 5 );
		add_action( 'woocommerce_before_checkout_form', array( __CLASS__, 'checkout_picker' ), 20 );
		add_action( 'wp_ajax_ezcd_apply_checkout_address', array( __CLASS__, 'ajax_apply_checkout' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( __CLASS__, 'save_order_address_to_book' ), 20, 2 );
		add_action( 'woocommerce_checkout_order_processed', array( __CLASS__, 'save_order_address_to_book_simple' ), 20, 1 );
	}

	/**
	 * بعد از ثبت سفارش، آدرس را در دفترچه ذخیره کن (اگر تکراری نبود)
	 */
	public static function save_order_address_to_book_simple( $order_id ) {
		self::save_order_address_to_book( $order_id, array() );
	}

	public static function save_order_address_to_book( $order_id, $data = array() ) {
		if ( ! is_user_logged_in() ) {
			return;
		}
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		$user_id = get_current_user_id();
		$addr    = array(
			'id'         => 'ord_' . $order_id . '_' . time(),
			'label'      => 'آدرس سفارش #' . $order_id,
			'first_name' => $order->get_billing_first_name(),
			'last_name'  => $order->get_billing_last_name(),
			'address_1'  => $order->get_billing_address_1(),
			'address_2'  => $order->get_billing_address_2(),
			'city'       => $order->get_billing_city(),
			'state'      => $order->get_billing_state(),
			'postcode'   => $order->get_billing_postcode(),
			'country'    => 'IR',
			'phone'      => $order->get_billing_phone(),
			'plaque'     => $order->get_meta( '_billing_plaque' ) ?: ( isset( $_POST['billing_plaque'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_plaque'] ) ) : '' ),
			'unit'       => $order->get_meta( '_billing_unit' ) ?: ( isset( $_POST['billing_unit'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_unit'] ) ) : '' ),
			'note'       => '',
		);
		if ( empty( $addr['address_1'] ) && empty( $addr['city'] ) ) {
			return;
		}
		$list = self::book( $user_id );
		// جلوگیری از تکرار تقریبی
		$key = md5( strtolower( trim( $addr['address_1'] . '|' . $addr['city'] . '|' . $addr['plaque'] . '|' . $addr['unit'] ) ) );
		foreach ( $list as $row ) {
			$k2 = md5( strtolower( trim( ( $row['address_1'] ?? '' ) . '|' . ( $row['city'] ?? '' ) . '|' . ( $row['plaque'] ?? '' ) . '|' . ( $row['unit'] ?? '' ) ) ) );
			if ( $k2 === $key ) {
				return;
			}
		}
		$list[] = $addr;
		update_user_meta( $user_id, self::META, $list );
	}

	public static function checkout_picker() {
		static $done = false;
		if ( $done || ! is_user_logged_in() ) {
			return;
		}
		$done = true;
		$list = self::book();
		// اگر دفترچه خالی ولی کاربر آدرس WC دارد
		if ( empty( $list ) ) {
			$pair = self::wc_pair();
			foreach ( array( 'billing', 'shipping' ) as $type ) {
				$a = $pair[ $type ];
				if ( ! empty( $a['address_1'] ) || ! empty( $a['city'] ) || ! empty( $a['plaque'] ) ) {
					$a['id'] = $type;
					$a['label'] = 'billing' === $type ? 'صورتحساب' : 'ارسال';
					if ( empty( $a['unit'] ) && ! empty( $a['address_2'] ) ) {
						$a['unit'] = $a['address_2'];
					}
					$list[] = $a;
				}
			}
		}
		echo '<div class="ezcd-co-addr" id="ezcd-co-addr">';
		echo '<div class="ezcd-co-addr-title">آدرس‌های من</div>';
		if ( empty( $list ) ) {
			echo '<p class="ezcd-co-addr-empty">برای شما آدرسی ثبت نشده است. پس از تکمیل خرید، این آدرس به‌صورت خودکار ذخیره می‌شود.</p>';
		} else {
			echo '<p class="ezcd-co-addr-hint">یکی از آدرس‌ها را انتخاب کنید تا فیلدها پر شوند.</p>';
			echo '<div class="ezcd-co-addr-list">';
			foreach ( $list as $a ) {
				$id    = esc_attr( $a['id'] ?? '' );
				$label = esc_html( $a['label'] ?? 'آدرس' );
				$line  = esc_html( self::line( $a ) );
				echo '<label class="ezcd-co-addr-item">';
				echo '<input type="radio" name="ezcd_checkout_address" value="' . $id . '" class="ezcd-checkout-addr-radio" />';
				echo '<span class="ezcd-co-addr-body"><strong>' . $label . '</strong><small>' . $line . '</small></span>';
				echo '</label>';
			}
			echo '</div>';
		}
		echo '</div>';
		?>
		<style>
		.ezcd-co-addr{margin:0 0 18px;padding:16px;border:1px solid #e2e8f0;border-radius:14px;background:linear-gradient(145deg,#f8fafc,#eef2ff)}
		.ezcd-co-addr-title{font-weight:800;font-size:14px;color:#031f8a;margin:0 0 8px}
		.ezcd-co-addr-empty,.ezcd-co-addr-hint{margin:0 0 10px;font-size:12.5px;color:#64748b;line-height:1.6}
		.ezcd-co-addr-list{display:flex;flex-direction:column;gap:8px}
		.ezcd-co-addr-item{display:flex;gap:10px;align-items:flex-start;cursor:pointer;padding:10px 12px;border-radius:10px;background:#fff;border:1.5px solid #e2e8f0;transition:border-color .15s,box-shadow .15s}
		.ezcd-co-addr-item:has(input:checked){border-color:#031f8a;box-shadow:0 0 0 3px rgba(3,31,138,.1)}
		.ezcd-co-addr-body{display:flex;flex-direction:column;gap:2px}
		.ezcd-co-addr-body strong{font-size:13px;color:#0f172a}
		.ezcd-co-addr-body small{font-size:12px;color:#64748b;line-height:1.5}
		</style>
		<script>
		(function(){
			function fill(d){
				var map = {
					billing_first_name:d.first_name, billing_last_name:d.last_name,
					billing_address_1:d.address_1, billing_address_2:d.address_2,
					billing_city:d.city, billing_state:d.state, billing_postcode:d.postcode,
					billing_phone:d.phone, billing_plaque:d.plaque||'', billing_unit:d.unit||d.address_2||'',
					billing_country:'IR'
				};
				Object.keys(map).forEach(function(k){
					var el = document.getElementById(k) || document.querySelector('[name="'+k+'"]');
					if(el){ el.value = map[k]==null?'':map[k]; el.dispatchEvent(new Event('change',{bubbles:true})); el.dispatchEvent(new Event('input',{bubbles:true})); }
				});
				if(typeof jQuery!=='undefined'){ jQuery(document.body).trigger('update_checkout'); }
			}
			document.querySelectorAll('.ezcd-checkout-addr-radio').forEach(function(r){
				r.addEventListener('change', function(){
					if(!r.checked) return;
					var body = new FormData();
					body.append('action','ezcd_apply_checkout_address');
					body.append('address_id', r.value);
					body.append('nonce', '<?php echo esc_js( wp_create_nonce( 'ezcd_checkout_addr' ) ); ?>');
					fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',{method:'POST',credentials:'same-origin',body:body})
						.then(function(x){return x.json();})
						.then(function(res){ if(res&&res.success&&res.data) fill(res.data); });
				});
			});
		})();
		</script>
		<?php
	}

	public static function ajax_apply_checkout() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error();
		}
		check_ajax_referer( 'ezcd_checkout_addr', 'nonce' );
		$id   = isset( $_POST['address_id'] ) ? sanitize_key( $_POST['address_id'] ) : '';
		$list = self::book();
		$item = null;
		foreach ( $list as $row ) {
			if ( ( $row['id'] ?? '' ) === $id ) {
				$item = $row;
				break;
			}
		}
		if ( ! $item ) {
			wp_send_json_error();
		}
		self::apply_to_wc( $item, get_current_user_id(), 'both' );
		if ( empty( $item['unit'] ) && ! empty( $item['address_2'] ) ) {
			$item['unit'] = $item['address_2'];
		}
		wp_send_json_success( $item );
	}
}
