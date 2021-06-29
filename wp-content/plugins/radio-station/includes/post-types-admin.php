<?php

/*
 * Admin Post Types Metaboxes and Post Lists
 * Author: Nikki Blight
 * Since: 2.2.7
 */

// === Metabox Positions ===
// - Metaboxes Above Content Area
// - Modify Taxonomy Metabox Positions
// === Language Selection ===
// - Add Language Metabox
// - Language Selection Metabox
// - Update Language Term on Save
// === Playlists ===
// - Add Playlist Data Metabox
// - Playlist Data Metabox
// - Add Assign Playlist to Show Metabox
// - Assign Playlist to Show Metabox
// - Update Playlist Data
// - Add Playlist List Columns
// - Playlist List Column Data
// - Playlist List Column Styles
// === Posts ===
// - Add Related Show Metabox
// - Related Show Metabox
// - Update Related Show
// - Related Show Quick Edit Select Input
// - Add Related Show Post List Column
// - Related Show Post List Column Data
// - Related Show Quick Edit Script
// - Add Bulk Edit Posts Action
// - Bulk Edit Posts Script
// - Bulk Edit Posts Handler
// - Bulk Edit Posts Notice
// - Related Show Post List Styles
// === Shows ===
// - Add Show Info Metabox
// - Show Info Metabox
// - Add Assign Hosts to Show Metabox
// - Assign Hosts to Show Metabox
// - Add Assign Producers to Show Metabox
// - Assign Producers to Show Metabox
// - Add Show Shifts Metabox
// - Show Shifts Metabox
// - Show Shift Table
// - Add Show Description Helper Metabox
// - Show Description Helper Metabox
// - Rename Show Featured Image Metabox
// - Add Show Images Metabox
// - Show Images Metabox
// - Update Show Metadata
// - Relogin AJAX Message
// - Add Show List Columns
// - Show List Column Data
// - Show List Column Styles
// === Schedule Overrides ===
// - Add Schedule Override Metabox
// - Schedule Override Metabox
// - Update Schedule Override
// - Add Schedule Override List Columns
// - Schedule Override Column Data
// - Schedule Override Column Styles
// - Sortable Override Date Column
// - Add Schedule Override Month Filter
// === Post Type List Query Filter ===


// -------------------------
// === Metabox Positions ===
// -------------------------

// ----------------------------
// Metaboxes Above Content Area
// ----------------------------
// (shows metaboxes above Editor area for Radio Station CPTs)
add_action( 'edit_form_after_title', 'radio_station_top_meta_boxes' );
function radio_station_top_meta_boxes() {
	global $post, $wp_meta_boxes;
	$current_screen = get_current_screen();

	if ( RADIO_STATION_DEBUG ) {
		echo "<!-- DOING TOP METABOXES -->";
		echo "<!-- TOP METABOXES: " . print_r( $wp_meta_boxes[$current_screen->post_type]['rstop'], true ) . " -->";
		echo "<!-- Current Screen: " . print_r( $current_screen, true ) . " -->";
		$metabox_order = get_user_option( 'meta-box-order_' . $current_screen->id );
		$hidden_metaboxes =  get_user_option( 'metaboxhidden_' . $current_screen->id );
		$screen_layout = get_user_option( 'screen_layout_' . $current_screen->id );
		echo "<!-- Metabox Order: " . print_r( $metabox_order, true ) . " -->";
		echo "<!-- Hidden Metaboxes: ". print_r( $hidden_metaboxes, true ) . " -->";
		echo "<!-- Screen Layout: " . print_r( $screen_layout, true ) . " -->";
	}

	// --- top metabox output ---
	// 2.3.2: change metabox ID from rs-top
	// (- is not supported in metabox ID for sort order saving)
	// (causing bug where sorted metaboxes disappear completely!)
	do_meta_boxes( $current_screen, 'rstop', $post );

	if ( RADIO_STATION_DEBUG ) {
		echo "<!-- DONE TOP METABOXES -->";
	}
}

// ---------------------------------
// Modify Taxonomy Metabox Positions
// ---------------------------------
// 2.3.0: also apply to override post type
// 2.3.0: remove default languages metabox from shows
add_action( 'add_meta_boxes', 'radio_station_modify_taxonomy_metabox_positions', 11 );
function radio_station_modify_taxonomy_metabox_positions() {

	global $wp_meta_boxes;

	// --- move genre selection metabox ---
	$id = RADIO_STATION_GENRES_SLUG . 'div';
	if ( isset( $wp_meta_boxes[RADIO_STATION_SHOW_SLUG]['side']['core'][$id] ) ) {
		$genres = $wp_meta_boxes[RADIO_STATION_SHOW_SLUG]['side']['core'][$id];
		unset( $wp_meta_boxes[RADIO_STATION_SHOW_SLUG]['side']['core'][$id] );
		$wp_meta_boxes[RADIO_STATION_SHOW_SLUG]['side']['high'][$id] = $genres;
	}
	// 2.3.0: do similar for overrides post type
	if ( isset( $wp_meta_boxes[RADIO_STATION_OVERRIDE_SLUG]['side']['core'][$id] ) ) {
		$genres = $wp_meta_boxes[RADIO_STATION_OVERRIDE_SLUG]['side']['core'][$id];
		unset( $wp_meta_boxes[RADIO_STATION_OVERRIDE_SLUG]['side']['core'][$id] );
		$wp_meta_boxes[RADIO_STATION_OVERRIDE_SLUG]['side']['high'][$id] = $genres;
	}

	// --- remove default language metabox from shows ---
	if ( isset( $wp_meta_boxes[RADIO_STATION_SHOW_SLUG]['side']['core'][RADIO_STATION_LANGUAGES_SLUG . 'div'] ) ) {
		unset( $wp_meta_boxes[RADIO_STATION_SHOW_SLUG]['side']['core'][RADIO_STATION_LANGUAGES_SLUG . 'div'] );
	}
	if ( isset( $wp_meta_boxes[RADIO_STATION_SHOW_SLUG]['side']['core']['tagsdiv-' . RADIO_STATION_LANGUAGES_SLUG] ) ) {
		unset( $wp_meta_boxes[RADIO_STATION_SHOW_SLUG]['side']['core']['tagsdiv-' . RADIO_STATION_LANGUAGES_SLUG] );
	}
	if ( isset( $wp_meta_boxes[RADIO_STATION_OVERRIDE_SLUG]['side']['core']['tagsdiv-' . RADIO_STATION_LANGUAGES_SLUG] ) ) {
		unset( $wp_meta_boxes[RADIO_STATION_OVERRIDE_SLUG]['side']['core']['tagsdiv-' . RADIO_STATION_LANGUAGES_SLUG] );
	}

	// if ( RADIO_STATION_DEBUG ) {
	// 	echo "<!-- METABOXES: " . print_r( $wp_meta_boxes, true ) . " -->";
	// }
}


// --------------------------
// === Language Selection ===
// --------------------------

// --------------------
// Add Language Metabox
// --------------------
// 2.3.0: add language selection metabox
add_action( 'add_meta_boxes', 'radio_station_add_show_language_metabox' );
function radio_station_add_show_language_metabox() {
	// note: only added to overrides as moved into show info metabox for shows
	// 2.3.2: removed unnecessary array wrapper from post type argument
	add_meta_box(
		RADIO_STATION_LANGUAGES_SLUG . 'div',
		__( 'Show Language', 'radio-station' ),
		'radio_station_show_language_metabox',
		RADIO_STATION_OVERRIDE_SLUG,
		'side',
		'high'
	);
}

// --------------------------
// Language Selection Metabox
// --------------------------
// 2.3.0: added language selection metabox
function radio_station_show_language_metabox() {

	// --- use same noncename as default box so no save_post hook needed ---
	wp_nonce_field( 'taxonomy_' . RADIO_STATION_LANGUAGES_SLUG, 'taxonomy_noncename' );

	// --- get terms associated with this post ---
	$terms = wp_get_object_terms( get_the_ID(), RADIO_STATION_LANGUAGES_SLUG );

	// --- get all language options ---
	$languages = radio_station_get_languages();

	echo '<div style="margin-bottom: 5px;">';

	// --- get main language ---
	$main_language = radio_station_get_language();
	foreach ( $languages as $i => $language ) {
		if ( strtolower( $main_language['slug'] ) == strtolower( $language['language'] ) ) {
			$label = $language['native_name'];
			if ( $language['native_name'] != $language['english_name'] ) {
				$label .= ' (' . $language['english_name'] . ')';
			}
		}
	}

	if ( isset( $label ) ) {
		echo '<b>' . esc_html( __( 'Main Radio Language', 'radio-station' ) ) . '</b>:<br>';
		echo esc_html( $label ) . '<br>';
	}

	echo '<div style="font-size:11px;">' . esc_html( __( 'Select below if Show language(s) differ.', 'radio-station' ) ) . '</div>';

	echo '<ul id="' . esc_attr( RADIO_STATION_LANGUAGES_SLUG ) . '_taxradiolist" data-wp-lists="list:' . esc_attr( RADIO_STATION_LANGUAGES_SLUG ) . '_tax" class="categorychecklist form-no-clear">';

	// --- loop existing terms ---
	$term_slugs = array();
	foreach ( $terms as $term ) {

		$slug = $term->slug;
		$term_slugs[] = $slug;
		$label = $term->name;
		if ( !empty( $term->description ) ) {
			$label .= ' (' . $term->description . ')';
		}

		echo '<li id="' .  esc_attr( RADIO_STATION_LANGUAGES_SLUG ) . '_tax-' . esc_attr( $slug ) . '">';

		// --- hidden input for term saving ---
		// echo '<input value="' . esc_attr( $name ) . '" type="checkbox" style="display: none;" name="tax_input[' . esc_attr( RADIO_STATION_LANGUAGES_SLUG ) . '][]" id="in-' . RADIO_STATION_LANGUAGES_SLUG . '_tax-' . esc_attr( $name ) . '" checked="checked">';
		echo '<input value="' . esc_attr( $slug ) . '" type="hidden" name="' . esc_attr( RADIO_STATION_LANGUAGES_SLUG ) . '[]" id="in-' . esc_attr( RADIO_STATION_LANGUAGES_SLUG ) . '_tax-' . esc_attr( $slug ) . '">';

		// --- language term label ---
		echo '<label>' . esc_html( $label ) . '</label>';

		// --- remove term button ---
		echo '<input type="button" class="button button-secondary" onclick="radio_remove_language(\'' . esc_attr( $slug ) . '\');" value="x" title="' . esc_attr( __( 'Remove Language', 'radio-station' ) ) . '">';

		echo '</li>';
	}
	echo '</ul>';

	// --- new language selection list ---
	echo '<select id="rs-add-language-selection" onchange="radio_add_language();">';
	echo '<option selected="selected">' . esc_html( __( 'Select Language', 'radio-station' ) ) . '</option>';
	foreach ( $languages as $i => $language ) {
		$code = $language['language'];
		echo '<option value="' . esc_attr( $code ) . '"';
		if ( in_array( strtolower( $code ), $term_slugs ) ) {
			echo ' disabled="disabled"';
		}
		echo '>' . esc_html( $language['native_name'] );
		if ( $language['native_name'] != $language['english_name'] ) {
			echo ' (' . esc_html( $language['english_name'] ) . ')';
		}
		echo '</option>';
	}
	echo '</select><br>';

	// --- add language term button ---
	echo '<div style="font-size:11px;">' . esc_html( __( 'Click on a Language to Add it.', 'radio-station' ) ) . '</div>';

	echo '</div>';

	// --- language selection javascript ---
	$js = "function radio_add_language() {
		/* get and disable selected language item */
		select = document.getElementById('rs-add-language-selection');
		options = select.options;
		for (i = 0; i < options.length; i++) {
			if (options[i].selected) {
				optionvalue = options[i].value;
				optionlabel = options[i].innerHTML;
				options[i].setAttribute('disabled', 'disabled');
			}
		}
		select.selectedIndex = 0;

		/* add item to term list */
		listitem = document.createElement('li');
		listitem.setAttribute('id', '" . esc_js( RADIO_STATION_LANGUAGES_SLUG ) . "_tax-'+optionvalue);
		input = document.createElement('input');
		input.value = optionvalue;
		input.setAttribute('type', 'hidden');
		input.setAttribute('name', '" . esc_js( RADIO_STATION_LANGUAGES_SLUG ) . "[]');
		input.setAttribute('id', 'in-" . esc_js( RADIO_STATION_LANGUAGES_SLUG ) . "_tax-'+optionvalue);
		listitem.appendChild(input);
		label = document.createElement('label');
		label.innerHTML = optionlabel;
		listitem.appendChild(label);
		button = document.createElement('input');
		button.setAttribute('type', 'button');
		button.setAttribute('class', 'button button-secondary');
		button.setAttribute('onclick', 'radio_remove_language(\"'+optionvalue+'\");');
		button.setAttribute('value', 'x');
		listitem.appendChild(button);
		document.getElementById('" . esc_js( RADIO_STATION_LANGUAGES_SLUG ) . "_taxradiolist').appendChild(listitem);
	}
	function radio_remove_language(term) {
		/* remove item from term list */
		listitem = document.getElementById('" . esc_js( RADIO_STATION_LANGUAGES_SLUG ) . "_tax-'+term);
		listitem.parentNode.removeChild(listitem);

		/* re-enable language select option */
		select = document.getElementById('rs-add-language-selection');
		options = select.options;
		for (i = 0; i < options.length; i++) {
			if (options[i].value == term) {
				options[i].removeAttribute('disabled');
			}
		}
	}";

	// --- add script inline ---
	wp_add_inline_script( 'radio-station-admin', $js );

	// --- language input style fixes ---
	echo "<style>#". RADIO_STATION_LANGUAGES_SLUG . "_taxradiolist input.button {
		margin-left: 10px; padding: 0 7px; color: #E00; border-radius: 7px;
	}</style>";
}

// ----------------------------
// Update Language Term on Save
// ----------------------------
// 2.3.0: added to sync language names to language term
add_action( 'save_post', 'radio_station_language_term_filter', 11 );
function radio_station_language_term_filter( $post_id ) {

	// ---- check permissions ---
	if ( !isset( $_POST[RADIO_STATION_LANGUAGES_SLUG] ) ) {return;}
	$check = wp_verify_nonce( $_POST['taxonomy_noncename'], 'taxonomy_' . RADIO_STATION_LANGUAGES_SLUG );
	if ( !$check ) {return;}
	$taxonomy_obj = get_taxonomy( RADIO_STATION_LANGUAGES_SLUG );
	if ( !current_user_can( $taxonomy_obj->cap->assign_terms ) ) {return;}

	// --- loop and set posted terms ---
	$terms = $_POST[RADIO_STATION_LANGUAGES_SLUG];

	$term_ids = array();
	if ( is_array( $terms ) && ( count( $terms ) > 0 ) ) {
		$languages = radio_station_get_languages();
		foreach ( $terms as $i => $term_slug ) {

			foreach ( $languages as $j => $language ) {

				if ( strtolower( $language['language'] ) == strtolower( $term_slug ) ) {

					// --- get existing term ---
					$term = get_term_by( 'slug', $term_slug, RADIO_STATION_LANGUAGES_SLUG );

					// --- set language name and description to the term ---
					if ( $term ) {
						$args = array(
							'slug'         => $term_slug,
							'name'         => $language['native_name'],
							'description ' => $language['english_name'],
						);
						wp_update_term( $term->term_id, RADIO_STATION_LANGUAGES_SLUG, $args );
						$term_ids[] = $term->term_id;
					} else {
						$args = array(
							'slug'        => $term_slug,
							'description' => $language['english_name'],
						);
						$term = wp_insert_term( $language['native_name'], RADIO_STATION_LANGUAGES_SLUG, $args );
						if ( !is_wp_error( $term ) ) {
							$term_ids[] = $term['term_id'];
						}
					}
				}
			}
		}
	}

	// --- set the language terms ---
	wp_set_post_terms( $post_id, $term_ids, RADIO_STATION_LANGUAGES_SLUG );
	// error_log( print_r( $term_ids, true ) , 3, WP_CONTENT_DIR . '/tax-debug.log' );

}


// -----------------
// === Playlists ===
// -----------------

// -------------------------
// Add Playlist Data Metabox
// -------------------------
// --- Add custom repeating meta field for the playlist edit form ---
// (Stores multiple associated values as a serialized string)
// Borrowed and adapted from http://wordpress.stackexchange.com/questions/19838/create-more-meta-boxes-as-needed/19852#19852
add_action( 'add_meta_boxes', 'radio_station_add_playlist_metabox' );
function radio_station_add_playlist_metabox() {
	// 2.2.2: change context to show at top of edit screen
	// 2.3.2: filter top metabox position
	$position = apply_filters( 'radio_station_metabox_position', 'rstop', 'playlist' );
	add_meta_box(
		'radio-station-playlist-metabox',
		__( 'Playlist Entries', 'radio-station' ),
		'radio_station_playlist_metabox',
		RADIO_STATION_PLAYLIST_SLUG,
		$position,
		'high'
	);
}

