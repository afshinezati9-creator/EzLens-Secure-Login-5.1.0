<?php
/**
 * عنوان: صفحه محصول حرفه‌ای
 * نام فایل: custom-product-page
 * نسخه: 17.1
 * شورت‌کد: [custom_product_page]
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   1. Icon loader
   ============================================================ */
if ( ! function_exists( 'ezp_pdp_icon' ) ) {
	function ezp_pdp_icon( $name, $fallback = '' ) {
		$path = EZLAUTH_PLUGIN_DIR . 'assets/icons/modern/' . $name . '.svg';
		if ( file_exists( $path ) ) {
			$content = file_get_contents( $path );
			if ( false !== $content && trim( $content ) !== '' ) return $content;
		}
		return $fallback;
	}
}

if ( ! function_exists( 'ezp_pdp_fa' ) ) {
	function ezp_pdp_fa( $text ) {
		if ( is_array( $text ) ) $text = implode( '', $text );
		return str_replace(
			array( '0','1','2','3','4','5','6','7','8','9' ),
			array( '۰','۱','۲','۳','۴','۵','۶','۷','۸','۹' ),
			(string) $text
		);
	}
}

/* ============================================================
   Product option helpers
   ============================================================ */
if ( ! function_exists( 'ezp_pdp_is_upload_field' ) ) {
	function ezp_pdp_is_upload_field( $type ) {
		$type = strtolower( trim( (string) $type ) );
		return in_array( $type, array(
			'file', 'upload', 'file_upload', 'image', 'image_upload',
			'attachment', 'media', 'photo', 'document',
		), true );
	}
}

if ( ! function_exists( 'ezp_pdp_option_value_label' ) ) {
	function ezp_pdp_option_value_label( $value ) {
		if ( is_array( $value ) ) {
			$value = $value['label'] ?? ( $value['value'] ?? '' );
		}
		return is_scalar( $value ) ? (string) $value : '';
	}
}

if ( ! function_exists( 'ezp_pdp_get_uploaded_option' ) ) {
	function ezp_pdp_get_uploaded_option( $template_id, $key ) {
		if ( empty( $_FILES['ezlens_options'] ) || ! is_array( $_FILES['ezlens_options'] ) ) {
			return null;
		}

		$files = $_FILES['ezlens_options'];

		foreach ( array( 'name', 'type', 'tmp_name', 'error', 'size' ) as $part ) {
			if ( ! isset( $files[ $part ][ $template_id ][ $key ] ) ) return null;
		}

		$file = array(
			'name'     => sanitize_file_name( wp_unslash( $files['name'][ $template_id ][ sanitize_key( $key ) ] ) ),
			'type'     => sanitize_text_field( wp_unslash( $files['type'][ $template_id ][ sanitize_key( $key ) ] ) ),
			'tmp_name' => $files['tmp_name'][ $template_id ][ sanitize_key( $key ) ],
			'error'    => (int) $files['error'][ $template_id ][ sanitize_key( $key ) ],
			'size'     => (int) $files['size'][ $template_id ][ sanitize_key( $key ) ],
		);

		if ( empty( $file['name'] ) || empty( $file['tmp_name'] ) ) return null;
		return $file;
	}
}

if ( ! function_exists( 'ezp_pdp_process_option_files' ) ) {
	function ezp_pdp_process_option_files( $template_id, $fields ) {
		$result = array();

		if ( empty( $fields ) || ! is_array( $fields ) ) return $result;

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		foreach ( $fields as $key => $field ) {
			$type = $field['type'] ?? 'text';
			if ( ! ezp_pdp_is_upload_field( $type ) ) continue;

			$file = ezp_pdp_get_uploaded_option( $template_id, $key );
			if ( ! $file ) {
				if ( ! empty( $field['required'] ) ) {
					return new WP_Error( 'ezp_required_upload', sprintf( 'لطفاً فایل «%s» را انتخاب کنید.', $field['label'] ?? 'آپلود' ) );
				}
				continue;
			}

			if ( UPLOAD_ERR_OK !== $file['error'] ) {
				return new WP_Error( 'ezp_upload_error', 'آپلود فایل انجام نشد. لطفاً دوباره تلاش کنید.' );
			}

			$max_size = 10 * MB_IN_BYTES;
			if ( $file['size'] > $max_size ) {
				return new WP_Error( 'ezp_upload_size', 'حجم فایل بیشتر از ۱۰ مگابایت است.' );
			}

			$allowed_mimes = array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'png'          => 'image/png',
				'webp'         => 'image/webp',
				'pdf'          => 'application/pdf',
				'doc'          => 'application/msword',
				'docx'         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			);

			$overrides = array(
				'test_form' => false,
				'mimes'     => $allowed_mimes,
			);

			$upload = wp_handle_sideload( $file, $overrides );

			if ( isset( $upload['error'] ) ) {
				return new WP_Error( 'ezp_upload_failed', wp_strip_all_tags( $upload['error'] ) );
			}

			$result[ sanitize_key( $key ) ] = array(
				'name' => $file['name'],
				'url'  => esc_url_raw( $upload['url'] ),
				'file' => sanitize_text_field( $upload['file'] ),
			);
		}

		return $result;
	}
}

/* ============================================================
   slots loader + product-options template (fields + custom code)
   ============================================================ */
if ( ! function_exists( 'ezp_pdp_load_template_storage' ) ) {
	function ezp_pdp_load_template_storage() {
		if ( class_exists( 'EzLens_PO_Template_File_Storage' ) ) {
			return;
		}
		$candidates = array();
		if ( defined( 'EZLAUTH_MODULES_DIR' ) ) {
			$candidates[] = trailingslashit( EZLAUTH_MODULES_DIR ) . 'product-options/includes/class-template-file-storage.php';
		}
		if ( defined( 'EZLAUTH_PLUGIN_DIR' ) ) {
			$candidates[] = trailingslashit( EZLAUTH_PLUGIN_DIR ) . 'modules/product-options/includes/class-template-file-storage.php';
		}
		foreach ( $candidates as $path ) {
			if ( $path && is_readable( $path ) ) {
				require_once $path;
				break;
			}
		}
	}
}

if ( ! function_exists( 'ezp_pdp_get_template_code' ) ) {
	/**
	 * Full code-editor payload (HTML + CSS + JS separators) for a palette.
	 *
	 * @param int $template_id
	 * @return string
	 */
	function ezp_pdp_get_template_code( $template_id ) {
		$template_id = absint( $template_id );
		if ( ! $template_id ) {
			return '';
		}

		ezp_pdp_load_template_storage();

		// 1) File storage (preferred — exact editor contents)
		if ( class_exists( 'EzLens_PO_Template_File_Storage' ) ) {
			$code = EzLens_PO_Template_File_Storage::read_code( $template_id );
			if ( is_string( $code ) && trim( $code ) !== '' ) {
				return $code;
			}
		}

		// 2) Fields meta _code_*
		if ( ! class_exists( 'EzLens_Product_Options_Template_Manager' ) ) {
			return '';
		}
		$manager = EzLens_Product_Options_Template_Manager::get_instance();
		if ( ! $manager || ! method_exists( $manager, 'get' ) ) {
			return '';
		}
		$template = $manager->get( $template_id );
		if ( ! is_array( $template ) || empty( $template['fields'] ) || ! is_array( $template['fields'] ) ) {
			return '';
		}
		$fields = $template['fields'];
		$html = isset( $fields['_code_html'] ) ? (string) $fields['_code_html'] : '';
		$css  = isset( $fields['_code_css'] ) ? (string) $fields['_code_css'] : '';
		$js   = isset( $fields['_code_js'] ) ? (string) $fields['_code_js'] : '';
		if ( $html === '' && $css === '' && $js === '' ) {
			return '';
		}
		$combined = $html;
		if ( $css !== '' || $js !== '' ) {
			$combined .= "\n\n/**CSS**/\n" . $css . "\n\n/**JS**/\n" . $js;
		}
		return $combined;
	}
}

if ( ! function_exists( 'ezp_pdp_code_is_custom' ) ) {
	function ezp_pdp_code_is_custom( $code ) {
		$code = (string) $code;
		if ( trim( $code ) === '' ) {
			return false;
		}
		// Skip auto-generated skeleton from builder
		if ( false !== strpos( $code, 'Generated from visual builder' ) && false === strpos( $code, 'ez-po-' ) && false === strpos( $code, 'ez-rx' ) ) {
			return false;
		}
		// Professional packs (ez-rx / ez-po-wrap forms)
		if ( false !== strpos( $code, 'ez-rx' ) || false !== strpos( $code, 'ez-po-wrap' ) || false !== strpos( $code, 'id="ez-po-wrap"' ) ) {
			return true;
		}
		// Separated CSS/JS blocks from code editor
		if ( preg_match( '/\/\*\*CSS\*\*\/\s*\S+/', $code ) ) {
			return true;
		}
		// Embedded style + form markup (paste-style packs)
		if ( false !== strpos( $code, '<style' ) && ( false !== strpos( $code, '<input' ) || false !== strpos( $code, '<select' ) ) ) {
			return true;
		}
		return false;
	}
}

if ( ! function_exists( 'ezp_pdp_split_code' ) ) {
	function ezp_pdp_split_code( $code ) {
		$code = (string) $code;
		$html = $code;
		$css  = '';
		$js   = '';
		if ( false !== strpos( $code, '/**CSS**/' ) || false !== strpos( $code, '/**JS**/' ) ) {
			$parts = preg_split( '/\/\*\*CSS\*\*\/|\/\*\*JS\*\*\//', $code );
			$html  = isset( $parts[0] ) ? trim( $parts[0] ) : '';
			$css   = isset( $parts[1] ) ? trim( $parts[1] ) : '';
			$js    = isset( $parts[2] ) ? trim( $parts[2] ) : '';
		}
		return array( $html, $css, $js );
	}
}

if ( ! function_exists( 'ezp_pdp_rewrite_option_names' ) ) {
	/**
	 * Ensure inputs inside custom HTML post under ezlens_options[tid][name].
	 *
	 * @param string $html
	 * @param int    $template_id
	 * @return string
	 */
	function ezp_pdp_rewrite_option_names( $html, $template_id ) {
		$template_id = absint( $template_id );
		if ( ! $template_id || $html === '' ) {
			return $html;
		}

		// name="foo" or name='foo' → name="ezlens_options[tid][foo]" (skip already rewritten / arrays)
		$html = preg_replace_callback(
			'/\bname=(["\'])([^"\']+)\1/i',
			function ( $m ) use ( $template_id ) {
				$q    = $m[1];
				$name = $m[2];
				if ( 0 === strpos( $name, 'ezlens_options[' ) ) {
					return $m[0];
				}
				// Keep bare array suffixes: color[] → ezlens_options[tid][color][]
				$suffix = '';
				if ( substr( $name, -2 ) === '[]' ) {
					$suffix = '[]';
					$name   = substr( $name, 0, -2 );
				}
				$name = preg_replace( '/[^a-zA-Z0-9_\-]/', '', $name );
				if ( $name === '' ) {
					return $m[0];
				}
				return 'name=' . $q . 'ezlens_options[' . $template_id . '][' . $name . ']' . $suffix . $q;
			},
			$html
		);

		// Add data-live-key from name when missing (for live panel)
		$html = preg_replace_callback(
			'/<((?:input|select|textarea)\b[^>]*?)\s*\/?>/i',
			function ( $m ) {
				$tag = $m[0];
				if ( false !== stripos( $tag, 'data-live-key' ) ) {
					return $tag;
				}
				if ( ! preg_match( '/\bname=["\']ezlens_options\[\d+\]\[([^\]]+)\]/', $tag, $nm ) ) {
					return $tag;
				}
				$key = $nm[1];
				if ( substr( $tag, -2 ) === '/>' ) {
					return substr( $tag, 0, -2 ) . ' data-live-key="' . esc_attr( $key ) . '" />';
				}
				if ( substr( $tag, -1 ) === '>' ) {
					return substr( $tag, 0, -1 ) . ' data-live-key="' . esc_attr( $key ) . '">';
				}
				return $tag;
			},
			$html
		);

		return $html;
	}
}

if ( ! function_exists( 'ezp_pdp_load_slots' ) ) {
	/**
	 * Load product option slots (placement / display / label / template).
	 * Primary meta: _ezlens_option_slots (ProductSlotsService)
	 * Fallbacks: ezlens_slots, _ezlens_option_template_id
	 *
	 * @param int $product_id
	 * @return array[]
	 */
	function ezp_pdp_load_slots( $product_id ) {
		$product_id = absint( $product_id );
		$slots      = array();

		// 1) Official Product Options service (preferred)
		if ( class_exists( 'EzLens_Product_Options_Template_Manager' ) ) {
			$manager = EzLens_Product_Options_Template_Manager::get_instance();
			if ( $manager && method_exists( $manager, 'get_slots_service' ) ) {
				$svc = $manager->get_slots_service();
				if ( $svc && method_exists( $svc, 'get_slots' ) ) {
					$raw = $svc->get_slots( $product_id );
					if ( is_array( $raw ) && ! empty( $raw ) ) {
						foreach ( $raw as $slot ) {
							$built = ezp_pdp_hydrate_slot( $slot );
							if ( $built ) {
								$slots[] = $built;
							}
						}
						if ( ! empty( $slots ) ) {
							return $slots;
						}
					}
				}
			}
		}

		// 2) Direct meta keys used by the module
		$meta_candidates = array( '_ezlens_option_slots', 'ezlens_slots', '_ezlens_slots' );
		foreach ( $meta_candidates as $meta_key ) {
			$raw = get_post_meta( $product_id, $meta_key, true );
			if ( is_string( $raw ) && $raw !== '' ) {
				$decoded = json_decode( $raw, true );
				if ( is_array( $decoded ) ) {
					$raw = $decoded;
				} else {
					$maybe = maybe_unserialize( $raw );
					if ( is_array( $maybe ) ) {
						$raw = $maybe;
					}
				}
			}
			if ( ! is_array( $raw ) || empty( $raw ) ) {
				continue;
			}
			foreach ( $raw as $slot ) {
				if ( ! is_array( $slot ) ) {
					continue;
				}
				$built = ezp_pdp_hydrate_slot( $slot );
				if ( $built ) {
					$slots[] = $built;
				}
			}
			if ( ! empty( $slots ) ) {
				return $slots;
			}
		}

		// 3) Legacy single template id
		$legacy_keys = array( '_ezlens_option_template_id', 'ezlens_option_template_id', '_ezlens_option_template' );
		foreach ( $legacy_keys as $lk ) {
			$tid = absint( get_post_meta( $product_id, $lk, true ) );
			if ( ! $tid ) {
				continue;
			}
			$built = ezp_pdp_hydrate_slot(
				array(
					'template_id' => $tid,
					'label'       => '',
					'placement'   => 'below_summary',
					'display'     => 'inline',
				)
			);
			if ( $built ) {
				$slots[] = $built;
				break;
			}
		}

		return $slots;
	}
}

if ( ! function_exists( 'ezp_pdp_hydrate_slot' ) ) {
	/**
	 * Expand a raw slot row into render payload (fields + code + display mode).
	 *
	 * @param array $slot
	 * @return array|null
	 */
	function ezp_pdp_hydrate_slot( $slot ) {
		if ( ! is_array( $slot ) ) {
			return null;
		}
		$tid = absint( $slot['template_id'] ?? ( $slot['id'] ?? 0 ) );
		if ( ! $tid ) {
			return null;
		}

		$placement = (string) ( $slot['placement'] ?? 'below_summary' );
		$allowed_p = array( 'gallery_side', 'below_price', 'below_summary', 'full_width' );
		if ( ! in_array( $placement, $allowed_p, true ) ) {
			$placement = 'below_summary';
		}

		$display = (string) ( $slot['display'] ?? 'inline' );
		$allowed_d = array( 'inline', 'accordion', 'ajax_modal' );
		if ( ! in_array( $display, $allowed_d, true ) ) {
			// tolerate alternate names from older UI
			$map = array(
				'modal'     => 'ajax_modal',
				'box'       => 'ajax_modal',
				'popup'     => 'ajax_modal',
				'ajax'      => 'ajax_modal',
				'collapse'  => 'accordion',
				'collapsed' => 'accordion',
			);
			$display = isset( $map[ $display ] ) ? $map[ $display ] : 'inline';
		}

		$label  = isset( $slot['label'] ) ? (string) $slot['label'] : '';
		$fields = ezp_pdp_get_template_fields( $tid );
		$code   = ezp_pdp_get_template_code( $tid );

		if ( empty( $fields ) && ! ezp_pdp_code_is_custom( $code ) ) {
			return null;
		}

		return array(
			'template_id' => $tid,
			'label'       => $label,
			'placement'   => $placement,
			'display'     => $display,
			'fields'      => $fields,
			'code'        => $code,
		);
	}
}

if ( ! function_exists( 'ezp_pdp_get_template_fields' ) ) {
	function ezp_pdp_get_template_fields( $template_id ) {
		if ( ! $template_id ) {
			return array();
		}
		if ( ! class_exists( 'EzLens_Product_Options_Template_Manager' ) ) {
			return array();
		}

		$manager = EzLens_Product_Options_Template_Manager::get_instance();
		if ( ! $manager || ! method_exists( $manager, 'get' ) ) {
			return array();
		}

		$template = $manager->get( $template_id );
		if ( ! $template || empty( $template['fields'] ) || ! is_array( $template['fields'] ) ) {
			return array();
		}

		$out = array();
		foreach ( $template['fields'] as $key => $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}
			if ( is_string( $key ) && strpos( $key, '_code_' ) === 0 ) {
				continue;
			}
			// List-style fields use name property; map-style use key
			$name = '';
			if ( ! empty( $field['name'] ) ) {
				$name = (string) $field['name'];
			} elseif ( is_string( $key ) && ! is_numeric( $key ) ) {
				$name = (string) $key;
			}
			$name = sanitize_key( $name );
			if ( $name === '' ) {
				continue;
			}
			$type = strtolower( (string) ( $field['type'] ?? 'text' ) );
			if ( in_array( $type, array( 'heading', 'divider', 'spacer', 'html' ), true ) ) {
				// Still expose headings for live panel optional skip
				if ( $type !== 'heading' ) {
					continue;
				}
			}
			$out[] = array(
				'key'         => $name,
				'label'       => (string) ( $field['label'] ?? $name ),
				'type'        => $type,
				'placeholder' => (string) ( $field['placeholder'] ?? '' ),
				'required'    => ! empty( $field['required'] ),
				'options'     => isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array(),
			);
		}
		return $out;
	}
}

