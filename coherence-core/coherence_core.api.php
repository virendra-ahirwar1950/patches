<?php

/**
 * @file
 * Coherence core hooks.
 */

/**
  * Determine on a per view/display basis whether BEF link processing should occur
  * for multiple exposed filter links.
  */
function hook_coherence_core_bef_links_enabled_for_view($view_id, $display_id) {
	if ($view_id == 'foo' && $display_id == 'bar') {
		return TRUE;
	}
	return FALSE;
}