// ---------------------
// Playlist Data Metabox
// ---------------------
function radio_station_playlist_metabox() {

	global $post, $current_screen;

	// --- add nonce field for verification ---
	wp_nonce_field( 'radio-station', 'playlist_tracks_nonce' );

	// --- get the saved meta as an array ---
	// 2.3.2: set single argument to true
	$entries = get_post_meta( $post->ID, 'playlist', true );

	// --- set button titles ---
	// 2.3.2: added titles for button icons
	$move_up_title = __( 'Move Track Up', 'radio-station' );
	$move_down_title = __( 'Move Track Down', 'radio-station' );
	$duplicate_title = __( 'Duplicate Track', 'radio-station' );
	$remove_title = __( 'Remove Track', 'radio-station' );

	echo '<div id="meta_inner">';

	// 2.3.2: separate track list table
	echo radio_station_playlist_track_table( $entries );

	// --- track save/add buttons ---
	// 2.3.2: change track save from button-primary to button-secondary
	// 2.3.2: added playlist AJAX save button (for existing posts only)
	// 2.3.2: added playlist tracks clear button
	// 2.3.2: added table and track saved message
    echo '<table id="track-table-buttons" width="100%"><tr><td width="33%" align="center">';
    echo '<input type="button" class="clear-tracks button-secondary" value="' . esc_attr( __( 'Clear Tracks', 'radio-station' ) ) . '" onclick="radio_tracks_clear();">';
	echo '</td><td width="33%" align="center">';
    if ( 'add' != $current_screen->action ) {
	    echo '<input type="button" class="save-tracks button-primary" value="' . esc_attr( __( 'Save Tracks', 'radio-station' ) ) . '" onclick="radio_tracks_save();">';
	}
    echo '</td><td width="33%" align="center">';
    echo '<input type="button" class="add-track button-secondary" value="' . esc_attr( __( 'Add Track', 'radio-station' ) ) . '" onclick="radio_track_add();">';
    echo '</td></tr>';
    echo '<tr><td></td><td align="center">';
    echo '<div id="tracks-saving-message" style="display:none;">' . esc_html( __( 'Saving Playlist Tracks...', 'radio-station' ) ) . '</div>';
    echo '<div id="tracks-saved-message" style="display:none;">' . esc_html( __( 'Playlist Tracks Saved.', 'radio-station' ) ) . '</div>';
    echo '<div id="tracks-error-message" style="display:none;"></div>';
    echo '</td><td></td></tr></table>';

    echo '<div style="clear: both;"></div>';

    // --- move new tracks message ---
    // 2.3.2: added new track move message
    echo '<center>' . __( 'Tracks marked New are moved to the end of Playlist on update.', 'radio-station' ) . '</center>';

	// --- clear all tracks function ---
	$confirm_clear = __( 'Are you sure you want to clear the track list?', 'radio-station' );
	$js = "function radio_tracks_clear() {
		if (jQuery('#track-table tr').length) {
			var agree = confirm('" . esc_js( $confirm_clear ) . "');
			if (!agree) {return false;}
			jQuery('#track-table tr').remove();
			trackcount = 1;
		}
	}" . PHP_EOL;

	// --- save tracks via AJAX ---
	// 2.3.2: added form input cloning to save playlist tracks
	$ajaxurl = admin_url( 'admin-ajax.php' );
	$js .= "function radio_tracks_save() {
		jQuery('#track-save-form, #track-save-frame').remove();
		form = '<form id=\"track-save-form\" method=\"post\" action=\"" . esc_url( $ajaxurl ) . "\" target=\"track-save-frame\">';
		form += '<input type=\"hidden\" name=\"action\" value=\"radio_station_playlist_save_tracks\"></form>';
		jQuery('#wpbody').append(form);
		if (!jQuery('#track-save-frame').length) {
			frame = '<iframe id=\"track-save-frame\" name=\"track-save-frame\" src=\"javascript:void(0);\" style=\"display:none;\"></iframe>';
			jQuery('#wpbody').append(frame);
		}
		/* copy tracklist input fields and nonce */
		jQuery('#track-table input').each(function() {jQuery(this).clone().appendTo('#track-save-form');});
		jQuery('#track-table select').each(function() {
			name = jQuery(this).attr('name'); value = jQuery(this).children('option:selected').val();
			jQuery('<input type=\"hidden\" name=\"'+name+'\" value=\"'+value+'\">').appendTo('#track-save-form');
		});
		jQuery('#playlist_tracks_nonce').clone().attr('id','').appendTo('#track-save-form');
		jQuery('#post_ID').clone().attr('id','').attr('name','playlist_id').appendTo('#track-save-form');
		jQuery('#tracks-saving-message').show();
		jQuery('#track-save-form').submit();
	}" . PHP_EOL;

	// --- move track up or down ---
	// 2.3.2: added move track function
	$js .= "function radio_track_move(updown, n) {
		/* swap track rows */
		if (updown == 'up') {
			m = n - 1;
			jQuery('#track-'+n+'-rowa').insertBefore('#track-'+m+'-rowa');
			jQuery('#track-'+n+'-rowb').insertAfter('#track-'+n+'-rowa');
			/* jQuery('#track-'+n+'-rowc').insertAfter('#track-'+n+'-rowb'); */
		}
		if (updown == 'down') {
			m = n + 1;
			jQuery('#track-'+n+'-rowa').insertAfter('#track-'+m+'-rowb');
			jQuery('#track-'+n+'-rowb').insertAfter('#track-'+n+'-rowa');
			/* jQuery('#track-'+n+'-rowc').insertAfter('#track-'+n+'-rowb'); */
		}
		/* reset track classes */
		radio_track_classes();

		/* swap track count */
		jQuery('#track-'+n+'-rowa .track-count').html(m);
		jQuery('#track-'+m+'-rowa .track-count').html(n);

		/* swap input name keys */
		jQuery('#track-'+n+'-rowa input, #track-'+n+'-rowb input, #track-'+n+'-rowb select').each(function() {
			jQuery(this).attr('name', jQuery(this).attr('name').replace('['+n+']', '['+m+']'));
		});
		jQuery('#track-'+m+'-rowa input, #track-'+m+'-rowb input, #track-'+m+'-rowb select').each(function() {
			jQuery(this).attr('name', jQuery(this).attr('name').replace('['+m+']', '['+n+']'));
		});

		/* swap button actions */
		jQuery('#track-'+n+'-rowb .track-arrow-up').attr('onclick', 'radio_track_move(\"up\", '+m+');');
		jQuery('#track-'+n+'-rowb .track-arrow-down').attr('onclick', 'radio_track_move(\"down\", '+m+');');
		jQuery('#track-'+m+'-rowb .track-arrow-up').attr('onclick', 'radio_track_move(\"up\", '+n+');');
		jQuery('#track-'+m+'-rowb .track-arrow-down').attr('onclick', 'radio_track_move(\"down\", '+n+');');
		jQuery('#track-'+n+'-rowb .track-duplicate').attr('onclick','radio_track_duplicate('+m+');');
		jQuery('#track-'+n+'-rowb .track-remove').attr('onclick','radio_track_remove('+m+');');
		jQuery('#track-'+m+'-rowb .track-duplicate').attr('onclick','radio_track_duplicate('+n+');');
		jQuery('#track-'+m+'-rowb .track-remove').attr('onclick','radio_track_remove('+n+');');

		/* swap row IDs */
		jQuery('#track-'+m+'-rowa').attr('id', 'track-0-rowa');
		jQuery('#track-'+m+'-rowb').attr('id', 'track-0-rowb');
		jQuery('#track-'+n+'-rowa').attr('id', 'track-'+m+'-rowa');
		jQuery('#track-'+n+'-rowb').attr('id', 'track-'+m+'-rowb');
		jQuery('#track-0-rowa').attr('id', 'track-'+n+'-rowa');
		jQuery('#track-0-rowb').attr('id', 'track-'+n+'-rowb');
	}" . PHP_EOL;

	// --- reset first and last track classes ---
	$js .= "function radio_track_classes() {
		jQuery('.track-rowa, .track-rowb, .track-rowc').removeClass('first-track').removeClass('last-track');
		jQuery('.track-rowa').first().addClass('first-track'); jQuery('.track-rowa').last().addClass('last-track');
		jQuery('.track-rowb').first().addClass('first-track'); jQuery('.track-rowb').last().addClass('last-track');
		/* jQuery('.track-rowc').first().addClass('first-track'); jQuery('.track-rowc').last().addClass('last-track'); */
	}" . PHP_EOL;

	// --- add track function ---
	// 2.3.0: set javascript as string to enqueue
	// 2.3.2: added missing track-meta cell class
	// 2.3.2: added track move arrows
	// 2.3.2: added first and last row classes
	// 2.3.2: set to standalone onclick function
	$js .= "function radio_track_add() {
		if (trackcount == 1) {classes = 'first-track last-track';} else {classes = 'last-track';}
		output = '<tr id=\"track-'+trackcount+'-rowa\" class=\"track-rowa '+classes+'\">';
			output += '<td><span class=\"track-count\">'+trackcount+'</span></td>';
			output += '<td><input type=\"text\" name=\"playlist['+trackcount+'][playlist_entry_artist]\" value=\"\" style=\"width:150px;\"></td>';
			output += '<td><input type=\"text\" name=\"playlist['+trackcount+'][playlist_entry_song]\" value=\"\" style=\"width:150px;\"></td>';
			output += '<td><input type=\"text\" name=\"playlist['+trackcount+'][playlist_entry_album]\" value=\"\" style=\"width:150px;\"></td>';
			output += '<td><input type=\"text\" name=\"playlist['+trackcount+'][playlist_entry_label]\" value=\"\" style=\"width:150px;\"></td>';
		output += '</tr>';
		output += '<tr id=\"track-'+trackcount+'-rowb\" class=\"track-rowb '+classes+'\">';
			output += '<td colspan=\"3\">" . esc_js( __( 'Comments', 'radio-station' ) ) . ": <input type=\"text\" name=\"playlist['+trackcount+'][playlist_entry_comments]\" value=\"\" style=\"width:300px;\"></td>';
			output += '<td class=\"track-meta\"><div>" . esc_js( __( 'New', 'radio-station' ) ) . ":</div>';
			output += '<div><input type=\"checkbox\" name=\"playlist['+trackcount+'][playlist_entry_new]\"></div>';
			output += '<div style=\"margin-left:5px;\">" . esc_js( __( 'Status', 'radio-station' ) ) . ":</div>';
			output += '<div><select name=\"playlist['+trackcount+'][playlist_entry_status]\">';
				output += '<option value=\"queued\">" . esc_js( __( 'Queued', 'radio-station' ) ) . "</option>';
				output += '<option value=\"played\">" . esc_js( __( 'Played', 'radio-station' ) ) . "</option>';
			output += '</select></div></td>';
			output += '<td class=\"track-controls\">';
				output += '<div class=\"track-move\">" . esc_js( __( 'Move', 'radio-station') ) . "</div>: ';
				output += '<div class=\"track-arrow-up\" onclick=\"radio_track_move(\'up\', '+trackcount+');\" title=\"" . esc_js( $move_up_title ) . "\">&#9652</div>';
				output += '<div class=\"track-arrow-down\" onclick=\"radio_track_move(\'down\', '+trackcount+');\" title=\"" . esc_js( $move_down_title ) . "\">&#9662</div>';
				output += '<div class=\"track-remove dashicons dashicons-no\" title=\"" . esc_js( $remove_title ) . "\" onclick=\"radio_track_remove('+trackcount+');\"></div>';
				output += '<div class=\"track-duplicate dashicons dashicons-admin-page\" title=\"" . esc_js( $duplicate_title ) . "\" onclick=\"radio_track_duplicate('+trackcount+')\"></div>';
			output += '</td>';
		output += '</tr>';

		/* output += '<tr id=\"track-'+trackcount+'-rowc\" class=\"track-rowc '+classes+'\">';
		output += '</tr>'; */

		jQuery('#track-table').append(output);
		trackcount++;
		radio_track_classes();
		return false;
	}" . PHP_EOL;

	// --- duplicate track function ---
	$js .= "function radio_track_duplicate(id) {
		var i; var nextid = id + 1;
		/* shift rows down */
		for (i = trackcount; i > id; i--) {
			jQuery('#track-'+i+'-rowa, #track-'+i+'-rowb').each(function() {
				jQuery(this).attr('id', jQuery(this).attr('id').replace(i, (i+1)));
				jQuery(this).find('.track-count').html(i+1);
				jQuery(this).find('input, select').each(function() {
					jQuery(this).attr('name', jQuery(this).attr('name').replace('['+i+']', '['+(i+1)+']'));
				});
				jQuery(this).find('.track-arrow-up').attr('onclick','radio_track_move(\"up\",'+(i+1)+');');
				jQuery(this).find('.track-arrow-down').attr('onclick','radio_track_move(\"down\",'+(i+1)+');');
				jQuery(this).find('.track-duplicate').attr('onclick','radio_track_duplicate('+(i+1)+');');
				jQuery(this).find('.track-remove').attr('onclick','radio_track_remove('+(i+1)+');');
			});
		}
		/* add duplicate row */
		jQuery('#track-'+id+'-rowa').clone().attr('id','track-'+nextid+'-rowa').insertAfter('#track-'+id+'-rowb');
		jQuery('#track-'+id+'-rowb').clone().attr('id','track-'+nextid+'-rowb').insertAfter('#track-'+nextid+'-rowa');
		jQuery('#track-'+nextid+'-rowa .track-count').html(nextid);
		jQuery('#track-'+nextid+'-rowa, #track-'+nextid+'-rowb').each(function() {
			jQuery(this).find('input, select').each(function() {
				jQuery(this).attr('name', jQuery(this).attr('name').replace('['+id+']', '['+nextid+']'));
			});
			jQuery(this).find('.track-arrow-up').attr('onclick','radio_track_move(\"up\", '+nextid+');');
			jQuery(this).find('.track-arrow-down').attr('onclick','radio_track_move(\"down\", '+nextid+');');
			jQuery(this).find('.track-duplicate').attr('onclick','radio_track_duplicate('+nextid+');');
			jQuery(this).find('.track-remove').attr('onclick','radio_track_remove('+nextid+');');
		});
		radio_track_classes();
		trackcount++;
	}" . PHP_EOL;

	// --- remove track function ---
	// 2.3.2: reset first and last classes on remove
	// 2.3.2: set to standalone onclick function
	$js .= "function radio_track_remove(id) {
		jQuery('#track-'+id+'-rowa, #track-'+id+'-rowb, #track-'+id+'-rowc').remove();
		radio_track_classes(); trackcount--;

		/* renumber track count */
		var tcount = 1;
		jQuery('.track-rowa').each(function() {
			jQuery(this).find('.track-count').html(tcount); tcount++;
		});
	}" . PHP_EOL;

	// --- set track count ---
	// 2.3.2: set count from row count length
	// 2.3.2: removed document ready wrapper
	$js .= "var trackcount = jQuery('.track-rowa').length + 1;";

	// --- enqueue inline script ---
	// 2.3.0: enqueue instead of echoing
	wp_add_inline_script( 'radio-station-admin', $js );

	// --- track list styles ---
	// 2.3.0: added track meta style fix
	// 2.3.2: added track meta select font size fix
	// 2.3.2: added track move arrow styles
	// 2.3.2: added table buttons styling
	// 2.3.2: added track save message styling
	echo '<style>.track-meta div {display: inline-block; margin-right: 3px;}
	.track-meta select {font-size: 12px;}
	.track-arrow-up, .track-arrow-down {font-size: 32px; line-height: 24px; cursor: pointer;}
	tr.first-track .track-arrow-up, tr.last-track .track-arrow-down {display: none;}
	tr.first-track .track-arrow-down {margin-left: 20px;}
	.track-controls .track-arrow-up, .track-controls .track-arrow-down,
	.track-controls .track-move, .track-controls .remove-track, .track-controls .duplicate-track {display: inline-block; vertical-align: middle;}
	.track-controls .track-duplicate, .track-controls .track-remove {float: right; margin-right: 15px; cursor: pointer;}
	#track-table-buttons {margin-top: 20px;}
	#track-table-buttons .clear-tracks, #track-table-buttons .save-tracks, #track-table-buttons .add-track {
		cursor: pointer; display: block; width: 120px; padding: 8px; text-align: center; line-height: 1em;}
	#tracks-saving-message, #tracks-saved-message {
		background-color: lightYellow; border: 1px solid #E6DB55; margin-top: 10px; font-weight: bold; width: 170px; padding: 5px 0;}
	</style>';

	// --- close meta inner ---
	echo '</div>';

	// 2.3.2: removed publish button duplication
	// (replaced with track save AJAX button)
}

// ----------------
// Track List Table
// ----------------
// 2.3.2: separated tracklist table (for AJAX)
function radio_station_playlist_track_table( $entries ) {

	// --- open track table ---
	echo '<table id="track-table" class="widefat">';
	echo '<tr>';
	echo '<th></th><th><b>' . esc_html( __( 'Artist', 'radio-station' ) ) . '</b></th>';
	echo '<th><b>' . esc_html( __( 'Song', 'radio-station' ) ) . '</b></th>';
	echo '<th><b>' . esc_html( __( 'Album', 'radio-station' ) ) . '</b></th>';
	echo '<th><b>' . esc_html( __( 'Record Label', 'radio-station' ) ) . '</th>';
	echo '</tr>';

	// --- set button titles ---
	// 2.3.2: added titles for icon buttons
	$move_up_title = __( 'Move Track Up', 'radio-station' );
	$move_down_title = __( 'Move Track Down', 'radio-station' );
	$duplicate_title = __( 'Duplicate Track', 'radio-station' );
	$remove_title = __( 'Remove Track', 'radio-station' );

	// 2.3.2: removed [0] array key
	$c = 1;
	if ( isset( $entries ) && !empty( $entries ) ) {

		foreach ( $entries as $track ) {
			if ( isset( $track['playlist_entry_artist'] ) || isset( $track['playlist_entry_song'] )
			     || isset( $track['playlist_entry_album'] ) || isset( $track['playlist_entry_label'] )
			     || isset( $track['playlist_entry_comments'] ) || isset( $track['playlist_entry_new'] )
			     || isset( $track['playlist_entry_status'] ) ) {

				// --- track row a ---
				$class = '';
				if ( 1 == $c ) {
					$class = 'first-track';
				} elseif ( $c == count( $entries ) ) {
					$class = 'last-track';
				}
				echo '<tr id="track-' . esc_attr( $c ) . '-rowa" class="track-rowa ' . esc_attr( $class ) . '">';

				// --- track count ---
				echo '<td><span class="track-count">' . esc_html( $c ) . '</span></td>';

				// --- track entry inputs ---
				echo '<td><input type="text" name="playlist[' . esc_attr( $c ) . '][playlist_entry_artist]" value="' . esc_attr( $track['playlist_entry_artist'] ) . '" style="width:150px;"></td>';
				echo '<td><input type="text" name="playlist[' . esc_attr( $c ) . '][playlist_entry_song]" value="' . esc_attr( $track['playlist_entry_song'] ) . '" style="width:150px;"></td>';
				echo '<td><input type="text" name="playlist[' . esc_attr( $c ) . '][playlist_entry_album]" value="' . esc_attr( $track['playlist_entry_album'] ) . '" style="width:150px;"></td>';
				echo '<td><input type="text" name="playlist[' . esc_attr( $c ) . '][playlist_entry_label]" value="' . esc_attr( $track['playlist_entry_label'] ) . '" style="width:150px;"></td>';
				echo '</tr>';

				// --- track row b ---
				echo '<tr id="track-' . esc_attr( $c ) . '-rowb" class="track-rowb ' . esc_attr( $class ) . '">';

				// --- track comments ---
				echo '<td colspan="3">' . esc_html__( 'Comments', 'radio-station' ) . ' ';
				echo '<input type="text" name="playlist[' . esc_attr( $c ) . '][playlist_entry_comments]" value="' . esc_attr( $track['playlist_entry_comments'] ) . '" style="width:300px;"></td>';

				// --- track meta ---
				echo '<td class="track-meta">';
				echo '<div>' . esc_html( __( 'New', 'radio-station' ) ) . ':</div>';
				// 2.3.2: remove new value checking as now used and cleared on save
				// $track['playlist_entry_new'] = isset( $track['playlist_entry_new'] ) ? $track['playlist_entry_new'] : false;
				// ' . checked( $track['playlist_entry_new'] ) . '
				echo '<div><input type="checkbox" style="display:inline-block;" name="playlist[' . esc_attr( $c ) . '][playlist_entry_new]"></div>';
				echo '<div style="margin-left:5px;">' . esc_html( __( 'Status', 'radio-station' ) ) . ':</div>';
				echo '<div><select name="playlist[' . esc_attr( $c ) . '][playlist_entry_status]">';
				echo '<option value="queued" ' . selected( $track['playlist_entry_status'], 'queued', false ) . '>' . esc_html__( 'Queued', 'radio-station' ) . '</option>';
				echo '<option value="played" ' . selected( $track['playlist_entry_status'], 'played', false ) . '>' . esc_html__( 'Played', 'radio-station' ) . '</option>';
				echo '</select></div></td>';

				// 2.3.2: added move track arrows
				echo '<td class="track-controls">';
				echo '<div class="track-move">' . esc_html( __( 'Move', 'radio-station') ) . ': </div>';
				echo '<div class="track-arrow-up" onclick="radio_track_move(\'up\', ' . esc_attr( $c ) . ');" title="' . esc_attr( $move_up_title ) . '">&#9652</div>';
				echo '<div class="track-arrow-down" onclick="radio_track_move(\'down\', ' . esc_attr( $c ) . ');" title="' . esc_attr( $move_down_title ) . '">&#9662</div>';

				// --- remove track button ---
				echo '<div class="track-remove dashicons dashicons-no" title="' . esc_attr( $remove_title ) . '" onclick="radio_track_remove(' . esc_attr( $c ) . ');"></div>';
				echo '<div class="track-duplicate dashicons dashicons-admin-page" title="' . esc_attr( $duplicate_title ) . '" onclick="radio_track_duplicate(' . esc_attr( $c ) . ');"></div>';
				echo '</td>';
				echo '</tr>';

				// --- track row c ---
				// TODO: add track time / start / end input fields ?
				// echo '<tr id="track-' . esc_attr( $c ) . '-rowc" class="track-rowc ' . esc_attr( $class ) . '">';
				// echo '</tr>';

				$c ++;
			}
		}
	}
	echo '</table>';
}

// -----------------------------------
// Add Assign Playlist to Show Metabox
// -----------------------------------
// (add metabox for assigning playlist to show)
add_action( 'add_meta_boxes', 'radio_station_add_playlist_show_metabox' );
function radio_station_add_playlist_show_metabox() {
	// 2.2.2: add high priority to shift above publish box
	add_meta_box(
		'radio-station-playlist-show-metabox',
		__( 'Linked Show', 'radio-station' ),
		'radio_station_playlist_show_metabox',
		RADIO_STATION_PLAYLIST_SLUG,
		'side',
		'high'
	);
}

// -------------------------------
// Assign Playlist to Show Metabox
// -------------------------------
function radio_station_playlist_show_metabox() {

	global $post, $wpdb;

	$user = wp_get_current_user();

	// --- check that we have at least one show ---
	// 2.3.0: moved up to check for any shows
	$args = array(
		'numberposts' => - 1,
		'offset'      => 0,
		'orderby'     => 'post_title',
		'order'       => 'ASC',
		'post_type'   => RADIO_STATION_SHOW_SLUG,
		'post_status' => 'publish',
	);
	$shows = get_posts( $args );
	if ( count( $shows ) > 0 ) {
		$have_shows = true;
	} else {
		$have_shows = false;
	}

	// --- maybe restrict show selection to user-assigned shows ---
	// 2.2.8: remove strict argument from in_array checking
	// 2.3.0: added check for new Show Editor role
	// 2.3.0: added check for edit_others_shows capability
	if ( !in_array( 'administrator', $user->roles )
	     && !in_array( 'show-editor', $user->roles )
	     && !current_user_can( 'edit_others_shows' ) ) {

		// --- get the user lists for all shows ---
		$allowed_shows = array();
		$query = "SELECT pm.meta_value, pm.post_id FROM " . $wpdb->prefix . "postmeta pm";
		$query .= "	WHERE pm.meta_key = 'show_user_list'";
		$show_user_lists = $wpdb->get_results( $query );

		// ---- check each list for the current user ---
		foreach ( $show_user_lists as $user_list ) {

			$user_list->meta_value = maybe_unserialize( $user_list->meta_value );

			// --- if a list has no users, unserialize() will return false instead of an empty array ---
			// (fix that to prevent errors in the foreach loop)
			if ( !is_array( $user_list->meta_value ) ) {
				$user_list->meta_value = array();
			}

			// --- only include shows the user is assigned to ---
			foreach ( $user_list->meta_value as $user_id ) {
				if ( $user->ID === $user_id ) {
					$allowed_shows[] = $user_list->post_id;
				}
			}
		}

		$args = array(
			'numberposts' => - 1,
			'offset'      => 0,
			'orderby'     => 'post_title',
			'order'       => 'aSC',
			'post_type'   => RADIO_STATION_SHOW_SLUG,
			'post_status' => 'publish',
			'include'     => implode( ',', $allowed_shows ),
		);

		$shows = get_posts( $args );
	}

	echo '<div id="meta_inner">';
	if ( !$have_shows ) {
		echo esc_html( __( 'No Shows were found.', 'radio-station' ) );
	} else {
		if ( count( $shows ) < 1 ) {
			echo esc_html( __( 'You are not assigned to any Shows.', 'radio-station' ) );
		} else {
			// --- add nonce field for verification ---
			wp_nonce_field( 'radio-station', 'playlist_show_nonce' );

			// --- select show to assign playlist to ---
			$current = get_post_meta( $post->ID, 'playlist_show_id', true );
			echo '<select name="playlist_show_id">';
			echo '<option value="" ' . selected( $current, false, false ) . '>' . esc_html__( 'Unassigned', 'radio-station' ) . '</option>';
			foreach ( $shows as $show ) {
				echo '<option value="' . esc_attr( $show->ID ) . '" ' . selected( $show->ID, $current, false ) . '>' . esc_html( $show->post_title ) . '</option>';
			}
			echo '</select>';
		}
	}
	echo '</div>';
}

// --------------------
// Update Playlist Data
// --------------------
// --- When a playlist is saved, saves our custom data ---
// 2.3.2: added action for AJAX save of tracks
add_action( 'wp_ajax_radio_station_playlist_save_tracks', 'radio_station_playlist_save_data' );
add_action( 'save_post', 'radio_station_playlist_save_data' );
function radio_station_playlist_save_data( $post_id ) {

	// --- verify if this is an auto save routine ---
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// --- make sure we have a post ID for AJAX save ---
	// 2.3.2: added AJAX track saving checks
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		// 2.3.3: added double check for AJAX action match
		if ( !isset( $_REQUEST['action'] ) || ( 'radio_station_playlist_save_tracks' != $_REQUEST['action'] ) ) {
			return;
		}
		if ( !isset( $_POST['playlist_id'] ) || ( '' == $_POST['playlist_id'] ) ) {
			return;
		}
		$post_id = absint( $_POST['playlist_id'] );
		$post = get_post( $post_id );

		$error = false;
		if ( !isset( $_POST['playlist_tracks_nonce'] ) || !wp_verify_nonce( $_POST['playlist_tracks_nonce'], 'radio-station' ) ) {
			$error = __( 'Expired. Publish or Update instead.', 'radio-station' );
		} elseif ( !$post ) {
			$error = __( 'Failed. Invalid Playlist ID.', 'radio-station' );
		} elseif ( !current_user_can( 'edit_playlists' ) ) {
			$error = __( 'Failed. Publish or Update instead.', 'radio-station' );
		}

		// --- send error message to parent window ---
		if ( $error ) {
			echo "<script>parent.document.getElementById('tracks-saving-message').style.display = 'none';
			parent.document.getElementById('tracks-error-message').style.display = '';
			parent.document.getElementById('tracks-error-message').innerHTML = '" . esc_js( $error ) . "';
			form = parent.document.getElementById('track-save-form'); form.parentNode.removeChild(form);
			</script>";

			exit;
		}
	}

	// --- save playlist tracks ---
	if ( isset( $_POST['playlist'] ) ) {

		// --- verify playlist nonce ---
		// 2.3.2: fix OR condition to AND condition
		if ( isset( $_POST['playlist_tracks_nonce'] )
		     && wp_verify_nonce( $_POST['playlist_tracks_nonce'], 'radio-station' ) ) {

			$playlist = isset( $_POST['playlist'] ) ? $_POST['playlist'] : array();

			// move songs that are still queued to the end of the list so that order is maintained
			foreach ( $playlist as $i => $song ) {
				// 2.3.2: move songs marked as new to the end instead of queued
				// if ( 'queued' === $song['playlist_entry_status'] ) {
				if ( $song['playlist_entry_new'] ) {
					// 2.3.2: unset before adding to maintain (now ordered) track count
					// 2.3.2: unset new flag from track record now it has been moved
					unset( $playlist[$i] );
					unset( $song['playlist_entry_new'] );
					$playlist[] = $song;
				}
			}
			update_post_meta( $post_id, 'playlist', $playlist );
		}
	}

	// --- sanitize and save related show ID ---
	// 2.3.0: check for changes in related show ID
	if ( isset( $_POST['playlist_show_id'] ) ) {

		// --- verify playlist related to show nonce ---
		if ( isset( $_POST['playlist_show_nonce'] )
		     && wp_verify_nonce( $_POST['playlist_show_nonce'], 'radio-station' ) ) {

			$changed = false;
			$prev_show = get_post_meta( $post_id, 'playlist_show_id', true );
			$show = $_POST['playlist_show_id'];
			if ( empty( $show ) ) {
				delete_post_meta( $post_id, 'playlist_show_id' );
				if ( $prev_show ) {
					$show = $prev_show;
					$changed = true;
				}
			} else {
				$show = absint( $show );
				if ( ( $show > 0 ) && ( $show != $prev_show ) ) {
					update_post_meta( $post_id, 'playlist_show_id', $show );
					$changed = true;
				}
			}

			// 2.3.0: maybe clear cached data to be safe
			// 2.3.3: remove current show transient
			// 2.3.4: add previous show transient
			if ( $changed ) {
				delete_transient( 'radio_station_current_schedule' );
				delete_transient( 'radio_station_next_show' );
				delete_transient( 'radio_station_previous_show' );

				// 2.3.4: delete all prefixed transients (for times)
				radio_station_delete_transients_with_prefix( 'radio_station_current_schedule' );
				radio_station_delete_transients_with_prefix( 'radio_station_next_show' );
				radio_station_delete_transients_with_prefix( 'radio_station_previous_show' );

				do_action( 'radio_station_clear_data', 'show_meta', $show );
			}
		}
	}

	// --- AJAX saving ---
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		if ( isset( $_POST['action'] ) && ( 'radio_station_playlist_save_tracks' == $_POST['action'] ) ) {

			// --- display tracks saved message ---
			$playlist_tracks_nonce = wp_create_nonce( 'radio-station' );
			echo "<script>parent.document.getElementById('tracks-saving-message').style.display = 'none';
			parent.document.getElementById('tracks-saved-message').style.display = '';
			setTimeout(function() {parent.document.getElementById('tracks-saved-message').style.display = 'none';}, 5000);
			form = parent.document.getElementById('track-save-form'); form.parentNode.removeChild(form);
			parent.document.getElementById('playlist_tracks_nonce').value = '" . esc_js( $playlist_tracks_nonce ) . "';
			</script>";

			// --- refresh track list table ---
			$entries = get_post_meta( $post_id, 'playlist', true );
			echo radio_station_playlist_track_table( $entries );
			echo "<script>tracktable = parent.document.getElementById('track-table');
			tracktable.innerHTML = document.getElementById('track-table').innerHTML;</script>";

			exit;
		}
	}
}