if ( ! function_exists( 'ezp_pdp_render_options_fields' ) ) {
	function ezp_pdp_render_options_fields( $template_id, $fields ) {
		foreach ( $fields as $field ) :
			if ( empty( $field['key'] ) ) {
				continue;
			}
			// Skip pure visual types in structured renderer
			if ( in_array( $field['type'], array( 'heading', 'divider', 'spacer', 'html' ), true ) ) {
				if ( $field['type'] === 'heading' ) {
					echo '<div class="ezp-pdp-field ezp-pdp-field--heading"><h4 class="ezp-pdp-field-heading">' . esc_html( $field['label'] ) . '</h4></div>';
				}
				continue;
			}
			$field_id   = 'ezp-pdp-field-' . $template_id . '-' . sanitize_html_class( $field['key'] );
			$field_name = 'ezlens_options[' . $template_id . '][' . sanitize_key( $field['key'] ) . ']';
			$field_type = strtolower( $field['type'] );
		?>
			<div class="ezp-pdp-field">
				<label for="<?php echo esc_attr( $field_id ); ?>">
					<?php echo esc_html( $field['label'] ); ?>
					<?php if ( ! empty( $field['required'] ) ) : ?>
						<span class="ezp-pdp-required">*</span>
					<?php endif; ?>
				</label>
				<?php if ( ezp_pdp_is_upload_field( $field_type ) ) : ?>
					<label class="ezp-pdp-upload-box" for="<?php echo esc_attr( $field_id ); ?>">
						<input type="file"
							id="<?php echo esc_attr( $field_id ); ?>"
							name="<?php echo esc_attr( $field_name ); ?>"
							data-live-key="<?php echo esc_attr( $field['key'] ); ?>"
							accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx"
							<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>>
						<span class="ezp-pdp-upload-box__icon" aria-hidden="true">
							<svg viewBox="0 0 24 24"><path d="M12 16V4"/><path d="m7 9 5-5 5 5"/><path d="M5 20h14"/></svg>
						</span>
						<span class="ezp-pdp-upload-box__content">
							<strong>فایل خود را انتخاب کنید</strong>
							<small class="ezp-pdp-upload-box__name">فرمت‌های مجاز: JPG، PNG، WebP، PDF، DOC و DOCX</small>
						</span>
						<span class="ezp-pdp-upload-box__arrow" aria-hidden="true">
							<svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
						</span>
					</label>
					<small class="ezp-pdp-field-help">حداکثر حجم فایل: ۱۰ مگابایت</small>
				<?php elseif ( in_array( $field_type, array( 'select', 'dropdown' ), true ) ) : ?>
					<select id="<?php echo esc_attr( $field_id ); ?>"
						name="<?php echo esc_attr( $field_name ); ?>"
						data-live-key="<?php echo esc_attr( $field['key'] ); ?>"
						<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>>
						<option value="">انتخاب کنید</option>
						<?php foreach ( $field['options'] as $ok => $opt ) :
							$ov = is_array( $opt ) ? ( $opt['value'] ?? $ok ) : $opt;
							$ol = is_array( $opt ) ? ( $opt['label'] ?? $ov ) : $opt;
						?>
							<option value="<?php echo esc_attr( $ov ); ?>"><?php echo esc_html( $ol ); ?></option>
						<?php endforeach; ?>
					</select>
				<?php elseif ( $field_type === 'textarea' ) : ?>
					<textarea id="<?php echo esc_attr( $field_id ); ?>"
						name="<?php echo esc_attr( $field_name ); ?>"
						placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
						data-live-key="<?php echo esc_attr( $field['key'] ); ?>"
						<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>></textarea>
				<?php elseif ( $field_type === 'number' ) : ?>
					<input type="number"
						id="<?php echo esc_attr( $field_id ); ?>"
						name="<?php echo esc_attr( $field_name ); ?>"
						placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
						data-live-key="<?php echo esc_attr( $field['key'] ); ?>"
						inputmode="decimal"
						step="any"
						autocomplete="off"
						<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>>
				<?php elseif ( $field_type === 'email' ) : ?>
					<input type="email"
						id="<?php echo esc_attr( $field_id ); ?>"
						name="<?php echo esc_attr( $field_name ); ?>"
						placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
						data-live-key="<?php echo esc_attr( $field['key'] ); ?>"
						autocomplete="email"
						<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>>
				<?php elseif ( $field_type === 'phone' ) : ?>
					<input type="tel"
						id="<?php echo esc_attr( $field_id ); ?>"
						name="<?php echo esc_attr( $field_name ); ?>"
						placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
						data-live-key="<?php echo esc_attr( $field['key'] ); ?>"
						inputmode="tel"
						autocomplete="tel"
						<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>>
				<?php elseif ( $field_type === 'color' ) : ?>
					<input type="color"
						id="<?php echo esc_attr( $field_id ); ?>"
						name="<?php echo esc_attr( $field_name ); ?>"
						data-live-key="<?php echo esc_attr( $field['key'] ); ?>"
						value="#000000"
						<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>>
				<?php elseif ( $field_type === 'date' ) : ?>
					<input type="date"
						id="<?php echo esc_attr( $field_id ); ?>"
						name="<?php echo esc_attr( $field_name ); ?>"
						data-live-key="<?php echo esc_attr( $field['key'] ); ?>"
						<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>>
				<?php elseif ( $field_type === 'time' ) : ?>
					<input type="time"
						id="<?php echo esc_attr( $field_id ); ?>"
						name="<?php echo esc_attr( $field_name ); ?>"
						data-live-key="<?php echo esc_attr( $field['key'] ); ?>"
						<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>>
				<?php elseif ( $field_type === 'radio' ) : ?>
					<div class="ezp-pdp-choice-group" role="radiogroup">
						<?php foreach ( $field['options'] as $ok => $opt ) :
							$ov = is_array( $opt ) ? ( $opt['value'] ?? $ok ) : $opt;
							$ol = is_array( $opt ) ? ( $opt['label'] ?? $ov ) : $opt;
						?>
							<label class="ezp-pdp-choice">
								<input type="radio"
									name="<?php echo esc_attr( $field_name ); ?>"
									value="<?php echo esc_attr( $ov ); ?>"
									data-live-key="<?php echo esc_attr( $field['key'] ); ?>"
									<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>>
								<span><?php echo esc_html( $ol ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				<?php elseif ( $field_type === 'checkbox' ) : ?>
					<div class="ezp-pdp-choice-group">
						<?php foreach ( $field['options'] as $ok => $opt ) :
							$ov = is_array( $opt ) ? ( $opt['value'] ?? $ok ) : $opt;
							$ol = is_array( $opt ) ? ( $opt['label'] ?? $ov ) : $opt;
						?>
							<label class="ezp-pdp-choice">
								<input type="checkbox"
									name="<?php echo esc_attr( $field_name ); ?>[]"
									value="<?php echo esc_attr( $ov ); ?>"
									data-live-key="<?php echo esc_attr( $field['key'] ); ?>">
								<span><?php echo esc_html( $ol ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<input type="text"
						id="<?php echo esc_attr( $field_id ); ?>"
						name="<?php echo esc_attr( $field_name ); ?>"
						placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
						data-live-key="<?php echo esc_attr( $field['key'] ); ?>"
						autocomplete="off"
						<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>>
				<?php endif; ?>
			</div>
		<?php
		endforeach;
	}
}

if ( ! function_exists( 'ezp_pdp_render_custom_code' ) ) {
	/**
	 * Render professional code-editor HTML/CSS/JS for a palette slot.
	 *
	 * @param int    $template_id
	 * @param string $code
	 */
	function ezp_pdp_render_custom_code( $template_id, $code ) {
		list( $html, $css, $js ) = ezp_pdp_split_code( $code );
		$html = ezp_pdp_rewrite_option_names( $html, $template_id );
		$uid  = 'ezp-po-code-' . absint( $template_id ) . '-' . wp_rand( 100, 999 );
		?>
		<div class="ezp-pdp-custom-code" id="<?php echo esc_attr( $uid ); ?>" data-template-id="<?php echo esc_attr( $template_id ); ?>">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- intentional admin-authored palette HTML
			echo $html;
			?>
		</div>
		<?php if ( $css !== '' ) : ?>
			<style id="<?php echo esc_attr( $uid ); ?>-css">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $css;
			?>
			</style>
		<?php endif; ?>
		<?php if ( $js !== '' ) : ?>
			<script id="<?php echo esc_attr( $uid ); ?>-js">
			(function(){
				try {
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $js;
					?>
				} catch (e) {
					if (window.console && console.warn) console.warn('EzLens palette JS', e);
				}
			})();
			</script>
		<?php endif; ?>
		<?php
	}
}

if ( ! function_exists( 'ezp_pdp_render_slot_body' ) ) {
	/**
	 * Inner form content for a slot (custom code or field grid + live panel).
	 *
	 * @param array $slot
	 * @param array $live_fields
	 * @param string $panel_id
	 */
	function ezp_pdp_render_slot_body( $slot, $live_fields, $panel_id ) {
		$tid        = (int) ( $slot['template_id'] ?? 0 );
		$code       = isset( $slot['code'] ) ? (string) $slot['code'] : '';
		$fields     = isset( $slot['fields'] ) && is_array( $slot['fields'] ) ? $slot['fields'] : array();
		$use_custom = ezp_pdp_code_is_custom( $code );
		?>
		<?php if ( $use_custom ) : ?>
			<div class="ezp-pdp-options-custom-wrap">
				<?php ezp_pdp_render_custom_code( $tid, $code ); ?>
			</div>
		<?php else : ?>
			<div class="ezp-pdp-options-grid">
				<?php ezp_pdp_render_options_fields( $tid, $fields ); ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $live_fields ) ) : ?>
		<div class="ezp-pdp-live" data-live-panel id="<?php echo esc_attr( $panel_id ); ?>">
			<button type="button"
				class="ezp-pdp-live-toggle"
				data-live-toggle
				aria-expanded="false"
				aria-controls="<?php echo esc_attr( $panel_id ); ?>-body">
				<span class="ezp-pdp-live-toggle-icon" aria-hidden="true">
					<?php echo ezp_pdp_icon( 'eye', '' ); ?>
				</span>
				<span class="ezp-pdp-live-toggle-text">مشخصات وارد شده شما</span>
				<span class="ezp-pdp-live-toggle-count is-empty" data-live-count>۰</span>
				<span class="ezp-pdp-live-toggle-caret" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<polyline points="6 9 12 15 18 9"/>
					</svg>
				</span>
			</button>
			<div class="ezp-pdp-live-body" id="<?php echo esc_attr( $panel_id ); ?>-body">
				<div class="ezp-pdp-live-title">
					<?php echo ezp_pdp_icon( 'eye', '' ); ?>
					مشخصات وارد شده شما:
				</div>
				<div class="ezp-pdp-live-grid">
					<?php foreach ( $live_fields as $field ) :
						if ( empty( $field['key'] ) || ( isset( $field['type'] ) && $field['type'] === 'heading' ) ) {
							continue;
						}
						?>
						<div class="ezp-pdp-live-item" data-live-wrap="<?php echo esc_attr( $field['key'] ); ?>">
							<span class="ezp-pdp-live-label"><?php echo esc_html( $field['label'] ); ?></span>
							<span class="ezp-pdp-live-value empty" data-live-output="<?php echo esc_attr( $field['key'] ); ?>">وارد نشده</span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
		<?php
	}
}

if ( ! function_exists( 'ezp_pdp_build_live_fields' ) ) {
	function ezp_pdp_build_live_fields( $slot ) {
		$fields     = isset( $slot['fields'] ) && is_array( $slot['fields'] ) ? $slot['fields'] : array();
		$code       = isset( $slot['code'] ) ? (string) $slot['code'] : '';
		$use_custom = ezp_pdp_code_is_custom( $code );
		$live_fields = $fields;

		if ( empty( $live_fields ) && $use_custom ) {
			list( $html_only ) = ezp_pdp_split_code( $code );
			if ( preg_match_all( '/\bname=["\']([^"\']+)["\']/', $html_only, $nm ) ) {
				foreach ( $nm[1] as $raw_name ) {
					$raw_name = preg_replace( '/\[\]$/', '', $raw_name );
					if ( preg_match( '/\[([^\]]+)\]\s*$/', $raw_name, $mm ) ) {
						$key = sanitize_key( $mm[1] );
					} else {
						$key = sanitize_key( $raw_name );
					}
					if ( $key === '' || $key === 'ezlens_options' ) {
						continue;
					}
					$live_fields[] = array( 'key' => $key, 'label' => $key, 'type' => 'text' );
				}
				$seen = array();
				$uniq = array();
				foreach ( $live_fields as $lf ) {
					if ( isset( $seen[ $lf['key'] ] ) ) {
						continue;
					}
					$seen[ $lf['key'] ] = 1;
					$uniq[] = $lf;
				}
				$live_fields = $uniq;
			}
		}
		return $live_fields;
	}
}

if ( ! function_exists( 'ezp_pdp_render_options_block' ) ) {
	/**
	 * Render product-option slots for a placement.
	 * Respects metabox: placement + display (inline | accordion | ajax_modal) + label.
	 *
	 * @param array  $slots
	 * @param string $placement_filter gallery_side|below_price|below_summary|full_width
	 */
	function ezp_pdp_render_options_block( $slots, $placement_filter = 'below_summary' ) {

		$filtered = array();
		foreach ( $slots as $slot ) {
			if ( ( $slot['placement'] ?? '' ) !== $placement_filter ) {
				continue;
			}
			$filtered[] = $slot;
		}
		if ( empty( $filtered ) ) {
			return;
		}

		foreach ( $filtered as $slot ) :
			$tid     = (int) $slot['template_id'];
			$label   = (string) ( $slot['label'] ?? '' );
			$display = (string) ( $slot['display'] ?? 'inline' );
			if ( ! in_array( $display, array( 'inline', 'accordion', 'ajax_modal' ), true ) ) {
				$display = 'inline';
			}
			$code       = isset( $slot['code'] ) ? (string) $slot['code'] : '';
			$use_custom = ezp_pdp_code_is_custom( $code );
			$default_title = $use_custom ? 'نسخه / مشخصات سفارش' : 'مشخصات سفارش خود را وارد کنید';
			$title_text = $label !== '' ? $label : $default_title;
			$panel_id   = 'ezp-live-panel-' . $tid . '-' . wp_rand( 1000, 9999 );
			$slot_uid   = 'ezp-slot-' . $tid . '-' . wp_rand( 1000, 9999 );
			$live_fields = ezp_pdp_build_live_fields( $slot );
			?>
			<div class="ezp-pdp-options ezp-pdp-options--<?php echo esc_attr( $display ); ?><?php echo $use_custom ? ' ezp-pdp-options--custom' : ''; ?>"
				data-display="<?php echo esc_attr( $display ); ?>"
				data-placement="<?php echo esc_attr( $placement_filter ); ?>"
				data-template-id="<?php echo esc_attr( $tid ); ?>"
				id="<?php echo esc_attr( $slot_uid ); ?>">

				<?php if ( $display === 'inline' ) : ?>
					<?php if ( $title_text !== '' ) : ?>
					<h3 class="ezp-pdp-options-title">
						<?php echo ezp_pdp_icon( 'options', '' ); ?>
						<?php echo esc_html( $title_text ); ?>
					</h3>
					<?php endif; ?>
					<?php ezp_pdp_render_slot_body( $slot, $live_fields, $panel_id ); ?>

				<?php elseif ( $display === 'accordion' ) : ?>
					<button type="button"
						class="ezp-pdp-acc-trigger"
						data-ezp-acc-trigger
						aria-expanded="false"
						aria-controls="<?php echo esc_attr( $slot_uid ); ?>-body">
						<span class="ezp-pdp-acc-trigger__icon" aria-hidden="true">
							<?php echo ezp_pdp_icon( 'options', '' ); ?>
						</span>
						<span class="ezp-pdp-acc-trigger__text"><?php echo esc_html( $title_text ); ?></span>
						<span class="ezp-pdp-acc-trigger__chev" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
						</span>
					</button>
					<div class="ezp-pdp-acc-body" id="<?php echo esc_attr( $slot_uid ); ?>-body" hidden>
						<?php ezp_pdp_render_slot_body( $slot, $live_fields, $panel_id ); ?>
					</div>

				<?php else : /* ajax_modal */ ?>
					<button type="button"
						class="ezp-pdp-modal-open"
						data-ezp-modal-open
						data-modal-target="<?php echo esc_attr( $slot_uid ); ?>-modal"
						aria-haspopup="dialog">
						<span class="ezp-pdp-modal-open__icon" aria-hidden="true">
							<?php echo ezp_pdp_icon( 'options', '' ); ?>
						</span>
						<span class="ezp-pdp-modal-open__text"><?php echo esc_html( $title_text ); ?></span>
						<span class="ezp-pdp-modal-open__hint">برای وارد کردن مشخصات ضربه بزنید</span>
						<span class="ezp-pdp-modal-open__arrow" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
						</span>
					</button>

					<div class="ezp-pdp-modal" id="<?php echo esc_attr( $slot_uid ); ?>-modal" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $slot_uid ); ?>-modal-title" hidden>
						<div class="ezp-pdp-modal__backdrop" data-ezp-modal-close tabindex="-1"></div>
						<div class="ezp-pdp-modal__panel">
							<header class="ezp-pdp-modal__head">
								<h3 class="ezp-pdp-modal__title" id="<?php echo esc_attr( $slot_uid ); ?>-modal-title">
									<?php echo ezp_pdp_icon( 'options', '' ); ?>
									<?php echo esc_html( $title_text ); ?>
								</h3>
								<button type="button" class="ezp-pdp-modal__close" data-ezp-modal-close aria-label="بستن">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
								</button>
							</header>
							<div class="ezp-pdp-modal__body">
								<?php ezp_pdp_render_slot_body( $slot, $live_fields, $panel_id ); ?>
							</div>
							<footer class="ezp-pdp-modal__foot">
								<button type="button" class="ezp-pdp-modal__done" data-ezp-modal-close>
									تأیید و بازگشت
								</button>
							</footer>
						</div>
					</div>
				<?php endif; ?>

			</div>
		<?php
		endforeach;
	}
}

/* ============================================================
   Comments: keep product reviews open + allow guests
   ============================================================ */
if ( ! function_exists( 'ezp_pdp_force_product_comments_open' ) ) {
	function ezp_pdp_force_product_comments_open( $open, $post_id ) {
		return ( $post_id && 'product' === get_post_type( $post_id ) ) ? true : $open;
	}
	add_filter( 'comments_open', 'ezp_pdp_force_product_comments_open', 20, 2 );
}

if ( ! function_exists( 'ezp_pdp_review_nonce' ) ) {
	function ezp_pdp_review_nonce() {
		return wp_create_nonce( 'ezp_pdp_review' );
	}
}

if ( ! function_exists( 'ezp_pdp_captcha_generate' ) ) {
	function ezp_pdp_captcha_generate() {
		$letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		$code = '';
		for ( $i = 0; $i < 4; $i++ ) $code .= $letters[ wp_rand( 0, strlen( $letters ) - 1 ) ];
		$a = wp_rand( 2, 9 ); $b = wp_rand( 1, 9 );
		$id = wp_generate_password( 20, false, false );
		$answer = strtoupper( $code ) . '-' . ( $a + $b );
		set_transient( 'ezp_pdp_captcha_' . sanitize_key( $id ), array(
			'answer' => hash_hmac( 'sha256', $answer, wp_salt( 'auth' ) ),
			'ip' => ezp_pdp_review_client_ip(),
		), 10 * MINUTE_IN_SECONDS );
		return array( 'id' => $id, 'code' => $code, 'a' => $a, 'b' => $b );
	}
}

if ( ! function_exists( 'ezp_pdp_captcha_verify' ) ) {
	function ezp_pdp_captcha_verify( $captcha_id, $captcha_answer ) {
		$captcha_id = sanitize_key( (string) $captcha_id );
		$captcha_answer = strtoupper( preg_replace( '/\s+/u', '', (string) $captcha_answer ) );
		if ( '' === $captcha_id || '' === $captcha_answer ) return new WP_Error( 'ezp_captcha_missing', 'لطفاً عبارت امنیتی را کامل وارد کنید.' );
		$key = 'ezp_pdp_captcha_' . $captcha_id;
		$data = get_transient( $key );
		delete_transient( $key );
		if ( ! is_array( $data ) || empty( $data['answer'] ) ) return new WP_Error( 'ezp_captcha_expired', 'کپتچا منقضی شده است. لطفاً عبارت جدید را وارد کنید.' );
		$ip = ezp_pdp_review_client_ip();
		if ( ! empty( $data['ip'] ) && $ip && ! hash_equals( (string) $data['ip'], $ip ) ) return new WP_Error( 'ezp_captcha_ip', 'تأیید امنیتی نامعتبر است. لطفاً دوباره تلاش کنید.' );
		$actual = hash_hmac( 'sha256', $captcha_answer, wp_salt( 'auth' ) );
		if ( ! hash_equals( (string) $data['answer'], $actual ) ) return new WP_Error( 'ezp_captcha_failed', 'پاسخ کپتچا صحیح نیست. لطفاً دوباره تلاش کنید.' );
		return true;
	}
}

if ( ! function_exists( 'ezp_pdp_captcha_markup' ) ) {
	function ezp_pdp_captcha_markup( $product_id = 0 ) {
		$captcha = ezp_pdp_captcha_generate();
		?>
		<div class="ezp-pdp-recaptcha ezp-pdp-local-captcha" data-captcha-id="<?php echo esc_attr( $captcha['id'] ); ?>">
			<div class="ezp-pdp-local-captcha__head">
				<span class="ezp-pdp-recaptcha__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="3"></rect><path d="M7 9h2M7 13h2M11 9h2M11 13h2M15 9h2M15 13h2"></path></svg></span>
				<div><div class="ezp-pdp-recaptcha__head-title">تأیید امنیتی</div><p class="ezp-pdp-local-captcha__hint">برای ثبت نظر، عبارت زیر را وارد کنید.</p></div>
			</div>
			<div class="ezp-pdp-local-captcha__challenge" aria-label="عبارت امنیتی"><span class="ezp-pdp-local-captcha__code"><?php echo esc_html( $captcha['code'] ); ?></span><span class="ezp-pdp-local-captcha__math"><?php echo esc_html( $captcha['a'] . ' + ' . $captcha['b'] . ' = ؟' ); ?></span></div>
			<input type="hidden" name="captcha_id" value="<?php echo esc_attr( $captcha['id'] ); ?>">
			<div class="ezp-pdp-local-captcha__input-wrap"><input type="text" name="captcha_answer" maxlength="20" autocomplete="off" inputmode="text" required placeholder="مثلاً: A7K2-11" aria-label="پاسخ تأیید امنیتی"></div>
			<p class="ezp-pdp-local-captcha__note">پاسخ را به شکل «کد-حاصل جمع» وارد کنید؛ حروف کوچک و بزرگ فرقی ندارند.</p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'ezp_pdp_review_client_ip' ) ) {
	function ezp_pdp_review_client_ip() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
	}
}
if ( ! function_exists( 'ezp_pdp_review_rate_key' ) ) {
	function ezp_pdp_review_rate_key( $prefix, $value ) {
		return $prefix . md5( strtolower( trim( (string) $value ) ) );
	}
}
if ( ! function_exists( 'ezp_pdp_guest_review_rate_limited' ) ) {
	function ezp_pdp_guest_review_rate_limited( $email = '', $ip = '' ) {
		$ip = $ip ? $ip : ezp_pdp_review_client_ip();

		$ip_key = $ip ? ezp_pdp_review_rate_key( 'ezp_pdp_review_ip_', $ip ) : '';
		if ( $ip_key && get_transient( $ip_key ) ) {
			return new WP_Error( 'ezp_review_rate_ip', 'برای جلوگیری از ارسال‌های خودکار، لطفاً کمی بعد دوباره تلاش کنید.' );
		}

		$email_key = $email ? ezp_pdp_review_rate_key( 'ezp_pdp_review_email_', $email ) : '';
		if ( $email_key && get_transient( $email_key ) ) {
			return new WP_Error( 'ezp_review_rate_email', 'از این ایمیل اخیراً نظری ثبت شده است. لطفاً کمی بعد دوباره تلاش کنید.' );
		}

		return true;
	}
}
if ( ! function_exists( 'ezp_pdp_mark_guest_review_rate' ) ) {
	function ezp_pdp_mark_guest_review_rate( $email = '', $ip = '' ) {
		$ip = $ip ? $ip : ezp_pdp_review_client_ip();
		if ( $ip ) set_transient( ezp_pdp_review_rate_key( 'ezp_pdp_review_ip_', $ip ), 1, 60 );
		if ( $email ) set_transient( ezp_pdp_review_rate_key( 'ezp_pdp_review_email_', $email ), 1, 300 );
	}
}
if ( ! function_exists( 'ezp_pdp_guest_review_attempt_limit' ) ) {
	function ezp_pdp_guest_review_attempt_limit( $ip = '' ) {
		$ip = $ip ? $ip : ezp_pdp_review_client_ip();
		if ( ! $ip ) return true;

		$key = ezp_pdp_review_rate_key( 'ezp_pdp_review_attempts_', $ip );
		$data = get_transient( $key );
		$data = is_array( $data ) ? $data : array( 'count' => 0, 'started' => time() );

		if ( $data['count'] >= 12 ) {
			return new WP_Error( 'ezp_review_rate_hour', 'تعداد تلاش‌های ارسال نظر از این اتصال بیش از حد مجاز است. لطفاً بعداً دوباره تلاش کنید.' );
		}

		$data['count']++;
		if ( ! get_transient( $key ) ) $data['started'] = time();
		set_transient( $key, $data, HOUR_IN_SECONDS );
		return true;
	}
}

/* ============================================================
   ADMIN META BOXES
   ============================================================ */
if ( ! function_exists( 'ezp_pdp_specs_register_metabox' ) ) {
	add_action( 'add_meta_boxes', 'ezp_pdp_specs_register_metabox' );
	function ezp_pdp_specs_register_metabox() {
		add_meta_box(
			'ezp_pdp_specs_box',
			'مشخصات فنی (محتوای تب مشخصات)',
			'ezp_pdp_specs_metabox_render',
			'product',
			'normal',
			'high'
		);
	}
}

if ( ! function_exists( 'ezp_pdp_specs_metabox_render' ) ) {
	function ezp_pdp_specs_metabox_render( $post ) {
		wp_nonce_field( 'ezp_pdp_specs_save', 'ezp_pdp_specs_nonce' );
		$content = get_post_meta( $post->ID, '_ezp_pdp_specs_content', true );
		?>
		<div style="margin-bottom:8px;font-size:12px;color:#64748b;">
			محتوایی که اینجا وارد می‌کنید، در تب «مشخصات» صفحه محصول نمایش داده می‌شود. اگر خالی بماند، مشخصات ووکامرس (Attributes) نمایش داده می‌شوند.
		</div>
		<?php
		wp_editor( $content, 'ezp_pdp_specs_content', array(
			'textarea_name' => 'ezp_pdp_specs_content',
			'textarea_rows' => 10,
			'media_buttons' => true,
			'teeny'         => false,
			'quicktags'     => true,
			'tinymce'       => true,
		) );
	}
}

if ( ! function_exists( 'ezp_pdp_specs_save_metabox' ) ) {
	add_action( 'save_post_product', 'ezp_pdp_specs_save_metabox' );
	function ezp_pdp_specs_save_metabox( $post_id ) {
		if ( ! isset( $_POST['ezp_pdp_specs_nonce'] ) ) return;
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ezp_pdp_specs_nonce'] ) ), 'ezp_pdp_specs_save' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		$raw = isset( $_POST['ezp_pdp_specs_content'] ) ? wp_unslash( $_POST['ezp_pdp_specs_content'] ) : '';

		if ( '' === trim( $raw ) ) {
			delete_post_meta( $post_id, '_ezp_pdp_specs_content' );
		} else {
			update_post_meta( $post_id, '_ezp_pdp_specs_content', $raw );
		}
	}
}

/* ============================================================
   Shortcode
   ============================================================ */
