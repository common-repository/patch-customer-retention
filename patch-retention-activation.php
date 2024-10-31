<?php

function patch_retention_activate () {
	register_uninstall_hook( __FILE__, 'patch_retention_uninstall' );
}
register_activation_hook( __FILE__, 'patch_retention_activate' );

function patch_retention_uninstall () {
	$patch_allowed_options = explode(',', PATCH_ALLOWED_OPTIONS);
	foreach ($patch_allowed_options as $option) {
		update_option($option, '');
		unregister_setting('patch-general', $option);
	}
}