// --------------------
// Relogin AJAX Message
// --------------------
// 2.3.2: added for show shifts and playlist tracks AJAX
add_action( 'wp_ajax_nopriv_radio_station_show_save_shifts', 'radio_station_relogin_message' );
add_action( 'wp_ajax_nopriv_radio_station_playlist_save_tracks', 'radio_station_relogin_message' );
function radio_station_relogin_message() {

	if ( 'radio_station_show_save_shifts' == $_REQUEST['action'] ) {
		$type = 'shift';
	} elseif ( 'station_playlist_save_tracks' == $_REQUEST['action'] ) {
		$type = 'track';
	}

	// --- send relogin message
	$error = __( 'Failed. Relogin in try again.', 'radio-station' );
	echo "<script>parent.document.getElementById('" . $type . "s-saving-message').style.display = 'none';
	parent.document.getElementById('" . $type . "s-error-message').style.display = '';
	parent.document.getElementById('" . $type . "s-error-message').innerHTML = '" . esc_js( $error ) . "';
	form = parent.document.getElementById('" . $type . "-save-form'); form.parentNode.removeChild(form);
	</script>";

	exit;
}

// -------------------------
// Add Playlist List Columns
// -------------------------
// 2.2.7: added data columns to playlist list display
add_filter( 'manage_edit-' . RADIO_STATION_PLAYLIST_SLUG . '_columns', 'radio_station_playlist_columns', 6 );
function radio_station_playlist_columns( $columns ) {
	if ( isset( $columns['thumbnail'] ) ) {
		unset( $columns['thumbnail'] );
	}
	if ( isset( $columns['post_thumb'] ) ) {
		unset( $columns['post_thumb'] );
	}
	$date = $columns['date'];
	unset( $columns['date'] );
	$comments = $columns['comments'];
	unset( $columns['comments'] );
	$columns['show'] = esc_attr( __( 'Show', 'radio-station' ) );
	$columns['trackcount'] = esc_attr( __( 'Tracks', 'radio-station' ) );
	$columns['tracklist'] = esc_attr( __( 'Track List', 'radio-station' ) );
	$columns['comments'] = $comments;
	$columns['date'] = $date;

	return $columns;
}

// -------------------------
// Playlist List Column Data
// -------------------------
// 2.2.7: added data columns for show list display
add_action( 'manage_' . RADIO_STATION_PLAYLIST_SLUG . '_posts_custom_column', 'radio_station_playlist_column_data', 5, 2 );
function radio_station_playlist_column_data( $column, $post_id ) {
	$tracks = get_post_meta( $post_id, 'playlist', true );
	if ( 'show' == $column ) {
		$show_id = get_post_meta( $post_id, 'playlist_show_id', true );
		$post = get_post( $show_id );
		echo "<a href='" . esc_url( get_edit_post_link( $post->ID ) ) . "'>" . esc_html( $post->post_title ) . "</a>";
	} elseif ( 'trackcount' == $column ) {
		echo count( $tracks );
	} elseif ( 'tracklist' == $column ) {
		echo '<a href="javascript:void(0);" onclick="showhidetracklist(\'' . esc_js( $post_id ) . '\')">';
		echo esc_html( __( 'Show/Hide Tracklist', 'radio-station' ) ) . "</a><br>";
		echo '<div id="tracklist-' . esc_attr( $post_id ) . '" style="display:none;">';
		echo '<table class="tracklist-table" cellpadding="0" cellspacing="0">';
		echo '<tr><td class="tracklist-heading"><b>#</b></td>';
		echo '<td><b>' . esc_html( __( 'Song', 'radio-station' ) ) . '</b></td>';
		echo '<td><b>' . esc_html( __( 'Artist', 'radio-station' ) ) . '</b></td>';
		echo '<td><b>' . esc_html( __( 'Status', 'radio-station' ) ) . '</b></td></tr>';
		foreach ( $tracks as $i => $track ) {
			echo '<tr><td class="tracklist-count">' . $i . '</td>';
			echo '<td class="tracklist-song">' . esc_html( $track['playlist_entry_song'] ) . '</td>';
			echo '<td class="tracklist-artist">' . esc_html( $track['playlist_entry_artist'] ) . '</td>';
			$status = $track['playlist_entry_status'];
			$status = strtoupper( substr( $status, 0, 1 ) ) . substr( $status, 1, strlen( $status ) );
			echo '</td>';
			echo '<td class="tracklist-status">' . esc_html( $status ) . '</td></tr>';
		}
		echo '</table></div>';
	}
}

// ---------------------------
// Playlist List Column Styles
// ---------------------------
add_action( 'admin_footer', 'radio_station_playlist_column_styles' );
function radio_station_playlist_column_styles() {
	$currentscreen = get_current_screen();
	if ( 'edit-' . RADIO_STATION_PLAYLIST_SLUG !== $currentscreen->id ) {
		return;
	}

	// --- playlist list styles ---
	echo "<style>#show {width: 100px;}
	#trackcount {width: 35px; font-size: 12px;}
	#tracklist {width: 250px;}
	.tracklist-table {width: 350px;}
	.tracklist-table td {padding: 0px 10px;}</style>";

	// --- expand/collapse tracklist data ---
	$js = "function showhidetracklist(postid) {
		if (document.getElementById('tracklist-'+postid).style.display == 'none') {
			document.getElementById('tracklist-'+postid).style.display = '';
		} else {document.getElementById('tracklist-'+postid).style.display = 'none';}
	}";

	// --- enqueue script inline ---
	// 2.3.0: enqueue instead of echo
	wp_add_inline_script( 'radio-station-admin', $js );
}


// -------------
// === Posts ===
// -------------

// ------------------------
// Add Related Show Metabox
// ------------------------
// (add metabox for show assignment on blog posts)
add_action( 'add_meta_boxes', 'radio_station_add_post_show_metabox' );
function radio_station_add_post_show_metabox() {

	// 2.3.0: moved check for shows inside metabox

	// ---- add a filter for which post types to show metabox on ---
	$post_types = array( 'post' );
	$post_types = apply_filters( 'radio_station_show_related_post_types', $post_types );

	// --- add the metabox to post types ---
	add_meta_box(
		'radio-station-post-show-metabox',
		__( 'Related to Show', 'radio-station' ),
		'radio_station_post_show_metabox',
		$post_types,
		'side'
	);
}

// --------------------
// Related Show Metabox
// --------------------
function radio_station_post_show_metabox() {

	global $post;

	// 2.3.3.6: store current post global
	$stored_post = $post;

	// --- add nonce field for verification ---
	wp_nonce_field( 'radio-station', 'post_show_nonce' );

	$args = array(
		'numberposts' => - 1,
		'offset'      => 0,
		'orderby'     => 'post_title',
		'order'       => 'ASC',
		'post_type'   => RADIO_STATION_SHOW_SLUG,
		'post_status' => 'publish', // ???
	);
	$shows = get_posts( $args );

	// --- get current selection ---
	$selected = get_post_meta( $post->ID, 'post_showblog_id', true );
	// 2.3.3.4: convert existing selection to array
	if ( !$selected ) {
		$selected = array();
	} elseif ( !is_array( $selected ) ) {
		$selected = array( $selected );
	}
	// 2.3.3.6: remove possible saved zero value
	// 2.3.3.9: fix to duplicate use of selected variable
	if ( count( $selected ) > 0 ) {
		foreach ( $selected as $i => $value ) {
			if ( 0 == $value ) {
				unset( $selected[$i] );
			}
		}
	}

	echo '<div id="meta_inner">';

	if ( count( $shows ) > 0 ) {

		// --- select related show input ---
		// 2.2.3.4: allow for multiple selections
		echo '<select multiple="multiple" name="post_showblog_id[]">';
		echo '<option value="">' . esc_html( __( 'Select Show(s)', 'radio-station') ) . '</option>';

		// --- loop shows for selection options ---
		// 2.3.3.4: check for multiple selections
		foreach ( $shows as $show ) {

			// 2.3.3.6: check capability of user to edit each Show
			// (override global post object temporarily to do this)
			$post = $show;
			echo '<option value="' . esc_attr( $show->ID ) . '"';
			// ' ' . selected( $show->ID, $current, false );
			if ( in_array( $show->ID, $selected ) ) {
				echo ' selected="selected"';
			}
			// 2.2.3.3.6: disable existing but uneditable options
			if ( !current_user_can( 'edit_shows' ) ) {
				echo ' disabled="disabled"';
				if ( in_array( $show->ID, $selected ) ) {
					echo ' class="pre-selected"';
				}
			}
			echo '>' . esc_html( $show->post_title ) . '</option>';
		}
		echo '</select>';
	} else {
		// --- no shows message ---
		echo esc_html( __( 'No Shows to Select.', 'radio-station' ) );
	}
	echo '</div>';

	// --- related shows post box styles ---
	// 2.3.3.6: add style for pre-selected option
	echo "<style>.pre-selected {background-color:#BBB;}</style>";

	// 2.3.3.6: revert current post global
	$post = $stored_post;
}

// -------------------
// Update Related Show
// -------------------
add_action( 'save_post', 'radio_station_post_save_data' );
function radio_station_post_save_data( $post_id ) {

	// --- do not save when doing autosaves ---
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// 2.3.3.6: store post for capability checking
	global $post;
	$stored_post = $post;

	// --- check related show field is set ---
	// 2.3.0: added check if changed
	if ( isset( $_POST['post_showblog_id'] ) ) {

		// ---  verify field save nonce ---
		if ( !isset( $_POST['post_show_nonce'] ) || !wp_verify_nonce( $_POST['post_show_nonce'], 'radio-station' ) ) {
			return;
		}

		// --- get the related show ID ---
		$changed = false;
		$current_shows = get_post_meta( $post_id, 'post_showblog_id', true );
		$show_ids = $_POST['post_showblog_id'];

		// 2.3.3.6: maybe add existing (uneditable) Show IDs
		$new_show_ids = array();
		if ( $current_shows && is_array( $current_shows ) && ( count( $current_shows ) > 0 ) ) {
			foreach ( $current_shows as $current_show ) {
				if ( $current_show > 0 ) {
					$post = get_post( $current_show );
					if ( $post && !current_user_can( 'edit_shows' ) ) {
						$new_show_ids[] = $current_show;
					}
				}
			}
		}

		if ( !empty( $show_ids ) ) {
			// --- sanitize to numeric before updating ---
			// 2.3.3.4: maybe sanitize multiple array values
			if ( !is_array( $show_ids ) ) {
				$show_ids = array( $show_ids );
			}
			foreach ( $show_ids as $i => $show_id ) {
				$show_id = absint( trim( $show_id ) );
				// 2.3.3.6: check show ID value is above zero not -1
				if ( $show_id > 0 ) {
				// 2.3.3.6: check edit Show capability before adding
					$post = get_post( $show_id );
					if ( $post && current_user_can( 'edit_shows' ) && !in_array( $show_id, $new_show_ids ) ) {
						$new_show_ids[] = $show_id;
					}
				}
			}
		}

		// --- delete or update Show IDs for post ---
		// 2.3.3.6: check existing versus new show ID values
		if ( 0 == count( $new_show_ids ) ) {
			delete_post_meta( $post_id, 'post_showblog_id' );
			$changed = true;
		} elseif ( $new_show_ids != $current_shows ) {
			update_post_meta( $post_id, 'post_showblog_id', $new_show_ids );
			$changed = true;
		}

		// 2.3.0: clear cached data to be safe
		// 2.3.3: remove current show transient
		// 2.3.4: add previous show transient
		if ( $changed ) {
			delete_transient( 'radio_station_current_schedule' );
			delete_transient( 'radio_station_next_show' );
			delete_transient( 'radio_station_previous_show' );

			// 2.3.4: delete all prefixed transients (for times)
			radio_station_delete_transients_with_prefix( 'radio_station_current_schedule' );
			radio_station_delete_transients_with_prefix( 'radio_station_next_show' );
			radio_station_delete_transients_with_prefix( 'radio_station_previous_show' );

			do_action( 'radio_station_clear_data', 'show_meta', $show );
		}
	}

	// 2.3.3.6 restore stored post object
	$post = $stored_post;
}

// ------------------------------------
// Related Show Quick Edit Select Input
// ------------------------------------
// 2.3.3.4: added Related Show field to Post List Quick Edit
add_action( 'quick_edit_custom_box', 'radio_station_quick_edit_post', 10, 2 );
function radio_station_quick_edit_post( $column_name, $post_type ) {

	global $post;
	$stored_post = $post;

	// 2.3.3.5: added fix for post type context
	if ( $post_type != 'post' ) {
		return;
	}

	// --- get all shows ---
	$args = array(
		'numberposts' => - 1,
		'offset'      => 0,
		'orderby'     => 'post_title',
		'order'       => 'ASC',
		'post_type'   => RADIO_STATION_SHOW_SLUG,
		'post_status' => 'publish', // ???
	);
	$shows = get_posts( $args );

	echo '<fieldset class="inline-edit-col-right related-show-field">';
		echo '<div class="inline-edit-col column-' . esc_attr( $column_name ) . '">';
			wp_nonce_field( 'radio-station', 'post_show_nonce' );
			echo '<label class="inline-edit-group">';
				echo '<span class="title">' . esc_html( __( 'Related Show(s)', 'radio-station' ) ) . '</span>';
				if ( count( $shows ) > 0 ) {
					echo '<select multiple="multiple" name="post_showblog_id[]" class="select-show">';
					// echo '<option value="">' . esc_html( __( 'Select Show(s)...', 'radio-station' ) ) . '</option>';
					foreach ( $shows as $show ) {
						$post = $show;
						echo '<option value="' . esc_attr( $show->ID ) . '"';
						// 2.3.3.6: disable uneditable show options
						if ( !current_user_can( 'edit_shows' ) ) {
							echo ' disabled="disabled"';
						}
						echo '>' . esc_html( $show->post_title ) . '</option>';
					}
					echo '</select>';
				} else {
					// --- no shows message ---
					echo esc_html( __( 'No Shows available to Select.', 'radio-station' ) );
				}
			echo '</label>';
		echo '</div>';
	echo '</fieldset>';

	// --- related shows post box styles ---
	// 2.3.3.6: add style for pre-selected option
	echo "<style>.pre-selected {background-color:#BBB;}</style>";

	// 2.3.3.6: restore stored post object
	$post = $stored_post;
}

// ---------------------------------
// Add Related Show Post List Column
// ---------------------------------
// 2.3.3.4: added Related Show Column to Post List
add_filter( 'manage_edit-post_columns', 'radio_station_post_columns', 6 );
function radio_station_post_columns( $columns ) {
	$columns['show'] = esc_attr( __( 'Show(s)', 'radio-station' ) );
	return $columns;
}

// ----------------------------------
// Related Show Post List Column Data
// ----------------------------------
// 2.3.3.4: added Related Show Column to Post List
add_action( 'manage_post_posts_custom_column', 'radio_station_post_column_data', 5, 2 );
function radio_station_post_column_data( $column, $post_id ) {
	if ( 'show' == $column ) {

		// 2.3.3.6: store global post object while capability checking
		global $post;
		$stored_post = $post;

		// --- get Shows linked to Post ---
		$data = '';
		$show_ids = $disabled = array();
		$show_id = get_post_meta( $post_id, 'post_showblog_id', true );
		if ( $show_id ) {
			// 2.3.3.6: add check to ignore possible saved zero value
			if ( is_array( $show_id ) ) {
				$show_ids = $show_id;
				foreach ( $show_ids as $i => $show_id ) {
					if ( 0 == $show_id ) {
						unset( $show_ids[$i] );
					}
				}
				// 2.3.3.8: fix to implode show_ids not show_id
				$data = implode( ',', $show_ids );
			} elseif ( $show_id > 0 ) {
				$show_ids = array( $show_id );
				$data = $show_id;
			}
		}

		// --- display Shows linked to post ---
		if ( count( $show_ids ) > 0 ) {
			foreach ( $show_ids as $show_id ) {
				$show = get_post( trim( $show_id ) );
				if ( $show ) {
					// 2.3.3.6: only link to Shows user can edit
					$post = $show;
					if ( current_user_can( 'edit_shows' ) ) {
						echo '<a href="' . get_edit_post_link( $show_id ) . '" title="' . esc_attr( __( 'Edit Show', 'radio-station' ) ) . ' ' . $show_id . '">';
					} else {
						// 2.3.3.6: set disabled (uneditable) data
						$disabled[] = $show_id;
					}
					echo esc_html( $show->post_title ) . '<br>';
					if ( current_user_can( 'edit_shows' ) ) {
						echo '</a>';
					}
				}
			}
		}
		echo '<span class="show-ids" style="display:none;">' . $data . '</span>';
		echo '<span class="disabled-ids" style="display:none;">' . implode( ',', $disabled ) . '</span>';

		// --- restore global post object ---
		$post = $stored_post;
	}
}

// ------------------------------
// Related Show Quick Edit Script
// ------------------------------
// 2.3.3.4: added Related Show Quick Edit value population script
// ref: https://codex.wordpress.org/Plugin_API/Action_Reference/quick_edit_custom_box
// 2.3.3.6: disable uneditable Show select options
add_action( 'admin_enqueue_scripts', 'radio_station_posts_quick_edit_script' );
function radio_station_posts_quick_edit_script( $hook ) {

	if ( 'edit.php' != $hook ) {
		return;
	}

	// 2.3.3.7: use jQuery instead of \$ for better compatibility
	if ( !isset( $_GET['post_type'] ) || ( 'post' == $_GET['post_type'] ) ) {
		$js = "(function($) {
			var \$wp_inline_edit = inlineEditPost.edit;
			inlineEditPost.edit = function( id ) {
				\$wp_inline_edit.apply(this, arguments);
				var post_id = 0; var disabled_ids;
				if (typeof(id) == 'object') {post_id = parseInt(this.getId(id));}
				if (post_id > 0) {
					var show_ids = jQuery('#post-'+post_id+' .column-show .show-ids').text();
					if (show_ids != '') {
						if (show_ids.indexOf(',') > -1) {ids = show_ids.split(',');}
						else {ids = new Array(); ids[0] = show_ids;}
						for (i = 0; i < ids.length; i++) {
							var thisshowid = ids[i];
							jQuery('#edit-'+post_id+' .select-show option').each(function() {
								if (jQuery(this).val() == thisshowid) {jQuery(this).attr('selected','selected');}
							});
						}
						/* disable uneditable options */
						disabled = jQuery('#post-'+post_id+' .column-show .disabled-ids').text();
						if (disabled != '') {
							if (disabled.indexOf(',') > -1) {disabled_ids = disabled.split(',');}
							else {disabled_ids = new Array(); disabled_ids[0] = disabled;}
							jQuery('#edit-'+post_id+' .select-show option').each(function() {
								for (j = 0; j < disabled_ids.length; j++) {
									if (jQuery(this).val() == disabled_ids[j]) {
										jQuery(this).attr('disabled','disabled');
										if (jQuery(this).attr('selected') == 'selected') {jQuery(this).addClass('pre-selected');}
									}
								}
							});
						}
					}
				}
			};
		})(jQuery);";

		wp_add_inline_script( 'radio-station-admin', $js );
	}
}


// --------------------------
// Add Bulk Edit Posts Action
// --------------------------
// 2.3.3.4: add action to Bulk Edit list
// ref: https://dream-encode.com/wordpress-custom-bulk-actions/
add_filter( 'bulk_actions-edit-post', 'radio_station_show_posts_bulk_edit_action' );
function radio_station_show_posts_bulk_edit_action( $bulk_actions ) {
	$bulk_actions['related_show'] = __( 'Set Related Show(s)', 'radio-station' );
	return $bulk_actions;
}

// ----------------------
// Bulk Edit Posts Script
// ----------------------
// 2.3.3.4: add script for Bulk Edit action
add_action( 'admin_enqueue_scripts', 'radio_station_show_posts_bulk_edit_script' );
function radio_station_show_posts_bulk_edit_script( $hook ) {

	if ( 'edit.php' != $hook ) {
		return;
	}

	// 2.3.3.7: use jQuery instead of \$ for better compatibility
	// 2.3.3.7: do not reclone the show field if it already exists
	if ( !isset( $_GET['post_type'] ) || ( 'post' == $_GET['post_type'] ) ) {
		$js = "jQuery(document).ready(function() {
			jQuery('#bulk-action-selector-top, #bulk-action-selector-bottom').on('change', function(e) {
				if (jQuery(this).val() == 'related_show') {
					/* clone the Quick Edit fieldset to after bulk action selector */
					if (!jQuery(this).parent().find('.related-show-field').length) {
						jQuery('.related-show-field').first().clone().insertAfter(jQuery(this));
					}
				} else {
					jQuery(this).find('.related-show-field').remove();
				}
			});
		});";

		wp_add_inline_script( 'radio-station-admin', $js );
	}
}

// -----------------------
// Bulk Edit Posts Handler
// -----------------------
// 2.3.3.4: add handler for bulk edit action
add_filter( 'handle_bulk_actions-edit-post', 'radio_station_posts_bulk_edit_handler', 10, 3 );
function radio_station_posts_bulk_edit_handler( $redirect_to, $action, $post_ids ) {

	global $post;
	$stored_post = $post;

	if ( 'related_show' !== $action ) {
		return $redirect_to;
	} elseif ( !isset($_REQUEST['post_showblog_id'] ) || ( '' == $_REQUEST['post_showblog_id'] ) ) {
		return $redirect_to;
	}

	$show_ids = $_REQUEST['post_showblog_id'];

	// 2.3.3.6: check that user can edit specified Shows
	$posted_show_ids = array();
	if ( count( $show_ids ) > 0 ) {
		foreach ( $show_ids as $show_id ) {
			// 2.3.3.6: added check to ignore zero values
			if ( 0 != $show_id ) {
				$post = get_post( $show_id );
				if ( current_user_can( 'edit_shows' ) ) {
					$posted_show_ids[] = $show_id;
				}
			}
		}
	}

	// --- loop post IDs to update ---
	$updated_post_ids = $failed_post_ids = array();
	foreach ( $post_ids as $post_id ) {
		$post = get_post( $post_id );
		if ( $post ) {

			// 2.3.3.6: keep existing (non-editable) related Shows for post
			$existing_show_ids = array();
			$current_ids = get_post_meta( $post_id, 'post_showblog_id', true );
			if ( $current_ids && is_array( $current_ids ) && ( count( $current_ids ) > 0 ) ) {
				foreach ( $current_ids as $i => $current_id ) {
					// 2.3.3.6: added check to ignore possible zero values
					if ( 0 != $current_id ) {
						$post = get_post( $current_id );
						if ( !current_user_can( 'edit_shows' ) ) {
							$existing_show_ids[] = $current_id;
						}
					}
				}
			}
			$new_show_ids = array_merge( $posted_show_ids, $existing_show_ids );

			// --- update to new show IDs ---
			update_post_meta( $post_id, 'post_showblog_id', $new_show_ids );
			$updated_post_ids[] = $post_id;
		} else {
			$failed_post_ids[] = $post_id;
		}
  	}

	if ( count( $updated_post_ids ) > 0 ) {
		$redirect_to = add_query_arg( 'radio_station_related_show_updated', count( $updated_post_ids ), $redirect_to );
	}
	if ( count( $failed_post_ids ) > 0 ) {
		$redirect_to = add_query_arg( 'radio_station_related_show_failed', count( $failed_post_ids ), $redirect_to );
	}

	// --- restore stored post ---
	$post = $stored_post;

	return $redirect_to;
}