if ( ! function_exists( 'ezp_custom_product_page' ) ) {

	add_shortcode( 'custom_product_page', 'ezp_custom_product_page' );

	function ezp_custom_product_page( $atts = array() ) {

		if ( ! function_exists( 'wc_get_product' ) ) return '';

		$atts = shortcode_atts( array( 'id' => 0 ), $atts, 'custom_product_page' );
		$product_id = absint( $atts['id'] ) ?: get_the_ID();
		if ( ! $product_id ) return '';

		$product = wc_get_product( $product_id );
		if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
			return '<div class="ezp-pdp-error">محصول یافت نشد.</div>';
		}

		$id          = $product->get_id();
		$title       = $product->get_name();
		$price       = $product->get_price_html();
		$short_desc  = $product->get_short_description();
		$description = $product->get_description();
		$sku         = $product->get_sku();
		$permalink   = get_permalink( $id );

		$average_rating = (float) $product->get_average_rating();
		$review_count   = (int) $product->get_review_count();

		$in_stock     = $product->is_in_stock();
		$stock_qty    = $product->get_stock_quantity();
		$manage_stock = $product->managing_stock();
		$is_on_sale   = $product->is_on_sale();

		$regular_price = (float) $product->get_regular_price();
		$sale_price    = (float) $product->get_sale_price();
		$discount_pct  = ( $is_on_sale && $regular_price > 0 ) ? round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 ) : 0;
		$price_raw     = (float) $product->get_price();

		$images = array();
		$main_image_id = $product->get_image_id();
		if ( $main_image_id ) {
			$images[] = array( 'id' => $main_image_id, 'url' => wp_get_attachment_image_url( $main_image_id, 'large' ) );
		}
		foreach ( $product->get_gallery_image_ids() as $gid ) {
			if ( $gid === $main_image_id ) continue;
			$url = wp_get_attachment_image_url( $gid, 'large' );
			if ( $url ) $images[] = array( 'id' => $gid, 'url' => $url );
		}
		if ( empty( $images ) ) {
			$images[] = array( 'id' => 0, 'url' => wc_placeholder_img_src( 'large' ) );
		}

		$categories = wp_get_post_terms( $id, 'product_cat', array( 'fields' => 'names' ) );

		$attributes = array();
		foreach ( $product->get_attributes() as $attribute ) {
			if ( ! $attribute->get_visible() ) continue;
			$name  = wc_attribute_label( $attribute->get_name() );
			$value = '';
			if ( $attribute->is_taxonomy() ) {
				$terms = wc_get_product_terms( $id, $attribute->get_name(), array( 'fields' => 'names' ) );
				if ( ! empty( $terms ) ) $value = implode( '، ', $terms );
			} else {
				$options = $attribute->get_options();
				if ( ! empty( $options ) ) $value = implode( '، ', $options );
			}
			if ( $value !== '' ) $attributes[] = array( 'name' => $name, 'value' => $value );
		}
		if ( $sku ) $attributes[] = array( 'name' => 'شناسه کالا', 'value' => $sku );

		$specs_content = get_post_meta( $id, '_ezp_pdp_specs_content', true );
		/* Load palette slots */
		$ezlens_slots = ezp_pdp_load_slots( $id );

		$related_products = array();
		foreach ( wc_get_related_products( $id, 8, array() ) as $rid ) {
			$rp = wc_get_product( $rid );
			if ( $rp ) $related_products[] = $rp;
		}

		$reviews = get_comments( array(
			'post_id' => $id, 'status' => 'approve', 'type' => 'review',
			'number' => 30, 'orderby' => 'comment_date_gmt', 'order' => 'DESC',
		) );

		$rating_counts = array( 5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0 );
		foreach ( get_comments( array( 'post_id' => $id, 'status' => 'approve', 'type' => 'review', 'number' => 0 ) ) as $r ) {
			$rt = (int) get_comment_meta( $r->comment_ID, 'rating', true );
			if ( $rt >= 1 && $rt <= 5 ) $rating_counts[ $rt ]++;
		}
		$total_rated = array_sum( $rating_counts );

		$free_ship_threshold = 500000;
		$remaining_to_free   = max( 0, $free_ship_threshold - $price_raw );
		$free_ship_progress  = min( 100, ( $price_raw / $free_ship_threshold ) * 100 );

		ob_start();
		?>
		<div class="ezp-pdp" dir="rtl" data-product-id="<?php echo esc_attr( $id ); ?>">
		<style>
		/* ============================================================
		   Fonts
		   ============================================================ */
		@font-face {
			font-family: 'EzLensVazir';
			src: url('<?php echo esc_url( EZLAUTH_PLUGIN_URL . 'assets/fonts/vazirmatn/Vazirmatn-Regular.woff2' ); ?>') format('woff2');
			font-weight: 400;
			font-display: swap;
		}
		@font-face {
			font-family: 'EzLensVazir';
			src: url('<?php echo esc_url( EZLAUTH_PLUGIN_URL . 'assets/fonts/vazirmatn/Vazirmatn-Medium.woff2' ); ?>') format('woff2');
			font-weight: 500;
			font-display: swap;
		}
		@font-face {
			font-family: 'EzLensVazir';
			src: url('<?php echo esc_url( EZLAUTH_PLUGIN_URL . 'assets/fonts/vazirmatn/Vazirmatn-Bold.woff2' ); ?>') format('woff2');
			font-weight: 700;
			font-display: swap;
		}

		.wd-sticky-btn,
		.wd-sticky-btn-container,
		.wd-sticky-btn-shown {
			display: none !important;
		}

		/* Woodmart Popup Override */
		.mfp-bg {
			background: rgba(15, 23, 42, .55) !important;
			backdrop-filter: blur(8px) !important;
			-webkit-backdrop-filter: blur(8px) !important;
		}
		.wd-popup.wd-popup-added-cart {
			background: #ffffff !important;
			border-radius: 24px !important;
			box-shadow: 0 25px 80px rgba(7, 27, 122, .18), 0 8px 30px rgba(0,0,0,.08) !important;
			padding: 0 !important;
			max-width: 440px !important;
			width: calc(100% - 32px) !important;
			border: 1px solid #e5e9f0 !important;
			overflow: hidden !important;
			position: relative !important;
			animation: ezpPopupIn .38s cubic-bezier(.34, 1.56, .64, 1) !important;
		}
		@keyframes ezpPopupIn {
			from { opacity: 0; transform: scale(.85) translateY(24px); }
			to { opacity: 1; transform: scale(1) translateY(0); }
		}
		.wd-popup.wd-popup-added-cart::before {
			content: "";
			position: absolute;
			top: 0; left: 0; right: 0;
			height: 5px;
			background: linear-gradient(90deg, #10b981, #34d399, #6ee7b7);
		}

		.wd-popup .wd-popup-close,
		.wd-popup-wrap .wd-popup-close,
		.mfp-content .wd-popup-close,
		.wd-popup-added-cart .wd-popup-close {
			position: absolute !important;
			top: 14px !important;
			right: 14px !important;
			left: auto !important;
			width: 38px !important;
			height: 38px !important;
			padding: 0 !important;
			margin: 0 !important;
			background: #f1f5f9 !important;
			border: 1.5px solid #e2e8f0 !important;
			border-radius: 50% !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			transition: all .35s cubic-bezier(.34, 1.56, .64, 1) !important;
			z-index: 9999 !important;
			cursor: pointer !important;
			box-shadow: 0 4px 12px rgba(15, 23, 42, .06) !important;
			animation: ezpCloseIn .5s cubic-bezier(.34, 1.56, .64, 1) .15s both;
			outline: none !important;
			text-decoration: none !important;
			overflow: hidden !important;
			line-height: 1 !important;
			font-size: 0 !important;
		}
		@keyframes ezpCloseIn {
			from { opacity: 0; transform: scale(0) rotate(-90deg); }
			to { opacity: 1; transform: scale(1) rotate(0deg); }
		}
		.wd-popup .wd-popup-close:hover,
		.wd-popup-wrap .wd-popup-close:hover,
		.mfp-content .wd-popup-close:hover,
		.wd-popup-added-cart .wd-popup-close:hover {
			background: linear-gradient(135deg, #e11d48 0%, #be123c 100%) !important;
			border-color: transparent !important;
			transform: rotate(180deg) scale(1.08) !important;
			box-shadow: 0 8px 24px rgba(225, 29, 72, .4) !important;
		}
		.wd-popup .wd-popup-close:active,
		.wd-popup-wrap .wd-popup-close:active,
		.mfp-content .wd-popup-close:active,
		.wd-popup-added-cart .wd-popup-close:active {
			transform: rotate(180deg) scale(.94) !important;
		}
		.wd-popup .wd-popup-close a,
		.wd-popup-wrap .wd-popup-close a,
		.mfp-content .wd-popup-close a,
		.wd-popup-added-cart .wd-popup-close a {
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			width: 100% !important;
			height: 100% !important;
			color: transparent !important;
			text-decoration: none !important;
			background: transparent !important;
			border: 0 !important;
			padding: 0 !important;
			margin: 0 !important;
			font-size: 0 !important;
			line-height: 0 !important;
			text-indent: -9999px !important;
			overflow: hidden !important;
		}
		.wd-popup .wd-popup-close .wd-action-icon,
		.wd-popup-wrap .wd-popup-close .wd-action-icon,
		.mfp-content .wd-popup-close .wd-action-icon,
		.wd-popup-added-cart .wd-popup-close .wd-action-icon {
			width: 14px !important;
			height: 14px !important;
			min-width: 14px !important;
			min-height: 14px !important;
			display: block !important;
			position: static !important;
			background: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'><line x1='6' y1='6' x2='18' y2='18'/><line x1='6' y1='18' x2='18' y2='6'/></svg>") center center / contain no-repeat !important;
			font-size: 0 !important;
			line-height: 0 !important;
			text-indent: -9999px !important;
			transform: none !important;
			border: 0 !important;
			padding: 0 !important;
			margin: 0 !important;
			box-shadow: none !important;
			transition: background-image .3s ease !important;
		}
		.wd-popup .wd-popup-close .wd-action-icon::before,
		.wd-popup .wd-popup-close .wd-action-icon::after,
		.wd-popup-wrap .wd-popup-close .wd-action-icon::before,
		.wd-popup-wrap .wd-popup-close .wd-action-icon::after,
		.mfp-content .wd-popup-close .wd-action-icon::before,
		.mfp-content .wd-popup-close .wd-action-icon::after,
		.wd-popup-added-cart .wd-popup-close .wd-action-icon::before,
		.wd-popup-added-cart .wd-popup-close .wd-action-icon::after {
			display: none !important;
			content: none !important;
		}
		.wd-popup .wd-popup-close:hover .wd-action-icon,
		.wd-popup-wrap .wd-popup-close:hover .wd-action-icon,
		.mfp-content .wd-popup-close:hover .wd-action-icon,
		.wd-popup-added-cart .wd-popup-close:hover .wd-action-icon {
			background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'><line x1='6' y1='6' x2='18' y2='18'/><line x1='6' y1='18' x2='18' y2='6'/></svg>") !important;
		}
		.wd-popup .wd-popup-close .wd-action-text,
		.wd-popup-wrap .wd-popup-close .wd-action-text,
		.mfp-content .wd-popup-close .wd-action-text,
		.wd-popup-added-cart .wd-popup-close .wd-action-text {
			display: none !important;
		}
		.wd-popup.wd-popup-added-cart .added-to-cart {
			padding: 46px 30px 30px !important;
			text-align: center !important;
			font-family: 'EzLensVazir', Tahoma, sans-serif !important;
		}
		.wd-popup.wd-popup-added-cart .added-to-cart::before {
			content: "";
			display: block;
			width: 80px;
			height: 80px;
			margin: 0 auto 18px;
			background: #dcfce7 url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='44' height='44' viewBox='0 0 24 24' fill='none' stroke='%23059669' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'><circle cx='12' cy='12' r='10'/><polyline points='8 12 11 15 16 9'/></svg>") center/44px no-repeat;
			border-radius: 50%;
			box-shadow: 0 0 0 0 rgba(5, 150, 105, .4);
			animation: ezpSuccessPulse 1.6s ease-out;
		}
		@keyframes ezpSuccessPulse {
			0% { box-shadow: 0 0 0 0 rgba(5, 150, 105, .5); transform: scale(.5); }
			50% { transform: scale(1.08); }
			100% { box-shadow: 0 0 0 26px rgba(5, 150, 105, 0); transform: scale(1); }
		}
		.wd-popup.wd-popup-added-cart .added-to-cart h3 {
			font-family: 'EzLensVazir', Tahoma, sans-serif !important;
			font-size: 0 !important;
			margin: 0 0 4px !important;
			line-height: 1.5 !important;
		}
		.wd-popup.wd-popup-added-cart .added-to-cart h3::before {
			content: "عالی شد!";
			display: block;
			font-size: 20px;
			font-weight: 800;
			color: #065f46;
			margin-bottom: 6px;
		}
		.wd-popup.wd-popup-added-cart .added-to-cart h3::after {
			content: "محصول شما با موفقیت به سبد خرید اضافه شد";
			display: block;
			font-size: 13px;
			font-weight: 500;
			color: #64748b;
			line-height: 1.7;
		}
		.wd-popup.wd-popup-added-cart .added-to-cart .btn {
			display: inline-flex !important;
			align-items: center !important;
			justify-content: center !important;
			gap: 6px !important;
			padding: 12px 24px !important;
			border-radius: 12px !important;
			font-family: 'EzLensVazir', Tahoma, sans-serif !important;
			font-weight: 700 !important;
			font-size: 14px !important;
			border: 2px solid transparent !important;
			transition: all .2s cubic-bezier(.4,0,.2,1) !important;
			text-decoration: none !important;
			margin: 4px !important;
			min-height: 48px !important;
			line-height: 1 !important;
			white-space: nowrap !important;
		}
		.wd-popup.wd-popup-added-cart .added-to-cart .btn-default {
			background: #ffffff !important;
			border-color: #e2e8f0 !important;
			color: #0f172a !important;
		}
		.wd-popup.wd-popup-added-cart .added-to-cart .btn-default:hover {
			background: #f8fafc !important;
			border-color: #cbd5e1 !important;
			transform: translateY(-2px) !important;
			box-shadow: 0 6px 20px rgba(15, 23, 42, .08) !important;
		}
		.wd-popup.wd-popup-added-cart .added-to-cart .btn-accent,
		.wd-popup.wd-popup-added-cart .added-to-cart .view-cart {
			background: linear-gradient(135deg, #071b7a 0%, #041252 100%) !important;
			border-color: transparent !important;
			color: #ffffff !important;
			box-shadow: 0 6px 20px rgba(7, 27, 122, .3) !important;
		}
		.wd-popup.wd-popup-added-cart .added-to-cart .btn-accent:hover,
		.wd-popup.wd-popup-added-cart .added-to-cart .view-cart:hover {
			background: linear-gradient(135deg, #041252 0%, #020b3a 100%) !important;
			transform: translateY(-2px) !important;
			box-shadow: 0 10px 30px rgba(7, 27, 122, .45) !important;
			color: #ffffff !important;
		}

		/* ============================================================
		   Container
		   ============================================================ */
		.ezp-pdp {
			--primary: #071b7a;
			--primary-dark: #041252;
			--accent: #e11d48;
			--accent-dark: #be123c;
			--text: #0f172a;
			--text-soft: #334155;
			--muted: #64748b;
			--border: #e5e9f0;
			--soft: #f7f9fc;
			--success: #059669;
			--success-dark: #047857;
			--warning: #f59e0b;
			--warning-dark: #b45309;
			--danger: #dc2626;

			width: 100%;
			max-width: 1400px;
			margin: 0 auto;
			padding: 16px;
			font-family: 'EzLensVazir', Tahoma, Arial, sans-serif;
			color: var(--text);
			line-height: 1.8;
			font-size: 14px;
		}
		.ezp-pdp * { box-sizing: border-box; }
		.ezp-pdp img { max-width: 100%; height: auto; }
		.ezp-pdp a { text-decoration: none; color: inherit; }
		.ezp-pdp button { font-family: inherit; }

		.ezp-pdp-grid {
			display: grid;
			grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr);
			gap: 32px;
			padding: 20px;
			background: #fff;
			border: 1px solid var(--border);
			border-radius: 20px;
			box-shadow: 0 6px 30px rgba(15, 23, 42, .05);
		}
		@media (max-width: 900px) {
			.ezp-pdp-grid { grid-template-columns: 1fr; gap: 22px; padding: 14px; }
		}

		/* Gallery */
		.ezp-pdp-main-img {
			position: relative;
			width: 100%;
			aspect-ratio: 1 / 1;
			display: flex;
			align-items: center;
			justify-content: center;
			overflow: hidden;
			background: #fafbfc;
			border: 1px solid var(--border);
			border-radius: 16px;
		}
		.ezp-pdp-main-img img {
			width: 100%; height: 100%; object-fit: contain;
			transition: transform .35s cubic-bezier(.4,0,.2,1);
		}
		.ezp-pdp-main-img:hover img { transform: scale(1.04); }

		.ezp-pdp-sale-badge {
			position: absolute; top: 14px; right: 14px; z-index: 5;
			display: inline-flex; align-items: center; gap: 5px;
			padding: 6px 14px;
			border-radius: 10px;
			background: linear-gradient(135deg, #ff4757, #e63946);
			color: #fff;
			font-size: 12px; font-weight: 800;
			box-shadow: 0 4px 15px rgba(225, 29, 72, .4);
			animation: ezpBadgePulse 2s ease-in-out infinite;
		}
		.ezp-pdp-sale-badge svg {
			width: 14px; height: 14px;
			fill: none; stroke: currentColor; stroke-width: 2.5;
			stroke-linecap: round; stroke-linejoin: round;
		}
		@keyframes ezpBadgePulse {
			0%, 100% { transform: scale(1); }
			50% { transform: scale(1.06); }
		}

		.ezp-pdp-thumbs {
			display: flex; gap: 8px;
			margin-top: 12px;
			overflow-x: auto;
			padding-bottom: 4px;
		}
		.ezp-pdp-thumb {
			flex: 0 0 72px;
			width: 72px; height: 72px;
			padding: 2px;
			background: #fff;
			border: 1px solid var(--border);
			border-radius: 10px;
			cursor: pointer;
			overflow: hidden;
			transition: all .2s cubic-bezier(.4,0,.2,1);
		}
		.ezp-pdp-thumb.active {
			border: 2px solid var(--primary);
			box-shadow: 0 0 0 3px rgba(7, 27, 122, .1);
		}
		.ezp-pdp-thumb:hover { transform: translateY(-2px); }
		.ezp-pdp-thumb img {
			width: 100%; height: 100%;
			object-fit: contain;
			border-radius: 8px;
		}

		/* Info */
		.ezp-pdp-info { display: flex; flex-direction: column; min-width: 0; }

		.ezp-pdp-breadcrumb {
			display: flex; align-items: center; flex-wrap: wrap; gap: 6px;
			margin-bottom: 12px;
			font-size: 12px; color: var(--muted);
		}
		.ezp-pdp-breadcrumb svg {
			width: 14px; height: 14px;
			fill: none; stroke: currentColor; stroke-width: 2;
			opacity: .5;
		}

		.ezp-pdp-title {
			margin: 0 0 12px;
			font-size: clamp(20px, 2.6vw, 26px);
			line-height: 1.5;
			font-weight: 700;
			color: var(--text);
		}

		.ezp-pdp-rating {
			display: flex; align-items: center;
			flex-wrap: wrap; gap: 8px;
			margin-bottom: 14px;
		}
		.ezp-pdp-stars { color: #f59e0b; font-size: 16px; letter-spacing: 1px; }
		.ezp-pdp-rating-number { font-weight: 700; font-size: 13px; }
		.ezp-pdp-rating-count { color: var(--muted); font-size: 12px; }

		.ezp-pdp-price {
			display: flex; align-items: center;
			flex-wrap: wrap; gap: 10px;
			margin: 4px 0 14px;
			font-size: 26px; font-weight: 800;
		}
		.ezp-pdp-price del { font-size: 15px; font-weight: 500; color: #94a3b8; }
		.ezp-pdp-price ins { text-decoration: none; color: var(--primary); }

		.ezp-pdp-discount-tag {
			display: inline-flex; align-items: center; gap: 4px;
			padding: 4px 10px;
			border-radius: 20px;
			background: linear-gradient(135deg, #fff1f2, #ffe4e6);
			border: 1px solid #fecdd3;
			color: var(--accent);
			font-size: 12px; font-weight: 700;
		}
		.ezp-pdp-discount-tag svg {
			width: 14px; height: 14px;
			fill: none; stroke: currentColor; stroke-width: 2;
		}

		.ezp-pdp-stock {
			display: inline-flex; align-items: center; gap: 6px;
			width: fit-content;
			padding: 6px 14px;
			margin-bottom: 14px;
			border-radius: 30px;
			font-size: 13px; font-weight: 700;
		}
		.ezp-pdp-stock svg {
			width: 16px; height: 16px;
			fill: none; stroke: currentColor; stroke-width: 2;
			stroke-linecap: round; stroke-linejoin: round;
		}
		.ezp-pdp-stock.in { background: #dcfce7; color: #166534; }
		.ezp-pdp-stock.low { background: #fef3c7; color: var(--warning-dark); }
		.ezp-pdp-stock.out { background: #fee2e2; color: #991b1b; }

		.ezp-pdp-short-desc {
			margin-bottom: 16px;
			padding: 14px 18px;
			background: var(--soft);
			border-radius: 12px;
			border-right: 4px solid var(--primary);
			font-size: 13px;
			color: var(--text-soft);
		}
		.ezp-pdp-short-desc p:last-child { margin-bottom: 0; }

		/* Free Ship */
		.ezp-pdp-ship {
			margin-bottom: 16px;
			padding: 12px 16px;
			background: linear-gradient(135deg, #fef3c7 0%, #fef9e7 100%);
			border: 1px solid #fde68a;
			border-radius: 12px;
			transition: all .4s ease;
		}
		.ezp-pdp-ship.done {
			background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
			border-color: #86efac;
		}
		.ezp-pdp-ship-top {
			display: flex; align-items: center; gap: 8px;
			margin-bottom: 8px;
			font-size: 12px; font-weight: 700;
			color: var(--warning-dark);
		}
		.ezp-pdp-ship.done .ezp-pdp-ship-top { color: #166534; }
		.ezp-pdp-ship-top svg {
			width: 16px; height: 16px;
			fill: none; stroke: currentColor; stroke-width: 2;
			stroke-linecap: round; stroke-linejoin: round;
			flex-shrink: 0;
		}
		.ezp-pdp-ship-amount {
			display: inline-block;
			padding: 1px 8px;
			background: #fff;
			border-radius: 6px;
			font-weight: 800;
			color: var(--warning-dark);
			margin: 0 3px;
		}
		.ezp-pdp-ship.done .ezp-pdp-ship-amount { color: var(--success); }
		.ezp-pdp-ship-track {
			height: 6px;
			background: #fff;
			border-radius: 20px;
			overflow: hidden;
			box-shadow: inset 0 1px 2px rgba(0,0,0,.05);
		}
		.ezp-pdp-ship-fill {
			height: 100%;
			background: linear-gradient(90deg, #f59e0b, #fbbf24);
			border-radius: inherit;
			transition: width 1s cubic-bezier(.4,0,.2,1);
			position: relative; overflow: hidden;
		}
		.ezp-pdp-ship.done .ezp-pdp-ship-fill {
			background: linear-gradient(90deg, #059669, #10b981);
		}
		.ezp-pdp-ship-shine {
			position: absolute; top: 0; left: 0; right: 0; bottom: 0;
			background: linear-gradient(90deg, transparent, rgba(255,255,255,.6), transparent);
			animation: ezpShine 2.2s infinite;
		}
		@keyframes ezpShine {
			0% { transform: translateX(-100%); }
			100% { transform: translateX(100%); }
		}

		/* Product Options */
		.ezp-pdp-options {
			margin: 6px 0 16px;
			padding: 16px;
			background: #fafbfc;
			border: 1px solid var(--border);
			border-radius: 14px;
			width: 100%;
			max-width: 100%;
			box-sizing: border-box;
			overflow: hidden;
		}
		.ezp-pdp-options-title {
			display: flex; align-items: center; gap: 8px;
			margin: 0 0 14px;
			font-size: 15px; font-weight: 700;
			min-width: 0;
			overflow-wrap: anywhere;
			word-break: break-word;
		}
		.ezp-pdp-options-title svg {
			width: 18px; height: 18px;
			fill: none; stroke: var(--primary); stroke-width: 2;
			stroke-linecap: round; stroke-linejoin: round;
			flex-shrink: 0;
		}
		.ezp-pdp-options-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 12px;
			width: 100%;
			max-width: 100%;
			box-sizing: border-box;
		}
		@media (max-width: 600px) {
			.ezp-pdp-options {
				padding: 12px;
				margin: 4px 0 12px;
			}
			.ezp-pdp-options-grid {
				grid-template-columns: minmax(0, 1fr);
				gap: 10px;
			}
		}

		/* —— Display modes: accordion —— */
		.ezp-pdp-options--accordion {
			padding: 0;
			overflow: hidden;
			background: #fff;
		}
		.ezp-pdp-acc-trigger {
			width: 100%;
			display: flex;
			align-items: center;
			gap: 10px;
			padding: 14px 16px;
			border: 0;
			background: linear-gradient(135deg, #f0f7ff 0%, #f8fafc 100%);
			cursor: pointer;
			font: inherit;
			color: #0f172a;
			text-align: right;
			transition: background .2s ease;
		}
		.ezp-pdp-acc-trigger:hover { background: linear-gradient(135deg, #e0effe 0%, #f1f5f9 100%); }
		.ezp-pdp-acc-trigger[aria-expanded="true"] {
			background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
			border-bottom: 1px solid var(--border, #e2e8f0);
		}
		.ezp-pdp-acc-trigger__icon svg,
		.ezp-pdp-acc-trigger__icon {
			width: 20px; height: 20px; flex-shrink: 0;
			color: #2563eb;
		}
		.ezp-pdp-acc-trigger__icon svg { width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 2; }
		.ezp-pdp-acc-trigger__text {
			flex: 1;
			font-size: 14px;
			font-weight: 700;
			min-width: 0;
		}
		.ezp-pdp-acc-trigger__chev {
			width: 18px; height: 18px; flex-shrink: 0;
			color: #64748b;
			transition: transform .25s ease;
		}
		.ezp-pdp-acc-trigger__chev svg { width: 18px; height: 18px; display: block; }
		.ezp-pdp-acc-trigger[aria-expanded="true"] .ezp-pdp-acc-trigger__chev {
			transform: rotate(180deg);
			color: #2563eb;
		}
		.ezp-pdp-acc-body {
			padding: 14px 16px 16px;
			background: #fafbfc;
		}
		.ezp-pdp-acc-body[hidden] { display: none !important; }

		/* —— Display modes: ajax_modal trigger —— */
		.ezp-pdp-options--ajax_modal {
			padding: 0;
			background: transparent;
			border: 0;
			overflow: visible !important;
			/* prevent transform/filter creating containing block for fixed modal */
			transform: none !important;
			filter: none !important;
			perspective: none !important;
		}
		.ezp-pdp-modal-open {
			width: 100%;
			display: grid;
			grid-template-columns: auto 1fr auto;
			grid-template-areas:
				"icon text arrow"
				"icon hint arrow";
			align-items: center;
			column-gap: 12px;
			row-gap: 2px;
			padding: 14px 16px;
			border: 1px solid #bfdbfe;
			border-radius: 14px;
			background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 55%, #ffffff 100%);
			box-shadow: 0 4px 14px rgba(37, 99, 235, .08);
			cursor: pointer;
			font: inherit;
			color: #0f172a;
			text-align: right;
			transition: box-shadow .2s ease, border-color .2s ease, background .2s ease;
		}
		.ezp-pdp-modal-open:hover {
			border-color: #93c5fd;
			box-shadow: 0 8px 22px rgba(37, 99, 235, .14);
			background: linear-gradient(135deg, #dbeafe 0%, #f8fafc 55%, #ffffff 100%);
		}
		.ezp-pdp-modal-open:active { box-shadow: 0 3px 10px rgba(37, 99, 235, .12); }
		.ezp-pdp-modal-open__icon { grid-area: icon; color: #2563eb; }
		.ezp-pdp-modal-open__icon svg { width: 22px; height: 22px; fill: none; stroke: currentColor; stroke-width: 2; display: block; }
		.ezp-pdp-modal-open__text {
			grid-area: text;
			font-size: 15px;
			font-weight: 700;
			line-height: 1.3;
		}
		.ezp-pdp-modal-open__hint {
			grid-area: hint;
			font-size: 12px;
			color: #64748b;
			line-height: 1.3;
		}
		.ezp-pdp-modal-open__arrow { grid-area: arrow; color: #3b82f6; }
		.ezp-pdp-modal-open__arrow svg { width: 18px; height: 18px; display: block; }

		/* —— Modal shell —— */
		.ezp-pdp-modal {
			position: fixed !important;
			inset: 0 !important;
			z-index: 999999 !important;
			display: none;
			align-items: flex-end;
			justify-content: center;
			padding: 0;
			margin: 0;
			/* never inherit parent transform/overflow */
			transform: none !important;
			filter: none !important;
			pointer-events: none;
		}
		.ezp-pdp-modal[hidden] {
			display: none !important;
			pointer-events: none !important;
			visibility: hidden !important;
		}
		.ezp-pdp-modal.is-open {
			display: flex !important;
			pointer-events: auto !important;
			visibility: visible !important;
			opacity: 1 !important;
		}
		.ezp-pdp-modal__backdrop {
			position: absolute;
			inset: 0;
			background: rgba(15, 23, 42, .55);
			backdrop-filter: blur(3px);
			-webkit-backdrop-filter: blur(3px);
			pointer-events: auto;
			cursor: pointer;
		}
		.ezp-pdp-modal__panel {
			position: relative;
			z-index: 2;
			width: min(720px, 100%);
			max-height: min(92vh, 900px);
			display: flex;
			flex-direction: column;
			background: #fff;
			border-radius: 18px 18px 0 0;
			box-shadow: 0 -12px 40px rgba(15, 23, 42, .2);
			animation: ezpModalIn .28s cubic-bezier(.22,1,.36,1);
			overflow: hidden;
			pointer-events: auto;
			/* stay visible on hover */
			opacity: 1 !important;
			visibility: visible !important;
		}
		@keyframes ezpModalIn {
			from { transform: translateY(24px); opacity: .6; }
			to { transform: translateY(0); opacity: 1; }
		}
		@media (min-width: 768px) {
			.ezp-pdp-modal {
				align-items: center;
				padding: 24px;
			}
			.ezp-pdp-modal__panel {
				border-radius: 18px;
				max-height: min(88vh, 860px);
			}
		}
		.ezp-pdp-modal__head {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			padding: 14px 16px;
			border-bottom: 1px solid #e2e8f0;
			background: linear-gradient(135deg, #eff6ff, #f8fafc);
			flex-shrink: 0;
		}
		.ezp-pdp-modal__title {
			display: flex;
			align-items: center;
			gap: 8px;
			margin: 0;
			font-size: 15px;
			font-weight: 700;
			color: #0f172a;
		}
		.ezp-pdp-modal__title svg { width: 18px; height: 18px; color: #2563eb; flex-shrink: 0; }
		.ezp-pdp-modal__close {
			width: 36px; height: 36px;
			border: 0;
			border-radius: 10px;
			background: #fff;
			color: #475569;
			cursor: pointer;
			display: grid;
			place-items: center;
			box-shadow: 0 1px 3px rgba(15,23,42,.08);
		}
		.ezp-pdp-modal__close:hover { background: #f1f5f9; color: #0f172a; }
		.ezp-pdp-modal__close svg { width: 18px; height: 18px; }
		.ezp-pdp-modal__body {
			padding: 14px 16px;
			overflow: auto;
			-webkit-overflow-scrolling: touch;
			flex: 1;
			min-height: 0;
		}
		.ezp-pdp-modal__foot {
			padding: 12px 16px 16px;
			border-top: 1px solid #e2e8f0;
			background: #fafbfc;
			flex-shrink: 0;
		}
		.ezp-pdp-modal__done {
			width: 100%;
			padding: 12px 16px;
			border: 0;
			border-radius: 12px;
			background: linear-gradient(135deg, #2563eb, #1d4ed8);
			color: #fff;
			font-size: 14px;
			font-weight: 700;
			cursor: pointer;
			box-shadow: 0 6px 16px rgba(37, 99, 235, .28);
		}
		.ezp-pdp-modal__done:hover { filter: brightness(1.05); }
		body.ezp-pdp-modal-open { overflow: hidden; }

		.ezp-pdp-options--custom {
			background: transparent;
			border: 0;
			padding: 0;
		}
		.ezp-pdp-options--custom.ezp-pdp-options--inline {
			padding: 0;
		}
		.ezp-pdp-options-custom-wrap {
			width: 100%;
			max-width: 100%;
			min-width: 0;
		}
		.ezp-pdp-field {
			display: flex;
			flex-direction: column;
			gap: 5px;
			min-width: 0;
			max-width: 100%;
			width: 100%;
			box-sizing: border-box;
		}
		.ezp-pdp-field label {
			font-size: 12px; font-weight: 700;
			color: var(--text-soft);
			min-width: 0;
			overflow-wrap: anywhere;
			word-break: break-word;
		}
		.ezp-pdp-required { color: var(--danger); margin-right: 2px; }

		.ezp-pdp-field input[type="text"],
		.ezp-pdp-field input[type="number"],
		.ezp-pdp-field input[type="email"],
		.ezp-pdp-field input[type="tel"],
		.ezp-pdp-field input[type="date"],
		.ezp-pdp-field input[type="time"],
		.ezp-pdp-field select,
		.ezp-pdp-field textarea {
			width: 100%;
			max-width: 100%;
			min-width: 0;
			box-sizing: border-box;
			min-height: 42px;
			padding: 8px 12px;
			border: 1px solid #dbe2ea;
			border-radius: 10px;
			background: #fff;
			color: var(--text);
			font-family: inherit;
			font-size: 14px;
			outline: none;
			transition: border-color .2s, box-shadow .2s;
		}
		.ezp-pdp-field input[type="color"] {
			width: 100%;
			max-width: 100%;
			min-width: 0;
			box-sizing: border-box;
			min-height: 42px;
			padding: 4px;
			border: 1px solid #dbe2ea;
			border-radius: 10px;
			background: #fff;
			cursor: pointer;
			outline: none;
		}
		.ezp-pdp-field input:focus,
		.ezp-pdp-field select:focus,
		.ezp-pdp-field textarea:focus {
			border-color: var(--primary);
			box-shadow: 0 0 0 3px rgba(7, 27, 122, .08);
		}
		.ezp-pdp-field textarea { min-height: 90px; resize: vertical; }

		.ezp-pdp-choice-group {
			display: flex; flex-wrap: wrap; gap: 8px;
		}
		.ezp-pdp-choice {
			display: inline-flex; align-items: center; gap: 8px;
			padding: 8px 14px;
			background: #fff;
			border: 1px solid #dbe2ea;
			border-radius: 10px;
			cursor: pointer;
			font-size: 13px;
			transition: all .2s;
		}
		.ezp-pdp-choice:hover {
			border-color: var(--primary);
			box-shadow: 0 0 0 3px rgba(7, 27, 122, .06);
		}
		.ezp-pdp-choice input { margin: 0; accent-color: var(--primary); }
		.ezp-pdp-choice span { font-weight: 600; color: var(--text-soft); }

		.ezp-pdp-field input[type="file"] {
			position: absolute !important;
			width: 1px !important;
			height: 1px !important;
			opacity: 0 !important;
			overflow: hidden !important;
			pointer-events: none !important;
		}
		.ezp-pdp-upload-box {
			position: relative;
			display: flex; align-items: center; gap: 10px;
			width: 100%;
			max-width: 100%;
			min-width: 0;
			box-sizing: border-box;
			min-height: 76px;
			padding: 12px 14px;
			border: 1px dashed #cbd5e1;
			border-radius: 14px;
			background: linear-gradient(180deg, #fff, #f8fafc);
			cursor: pointer;
			transition: border-color .2s, background .2s, box-shadow .2s, transform .2s;
			overflow: hidden;
		}
		.ezp-pdp-upload-box:hover,
		.ezp-pdp-upload-box:focus-within {
			border-color: var(--primary);
			background: #fff;
			box-shadow: 0 10px 24px -18px rgba(3, 31, 138, .35);
			transform: translateY(-1px);
		}
		.ezp-pdp-upload-box.is-selected {
			border-style: solid;
			border-color: #10b981;
			background: #f0fdf4;
		}
		.ezp-pdp-upload-box__icon {
			width: 42px; height: 42px;
			border-radius: 12px;
			display: inline-flex; align-items: center; justify-content: center;
			flex: 0 0 42px;
			background: #eef2ff;
			color: var(--primary);
		}
		.ezp-pdp-upload-box__icon svg,
		.ezp-pdp-upload-box__arrow svg {
			width: 20px; height: 20px;
			fill: none; stroke: currentColor;
			stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;
		}
		.ezp-pdp-upload-box__content {
			display: flex; flex-direction: column; gap: 3px;
			min-width: 0; flex: 1;
		}
		.ezp-pdp-upload-box__content strong {
			font-size: 12px; font-weight: 800; color: var(--text);
		}
		.ezp-pdp-upload-box__name {
			font-size: 10px; color: var(--muted);
			white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
		}
		.ezp-pdp-upload-box__arrow {
			display: inline-flex; align-items: center; justify-content: center;
			width: 30px; height: 30px;
			border-radius: 9px;
			background: #f1f5f9;
			color: #64748b;
			flex: 0 0 30px;
			transform: rotate(180deg);
		}
		.ezp-pdp-upload-box.is-selected .ezp-pdp-upload-box__icon {
			background: #dcfce7; color: #059669;
		}
		.ezp-pdp-upload-box.is-selected .ezp-pdp-upload-box__name {
			color: #047857; font-weight: 700;
		}
		.ezp-pdp-field-help {
			display: block;
			margin-top: 4px;
			color: var(--muted);
			font-size: 10px;
		}

		/* ============================================================
		   Live preview — Desktop (default, always visible)
		   ============================================================ */
		.ezp-pdp-live {
			margin-top: 14px;
			padding-top: 12px;
			border-top: 1px dashed #dbe2ea;
		}
		.ezp-pdp-live-title {
			display: flex; align-items: center; gap: 6px;
			font-size: 12px; font-weight: 700;
			color: var(--muted);
			margin-bottom: 8px;
		}
		.ezp-pdp-live-title svg {
			width: 14px; height: 14px;
			fill: none; stroke: currentColor; stroke-width: 2;
			stroke-linecap: round; stroke-linejoin: round;
		}
		.ezp-pdp-live-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(0, 1fr));
			gap: 6px;
			width: 100%;
			max-width: 100%;
			box-sizing: border-box;
		}
		@media (min-width: 480px) {
			.ezp-pdp-live-grid {
				grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
			}
		}
		.ezp-pdp-live-item {
			display: flex; flex-direction: column; gap: 2px;
			padding: 6px 10px;
			background: #fff;
			border: 1px solid var(--border);
			border-radius: 8px;
			font-size: 12px;
			transition: all .25s cubic-bezier(.4,0,.2,1);
			min-width: 0;
			max-width: 100%;
			box-sizing: border-box;
			overflow-wrap: anywhere;
			word-break: break-word;
		}
		.ezp-pdp-live-item.has-value {
			border-color: #bbf7d0;
			background: #f0fdf4;
			transform: translateY(-1px);
			box-shadow: 0 2px 6px rgba(5, 150, 105, .08);
		}
		.ezp-pdp-live-label {
			color: var(--muted);
			font-size: 10px;
			font-weight: 600;
		}
		.ezp-pdp-live-value {
			font-weight: 700;
			color: var(--primary);
			font-size: 13px;
			min-height: 16px;
		}
		.ezp-pdp-live-value.empty {
			color: #cbd5e1;
			font-weight: 400;
		}
		.ezp-pdp-live-item.has-value .ezp-pdp-live-value { color: var(--success-dark); }

		.ezp-pdp-live-toggle { display: none; }
		.ezp-pdp-live-body { display: block; }

		/* ============================================================
		   Live preview — Mobile Accordion (≤ 768px)
		   ============================================================ */
		@media (max-width: 768px) {

			.ezp-pdp-live {
				margin-top: 14px;
				padding: 0;
				border-top: 0;
				border: 1px solid var(--border);
				border-radius: 12px;
				background: #fff;
				overflow: hidden;
				transition: border-color .25s ease, box-shadow .25s ease;
			}

			.ezp-pdp-live.is-open {
				border-color: #c7d2fe;
				box-shadow: 0 6px 18px rgba(7, 27, 122, .06);
			}

			.ezp-pdp-live-title {
				display: none;
			}

			.ezp-pdp-live-toggle {
				display: flex;
				align-items: center;
				gap: 8px;
				width: 100%;
				padding: 12px 14px;
				border: 0;
				border-radius: 0;
				background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
				font-family: inherit;
				font-size: 13px;
				font-weight: 800;
				color: var(--primary);
				cursor: pointer;
				text-align: right;
				transition: background .2s ease, color .2s ease;
				-webkit-tap-highlight-color: transparent;
				outline: none;
				box-sizing: border-box;
			}
			.ezp-pdp-live-toggle:hover,
			.ezp-pdp-live-toggle:focus-visible {
				background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
				color: var(--primary-dark);
			}
			.ezp-pdp-live.is-open .ezp-pdp-live-toggle {
				border-bottom: 1px solid #e0e7ff;
			}

			.ezp-pdp-live-toggle-icon {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				width: 28px;
				height: 28px;
				flex: 0 0 28px;
				border-radius: 8px;
				background: #ffffff;
				color: var(--primary);
				box-shadow: 0 1px 3px rgba(7, 27, 122, .08);
			}
			.ezp-pdp-live-toggle-icon svg {
				width: 15px;
				height: 15px;
				fill: none;
				stroke: currentColor;
				stroke-width: 2;
				stroke-linecap: round;
				stroke-linejoin: round;
				display: block;
			}

			.ezp-pdp-live-toggle-text {
				flex: 1;
				min-width: 0;
				white-space: nowrap;
				overflow: hidden;
				text-overflow: ellipsis;
			}

			.ezp-pdp-live-toggle-count {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				min-width: 22px;
				height: 22px;
				padding: 0 7px;
				border-radius: 999px;
				background: #dcfce7;
				color: #065f46;
				font-size: 11px;
				font-weight: 800;
				flex: 0 0 auto;
				transition: background .2s, color .2s;
			}
			.ezp-pdp-live-toggle-count.is-empty {
				background: #f1f5f9;
				color: #94a3b8;
			}

			.ezp-pdp-live-toggle-caret {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				width: 20px;
				height: 20px;
				flex: 0 0 20px;
				color: var(--muted);
				transition: transform .35s cubic-bezier(.4, 0, .2, 1);
			}
			.ezp-pdp-live-toggle-caret svg {
				width: 14px;
				height: 14px;
				fill: none;
				stroke: currentColor;
				stroke-width: 2.5;
				stroke-linecap: round;
				stroke-linejoin: round;
				display: block;
			}
			.ezp-pdp-live.is-open .ezp-pdp-live-toggle-caret {
				transform: rotate(180deg);
				color: var(--primary);
			}

			.ezp-pdp-live-body {
				max-height: 0;
				overflow: hidden;
				transition: max-height .4s cubic-bezier(.4, 0, .2, 1),
							padding .3s ease;
				padding: 0 10px;
			}
			.ezp-pdp-live.is-open .ezp-pdp-live-body {
				max-height: 1400px;
				padding: 10px;
			}

			.ezp-pdp-live-grid {
				grid-template-columns: 1fr;
				gap: 6px;
			}

			.ezp-pdp-live-item {
				padding: 8px 12px;
			}
			.ezp-pdp-live-label {
				font-size: 10.5px;
			}
			.ezp-pdp-live-value {
				font-size: 13px;
			}
		}

		/* Actions */
		.ezp-pdp-cart-form { display: contents; }

		.ezp-pdp-actions {
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
			align-items: stretch;
			margin-top: 6px;
		}

		/* Quantity */
		.ezp-pdp .ezp-pdp-qty {
			display: inline-flex !important;
			align-items: stretch !important;
			height: 50px !important;
			padding: 0 !important;
			margin: 0 !important;
			background: #ffffff !important;
			border: 2px solid #e5e9f0 !important;
			border-radius: 12px !important;
			overflow: hidden !important;
			transition: border-color .25s ease, box-shadow .25s ease !important;
			flex: 0 0 auto !important;
			box-shadow: 0 1px 2px rgba(15, 23, 42, .02) !important;
			vertical-align: middle !important;
		}
		.ezp-pdp .ezp-pdp-qty:focus-within {
			border-color: #071b7a !important;
			box-shadow: 0 0 0 3px rgba(7, 27, 122, .12) !important;
		}
		.ezp-pdp .ezp-pdp-qty-btn {
			width: 44px !important;
			min-width: 44px !important;
			max-width: 44px !important;
			height: 100% !important;
			padding: 0 !important;
			margin: 0 !important;
			border: 0 !important;
			border-radius: 0 !important;
			background: #f8fafc !important;
			color: #334155 !important;
			cursor: pointer !important;
			display: inline-flex !important;
			align-items: center !important;
			justify-content: center !important;
			flex: 0 0 44px !important;
			transition: background .2s ease, color .2s ease, transform .15s ease !important;
			-webkit-tap-highlight-color: transparent !important;
			outline: none !important;
			box-shadow: none !important;
			font-size: 0 !important;
			line-height: 0 !important;
			text-shadow: none !important;
			text-indent: 0 !important;
			appearance: none !important;
			-webkit-appearance: none !important;
		}
		.ezp-pdp .ezp-pdp-qty-btn svg {
			width: 16px !important;
			height: 16px !important;
			display: block !important;
			pointer-events: none !important;
			fill: none !important;
			stroke: currentColor !important;
			stroke-width: 2.5 !important;
			stroke-linecap: round !important;
			stroke-linejoin: round !important;
			overflow: visible !important;
			color: inherit !important;
		}
		.ezp-pdp .ezp-pdp-qty-btn svg line {
			stroke: currentColor !important;
			stroke-width: 2.5 !important;
		}
		.ezp-pdp .ezp-pdp-qty-btn:hover {
			background: #071b7a !important;
			color: #ffffff !important;
			border-color: transparent !important;
		}
		.ezp-pdp .ezp-pdp-qty-btn:active {
			transform: scale(.94) !important;
			background: #041252 !important;
			color: #ffffff !important;
		}
		.ezp-pdp .ezp-pdp-qty-minus { border-right: 1px solid #e5e9f0 !important; }
		.ezp-pdp .ezp-pdp-qty-plus  { border-left: 1px solid #e5e9f0 !important; }

		.ezp-pdp .ezp-pdp-qty-input,
		.ezp-pdp .ezp-pdp-qty-input[type="number"] {
			width: 60px !important;
			min-width: 60px !important;
			max-width: 60px !important;
			height: 100% !important;
			padding: 0 !important;
			margin: 0 !important;
			border: 0 !important;
			border-radius: 0 !important;
			outline: 0 !important;
			background: #ffffff !important;
			box-shadow: none !important;
			color: #0f172a !important;
			font-family: inherit !important;
			font-size: 16px !important;
			font-weight: 800 !important;
			text-align: center !important;
			-moz-appearance: textfield !important;
			appearance: textfield !important;
			-webkit-appearance: textfield !important;
			flex: 0 0 60px !important;
			line-height: 1 !important;
			vertical-align: middle !important;
			text-indent: 0 !important;
		}
		.ezp-pdp .ezp-pdp-qty-input:focus {
			background: #f8fafc !important;
			box-shadow: none !important;
			border: 0 !important;
			outline: 0 !important;
		}
		.ezp-pdp .ezp-pdp-qty-input::-webkit-outer-spin-button,
		.ezp-pdp .ezp-pdp-qty-input::-webkit-inner-spin-button {
			-webkit-appearance: none !important;
			appearance: none !important;
			margin: 0 !important;
			display: none !important;
		}

		.ezp-pdp-btn {
			position: relative;
			overflow: hidden;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			min-height: 50px;
			padding: 0 26px;
			border: 0;
			border-radius: 12px;
			font-family: inherit;
			font-size: 14px;
			font-weight: 700;
			cursor: pointer;
			transition: transform .2s cubic-bezier(.4,0,.2,1), box-shadow .25s ease, background .25s ease;
			-webkit-tap-highlight-color: transparent;
			flex: 1 1 auto;
			min-width: 160px;
			white-space: nowrap !important;
			line-height: 1 !important;
			text-align: center;
		}
		.ezp-pdp-btn span {
			white-space: nowrap !important;
			display: inline-block;
		}
		.ezp-pdp-btn::before {
			content: "";
			position: absolute;
			top: 0; left: -100%;
			width: 100%; height: 100%;
			background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,.4) 50%, transparent 70%);
			transition: left .7s ease;
			pointer-events: none;
		}
		.ezp-pdp-btn:hover::before { left: 100%; }
		.ezp-pdp-btn:active { transform: scale(.98); }
		.ezp-pdp-btn svg {
			width: 18px; height: 18px;
			fill: none; stroke: currentColor;
			stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
			flex-shrink: 0;
		}
		.ezp-pdp-btn:disabled { opacity: .7; cursor: not-allowed; }

		.ezp-pdp-btn-add {
			background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
			color: #fff;
			box-shadow: 0 6px 20px rgba(7, 27, 122, .28);
		}
		.ezp-pdp-btn-add:hover {
			background: linear-gradient(135deg, var(--primary-dark) 0%, #020b3a 100%);
			color: #fff;
			transform: translateY(-2px);
			box-shadow: 0 10px 30px rgba(7, 27, 122, .45);
		}
		.ezp-pdp-btn-buy {
			background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
			color: #fff;
			box-shadow: 0 6px 20px rgba(225, 29, 72, .3);
			animation: ezpBtnPulse 2.5s ease-in-out infinite;
		}
		.ezp-pdp-btn-buy:hover {
			background: linear-gradient(135deg, var(--accent-dark) 0%, #9f1239 100%);
			color: #fff;
			transform: translateY(-2px);
			box-shadow: 0 10px 30px rgba(225, 29, 72, .55);
			animation-play-state: paused;
		}
		@keyframes ezpBtnPulse {
			0%, 100% { box-shadow: 0 6px 20px rgba(225, 29, 72, .3), 0 0 0 0 rgba(225, 29, 72, .5); }
			50% { box-shadow: 0 6px 20px rgba(225, 29, 72, .3), 0 0 0 10px rgba(225, 29, 72, 0); }
		}

		.ezp-pdp-btn.is-loading svg { display: none; }
		.ezp-pdp-btn.is-loading::after {
			content: "";
			width: 18px; height: 18px;
			border: 2px solid rgba(255,255,255,.35);
			border-top-color: #fff;
			border-radius: 50%;
			animation: ezpSpin .7s linear infinite;
		}
		@keyframes ezpSpin { to { transform: rotate(360deg); } }

		.ezp-pdp-btn.is-success {
			background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%) !important;
			color: #fff !important;
			box-shadow: 0 6px 20px rgba(5, 150, 105, .45) !important;
			animation: ezpSuccessBounce .5s cubic-bezier(.34, 1.56, .64, 1);
		}
		@keyframes ezpSuccessBounce {
			0% { transform: scale(1); }
			40% { transform: scale(1.06); }
			100% { transform: scale(1); }
		}

		.ezp-pdp-ripple {
			position: absolute;
			border-radius: 50%;
			background: rgba(255,255,255,.55);
			transform: scale(0);
			animation: ezpRipple .7s cubic-bezier(.4,0,.2,1);
			pointer-events: none;
		}
		@keyframes ezpRipple {
			to { transform: scale(4); opacity: 0; }
		}

		@media (max-width: 600px) {
			.ezp-pdp-actions {
				flex-direction: column;
				align-items: stretch;
			}
			.ezp-pdp .ezp-pdp-qty {
				width: 100% !important;
				justify-content: space-between !important;
			}
			.ezp-pdp .ezp-pdp-qty-btn { width: 52px !important; min-width: 52px !important; max-width: 52px !important; flex: 0 0 52px !important; }
			.ezp-pdp .ezp-pdp-qty-input,
			.ezp-pdp .ezp-pdp-qty-input[type="number"] {
				flex: 1 1 auto !important;
				width: auto !important;
				min-width: 0 !important;
				max-width: none !important;
			}
			.ezp-pdp-btn {
				width: 100%;
				min-width: 0;
			}
		}

		.ezp-pdp-social-proof {
			display: flex; align-items: center; gap: 8px;
			margin-top: 14px;
			padding: 10px 14px;
			background: linear-gradient(135deg, #ecfdf5, #d1fae5);
			border: 1px solid #a7f3d0;
			border-radius: 10px;
			font-size: 12px; font-weight: 600;
			color: #065f46;
		}
		.ezp-pdp-social-proof svg {
			width: 16px; height: 16px;
			fill: none; stroke: currentColor; stroke-width: 2;
			stroke-linecap: round; stroke-linejoin: round;
			flex-shrink: 0;
		}

		/* Trust Badges */
		.ezp-pdp-trust {
			margin-top: 24px;
			display: grid;
			grid-template-columns: repeat(3, minmax(0, 1fr)) 1.2fr;
			gap: 12px;
		}
		@media (max-width: 900px) {
			.ezp-pdp-trust { grid-template-columns: repeat(2, minmax(0, 1fr)); }
		}
		@media (max-width: 500px) {
			.ezp-pdp-trust { grid-template-columns: 1fr; }
		}

		.ezp-pdp-trust-item {
			position: relative;
			display: flex;
			align-items: center;
			gap: 12px;
			padding: 16px;
			border-radius: 16px;
			border: 1px solid transparent;
			overflow: hidden;
			transition: all .35s cubic-bezier(.34, 1.26, .64, 1);
			background: #fff;
		}
		.ezp-pdp-trust-item::before {
			content: "";
			position: absolute;
			inset: 0;
			opacity: 0;
			transition: opacity .35s ease;
			background: linear-gradient(135deg, rgba(255,255,255,.6) 0%, rgba(255,255,255,0) 50%);
			pointer-events: none;
		}
		.ezp-pdp-trust-item:hover { transform: translateY(-4px); }
		.ezp-pdp-trust-item:hover::before { opacity: 1; }

		.ezp-pdp-trust-icon {
			width: 48px;
			height: 48px;
			flex: 0 0 48px;
			display: flex;
			align-items: center;
			justify-content: center;
			border-radius: 14px;
			background: #fff;
			box-shadow: 0 4px 12px rgba(15, 23, 42, .08);
			position: relative;
			z-index: 1;
		}
		.ezp-pdp-trust-icon svg {
			width: 24px;
			height: 24px;
			fill: none;
			stroke-width: 2;
			stroke-linecap: round;
			stroke-linejoin: round;
			display: block;
		}
		.ezp-pdp-trust-text {
			display: flex;
			flex-direction: column;
			gap: 2px;
			min-width: 0;
			position: relative;
			z-index: 1;
		}
		.ezp-pdp-trust-title {
			font-size: 13.5px;
			font-weight: 800;
			line-height: 1.4;
		}
		.ezp-pdp-trust-sub {
			font-size: 11px;
			font-weight: 600;
			opacity: .75;
			line-height: 1.5;
		}

		.ezp-pdp-trust-delivery {
			background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 60%, #bfdbfe 100%);
			border-color: rgba(59, 130, 246, .25);
			box-shadow: 0 4px 16px rgba(59, 130, 246, .08);
		}
		.ezp-pdp-trust-delivery .ezp-pdp-trust-title { color: #1e3a8a; }
		.ezp-pdp-trust-delivery .ezp-pdp-trust-icon svg { stroke: #2563eb; }
		.ezp-pdp-trust-delivery:hover { box-shadow: 0 12px 28px rgba(59, 130, 246, .22); border-color: rgba(59, 130, 246, .5); }

		.ezp-pdp-trust-shield {
			background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 60%, #a7f3d0 100%);
			border-color: rgba(16, 185, 129, .25);
			box-shadow: 0 4px 16px rgba(16, 185, 129, .08);
		}
		.ezp-pdp-trust-shield .ezp-pdp-trust-title { color: #065f46; }
		.ezp-pdp-trust-shield .ezp-pdp-trust-icon svg { stroke: #059669; }
		.ezp-pdp-trust-shield:hover { box-shadow: 0 12px 28px rgba(16, 185, 129, .22); border-color: rgba(16, 185, 129, .5); }

		.ezp-pdp-trust-support {
			background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 60%, #e9d5ff 100%);
			border-color: rgba(168, 85, 247, .25);
			box-shadow: 0 4px 16px rgba(168, 85, 247, .08);
		}
		.ezp-pdp-trust-support .ezp-pdp-trust-title { color: #6b21a8; }
		.ezp-pdp-trust-support .ezp-pdp-trust-icon svg { stroke: #9333ea; }
		.ezp-pdp-trust-support:hover { box-shadow: 0 12px 28px rgba(168, 85, 247, .22); border-color: rgba(168, 85, 247, .5); }

		.ezp-pdp-trust-share {
			background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 60%, #fed7aa 100%);
			border-color: rgba(249, 115, 22, .25);
			box-shadow: 0 4px 16px rgba(249, 115, 22, .08);
			justify-content: space-between;
			gap: 10px;
		}
		.ezp-pdp-trust-share .ezp-pdp-trust-title { color: #9a3412; }
		.ezp-pdp-trust-share .ezp-pdp-trust-icon svg { stroke: #ea580c; }
		.ezp-pdp-trust-share:hover { box-shadow: 0 12px 28px rgba(249, 115, 22, .22); border-color: rgba(249, 115, 22, .5); }
		.ezp-pdp-trust-share .ezp-pdp-trust-text { flex: 1; }

		.ezp-pdp-trust-socials {
			display: flex;
			gap: 6px;
			flex-shrink: 0;
			position: relative;
			z-index: 1;
		}
		.ezp-pdp-trust-socials a {
			width: 34px;
			height: 34px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			border-radius: 10px;
			background: #ffffff;
			color: #ea580c;
			box-shadow: 0 2px 8px rgba(15, 23, 42, .06);
			transition: all .25s cubic-bezier(.34, 1.26, .64, 1);
			border: 1px solid rgba(249, 115, 22, .15);
		}
		.ezp-pdp-trust-socials a:hover { transform: translateY(-2px) scale(1.06); box-shadow: 0 6px 16px rgba(249, 115, 22, .25); }
		.ezp-pdp-trust-socials a.ezp-whatsapp:hover { background: linear-gradient(135deg, #25d366, #128c7e); border-color: transparent; color: #fff; }
		.ezp-pdp-trust-socials a.ezp-telegram:hover { background: linear-gradient(135deg, #0088cc, #006699); border-color: transparent; color: #fff; }
		.ezp-pdp-trust-socials a.ezp-generic-share:hover { background: linear-gradient(135deg, #ea580c, #c2410c); border-color: transparent; color: #fff; }
		.ezp-pdp-trust-socials svg {
			width: 16px;
			height: 16px;
			fill: none;
			stroke: currentColor;
			stroke-width: 2;
			stroke-linecap: round;
			stroke-linejoin: round;
		}

		/* Tabs */
		.ezp-pdp-tabs {
			margin-top: 22px;
			background: #fff;
			border: 1px solid var(--border);
			border-radius: 18px;
			overflow: hidden;
		}
		.ezp-pdp-tabs-nav {
			display: flex; gap: 4px;
			padding: 8px;
			background: #fafbfc;
			border-bottom: 1px solid var(--border);
			overflow-x: auto;
		}
		.ezp-pdp-tab-btn {
			flex: 0 0 auto;
			padding: 10px 20px;
			border: 0; border-radius: 8px;
			background: transparent;
			font-family: inherit;
			font-size: 13px; font-weight: 700;
			color: var(--muted);
			cursor: pointer;
			transition: all .25s cubic-bezier(.4,0,.2,1);
		}
		.ezp-pdp-tab-btn:hover { background: #fff; color: var(--text); }
		.ezp-pdp-tab-btn.active {
			background: #fff;
			color: var(--primary);
			box-shadow: 0 3px 12px rgba(15, 23, 42, .06);
		}
		.ezp-pdp-tab-panel {
			display: none;
			padding: 24px 28px;
			animation: ezpFadeIn .3s ease;
		}
		.ezp-pdp-tab-panel.active { display: block; }
		@keyframes ezpFadeIn {
			from { opacity: 0; transform: translateY(6px); }
			to { opacity: 1; transform: translateY(0); }
		}

		/* Specs */
		.ezp-pdp-specs {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 8px;
			width: 100%;
			max-width: 100%;
			box-sizing: border-box;
		}
		.ezp-pdp-spec {
			display: flex;
			align-items: flex-start;
			justify-content: space-between;
			gap: 10px;
			padding: 12px 14px;
			background: var(--soft);
			border-radius: 10px;
			font-size: 13px;
			transition: all .25s cubic-bezier(.4,0,.2,1);
			min-width: 0;
			max-width: 100%;
			box-sizing: border-box;
			overflow-wrap: anywhere;
			word-break: break-word;
		}
		.ezp-pdp-spec:hover { background: #eef2ff; transform: translateX(-2px); }
		.ezp-pdp-spec-label {
			color: var(--muted);
			font-weight: 600;
			flex: 0 1 auto;
			min-width: 0;
			max-width: 48%;
			overflow-wrap: anywhere;
			word-break: break-word;
		}
		.ezp-pdp-spec-value {
			font-weight: 700;
			color: var(--text);
			text-align: left;
			flex: 1 1 auto;
			min-width: 0;
			overflow-wrap: anywhere;
			word-break: break-word;
		}
		@media (max-width: 650px) {
			.ezp-pdp-specs { grid-template-columns: 1fr; gap: 8px; }
			.ezp-pdp-spec {
				flex-direction: column;
				align-items: stretch;
				gap: 4px;
				padding: 12px;
			}
			.ezp-pdp-spec:hover { transform: none; }
			.ezp-pdp-spec-label,
			.ezp-pdp-spec-value {
				max-width: 100%;
				text-align: right;
			}
			.ezp-pdp-spec-value { font-size: 13px; color: var(--text); }
		}

		/* Reviews */
		.ezp-pdp-reviews-summary {
			display: grid;
			grid-template-columns: 180px 1fr;
			gap: 24px;
			margin-bottom: 22px;
		}
		@media (max-width: 650px) { .ezp-pdp-reviews-summary { grid-template-columns: 1fr; } }
		.ezp-pdp-score {
			text-align: center;
			padding: 20px;
			background: var(--soft);
			border-radius: 14px;
		}
		.ezp-pdp-score-number {
			font-size: 42px;
			font-weight: 800;
			line-height: 1;
			color: var(--primary);
		}
		.ezp-pdp-score-stars { margin: 8px 0; color: #f59e0b; font-size: 17px; }
		.ezp-pdp-rating-bar {
			display: grid;
			grid-template-columns: 24px 1fr 32px;
			align-items: center;
			gap: 8px;
			margin: 6px 0;
			font-size: 12px;
		}
		.ezp-pdp-rating-track {
			height: 7px;
			overflow: hidden;
			background: #e8edf4;
			border-radius: 20px;
		}
		.ezp-pdp-rating-fill {
			height: 100%;
			background: linear-gradient(90deg, #f59e0b, #fbbf24);
			border-radius: inherit;
			transition: width .8s cubic-bezier(.4,0,.2,1);
		}
		.ezp-pdp-review {
			padding: 16px 0;
			border-bottom: 1px solid var(--border);
		}
		.ezp-pdp-review:last-child { border-bottom: 0; }
		.ezp-pdp-review-head {
			display: flex; align-items: center; justify-content: space-between;
			gap: 10px; margin-bottom: 6px;
		}
		.ezp-pdp-review-author { font-weight: 700; font-size: 13px; }
		.ezp-pdp-review-date { color: var(--muted); font-size: 11px; }
		.ezp-pdp-review-stars { color: #f59e0b; font-size: 13px; }
		.ezp-pdp-review-text { font-size: 13px; color: var(--text-soft); }

		.ezp-pdp-review-form-wrap {
			margin-top: 28px;
			padding: 20px;
			background: #fff;
			border: 1px solid var(--border);
			border-radius: 14px;
		}
		.ezp-pdp-review-form-title { margin: 0 0 4px; font-size: 17px; font-weight: 800; }
		.ezp-pdp-review-form-note { margin: 0 0 18px; color: var(--muted); font-size: 12px; }
		.ezp-pdp-review-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 12px; }
		.ezp-pdp-review-field { margin-bottom: 13px; }
		.ezp-pdp-review-field label { display:block; margin-bottom:6px; font-size:12px; font-weight:700; }
		.ezp-pdp-review-field label span { color:var(--danger); }
		.ezp-pdp-review-field input[type="text"],
		.ezp-pdp-review-field input[type="email"],
		.ezp-pdp-review-field textarea {
			width:100%; border:1px solid #dbe2ea; border-radius:10px; background:#fff;
			padding:10px 12px; font-family:inherit; font-size:13px; outline:none;
			transition:border-color .2s, box-shadow .2s;
		}
		.ezp-pdp-review-field textarea { min-height:110px; resize:vertical; }
		.ezp-pdp-review-field input:focus,
		.ezp-pdp-review-field textarea:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(7,27,122,.08); }
		.ezp-pdp-review-stars-input { display:flex; flex-direction:row; gap:5px; direction:rtl; }
		.ezp-pdp-review-stars-input label { margin:0; cursor:pointer; }
		.ezp-pdp-review-stars-input input { position:absolute; opacity:0; pointer-events:none; }
		.ezp-pdp-review-stars-input span {
			display:block; padding:6px 8px; border:1px solid var(--border); border-radius:8px;
			color:#f59e0b; font-size:16px; line-height:1; background:#fff;
		}
		.ezp-pdp-review-stars-input input:checked + span { border-color:#f59e0b; background:#fff7ed; }
		.ezp-pdp-review-submit {
			display:inline-flex; align-items:center; justify-content:center; min-height:46px;
			padding:0 24px; border:0; border-radius:11px; background:linear-gradient(135deg,var(--primary),var(--primary-dark));
			color:#fff; font-family:inherit; font-weight:700; cursor:pointer;
		}
		.ezp-pdp-review-submit:disabled { opacity:.65; cursor:not-allowed; }
		.ezp-pdp-review-message { margin-top:10px; font-size:12px; font-weight:600; }
		.ezp-pdp-review-message.success { color:#047857; }
		.ezp-pdp-review-message.error { color:#b91c1c; }
		.ezp-pdp-review-welcome {display:flex;align-items:center;gap:12px;margin-bottom:15px;padding:13px 14px;border:1px solid #e2e8f0;border-radius:14px;background:linear-gradient(180deg,#fff,#f8fafc);}
		.ezp-pdp-review-welcome__icon {width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex:0 0 42px;background:#eef2ff;color:var(--primary);}
		.ezp-pdp-review-welcome__icon svg {width:21px;height:21px;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round;}
		.ezp-pdp-review-welcome strong {display:block;font-size:13px;font-weight:800;color:var(--text);margin-bottom:2px;}
		.ezp-pdp-review-welcome span {display:block;font-size:11px;line-height:1.8;color:var(--muted);}
		.ezp-pdp-review-field input[readonly] {background:#f8fafc;color:#475569;cursor:default;}
		.ezp-pdp-review-hp{position:absolute!important;left:-10000px!important;top:auto!important;width:1px!important;height:1px!important;overflow:hidden!important;opacity:0!important;pointer-events:none!important}
		.ezp-pdp-recaptcha { margin:4px 0 15px; padding:12px; border:1px solid var(--border); border-radius:14px; background:#f8fafc; display:block; min-height:0; overflow:hidden; }
		.ezp-pdp-local-captcha__head { display:flex;align-items:center;gap:9px;margin-bottom:10px; }
		.ezp-pdp-recaptcha__icon { width:32px;height:32px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;background:#fff;border:1px solid #e2e8f0;color:var(--primary);flex:0 0 auto; }
		.ezp-pdp-recaptcha__icon svg { width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round; }
		.ezp-pdp-recaptcha__head-title { font-size:12px;font-weight:800;color:var(--text);line-height:1.4; }
		.ezp-pdp-local-captcha__hint { font-size:10.5px;color:var(--muted);margin:2px 0 0; }
		.ezp-pdp-local-captcha__challenge { display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:9px; }
		.ezp-pdp-local-captcha__code,.ezp-pdp-local-captcha__math { display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 13px;border:1px solid #dbe3ec;border-radius:10px;background:#fff;font-weight:900;letter-spacing:.14em;direction:ltr; }
		.ezp-pdp-local-captcha__code { color:var(--primary);font-size:15px;text-decoration:line-through;text-decoration-thickness:1px; }
		.ezp-pdp-local-captcha__math { color:var(--text);font-size:13px;letter-spacing:.03em; }
		.ezp-pdp-local-captcha__input-wrap input { width:100%;height:42px;border:1px solid #dbe3ec;border-radius:10px;background:#fff;padding:0 12px;font-size:13px;direction:ltr;text-align:left;outline:none;transition:border-color .2s,box-shadow .2s; }
		.ezp-pdp-local-captcha__input-wrap input:focus { border-color:var(--primary);box-shadow:0 0 0 3px rgba(3,31,138,.08); }
		.ezp-pdp-local-captcha__note { font-size:10px;color:var(--muted);margin:6px 2px 0;line-height:1.7; }
		@media (max-width:600px) {
			.ezp-pdp-review-grid { grid-template-columns:1fr; gap:0; }
			.ezp-pdp-review-form-wrap { padding:15px; }
		}

		/* Related Products Slider */
		.ezp-pdp-related {
			margin-top: 26px;
			padding: 0 6px;
		}
		.ezp-pdp-related-head {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			margin-bottom: 16px;
		}
		.ezp-pdp-related-title {
			display: flex;
			align-items: center;
			gap: 8px;
			margin: 0;
			font-size: 18px;
			font-weight: 700;
		}
		.ezp-pdp-related-title svg {
			width: 22px;
			height: 22px;
			fill: none;
			stroke: var(--primary);
			stroke-width: 1.8;
			stroke-linecap: round;
			stroke-linejoin: round;
		}
		.ezp-pdp-related-nav { display: flex; gap: 6px; flex-shrink: 0; }
		.ezp-pdp-related-prev,
		.ezp-pdp-related-next {
			width: 38px;
			height: 38px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			padding: 0;
			border: 1.5px solid var(--border);
			border-radius: 10px;
			background: #fff;
			color: var(--text);
			cursor: pointer;
			transition: all .25s cubic-bezier(.4,0,.2,1);
		}
		.ezp-pdp-related-prev:hover,
		.ezp-pdp-related-next:hover {
			background: linear-gradient(135deg, var(--primary), var(--primary-dark));
			border-color: var(--primary);
			color: #fff;
			transform: translateY(-1px);
			box-shadow: 0 6px 16px rgba(7, 27, 122, .25);
		}
		.ezp-pdp-related-prev:active,
		.ezp-pdp-related-next:active { transform: scale(.94); }
		.ezp-pdp-related-prev:disabled,
		.ezp-pdp-related-next:disabled { opacity: .35; cursor: not-allowed; pointer-events: none; }
		.ezp-pdp-related-prev svg,
		.ezp-pdp-related-next svg {
			width: 18px;
			height: 18px;
			fill: none;
			stroke: currentColor;
			stroke-width: 2.2;
			stroke-linecap: round;
			stroke-linejoin: round;
			display: block;
			pointer-events: none;
		}
		.ezp-pdp-related-track {
			display: flex;
			gap: 14px;
			overflow-x: auto;
			overflow-y: hidden;
			scroll-snap-type: x mandatory;
			scroll-behavior: smooth;
			padding-bottom: 10px;
			-webkit-overflow-scrolling: touch;
			scrollbar-width: none;
			scroll-padding-left: 4px;
		}
		.ezp-pdp-related-track::-webkit-scrollbar { display: none; }
		.ezp-pdp-related-card {
			flex: 0 0 calc(25% - 10.5px);
			scroll-snap-align: start;
			display: block;
			background: #fff;
			border: 1px solid var(--border);
			border-radius: 14px;
			overflow: hidden;
			transition: all .35s cubic-bezier(.34, 1.26, .64, 1);
			animation: ezpCardFadeUp .5s cubic-bezier(.34, 1.26, .64, 1) both;
		}
		.ezp-pdp-related-card:nth-child(1) { animation-delay: .05s; }
		.ezp-pdp-related-card:nth-child(2) { animation-delay: .10s; }
		.ezp-pdp-related-card:nth-child(3) { animation-delay: .15s; }
		.ezp-pdp-related-card:nth-child(4) { animation-delay: .20s; }
		.ezp-pdp-related-card:nth-child(5) { animation-delay: .25s; }
		.ezp-pdp-related-card:nth-child(6) { animation-delay: .30s; }
		.ezp-pdp-related-card:nth-child(7) { animation-delay: .35s; }
		.ezp-pdp-related-card:nth-child(8) { animation-delay: .40s; }
		@keyframes ezpCardFadeUp {
			from { opacity: 0; transform: translateY(14px); }
			to { opacity: 1; transform: translateY(0); }
		}
		.ezp-pdp-related-card:hover {
			transform: translateY(-4px);
			box-shadow: 0 12px 32px rgba(15, 23, 42, .1);
			border-color: var(--primary);
		}
		.ezp-pdp-related-img {
			aspect-ratio: 1;
			background: var(--soft);
			overflow: hidden;
		}
		.ezp-pdp-related-img img {
			width: 100%;
			height: 100%;
			object-fit: contain;
			transition: transform .5s cubic-bezier(.34, 1.26, .64, 1);
		}
		.ezp-pdp-related-card:hover .ezp-pdp-related-img img { transform: scale(1.08); }
		.ezp-pdp-related-body { padding: 12px; }
		.ezp-pdp-related-name {
			font-size: 13px;
			font-weight: 700;
			line-height: 1.6;
			margin-bottom: 4px;
			min-height: 42px;
			display: -webkit-box;
			-webkit-line-clamp: 2;
			-webkit-box-orient: vertical;
			overflow: hidden;
		}
		.ezp-pdp-related-price {
			font-size: 13px;
			font-weight: 700;
			color: var(--primary);
		}
		@media (max-width: 900px) {
			.ezp-pdp-related-card { flex: 0 0 calc(33.333% - 9.33px); }
		}
		@media (max-width: 600px) {
			.ezp-pdp-related { padding: 0; }
			.ezp-pdp-related-track { gap: 10px; padding-bottom: 8px; scroll-padding-left: 2px; }
			.ezp-pdp-related-card { flex: 0 0 calc(50% - 5px); border-radius: 12px; }
			.ezp-pdp-related-body { padding: 9px; }
			.ezp-pdp-related-name { font-size: 11.5px; min-height: 36px; line-height: 1.55; }
			.ezp-pdp-related-price { font-size: 12px; }
			.ezp-pdp-related-prev,
			.ezp-pdp-related-next { width: 34px; height: 34px; }
			.ezp-pdp-related-prev svg,
			.ezp-pdp-related-next svg { width: 16px; height: 16px; }
		}
		@media (max-width: 400px) {
			.ezp-pdp-related-card { flex: 0 0 calc(50% - 4px); }
			.ezp-pdp-related-track { gap: 8px; }
		}

		/* Mobile Sticky Bar */
		.ezp-pdp-sticky {
			position: fixed !important;
			bottom: 0 !important;
			left: 0 !important;
			right: 0 !important;
			top: auto !important;
			z-index: 999999 !important;
			display: none;
			align-items: center;
			gap: 10px;
			padding: 10px 12px calc(10px + env(safe-area-inset-bottom, 0px));
			background: linear-gradient(135deg, rgba(255,255,255,.98) 0%, rgba(247,249,252,.98) 100%);
			border-top: 1px solid rgba(7, 27, 122, .08);
			box-shadow: 0 -8px 32px rgba(15, 23, 42, .14);
			backdrop-filter: blur(16px);
			-webkit-backdrop-filter: blur(16px);
			transform: translateY(110%);
			transition: transform .45s cubic-bezier(.34, 1.26, .64, 1);
			will-change: transform;
			box-sizing: border-box;
			margin: 0 !important;
		}
		.ezp-pdp-sticky.show { transform: translateY(0); }
		.ezp-pdp-sticky.hide { transform: translateY(110%) !important; }
		@media (max-width: 768px) {
			body .ezp-pdp-sticky,
			body .ezp-pdp-sticky.ezp-pdp-sticky { display: flex !important; }
			body.ezp-has-pdp-sticky { padding-bottom: 80px !important; }
		}
		.ezp-pdp-sticky-info {
			flex: 1;
			min-width: 0;
			display: flex;
			flex-direction: column;
			gap: 1px;
			line-height: 1.3;
		}
		.ezp-pdp-sticky-title {
			font-size: 11.5px;
			font-weight: 700;
			color: #0f172a !important;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}
		.ezp-pdp-sticky-price,
		.ezp-pdp-sticky-price *,
		.ezp-pdp-sticky-price .amount,
		.ezp-pdp-sticky-price .woocommerce-Price-amount,
		.ezp-pdp-sticky-price bdi {
			font-size: 13px !important;
			font-weight: 800 !important;
			color: #071b7a !important;
			-webkit-text-fill-color: #071b7a !important;
		}
		.ezp-pdp-sticky-btn {
			flex: 0 0 auto;
			min-height: 46px;
			padding: 0 18px;
			border: 0 !important;
			border-radius: 12px;
			background: linear-gradient(135deg, #1d4ed8 0%, #071b7a 48%, #041252 100%) !important;
			color: #ffffff !important;
			-webkit-text-fill-color: #ffffff !important;
			font-family: inherit;
			font-size: 13px;
			font-weight: 800;
			letter-spacing: .2px;
			cursor: pointer;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 7px;
			box-shadow: 0 8px 22px rgba(7, 27, 122, .42);
			transition: all .25s cubic-bezier(.4,0,.2,1);
			white-space: nowrap;
			position: relative;
			overflow: hidden;
			text-shadow: none !important;
		}
		.ezp-pdp-sticky-btn span,
		.ezp-pdp-sticky-btn * {
			color: #ffffff !important;
			-webkit-text-fill-color: #ffffff !important;
		}
		.ezp-pdp-sticky-btn::before {
			content: "";
			position: absolute;
			top: 0; left: -100%;
			width: 100%; height: 100%;
			background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,.35) 50%, transparent 70%);
			transition: left .7s ease;
			pointer-events: none;
		}
		.ezp-pdp-sticky-btn:hover {
			background: linear-gradient(135deg, #2563eb 0%, #0a2bb8 45%, #041252 100%) !important;
			color: #ffffff !important;
			box-shadow: 0 10px 26px rgba(7, 27, 122, .5);
		}
		.ezp-pdp-sticky-btn:hover::before { left: 100%; }
		.ezp-pdp-sticky-btn:active { transform: scale(.96); }
		.ezp-pdp-sticky-btn svg {
			width: 16px; height: 16px;
			fill: none !important;
			stroke: #ffffff !important;
			stroke-width: 2.2;
			stroke-linecap: round;
			stroke-linejoin: round;
			flex: 0 0 auto;
		}
		.ezp-pdp-sticky-close {
			flex: 0 0 auto;
			width: 40px;
			height: 40px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			border: 1.5px solid rgba(225, 29, 72, .2);
			border-radius: 12px;
			background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
			color: #e11d48;
			cursor: pointer;
			transition: all .35s cubic-bezier(.34, 1.56, .64, 1);
			padding: 0;
			margin: 0;
			position: relative;
			overflow: hidden;
		}
		.ezp-pdp-sticky-close::before {
			content: "";
			position: absolute;
			inset: 0;
			background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
			opacity: 0;
			transition: opacity .3s ease;
		}
		.ezp-pdp-sticky-close:hover {
			border-color: transparent;
			color: #fff;
			transform: rotate(90deg) scale(1.08);
			box-shadow: 0 8px 20px rgba(225, 29, 72, .4);
		}
		.ezp-pdp-sticky-close:hover::before { opacity: 1; }
		.ezp-pdp-sticky-close:active { transform: rotate(90deg) scale(.94); }
		.ezp-pdp-sticky-close svg {
			width: 18px; height: 18px;
			fill: none; stroke: currentColor;
			stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round;
			display: block;
			pointer-events: none;
			position: relative;
			z-index: 2;
			transition: stroke .3s ease;
		}
		.ezp-pdp-sticky-close:hover svg { stroke: #fff; }

		@media (max-width: 768px) {
			.ezp-pdp-tabs-nav { display: none; }
			.ezp-pdp-tabs,
			.ezp-pdp-tab-panel {
				overflow-x: hidden;
				max-width: 100%;
				box-sizing: border-box;
			}
			.ezp-pdp-tab-panel {
				display: block !important;
				padding: 18px 14px;
				border-bottom: 1px solid var(--border);
			}
			.ezp-pdp-tab-panel:last-child { border-bottom: 0; }
			.ezp-pdp-tab-panel::before {
				content: attr(data-title);
				display: flex;
				align-items: center;
				gap: 8px;
				font-size: 16px;
				font-weight: 700;
				color: var(--primary);
				margin-bottom: 14px;
				padding-bottom: 10px;
				border-bottom: 2px solid var(--primary);
			}
		}

		@media (max-width: 480px) {
			.ezp-pdp { padding: 10px; overflow-x: hidden; }
			.ezp-pdp-grid { padding: 12px; gap: 18px; max-width: 100%; box-sizing: border-box; }
			.ezp-pdp-title { font-size: 18px; }
			.ezp-pdp-price { font-size: 22px; }
			.ezp-pdp-tab-panel { padding: 16px 14px; }
			.ezp-pdp-options { padding: 10px; }
			.ezp-pdp-options-grid { gap: 8px; }
			.ezp-pdp-upload-box {
				flex-wrap: wrap;
				gap: 8px;
				padding: 10px;
				min-height: 0;
			}
			.ezp-pdp-upload-box__arrow { display: none; }
			.ezp-pdp-upload-box__content strong { font-size: 11px; }
			.ezp-pdp-upload-box__name { white-space: normal; }
			.ezp-pdp-field input[type="text"],
			.ezp-pdp-field input[type="number"],
			.ezp-pdp-field input[type="email"],
			.ezp-pdp-field input[type="tel"],
			.ezp-pdp-field input[type="date"],
			.ezp-pdp-field input[type="time"],
			.ezp-pdp-field select,
			.ezp-pdp-field textarea {
				font-size: 16px;
			}
			.ezp-pdp-actions { gap: 8px; }
			.ezp-pdp .ezp-pdp-qty { height: 48px !important; }
			.ezp-pdp .ezp-pdp-qty-btn { width: 46px !important; min-width: 46px !important; max-width: 46px !important; flex: 0 0 46px !important; }
			.ezp-pdp-btn { min-height: 48px; font-size: 13px; padding: 0 20px; }
			.ezp-pdp-trust-item { padding: 14px; gap: 10px; }
			.ezp-pdp-trust-icon { width: 42px; height: 42px; flex: 0 0 42px; }
			.ezp-pdp-trust-icon svg { width: 20px; height: 20px; }
		}

		.ezp-pdp-error {
			padding: 18px;
			border-radius: 12px;
			background: #fee2e2;
			color: #991b1b;
			text-align: center;
		}
		</style>

		<div class="ezp-pdp-grid">
			<div class="ezp-pdp-gallery">
				<div class="ezp-pdp-main-img">
					<?php if ( $is_on_sale && $discount_pct > 0 ) : ?>
						<span class="ezp-pdp-sale-badge">
							<?php echo ezp_pdp_icon( 'percent', '' ); ?>
							<?php echo esc_html( ezp_pdp_fa( $discount_pct ) ); ?>٪ تخفیف
						</span>
					<?php endif; ?>
					<img class="ezp-pdp-main-image" src="<?php echo esc_url( $images[0]['url'] ); ?>" alt="<?php echo esc_attr( $title ); ?>">
				</div>

				<?php if ( count( $images ) > 1 ) : ?>
					<div class="ezp-pdp-thumbs">
						<?php foreach ( $images as $i => $img ) : ?>
							<button type="button" class="ezp-pdp-thumb <?php echo 0 === $i ? 'active' : ''; ?>" data-image="<?php echo esc_url( $img['url'] ); ?>">
								<img src="<?php echo esc_url( $img['url'] ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="<?php echo 0 === $i ? 'eager' : 'lazy'; ?>">
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php ezp_pdp_render_options_block( $ezlens_slots, 'gallery_side' ); ?>
			</div>

			<div class="ezp-pdp-info">
				<?php if ( ! empty( $categories ) ) : ?>
					<div class="ezp-pdp-breadcrumb">
						<?php echo ezp_pdp_icon( 'home', '' ); ?>
						<span><?php echo esc_html( implode( '، ', $categories ) ); ?></span>
					</div>
				<?php endif; ?>

				<h1 class="ezp-pdp-title"><?php echo esc_html( $title ); ?></h1>

				<?php if ( $review_count > 0 ) : ?>
					<div class="ezp-pdp-rating">
						<span class="ezp-pdp-stars"><?php echo esc_html( str_repeat( '★', (int) round( $average_rating ) ) . str_repeat( '☆', 5 - (int) round( $average_rating ) ) ); ?></span>
						<span class="ezp-pdp-rating-number"><?php echo esc_html( ezp_pdp_fa( number_format_i18n( $average_rating, 1 ) ) ); ?></span>
						<span class="ezp-pdp-rating-count">(<?php echo esc_html( ezp_pdp_fa( $review_count ) ); ?> نظر)</span>
					</div>
				<?php endif; ?>

				<div class="ezp-pdp-price">
					<?php echo wp_kses_post( ezp_pdp_fa( $price ) ); ?>
					<?php if ( $discount_pct > 0 ) : ?>
						<span class="ezp-pdp-discount-tag">
							<?php echo ezp_pdp_icon( 'percent', '' ); ?>
							صرفه‌جویی <?php echo esc_html( ezp_pdp_fa( $discount_pct ) ); ?>٪
						</span>
					<?php endif; ?>
				</div>

				<?php ezp_pdp_render_options_block( $ezlens_slots, 'below_price' ); ?>

				<div class="ezp-pdp-stock <?php echo $in_stock ? ( ( $manage_stock && $stock_qty && $stock_qty <= 5 ) ? 'low' : 'in' ) : 'out'; ?>">
					<?php echo ezp_pdp_icon( $in_stock ? 'check-circle' : 'alert-circle', '' ); ?>
					<?php if ( $in_stock ) : ?>
						موجود در انبار
						<?php if ( $manage_stock && $stock_qty && $stock_qty <= 5 ) : ?>
							— فقط <?php echo esc_html( ezp_pdp_fa( $stock_qty ) ); ?> عدد باقی مانده
						<?php endif; ?>
					<?php else : ?>
						ناموجود
					<?php endif; ?>
				</div>

				<?php if ( $short_desc ) : ?>
					<div class="ezp-pdp-short-desc"><?php echo wp_kses_post( $short_desc ); ?></div>
				<?php endif; ?>

				<?php if ( $remaining_to_free > 0 ) : ?>
					<div class="ezp-pdp-ship">
						<div class="ezp-pdp-ship-top">
							<?php echo ezp_pdp_icon( 'delivery', ezp_pdp_icon( 'truck', '' ) ); ?>
							<span>تا ارسال رایگان
								<span class="ezp-pdp-ship-amount"><?php echo esc_html( ezp_pdp_fa( number_format( $remaining_to_free ) ) ); ?> تومان</span>
								باقی مانده
							</span>
						</div>
						<div class="ezp-pdp-ship-track">
							<div class="ezp-pdp-ship-fill" style="width:<?php echo esc_attr( $free_ship_progress ); ?>%;">
								<span class="ezp-pdp-ship-shine"></span>
							</div>
						</div>
					</div>
				<?php else : ?>
					<div class="ezp-pdp-ship done">
						<div class="ezp-pdp-ship-top">
							<?php echo ezp_pdp_icon( 'check-circle', '' ); ?>
							<span>ارسال رایگان برای این سفارش فعال شد</span>
						</div>
					</div>
				<?php endif; ?>

				<?php ezp_pdp_render_options_block( $ezlens_slots, 'below_summary' ); ?>

				<?php if ( $in_stock ) : ?>
					<form class="ezp-pdp-cart-form" method="post" action="<?php echo esc_url( wc_get_cart_url() ); ?>">
						<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $id ); ?>">
						<input type="hidden" name="product_id" value="<?php echo esc_attr( $id ); ?>">

						<div class="ezp-pdp-actions">
							<div class="ezp-pdp-qty">
								<button type="button" class="ezp-pdp-qty-btn ezp-pdp-qty-minus" aria-label="کاهش تعداد">
									<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
										<line x1="5" y1="12" x2="19" y2="12"/>
									</svg>
								</button>
								<input type="number" class="ezp-pdp-qty-input" name="quantity" value="1" min="1" <?php echo ( $manage_stock && $stock_qty ) ? 'max="' . esc_attr( $stock_qty ) . '"' : ''; ?> inputmode="numeric" aria-label="تعداد">
								<button type="button" class="ezp-pdp-qty-btn ezp-pdp-qty-plus" aria-label="افزایش تعداد">
									<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
										<line x1="12" y1="5" x2="12" y2="19"/>
										<line x1="5" y1="12" x2="19" y2="12"/>
									</svg>
								</button>
							</div>

							<button type="submit" class="ezp-pdp-btn ezp-pdp-btn-add">
								<?php echo ezp_pdp_icon( 'cart', '' ); ?>
								<span>افزودن به سبد خرید</span>
							</button>

							<button type="button" class="ezp-pdp-btn ezp-pdp-btn-buy" data-product="<?php echo esc_attr( $id ); ?>">
								<span>خرید سریع</span>
							</button>
						</div>
					</form>
				<?php endif; ?>

				<div class="ezp-pdp-social-proof">
					<?php echo ezp_pdp_icon( 'check-circle', '' ); ?>
					<span><?php echo esc_html( ezp_pdp_fa( rand( 30, 80 ) ) ); ?> نفر امروز این محصول را خریدند</span>
				</div>
			</div>
		</div>

		<?php ezp_pdp_render_options_block( $ezlens_slots, 'full_width' ); ?>

		<div class="ezp-pdp-trust">
			<div class="ezp-pdp-trust-item ezp-pdp-trust-delivery">
				<div class="ezp-pdp-trust-icon">
					<?php echo ezp_pdp_icon( 'truck', ezp_pdp_icon( 'delivery', '' ) ); ?>
				</div>
				<div class="ezp-pdp-trust-text">
					<span class="ezp-pdp-trust-title">ارسال سریع و مطمئن</span>
					<span class="ezp-pdp-trust-sub">ارسال به سراسر کشور</span>
				</div>
			</div>

			<div class="ezp-pdp-trust-item ezp-pdp-trust-shield">
				<div class="ezp-pdp-trust-icon">
					<?php echo ezp_pdp_icon( 'shield', ezp_pdp_icon( 'shield-check', '' ) ); ?>
				</div>
				<div class="ezp-pdp-trust-text">
					<span class="ezp-pdp-trust-title">تضمین اصالت کالا</span>
					<span class="ezp-pdp-trust-sub">۱۰۰٪ اورجینال</span>
				</div>
			</div>

			<div class="ezp-pdp-trust-item ezp-pdp-trust-support">
				<div class="ezp-pdp-trust-icon">
					<?php echo ezp_pdp_icon( 'support', '' ); ?>
				</div>
				<div class="ezp-pdp-trust-text">
					<span class="ezp-pdp-trust-title">پشتیبانی ایزی‌لنز</span>
					<span class="ezp-pdp-trust-sub">پاسخگویی ۲۴ ساعته</span>
				</div>
			</div>

			<div class="ezp-pdp-trust-item ezp-pdp-trust-share">
				<div class="ezp-pdp-trust-icon">
					<?php echo ezp_pdp_icon( 'share', '' ); ?>
				</div>
				<div class="ezp-pdp-trust-text">
					<span class="ezp-pdp-trust-title">اشتراک‌گذاری</span>
					<span class="ezp-pdp-trust-sub">با دوستان خود به اشتراک بگذارید</span>
				</div>
				<div class="ezp-pdp-trust-socials">
					<a class="ezp-whatsapp" href="https://wa.me/?text=<?php echo rawurlencode( $title . ' ' . $permalink ); ?>" target="_blank" rel="noopener" aria-label="واتساپ">
						<?php echo ezp_pdp_icon( 'whatsapp', '' ); ?>
					</a>
					<a class="ezp-telegram" href="https://t.me/share/url?url=<?php echo rawurlencode( $permalink ); ?>&text=<?php echo rawurlencode( $title ); ?>" target="_blank" rel="noopener" aria-label="تلگرام">
						<?php echo ezp_pdp_icon( 'telegram', '' ); ?>
					</a>
					<a class="ezp-generic-share" href="https://twitter.com/intent/tweet?url=<?php echo rawurlencode( $permalink ); ?>&text=<?php echo rawurlencode( $title ); ?>" target="_blank" rel="noopener" aria-label="اشتراک‌گذاری">
						<?php echo ezp_pdp_icon( 'share', '' ); ?>
					</a>
				</div>
			</div>
		</div>

		<div class="ezp-pdp-tabs">
			<div class="ezp-pdp-tabs-nav">
				<button type="button" class="ezp-pdp-tab-btn active" data-tab="desc">توضیحات</button>
				<button type="button" class="ezp-pdp-tab-btn" data-tab="specs">مشخصات</button>
				<button type="button" class="ezp-pdp-tab-btn" data-tab="reviews">نظرات (<?php echo esc_html( ezp_pdp_fa( $review_count ) ); ?>)</button>
			</div>

			<?php /* DESCRIPTION: خروجی پیش‌فرض ووکامرس */ ?>
			<div class="ezp-pdp-tab-panel active" data-content="desc" data-title="توضیحات محصول">
				<?php
				wc_get_template( 'single-product/tabs/description.php' );
				?>
			</div>

			<div class="ezp-pdp-tab-panel" data-content="specs" data-title="مشخصات فنی">
				<?php
				if ( ! empty( $specs_content ) ) {
					echo wp_kses_post( $specs_content );
				} elseif ( ! empty( $attributes ) ) {
					?>
					<div class="ezp-pdp-specs">
						<?php foreach ( $attributes as $attr ) : ?>
							<div class="ezp-pdp-spec">
								<span class="ezp-pdp-spec-label"><?php echo esc_html( $attr['name'] ); ?></span>
								<span class="ezp-pdp-spec-value"><?php echo esc_html( ezp_pdp_fa( $attr['value'] ) ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
					<?php
				} else {
					echo '<p>مشخصات فنی برای این محصول ثبت نشده است.</p>';
				}
				?>
			</div>

			<div class="ezp-pdp-tab-panel" data-content="reviews" data-title="نظرات کاربران">
				<div class="ezp-pdp-reviews-summary">
					<div class="ezp-pdp-score">
						<div class="ezp-pdp-score-number"><?php echo esc_html( ezp_pdp_fa( number_format_i18n( $average_rating, 1 ) ) ); ?></div>
						<div class="ezp-pdp-score-stars"><?php echo esc_html( str_repeat( '★', (int) round( $average_rating ) ) . str_repeat( '☆', 5 - (int) round( $average_rating ) ) ); ?></div>
						<div style="font-size:12px;color:var(--muted);"><?php echo esc_html( ezp_pdp_fa( $review_count ) ); ?> نظر</div>
					</div>
					<div>
						<?php for ( $star = 5; $star >= 1; $star-- ) :
							$percentage = $total_rated ? ( $rating_counts[ $star ] / $total_rated ) * 100 : 0;
						?>
							<div class="ezp-pdp-rating-bar">
								<span><?php echo esc_html( ezp_pdp_fa( $star ) ); ?></span>
								<div class="ezp-pdp-rating-track">
									<div class="ezp-pdp-rating-fill" style="width:<?php echo esc_attr( $percentage ); ?>%;"></div>
								</div>
								<span><?php echo esc_html( ezp_pdp_fa( $rating_counts[ $star ] ) ); ?></span>
							</div>
						<?php endfor; ?>
					</div>
				</div>

				<?php if ( ! empty( $reviews ) ) : ?>
					<?php foreach ( $reviews as $review ) :
						$rr = max( 0, min( 5, (int) get_comment_meta( $review->comment_ID, 'rating', true ) ) );
					?>
						<div class="ezp-pdp-review">
							<div class="ezp-pdp-review-head">
								<div>
									<div class="ezp-pdp-review-author"><?php echo esc_html( $review->comment_author ); ?></div>
									<div class="ezp-pdp-review-date"><?php echo esc_html( get_comment_date( '', $review ) ); ?></div>
								</div>
								<div class="ezp-pdp-review-stars"><?php echo esc_html( str_repeat( '★', $rr ) . str_repeat( '☆', 5 - $rr ) ); ?></div>
							</div>
							<div class="ezp-pdp-review-text"><?php echo wp_kses_post( wpautop( $review->comment_content ) ); ?></div>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<p>هنوز نظری برای این محصول ثبت نشده است.</p>
				<?php endif; ?>

				<?php $ezp_review_user = wp_get_current_user(); $ezp_review_logged_in = is_user_logged_in() && $ezp_review_user && $ezp_review_user->ID; ?>
				<div class="ezp-pdp-review-form-wrap<?php echo $ezp_review_logged_in ? ' is-logged-in' : ''; ?>">
					<?php if ( $ezp_review_logged_in ) : ?>
						<div class="ezp-pdp-review-welcome">
							<div class="ezp-pdp-review-welcome__icon"><svg viewBox="0 0 24 24"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg></div>
							<div><strong><?php echo esc_html( $ezp_review_user->display_name ?: $ezp_review_user->user_login ); ?> عزیز،</strong><span>تجربه‌ات درباره این محصول برای ما ارزشمند است. چند لحظه وقت بگذار و نظرت را با ما و خریداران بعدی در میان بگذار.</span></div>
						</div>
					<?php else : ?>
						<h3 class="ezp-pdp-review-form-title">تجربه‌تان را درباره این محصول بنویسید</h3>
						<p class="ezp-pdp-review-form-note">عضویت لازم نیست؛ کافی است نام، ایمیل و نظرتان را وارد کنید.</p>
					<?php endif; ?>
					<form class="ezp-pdp-review-form" id="ezp-pdp-review-form-<?php echo esc_attr( $id ); ?>" data-product-id="<?php echo esc_attr( $id ); ?>">
						<div class="ezp-pdp-review-grid">
							<div class="ezp-pdp-review-field">
								<label for="ezp-review-name-<?php echo esc_attr( $id ); ?>">نام کاربری <span>*</span></label>
								<input type="text" id="ezp-review-name-<?php echo esc_attr( $id ); ?>" name="author" required maxlength="100" autocomplete="name" value="<?php echo $ezp_review_logged_in ? esc_attr( $ezp_review_user->user_login ) : ''; ?>" <?php echo $ezp_review_logged_in ? 'readonly' : ''; ?>>
							</div>
							<div class="ezp-pdp-review-field">
								<label for="ezp-review-email-<?php echo esc_attr( $id ); ?>">ایمیل شما <span>*</span></label>
								<input type="email" id="ezp-review-email-<?php echo esc_attr( $id ); ?>" name="email" required maxlength="100" autocomplete="email" value="<?php echo $ezp_review_logged_in ? esc_attr( $ezp_review_user->user_email ) : ''; ?>" <?php echo $ezp_review_logged_in ? 'readonly' : ''; ?>>
							</div>
						</div>
						<div class="ezp-pdp-review-field">
							<label>امتیاز <span>*</span></label>
							<div class="ezp-pdp-review-stars-input" role="radiogroup" aria-label="امتیاز محصول">
								<?php for ( $rs = 5; $rs >= 1; $rs-- ) : ?>
									<label>
										<input type="radio" name="rating" value="<?php echo esc_attr( $rs ); ?>" <?php echo 5 === $rs ? 'checked' : ''; ?>>
										<span><?php echo esc_html( str_repeat( '★', $rs ) ); ?></span>
									</label>
								<?php endfor; ?>
							</div>
						</div>
						<div class="ezp-pdp-review-field">
							<label for="ezp-review-content-<?php echo esc_attr( $id ); ?>">متن نظر <span>*</span></label>
							<textarea id="ezp-review-content-<?php echo esc_attr( $id ); ?>" name="content" rows="5" required maxlength="5000" placeholder="چه چیزی در این محصول برایتان مفید بود؟ تجربه واقعی شما می‌تواند انتخاب را برای دیگران راحت‌تر کند."></textarea>
						</div>
						<?php if ( ! is_user_logged_in() ) : ?>
							<?php ezp_pdp_captcha_markup( $id ); ?>
						<?php endif; ?>
						<?php if ( ! is_user_logged_in() ) : ?>
							<div class="ezp-pdp-review-hp" aria-hidden="true">
								<label for="ezp-review-website-<?php echo esc_attr( $id ); ?>">Website</label>
								<input type="text" id="ezp-review-website-<?php echo esc_attr( $id ); ?>" name="website" value="" tabindex="-1" autocomplete="off">
							</div>
						<?php endif; ?>
						<button type="submit" class="ezp-pdp-review-submit">
							<span>ثبت نظر</span>
						</button>
						<div class="ezp-pdp-review-message" aria-live="polite"></div>
					</form>
				</div>
			</div>
		</div>

		<?php if ( ! empty( $related_products ) ) : ?>
			<div class="ezp-pdp-related">
				<div class="ezp-pdp-related-head">
					<h2 class="ezp-pdp-related-title">
						<?php echo ezp_pdp_icon( 'gift', '' ); ?>
						محصولات مرتبط
					</h2>
					<div class="ezp-pdp-related-nav">
						<button type="button" class="ezp-pdp-related-prev" aria-label="قبلی">
							<svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
						</button>
						<button type="button" class="ezp-pdp-related-next" aria-label="بعدی">
							<svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
						</button>
					</div>
				</div>
				<div class="ezp-pdp-related-track" id="ezp-pdp-related-track">
					<?php foreach ( $related_products as $rp ) :
						$rid = $rp->get_id();
						$rimg = wp_get_attachment_image_url( $rp->get_image_id(), 'woocommerce_thumbnail' ) ?: wc_placeholder_img_src();
					?>
						<a class="ezp-pdp-related-card" href="<?php echo esc_url( get_permalink( $rid ) ); ?>">
							<div class="ezp-pdp-related-img">
								<img src="<?php echo esc_url( $rimg ); ?>" alt="<?php echo esc_attr( $rp->get_name() ); ?>" loading="lazy">
							</div>
							<div class="ezp-pdp-related-body">
								<div class="ezp-pdp-related-name"><?php echo esc_html( $rp->get_name() ); ?></div>
								<div class="ezp-pdp-related-price"><?php echo wp_kses_post( ezp_pdp_fa( $rp->get_price_html() ) ); ?></div>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		</div>

		<?php if ( $in_stock ) : ?>
			<div class="ezp-pdp-sticky" id="ezp-pdp-sticky" data-product-id="<?php echo esc_attr( $id ); ?>" role="region" aria-label="افزودن سریع به سبد خرید">
				<div class="ezp-pdp-sticky-info">
					<span class="ezp-pdp-sticky-title"><?php echo esc_html( $title ); ?></span>
					<span class="ezp-pdp-sticky-price"><?php echo wp_kses_post( ezp_pdp_fa( $price ) ); ?></span>
				</div>
				<button type="button" class="ezp-pdp-sticky-btn" id="ezp-pdp-sticky-add">
					<?php echo ezp_pdp_icon( 'cart', '' ); ?>
					<span>افزودن به سبد</span>
				</button>
				<button type="button" class="ezp-pdp-sticky-close" id="ezp-pdp-sticky-close" aria-label="بستن نوار شناور">
					<svg viewBox="0 0 24 24" aria-hidden="true">
						<line x1="18" y1="6" x2="6" y2="18"/>
						<line x1="6" y1="6" x2="18" y2="18"/>
					</svg>
				</button>
			</div>
		<?php endif; ?>

		<script>
		(function () {
			'use strict';

			const root = document.querySelector('.ezp-pdp[data-product-id="<?php echo esc_js( $id ); ?>"]');
			if (!root) return;

			const ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';

			/* Gallery */
			const mainImg = root.querySelector('.ezp-pdp-main-image');
			root.querySelectorAll('.ezp-pdp-thumb').forEach((t) => {
				t.addEventListener('click', () => {
					const src = t.getAttribute('data-image');
					if (mainImg && src) mainImg.src = src;
					root.querySelectorAll('.ezp-pdp-thumb').forEach((x) => x.classList.remove('active'));
					t.classList.add('active');
				});
			});

			/* Quantity */
			const qtyInput = root.querySelector('.ezp-pdp-qty-input');
			const qtyMinus = root.querySelector('.ezp-pdp-qty-minus');
			const qtyPlus = root.querySelector('.ezp-pdp-qty-plus');

			const setQty = (val) => {
				if (!qtyInput) return;
				let max = parseInt(qtyInput.getAttribute('max') || 999999, 10);
				let v = parseInt(val, 10);
				if (!Number.isFinite(v)) v = 1;
				v = Math.max(1, Math.min(max, v));
				qtyInput.value = v;
			};

			if (qtyMinus && qtyInput) {
				qtyMinus.addEventListener('click', () => {
					setQty(parseInt(qtyInput.value || 1, 10) - 1);
				});
			}
			if (qtyPlus && qtyInput) {
				qtyPlus.addEventListener('click', () => {
					setQty(parseInt(qtyInput.value || 1, 10) + 1);
				});
			}
			if (qtyInput) {
				qtyInput.addEventListener('input', () => setQty(qtyInput.value));
				qtyInput.addEventListener('blur', () => setQty(qtyInput.value));
			}

			/* Tabs */
			root.querySelectorAll('.ezp-pdp-tab-btn').forEach((b) => {
				b.addEventListener('click', () => {
					const target = b.getAttribute('data-tab');
					root.querySelectorAll('.ezp-pdp-tab-btn').forEach((x) => x.classList.remove('active'));
					root.querySelectorAll('.ezp-pdp-tab-panel').forEach((x) => x.classList.remove('active'));
					b.classList.add('active');
					const panel = root.querySelector('.ezp-pdp-tab-panel[data-content="' + target + '"]');
					if (panel) panel.classList.add('active');
				});
			});

			/* Live values */
			root.querySelectorAll('[data-live-key]').forEach((input) => {
				const key = input.getAttribute('data-live-key');
				const output = root.querySelector('[data-live-output="' + key + '"]');
				const wrap = root.querySelector('[data-live-wrap="' + key + '"]');
				if (!output) return;

				const update = () => {
					let val = input.value || '';
					if (input.type === 'file') {
						val = (input.files && input.files.length) ? input.files[0].name : '';
					}
					if (input.tagName === 'SELECT') {
						const opt = input.options[input.selectedIndex];
						val = (opt && opt.value) ? (opt.textContent || '') : '';
					}
					if (input.type === 'checkbox' || input.type === 'radio') {
						if (!input.checked) val = '';
					}
					if (val.trim() === '') {
						output.textContent = 'وارد نشده';
						output.classList.add('empty');
						if (wrap) wrap.classList.remove('has-value');
					} else {
						val = val.replace(/[0-9]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);
						output.textContent = val;
						output.classList.remove('empty');
						if (wrap) wrap.classList.add('has-value');
					}
					/* refresh count on same panel */
					const panel = input.closest('[data-live-panel]');
					if (panel && typeof ezpUpdateLiveCount === 'function') {
						ezpUpdateLiveCount(panel);
					}
				};
				input.addEventListener('input', update);
				input.addEventListener('change', update);
				update();
			});

			/* Universal upload boxes */
			root.querySelectorAll('.ezp-pdp-upload-box input[type="file"]').forEach((input) => {
				const box = input.closest('.ezp-pdp-upload-box');
				if (!box) return;
				const nameEl = box.querySelector('.ezp-pdp-upload-box__name');
				const defaultText = nameEl ? nameEl.textContent : '';
				input.addEventListener('change', () => {
					const file = input.files && input.files.length ? input.files[0] : null;
					box.classList.toggle('is-selected', !!file);
					if (nameEl) nameEl.textContent = file ? file.name + ' • ' + Math.round(file.size / 1024) + ' KB' : defaultText;
				});
			});

			/* ============================================================
			   LIVE PREVIEW — Mobile Accordion Toggle + Count
			   ============================================================ */
			function ezpUpdateLiveCount(panel){
				if (!panel) return;
				const items = panel.querySelectorAll('.ezp-pdp-live-item');
				let filled = 0;
				items.forEach(function(item){
					if (item.classList.contains('has-value')) filled++;
				});

				const counter = panel.querySelector('[data-live-count]');
				if (!counter) return;

				counter.textContent = String(filled).replace(/[0-9]/g, function(d){
					return '۰۱۲۳۴۵۶۷۸۹'[d];
				});
				counter.classList.toggle('is-empty', filled === 0);
			}
			/* expose for live value updates */
			window.ezpUpdateLiveCount = ezpUpdateLiveCount;

			root.querySelectorAll('[data-live-panel]').forEach(function(panel){
				const toggleBtn = panel.querySelector('[data-live-toggle]');
				if (!toggleBtn) return;

				toggleBtn.addEventListener('click', function(){
					const isOpen = panel.classList.toggle('is-open');
					toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
				});

				ezpUpdateLiveCount(panel);
			});

			/* Ripple */
			root.querySelectorAll('.ezp-pdp-btn').forEach((btn) => {
				btn.addEventListener('click', (e) => {
					const rect = btn.getBoundingClientRect();
					const size = Math.max(rect.width, rect.height);
					const ripple = document.createElement('span');
					ripple.className = 'ezp-pdp-ripple';
					ripple.style.width = ripple.style.height = size + 'px';
					ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
					ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
					btn.appendChild(ripple);
					setTimeout(() => ripple.remove(), 700);
				});
			});

			/* Toast */
			const showToast = (msg, type) => {
				const old = document.getElementById('ezp-pdp-toast');
				if (old) old.remove();
				const t = document.createElement('div');
				t.id = 'ezp-pdp-toast';
				const bg = type === 'error'
					? 'linear-gradient(135deg,#dc2626,#b91c1c)'
					: 'linear-gradient(135deg,#059669,#047857)';
				t.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(120px);z-index:999999;display:flex;align-items:center;gap:10px;padding:14px 22px;background:' + bg + ';color:#fff;border-radius:14px;box-shadow:0 15px 45px rgba(0,0,0,.25);font-size:13px;font-weight:700;opacity:0;transition:all .4s cubic-bezier(.4,0,.2,1);font-family:\'EzLensVazir\',Tahoma,sans-serif;max-width:calc(100% - 32px);';
				t.textContent = msg;
				document.body.appendChild(t);
				requestAnimationFrame(() => {
					t.style.opacity = '1';
					t.style.transform = 'translateX(-50%) translateY(0)';
				});
				setTimeout(() => {
					t.style.opacity = '0';
					t.style.transform = 'translateX(-50%) translateY(120px)';
					setTimeout(() => t.remove(), 400);
				}, 3200);
			};

			/* Validation */
			const validateForm = (form) => {
				let valid = true, firstInvalid = null;
				form.querySelectorAll('[required]').forEach((field) => {
					const val = (field.value || '').trim();
					if (val === '') {
						valid = false;
						field.style.borderColor = '#dc2626';
						field.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, .1)';
						if (!firstInvalid) firstInvalid = field;
					} else {
						field.style.borderColor = '';
						field.style.boxShadow = '';
					}
				});
				if (!valid && firstInvalid) {
					firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
					setTimeout(() => firstInvalid.focus(), 400);
				}
				return valid;
			};

			/* Add to cart */
			const form = root.querySelector('.ezp-pdp-cart-form');
			const addBtn = root.querySelector('.ezp-pdp-btn-add');

			if (form && addBtn) {
				form.addEventListener('submit', (e) => {
					e.preventDefault();

					if (!validateForm(form)) {
						showToast('لطفاً فیلدهای الزامی را پر کنید.', 'error');
						return;
					}

					const formData = new FormData(form);
					formData.append('action', 'ezp_ajax_add_to_cart');
					formData.append('security', '<?php echo esc_js( wp_create_nonce( "ezp_add_to_cart" ) ); ?>');

					addBtn.disabled = true;
					addBtn.classList.add('is-loading');
					const originalText = addBtn.querySelector('span')?.textContent || 'افزودن به سبد خرید';

					fetch(ajaxUrl, {
						method: 'POST',
						body: formData,
						credentials: 'same-origin'
					})
					.then((r) => r.json())
					.then((res) => {
						addBtn.classList.remove('is-loading');

						if (res && res.success) {
							addBtn.classList.add('is-success');
							const span = addBtn.querySelector('span');
							if (span) span.textContent = '✓ اضافه شد';

							showToast((res.data && res.data.message) || 'محصول با موفقیت به سبد خرید اضافه شد.', 'success');

							if (typeof jQuery !== 'undefined') {
								try {
									jQuery(document.body).trigger('wc_fragment_refresh');
									jQuery(document.body).trigger('added_to_cart', [
										(res.data && res.data.fragments) || {},
										(res.data && res.data.cart_hash) || '',
										jQuery(addBtn)
									]);
								} catch (err) { /* silent */ }
							}

							setTimeout(() => {
								addBtn.classList.remove('is-success');
								addBtn.disabled = false;
								if (span) span.textContent = originalText;
							}, 2200);
						} else {
							addBtn.disabled = false;
							showToast((res && res.data && res.data.message) || 'خطا در افزودن به سبد خرید', 'error');
						}
					})
					.catch(() => {
						addBtn.disabled = false;
						addBtn.classList.remove('is-loading');
						showToast('خطای ارتباط با سرور. دوباره تلاش کنید.', 'error');
					});
				});
			}

			/* Buy now */
			const buyBtn = root.querySelector('.ezp-pdp-btn-buy');
			if (buyBtn && form) {
				buyBtn.addEventListener('click', () => {
					if (!validateForm(form)) {
						showToast('لطفاً فیلدهای الزامی را پر کنید.', 'error');
						return;
					}

					buyBtn.disabled = true;
					buyBtn.classList.add('is-loading');
					const origHtml = buyBtn.innerHTML;
					buyBtn.innerHTML = '<span>در حال انتقال...</span>';

					const fd = new FormData(form);
					fd.append('action', 'ezp_ajax_buy_now');
					fd.append('security', '<?php echo esc_js( wp_create_nonce( "ezp_add_to_cart" ) ); ?>');

					fetch(ajaxUrl, {
						method: 'POST',
						body: fd,
						credentials: 'same-origin'
					})
					.then((r) => r.json())
					.then((res) => {
						if (res && res.success && res.data && res.data.redirect) {
							window.location.href = res.data.redirect;
						} else {
							buyBtn.disabled = false;
							buyBtn.classList.remove('is-loading');
							buyBtn.innerHTML = origHtml;
							showToast((res && res.data && res.data.message) || 'خطا در انتقال', 'error');
						}
					})
					.catch(() => {
						buyBtn.disabled = false;
						buyBtn.classList.remove('is-loading');
						buyBtn.innerHTML = origHtml;
						showToast('خطای ارتباط با سرور.', 'error');
					});
				});
			}

			/* Product review form */
			const reviewForm = root.querySelector('.ezp-pdp-review-form');
			if (reviewForm) {
				reviewForm.addEventListener('submit', (e) => {
					e.preventDefault();

					const submit = reviewForm.querySelector('.ezp-pdp-review-submit');
					const localCaptcha = reviewForm.querySelector('.ezp-pdp-local-captcha');
					if (localCaptcha) {
						const captchaAnswer = localCaptcha.querySelector('input[name="captcha_answer"]');
						if (!captchaAnswer || !captchaAnswer.value.trim()) {
							const captchaMsg = reviewForm.querySelector('.ezp-pdp-review-message');
							if (captchaMsg) { captchaMsg.className = 'ezp-pdp-review-message error'; captchaMsg.textContent = 'لطفاً عبارت امنیتی را وارد کنید.'; }
							if (captchaAnswer) captchaAnswer.focus();
							return;
						}
					}
					const message = reviewForm.querySelector('.ezp-pdp-review-message');
					const fd = new FormData(reviewForm);
					fd.append('action', 'ezp_pdp_submit_review');
					fd.append('product_id', reviewForm.getAttribute('data-product-id') || '');
					fd.append('security', '<?php echo esc_js( ezp_pdp_review_nonce() ); ?>');

					if (submit) submit.disabled = true;
					if (message) {
						message.className = 'ezp-pdp-review-message';
						message.textContent = 'در حال ثبت نظر...';
					}

					fetch(ajaxUrl, {
						method: 'POST',
						body: fd,
						credentials: 'same-origin'
					})
					.then((r) => r.json())
					.then((res) => {
						if (res && res.success) {
							if (message) {
								message.className = 'ezp-pdp-review-message success';
								message.textContent = res.data?.message || 'نظر شما با موفقیت ثبت شد.';
							}
							reviewForm.reset();
							const firstRating = reviewForm.querySelector('input[name="rating"][value="5"]');
							if (firstRating) firstRating.checked = true;

							if (res.data?.html) {
								const empty = reviewForm.closest('.ezp-pdp-tab-panel')?.querySelector('.ezp-pdp-review');
								const wrap = reviewForm.closest('.ezp-pdp-review-form-wrap');
								if (wrap && res.data.html) wrap.insertAdjacentHTML('beforebegin', res.data.html);
								if (empty) empty.remove();
							}
						} else {
							if (message) {
								message.className = 'ezp-pdp-review-message error';
								message.textContent = res?.data?.message || 'ثبت نظر انجام نشد.';
							}
						}
					})
					.catch(() => {
						if (message) {
							message.className = 'ezp-pdp-review-message error';
							message.textContent = 'خطای ارتباط با سرور. لطفاً دوباره تلاش کنید.';
						}
					})
					.finally(() => {
						if (submit) submit.disabled = false;
					});
				});
			}

			/* Related Products Slider */
			const relatedTrack = document.getElementById('ezp-pdp-related-track');
			const relatedPrev = root.querySelector('.ezp-pdp-related-prev');
			const relatedNext = root.querySelector('.ezp-pdp-related-next');

			if (relatedTrack && relatedPrev && relatedNext) {
				const getCardWidth = () => {
					const card = relatedTrack.querySelector('.ezp-pdp-related-card');
					if (!card) return 0;
					const gap = window.innerWidth <= 600 ? 10 : 14;
					return card.offsetWidth + gap;
				};

				const updateNav = () => {
					const maxScroll = relatedTrack.scrollWidth - relatedTrack.clientWidth;
					relatedPrev.disabled = relatedTrack.scrollLeft <= 5;
					relatedNext.disabled = relatedTrack.scrollLeft >= (maxScroll - 5);
				};

				relatedPrev.addEventListener('click', () => {
					relatedTrack.scrollBy({ left: -getCardWidth(), behavior: 'smooth' });
				});

				relatedNext.addEventListener('click', () => {
					relatedTrack.scrollBy({ left: getCardWidth(), behavior: 'smooth' });
				});

				let relatedTicking = false;
				relatedTrack.addEventListener('scroll', () => {
					if (!relatedTicking) {
						window.requestAnimationFrame(() => {
							updateNav();
							relatedTicking = false;
						});
						relatedTicking = true;
					}
				}, { passive: true });

				window.addEventListener('resize', () => {
					updateNav();
				}, { passive: true });

				updateNav();
			}

			/* Mobile Sticky Bar */
			const sticky = document.getElementById('ezp-pdp-sticky');
			if (sticky) {
				if (sticky.parentNode !== document.body) {
					document.body.appendChild(sticky);
				}

				let userClosed = false;
				try {
					if (sessionStorage.getItem('ezp_pdp_sticky_closed') === '1') {
						userClosed = true;
					}
				} catch (e) {}

				const isMobile = () => window.matchMedia('(max-width: 768px)').matches;

				const applyBaseDisplay = () => {
					if (isMobile()) {
						sticky.style.setProperty('display', 'flex', 'important');
						document.body.classList.add('ezp-has-pdp-sticky');
					} else {
						sticky.style.setProperty('display', 'none', 'important');
						document.body.classList.remove('ezp-has-pdp-sticky');
					}
				};

				const showSticky = () => {
					if (userClosed || !isMobile()) return;
					sticky.classList.remove('hide');
					sticky.classList.add('show');
				};
				const hideSticky = () => {
					sticky.classList.remove('show');
				};

				if (userClosed) {
					sticky.style.setProperty('display', 'none', 'important');
					document.body.classList.remove('ezp-has-pdp-sticky');
				} else {
					applyBaseDisplay();

					const closeBtn = document.getElementById('ezp-pdp-sticky-close');
					if (closeBtn) {
						closeBtn.addEventListener('click', (e) => {
							e.preventDefault();
							e.stopPropagation();
							userClosed = true;
							sticky.classList.remove('show');
							sticky.classList.add('hide');
							sticky.style.setProperty('display', 'none', 'important');
							document.body.classList.remove('ezp-has-pdp-sticky');
							try { sessionStorage.setItem('ezp_pdp_sticky_closed', '1'); } catch (e) {}
						});
					}

					const mainAddBtn = root.querySelector('.ezp-pdp-btn-add');

					const checkVisibility = () => {
						if (userClosed || !isMobile()) {
							hideSticky();
							return;
						}

						if (mainAddBtn) {
							const rect = mainAddBtn.getBoundingClientRect();
							const isVisible = rect.top < window.innerHeight && rect.bottom > 0;
							if (isVisible) {
								hideSticky();
							} else {
								showSticky();
							}
						} else {
							if (window.scrollY > 250) showSticky();
							else hideSticky();
						}
					};

					let scrollTicking = false;
					window.addEventListener('scroll', () => {
						if (!scrollTicking) {
							window.requestAnimationFrame(() => {
								checkVisibility();
								scrollTicking = false;
							});
							scrollTicking = true;
						}
					}, { passive: true });

					window.addEventListener('resize', () => {
						applyBaseDisplay();
						checkVisibility();
					}, { passive: true });

					requestAnimationFrame(checkVisibility);
					setTimeout(checkVisibility, 300);
				}

				const stickyAdd = document.getElementById('ezp-pdp-sticky-add');
				if (stickyAdd) {
					stickyAdd.addEventListener('click', () => {
						if (form) form.scrollIntoView({ behavior: 'smooth', block: 'center' });
						setTimeout(() => { if (addBtn) addBtn.click(); }, 500);
					});
				}
			}

		
			/* Product Options: accordion + modal (portal to body — stable on hover) */
			(function initEzpDisplayModes() {
				const root = document.querySelector('.ezp-pdp') || document;

				root.querySelectorAll('[data-ezp-acc-trigger]').forEach(function(btn) {
					btn.addEventListener('click', function(e) {
						e.preventDefault();
						const expanded = btn.getAttribute('aria-expanded') === 'true';
						const bodyId = btn.getAttribute('aria-controls');
						const body = bodyId ? document.getElementById(bodyId) : null;
						btn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
						if (body) {
							if (expanded) body.setAttribute('hidden', '');
							else body.removeAttribute('hidden');
						}
					});
				});

				function openModal(modal) {
					if (!modal) return;
					// Move to body so parent overflow/transform cannot hide it on hover
					if (modal.parentElement !== document.body) {
						modal.__ezpHome = modal.parentElement;
						document.body.appendChild(modal);
					}
					modal.removeAttribute('hidden');
					modal.classList.add('is-open');
					modal.style.display = 'flex';
					modal.style.visibility = 'visible';
					modal.style.opacity = '1';
					document.body.classList.add('ezp-pdp-modal-open');
					const focusable = modal.querySelector('.ezp-pdp-modal__close, .ezp-pdp-modal__done');
					if (focusable) {
						try { focusable.focus({ preventScroll: true }); } catch (err) { try { focusable.focus(); } catch (e2) {} }
					}
				}
				function closeModal(modal) {
					if (!modal) return;
					modal.classList.remove('is-open');
					modal.setAttribute('hidden', '');
					modal.style.display = 'none';
					if (!document.querySelector('.ezp-pdp-modal.is-open')) {
						document.body.classList.remove('ezp-pdp-modal-open');
					}
					// Optional: leave on body for next open (stable). Do not move back — avoids reflow bugs.
				}

				root.querySelectorAll('[data-ezp-modal-open]').forEach(function(btn) {
					btn.addEventListener('click', function(e) {
						e.preventDefault();
						e.stopPropagation();
						const id = btn.getAttribute('data-modal-target');
						const modal = id ? document.getElementById(id) : null;
						openModal(modal);
					});
				});

				// Close only on explicit close controls / backdrop click — never on mouseleave/hover
				document.addEventListener('click', function(e) {
					const closer = e.target.closest('[data-ezp-modal-close]');
					if (!closer) return;
					const modal = closer.closest('.ezp-pdp-modal');
					if (modal) {
						e.preventDefault();
						closeModal(modal);
					}
				});

				// Keep panel from closing when interacting inside
				document.addEventListener('mouseover', function(e) {
					const modal = e.target.closest && e.target.closest('.ezp-pdp-modal.is-open');
					if (!modal) return;
					modal.classList.add('is-open');
					modal.style.display = 'flex';
					modal.style.visibility = 'visible';
					modal.style.opacity = '1';
				}, true);

				document.addEventListener('keydown', function(e) {
					if (e.key !== 'Escape') return;
					const open = document.querySelector('.ezp-pdp-modal.is-open');
					if (open) closeModal(open);
				});
			})();

		})();
		</script>

		<?php
		return ob_get_clean();
	}
}

/* ============================================================
   AJAX: Add to cart
   ============================================================ */
if ( ! function_exists( 'ezp_pdp_ajax_add_to_cart' ) ) {
	add_action( 'wp_ajax_ezp_ajax_add_to_cart', 'ezp_pdp_ajax_add_to_cart' );
	add_action( 'wp_ajax_nopriv_ezp_ajax_add_to_cart', 'ezp_pdp_ajax_add_to_cart' );

	function ezp_pdp_ajax_add_to_cart() {
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'ezp_add_to_cart' ) ) {
			wp_send_json_error( array( 'message' => 'درخواست نامعتبر.' ) );
		}

		$product_id = absint( $_POST['product_id'] ?? 0 );
		$quantity   = max( 1, absint( $_POST['quantity'] ?? 1 ) );

		if ( ! $product_id ) {
			wp_send_json_error( array( 'message' => 'محصول نامعتبر.' ) );
		}

		$product = wc_get_product( $product_id );
		if ( ! $product || ! $product->is_in_stock() ) {
			wp_send_json_error( array( 'message' => 'محصول موجود نیست.' ) );
		}

		$ezlens_options = array();
		if ( ! empty( $_POST['ezlens_options'] ) && is_array( $_POST['ezlens_options'] ) ) {
			foreach ( $_POST['ezlens_options'] as $tid => $fields ) {
				if ( ! is_array( $fields ) ) continue;
				foreach ( $fields as $k => $v ) {
					$ck = sanitize_key( $k );
					if ( is_array( $v ) ) {
						$cv = implode( '، ', array_map( 'sanitize_text_field', wp_unslash( $v ) ) );
					} else {
						$cv = sanitize_text_field( wp_unslash( $v ) );
					}
					if ( $cv !== '' ) $ezlens_options[ $ck ] = $cv;
				}
			}
		}

		$cart_item_data = array();

		$slots = ezp_pdp_load_slots( $product_id );
		foreach ( $slots as $slot ) {
			$tid = $slot['template_id'];
			$template_fields = array();
			foreach ( $slot['fields'] as $f ) {
				$template_fields[ $f['key'] ] = $f;
			}
			if ( empty( $template_fields ) ) continue;

			$file_options = ezp_pdp_process_option_files( absint( $tid ), $template_fields );
			if ( is_wp_error( $file_options ) ) {
				wp_send_json_error( array( 'message' => $file_options->get_error_message() ) );
			}
			foreach ( $file_options as $file_key => $file_data ) {
				$ezlens_options[ $file_key ] = $file_data['url'];
			}
		}

		if ( ! empty( $ezlens_options ) ) {
			$cart_item_data['ezlens_options'] = $ezlens_options;
			$cart_item_data['unique_key'] = md5( wp_json_encode( $ezlens_options ) . microtime() );
		}

		remove_all_filters( 'woocommerce_add_to_cart_validation' );
		add_filter( 'woocommerce_add_to_cart_validation', 'wc_validate_add_to_cart', 10, 5 );

		$cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity, 0, array(), $cart_item_data );

		if ( ! $cart_item_key ) {
			$notices = wc_get_notices( 'error' );
			$msg = ! empty( $notices ) ? wp_strip_all_tags( $notices[0]['notice'] ) : 'خطا در افزودن به سبد خرید.';
			wc_clear_notices();
			wp_send_json_error( array( 'message' => $msg ) );
		}

		wc_clear_notices();

		ob_start();
		woocommerce_mini_cart();
		$mini_cart = ob_get_clean();

		wp_send_json_success( array(
			'message'   => 'محصول با موفقیت به سبد خرید اضافه شد',
			'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array(
				'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
			) ),
			'cart_hash' => WC()->cart->get_cart_hash(),
		) );
	}
}

/* ============================================================
   AJAX: Buy now
   ============================================================ */
if ( ! function_exists( 'ezp_pdp_ajax_buy_now' ) ) {
	add_action( 'wp_ajax_ezp_ajax_buy_now', 'ezp_pdp_ajax_buy_now' );
	add_action( 'wp_ajax_nopriv_ezp_ajax_buy_now', 'ezp_pdp_ajax_buy_now' );

	function ezp_pdp_ajax_buy_now() {
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'ezp_add_to_cart' ) ) {
			wp_send_json_error( array( 'message' => 'درخواست نامعتبر.' ) );
		}

		$product_id = absint( $_POST['product_id'] ?? 0 );
		$quantity   = max( 1, absint( $_POST['quantity'] ?? 1 ) );

		if ( ! $product_id ) {
			wp_send_json_error( array( 'message' => 'محصول نامعتبر.' ) );
		}

		$product = wc_get_product( $product_id );
		if ( ! $product || ! $product->is_in_stock() ) {
			wp_send_json_error( array( 'message' => 'محصول موجود نیست.' ) );
		}

		$ezlens_options = array();
		if ( ! empty( $_POST['ezlens_options'] ) && is_array( $_POST['ezlens_options'] ) ) {
			foreach ( $_POST['ezlens_options'] as $tid => $fields ) {
				if ( ! is_array( $fields ) ) continue;
				foreach ( $fields as $k => $v ) {
					$ck = sanitize_key( $k );
					if ( is_array( $v ) ) {
						$cv = implode( '، ', array_map( 'sanitize_text_field', wp_unslash( $v ) ) );
					} else {
						$cv = sanitize_text_field( wp_unslash( $v ) );
					}
					if ( $cv !== '' ) $ezlens_options[ $ck ] = $cv;
				}
			}
		}

		$cart_item_data = array();

		$slots = ezp_pdp_load_slots( $product_id );
		foreach ( $slots as $slot ) {
			$tid = $slot['template_id'];
			$template_fields = array();
			foreach ( $slot['fields'] as $f ) {
				$template_fields[ $f['key'] ] = $f;
			}
			if ( empty( $template_fields ) ) continue;

			$file_options = ezp_pdp_process_option_files( absint( $tid ), $template_fields );
			if ( is_wp_error( $file_options ) ) {
				wp_send_json_error( array( 'message' => $file_options->get_error_message() ) );
			}
			foreach ( $file_options as $file_key => $file_data ) {
				$ezlens_options[ $file_key ] = $file_data['url'];
			}
		}

		if ( ! empty( $ezlens_options ) ) {
			$cart_item_data['ezlens_options'] = $ezlens_options;
			$cart_item_data['unique_key'] = md5( wp_json_encode( $ezlens_options ) . microtime() );
		}

		WC()->cart->empty_cart();

		remove_all_filters( 'woocommerce_add_to_cart_validation' );
		add_filter( 'woocommerce_add_to_cart_validation', 'wc_validate_add_to_cart', 10, 5 );

		$key = WC()->cart->add_to_cart( $product_id, $quantity, 0, array(), $cart_item_data );

		if ( ! $key ) {
			wp_send_json_error( array( 'message' => 'خطا در افزودن به سبد.' ) );
		}

		wc_clear_notices();

		wp_send_json_success( array( 'redirect' => wc_get_checkout_url() ) );
	}
}


/* ============================================================
   AJAX: Product review
   ============================================================ */
if ( ! function_exists( 'ezp_pdp_ajax_submit_review' ) ) {
	add_action( 'wp_ajax_ezp_pdp_submit_review', 'ezp_pdp_ajax_submit_review' );
	add_action( 'wp_ajax_nopriv_ezp_pdp_submit_review', 'ezp_pdp_ajax_submit_review' );

	function ezp_pdp_ajax_submit_review() {
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'ezp_pdp_review' ) ) {
			wp_send_json_error( array( 'message' => 'درخواست نامعتبر است.' ), 403 );
		}

		$is_guest = ! is_user_logged_in();

		if ( $is_guest ) {
			$honeypot = isset( $_POST['website'] ) ? trim( (string) wp_unslash( $_POST['website'] ) ) : '';
			if ( '' !== $honeypot ) {
				wp_send_json_error( array( 'message' => 'ارسال نظر انجام نشد. لطفاً دوباره تلاش کنید.' ), 403 );
			}

			$attempt_result = ezp_pdp_guest_review_attempt_limit();
			if ( is_wp_error( $attempt_result ) ) {
				wp_send_json_error( array( 'message' => $attempt_result->get_error_message() ), 429 );
			}

			$captcha_result = ezp_pdp_captcha_verify(
				wp_unslash( $_POST['captcha_id'] ?? '' ),
				wp_unslash( $_POST['captcha_answer'] ?? '' )
			);
			if ( is_wp_error( $captcha_result ) ) {
				wp_send_json_error( array( 'message' => $captcha_result->get_error_message() ), 403 );
			}
		}

		$product_id = absint( $_POST['product_id'] ?? 0 );
		$product    = $product_id ? wc_get_product( $product_id ) : false;

		if ( ! $product || 'product' !== get_post_type( $product_id ) ) {
			wp_send_json_error( array( 'message' => 'محصول نامعتبر است.' ), 400 );
		}

		$name    = sanitize_text_field( wp_unslash( $_POST['author'] ?? '' ) );
		$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$content = trim( wp_kses_post( wp_unslash( $_POST['content'] ?? '' ) ) );
		$rating  = absint( $_POST['rating'] ?? 0 );

		if ( '' === $name || mb_strlen( $name ) > 100 ) {
			wp_send_json_error( array( 'message' => 'لطفاً نام خود را وارد کنید.' ) );
		}
		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => 'لطفاً یک ایمیل معتبر وارد کنید.' ) );
		}

		if ( $is_guest ) {
			$rate_result = ezp_pdp_guest_review_rate_limited( $email );
			if ( is_wp_error( $rate_result ) ) {
				wp_send_json_error( array( 'message' => $rate_result->get_error_message() ), 429 );
			}
		}

		if ( '' === $content || mb_strlen( wp_strip_all_tags( $content ) ) < 3 ) {
			wp_send_json_error( array( 'message' => 'لطفاً متن نظر را وارد کنید.' ) );
		}
		if ( $is_guest ) {
			$url_count = preg_match_all( '/(?:https?:\/\/|www\.)/iu', $content, $unused_matches );
			if ( false !== $url_count && $url_count > 1 ) {
				wp_send_json_error( array( 'message' => 'متن نظر نمی‌تواند شامل چندین لینک باشد.' ), 400 );
			}
		}
		if ( $rating < 1 || $rating > 5 ) {
			wp_send_json_error( array( 'message' => 'امتیاز انتخاب‌شده معتبر نیست.' ) );
		}

		add_filter( 'pre_option_comment_registration', '__return_zero', 999 );
		add_filter( 'comments_open', 'ezp_pdp_force_product_comments_open', 999, 2 );

		$commentdata = array(
			'comment_post_ID'      => $product_id,
			'comment_author'       => $name,
			'comment_author_email' => $email,
			'comment_content'      => $content,
			'comment_type'         => 'review',
			'user_id'              => get_current_user_id(),
		);

		if ( $is_guest ) {
			$commentdata['comment_approved'] = 0;
		}

		$comment_id = wp_new_comment( wp_slash( $commentdata ), true );

		if ( is_wp_error( $comment_id ) ) {
			remove_filter( 'pre_option_comment_registration', '__return_zero', 999 );
			remove_filter( 'comments_open', 'ezp_pdp_force_product_comments_open', 999 );
			wp_send_json_error( array( 'message' => $comment_id->get_error_message() ) );
		}

		if ( $is_guest ) {
			ezp_pdp_mark_guest_review_rate( $email );
		}

		update_comment_meta( $comment_id, 'rating', $rating );

		$comment = get_comment( $comment_id );
		$approved = $comment && '1' === (string) $comment->comment_approved;

		$html = '';
		if ( $approved ) {
			$html = '<div class="ezp-pdp-review">'
				. '<div class="ezp-pdp-review-head"><div>'
				. '<div class="ezp-pdp-review-author">' . esc_html( $name ) . '</div>'
				. '<div class="ezp-pdp-review-date">' . esc_html( get_comment_date( '', $comment ) ) . '</div>'
				. '</div><div class="ezp-pdp-review-stars">' . esc_html( str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) ) . '</div></div>'
				. '<div class="ezp-pdp-review-text">' . wp_kses_post( wpautop( $content ) ) . '</div>'
				. '</div>';
		}

		remove_filter( 'pre_option_comment_registration', '__return_zero', 999 );
		remove_filter( 'comments_open', 'ezp_pdp_force_product_comments_open', 999 );

		wp_send_json_success( array(
			'message' => $approved
				? 'نظر شما با موفقیت ثبت و نمایش داده شد.'
				: 'نظر شما ثبت شد و پس از تأیید مدیر نمایش داده می‌شود.',
			'html'    => $html,
		) );
	}
}

/* ============================================================
   Cart display + Order save
   ============================================================ */
if ( ! function_exists( 'ezp_pdp_option_label_map' ) ) {
	function ezp_pdp_option_label_map( $product_id ) {
		static $cache = array();
		$product_id = absint( $product_id );
		if ( isset( $cache[ $product_id ] ) ) {
			return $cache[ $product_id ];
		}
		$map = array();
		$slots = ezp_pdp_load_slots( $product_id );
		foreach ( $slots as $slot ) {
			if ( empty( $slot['fields'] ) || ! is_array( $slot['fields'] ) ) {
				continue;
			}
			foreach ( $slot['fields'] as $f ) {
				if ( empty( $f['key'] ) ) {
					continue;
				}
				$map[ $f['key'] ] = $f['label'] ?? $f['key'];
			}
		}
		$cache[ $product_id ] = $map;
		return $map;
	}
}

if ( ! function_exists( 'ezp_pdp_display_in_cart' ) ) {
	add_filter( 'woocommerce_get_item_data', 'ezp_pdp_display_in_cart', 10, 2 );
	function ezp_pdp_display_in_cart( $item_data, $cart_item ) {
		if ( ! empty( $cart_item['ezlens_options'] ) && is_array( $cart_item['ezlens_options'] ) ) {
			$pid  = isset( $cart_item['product_id'] ) ? absint( $cart_item['product_id'] ) : 0;
			$map  = $pid ? ezp_pdp_option_label_map( $pid ) : array();
			foreach ( $cart_item['ezlens_options'] as $key => $val ) {
				if ( $val === '' ) {
					continue;
				}
				$label = isset( $map[ $key ] ) ? $map[ $key ] : ucfirst( str_replace( '_', ' ', $key ) );
				$val_fa = str_replace( array( '0','1','2','3','4','5','6','7','8','9' ), array( '۰','۱','۲','۳','۴','۵','۶','۷','۸','۹' ), $val );
				$item_data[] = array( 'name' => $label, 'value' => $val_fa );
			}
		}
		return $item_data;
	}
}

if ( ! function_exists( 'ezp_pdp_save_to_order' ) ) {
	add_action( 'woocommerce_checkout_create_order_line_item', 'ezp_pdp_save_to_order', 10, 4 );
	function ezp_pdp_save_to_order( $item, $cart_item_key, $values, $order ) {
		if ( ! empty( $values['ezlens_options'] ) && is_array( $values['ezlens_options'] ) ) {
			foreach ( $values['ezlens_options'] as $key => $val ) {
				if ( $val === '' ) continue;
				$label = ucfirst( str_replace( '_', ' ', $key ) );
				$item->add_meta_data( $label, $val );
			}
		}
	}
}

/* ============================================================
   Replace product page
   ============================================================ */
if ( ! function_exists( 'ezp_pdp_replace_default' ) ) {
	add_action( 'wp', 'ezp_pdp_replace_default', 20 );
	function ezp_pdp_replace_default() {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) return;

		static $done = false;
		if ( $done ) return;
		$done = true;

		$hooks = array(
			'woocommerce_template_single_title'       => 5,
			'woocommerce_template_single_rating'      => 10,
			'woocommerce_template_single_price'       => 10,
			'woocommerce_template_single_excerpt'     => 20,
			'woocommerce_template_single_add_to_cart' => 30,
			'woocommerce_template_single_meta'        => 40,
			'woocommerce_template_single_sharing'     => 50,
		);
		foreach ( $hooks as $fn => $pri ) {
			remove_action( 'woocommerce_single_product_summary', $fn, $pri );
		}

		add_action( 'woocommerce_single_product_summary', function () {
			echo do_shortcode( '[custom_product_page]' );
		}, 5 );
	}
}