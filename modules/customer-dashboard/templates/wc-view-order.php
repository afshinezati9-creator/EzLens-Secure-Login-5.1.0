<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( class_exists( 'EzLens_CD_Router' ) ) {
	EzLens_CD_Router::get_instance()->render_shell( 'view-order' );
}