// ----------------------
// Bulk Edit Posts Notice
// ----------------------
// 2.3.3.4: add notice for bulk edit result
add_action( 'admin_notices', 'radio_station_posts_bulk_edit_notice' );
function radio_station_posts_bulk_edit_notice() {
	$updated = $failed = false;
  	if ( isset( $_REQUEST['radio_station_related_show_updated'] ) ) {
    	$updated_ = intval( $_REQUEST['radio_station_related_show_updated'] );
    }
    if ( isset( $_REQUEST['radio_station_related_show_failed'] ) ) {
    	$failed = intval( $_REQUEST['radio_station_related_show_failed'] );
	}
	if ( $updated || $failed ) {

    	echo '<div id="message" class="' . ( $updated_products_count > 0 ? 'updated' : 'error' ) . '">';

		if ( $updated > 0 ) {
			// --- number of posts updated message ---
			echo '<p>';
				$message = __( 'Updated Related Shows for %d Posts.', 'radio_station' );
				$message = sprintf( $message, $updated );
				echo esc_html( $message );
			echo '</p>';
		}
		if ( $failed > 0 ) {
			// --- number of posts failed messsage ---
			echo '<p>';
				$message = __( 'Failed to Update Related Shows for %d Posts.', 'radio-station' );
				$message = sprintf( $message, $failed );
				esc_html( $message );
			echo '</p>';
		}

		echo '</div>';
  	}
}

// -----------------------------
// Related Show Post List Styles
// -----------------------------
// 2.3.3.4: added Related Show Post List styles
add_action( 'admin_footer', 'radio_station_posts_list_styles' );
function radio_station_posts_list_styles() {
	$currentscreen = get_current_screen();
	if ( 'edit-post' !== $currentscreen->id ) {
		return;
	}

	// --- post list styles ---
	echo "<style>.wp-list-table .posts .oclumn-show {max-width: 100px;}
	.inline-edit-col .select-show {min-width: 200px; min-height: 100px;}
	.bulkactions .column-show .title {display: none;}
	</style>";
}


// -------------
// === Shows ===
// -------------

// ---------------------
// Add Show Info Metabox
// ---------------------
add_action( 'add_meta_boxes', 'radio_station_add_show_info_metabox' );
function radio_station_add_show_info_metabox() {
	// 2.2.2: change context to show at top of edit screen
	// 2.3.2: filter top metabox position
	$position = apply_filters( 'radio_station_metabox_position', 'rstop', 'shows' );
	add_meta_box(
		'radio-station-show-info-metabox',
		__( 'Show Information', 'radio-station' ),
		'radio_station_show_info_metabox',
		RADIO_STATION_SHOW_SLUG,
		$position,
		'high'
	);
}

// -----------------
// Show Info Metabox
// -----------------
function radio_station_show_info_metabox() {

	global $post;

	// 2.3.0: added missing nonce field
	wp_nonce_field( 'radio-station', 'show_meta_nonce' );

	// --- get show meta ---
	// 2.3.2: added show download disable switch
	$active = get_post_meta( $post->ID, 'show_active', true );
	$link = get_post_meta( $post->ID, 'show_link', true );
	$email = get_post_meta( $post->ID, 'show_email', true );
	$phone = get_post_meta( $post->ID, 'show_phone', true );
	$file = get_post_meta( $post->ID, 'show_file', true );
	$download = get_post_meta( $post->ID, 'show_download', true );
	$patreon_id = get_post_meta( $post->ID, 'show_patreon', true );

	// added max-width to prevent metabox overflows
	// 2.3.0: removed new lines between labels and fields and changed widths
	// 2.3.2: increase label width to 120px for disable download field label
	echo '<div id="meta_inner">';

		echo '<p><div style="width:120px; display:inline-block;"><label>' . esc_html( __( 'Active', 'radio-station' ) ) . '?</label></div>
		<input type="checkbox" name="show_active" ' . checked( $active, 'on', false ) . '>
		<em>' . esc_html( __( 'Check this box if show is currently active (Show will not appear on programming schedule if unchecked.)', 'radio-station' ) ) . '</em></p>';

		echo '<p><div style="width:120px; display:inline-block;"><label>' . esc_html( __( 'Website Link', 'radio-station' ) ) . ':</label></div>
		<input type="text" name="show_link" size="100" style="max-width:80%;" value="' . esc_url( $link ) . '" /></p>';

		// 2.3.3.6: change text string from DJ / Host email (as maybe multiple hosts)
		echo '<p><div style="width:120px; display:inline-block;"><label>' . esc_html( __( 'Show Email', 'radio-station' ) ) . ':</label></div>
		<input type="text" name="show_email" size="100" style="max-width:80%;" value="' . esc_attr( $email ) . '" /></p>';

		// 2.3.3.6: added Show phone number input field
		echo '<p><div style="width:120px; display:inline-block;"><label>' . esc_html( __( 'Show Phone', 'radio-station' ) ) . ':</label></div>
		<input type="text" name="show_phone" size="100" style="max-width:80%;" value="' . esc_attr( $phone ) . '" /></p>';

		echo '<p><div style="width:120px; display:inline-block;"><label>' . esc_html( __( 'Latest Audio File', 'radio-station' ) ) . ':</label></div>
		<input type="text" name="show_file" size="100" style="max-width:80%;" value="' . esc_attr( $file ) . '" /></p>';

		// 2.3.2: added show download disable field
		echo '<p><div style="width:120px; display:inline-block;"><label>' . esc_html( __( 'Disable Download', 'radio-station' ) ) . '?</label></div>
		<input type="checkbox" name="show_download" ' . checked( $download, 'on', false ) . '></p>';

		// 2.3.0: added patreon page field
		echo '<p><div style="width:120px; display:inline-block;"><label>' . esc_html( __( 'Patreon Page ID', 'radio-station' ) ) . ':</label></div>';
		echo ' https://patreon.com/<input type="text" name="show_patreon" size="80" style="max-width:50%;" value="' . esc_attr( $patreon_id ) . '" /></p>';

		// 2.3.3.5: added action for further custom fields
		do_action( 'radio_station_show_fields', $post->ID, 'show' );

	echo '</div>';

	// --- inside show metaboxes ---
	// 2.3.0: move metaboxes together inside meta
	$inside_metaboxes = array(
		'hosts'     => array(
			'title'    => __( 'Show DJ(s) / Host(s)', 'radio-station' ),
			'callback' => 'radio_station_show_hosts_metabox',
		),
		'producers' => array(
			'title'    => __( 'Show Producer(s)', 'radio-station' ),
			'callback' => 'radio_station_show_producers_metabox',
		),
		'languages' => array(
			'title'    => __( 'Show Language(s)', 'radio-station' ),
			'callback' => 'radio_station_show_language_metabox',
		)
	);

	// --- display inside metaboxes ---
	echo '<div id="show-inside-metaboxes">';
	$i = 1;
	foreach ( $inside_metaboxes as $key => $metabox ) {

		$classes = array( 'postbox' );
		if ( 1 == $i ) {
			$classes[] = 'first';
		} elseif ( count( $inside_metaboxes ) == $i ) {
			$classes[] = 'last';
		}
		$class = implode( ' ', $classes );

		echo '<div id="' . esc_attr( $key ) . '" class="' . esc_attr( $class ) . '">' . "\n";
		$widget_title = $metabox['title'];

		// echo '<button type="button" class="handlediv" aria-expanded="true">';
		// echo '<span class="screen-reader-text">' . esc_html( sprintf( __( 'Toggle panel: %s' ), $metabox['title'] ) ) . '</span>';
		// echo '<span class="toggle-indicator" aria-hidden="true"></span>';
		// echo '</button>';

		// 2.3.2: remove class="hndle" to prevent box sorting
		echo '<h2><span>' . esc_html( $metabox['title'] ) . '</span></h2>';
		echo '<div class="inside">';
			call_user_func( $metabox['callback'] );
		echo "</div>";
		echo "</div>";

		$i ++;
	}
	echo '</div>';

	// --- output inside metabox styles ---
	echo "<style>#show-inside-metaboxes .postbox {display: inline-block !important; min-width: 230px; max-width: 250px; vertical-align: top;}
	#show-inside-metaboxes .postbox.first {margin-right: 20px;}
	#show-inside-metaboxes .postbox.last {margin-left: 20px;}
	#show-inside-metaboxes .postbox select {max-width: 200px;}</style>";
}

// ------------------------------
// Add Assign DJs to Show Metabox
// ------------------------------
// 2.3.0: move inside show meta selection metabox to reduce clutter
// add_action( 'add_meta_boxes', 'radio_station_add_show_hosts_metabox' );
function radio_station_add_show_hosts_metabox() {
	// 2.2.2: add high priority to show at top of edit sidebar
	// 2.3.0: change metabox title from DJs to DJs / Hosts
	add_meta_box(
		'radio-station-show-hosts-metabox',
		__( 'DJs / Hosts', 'radio-station' ),
		'radio_station_show_hosts_metabox',
		RADIO_STATION_SHOW_SLUG,
		'side',
		'high'
	);
}

// ----------------------------
// Assign Hosts to Show Metabox
// ----------------------------
function radio_station_show_hosts_metabox() {

	global $post, $wp_roles, $wpdb;

	// --- add nonce field for verification ---
	wp_nonce_field( 'radio-station', 'show_hosts_nonce' );

	// --- check for DJ / Host roles ---
	// 2.3.0: simplified by using role__in argument
	$args = array(
		'role__in' => array( 'dj', 'administrator' ),
		'orderby'  => 'display_name',
		'order'    => 'ASC'
	);
	$hosts = get_users( $args );

	// --- get the Hosts currently assigned to the show ---
	$current = get_post_meta( $post->ID, 'show_user_list', true );
	if ( !$current ) {
		$current = array();
	}

	// --- move any selected Hosts to the top of the list ---
	foreach ( $hosts as $i => $host ) {
		// 2.2.8: remove strict in_array checking
		if ( in_array( $host->ID, $current ) ) {
			unset( $hosts[$i] ); // unset first, or prepending will change the index numbers and cause you to delete the wrong item
			array_unshift( $hosts, $host );  // prepend the user to the beginning of the array
		}
	}

	// --- Host Selection Input ---
	// 2.2.2: add fix to make DJ multi-select input full metabox width
	echo '<div id="meta_inner">';
		echo '<select name="show_user_list[]" multiple="multiple" style="height: 120px; width: 100%;">';
			echo '<option value=""></option>';
			foreach ( $hosts as $host ) {
				// 2.2.2: set DJ display name maybe with username
				$display_name = $host->display_name;
				if ( $host->display_name !== $host->user_login ) {
					$display_name .= ' (' . $host->user_login . ')';
				}
				// 2.2.7: fix to remove unnecessary third argument
				// 2.2.8: removed unnecessary fix for non-strict check
				echo '<option value="' . esc_attr( $host->ID ) . '"';
				if ( in_array( $host->ID, $current ) ) {
					echo ' selected="selected"';
				}
				echo '>' . esc_html( $display_name ) . '</option>';
			}
        echo '</select>';

	    // --- multiple selection helper text ---
		// 2.3.0: added multiple selection helper text
		echo '<div style="font-size: 10px;">' . esc_html( __( 'Ctrl-Click selects multiple.', 'radio-station' ) ) . '</div>';
	echo '</div>';
}

// ------------------------------------
// Add Assign Producers to Show Metabox
// ------------------------------------
// 2.3.0: move inside show meta selection metabox to reduce clutter
// add_action( 'add_meta_boxes', 'radio_station_add_show_producers_metabox' );
function radio_station_add_show_producers_metabox() {
	add_meta_box(
		'radio-station-show-producers-metabox',
		__( 'Show Producer(s)', 'radio-station' ),
		'radio_station_show_producers_metabox',
		RADIO_STATION_SHOW_SLUG,
		'side',
		'high'
	);
}

// --------------------------------
// Assign Producers to Show Metabox
// --------------------------------
function radio_station_show_producers_metabox() {

	global $post, $wp_roles, $wpdb;

	// --- add nonce field for verification ---
	wp_nonce_field( 'radio-station', 'show_producers_nonce' );

	// --- check for Producer roles ---
	$args = array(
		'role__in' => array( 'producer', 'administrator', 'show-editor' ),
		'orderby'  => 'display_name',
		'order'    => 'ASC'
	);
	$producers = get_users( $args );

	// --- get Producers currently assigned to the show ---
	$current = get_post_meta( $post->ID, 'show_producer_list', true );
	if ( !$current ) {
		$current = array();
	}

	// --- move any selected DJs to the top of the list ---
	foreach ( $producers as $i => $producer ) {
		if ( in_array( $producer->ID, $current ) ) {
			unset( $producers[$i] ); // unset first, or prepending will change the index numbers and cause you to delete the wrong item
			array_unshift( $producers, $producer ); // prepend the user to the beginning of the array
		}
	}

	// --- Producer Selection Input ---
	echo '<div id="meta_inner">';
		echo '<select name="show_producer_list[]" multiple="multiple" style="height: 120px; width: 100%;">';
			echo '<option value=""></option>';
			foreach ( $producers as $producer ) {
				$display_name = $producer->display_name;
				if ( $producer->display_name !== $producer->user_login ) {
					$display_name .= ' (' . $producer->user_login . ')';
				}
				echo '<option value="' . esc_attr( $producer->ID ) . '"';
				if ( in_array( $producer->ID, $current ) ) {
					echo ' selected="selected"';
				}
				echo '>' . esc_html( $display_name ) . '</option>';
			}
		echo '</select>';

		// --- multiple selection helper text ---
		echo '<div style="font-size: 10px;">' . esc_html( __( 'Ctrl-Click selects multiple.', 'radio-station' ) ) . '</div>';
	echo '</div>';
}

// -----------------------
// Add Show Shifts Metabox
// -----------------------
// --- Adds schedule box to show edit screens ---
add_action( 'add_meta_boxes', 'radio_station_add_show_shifts_metabox' );
function radio_station_add_show_shifts_metabox() {
	// 2.2.2: change context to show at top of edit screen
	// 2.3.2: filter top metabox position
	$position = apply_filters( 'radio_station_metabox_position', 'rstop', 'shifts' );
	add_meta_box(
		'radio-station-show-shifts-metabox',
		__( 'Show Schedule', 'radio-station' ),
		'radio_station_show_shifts_metabox',
		RADIO_STATION_SHOW_SLUG,
		$position,
		'low'
	);
}

// -------------------
// Show Shifts Metabox
// -------------------
function radio_station_show_shifts_metabox() {

	global $post, $current_screen;

	// --- set days, hours and minutes arrays ---
	$days = array( '', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );
	$hours = $mins = array();
	for ( $i = 1; $i <= 12; $i ++ ) {
		$hours[$i] = $i;
	}
	for ( $i = 0; $i < 60; $i ++ ) {
		if ( $i < 10 ) {
			$min = '0' . $i;
		} else {
			$min = $i;
		}
		$mins[$i] = $min;
	}

	// 2.2.7: added meridiem translations
	$am = radio_station_translate_meridiem( 'am' );
	$pm = radio_station_translate_meridiem( 'pm' );

	// --- hidden debug fields ---
	// 2.3.2: added save debugging field
	if ( RADIO_STATION_DEBUG ) {
		echo '<input type="hidden" name="rs-debug" value="1">';
	}
	if ( RADIO_STATION_SAVE_DEBUG ) {
		echo '<input type="hidden" name="rs-save-debug" value="1">';
	}

	// --- add nonce field for verification ---
	wp_nonce_field( 'radio-station', 'show_shifts_nonce' );

	echo '<div id="meta_inner">';
	echo '<div id="shifts-list">';

	// 2.3.2: added to bypass shift check on add (for debugging)
	if ( isset( $_REQUEST['check-bypass'] ) && ( '1' == $_REQUEST['check-bypass'] ) ) {
		echo '<input type="hidden" name="check-bypass" value="1">';
	}

	// --- output show shifts table ---
	// 2.3.2: separated table function (for AJAX saving)
	$table = radio_station_show_shifts_table( $post->ID );

	// --- show inactive message ---
	// 2.3.0: added show inactive reminder message
	if ( !$table['active'] ) {
		echo '<div class="shift-conflicts-message">';
		echo '<b style="color:#EE0000;">' . esc_html( __( 'This Show is inactive!', 'radio-station' ) ) . '</b> ';
		echo esc_html( __( 'All Shifts are inactive also until Show is activated.', 'radio-station' ) );
		echo '</div>';
	}

	// --- shift conflicts message ---
	// 2.3.0: added instructions for fixing shift conflicts
	if ( $table['conflicts'] ) {
		radio_station_shifts_conflict_message();
	}

	// --- output shift list ---
	if ( '' != $table['list'] ) {
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo $table['list'];
	}
	echo "</div>";

	// 2.3.0: center add shift button
	// 2.3.2: fix centering by removing span wrapper
	// 2.3.2: change from button-primary to button-secondary
	echo '<center>';
	// echo '<a class="shift-add button-secondary" style="margin-top: 10px;">' . esc_html( __( 'Add Shift', 'radio-station' ) ) . '</a>';

	// --- shift save/add buttons ---
	// 2.3.2: added show shifts AJAX save button (for existing posts only)
	// 2.3.2: added show shifts clear button
	// 2.3.2: added table and shifts saved message
    echo '<table id="shifts-table-buttons" width="100%"><tr><td width="33%" align="center">';
    echo '<input type="button" class="shifts-clear button-secondary" value="' . esc_attr( __( 'Clear Shifts', 'radio-station' ) ) . '" onclick="radio_shifts_clear();">';
	echo '</td><td width="33%" align="center">';
    if ( 'add' != $current_screen->action ) {
	    echo '<input type="button" class="shifts-save button-primary" value="' . esc_attr( __( 'Save Shifts', 'radio-station' ) ) . '" onclick="radio_shifts_save();">';
	}
    echo '</td><td width="33%" align="center">';
    echo '<input type="button" class="shift-add button-secondary" value="' . esc_attr( __( 'Add Shift', 'radio-station' ) ) . '" onclick="radio_shift_new();">';
    echo '</td></tr>';
    echo '<tr><td></td><td align="center">';
    echo '<div id="shifts-saving-message" style="display:none;">' . esc_html( __( 'Saving Show Shifts...', 'radio-station' ) ) . '</div>';
    echo '<div id="shifts-saved-message" style="display:none;">' . esc_html( __( 'Show Shifts Saved.', 'radio-station' ) ) . '</div>';
    echo '<div id="shifts-error-message" style="display:none;"></div>';
    echo '</td><td></td></tr></table>';
    echo '</center>';

	// --- show shifts scripts ---
	// 2.3.0: added confirmation to remove shift button
	// 2.3.2: removed document ready functions wrapper
	$c = 0;
	$confirm_remove = __( 'Are you sure you want to remove this shift?', 'radio-station' );
	$confirm_clear = __( 'Are you sure you want to clear the shift list?', 'radio-station' );
	// $js = "var count = " . esc_attr( $c ) . ";";

	// --- clear all shifts function ---
	$js = "function radio_shifts_clear() {
		if (jQuery('#shifts-list').children().length) {
			var agree = confirm('" . esc_js( $confirm_clear ) . "');
			if (!agree) {return false;}
			jQuery('#shifts-list').children().remove();
			jQuery('<div id=\"new-shifts\"></div>').appendTo('#shifts-list');
		}
	}" . PHP_EOL;

	// --- save shifts via AJAX ---
	// 2.3.2: added form input cloning to saving show shifts
	$ajaxurl = admin_url( 'admin-ajax.php' );
	$js .= "function radio_shifts_save() {
		jQuery('#shift-save-form, #shift-save-frame').remove();
		form = '<form id=\"shift-save-form\" method=\"post\" action=\"" . esc_url( $ajaxurl ) . "\" target=\"shift-save-frame\">';
		form += '<input type=\"hidden\" name=\"action\" value=\"radio_station_show_save_shifts\"></form>';
		jQuery('#wpbody').append(form);
		if (!jQuery('#shift-save-frame').length) {
			frame = '<iframe id=\"shift-save-frame\" name=\"shift-save-frame\" src=\"javascript:void(0);\" style=\"display:none;\"></iframe>';
			jQuery('#wpbody').append(frame);
		}
		/* copy shifts input fields and nonce */
		jQuery('#shifts-list input').each(function() {jQuery(this).clone().appendTo('#shift-save-form');});
		jQuery('#shifts-list select').each(function() {
			name = jQuery(this).attr('name'); value = jQuery(this).children('option:selected').val();
			jQuery('<input type=\"hidden\" name=\"'+name+'\" value=\"'+value+'\">').appendTo('#shift-save-form');
		});
		jQuery('#show_shifts_nonce').clone().attr('id','').appendTo('#shift-save-form');
		jQuery('#post_ID').clone().attr('id','').attr('name','show_id').appendTo('#shift-save-form');
		jQuery('#shifts-saving-message').show();
		jQuery('#shift-save-form').submit();
	}" . PHP_EOL;

	// --- check select change ---
	// 2.3.3: added select change detection
	$js .= "function radio_check_select(el) {
		val = el.options[el.selectedIndex].value;
		if (val == '') {jQuery('#'+el.id).addClass('incomplete');}
		else {jQuery('#'+el.id).removeClass('incomplete');}
		origid = el.id.replace('shift-','');
		origval = jQuery('#'+origid).val();
		if (val == origval) {jQuery('#'+el.id).removeClass('changed');}
		else {jQuery('#'+el.id).addClass('changed');}
		uid = origid.substr(0,8);
		radio_check_shift(uid);
	}" . PHP_EOL;

	// --- check checkbox change ---
	// 2.3.3: added checkbox change detection
	$js .= "function radio_check_checkbox(el) {
		val = el.checked ? 'on' : '';
		origid = el.id.replace('shift-','');
		origval = jQuery('#'+origid).val();
		if (val == origval) {jQuery('#'+el.id).removeClass('changed');}
		else {jQuery('#'+el.id).addClass('changed');}
		uid = origid.substr(0,8);
		radio_check_shift(uid);
	}" . PHP_EOL;

	// --- check shift change ---
	// 2.3.3: added shift change detection
	$js .= "function radio_check_shift(id) {
		var shiftchanged = false;
		jQuery('#shift-'+id).find('select,input').each(function() {
			if ( (jQuery(this).attr('id').indexOf('shift-') == 0) && (jQuery(this).hasClass('changed')) ) {
				shiftchanged = true;
			}
		});
		if (shiftchanged) {jQuery('#shift-'+id).addClass('changed');}
		else {jQuery('#shift-'+id).removeClass('changed');}
		radio_check_shifts();
	}" . PHP_EOL;

	// 2.3.3.6: store possible existing onbeforeunload function
	// (to help prevent conflicts with other plugins using this event)
	$js .= "var storedonbeforeunload = null; var onbeforeunloadset = false;" . PHP_EOL;
	$js .= "function radio_check_shifts() {
		if (jQuery('.show-shift.changed').length) {
			if (!onbeforeunloadset) {
				storedonbeforeunload = window.onbeforeunload;
				window.onbeforeunload = function() {return true;}
				onbeforeunloadset = true;
			}
		} else {
			if (onbeforeunloadset) {
				window.onbeforeunload = storedonbeforeunload;
				onbeforeunloadset = false;
			}
		}
	}" . PHP_EOL;

	// --- add new shift ---
	// 2.3.2: separate function for onclick
	$js .= "function radio_shift_new() {
			values = {};
			values.day = '';
			values.start_hour = '';
			values.start_min = '';
			values.start_meridian = '';
			values.end_hour = '';
			values.end_min = '';
			values.end_meridian = '';
			values.encore = '';
			values.disabled = '';
			radio_shift_add(values);
	}" . PHP_EOL;

	// --- remove shift ----
	// 2.3.2: separate function for onclick
	// 2.3.3: fix to jQuery targeting for new shifts
	$js .= "function radio_shift_remove(el) {
		agree = confirm('" . esc_js( $confirm_remove ) . "');
		if (!agree) {return false;}
		shiftid = el.id.replace('shift-','').replace('-remove','');
		jQuery('#'+el.id).closest('.shift-wrapper').remove();
	}" . PHP_EOL;

	// --- duplicate shift ---
	// 2.3.2: separate function for onclick
	$js .= "function radio_shift_duplicate(el) {
		shiftid = el.id.replace('shift-','').replace('-duplicate','');
		values = {};
		values.day = jQuery('#shift-'+shiftid+'-day').val();
		values.start_hour = jQuery('#shift-'+shiftid+'-start-hour').val();
		values.start_min = jQuery('#shift-'+shiftid+'-start-min').val();
		values.start_meridian = jQuery('#shift-'+shiftid+'-start-meridian').val();
		values.end_hour = jQuery('#shift-'+shiftid+'-end-hour').val();
		values.end_min = jQuery('#shift-'+shiftid+'-end-min').val();
		values.end_meridian = jQuery('#shift-'+shiftid+'-end-meridian').val();
		values.encore = '';
		if (jQuery('#shift-'+shiftid+'-encore').prop('checked')) {values.encore = 'on';}
		values.disabled = 'yes';
		radio_shift_add(values);
	}" . PHP_EOL;

	// --- add shift function ---
	// 2.3.2: added missing shift wrapper class
	// 2.3.2: set new count based on new shift children
	// 2.3.3: add input IDs so new shifts can be duplicated
	$js .= "function radio_shift_add(values) {
			var count = jQuery('#new-shifts').children().length + 1;
			output = '<div id=\"shift-wrapper-new' + count + '\" class=\"shift-wrapper\">';
			output += '<ul id=\"shift-' + count + '\" class=\"show-shift new-shift\">';
				output += '<li class=\"first-item\">';
					output += '" . esc_js( __( 'Day', 'radio-station' ) ) . ": ';
					output += '<select id=\"shift-new' + count + '-day\" name=\"show_sched[new-' + count + '][day]\" id=\"shift-new-' + count +'-day\">';";

	// - shift day -
	// 2.3.0: simplify by looping days and add translation
	foreach ( $days as $day ) {
		$js .= "output += '<option value=\"" . esc_js( $day ) . "\"';
			if (values.day == '" . esc_js( $day ) . "') {output += ' selected=\"selected\"';}
			output += '>" . esc_js( radio_station_translate_weekday( $day ) ) . "</option>';";
	}
	$js .= "output += '</select>';
			output += '</li>';

			output += '<li>';
				output += '" . esc_js( __( 'Start Time', 'radio-station' ) ) . ": ';
				output += '<select id=\"shift-new' + count + '-start-min\" name=\"show_sched[new-' + count + '][start_hour]\" id=\"shift-new-' + count + '-start-hour\" style=\"min-width:35px;\">';";

	// - start hour -
	foreach ( $hours as $hour ) {
		$js .= "output += '<option value=\"" . esc_js( $hour ) . "\"';
			if (values.start_hour == '" . esc_js( $hour ) . "') {output += ' selected=\"selected\"';}
			output += '>" . esc_js( $hour ) . "</option>';";
	}
		$js .= "output += '</select> ';
				output += '<select id=\"shift-new' + count + '-start-hour\" name=\"show_sched[new-' + count + '][start_min]\" id=\"shift-new-' + count + '-start-min\" style=\"min-width:35px;\">';
				output += '<option value=\"00\">00</option><option value=\"15\">15</option><option value=\"30\">30</option><option value=\"45\">45</option>';";

	// - start minute -
	foreach ( $mins as $min ) {
		$js .= "output += '<option value=\"" . esc_js( $min ) . "\"';
				if (values.start_min == '" . esc_js( $min ) . "') {output += ' selected=\"selected\"';}
				output += '>" . esc_js( $min ) . "</option>';";
	}

	// - start meridian -
	$js .= "output += '</select>';
			output += '<select id=\"shift-new' + count + '-start-meridian\" name=\"show_sched[new-' + count + '][start_meridian]\" id=\"shift-new-' + count + '-start-meridian\" style=\"min-width:35px;\">';
				output += '<option value=\"am\"';
				if (values.start_meridian == '" . esc_js( $am ) . "') {output += ' selected=\"selected\"';}
				output += '>" . esc_js( $am ) . "</option>';
				output += '<option value=\"pm\"';
				if (values.start_meridian == '" . esc_js( $pm ) . "') {output += ' selected=\"selected\"';}
				output += '>" . esc_js( $pm ) . "</option>';
			output += '</select> ';
		output += '</li>';";

	$js .= "output += '<li>';
			output += '" . esc_js( __( 'End Time', 'radio-station' ) ) . ": ';
			output += '<select id=\"shift-new' + count + '-end-hour\" name=\"show_sched[new-' + count + '][end_hour]\" id=\"shift-new-' + count + '-end-hour\" style=\"min-width:35px;\">';";

	// - end hour -
	foreach ( $hours as $hour ) {
		$js .= "output += '<option value=\"" . esc_js( $hour ) . "\"';
				if (values.end_hour == '" . esc_js( $hour ) . "') {output += ' selected=\"selected\"';}
				output += '>" . esc_js( $hour ) . "</option>';";
	}
	$js .= "output += '</select> ';";


	// - end min -
	$js .= "output += '<select id=\"shift-new' + count + '-end-min\" name=\"show_sched[new-' + count + '][end_min]\" id=\"shift-new-' + count + '-end-min\" style=\"min-width:35px;\">';
			output += '<option value=\"00\">00</option><option value=\"15\">15</option><option value=\"30\">30</option><option value=\"45\">45</option>';";
	foreach ( $mins as $min ) {
		$js .= "output += '<option value=\"" . esc_js( $min ) . "\"';
				if (values.end_min == '" . esc_js( $min ) . "') {output += ' selected=\"selected\"';}
				output += '>" . esc_js( $min ) . "</option>';";
	}
	$js .= "output += '</select> ';";

	// - end meridian -
	$js .= "output += '<select id=\"shift-new' + count + '-end-meridian\" name=\"show_sched[new-' + count + '][end_meridian]\" id=\"shift-new-' + count + '-end-meridian\" style=\"min-width:35px;\">';
				output += '<option value=\"am\"';
				if (values.end_meridian == '" . esc_js( $am ) . "') {output += ' selected=\"selected\"';}
				output += '>" . esc_js( $am ) . "</option>';
				output += '<option value=\"pm\"';
				if (values.end_meridian == '" . esc_js( $pm ) . "') {output += ' selected=\"selected\"';}
				output += '>" . esc_js( $pm ) . "</option>';
			output += '</select> ';
		output += '</li>';";

	// - encore -
	$js .= "output += '<li>';
			output += '<input id=\"shift-new' + count + '-disabled\" type=\"checkbox\" value=\"on\" name=\"show_sched[new-' + count + '][encore]\" id=\"shift-new-' + count + '-encore\"';
			if (values.encore == 'on') {output += ' checked=\"checked\"';}
			output += '> " . esc_js( __( 'Encore', 'radio-station' ) ) . "';
		output += '</li>';";

	// - disabled shift -
	$js .= "output += '<li>';
			output += '<input id=\"shift-new' + count + '-disabled\" type=\"checkbox\" value=\"yes\" name=\"show_sched[new-' + count + '][disabled]\" id=\"shift-new-' + count + '-disabled\"';
			if (values.disabled != '') {output += ' checked=\"checked\"';}
			output += '> " . esc_js( __( 'Disabled', 'radio-station' ) ) . "';
		output += '</li>';";

	// - duplicate shift -
	$js .= "output += '<li>';
			output += '<span id=\"shift-new' + count + '-duplicate\" class=\"shift-duplicate dashicons dashicons-admin-page\" title=\"" . esc_js( __( 'Duplicate Shift', 'radio-station' ) ) . "\" onclick=\"radio_shift_duplicate(this);\"></span>';
		output += '</li>';";

	// - remove shift -
	$js .= "output += '<li class=\"last-item\">';
			output += '<span id=\"shift-new' + count + '-remove\" class=\"shift-remove dashicons dashicons-no\" title=\"" . esc_js( __( 'Remove Shift', 'radio-station' ) ) . "\" onclick=\"radio_shift_remove(this);\"></span>';
		output += '</li>';";

	// --- append new shift list item ---
	$js .= "output += '</ul></div>';
		jQuery('#new-shifts').append(output);
		return false;
	}";

	// --- enqueue inline script ---
	// 2.3.0: enqueue instead of echoing
	wp_add_inline_script( 'radio-station-admin', $js );

	// --- shift display styles ---
	// 2.3.2: added dashed border to new shift
	echo '<style>#new-shifts .show-shift {border: 2px dashed green; background-color: #FFFFDD;}
	.show-shift {list-style: none; margin-bottom: 10px;	border: 2px solid green; background-color: #EEFFEE;}
	.show-shift select.changed, .show-shift input[checkbox].changed {background-color: #FFFFCC;}
	.show-shift select option.original {font-weight: bold;}
	.show-shift li {display: inline-block; vertical-align: middle;
		margin-left: 20px; margin-top: 10px; margin-bottom: 10px;}
	.show-shift li.first-item {margin-left: 10px;}
	.show-shift li.last-item {margin-right: 10px;}
	.show-shift.changed, .show-shift.changed.disabled {background-color: #FFEECC;}
	.show-shift.disabled {border: 2px dashed orange; background-color: #FFDDDD;}
	.show-shift.conflicts {outline: 2px solid red;}
	.show-shift.disabled.conflicts {border: 2px dashed red;	outline: none;}
	.show-shift select.incomplete {border: 2px solid orange;}
	#shifts-table-buttons .shifts-clear, #shifts-table-buttons .shifts-save, #shifts-table-buttons .shift-add {
		cursor: pointer; display:block; width: 150px; padding: 8px; text-align: center; line-height: 1em;}
	.shift-duplicate, .shift-remove {cursor: pointer;}
	#shifts-saving-message, #shifts-saved-message {
		background-color: lightYellow; border: 1px solid #E6DB55; margin-top: 10px; font-weight: bold; width: 170px; padding: 5px 0;}
	</style>';

	// --- close meta inner ---
	echo '</div>';
}

// -----------------------
// Shifts Oonflict Message
// -----------------------
function radio_station_shifts_conflict_message() {
	echo '<div class="shift-conflicts-message">';
	echo '<b style="color:#EE0000;">' . esc_html( __( 'Warning! Show Shift Conflicts were detected!', 'radio-station' ) ) . '</b><br>';
	echo esc_html( __( 'Please note that Shifts with conflicts are automatically disabled upon saving.', 'radio-station' ) ) . '<br>';
	echo esc_html( __( 'Fix the Shift and/or the Shift on the conflicting Show and Update them both.', 'radio-station' ) ) . '<br>';
	echo esc_html( __( 'Then you can uncheck the shift Disable box and Update to re-enable the Shift.', 'radio-station' ) ) . '<br>';
	// TODO: add more information blog post / documentation link ?
	// echo '<a href="' . RADIO_STATION_DOC_URL . '/manage/#" target="_blank">' . esc_html( __( 'Show Documentation', 'radio-station' ) ) . '</a>';
	echo '</div><br>';
}

// ----------------
// Show Shift Table
// ----------------
// 2.3.2: separate shift table function (for AJAX saving)
function radio_station_show_shifts_table( $post_id ) {

	global $post;

	// --- edit show link ---
	$edit_link = add_query_arg( 'action', 'edit', admin_url( 'post.php' ) );

	// 2.2.7: added meridiem translations
	$am = radio_station_translate_meridiem( 'am' );
	$pm = radio_station_translate_meridiem( 'pm' );

	// --- set days, hours and minutes arrays ---
	$days = array( '', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );
	$hours = $mins = array();
	for ( $i = 1; $i <= 12; $i ++ ) {
		$hours[$i] = $i;
	}
	for ( $i = 0; $i < 60; $i ++ ) {
		if ( $i < 10 ) {
			$min = '0' . $i;
		} else {
			$min = $i;
		}
		$mins[$i] = $min;
	}

	// --- get the saved meta as an array ---
	$shifts = radio_station_get_show_schedule( $post_id );
	$active = get_post_meta( $post_id, 'show_active', true );

	$has_conflicts = false;
	$list = '';
	if ( isset( $shifts ) && is_array( $shifts ) && ( count( $shifts ) > 0 ) ) {

		// 2.2.7: soft shifts by start day and time for ordered display
		foreach ( $shifts as $unique_id => $shift ) {
			// 2.3.0: add shift index to prevent start time overwriting
			$j = 1;
			$shift['unique_id'] = $unique_id;
			$shift_start = $shift['start_hour'] . ':' . $shift['start_min'] . ' ' . $shift['start_meridian'];
			$shift_start = radio_station_convert_shift_time( $shift_start );
			if ( isset( $shift['day'] ) && ( '' != $shift['day'] ) ) {
				// --- group shifts by days of week ---
				$starttime = radio_station_to_time( 'next ' . $shift['day'] . ' ' . $shift_start );
				// 2.3.0: simplify by getting day index
				$i = array_search( $shift['day'], $days );
				$day_shifts[$i][$starttime . '.' . $j] = $shift;
			} else {
				// --- to still allow shift time sorting if day is not set ---
				$starttime = radio_station_to_time( '1981-04-28 ' . $shift_start );
				$day_shifts[7][$starttime . '.' . $j] = $shift;
			}
			$j ++;
		}

		// --- sort day shifts by day and time ---
		ksort( $day_shifts );
		// 2.3.0: resort order using start of week
		$sorted_shifts = array();
		$weekdays = radio_station_get_schedule_weekdays();
		foreach ( $weekdays as $i => $weekday ) {
			if ( isset( $day_shifts[$i] ) ) {
				$sorted_shifts[$i] = $day_shifts[$i];
			}
		}
		if ( isset( $day_shifts[7] ) ) {
			$sorted_shifts[7] = $day_shifts[7];
		}
		$show_shifts = array();
		foreach ( $sorted_shifts as $shift_day => $day_shift ) {
			// --- sort shifts by (unique) start time for each day ---
			ksort( $day_shift );
			foreach ( $day_shift as $shift ) {
				$unique_id = $shift['unique_id'];
				unset( $shift['unique_id'] );
				$show_shifts[$unique_id] = $shift;
			}
		}

		// --- loop ordered show shifts ---
		foreach ( $show_shifts as $unique_id => $shift ) {

			$classes = array( 'show-shift' );

			// --- check conflicts with other show shifts ---
			// 2.3.0: added shift conflict checking
			$conflicts = radio_station_check_shift( $post_id, $shift );
			if ( $conflicts && is_array( $conflicts ) ) {
				$has_conflicts = true;
				$classes[] = 'conflicts';
			}

			// --- check if shift disabled ---
			// 2.3.0: check shift disabled switch or show inactive
			if ( isset( $shift['disabled'] ) && ( 'yes' == $shift['disabled'] ) ) {
				$classes[] = 'disabled';
			} elseif ( !$active ) {
				$classes[] = 'disabled';
			}
			$classlist = implode( " ", $classes );

			$list .= '<div id="shift-wrapper-' . esc_attr( $unique_id ) . '" class="shift-wrapper">';
			$list .= '<ul id="shift-' . esc_attr( $unique_id ) . '" class="' . esc_attr( $classlist ) . '">';

			// --- shift day selection ---
			$list .= '<li class="first-item">';
			$list .= esc_html( __( 'Day', 'radio-station' ) ) . ': ';

			$class = '';
			if ( '' == $shift['day'] ) {
				$class = 'incomplete';
			}
			$list .= '<select id="shift-' . esc_attr( $unique_id ) . '-day" class="' . esc_attr( $class ) . '" name="show_sched[' . esc_attr( $unique_id ) . '][day]" onchange="radio_check_select(this);">';
			// 2.3.0: simplify by looping days
			foreach ( $days as $day ) {
				// 2.3.0: add weekday translation to display
				$list .= '<option value="' . esc_attr( $day ) . '" ' . selected( $day, $shift['day'], false ) . '>';
				$list .= esc_html( radio_station_translate_weekday( $day ) ) . '</option>';
			}
			$list .= '</select>';
			$list .= '<input type="hidden" id="' . esc_attr( $unique_id ) . '-day" value="' . esc_attr( $shift['day'] ) . '">';
			$list .= '</li>';

			// --- shift start time ---
			$list .= '<li>';
			$list .= esc_html( __( 'Start Time', 'radio-station' ) ) . ': ';

			// --- start hour selection ---
            $class = '';
			if ( '' == $shift['start_hour'] ) {
				$class = 'incomplete';
			}
			$list .= '<select id="shift-' . esc_attr( $unique_id ) . '-start-hour" class="' . esc_attr( $class ) . '" name="show_sched[' . esc_attr( $unique_id ) . '][start_hour]" onchange="radio_check_select(this);" style="min-width:35px;">';
			foreach ( $hours as $hour ) {
				$class = '';
				if ( $hour == $shift['start_hour'] ) {
					$class = 'original';
				}
				$list .= '<option class="' . esc_attr( $class ) . '" value="' . esc_attr( $hour ) . '" ' . selected( $hour, $shift['start_hour'], false ) . '>' . esc_html( $hour ) . '</option>';
			}
			$list .= '</select>';
			$list .= '<input type="hidden" id="' . esc_attr( $unique_id ) . '-start-hour" value="' . esc_attr( $shift['start_hour'] ) . '">';

			// --- start minute selection ---
			$list .= '<select id="shift-' . esc_attr( $unique_id ) . '-start-min" name="show_sched[' . esc_attr( $unique_id ) . '][start_min]" onchange="radio_check_select(this);" style="min-width:35px;">';
			$list .= '<option value=""></option>';
			$list .= '<option value="00">00</option>';
			$list .= '<option value="15">15</option>';
			$list .= '<option value="30">30</option>';
			$list .= '<option value="45">45</option>';
			foreach ( $mins as $min ) {
				$class = '';
				if ( $min == $shift['start_min'] ) {
					$class = 'original';
				}
				$list .= '<option class="' . esc_attr( $class ) . '" value="' . esc_attr( $min ) . '" ' . selected( $min, $shift['start_min'], false ) . '>' . esc_html( $min ) . '</option>';
			}
			$list .= '</select>';
			$list .= '<input type="hidden" id="' . esc_attr( $unique_id ) . '-start-min" value="' . esc_attr( $shift['start_min'] ) . '">';

			// --- start meridiem selection ---
			$class = '';
			if ( '' == $shift['start_meridian'] ) {
				$class = 'incomplete';
			}
			$list .= '<select id="shift-' . esc_attr( $unique_id ) . '-start-meridian" class="' . esc_attr( $class ) . '" name="show_sched[' . esc_attr( $unique_id ) . '][start_meridian]" onchange="radio_check_select(this);" style="min-width:35px;">';
			$class = '';
			if ( 'am' == $shift['start_meridian'] ) {
				$class = 'original';
			}
			$list .= '<option class="' . esc_attr( $class ) . '" value="am" ' . selected( $shift['start_meridian'], 'am', false ) . '>' . esc_html( $am ) . '</option>';
			$class = '';
			if ( 'pm' == $shift['start_meridian'] ) {
				$class = 'original';
			}
			$list .= '<option class="' . esc_attr( $class ) . '" value="pm" ' . selected( $shift['start_meridian'], 'pm', false ) . '>' . esc_html( $pm ) . '</option>';
			$list .= '</select>';
			$list .= '<input type="hidden" id="' . esc_attr( $unique_id ) . '-start-meridian" value="' . esc_attr( $shift['start_meridian'] ) . '">';
			$list .= '</li>';

			// --- shift end time ---
			$list .= '<li>';
			$list .= esc_html( __( 'End Time', 'radio-station' ) ) . ': ';

			// --- end hour selection ---
			$class = '';
			if ( '' == $shift['end_hour'] ) {
				$class = 'incomplete';
			}
			$list .= '<select id="shift-' . esc_attr( $unique_id ) . '-end-hour" class="' . esc_attr( $class ) . '" name="show_sched[' . esc_attr( $unique_id ) . '][end_hour]" onchange="radio_check_select(this);" style="min-width:35px;">';
			foreach ( $hours as $hour ) {
				$class = '';
				if ( $hour == $shift['end_hour'] ) {
					$class = 'original';
				}
				$list .= '<option class="' . esc_attr( $class ) . '" value="' . esc_attr( $hour ) . '" ' . selected( $shift['end_hour'], $hour, false ) . '>' . esc_html( $hour ) . '</option>';
			}
			$list .= '</select>';
			$list .= '<input type="hidden" id="' . esc_attr( $unique_id ) . '-end-hour" value="' . esc_attr( $shift['end_hour'] ) . '">';

			// --- end minute selection ---
			$list .= '<select id="shift-' . esc_attr( $unique_id ) . '-end-min" name="show_sched[' . esc_attr( $unique_id ) . '][end_min]" onchange="radio_check_select(this);" style="min-width:35px;">';
			$list .= '<option value=""></option>';
			$list .= '<option value="00">00</option>';
			$list .= '<option value="15">15</option>';
			$list .= '<option value="30">30</option>';
			$list .= '<option value="45">45</option>';
			foreach ( $mins as $min ) {
				$class = '';
				if ( $min == $shift['end_min'] ) {
					$class = 'original';
				}
				$list .= '<option class="' . esc_attr( $class ) . '" value="' . esc_attr( $min ) . '" ' . selected( $shift['end_min'], $min, false ) . '>' . esc_html( $min ) . '</option>';
			}
			$list .= '</select>';
			$list .= '<input type="hidden" id="' . esc_attr( $unique_id ) . '-end-min" value="' . esc_attr( $shift['end_min'] ) . '">';

			// --- end meridiem selection ---
			$class = '';
			if ( '' == $shift['end_meridian'] ) {
				$class = 'incomplete';
			}
			$list .= '<select id="shift-' . esc_attr( $unique_id ) . '-end-meridian" class="' . esc_attr( $class ) . '" name="show_sched[' . esc_attr( $unique_id ) . '][end_meridian]" onchange="radio_check_select(this);" style="min-width:35px;">';
			$class = '';
			if ( 'am' == $shift['end_meridian'] ) {
				$class = 'original';
			}
			$list .= '<option class="' . esc_attr( $class ) . '" value="am" ' . selected( $shift['end_meridian'], 'am', false ) . '>' . esc_html( $am ) . '</option>';
			$class = '';
			if ( 'pm' == $shift['end_meridian'] ) {
				$class = 'original';
			}
			$list .= '<option class="' . esc_attr( $class ) . '" value="pm" ' . selected( $shift['end_meridian'], 'pm', false ) . '>' . esc_html( $pm ) . '</option>';
			$list .= '</select>';
			$list .= '<input type="hidden" id="' . esc_attr( $unique_id ) . '-end-meridian" value="' . esc_attr( $shift['end_meridian'] ) . '">';
			$list .= '</li>';

			// --- encore presentation ---
			if ( !isset( $shift['encore'] ) ) {$shift['encore'] = '';}
			$list .= '<li>';
			$list .= '<input id="' . esc_attr( $unique_id ) . '-encore" type="checkbox" value="on" name="show_sched[' . esc_attr( $unique_id ) . '][encore]" id="shift-' . esc_attr( $unique_id ) . '-encore"' . checked( $shift['encore'], 'on', false ) . ' onchange="radio_check_checkbox(this);">';
			$list .= esc_html( __( 'Encore', 'radio-station' ) );
			$list .= '<input type="hidden" id="' . esc_attr( $unique_id ) . '-encore" value="' . esc_attr( $shift['encore'] ) . '">';
			$list .= '</li>';

			// --- shift disabled ---
			// 2.3.0: added disabled checkbox to shift row
			if ( !isset( $shift['disabled'] ) ) {$shift['disabled'] = '';}
			$list .= '<li>';
			$list .= '<input id="' . esc_attr( $unique_id ) . '-disabled" type="checkbox" value="yes" name="show_sched[' . esc_attr( $unique_id ) . '][disabled]" id="shift-' . esc_attr( $unique_id ) . '-disabled"' . checked( $shift['disabled'], 'yes', false ) . ' onchange="radio_check_checkbox(this);">';
			$list .= esc_html( __( 'Disabled', 'radio-station' ) );
			$list .= '<input type="hidden" id="' . esc_attr( $unique_id ) . '-disabled" value="' . esc_attr( $shift['disabled'] ) . '">';
			$list .= '</li>';

			// --- duplicate shift icon ---
			// 2.3.0: added duplicate shift icon
			$list .= '<li>';
			$title = __( 'Duplicate Shift', 'radio-station' );
			$list .= '<span id="shift-' . esc_attr( $unique_id ) . '-duplicate" class="shift-duplicate dashicons dashicons-admin-page" title="' . esc_attr( $title ) . '" onclick="radio_shift_duplicate(this);"></span>';
			$list .= '</li>';

			// --- remove shift icon ---
			// 2.3.0: change remove button to icon
			$list .= '<li class="last-item">';
			$title = __( 'Remove Shift', 'radio-station' );
			$list .= '<span id="shift-' . esc_attr( $unique_id ) . '-remove" class="shift-remove dashicons dashicons-no" title="' . esc_attr( $title ) . '" onclick="radio_shift_remove(this);"></span>';
			// $list .= '<span class="shify-remove button button-secondary" style="cursor: pointer;">';
			// $list .= esc_html( __( 'Remove', 'radio-station' ) );
			$list .= '</span>';
			$list .= '</li>';

			$list .= '</ul>';

			// --- output any shift conflicts found ---
			if ( $conflicts && is_array( $conflicts ) && ( count( $conflicts ) > 0 ) ) {
				$list .= '<div class="shift-conflicts">';
				$list .= '<b>' . esc_html( __( 'Shift Conflicts', 'radio-station' ) ) . '</b>: ';
				foreach ( $conflicts as $j => $conflict ) {
					if ( $j > 0 ) {
						$list .= ', ';
					}
					if ( $conflict['show'] == $post_id ) {
						$list .= '<i>' . esc_html( __('This Show', 'radio-station' ) ) . '</i>';
					} else {
						$show_edit_link = add_query_arg( 'post', $conflict['show'], $edit_link );
						$show_title = get_the_title( $conflict['show'] );
						$list .= '<a href="' . esc_url( $show_edit_link ) . '">' . esc_html( $show_title ) . '</a>';
					}
					$conflict_start = esc_html( $conflict['shift']['start_hour'] ) . ':' . esc_html( $conflict['shift']['start_min'] ) . ' ' . esc_html( $conflict['shift']['start_meridian'] );
					$conflict_end = esc_html( $conflict['shift']['end_hour'] ) . ':' . esc_html( $conflict['shift']['end_min'] ). ' ' . esc_html( $conflict['shift']['end_meridian'] );
					$list .= ' - ' . esc_html( $conflict['shift']['day'] ) . ' ' . $conflict_start . ' - ' . $conflict_end;
				}
				$list .= '</div><br>';
			}

			// --- close shift wrapper ---
			$list .= '</div>';

		}
	}

	// 2.3.2: moved into function and changed ID
	$list .= '<span id="new-shifts"></span>';

	// --- set return data ---
	// 2.3.2: added for separated function
	$table = array(
		'list'      => $list,
		'active'    => $active,
		'conflicts' => $has_conflicts,
	);

	return $table;
}

// -----------------------------------
// Add Show Description Helper Metabox
// -----------------------------------
// 2.3.0: added metabox for show description helper text
add_action( 'add_meta_boxes', 'radio_station_add_show_helper_box' );
function radio_station_add_show_helper_box() {
	// 2.3.2: filter top metabox position
	$position = apply_filters( 'radio_station_metabox_position', 'rstop', 'helper' );
	add_meta_box(
		'radio-station-show-helper-box',
		__( 'Show Description', 'radio-station' ),
		'radio_station_show_helper_box',
		RADIO_STATION_SHOW_SLUG,
		$position,
		'low'
	);
}

// -------------------------------
// Show Description Helper Metabox
// -------------------------------
// 2.3.0: added metabox for show description helper text
function radio_station_show_helper_box() {

	// --- show description helper text ---
	echo '<p>';
	echo esc_html( __( "The text field below is for your Show Description. It will display in the About section of your Show page.", 'radio-station' ) );
	echo ' ' . esc_html( __( "It is not recommended to include your past show content or archives in this area, as it will affect the Show page layout your visitors see.", 'radio-station' ) );
	echo esc_html( __( "It may also impact SEO, as archived content won't have their own pages and thus their own SEO and Social meta rules.", 'radio-station' ) ) . "<br>";
	echo esc_html( __( "We recommend using WordPress Posts to add new posts and assign them to your Show(s) using the Related Show metabox on the Post Edit screen so they display on the Show page.", 'radio-station' ) );
	echo ' ' . esc_html( __( "You can then assign them to a relevant Post Category for display on your site also.", 'radio-station' ) );
	echo '</p>';

	// TODO: upgrade to Pro for upcoming Show Episodes blurb
	// echo '<br>' . esc_html( 'In future, Radio Station Pro will include an Episodes post type', 'radio-station' ) );
	// TODO: change this text/link when Pro Episodes become available
	// $upgrade_url = radio_station_get_upgrade_url();
	// echo '<br><a href="' . esc_url( $upgrade_url ) . '" target="_blank">';
	//	// echo esc_html( __( "Upgrade to Radio Station Pro', 'radio-station' ) );
	//	echo esc_html( __( 'Find out more about Radio Station Pro', 'radio-station' ) );
	// echo ' &rarr;</a>.';

}

// ----------------------------------
// Rename Show Featured Image Metabox
// ----------------------------------
// 2.3.0: renamed from "Feature Image" to be clearer
// 2.3.0: removed this as now implementing show images separately
// (note this is the Show Logo for backwards compatibility reasons)
// add_action( 'do_meta_boxes', 'radio_station_rename_featured_image_metabox' );
function radio_station_rename_featured_image_metabox() {
	remove_meta_box( 'postimagediv', RADIO_STATION_SHOW_SLUG, 'side' );
	add_meta_box(
		'postimagediv',
		__( 'Show Logo', 'radio-station' ),
		'post_thumbnail_meta_box',
		RADIO_STATION_SHOW_SLUG,
		'side',
		'low'
	);
}

// -----------------------
// Add Show Images Metabox
// -----------------------
// 2.3.0: added show images metabox
add_action( 'add_meta_boxes', 'radio_station_add_show_images_metabox' );
function radio_station_add_show_images_metabox() {
	add_meta_box(
		'radio-station-show-images-metabox',
		__( 'Show Images', 'radio-station' ),
		'radio_station_show_images_metabox',
		array( RADIO_STATION_SHOW_SLUG, RADIO_STATION_OVERRIDE_SLUG ),
		'side',
		'low'
	);
}

// -------------------
// Show Images Metabox
// -------------------
// 2.3.0: added show header and avatar image metabox
// ref: https://codex.wordpress.org/Javascript_Reference/wp.media
function radio_station_show_images_metabox() {

	global $post;

	if ( isset( $_GET['avatar_refix'] ) && ( 'yes' == $_GET['avatar_refix'] ) ) {
		delete_post_meta( $post->ID, '_rs_image_updated', true );
		$show_avatar = radio_station_get_show_avatar_id( $post->ID );
		echo "Transferred ID: " . $show_avatar;
	}

	wp_nonce_field( 'radio-station', 'show_images_nonce' );
	$upload_link = get_upload_iframe_src( 'image', $post->ID );

	// --- get show avatar image info ---
	$show_avatar = get_post_meta( $post->ID, 'show_avatar', true );
	$show_avatar_src = wp_get_attachment_image_src( $show_avatar, 'full' );
	$has_show_avatar = is_array( $show_avatar_src );

	// --- show avatar image ---
	echo '<div id="show-avatar-image">';

	// --- image container ---
	echo '<div class="custom-image-container">';
	if ( $has_show_avatar ) {
		echo '<img src="' . esc_url( $show_avatar_src[0] ) . '" alt="" style="max-width:100%;">';
	}
	echo '</div>';

	// --- add and remove links ---
	echo '<p class="hide-if-no-js">';
	$hidden = '';
	if ( $has_show_avatar ) {
		$hidden = ' hidden';
	}
	echo '<a class="upload-custom-image' . esc_attr( $hidden ) . '" href="' . esc_url( $upload_link ) . '">';
	echo esc_html( __( 'Set Show Avatar Image', 'radio-station' ) );
	echo '</a>';
	$hidden = '';
	if ( !$has_show_avatar ) {
		$hidden = ' hidden';
	}
	echo '<a class="delete-custom-image' . esc_attr( $hidden ) . '" href="#">';
	echo esc_html( __( 'Remove Show Avatar Image', 'radio-station' ) );
	echo '</a>';
	echo '</p>';

	// --- hidden input for image ID ---
	echo '<input class="custom-image-id" name="show_avatar" type="hidden" value="' . esc_attr( $show_avatar ) . '">';

	echo '</div>';

	// --- check if show content header image is enabled ---
	$header_image = radio_station_get_setting( 'show_header_image' );
	if ( $header_image ) {

		// --- get show header image info
		$show_header = get_post_meta( $post->ID, 'show_header', true );
		$show_header_src = wp_get_attachment_image_src( $show_header, 'full' );
		$has_show_header = is_array( $show_header_src );

		// --- show header image ---
		echo '<div id="show-header-image">';

		// --- image container ---
		echo '<div class="custom-image-container">';
		if ( $has_show_header ) {
			echo '<img src="' . esc_url( $show_header_src[0] ) . '" alt="" style="max-width:100%;">';
		}
		echo '</div>';

		// --- add and remove links ---
		echo '<p class="hide-if-no-js">';
		$hidden = '';
		if ( $has_show_header ) {
			$hidden = ' hidden';
		}
		echo '<a class="upload-custom-image' . esc_attr( $hidden ) . '" href="' . esc_url( $upload_link ) . '">';
		echo esc_html( __( 'Set Show Header Image', 'radio-station' ) );
		echo '</a>';
		$hidden = '';
		if ( !$has_show_header ) {
			$hidden = ' hidden';
		}
		echo '<a class="delete-custom-image' . esc_attr( $hidden ) . '" href="#">';
		echo esc_html( __( 'Remove Show Header Image', 'radio-station' ) );
		echo '</a>';
		echo '</p>';

		// --- hidden input for image ID ---
		echo '<input class="custom-image-id" name="show_header" type="hidden" value="' . esc_attr( $show_header ) . '">';

		echo '</div>';

	}

	// --- set images autosave nonce and iframe ---
	$images_autosave_nonce = wp_create_nonce( 'show-images-autosave' );
	echo '<input type="hidden" id="show-images-save-nonce" value="' . esc_attr( $images_autosave_nonce ) . '">';
	echo '<iframe src="javascript:void(0);" name="show-images-save-frame" id="show-images-save-frame" style="display:none;"></iframe>';

	// --- image selection script ---
	$confirm_remove = __( 'Are you sure you want to remove this image?', 'radio-station' );
	$js = "jQuery(function(){

		var mediaframe, parentdiv,
			imagesmetabox = jQuery('#radio-station-show-images-metabox'),
			addimagelink = imagesmetabox.find('.upload-custom-image'),
			deleteimagelink = imagesmetabox.find('.delete-custom-image');

		/* Add Image on Click */
		addimagelink.on( 'click', function( event ) {

			event.preventDefault();
			parentdiv = jQuery(this).parent().parent();

			if (mediaframe) {mediaframe.open(); return;}
			mediaframe = wp.media({
				title: 'Select or Upload Image',
				button: {text: 'Use this Image'},
				multiple: false
			});

			mediaframe.on( 'select', function() {
				var attachment = mediaframe.state().get('selection').first().toJSON();
				image = '<img src=\"'+attachment.url+'\" alt=\"\" style=\"max-width:100%;\"/>';
				parentdiv.find('.custom-image-container').append(image);
				parentdiv.find('.custom-image-id').val(attachment.id);
				parentdiv.find('.upload-custom-image').addClass('hidden');
				parentdiv.find('.delete-custom-image').removeClass('hidden');

				/* auto-save image via AJAX */
				postid = '" . $post->ID . "'; imgid = attachment.id;
				if (parentdiv.attr('id') == 'show-avatar-image') {imagetype = 'avatar';}
				if (parentdiv.attr('id') == 'show-header-image') {imagetype = 'header';}
				imagessavenonce = jQuery('#show-images-save-nonce').val();
				framesrc = ajaxurl+'?action=radio_station_show_images_save';
				framesrc += '&post_id='+postid+'&image_type='+imagetype;
				framesrc += '&image_id='+imgid+'&_wpnonce='+imagessavenonce;
				jQuery('#show-images-save-frame').attr('src', framesrc);
			});

			mediaframe.open();
		});

		/* Delete Image on Click */
		deleteimagelink.on( 'click', function( event ) {
			event.preventDefault();
			agree = confirm('" . esc_js( $confirm_remove ) . "');
			if (!agree) {return;}
			parentdiv = jQuery(this).parent().parent();
			parentdiv.find('.custom-image-container').html('');
			parentdiv.find('.custom-image-id').val('');
			parentdiv.find('.upload-custom-image').removeClass('hidden');
			parentdiv.find('.delete-custom-image').addClass('hidden');
		});

	});";

	// --- enqueue script inline ---
	// 2.3.0: enqueue instead of echoing
	wp_add_inline_script( 'radio-station-admin', $js );

}

// ---------------------------------
// AJAX to AutoSave Images on Change
// ---------------------------------
add_action( 'wp_ajax_radio_station_show_images_save', 'radio_station_show_images_save' );
function radio_station_show_images_save() {

	global $post;

	// --- sanitize posted values ---
	if ( isset( $_GET['post_id'] ) ) {
		$post_id = absint( $_GET['post_id'] );
		if ( $post_id < 1 ) {
			unset( $post_id );
		}
	}

	// 2.3.3.6: get post for checking capability
	$post = get_post( $post_id );
	if ( !$post ) {
		exit;
	}

	// --- check edit capability ---
	if ( !current_user_can( 'edit_shows' ) ) {
		exit;
	}

	// --- verify nonce value ---
	if ( !isset( $_GET['_wpnonce'] ) || !wp_verify_nonce( $_GET['_wpnonce'], 'show-images-autosave' ) ) {
		exit;
	}

	if ( isset( $_GET['image_id'] ) ) {
		$image_id = absint( $_GET['image_id'] );
		if ( $image_id < 1 ) {
			unset( $image_id );
		}
	}
	if ( isset( $_GET['image_type'] ) ) {
		if ( in_array( $_GET['image_type'], array( 'header', 'avatar' ) ) ) {
			$image_type = $_GET['image_type'];
		}
	}

	if ( isset( $post_id ) && isset( $image_id ) && isset( $image_type ) ) {
		update_post_meta( $post_id, 'show_' . $image_type, $image_id );
	} else {
		exit;
	}

	// --- add image updated flag ---
	// (help prevent duplication on new posts)
	$updated = get_post_meta( $post_id, '_rs_image_updated', true );
	if ( !$updated ) {
		add_post_meta( $post_id, '_rs_image_updated', true );
	}

	// --- refresh parent frame nonce ---
	$images_save_nonce = wp_create_nonce( 'show-images-autosave' );
	echo "<script>parent.document.getElementById('show-images-save-nonce').value = '" . esc_js( $images_save_nonce ) . "';</script>";

	exit;
}

// --------------------
// Update Show Metadata
// --------------------
// 2.3.2: added AJAX show save action
add_action( 'wp_ajax_radio_station_show_save_shifts', 'radio_station_show_save_data' );
add_action( 'save_post', 'radio_station_show_save_data' );
function radio_station_show_save_data( $post_id ) {

	// --- verify if this is an auto save routine ---
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// --- make sure we have a post ID for AJAX save ---
	// 2.3.2: added AJAX shift saving checks
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		// 2.3.3: added double check for AJAX action match
		if ( !isset( $_REQUEST['action'] ) || ( 'radio_station_show_save_shifts' != $_REQUEST['action'] ) ) {
			return;
		}
		if ( !isset( $_POST['show_id'] ) || ( '' == $_POST['show_id'] ) ) {
			return;
		}
		$post_id = absint( $_POST['show_id'] );
		$post = get_post( $post_id );

		// --- check for errors ---
		$error = false;
		if ( !isset( $_POST['show_shifts_nonce'] ) || !wp_verify_nonce( $_POST['show_shifts_nonce'], 'radio-station' ) ) {
			$error = __( 'Expired. Publish or Update instead.', 'radio-station' );
		} elseif ( !$post ) {
			$error = __( 'Failed. Invalid Show.', 'radio-station' );
		} elseif ( !current_user_can( 'edit_shows' ) ) {
			$error = __( 'Failed. Publish or Update instead.', 'radio-station' );
		}

		// --- send error to parent frame ---
		if ( $error ) {
			echo "<script>parent.document.getElementById('shifts-saving-message').style.display = 'none';
			parent.document.getElementById('shifts-error-message').style.display = '';
			parent.document.getElementById('shifts-error-message').innerHTML = '" . esc_js( $error ) . "';
			form = parent.document.getElementById('track-save-form'); form.parentNode.removeChild(form);
			</script>";

			exit;
		}
	}

	// --- set show meta changed flags ---
	$show_meta_changed = $show_shifts_changed = false;

	// --- get posted DJ / host list ---
	// 2.2.7: check DJ post value is set
	if ( isset( $_POST['show_hosts_nonce'] ) && wp_verify_nonce( $_POST['show_hosts_nonce'], 'radio-station' ) ) {

		if ( isset( $_POST['show_user_list'] ) ) {
			$hosts = $_POST['show_user_list'];
		}
		if ( !isset( $hosts ) || !is_array( $hosts ) ) {
			$hosts = array();
		} else {
			foreach ( $hosts as $i => $host ) {
				if ( !empty( $host ) ) {
					$userid = get_user_by( 'ID', $host );
					if ( !$userid ) {
						unset( $hosts[$i] );
					}
				}
			}
		}
		update_post_meta( $post_id, 'show_user_list', $hosts );
		$prev_hosts = get_post_meta( $post_id, 'show_user_list', true );
		if ( $prev_hosts != $hosts ) {
			$show_meta_changed = true;
		}
	}

	// --- get posted show producers ---
	// 2.3.0: added show producer sanitization
	if ( isset( $_POST['show_producers_nonce'] ) && wp_verify_nonce( $_POST['show_producers_nonce'], 'radio-station' ) ) {

		if ( isset( $_POST['show_producer_list'] ) ) {
			$producers = $_POST['show_producer_list'];
		}
		if ( !isset( $producers ) || !is_array( $producers ) ) {
			$producers = array();
		} else {
			foreach ( $producers as $i => $producer ) {
				if ( !empty( $producer ) ) {
					$userid = get_user_by( 'ID', $producer );
					if ( !$userid ) {
						unset( $producers[$i] );
					}
				}
			}
		}
		// 2.3.0: added save of show producers
		update_post_meta( $post_id, 'show_producer_list', $producers );
		$prev_producers = get_post_meta( $post_id, 'show_producer_list', true );
		if ( $prev_producers != $producers ) {
			$show_meta_changed = true;
		}
	}

	// --- save show meta data ---
	// 2.3.0: added separate nonce check for show meta
	if ( isset( $_POST['show_meta_nonce'] ) && wp_verify_nonce( $_POST['show_meta_nonce'], 'radio-station' ) ) {

		// --- get the meta data to be saved ---
		// 2.2.3: added show metadata value sanitization
		$file = wp_strip_all_tags( trim( $_POST['show_file'] ) );
		$email = sanitize_email( trim( $_POST['show_email'] ) );
		$link = filter_var( trim( $_POST['show_link'] ), FILTER_SANITIZE_URL );
		$patreon_id = sanitize_title( $_POST['show_patreon'] );

		// 2.3.3.6: added phone number with character filter validation
		$phone = trim( $_POST['show_phone'] );
		if ( strlen( $phone ) > 0 ) {
			$phone = str_split( $phone, 1 );
			$phone = preg_filter( '/^[0-9+\(\)#\.\s\-]+$/', '$0', $phone );
			if ( count( $phone ) > 0 ) {
				$phone = implode( '', $phone );
			} else {
				$phone = '';
			}
		}

		// 2.2.8: removed strict in_array checking
		// 2.3.2: fix for unchecked boxes index warning
		$active = $download = '';
		if ( isset( $_POST['show_active'] ) ) {
			$active = $_POST['show_active'];
		}
		if ( !in_array( $active, array( '', 'on' ) ) ) {
			$active = '';
		}
		// 2.3.2: added download disable switch
		if ( isset( $_POST['show_download'] ) ) {
			$download = $_POST['show_download'];
		}
		if ( !in_array( $download, array( '', 'on' ) ) ) {
			$download = '';
		}

		// --- get existing values and check if changed ---
		// 2.3.0: added check against previous values
		// 2.3.2: added download disable switch
		// 2.3.3.6: added phone number field saving
		$prev_file = get_post_meta( $post_id, 'show_file', true );
		$prev_download = get_post_meta( $post_id, 'show_download', true );
		$prev_email = get_post_meta( $post_id, 'show_email', true );
		$prev_phone = get_post_meta( $post_id, 'show_phone', true );
		$prev_active = get_post_meta( $post_id, 'show_active', true );
		$prev_link = get_post_meta( $post_id, 'show_link', true );
		$prev_patreon_id = get_post_meta( $post_id, 'show_patreon', true );
		if ( ( $prev_active != $active ) || ( $prev_link != $link )
		     || ( $prev_email != $email ) || ( $prev_phone != $phone )
		     || ( $prev_file != $file ) || ( $prev_download != $download )
		     || ( $prev_patreon_id != $patreon_id ) ) {
			$show_meta_changed = true;
		}

		// --- update the show metadata ---
		// 2.3.2: added download disable switch
		update_post_meta( $post_id, 'show_file', $file );
		update_post_meta( $post_id, 'show_download', $download );
		update_post_meta( $post_id, 'show_email', $email );
		update_post_meta( $post_id, 'show_phone', $phone );
		update_post_meta( $post_id, 'show_active', $active );
		update_post_meta( $post_id, 'show_link', $link );
		update_post_meta( $post_id, 'show_patreon', $patreon_id );
	}


	// --- update the show images ---
	if ( isset( $_POST['show_images_nonce'] ) && wp_verify_nonce( $_POST['show_images_nonce'], 'radio-station' ) ) {

		// --- show header image ---
		if ( isset( $_POST['show_header'] ) ) {
			$header = absint( $_POST['show_header'] );
			if ( $header > 0 ) {
				// $prev_header = get_post_meta( $post_id, 'show_header', true );
				// if ( $header != $prev_header ) {$show_meta_changed = true;}
				update_post_meta( $post_id, 'show_header', $header );
			}
		}

		// --- show avatar image ---
		$avatar = absint( $_POST['show_avatar'] );
		if ( $avatar > 0 ) {
			// $prev_avatar = get_post_meta( $post_id, 'show_avatar', true );
			// if ( $avatar != $prev_avatar ) {$show_meta_changed = true;}
			update_post_meta( $post_id, 'show_avatar', $avatar );
		}

		// --- add image updated flag ---
		// (to prevent duplication for new posts)
		$updated = get_post_meta( $post_id, '_rs_image_updated', true );
		if ( !$updated ) {
			add_post_meta( $post_id, '_rs_image_updated', true );
		}
	}

	// --- check show shift nonce ---
	if ( isset( $_POST['show_shifts_nonce'] ) && wp_verify_nonce( $_POST['show_shifts_nonce'], 'radio-station' ) ) {

		// --- loop posted show shift times ---
		// 2.3.1: added check if any shifts are set (fix undefined index warning)
		$shifts = $new_shifts = array();
		if ( isset( $_POST['show_sched'] ) ) {
			$shifts = $_POST['show_sched'];
		}
		$prev_shifts = radio_station_get_show_schedule( $post_id );
		$days = array( '', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );
		if ( $shifts && is_array( $shifts ) && ( count( $shifts ) > 0 ) ) {
			foreach ( $shifts as $i => $shift ) {

				// --- reset shift disabled flag ---
				// 2.3.0: added shift disabling logic
				$disabled = false;

				// --- maybe generate new unique shift ID ---
				if ( 'new-' == substr( $i, 0, 4 ) ) {
					$i = radio_station_unique_shift_id();
				}

				// --- loop shift keys ---
				foreach ( $shift as $key => $value ) {

					// --- validate according to key ---
					$isvalid = false;
					if ( 'day' === $key ) {

						// --- check shift day ---
						// 2.2.8: remove strict in_array checking
						if ( in_array( $value, $days ) ) {
							$isvalid = true;
						}
						if ( '' == $value ) {
							// 2.3.0: auto-disable if no day is set
							$disabled = true;
						}

					} elseif ( ( 'start_hour' === $key ) || ( 'end_hour' === $key ) ) {

						// --- check shift start and end hour ---
						if ( empty( $value ) ) {
							// 2.3.0: auto-disable shift if not start/end hour
							$isvalid = $disabled = true;
						} elseif ( ( absint( $value ) > 0 ) && ( absint( $value ) < 13 ) ) {
							$isvalid = true;
						}

					} elseif ( ( 'start_min' === $key ) || ( 'end_min' === $key ) ) {

						// --- check shift start and end minute ---
						if ( empty( $value ) ) {
							// 2.3.0: auto-set minute value to 00 if empty
							$isvalid = true;
							$value = '00';
						} elseif ( ( absint( $value ) > - 1 ) && ( absint( $value ) < 61 ) ) {
							$isvalid = true;
						} else {
							$disabled = true;
						}

					} elseif ( ( 'start_meridian' === $key ) || ( 'end_meridian' === $key ) ) {

						// --- check shift meridiem ---
						$valid = array( '', 'am', 'pm' );
						// 2.2.8: remove strict in_array checking
						if ( in_array( $value, $valid ) ) {
							$isvalid = true;
						}
						if ( '' == $value ) {
							$disabled = true;
						}

					} elseif ( 'encore' === $key ) {

						// --- check shift encore switch ---
						// 2.2.4: fix to missing encore sanitization saving
						$valid = array( '', 'on' );
						// 2.2.8: remove strict in_array checking
						if ( in_array( $value, $valid ) ) {
							$isvalid = true;
						}

					} elseif ( 'disabled' == $key ) {

						// --- check shift disabled switch ---
						// 2.3.0: added shift disable switch
						// note: overridden on incomplete data or shift conflict
						$valid = array( '', 'yes' );
						if ( in_array( $value, $valid ) ) {
							$isvalid = true;
						}
						if ( 'yes' == $value ) {
							$disabled = true;
						}

					}

					// --- if valid add to new schedule ---
					if ( $isvalid ) {
						$new_shifts[$i][$key] = $value;
					} else {
						$new_shifts[$i][$key] = '';
					}
				}

				// --- check for shift conflicts with other shows ---
				// 2.3.0: added show shift conflict checking
				if ( !$disabled ) {
					$conflicts = radio_station_check_shift( $post_id, $new_shifts[$i], 'shows' );
					if ( $conflicts ) {
						$disabled = true;
						if ( RADIO_STATION_DEBUG ) {
							echo "*Conflicting Shift Disabled*";
						}
					}
				}

				// --- disable if incomplete data or shift conflicts ---
				if ( $disabled ) {
					$new_shifts[$i]['disabled'] = 'yes';
					if ( RADIO_STATION_DEBUG ) {
						echo "*Shift Disabled*";
					}
				}

			}

			// --- recheck for conflicts with other shifts for this show ---
			// 2.3.0: added new shift conflict checking
			$new_shifts = radio_station_check_new_shifts( $new_shifts );

			// --- update the schedule meta entry ---
			// 2.3.0: check if shift times have changed before saving
			if ( $new_shifts != $prev_shifts ) {
				$show_shifts_changed = true;
				update_post_meta( $post_id, 'show_sched', $new_shifts );
			}
		} else {
			// 2.3.0: fix to clear data if all shifts removed
			delete_post_meta( $post_id, 'show_sched' );
			$show_shifts_changed = true;
		}
	}

	// --- maybe clear transient data ---
	// 2.3.0: added to clear transients if any meta has changed
	// 2.3.3: remove current show transient
	if ( $show_meta_changed || $show_shifts_changed ) {
		delete_transient( 'radio_station_current_schedule' );
		delete_transient( 'radio_station_next_show' );
		delete_transient( 'radio_station_previous_show' );

		// 2.3.4: delete all prefixed transients (for times)
		radio_station_delete_transients_with_prefix( 'radio_station_current_schedule' );
		radio_station_delete_transients_with_prefix( 'radio_station_next_show' );
		radio_station_delete_transients_with_prefix( 'radio_station_previous_show' );

		do_action( 'radio_station_clear_data', 'show', $post_id );
		do_action( 'radio_station_clear_data', 'show_meta', $post_id );

		// --- set last updated schedule time ---
		// 2.3.2: added for data API use
		update_option( 'radio_station_schedule_updated', time() );

		// --- maybe send directory ping ---
		// 2.3.1: added directory update ping option
		// 2.3.2: queue directory ping
		radio_station_queue_directory_ping();
	}

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		if ( isset( $_POST['action'] ) && ( 'radio_station_show_save_shifts' == $_POST['action'] ) ) {

			// --- (hidden) debug information ---
			echo "Posted Shifsts: " . print_r( $_POST['show_sched'], true ) . PHP_EOL;
			echo "New Shifts: " . print_r( $new_shifts, true ) . PHP_EOL;

			// --- display shifts saved message ---
			$show_shifts_nonce = wp_create_nonce( 'radio-station' );
			echo "<script>parent.document.getElementById('shifts-saving-message').style.display = 'none';
			parent.document.getElementById('shifts-saved-message').style.display = '';
			setTimeout(function() {parent.document.getElementById('shifts-saved-message').style.display = 'none';}, 5000);
			form = parent.document.getElementById('shift-save-form'); form.parentNode.removeChild(form);
			parent.document.getElementById('show_shifts_nonce').value = '" . esc_js( $show_shifts_nonce ) . "';
			</script>";

			// --- output new show shifts list ---
			echo '<div id="shifts-list">';
			if ( isset( $_REQUEST['check-bypass'] ) && ( '1' == $_REQUEST['check-bypass'] ) ) {
				echo '<input type="hidden" name="check-bypass" value="1">';
			}
			$table = radio_station_show_shifts_table( $post_id );
			if ( $table['conflicts'] ) {
				radio_station_shifts_conflict_message();
			}

			// phpcs:ignore WordPress.Security.OutputNotEscaped
			echo $table['list'];

			echo '<div id="updated-div"></div>';
			echo '</div>';

			// --- refresh show shifts list ---
			echo "<script>showslist = parent.document.getElementById('shifts-list');
			showslist.innerHTML = document.getElementById('shifts-list').innerHTML;</script>";

			// 2.3.3.6: clear changes may not have been saved window reload message
			echo "<script>if (parent.window.onbeforeunloadset) {
				parent.window.onbeforeunload = parent.storedonbeforeunload;
				parent.window.onbeforeunloadset = false;
			}</script>";

			// --- alert on conflicts ---
			if ( $table['conflicts'] ) {
				$warning = __( 'Warning! Shift conflicts detected.', 'radio-station' );
				echo "<script>alert('" . esc_js( $warning ) . "');</script>";
			}

			exit;
		}
	}

}

// -----------------
// Save Output Debug
// -----------------
add_action( 'save_post_' . RADIO_STATION_SHOW_SLUG, 'radio_station_save_debug_start', 0 );
add_action( 'save_post_' . RADIO_STATION_OVERRIDE_SLUG, 'radio_station_save_debug_start', 0 );
add_action( 'save_post_' . RADIO_STATION_PLAYLIST_SLUG, 'radio_station_save_debug_start', 0 );
function radio_station_save_debug_start( $post_id ) {
	if ( !RADIO_STATION_SAVE_DEBUG ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	ob_start();
}
add_action( 'save_post_' . RADIO_STATION_SHOW_SLUG, 'radio_station_save_debug_start', 9999 );
add_action( 'save_post_' . RADIO_STATION_OVERRIDE_SLUG, 'radio_station_save_debug_start', 9999 );
add_action( 'save_post_' . RADIO_STATION_PLAYLIST_SLUG, 'radio_station_save_debug_start', 9999 );
function radio_station_save_debug_end( $post_id ) {
	if ( !RADIO_STATION_SAVE_DEBUG ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	$contents = ob_get_contents();
	ob_end_clean();
	if ( strlen( $contents ) > 0 ) {
		echo "Output Detected During Save (preventing redirect):<br>";
		echo '<textarea rows="40" cols="80">' . $contents . '</textarea>';
		exit;
	}
}

// ---------------------
// Add Show List Columns
// ---------------------
// 2.2.7: added data columns to show list display
add_filter( 'manage_edit-' . RADIO_STATION_SHOW_SLUG . '_columns', 'radio_station_show_columns', 6 );
function radio_station_show_columns( $columns ) {

	if ( isset( $columns['thumbnail'] ) ) {
		unset( $columns['thumbnail'] );
	}
	if ( isset( $columns['post_thumb'] ) ) {
		unset( $columns['post_thumb'] );
	}

	$date = $columns['date'];
	unset( $columns['date'] );
	$comments = $columns['comments'];
	unset( $columns['comments'] );
	$genres = $columns['taxonomy-' . RADIO_STATION_GENRES_SLUG];
	unset( $columns['taxonomy-' . RADIO_STATION_GENRES_SLUG] );
	$languages = $columns['taxonomy-' . RADIO_STATION_LANGUAGES_SLUG];
	unset( $columns['taxonomy-' . RADIO_STATION_LANGUAGES_SLUG] );

	$columns['active'] = esc_attr( __( 'Active?', 'radio-station' ) );
	// 2.3.0: added show description indicator column
	$columns['description'] = esc_attr( __( 'About?', 'radio-station' ) );
	$columns['shifts'] = esc_attr( __( 'Shifts', 'radio-station' ) );
	// 2.3.0: change DJs column label to Hosts
	$columns['hosts'] = esc_attr( __( 'Hosts', 'radio-station' ) );
	$columns['taxonomy-' . RADIO_STATION_GENRES_SLUG] = $genres;
	$columns['taxonomy-' . RADIO_STATION_LANGUAGES_SLUG] = $languages;
	$columns['comments'] = $comments;
	$columns['date'] = $date;
	$columns['show_image'] = esc_attr( __( 'Show Avatar', 'radio-station' ) );

	return $columns;
}

// ---------------------
// Show List Column Data
// ---------------------
// 2.2.7: added data columns for show list display
add_action( 'manage_' . RADIO_STATION_SHOW_SLUG . '_posts_custom_column', 'radio_station_show_column_data', 5, 2 );
function radio_station_show_column_data( $column, $post_id ) {

	if ( 'active' == $column ) {

		$active = get_post_meta( $post_id, 'show_active', true );
		if ( 'on' == $active ) {
			echo esc_html( __( 'Yes', 'radio-station' ) );
		} else {
			echo esc_html( __( 'No', 'radio-station' ) );
		}

	} elseif ( 'description' == $column ) {

		// 2.3.0: added show description indicator
		global $wpdb;
		$query = "SELECT post_content FROM " . $wpdb->prefix . "posts WHERE ID = %d";
		$query = $wpdb->prepare( $query, $post_id );
		$content = $wpdb->get_var( $query );
		if ( !$content || ( trim( $content ) == '' ) ) {
			echo '<b>' . esc_html( __( 'No', 'radio-station' ) ) . '</b>';
		} else {
			echo esc_html( __( 'Yes', 'radio-station' ) );
		}

	} elseif ( 'shifts' == $column ) {

		$active = get_post_meta( $post_id, 'show_active', true );
		if ( 'on' == $active ) {
			$active = true;
		}

		// 2.3.0: check using dates for reliability
		$now = radio_station_get_now();
		$weekdays = radio_station_get_schedule_weekdays();
		$weekdates = radio_station_get_schedule_weekdates( $weekdays, $now );

		$shifts = get_post_meta( $post_id, 'show_sched', true );
		if ( $shifts && is_array( $shifts ) && ( count( $shifts ) > 0 ) ) {

			$sorted_shifts = $dayless_shifts = array();
			foreach ( $shifts as $shift ) {
				// 2.3.2: added check that shift day is not empty
				if ( isset( $shift['day'] ) && ( '' != $shift['day'] ) ) {
					// 2.3.2: fix to convert shift time to 24 hour format
					$shift_time = $shift['start_hour'] . ":" . $shift['start_min'] . ' ' . $shift['start_meridian'];
					$shift_time = radio_station_convert_shift_time( $shift_time );
					$shift_time = $weekdates[$shift['day']] . $shift_time;
					$timestamp = radio_station_to_time( $shift_time );
					$sortedshifts[$timestamp] = $shift;
				} else {
					$dayless_shifts[] = $shift;
				}
			}
			ksort( $sortedshifts );

			foreach ( $sortedshifts as $shift ) {

				// 2.3.0: highlight disabled shifts
				$classes = array( 'show-shift' );
				$disabled = false;
				$title = '';
				if ( isset( $shift['disabled'] ) && ( 'yes' == $shift['disabled'] ) ) {
					$disabled = true;
					$classes[] = 'disabled';
					$title = __( 'This Shift is Disabled.', 'radio-station' );
				}

				// --- check and highlight conflicts ---
				// 2.3.0: added shift conflict checking
				$conflicts = radio_station_check_shift( $post_id, $shift );
				if ( $conflicts ) {
					$classes[] = 'conflict';
					if ( $disabled ) {
						$title = __( 'This Shift has Schedule Conflicts and is Disabled.', 'radio-station' );
					} else {
						$title = __( 'This Shift has Schedule Conflicts.', 'radio-station' );
					}
				}

				// 2.3.0: also highlight if the show is not active
				if ( !$active ) {
					if ( !in_array( 'disabled', $classes ) ) {
						$classes[] = 'disabled';
					}
					$title = __( 'This Show is not currently active.', 'radio-station' );
				}
				$classlist = implode( ' ', $classes );

				echo "<div class='" . esc_attr( $classlist ) . "' title='" . esc_attr( $title ) . "'>";

				// --- get shift start and end times ---
				// 2.3.2: fix to convert to 24 hour time
				$start = $shift['start_hour'] . ":" . $shift['start_min'] . $shift['start_meridian'];
				$end = $shift['end_hour'] . ":" . $shift['end_min'] . $shift['end_meridian'];
				$start_time = radio_station_convert_shift_time( $start );
				$end_time =  radio_station_convert_shift_time( $end );
				$start_time = radio_station_to_time( $weekdates[$shift['day']] . ' ' . $start_time );
				$end_time = radio_station_to_time( $weekdates[$shift['day']] . ' ' . $end_time );

				// --- make weekday filter selections bold ---
				// 2.3.0: fix to bolding only if weekday isset
				$bold = false;
				if ( isset( $_GET['weekday'] ) ) {
					$weekday = trim( $_GET['weekday'] );
					$nextday = radio_station_get_next_day( $weekday );
					// 2.3.0: handle shifts that go overnight for weekday filter
					if ( ( $weekday == $shift['day'] ) || ( ( $shift['day'] == $nextday ) && ( $end_time < $start_time ) ) ) {
						echo "<b>";
						$bold = true;
					}
				}

				echo esc_html( radio_station_translate_weekday( $shift['day'] ) );
				echo " " . esc_html( $start ) . " - " . esc_html( $end );
				if ( $bold ) {
					echo "</b>";
				}
				echo "</div>";
			}

			// --- dayless shifts ---
			// 2.3.2: added separate display of dayless shifts
			if ( count( $dayless_shifts ) > 0 ) {
				foreach ( $dayless_shifts as $shift ) {
					$title = __( 'This shift is disabled as no day is set.', 'radio-station' );
					echo "<div class='show-shift disabled' title='" . esc_attr( $title ) . "'>";
					$start = $shift['start_hour'] . ":" . $shift['start_min'] . $shift['start_meridian'];
					$end = $shift['end_hour'] . ":" . $shift['end_min'] . $shift['end_meridian'];
					echo esc_html( $start ) . " - " . esc_html( $end );
					echo "</div>";
				}
			}
		}

	} elseif ( 'hosts' == $column ) {

		$hosts = get_post_meta( $post_id, 'show_user_list', true );
		if ( $hosts && ( count( $hosts ) > 0 ) ) {
			foreach ( $hosts as $host ) {
				$user_info = get_userdata( $host );
				echo esc_html( $user_info->display_name ) . "<br>";
			}
		}

	} elseif ( 'producers' == $column ) {

		// 2.3.0: added column for Producers
		$producers = get_post_meta( $post_id, 'show_producer_list', true );
		if ( $producers && ( count( $producers ) > 0 ) ) {
			foreach ( $producers as $producer ) {
				$user_info = get_userdata( $producer );
				echo esc_html( $user_info->display_name ) . "<br>";
			}
		}

	} elseif ( 'show_image' == $column ) {

		// 2.3.0: get show avatar (with fallback to thumbnail)
		$image_url = radio_station_get_show_avatar_url( $post_id );
		if ( $image_url ) {
			echo "<div class='show-image'><img src='" . esc_url( $image_url ) . "' alt='" . esc_html( __( 'Show Avatar', 'radio-station' ) ) . "'></div>";
		}

	}
}

// -----------------------
// Show List Column Styles
// -----------------------
// 2.2.7: added show column styles
add_action( 'admin_footer', 'radio_station_show_column_styles' );
function radio_station_show_column_styles() {
	$current_screen = get_current_screen();
	if ( 'edit-' . RADIO_STATION_SHOW_SLUG !== $current_screen->id ) {
		return;
	}

	echo "<style>#shifts {width: 200px;} #active, #description, #comments {width: 50px;}
	.show-image {width: 100px;} .show-image img {width: 100%; height: auto;}
	.show-shift.disabled {border: 1px dashed orange;}
	.show-shift.conflict {border: 1px solid red;}
	.show-shift.disabled.conflict {border: 1px dashed red;}</style>";
}

// -------------------------
// Add Show Shift Day Filter
// -------------------------
// 2.2.7: added show day selection filtering
add_action( 'restrict_manage_posts', 'radio_station_show_day_filter', 10, 2 );
function radio_station_show_day_filter( $post_type, $which ) {

	if ( RADIO_STATION_SHOW_SLUG !== $post_type ) {
		return;
	}

	// -- maybe get specified day ---
	$d = isset( $_GET['weekday'] ) ? $_GET['weekday'] : 0;

	// --- show day selector ---
	$days = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );

	echo '<label for="filter-by-show-day" class="screen-reader-text">' . esc_html( __( 'Filter by show day', 'radio-station' ) ) . '</label>';
	echo '<select name="weekday" id="filter-by-show-day">';
	echo '<option value="0" ' . selected( $d, 0, false ) . '>' . esc_html( __( 'All show days', 'radio-station' ) ) . '</option>';

	foreach ( $days as $day ) {
		$label = esc_attr( radio_station_translate_weekday( $day ) );
		echo '<option value="' . esc_attr( $day ) . '" ' . selected( $d, $day, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select>';
}


// --------------------------
// === Schedule Overrides ===
// --------------------------

// -----------------------------
// Add Schedule Override Metabox
// -----------------------------
// --- Add schedule override box to override edit screens ---
add_action( 'add_meta_boxes', 'radio_station_add_schedule_override_metabox' );
function radio_station_add_schedule_override_metabox() {
	// 2.2.2: add high priority to show at top of edit screen
	// 2.3.0: set position to top to be above editor box
	// 2.3.0: update meta box ID for consistency
	// 2.3.2: filter top metabox position
	$position = apply_filters( 'radio_station_metabox_position', 'rstop', 'overrides' );
	add_meta_box(
		'radio-station-override-metabox',
		__( 'Override Schedule', 'radio-station' ),
		'radio_station_schedule_override_metabox',
		RADIO_STATION_OVERRIDE_SLUG,
		$position,
		'high'
	);
}

// -------------------------
// Schedule Override Metabox
// -------------------------
function radio_station_schedule_override_metabox() {

	global $post;

	// 2.2.7: added meridiem translations
	$am = radio_station_translate_meridiem( 'am' );
	$pm = radio_station_translate_meridiem( 'pm' );

	// --- add nonce field for update verification ---
	wp_nonce_field( 'radio-station', 'show_override_nonce' );

	// 2.2.7: add explicit width to date picker field to ensure date is visible
	// 2.3.0: convert template style output to straight php output
	echo '<div id="meta_inner" class="sched-override">';

	// --- get the saved meta as an array ---
	$override = get_post_meta( $post->ID, 'show_override_sched', false );
	if ( $override ) {
		$override = $override[0];
	} else {
		// 2.2.8: fix undefined index warnings for new schedule overrides
		$override = array(
			'date'           => '',
			'start_hour'     => '',
			'start_min'      => '',
			'start_meridian' => '',
			'end_hour'       => '',
			'end_min'        => '',
			'end_meridian'   => ''
		);
	}

	echo '<ul style="list-style:none;">';

	echo '<li style="display:inline-block;">';
	echo esc_html( __( 'Date', 'radio-station' ) ) . ':';
	if ( !empty( $override['date'] ) ) {
		$date = trim( $override['date'] );
	} else {
		$date = '';
	}
	echo '<input type="text" id="OverrideDate" style="width:200px; text-align:center;" name="show_sched[date]" value="' . esc_attr( $date ) . '">';
	echo '</li>';

	echo '<li style="display:inline-block; margin-left:20px;">';
	echo esc_html( __( 'Start Time', 'radio-station' ) ) . ':';
	echo '<select name="show_sched[start_hour]" style="min-width:35px;">';
	echo '<option value=""></option>';
	for ( $i = 1; $i <= 12; $i ++ ) {
		echo '<option value="' . esc_attr( $i ) . '" ' . selected( $override['start_hour'], $i, false ) . '>' . esc_html( $i ) . '</option>';
	}
	echo '</select>';

	// 2.3.4: add common start minutes to top of options
	echo '<select name="show_sched[start_min]" style="min-width:35px;">';
	echo '<option value=""></option>';
	echo '<option value="00">00</option>';
	echo '<option value="15">15</option>';
	echo '<option value="30">30</option>';
	echo '<option value="45">45</option>';
	for ( $i = 0; $i < 60; $i ++ ) {
		$min = $i;
		if ( $i < 10 ) {
			$min = '0' . $i;
		}
		echo '<option value="' . esc_attr( $min ) . '" ' . selected( $override['start_min'], $min, false ) . '>' . esc_html( $min ) . '</option>';
	}
	echo '</select>';
	echo '<select name="show_sched[start_meridian]" style="min-width:35px;">';
	echo '<option value=""></option>';
	echo '<option value="am" ' . selected( $override['start_meridian'], 'am', false ) . '>' . esc_html( $am ) . '</option>';
	echo '<option value="pm" ' . selected( $override['start_meridian'], 'pm', false ) . '>' . esc_html( $pm ) . '</option>';
	echo '</select>';
	echo '</li>';

	// 2.3.4: add common end minutes to top of options
	echo '<li style="display:inline-block; margin-left:20px;">';
	echo esc_html( __( 'End Time', 'radio-station' ) ) . ':';
	echo '<select name="show_sched[end_hour]" style="min-width:35px;">';
	echo '<option value=""></option>';
	for ( $i = 1; $i <= 12; $i ++ ) {
		echo '<option value="' . esc_attr( $i ) . '" ' . selected( $override['end_hour'], $i, false ) . '>' . esc_html( $i ) . '</option>';
	}
	echo '</select>';
	echo '<select name="show_sched[end_min]" style="min-width:35px;">';
	echo '<option value=""></option>';
	echo '<option value="00">00</option>';
	echo '<option value="15">15</option>';
	echo '<option value="30">30</option>';
	echo '<option value="45">45</option>';
	for ( $i = 0; $i < 60; $i ++ ) {
		$min = $i;
		if ( $i < 10 ) {
			$min = '0' . $i;
		}
		echo '<option value="' . esc_attr( $min ) . '"' . selected( $override['end_min'], $min, false ) . '>' . esc_html( $min ) . '</option>';
	}
	echo '</select>';
	echo '<select name="show_sched[end_meridian]" style="min-width:35px;">';
	echo '<option value=""></option>';
	echo '<option value="am" ' . selected( $override['end_meridian'], 'am', false ) . '>' . esc_html( $am ) . '</option>';
	echo '<option value="pm" ' . selected( $override['end_meridian'], 'pm', false ) . '>' . esc_html( $pm ) . '</option>';

	echo '</select>';
	echo '</li>';
	echo '</ul>';
	echo '</div>';

	// --- datepicker z-index style fix ---
	// 2.3.0: added for display conflict with editor buttons
	echo "<style>body.post-type-override #ui-datepicker-div {z-index: 1001 !important;}</style>";

	// --- enqueue datepicker script and styles ---
	// 2.3.0: enqueue for override post type only
	radio_station_enqueue_datepicker();

	// --- enqueue inline script ---
	// 2.3.0: enqeue instead of echoing
	$js = "jQuery(document).ready(function() {
		jQuery('#OverrideDate').datepicker({dateFormat : 'yy-mm-dd'});
	});";
	wp_add_inline_script( 'radio-station-admin', $js );

}

// ------------------------
// Update Schedule Override
// ------------------------
add_action( 'save_post', 'radio_station_override_save_data' );
function radio_station_override_save_data( $post_id ) {

	// --- verify if this is an auto save routine ---
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// --- verify this came from the our screen and with proper authorization ---
	if ( !isset( $_POST['show_override_nonce'] ) || !wp_verify_nonce( $_POST['show_override_nonce'], 'radio-station' ) ) {
		return;
	}

	// --- get the show override data ---
	$sched = $_POST['show_sched'];
	if ( !is_array( $sched ) ) {
		return;
	}

	// --- get/set current schedule for merging ---
	// 2.2.2: added to set default keys
	$current_sched = get_post_meta( $post_id, 'show_override_sched', true );
	if ( !$current_sched || !is_array( $current_sched ) ) {
		$current_sched = array(
			'date'           => '',
			'start_hour'     => '',
			'start_min'      => '',
			'start_meridian' => '',
			'end_hour'       => '',
			'end_min'        => '',
			'end_meridian'   => '',
		);
	}

	// --- sanitize values before saving ---
	// 2.2.2: loop and validate schedule override values
	$changed = false;
	foreach ( $sched as $key => $value ) {
		$isvalid = false;

		// --- validate according to key ---
		if ( 'date' === $key ) {
			// check posted date format (yyyy-mm-dd) with checkdate (month, date, year)
			$parts = explode( '-', $value );
			if ( checkdate( $parts[1], $parts[2], $parts[0] ) ) {
				$isvalid = true;
			}
		} elseif ( ( 'start_hour' === $key ) || ( 'end_hour' === $key ) ) {
			if ( empty( $value ) ) {
				$isvalid = true;
			} elseif ( ( absint( $value ) > 0 ) && ( absint( $value ) < 13 ) ) {
				$isvalid = true;
			}
		} elseif ( ( 'start_min' === $key ) || ( 'end_min' === $key ) ) {
			// 2.2.3: fix to validate 00 minute value
			if ( empty( $value ) ) {
				$isvalid = true;
			} elseif ( absint( $value ) > - 1 && absint( $value ) < 61 ) {
				$isvalid = true;
			}
		} elseif ( ( 'start_meridian' === $key ) || ( 'end_meridian' === $key ) ) {
			$valid = array( '', 'am', 'pm' );
			// 2.2.8: remove strict in_array checking
			if ( in_array( $value, $valid ) ) {
				$isvalid = true;
			}
		}

		// --- if valid add to current schedule setting ---
		if ( $isvalid && ( $value !== $current_sched[$key] ) ) {
			$current_sched[$key] = $value;
			$changed = true;

			// 2.2.7: sync separate meta key for override date
			// (could be used to improve column sorting efficiency)
			if ( 'date' == $key ) {
				update_post_meta( $post_id, 'show_override_date', $value );
			}
		}
	}

	// --- save schedule setting if changed ---
	// 2.3.0: check if changed before saving
	if ( $changed ) {
		update_post_meta( $post_id, 'show_override_sched', $current_sched );

		// --- clear cached schedule data if changed ---
		// 2.3.3: remove current show transient
		// 2.3.4: add previous show transient
		delete_transient( 'radio_station_current_schedule' );
		delete_transient( 'radio_station_next_show' );
		delete_transient( 'radio_station_previous_show' );

		// 2.3.4: delete all prefixed transients (for times)
		radio_station_delete_transients_with_prefix( 'radio_station_current_schedule' );
		radio_station_delete_transients_with_prefix( 'radio_station_next_show' );
		radio_station_delete_transients_with_prefix( 'radio_station_previous_show' );

		// --- set last updated schedule time ---
		// 2.3.2: added for data API use
		update_option( 'radio_station_schedule_updated', time() );

		// --- maybe send directory ping ---
		// 2.3.1: added directory update ping option
		// 2.3.2: queue directory ping
		radio_station_queue_directory_ping();
	}
}

// ----------------------------------
// Add Schedule Override List Columns
// ----------------------------------
// 2.2.7: added data columns to override list display
add_filter( 'manage_edit-' . RADIO_STATION_OVERRIDE_SLUG . '_columns', 'radio_station_override_columns', 6 );
function radio_station_override_columns( $columns ) {

	if ( isset( $columns['thumbnail'] ) ) {
		unset( $columns['thumbnail'] );
	}
	if ( isset( $columns['post_thumb'] ) ) {
		unset( $columns['post_thumb'] );
	}
	$date = $columns['date'];
	unset( $columns['date'] );

	$columns['override_date'] = esc_attr( __( 'Date', 'radio-station' ) );
	$columns['start_time'] = esc_attr( __( 'Start Time', 'radio-station' ) );
	$columns['end_time'] = esc_attr( __( 'End Time', 'radio-station' ) );
	$columns['shows_affected'] = esc_attr( __( 'Affected Show(s) on Date', 'radio-station' ) );
	// 2.3.0: added description indicator column
	$columns['description'] = esc_attr( __( 'About?', 'radio-station' ) );
	// 2.3.2: added missing translation text domain
	$columns['override_image'] = esc_attr( __( 'Image', 'radio-station' ) );
	$columns['date'] = $date;

	return $columns;
}

// -----------------------------
// Schedule Override Column Data
// -----------------------------
// 2.2.7: added data columns for override list display
add_action( 'manage_' . RADIO_STATION_OVERRIDE_SLUG . '_posts_custom_column', 'radio_station_override_column_data', 5, 2 );
function radio_station_override_column_data( $column, $post_id ) {

	global $radio_station_show_shifts;

	$override = get_post_meta( $post_id, 'show_override_sched', true );
	if ( 'override_date' == $column ) {

		// 2.3.2: no need to apply timezone conversions here
		$datetime = strtotime( $override['date'] );
		$month = date( 'F', $datetime );
		$month = radio_station_translate_month( $month );
		$weekday = date( 'l', $datetime );
		$weekday = radio_station_translate_weekday( $weekday );
		echo esc_html( $weekday ) . ' ' . esc_html( date( 'j', $datetime ) ) . ' ';
		echo esc_html( $month ) . ' ' . esc_html( date( 'Y', $datetime ) );

	} elseif ( 'start_time' == $column ) {

		echo esc_html( $override['start_hour'] ) . ':' . esc_html( $override['start_min'] ) . ' ' . esc_html( $override['start_meridian'] );

	} elseif ( 'end_time' == $column ) {

		echo esc_html( $override['end_hour'] ) . ':' . esc_html( $override['end_min'] ) . ' ' . esc_html( $override['end_meridian'] );

	} elseif ( 'shows_affected' == $column ) {

		// --- maybe get all show shifts ---
		if ( isset( $radio_station_show_shifts ) ) {
			$show_shifts = $radio_station_show_shifts;
		} else {
			global $wpdb;
			$query = "SELECT posts.post_title, meta.post_id, meta.meta_value FROM " . $wpdb->prefix . "postmeta AS meta
				JOIN " . $wpdb->prefix . "posts as posts ON posts.ID = meta.post_id
				WHERE meta.meta_key = 'show_sched' AND posts.post_status = 'publish'";
			// 2.3.0: get results as an array
			$show_shifts = $wpdb->get_results( $query, ARRAY_A );
			$radio_station_show_shifts = $show_shifts;
		}
		if ( !$show_shifts || ( count( $show_shifts ) == 0 ) ) {
			return;
		}

		// --- get the override weekday ---
		// 2.3.2: remove date time and get day from date directly
		$weekday = date( 'l', strtotime( $override['date'] ) );

		// --- get start and end override times ---
		// 2.3.2: fix to convert to 24 hour format first
		$start = $override['start_hour'] . ':' . $override['start_min'] . ' ' . $override['start_meridian'];
		$end = $override['end_hour'] . ':' . $override['end_min'] . ' ' . $override['end_meridian'];
		$start = radio_station_convert_shift_time( $start );
		$end = radio_station_convert_shift_time( $end );
		$override_start = radio_station_to_time( $override['date'] . ' ' . $start );
		$override_end = radio_station_to_time( $override['date'] . ' ' . $end );
		// (if the end time is less than start time, adjust end to next day)
		if ( $override_end <= $override_start ) {
			$override_end = $override_end + ( 24 * 60 * 60 );
		}

		// --- loop show shifts ---
		foreach ( $show_shifts as $show_shift ) {
			$shifts = maybe_unserialize( $show_shift['meta_value'] );
			if ( !is_array( $shifts ) ) {
				$shifts = array();
			}

			foreach ( $shifts as $shift ) {
				if ( isset( $shift['day'] ) && ( $shift['day'] == $weekday ) ) {

					// --- get start and end shift times ---
					// 2.3.0: validate shift time to check if complete
					// 2.3.2: replace strtotime with to_time for timezones
					// 2.3.2: fix to convert to 24 hour format first
					$time = radio_station_validate_shift( $shift );
					$start = $shift['start_hour'] . ':' . $shift['start_min'] . ' ' . $shift['start_meridian'];
					$end = $shift['end_hour'] . ':' . $shift['end_min'] . ' ' . $shift['end_meridian'];
					$start = radio_station_convert_shift_time( $start );
					$end = radio_station_convert_shift_time( $end );
					$shift_start = radio_station_to_time( $override['date'] . ' ' . $start );
					$shift_end = radio_station_to_time( $override['date'] . ' ' . $end );
					if ( ( $shift_start == $shift_end ) || ( $shift_start > $shift_end ) ) {
						$shift_end = $shift_end + ( 24 * 60 * 60 );
					}

					if ( RADIO_STATION_DEBUG ) {
						echo $weekday . ': ' . $start . ' to ' . $end . '<br> ' . PHP_EOL;
						echo $override['date'] . ': ' . $shift_start . ' to ' . $shift_end . '<br>' . PHP_EOL;
						echo $override['date'] . ': ' . $override_start . ' to ' . $override_end . '<br>' . PHP_EOL;
						echo '<br>' . PHP_EOL;
					}

					// --- compare override time overlaps to get affected shows ---
					// 2.3.2: fix to override overlap checking logic
					if ( ( ( $override_start < $shift_start ) && ( $override_end > $shift_start ) )
						 || ( ( $override_start < $shift_start ) && ( $override_end > $shift_end ) )
					     || ( $override_start == $shift_start )
					     || ( ( $override_start > $shift_start ) && ( $override_end < $shift_end ) )
					     || ( ( $override_start > $shift_start ) && ( $override_start < $shift_end ) ) ) {

						// 2.3.0: adjust cell display to two line (to allow for long show titles)
						// 2.3.2: deduplicate show check (if same show as last show displayed)
						if ( !isset( $last_show ) || ( $last_show != $show_shift['post_id'] ) ) {
							$active = get_post_meta( $show_shift['post_id'], 'show_active', true );
							if ( 'on' != $active ) {
								echo "[<i>" . esc_html( __( 'Inactive', 'radio-station' ) ) . "</i>] ";
							}
							echo '<b>' . $show_shift['post_title'] . "</b><br>";
						}

						if ( isset( $shift['disabled'] ) && $shift['disabled'] ) {
							echo "[<i>" . esc_html( __( 'Disabled', 'radio-station' ) ) . "</i>] ";
						}
						echo radio_station_translate_weekday( $shift['day'] );
						echo " " . esc_html( $shift['start_hour'] ) . ":" . esc_html( $shift['start_min'] ) . esc_html( $shift['start_meridian'] );
						echo " - " . esc_html( $shift['end_hour'] ) . ":" . esc_html( $shift['end_min'] ) . esc_html( $shift['end_meridian'] );
						echo "<br>";

						// 2.3.2: store last show displayed
						$last_show = $show_shift['post_id'];
					}
				}
			}
		}

	} elseif ( 'description' == $column ) {

		// 2.3.0: added override description indicator
		global $wpdb;
		$query = "SELECT post_content FROM " . $wpdb->prefix . "posts WHERE ID = %d";
		$query = $wpdb->prepare( $query, $post_id );
		$content = $wpdb->get_var( $query );
		if ( !$content || ( trim( $content ) == '' ) ) {
			echo '<b>' . esc_html( __( 'No', 'radio-station' ) ) . '</b>';
		} else {
			echo esc_html( __( 'Yes', 'radio-station' ) );
		}

	} elseif ( 'override_image' == $column ) {
		$thumbnail_url = radio_station_get_show_avatar_url( $post_id );
		if ( $thumbnail_url ) {
			echo "<div class='override_image'><img src='" . esc_url( $thumbnail_url ) . "' alt='" . esc_attr( __( 'Override Logo', 'radio-station' ) ) . "'></div>";
		}
	}
}

// -----------------------------
// Sortable Override Date Column
// -----------------------------
// 2.2.7: added to allow override date column sorting
add_filter( 'manage_edit-override_sortable_columns', 'radio_station_override_sortable_columns' );
function radio_station_override_sortable_columns( $columns ) {
	$columns['override_date'] = 'show_override_date';
	return $columns;
}

// -------------------------------
// Schedule Override Column Styles
// -------------------------------
add_action( 'admin_footer', 'radio_station_override_column_styles' );
function radio_station_override_column_styles() {
	$currentscreen = get_current_screen();
	if ( 'edit-' . RADIO_STATION_OVERRIDE_SLUG !== $currentscreen->id ) {
		return;
	}

	// 2.3.2: set override image column width to override image width
	echo "<style>#shows_affected {width: 250px;} #start_time, #end_time {width: 65px;}
	#override_image, .override_image {width: 75px;}
	.override_image img {width: 100%; height: auto;}</style>";
}

// ----------------------------------
// Add Schedule Override Month Filter
// ----------------------------------
// 2.2.7: added month selection filtering
add_action( 'restrict_manage_posts', 'radio_station_override_date_filter', 10, 2 );
function radio_station_override_date_filter( $post_type, $which ) {

	global $wp_locale;
	if ( RADIO_STATION_OVERRIDE_SLUG !== $post_type ) {
		return;
	}

	// --- get all show override months / years ---
	global $wpdb;
	$overridequery = "SELECT ID FROM " . $wpdb->posts . " WHERE post_type = '" . RADIO_STATION_OVERRIDE_SLUG . "'";
	$results = $wpdb->get_results( $overridequery, ARRAY_A );
	$months = array();
	if ( $results && ( count( $results ) > 0 ) ) {
		foreach ( $results as $result ) {
			$post_id = $result['ID'];
			$override = get_post_meta( $post_id, 'show_override_date', true );
			$datetime = radio_station_to_time( $override );
			$month = radio_station_get_time( 'm', $datetime );
			$year = radio_station_get_time( 'Y', $datetime );
			$months[$year . '-' . $month] = array( 'year' => $year, 'month' => $month );
		}
	} else {
		return;
	}

	// --- maybe get specified month ---
	// TODO: maybe use get_query_var for month ?
	$m = isset( $_GET['month'] ) ? (int) $_GET['month'] : 0;

	// --- month override selector ---
	echo '<label for="filter-by-override-date" class="screen-reader-text">' . esc_html( __( 'Filter by override date', 'radio-station' ) ) . '</label>';
	echo '<select name="month" id="filter-by-override-date">';
	echo '<option value="0" ' . selected( $m, 0, false ) . '>' . esc_html( __( 'All Override Months', 'radio-station' ) ) . '</option>';
	if ( count( $months ) > 0 ) {
		foreach ( $months as $key => $data ) {
			$label = $wp_locale->get_month( $data['month'] ) . ' ' . $data['year'];
			echo "<option value='" . esc_attr( $key ) . "' " . selected( $m, $key, false ) . ">" . esc_html( $label ) . "</option>\n";
		}
	}
	echo '</select>';

}

// -------------------------------
// Add Schedule Past Future Filter
// -------------------------------
// 2.3.3: added past future filter prototype code
add_action( 'restrict_manage_posts', 'radio_station_override_past_future_filter', 10, 2 );
function radio_station_override_past_future_filter( $post_type, $which ) {

	if ( RADIO_STATION_OVERRIDE_SLUG !== $post_type ) {
		return;
	}

	// --- set past future selection / default ---
	$pastfuture = isset( $_GET['pastfuture'] ) ? $_GET['pastfuture'] : '';
	$pastfuture = apply_filters( 'radio_station_overrides_past_future_default', $pastfuture );

	// --- past / future override selector ---
	// 2.3.3.5: added option for today filtering
	echo '<label for="filter-by-past-future" class="screen-reader-text">' . esc_html( __( 'Past and Future', 'radio-station' ) ) . '</label>';
	echo '<select name="pastfuture" id="filter-by-past-future">';
	echo '<option value="" ' . selected( $pastfuture, 0, false ) . '>' . esc_html( __( 'All Overrides', 'radio-station' ) ) . '</option>';
	echo '<option value="past"' . selected( $pastfuture, 'past', false ) . '>' . esc_html( __( 'Past Overrides', 'radio-station' ) ) . '</option>';
	echo '<option value="today"' . selected( $pastfuture, 'today', false ) . '>' . esc_html( __( 'Overrides Today', 'radio-station' ) ) . '</option>';
	echo '<option value="future"' . selected( $pastfuture, 'future', false ) . '>' . esc_html( __( 'Future Overrides', 'radio-station' ) ) . '</option>';
 	echo '</select>';

}

// -----------------------------------
// === Post Type List Query Filter ===
// -----------------------------------
// 2.2.7: added filter for custom column sorting
add_action( 'pre_get_posts', 'radio_station_columns_query_filter' );
function radio_station_columns_query_filter( $query ) {

	if ( !is_admin() || !$query->is_main_query() ) {
		return;
	}

	// --- Shows by Shift Days Filtering ---
	if ( RADIO_STATION_SHOW_SLUG === $query->get( 'post_type' ) ) {

		// --- check if day filter is set ---
		if ( isset( $_GET['weekday'] ) && ( '0' != $_GET['weekday'] ) ) {

			$weekday = $_GET['weekday'];

			// need to loop and sync a separate meta key to enable filtering
			// (not really efficient but at least it makes it possible!)
			// ...but could be improved by checking against postmeta table
			// 2.3.0: cache all show posts query result for efficiency
			global $radio_station_data;
			if ( isset( $radio_station_data['all-shows'] ) ) {
				$results = $radio_station_data['all-shows'];
			} else {
				global $wpdb;
				$showquery = "SELECT ID FROM " . $wpdb->posts . " WHERE post_type = '" . RADIO_STATION_SHOW_SLUG . "'";
				$results = $wpdb->get_results( $showquery, ARRAY_A );
				$radio_station_data['all-shows'] = $results;
			}
			if ( $results && ( count( $results ) > 0 ) ) {
				foreach ( $results as $result ) {

					$post_id = $result['ID'];
					$shifts = radio_station_get_show_schedule( $post_id );

					if ( $shifts && is_array( $shifts ) && ( count( $shifts ) > 0 ) ) {
						$shiftdays = array();
						$shiftstart = $prevtime = false;
						foreach ( $shifts as $shift ) {
							if ( $shift['day'] == $weekday ) {
								// 2.3.0: replace old with new 24 hour conversion
								// $shiftstart = $shifttime['start_hour'] . ':' . $shifttime['start_min'] . ":00";
								// $shiftstart = radio_station_convert_schedule_to_24hour( $shift );
								// 2.3.2: replace strtotime with to_time for timezones
								$shiftstart = $shift['start_hour'] . ':' . $shift['start_min'] . $shift['start_meridian'];
								$shifttime = radio_station_convert_shift_time( $shiftstart );
								$shifttime = radio_station_to_time( $weekday . ' ' . $shiftstart );
								// 2.3.0: check for earliest shift for that day
								if ( !$prevtime || ( $shifttime < $prevtime ) ) {
									// 2.3.2: adjust as already converted to 24 hour
									// $shiftstart = radio_station_convert_shift_time( $shiftstart, 24 ) . ':00';
									$shiftstart .= ':00';
									$prevtime = $shifttime;
								}
							}
						}
						if ( $shiftstart ) {
							update_post_meta( $post_id, 'show_shift_time', $shiftstart );
						} else {
							delete_post_meta( $post_id, 'show_shift_time' );
						}
					} else {
						delete_post_meta( $post_id, 'show_shift_time' );
					}
				}
			}

			// --- order by show time start ---
			// only need to set the orderby query and exists check is automatically done!
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', 'show_shift_time' );
			$query->set( 'meta_type', 'TIME' );
		}
	}

	// --- Order Show Overrides by Override Date ---
	// also making this the default sort order
	// if ( 'show_override_date' === $query->get( 'orderby' ) ) {
	if ( RADIO_STATION_OVERRIDE_SLUG === $query->get( 'post_type' ) ) {

		// unless order by published date is explicitly chosen
		if ( 'date' !== $query->get( 'orderby' ) ) {

			// need to loop and sync a separate meta key to enable orderby sorting
			// (not really efficient but at least it makes it possible!)
			// ...but could be improved by checking against postmeta table
			// 2.3.3.5: use wpdb prepare method on query
			global $wpdb;
			$overridequery = "SELECT ID FROM " . $wpdb->posts . " WHERE post_type = %s";
			$overridequery = $wpdb->prepare( $overridequery, RADIO_STATION_OVERRIDE_SLUG );
			$results = $wpdb->get_results( $overridequery, ARRAY_A );
			if ( $results && ( count( $results ) > 0 ) ) {
				foreach ( $results as $result ) {
					$post_id = $result['ID'];
					$override = get_post_meta( $post_id, 'show_override_sched', true );
					if ( $override ) {
						update_post_meta( $post_id, 'show_override_date', $override['date'] );
					} else {
						delete_post_meta( $post_id, 'show_override_date' );
					}
				}
			}

			// --- now we can set the orderby meta query to the synced key ---
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', 'show_override_date' );
			$query->set( 'meta_type', 'date' );

			// --- apply override year/month filtering ---
			if ( isset( $_GET['month'] ) && ( '0' != $_GET['month'] ) ) {
				$yearmonth = $_GET['month'];
				$start_date = date( $yearmonth . '-01' );
				$end_date = date( $yearmonth . '-t' );
				$meta_query = array(
					'key'     => 'show_override_date',
					'value'   => array( $start_date, $end_date ),
					'compare' => 'BETWEEN',
					'type'    => 'DATE',
				);
				$query->set( 'meta_query', $meta_query );
			}

			// --- meta query for past / future overrides filter ---
			// 2.3.3: added past future query prototype code
			// 2.3.3.5: added option for today selection
			$valid = array( 'past', 'today', 'future' );
			if ( isset( $_GET['pastfuture'] ) && in_array( $_GET['pastfuture'], $valid ) ) {

				$date = date( 'Y-m-d', time() );
				$yesterday = date( 'Y-m-d', time() - ( 24 * 60 * 60 ) );
				$tomorrow = date( 'Y-m-d', time() + ( 24 * 60 * 60 ) );
				$pastfuture = $_GET['pastfuture'];
				if ( 'today' == $pastfuture ) {
					$compare = 'BETWEEN';
					$value = array( $yesterday, $tomorrow );
				} elseif ( 'past' == $pastfuture ) {
					$compare = '<';
					$value = $date;
				} elseif ( 'future' == $pastfuture ) {
					$compare = '>';
					$value = $date;
				}

				$pastfuture_query = array(
					'key'		=> 'show_override_date',
					'value'		=> $value,
					'compare'	=> $compare,
					'type'		=> 'DATE',
				);
				if ( isset( $meta_query ) ) {
					$combined_query = array(
						'relation'	=> 'AND',
						$meta_query,
						$pastfuture_query,
					);
					$meta_query = $combined_query;
				} else {
					$meta_query = $pastfuture_query;
				}
				$query->set( 'meta_query', $meta_query );
			}

		}
	}
}

