<?php

/*
* Support Functions for Radio Station plugin
* Author: Tony Hayes
* Since: 2.3.0
*/

// === Data Functions ===
// - Get Show
// - Get Shows
// - Get Show Schedule
// - Generate Unique Shift ID
// - Get Show Shifts
// - Get Schedule Overrides
// - Get Show Data
// - Get Show Metadata
// - Get Override Metadata
// - Get Current Schedule
// - Get Current Show
// - Get Previous Show
// - Get Next Show
// - Get Next Shows
// - Get Current Playlist
// - Get Blog Posts for Show
// - Get Playlists for Show
// - Get Genre
// - Get Genres
// - Get Shows for Genre
// - Get Shows for Language
// === Shift Checking ===
// - Schedule Conflict Checker
// - Show Shift Checker
// - New Shifts Checker
// - Validate Shift Time
// === Show Avatar ===
// - Get Show Avatar ID
// - Get Show Avatar URL
// - Get Show Avatar
// === URL Functions ===
// - Get Streaming URL
// - Get Stream Formats
// - Get Master Schedule Page URL
// - Get Radio Station API URL
// - Get Route URL
// - Get Feed URL
// - Get DJ / Host Profile URL
// - Get Producer Profile URL
// - Get Upgrade URL
// - Patreon Supporter Button
// - Patreon Button Styles
// - Send Directory Ping
// === Time Conversions ===
// - Get Now
// - Get Timezone
// - Get Timezone Code
// - Get Date Time Object
// - String To Time
// - Get Time
// - Get Timezone Options
// - Get Weekday(s)
// - Get Month(s)
// - Get Schedule Weekdays
// - Get Schedule Weekdates
// - Get Next Day
// - Get Previous Day
// - Get Next Date
// - Get Previous Date
// - Get All Hours
// - Convert Hour to Time Format
// - Convert Shift to Time Format
// - Convert Show Shift
// - Convert Show Shifts
// - Convert Schedule Shifts
// === Helper Functions ===
// - Get Profile ID
// - Get Languages
// - Get Language Options
// - Get Language
// - Trim Excerpt
// - Shorten String
// - Sanitize Values
// - Sanitize Shortcode Values
// === Translations ===
// - Translate Weekday
// - Replace Weekdays
// - Translate Month
// - Replace Months
// - Translate Meridiem
// - Replace Meridiems
// - Translate Time String

// ----------------------
// === Data Functions ===
// ----------------------

// --------
// Get Show
// --------
// 2.3.0: added get show data grabber
function radio_station_get_show( $show ) {
	if ( !is_object( $show ) ) {
		if ( is_string( $show ) ) {
			global $wpdb;
			$query = "SELECT ID FROM " . $wpdb->posts . " WHERE post_type = '" . RADIO_STATION_SHOW_SLUG . "' AND post_name = %s";
			$query = $wpdb->prepare( $query, $show );
			$show_id = $wpdb->get_var( $query );
			$show = get_post( $show_id );
		} elseif ( is_int( $show ) ) {
			$show = get_post( $show );
		}
	}

	return $show;
}

// ---------
// Get Shows
// ---------
// 2.3.0: added get shows data grabber
function radio_station_get_shows( $args = false ) {

	// --- set default args ---
	$defaults = array(
		'post_type'   => RADIO_STATION_SHOW_SLUG,
		'post_status' => 'publish',
		'numberposts' => - 1,
		'meta_query'  => array(
			'relation' => 'AND',
			array(
				'key'		=> 'show_sched',
				'compare'	=> 'EXISTS',
				// 'value'		=> 's:',
				// 'compare'	=> 'LIKE',
			),
			array(
				'key'		=> 'show_active',
				'value'		=> 'on',
				'compare'	=> '=',
			),
		),
		'orderby'	=> 'post_name',
		'order'		=> 'ASC',
	);

	// --- overwrite defaults with any arguments passed ---
	if ( $args && is_array( $args ) && ( count( $args ) > 0 ) ) {
		foreach ( $args as $key => $value ) {
			$defaults[$key] = $value;
		}
	}

	// --- get and return shows ---
	$shows = get_posts( $defaults );
	$shows = apply_filters( 'radio_station_get_shows', $shows, $defaults );

	return $shows;
}

// -----------------
// Get Show Schedule
// -----------------
// 2.3.0: added to give each shift a unique ID
function radio_station_get_show_schedule( $show_id ) {

	// --- get show shift schedule ---
	$shifts = get_post_meta( $show_id, 'show_sched', true );
	if ( $shifts && is_array( $shifts ) && ( count( $shifts ) > 0 ) ) {
		$changed = false;
		foreach ( $shifts as $i => $shift ) {

			// --- check for unique ID length ---
			if ( strlen( $i ) != 8 ) {

				// --- generate unique shift ID ---
				unset( $shifts[$i] );
				$unique_id = radio_station_unique_shift_id();
				$shifts[$unique_id] = $shift;
				$changed = true;
			}
		}

		// --- update shifts to save unique ID indexes ---
		if ( $changed ) {
			update_post_meta( $show_id, 'show_sched', $shifts );
		}
	}

	return $shifts;
}

// ------------------------
// Generate Unique Shift ID
// ------------------------
function radio_station_unique_shift_id() {

	$shift_ids = get_option( 'radio_station_shifts_ids' );
	if ( !$shift_ids ) {
		$shift_ids = array();
	}
	$unique_id = wp_generate_password( 8, false, false );
	if ( in_array( $unique_id, $shift_ids ) ) {
		while ( in_array( $unique_id, $shift_ids ) ) {
			$unique_id = wp_generate_password( 8, false, false );
		}
		$shift_ids[] = $unique_id;
	}

	// --- store the unique shift ID ---
	update_option( 'radio_station_shifts_ids', $shift_ids );

	return $unique_id;
}

// --------------------
// Generate Hashed GUID
// --------------------
// 2.3.2: add hashing function for show GUID
function radio_station_get_show_guid( $show_id ) {

	global $wpdb;
	$query = "SELECT guid FROM " . $wpdb->posts . " WHERE ID = %d";
	$guid = $wpdb->get_var( $query );
	if ( !$guid ) {
		$guid = get_permalink( $show_id );
	}
	$hash = md5( $guid );

	return $hash;
}

// ---------------
// Get Show Shifts
// ---------------
// 2.3.0: added get show shifts data grabber
// 2.3.2: added second argument to get non-split shifts
// 2.3.3: added time as third argument for ondemand shifts
function radio_station_get_show_shifts( $check_conflicts = true, $split = true, $time = false ) {

	// --- get all shows ---
	$errors = array();
	$shows = radio_station_get_shows();

	// --- debug point ---
	if ( RADIO_STATION_DEBUG ) {
		foreach ( $shows as $i => $show ) {
			$shows[$i]->post_content = '';
		}
		$debug = "Shows" . PHP_EOL . PHP_EOL . print_r( $shows, true );
		radio_station_debug( $debug );
	}

	// --- get weekdates for checking ---
	if ( $time ) {
		$now = $time;
	} else {
		$now = radio_station_get_now();
	}
	$today = radio_station_get_time( 'l', $now );
	$weekdays = radio_station_get_schedule_weekdays( $today );
	$weekdates = radio_station_get_schedule_weekdates( $weekdays, $now );

	// --- loop shows to get shifts ---
	$all_shifts = array();
	if ( $shows && is_array( $shows ) && ( count( $shows ) > 0 ) ) {
		foreach ( $shows as $show ) {

			$shifts = radio_station_get_show_schedule( $show->ID );

			if ( $shifts && is_array( $shifts) && ( count( $shifts ) > 0 ) ) {
				foreach ( $shifts as $i => $shift ) {

					// --- make sure shift has sufficient info ---
					if ( isset( $shift['disabled'] ) && ( 'yes' == $shift['disabled'] ) ) {
						$isdisabled = true;
					} else {
						$isdisabled = false;
					}
					$shift = radio_station_validate_shift( $shift );

					if ( isset( $shift['disabled'] ) && ( 'yes' == $shift['disabled'] ) ) {

						// --- if it was not already disabled, add to shift errors ---
						if ( !$isdisabled ) {
							$errors[$show->ID][] = $shift;
						}

					} else {

						// --- shift is valid so continue checking ---
						// 2.3.2: replace strtotime with to_time for timezones
						// 2.3.2: fix to conver to 24 hour format first
						$day = $shift['day'];
						$thisdate = $weekdates[$day];
						$midnight = radio_station_to_time( $thisdate . ' 23:59:59' ) + 1;
						$start = $shift['start_hour'] . ':' . $shift['start_min'] . ' ' . $shift['start_meridian'];
						$end = $shift['end_hour'] . ':' . $shift['end_min'] . ' ' . $shift['end_meridian'];
						$start_time = radio_station_convert_shift_time( $start );
						$start_time = radio_station_to_time( $thisdate . ' ' . $start_time );
						if ( ( '11:59:59 pm' == $end ) || ( '12:00 am' == $end ) ) {
							// 2.3.2: simplify using existing midnight time
							$end_time = $midnight;
						} else {
							$end_time = radio_station_convert_shift_time( $end );
							$end_time = radio_station_to_time( $thisdate . ' ' . $end );
						}
						if ( isset( $shift['encore'] ) && ( 'on' == $shift['encore'] ) ) {
							$encore = true;
						} else {
							$encore = false;
						}
						$updated = $show->post_modified_gmt;

						if ( $split ) {

							// --- check if show goes over midnight ---
							if ( ( $end_time > $start_time ) || ( $end_time == $midnight ) ) {

								// --- set the shift time as is ---
								// 2.3.2: added date data
								$all_shifts[$day][$start_time . '.' . $show->ID] = array(
									'day'      => $day,
									'date'     => $thisdate,
									'start'    => $start,
									'end'      => $end,
									'show'     => $show->ID,
									'encore'   => $encore,
									'split'    => false,
									'updated'  => $updated,
									'shift'    => $shift,
									'override' => false,
								);

							} else {

								// --- split shift for this day ---
								// 2.3.2: added date data
								$all_shifts[$day][$start_time . '.' . $show->ID] = array(
									'day'      => $day,
									'date'     => $thisdate,
									'start'    => $start,
									'end'      => '11:59:59 pm', // midnight
									'show'     => $show->ID,
									'split'    => true,
									'encore'   => $encore,
									'updated'  => $updated,
									'shift'    => $shift,
									'real_end' => $end,
									'override' => false,
								);

								// --- split shift for next day ---
								// 2.3.2: added date data for next day
								$nextday = radio_station_get_next_day( $day );
								$nextdate = $weekdates[$nextday];
								// 2.3.2: fix midnight timestamp for sorting
								if ( strtotime( $nextdate ) < strtotime( $thisdate ) ) {
									$midnight = radio_station_to_time( $nextdate . ' 00:00:00' );
								}
								$all_shifts[$nextday][$midnight . '.' . $show->ID] = array(
									'day'        => $nextday,
									'date'       => $nextdate,
									'start'      => '00:00 am', // midnight
									'end'        => $end,
									'show'       => $show->ID,
									'encore'     => $encore,
									'split'      => true,
									'updated'    => $updated,
									'shift'      => $shift,
									'real_start' => $start,
									'override'   => false,
								);
							}

						} else {

							// --- set the shift time as is ---
							// 2.3.2: added for non-split argument
							$all_shifts[$day][$start_time . '.' . $show->ID] = array(
								'day'      => $day,
								'date'     => $thisdate,
								'start'    => $start,
								'end'      => $end,
								'show'     => $show->ID,
								'encore'   => $encore,
								'split'    => false,
								'updated'  => $updated,
								'shift'    => $shift,
								'override' => false,
							);

						}
					}
				}
			}
		}
	}

	// --- maybe store any found shift errors ---
	if ( count( $errors ) > 0 ) {
		update_option( 'radio_station_shift_errors', $errors );
	} else {
		delete_option( 'radio_station_shift_errors' );
	}

	// --- debug point ---
	if ( RADIO_STATION_DEBUG ) {
		$debug = "Raw Shifts" . PHP_EOL . PHP_EOL . print_r( $all_shifts, true );
		$debug .= "Shift Errors" . PHP_EOL . PHP_EOL . print_r( $errors, true );
		radio_station_debug( $debug );
	}

	// --- sort by start time for each day ---
	// note: all_shifts keys are made unique by combining start time and show ID
	// which allows them to be both be sorted and then checked for conflicts
	if ( count( $all_shifts ) > 0 ) {
		foreach ( $all_shifts as $day => $shifts ) {
			ksort( $shifts );
			$all_shifts[$day] = $shifts;
		}
	}

	// --- reorder by weekdays ---
	// 2.3.2: added for passing to shift checker
	$sorted_shifts = array();
	foreach ( $weekdays as $weekday ) {
		if ( isset( $all_shifts[$weekday] ) ) {
			$sorted_shifts[$weekday] = $all_shifts[$weekday];
		}
	}
	$all_shifts = $sorted_shifts;

	// --- debug point ---
	if ( RADIO_STATION_DEBUG ) {
		$debug = "Sorted Shifts" . PHP_EOL . PHP_EOL . print_r( $sorted_shifts, true );
		radio_station_debug( $debug );
	}

	// --- check shifts for conflicts ---
	if ( $check_conflicts ) {
		$all_shifts = radio_station_check_shifts( $all_shifts );
	} else {
		// --- return raw data for other shift conflict checking ---
		return $all_shifts;
	}

	// --- debug point ---
	if ( RADIO_STATION_DEBUG ) {
		$debug = "Conflict Checked Shifts" . PHP_EOL . PHP_EOL . print_r( $all_shifts, true );
		radio_station_debug( $debug );
	}

	// --- shuffle shift days so today is first day ---
	// 2.3.2: use get time function for day with timezone
	// $today = date( 'l' );
	$today = radio_station_get_time( 'day' );
	$day_shifts = array();
	for ( $i = 0; $i < 7; $i ++ ) {
		if ( 0 == $i ) {
			$day = $today;
		} else {
			$day = radio_station_get_next_day( $day );
		}
		if ( isset( $all_shifts[$day] ) ) {
			$day_shifts[$day] = $all_shifts[$day];
		}
	}

	// --- filter and return ---
	$day_shifts = apply_filters( 'radio_station_show_shifts', $day_shifts );

	return $day_shifts;
}

// ----------------------
// Get Schedule Overrides
// ----------------------
// 2.3.0: added get schedule overrides data grabber
function radio_station_get_overrides( $start_date = false, $end_date = false ) {

	// --- convert dates to times for checking ---
	// (we allow an extra day either way for overflows)
	// 2.3.2: replace strtotime with to_time for timezones
	// 2.3.2: fix to variable conflict with start_time/end_time
	if ( $start_date ) {
		// 2.3.2: added missing backwards day allowance
		$range_start_time = radio_station_to_time( $start_date  ) - ( 24 * 60 * 60 ) + 1;
	}
	if ( $end_date ) {
		$range_end_time = radio_station_to_time( $end_date ) + ( 24 * 60 * 60 ) - 1 ;
	}

	// --- get all override IDs ---
	global $wpdb;
	$query = "SELECT ID,post_title,post_name FROM " . $wpdb->posts;
	$query .= " WHERE post_type = '" . RADIO_STATION_OVERRIDE_SLUG . "'";
	$query .= " AND post_status = 'publish'";
	$overrides = $wpdb->get_results( $query, ARRAY_A );
	if ( !$overrides || !is_array( $overrides ) || ( count( $overrides ) < 1 ) ) {
		return false;
	}

	// --- loop overrides and get data ---
	$override_list = array();
	foreach ( $overrides as $i => $override ) {
		$data = get_post_meta( $override['ID'], 'show_override_sched', true );

		if ( $data ) {
			$date = $data['date'];
			if ( '' != $date ) {

				// 2.3.2: replace strtotime with to_time for timezones
				$date_time = radio_station_to_time( $date );
				$inrange = true;

				// --- check if in specified date range ---
				if ( ( isset( $range_start_time ) && ( $date_time < $range_start_time ) )
				     || ( isset( $range_end_time ) && ( $date_time > $range_end_time ) ) ) {
					$inrange = false;
				}

				// --- add the override data ---
				if ( $inrange ) {

					// 2.3.2: get day from date directly
					// $thisday = date( 'l', $date_time );
					$day = date( 'l', strtotime( $date ) );

					// 2.3.2: replace strtotime with to_time for timezones
					// 2.3.2: fix to conver to 24 hour format first
					$start = $data['start_hour'] . ':' . $data['start_min'] . ' ' . $data['start_meridian'];
					$end = $data['end_hour'] . ':' . $data['end_min'] . ' ' . $data['end_meridian'];
					$start_time = radio_station_convert_shift_time( $start );
					$end_time = radio_station_convert_shift_time( $end );
					$override_start_time = radio_station_to_time( $date . ' ' . $start_time );
					$override_end_time = radio_station_to_time( $date . ' ' . $end_time );
					// 2.3.2: fix for overrides ending at midnight
					if ( '12:00 am' == $end ) {
						$override_end_time = $override_end_time + ( 24 * 60 * 60 );
					}

					if ( $override_start_time < $override_end_time ) {

						// --- add the override as is ---
						$override_data = array(
							'override' => $override['ID'],
							'name'     => $override['post_title'],
							'slug'     => $override['post_name'],
							'date'     => $date,
							'day'      => $day,
							'start'    => $start,
							'end'      => $end,
							'url'      => get_permalink( $override['ID'] ),
							'split'    => false,
						);
						// 2.3.3.7: set array order by start time
						$override_list[$date][$override_start_time] = $override_data;

					} else {

						// --- split the override overnight ---
						$override_data = array(
							'override' => $override['ID'],
							'name'     => $override['post_title'],
							'slug'     => $override['post_name'],
							'date'     => $date,
							'day'      => $day,
							'start'    => $start,
							'end'      => '11:59:59 pm',
							'real_end' => $end,
							'url'      => get_permalink( $override['ID'] ),
							'split'    => true,
						);
						// 2.3.3.7: set array order by start time
						$override_list[$date][$override_start_time] = $override_data;

						// --- set the next day split shift ---
						// note: these should not wrap around to start of week
						// 2.3.2: use get next date/day functions
						// $nextday = date( 'l', $next_date_time );
						// $nextdate = date( 'Y-m-d', $next_date_time );
						$nextdate = radio_station_get_next_date( $date );
						$nextday = radio_station_get_next_day( $day );

						$override_data = array(
							'override'   => $override['ID'],
							'name'       => $override['post_title'],
							'slug'       => $override['post_name'],
							'date'       => $nextdate,
							'day'        => $nextday,
							'real_start' => $start,
							'start'      => '00:00 am',
							'end'        => $end,
							'url'        => get_permalink( $override['ID'] ),
							'split'      => true,
						);
						// 2.3.3.7: set array order by start time
						$override_list[$nextdate][$override_start_time] = $override_data;
					}
				}
			}
		}
	}

	// 2.3.3.7: reorder overrides by sequential times
	if ( count( $override_list ) > 0 ) {
		foreach ( $override_list as $day => $overrides ) {
			ksort( $overrides );
			$override_list[$day] = $overrides;
		}
	}

	// --- filter and return ---
	$override_list = apply_filters( 'radio_station_get_overrides', $override_list, $start_date, $end_date );

	return $override_list;
}

// -------------
// Get Show Data
// -------------
// 2.3.0: added get show data grabber
function radio_station_get_show_data( $datatype, $show_id, $args = array() ) {

	// --- we need a data type and show ID ---
	if ( !$datatype ) {
		return false;
	}
	if ( !$show_id ) {
		return false;
	}

	// --- get meta key for valid data types ---
	if ( 'posts' == $datatype ) {
		$metakey = 'post_showblog_id';
	} elseif ( 'playlists' == $datatype ) {
		$metakey = 'playlist_show_id';
	} elseif ( 'episodes' == $datatype ) {
		$metakey = 'episode_show_id';
	} else {
		return false;
	}

	// --- check for optional arguments ---
	$default = true;
	if ( !isset( $args['limit'] ) ) {
		$args['limit'] = false;
	} elseif ( false !== $args['limit'] ) {
		$default = false;
	}
	if ( !isset( $args['data'] ) ) {
		$args['data'] = true;
	} elseif ( true !== $args['data'] ) {
		$default = false;
	}
	if ( !isset( $args['columns'] ) || !is_array( $args['columns'] ) || ( count( $args['columns'] ) < 1 ) ) {
		$columns = 'posts.ID, posts.post_title, posts.post_content, posts.post_excerpt, posts.post_date';
	} else {
		$columns = array();
		$default = false;
		$valid = array(
			'ID',
			'post_author',
			'post_date',
			'post_date_gmt',
			'post_content',
			'post_title',
			'post_excerpt',
			'post_status',
			'comment_status',
			'ping_status',
			'post_password',
			'post_name',
			'to_ping',
			'pinged',
			'post_modified',
			'post_modified_gmt',
			'post_content_filtered',
			'post_parent',
			'guid',
			'menu_order',
			'post_type',
			'post_mime_type',
			'comment_count',
		);
		foreach ( $args['columns'] as $i => $column ) {
			if ( in_array( $column, $valid ) ) {
				if ( !isset( $columns ) ) {
					$columns = 'posts.' . $column;
				} else {
					$columns .= ', posts.' . $column;
				}
			}
		}
	}

	// --- check for cached default show data ---
	if ( $default ) {
		$default_data = apply_filters( 'radio_station_cached_data', false, $datatype, $show_id );
		if ( $default_data ) {
			return $default_data;
		}
	}

	// --- get records with associated show ID ---

	global $wpdb;
	if ( 'posts' == $datatype ) {

		// 2.3.3.4: handle possible multiple show post values
		$query = "SELECT post_id,meta_value FROM " . $wpdb->prefix . "postmeta"
				. " WHERE meta_key = '" . $metakey . "' AND meta_value LIKE '%" . $show_id . "%'";
		$results = $wpdb->get_results( $query, ARRAY_A );
		// echo "Results: "; print_r( $results );
		if ( !$results || !is_array( $results ) || ( count( $results ) < 1 ) ) {
			return false;
		}

		// --- get/check post IDs in post meta ---
		$post_ids = array();
		foreach ( $results as $result ) {
			// TODO: check raw result is serialized or array ?
			$show_ids = maybe_unserialize( $result['meta_value'] );
			if ( $show_id == $result['meta_value'] || in_array( $show_id, $show_ids ) ) {
				$post_ids[] = $result['post_id'];
			}
		}
		// echo "Post IDs: "; print_r( $post_ids );
	} else {
		$query = "SELECT post_id FROM " . $wpdb->prefix . "postmeta"
		         . " WHERE meta_key = '" . $metakey . "' AND meta_value = %d";
		$query = $wpdb->prepare( $query, $show_id );
		$post_metas = $wpdb->get_results( $query, ARRAY_A );
		if ( !$post_metas || !is_array( $post_metas ) || ( count( $post_metas ) < 1 ) ) {
			return false;
		}

		// --- get post IDs from post meta ---
		$post_ids = array();
		foreach ( $post_metas as $post_meta ) {
			$post_ids[] = $post_meta['post_id'];
		}
	}

	// --- check for post IDs ---
	if ( count( $post_ids ) < 1 ) {
		return false;
	}

	// --- get posts from post IDs ---
	$post_id_list = implode( ',', $post_ids );
	$query = "SELECT " . $columns . " FROM " . $wpdb->prefix . "posts AS posts
		WHERE posts.ID IN(" . $post_id_list . ") AND posts.post_status = 'publish'
		ORDER BY posts.post_date DESC";
	if ( $args['limit'] ) {
		$query .= $wpdb->prepare( " LIMIT %d", $args['limit'] );
	}
	$results = $wpdb->get_results( $query, ARRAY_A );

	// --- maybe get additional data ---
	// TODO: maybe get additional data for each data type ?
	// if ( $args['data'] && $results && is_array( $results ) && ( count( $results ) > 0 ) ) {
	//	if ( 'posts' == $datatype ) {
	//
	//	} elseif ( 'playlists' == $datatype ) {
	//
	//	} elseif ( 'episodes' == $datatype ) {
	//
	//	}
	// }

	// --- maybe cache default show data ---
	if ( $default ) {
		do_action( 'radio_station_cache_data', $datatype, $show_id, $results );
	}

	// --- filter and return ---
	$results = apply_filters( 'radio_station_show_' . $datatype, $results, $show_id, $args );

	return $results;
}

// ------------------
// Get Show Data Meta
// ------------------
function radio_station_get_show_data_meta( $show, $single = false ) {

	global $radio_station_data;

	// --- get show post ---
	if ( !is_object( $show ) ) {
		$show = get_post( $show );
	}

	// --- get show terms ---
	$genre_list = $language_list = array();
	$genres = wp_get_post_terms( $show->ID, RADIO_STATION_GENRES_SLUG );
	if ( $genres ) {
		foreach ( $genres as $genre ) {
			$genre_list[] = $genre->name;
		}
	}
	$languages = wp_get_post_terms( $show->ID, RADIO_STATION_LANGUAGES_SLUG );
	if ( $languages ) {
		foreach ( $languages as $language ) {
			$language_list[] = $language->name;
		}
	}

	// --- get show data ---
	// note: show email intentionally not included
	// $show_email = get_post_meta( $show->ID, 'show_email', true );
	$show_link = get_post_meta( $show->ID, 'show_link', true );
	$show_file = get_post_meta( $show->ID, 'show_file', true );
	$show_schedule = radio_station_get_show_schedule( $show->ID );
	$show_shifts = array();
	if ( $show_schedule && is_array( $show_schedule ) && ( count( $show_schedule ) > 0 ) ) {
		foreach ( $show_schedule as $i => $shift ) {
			$shift = radio_station_validate_shift( $shift );
			if ( !isset( $shift['disabled'] ) || ( 'yes' != $shift['disabled'] ) ) {
				$show_shifts[] = $shift;
			}
		}
	}

	// --- get show user data ---
	$show_hosts = get_post_meta( $show->ID, 'show_user_list', true );
	$show_producers = get_post_meta( $show->ID, 'show_producer_list', true );
	$hosts = $producers = array();
	if ( is_array( $show_hosts ) && ( count( $show_hosts ) > 0 ) ) {
		foreach ( $show_hosts as $host ) {
			if ( isset( $radio_station_data['user-' . $host] ) ) {
				$user = $radio_station_data['user-' . $host];
			} else {
				$user = get_user_by( 'ID', $host );
				$radio_station_data['user-' . $host] = $user;
			}
			$hosts[] = array(
				'name'  => $user->display_name,
				'url'   => radio_station_get_host_url( $host ),
			);
		}
	}
	if ( is_array( $show_producers ) && ( count( $show_producers ) > 0 ) ) {
		foreach ( $show_producers as $producer ) {
			if ( isset( $radio_station_data['user-' . $producer] ) ) {
				$user = $radio_station_data['user-' . $producer];
			} else {
				$user = get_user_by( 'ID', $producer );
				$radio_station_data['user-' . $producer] = $user;
			}
			$producers[] = array(
				'name'  => $user->display_name,
				'url'   => radio_station_get_producer_url( $producer ),
			);
		}
	}

	// --- get avatar and thumbnail URL ---
	// 2.3.1: added show avatar and image URLs
	$avatar_url = radio_station_get_show_avatar_url( $show->ID );
	if ( !$avatar_url ) {
		$avatar_url = '';
	}
	$thumbnail_url = '';
	$thumbnail_id = get_post_meta( $show->ID, '_thumbnail_id', true );
	if ( $thumbnail_id ) {
		$thumbnail = wp_get_attachment_image_src( $thumbnail_id, 'thumbnail' );
		$thumbnail_url = $thumbnail[0];
	}

	// --- create array and return ---
	// 2.3.1: added show avatar and image URLs
	$show_data = array(
		'id'        => $show->ID,
		'name'      => $show->post_title,
		'slug'      => $show->post_name,
		'url'       => get_permalink( $show->ID ),
		'latest'    => $show_file,
		'website'   => $show_link,
		// note: left out intentionally to avoid spam scraping
		// 'email'		=> $show_email,
		'hosts'     => $hosts,
		'producers' => $producers,
		'genres'    => $genre_list,
		'languages' => $language_list,
		'schedule'  => $show_shifts,
		'avatar_url' => $avatar_url,
		'image_url'  => $thumbnail_url,
	);

	// --- data route / feed for show ---
	if ( radio_station_get_setting( 'enable_data_routes' ) == 'yes' ) {
		$route_link = radio_station_get_route_url( 'shows' );
		$show_route = add_query_arg( 'show', $show->post_name, $route_link );
		$show_data['route'] = $show_route;
	}
	if ( radio_station_get_setting( 'enable_data_feeds' ) == 'yes' ) {
		$feed_link = radio_station_get_feed_url( 'shows' );
		$show_feed = add_query_arg( 'show', $show->post_name, $feed_link );
		$show_data['feed'] = $show_feed;
	}

	// --- add extra data for single show route/feed ---
	$show_id = $show->ID;
	if ( $single ) {

		// --- add show posts ---
		$show_data['posts'] = radio_station_get_show_posts( $show->ID );

		// --- add show playlists ---
		$show_data['playlists'] = radio_station_get_show_playlists( $show->ID );

		// --- filter to maybe add more data ---
		$show_data = apply_filters( 'radio_station_show_data_meta', $show_data, $show_id );
	}

	// --- maybe cache Show meta data ---
	do_action( 'radio_station_cache_data', 'show_meta', $show_id, $show_data );

	return $show_data;
}

// --------------------
// Get Show Description
// --------------------
// 2.3.3.8: added for show data API feed
function radio_station_get_show_description( $show_data ) {

	// --- get description and excerpt ---
	$show_id = $show_data['id'];
	$show_post = get_post( $show_id );
	$description = $show_post->post_content;
	if ( !empty( $show_post->post_excerpt ) ) {
		$excerpt = $show_post->post_excerpt;
	} else {
		$length = apply_filters( 'radio_station_show_data_excerpt_length', false );
		$more = apply_filters( 'radio_station_show_data_excerpt_more', '' );
		$excerpt = radio_station_trim_excerpt( $description, $length, $more, false );
	}

	// --- filter description and excerpt ---
	$description = apply_filters( 'radio_station_show_data_description', $description, $show_id );
	$excerpt = apply_filters( 'radio_station_show_data_excerpt', $excerpt, $show_id );

	// --- add to existing show data ---
	$show_data['description'] = $description;
	$show_data['excerpt'] = $excerpt;

	return $show_data;
}

// ----------------------
// Get Override Data Meta
// ----------------------
function radio_station_get_override_data_meta( $override ) {

	global $radio_station_data;

	// --- get override post ---
	if ( !is_object( $override ) ) {
		$override = get_post( $override );
	}

	// --- get override terms ---
	$genre_list = $language_list = array();
	$genres = wp_get_post_terms( $override->ID, RADIO_STATION_GENRES_SLUG );
	if ( $genres ) {
		foreach ( $genres as $genre ) {
			$genre_list[] = $genre->name;
		}
	}
	$languages = wp_get_post_terms( $override->ID, RADIO_STATION_LANGUAGES_SLUG );
	if ( $languages ) {
		foreach ( $languages as $language ) {
			$language_list[] = $language->name;
		}
	}

	// --- get override user data ---
	$override_hosts = get_post_meta( $override->ID, 'override_user_list', true );
	$override_producers = get_post_meta( $override->ID, 'override_producer_list', true );
	$hosts = $producers = array();
	if ( is_array( $override_hosts ) && ( count( $override_hosts ) > 0 ) ) {
		foreach ( $override_hosts as $host ) {
			if ( isset( $radio_station_data['user-' . $host] ) ) {
				$user = $radio_station_data['user-' . $host];
			} else {
				$user = get_user_by( 'ID', $host );
				$radio_station_data['user-' . $host] = $user;
			}
			$hosts[]['name'] = $user->display_name;
			$hosts[]['url'] = radio_station_get_host_url( $host );
		}
	}
	if ( is_array( $override_producers ) && ( count( $override_producers ) > 0 ) ) {
		foreach ( $override_producers as $producer ) {
			if ( isset( $radio_station_data['user-' . $producer] ) ) {
				$user = $radio_station_data['user-' . $producer];
			} else {
				$user = get_user_by( 'ID', $producer );
				$radio_station_data['user-' . $producer] = $user;
			}
			$producers[]['name'] = $user->display_name;
			$producers[]['url'] = radio_station_get_producer_url( $producer );
		}
	}

	// --- get avatar and thumbnail URL ---
	// 2.3.1: added show avatar and image URLs
	$avatar_url = radio_station_get_show_avatar_url( $override->ID );
	if ( !$avatar_url ) {
		$avatar_url = '';
	}
	$thumbnail_url = '';
	$thumbnail_id = get_post_meta( $override->ID, '_thumbnail_id', true );
	if ( $thumbnail_id ) {
		$thumbnail = wp_get_attachment_image_src( $thumbnail_id, 'thumbnail' );
		$thumbnail_url = $thumbnail[0];
	}

	// --- create array and return ---
	// 2.3.1: added show avatar and image URLs
	$override_data = array(
		'id'         => $override->ID,
		'name'       => $override->post_title,
		'slug'       => $override->post_name,
		'url'        => get_permalink( $override->ID ),
		'genres'     => $genre_list,
		'languages'  => $language_list,
		'hosts'      => $hosts,
		'producers'  => $producers,
		'avatar_url' => $avatar_url,
		'image_url'  => $thumbnail_url,
	);

	// --- filter and return ---
	$override_id = $override->ID;
	$override_data = apply_filters( 'radio_station_override_data', $override_data, $override_id );

	return $override_data;
}

// --------------------
// Get Current Schedule
// --------------------
// 2.3.2: added optional time argument
// 2.3.3.5: added optional weekstart argument
function radio_station_get_current_schedule( $time = false, $weekstart = false ) {

	global $radio_station_data;

	$show_shifts = false;

	// --- maybe get cached schedule ---
	if ( !$time ) {
		// 2.3.3: check data global first
		if ( isset( $radio_station_data['current_schedule'] ) ) {
			return $radio_station_data['current_schedule'];
		} else {
			$show_shifts = get_transient( 'radio_station_current_schedule' );
		}
	} else {
		// --- get schedule for time ---
		// 2.3.2: added transient for time schedule
		// 2.3.3: check data global first
		if ( isset( $radio_station_data['current_schedule_' . $time ] ) ) {
			return $radio_station_data['current_schedule_' . $time ];
		} else {
			$show_shifts = get_transient( 'radio_station_current_schedule_' . $time );
		}
	}

	if ( !$show_shifts ) {

		// --- get all show shifts ---
		// 2.3.3: also pass time to get show_shifts function
		$show_shifts = radio_station_get_show_shifts( true, true, $time );

		// --- get weekdates ---
		if ( !$time ) {
			$now = radio_station_get_now();
		} else {
			$now = $time;
		}
		// 2.3.3.5: add passthrough of optional week start argument
		$weekdays = radio_station_get_schedule_weekdays( $weekstart );
		$weekdates = radio_station_get_schedule_weekdates( $weekdays, $now );
		// 2.3.1: add empty keys to ensure overrides are checked
		foreach ( $weekdays as $weekday ) {
			if ( !isset( $show_shifts[$weekday] ) ) {
				$show_shifts[$weekday] = array();
			}
		}

		// --- debug point ---
		if ( RADIO_STATION_DEBUG ) {
			$debug = "Show Shifts: " . print_r( $show_shifts, true ) . PHP_EOL;
			radio_station_debug( $debug );
		}

		// --- get show overrides ---
		// (from 12am this morning, for one week ahead and back)
		// 2.3.1: get start and end dates from weekdays
		// 2.3.2: use get time function with timezone
		$date = radio_station_get_time( 'date' );

		// $start_time = strtotime( '12am ' . $date );
		// $end_time = $start_time + ( 7 * 24 * 60 * 60 ) + 1;
		// $start_time = $start_time - ( 7 * 24 * 60 * 60 ) - 1;
		// $start_date = date( 'd-m-Y', $start_time );
		// $end_date = date( 'd-m-Y', $end_time );
		$start_date = $weekdates[$weekdays[0]];
		$end_date = $weekdates[$weekdays[6]];
		$override_list = radio_station_get_overrides( $start_date, $end_date );

		// --- debug point ---
		if ( RADIO_STATION_DEBUG ) {
			$debug = "Now: " . $now . " - Date: " . $date . PHP_EOL;
			$debug .= "Week Start Date: " . $start_date . " - Week End Date: " . $end_date . PHP_EOL;
			$debug .= "Schedule Overrides: " . print_r( $override_list, true ) . PHP_EOL;
			radio_station_debug( $debug );
		}

		// --- apply overrides to the schedule ---
		$debugday = 'Monday';
		if ( isset( $_REQUEST['debug-day'] ) ) {
			$debugday = $_REQUEST['debug-day'];
		}
		$done_overrides = array();
		if ( $override_list && is_array( $override_list ) && ( count( $override_list ) > 0 ) ) {
			foreach ( $show_shifts as $day => $shifts ) {

				$date = $weekdates[$day];
				if ( RADIO_STATION_DEBUG ) {
					echo "Override Date: " . $date . PHP_EOL;
				}

				// 2.3.2: reset overrides for loop
				$overrides = array();
				if ( isset( $override_list[$date] ) ) {

					$overrides = $override_list[$date];
					if ( RADIO_STATION_DEBUG ) {
						echo "Overrides for " . $day . ": " . print_r( $overrides, true ) . PHP_EOL;
					}

					// --- maybe reloop to insert any overrides before shows ---
					if ( count( $overrides ) > 0 ) {
						foreach ( $overrides as $i => $override ) {

							if ( $date == $override['date'] ) {
								// 2.3.1: added check if override already done
								// 2.3.2: replace strtotime with to_time for timezones
								// 2.3.2: fix to convert to 24 hour format first
								// 2.3.3.7: remove check if override already done from here

								$override_start = radio_station_convert_shift_time( $override['start'] );
								$override_end = radio_station_convert_shift_time( $override['end'] );
								$override_start_time = radio_station_to_time( $date . ' ' . $override_start );
								$override_end_time = radio_station_to_time( $date . ' ' . $override_end );
								if ( isset( $override['split'] ) && $override['split'] && ( '11:59:59 pm' == $override['end'] ) ) {
									// 2.3.3.8: fix to add 60 seconds instead of 1
									$override_end_time = $override_end_time + 60;
								}
								// 2.3.2: fix for non-split overrides ending on midnight
								if ( $override_end_time < $override_start_time ) {
									$override_end_time = $override_end_time + ( 24 * 60 * 60 );
								}

								// --- check for overlapped shift (if any) ---
								// 2.3.1 added check for shift count
								if ( count( $shifts ) > 0 ) {
									// 2.3.3.7: change shifts variable in loop not just show_shifts
									foreach ( $shifts as $start => $shift ) {

										// 2.3.2: replace strtotime with to_time for timezones
										// 2.3.2: fix to convert to 24 hour format first
										$shift_start = radio_station_convert_shift_time( $shift['start'] );
										$shift_end = radio_station_convert_shift_time( $shift['end'] );
										$start_time = radio_station_to_time( $date . ' ' . $shift_start );
										$end_time = radio_station_to_time( $date . ' ' . $shift_end );
										if ( isset( $shift['split'] ) && $shift['split'] && ( '11:59:59 pm' == $shift['end'] ) ) {
											// 2.3.3.8: fix to add 60 seconds instead of 1
											$end_time = $end_time + 60;
										}
										// 2.3.2: fix for non-split shifts ending on midnight
										if ( $end_time < $start_time ) {
											$end_time = $end_time + ( 24 * 60 * 60 );
										}

										if ( $day == $debugday ) {
											$debugshifts .= $day . ' Show from ' . $shift['start'] . ': ' . $start_time . ' to ' . $shift['end'] . ': ' . $end_time . PHP_EOL;
											$debugshifts .= $day . ' Override from ' . $override['start'] . ': ' . $override_start_time . ' to ' . $override['end'] . ': ' . $override_end_time . PHP_EOL;
										}

										// --- check if the override starts earlier than shift ---
										if ( $override_start_time < $start_time ) {

											// --- check when the shift ends ---
											if ( ( $override_end_time > $end_time )
											  || ( $override_end_time == $end_time ) ) {
												// --- overlaps so remove shift ---
												if ( $day == $debugday ) {
													$debugshifts .= "Removed Shift: " . print_r( $shift, true ) . PHP_EOL;
												}
												unset( $show_shifts[$day][$start] );
												unset( $shifts[$start] );
											} elseif ( $override_end_time > $start_time ) {
												// --- add trimmed shift remainder ---
												if ( $day == $debugday ) {
													$debugshifts .= "Trimmed Start of Shift to " . $override['end'] . ": " . print_r( $shift, true ) . PHP_EOL;
												}
												unset( $show_shifts[$day][$start] );
												unset( $shifts[$start] );
												$shift['start'] = $override['end'];
												$shift['trimmed'] = 'start';
												$shifts[$override['end']] = $shift;
												$show_shifts[$day] = $shifts;
											}

											// --- add the override if not already added ---
											// 2.3.3.8: removed adding of overrides here
											/* if ( !in_array( $override['date'] . '--' . $i, $done_overrides ) ) {
												$done_overrides[] = $override['date'] . '--' . $i;
												if ( $day == $debugday ) {
													$debugshifts .= "Added Override: " . print_r( $override, true ) . PHP_EOL;
												}
												$shifts[$override['start']] = $override;
												$show_shifts[$day] = $shifts;
											} */

										} elseif ( $override_start_time == $start_time ) {

											// --- same start so overwrite the existing shift ---
											// 2.3.1: set override done instead of unsetting override
											// 2.3.3.7: remove check if override already done
											// $done_overrides[] = $date . '--' . $i;
											if ( $day == $debugday ) {
												$debugshifts .= "Replaced Shift with Override: " . print_r( $show_shifts[$day][$start], true ) . PHP_EOL;
											}
											$shifts[$start] = $override;
											$show_shifts[$day] = $shifts;

											// --- check if there is remainder of existing show ---
											if ( $override_end_time < $end_time ) {
												$shift['start'] = $override['end'];
												$shift['trimmed'] = 'start';
												$shifts[$override['end']] = $shift;
												$show_shifts[$day] = $shifts;
												if ( $day == $debugday ) {
													$debugshifts .= "And trimmed Shift Start to " . $override['end'] . PHP_EOL;
												}
											}
											// elseif ( $override_end_time == $end_time ) {
												// --- remove exact override ---
												// do nothing, already overridden
											// }

										} elseif ( ( $override_start_time > $start_time )
												&& ( $override_start_time < $end_time ) ) {

											$end = $shift['end'];

											// --- partial shift before override ---
											if ( $day == $debugday ) {
												$debugshifts .= "Trimmed Shift End to " . $override['start'] . ": " . print_r( $shift, true ) . PHP_EOL;
											}
											$shift['start'] = $start;
											$shift['end'] = $override['start'];
											$shift['trimmed'] = 'end';
											$shifts[$start] = $shift;
											$show_shifts[$day] = $shifts;

											// --- add the override ---
											$show_shifts[$day][$override['start']] = $override;
											// 2.3.1: track done instead of unsetting
											// 2.3.3.7: remove check if override already done here
											// $done_overrides[] = $date . '--' . $i;

											// --- partial shift after override ----
											if ( $override_end_time < $end_time ) {
												if ( $day == $debugday ) {
													$debugshifts .= "And added partial Shift after " . $override['end'] . ": " . print_r( $shift, true ) . PHP_EOL;
												}
												$shift['start'] = $override['end'];
												$shift['end'] = $end;
												$shift['trimmed'] = 'start';
												$shifts[$override['end']] = $shift;
												$show_shifts[$day] = $shifts;
											}
										}
									}
								}
							}
						}
					}
				}

				// --- add directly any remaining overrides ---
				// 2.3.1: fix to include standalone overrides on days
				// 2.3.3.8: moved override adding to fix shift order
				if ( count( $overrides ) > 0 ) {
					foreach ( $overrides as $i => $override ) {
						if ( $date == $override['date'] ) {
							// 2.3.3.7: remove check if override already done
							// if ( !in_array( $date . '--' . $i, $done_overrides ) ) {
								// $done_overrides[] = $date . '--' . $i;
								$show_shifts[$day][$override['start']] = $override;
								if ( $day == $debugday ) {
									$debugshifts .= "Added Override: " . print_r( $override, true ) . PHP_EOL;
								}
							// }
						}
					}
				}

				// --- sort the shifts using 24 hour time ---
				$shifts = $show_shifts[$day];
				if ( count( $shifts ) > 0 ) {
					// 2.3.2: fix to clear shift keys between days
					$new_shifts = $shift_keys = array();
					$keys = array_keys( $shifts );
					foreach ( $keys as $i => $key ) {
						$converted = radio_station_convert_shift_time( $key, 24 );
						unset( $keys[$i] );
						$keys[$key] = $shift_keys[$key] = $converted;
					}
					sort( $shift_keys );
					foreach ( $shift_keys as $shift_key ) {
						if ( in_array( $shift_key, $keys ) ) {
							$key = array_search( $shift_key, $keys );
							$new_shifts[$key] = $shifts[$key];
						}
					}
					$shifts = $show_shifts[$day] = $new_shifts;
				}

				if ( RADIO_STATION_DEBUG ) {
					if ( isset( $debugshifts ) ) {
						echo "Day Debug: " . $debugshifts . PHP_EOL;
					}
					echo "Shift Keys: " . print_r( $keys, true ) . PHP_EOL;
					echo "Sorted Keys: " . print_r( $shift_keys, true ) . PHP_EOL;
					echo "Sorted Shifts: " . print_r( $new_shifts, true ) . PHP_EOL;
				}

				$shifts = $show_shifts[$day];
				// ksort( $shifts );
				if ( RADIO_STATION_DEBUG ) {
					echo "New Day Shifts: " . print_r( $shifts, true ) . PHP_EOL;
					// echo "Done Overrides: " . print_r( $done_overrides, true ) . PHP_EOL;
				}
			}

			if ( RADIO_STATION_DEBUG ) {
				$debug = "Combined Schedule: " . print_r( $show_shifts, true ) . PHP_EOL;
				radio_station_debug( $debug );
			}
		}

		// --- loop all shifts to add show data ---
		$prev_shift = $set_prev_shift = $prev_shift_end = false;
		foreach ( $show_shifts as $day => $shifts ) {
			// 2.3.1: added check for shift count
			if ( count( $shifts ) > 0 ) {
				foreach ( $shifts as $start => $shift ) {

					// --- check if shift is an override ---
					if ( isset( $shift['override'] ) && $shift['override'] ) {

						// ---- add the override data ---
						$override = radio_station_get_override_data_meta( $shift['override'] );
						$shift['show'] = $show_shifts[$day][$start]['show'] = $override;

					} else {

						// --- get (or get stored) show data ---
						$show_id = $shift['show'];
						if ( isset( $radio_station_data['show-' . $show_id] ) ) {
							$show = $radio_station_data['show-' . $show_id];
						} else {
							$show = radio_station_get_show_data_meta( $show_id );
							$radio_station_data['show-' . $show_id] = $show;
						}
						unset( $show['schedule'] );

						// --- add show data back to shift ---
						$shift['show'] = $show_shifts[$day][$start]['show'] = $show;
					}

					if ( !isset( $current_show ) ) {

						// --- get this shift start and end times ---
						// 2.3.2: replace strtotime with to_time for timezones
						// 2.3.2: fix to convert to 24 hour format first
						$shift_start = radio_station_convert_shift_time( $shift['start'] );
						$shift_end = radio_station_convert_shift_time( $shift['end'] );
						$shift_start_time = radio_station_to_time( $weekdates[$day] . ' ' . $shift_start );
						$shift_end_time = radio_station_to_time( $weekdates[$day] . ' ' . $shift_end );

						// if ( isset( $shift['split'] ) && $shift['split'] && isset( $shift['real_end'] ) {
						//	$nextdate = radio_station_get_time( 'date', $shift_end_time + ( 23 * 60 * 60 ) );
						//	$shift_end = $nextdate[$day] . ' ' . $shift['real_end'];
						// }

						// - adjust for shifts ending past midnight -
						if ( $shift_start_time > $shift_end_time ) {
							$shift_end_time = $shift_end_time + ( 24 * 60 * 60 );
						}

						// --- check if this is the currently scheduled show ---
						if ( ( $now >= $shift_start_time ) && ( $now < $shift_end_time ) ) {

							if ( isset( $maybe_next_show ) ) {
								unset( $maybe_next_show );
							}
							$shift['day'] = $day;
							$current_show = $shift;

							// 2.3.3: set current show to global data
							// 2.3.4: set previous show shift to global and transient
							// 2.3.3.8: move expires declaration earlier
							$expires = $shift_end_time - $now - 1;
							if ( $expires > 3600 ) {
								$expires = 3600;
							}
							if ( !$time ) {
								$radio_station_data['current_show'] = $current_show;
								if ( $prev_shift ) {
									$prev_show = apply_filters( 'radio_station_previous_show', $prev_shift, $time );
									$radio_station_data['previous_show'] = $prev_show;
									set_transient( 'radio_station_previous_show', $prev_show, $expires );
								}
							} else {
								$radio_station_data['current_show_' . $time ] = $current_show;
								if ( $prev_shift ) {
									$prev_show = apply_filters( 'radio_station_previous_show', $prev_shift, $time );
									$radio_station_data['previous_show_' . $time] = $prev_show;
									set_transient( 'radio_station_previous_show_' . $time, $prev_show, $expires );
								}
							}

							// 2.3.2: set temporary transient if time is specified
							// 2.3.3: remove current show transient (as being unreliable)
							/* if ( !$time ) {
								set_transient( 'radio_station_current_show', $current_show, $expires );
							} else {
								set_transient( 'radio_station_current_show_' . $time, $current_show, $expires );
							} */

						} elseif ( $now > $shift_end_time ) {

							// 2.3.2: set previous shift flag
							$set_prev_shift = true;

						} elseif ( ( $now < $shift_start_time ) && !isset( $maybe_next_show ) ) {

							// 2.3.2: set maybe next show
							$maybe_next_show = $shift;

						}

						// --- debug point ---
						if ( RADIO_STATION_DEBUG ) {
							$debug = 'Now: ' . $now . PHP_EOL;
							$debug .= 'Date: ' . date( 'm-d H:i:s', $now ) . PHP_EOL;
							$debug .= 'Shift Start: ' . $shift_start . ' (' . $shift_start_time . ')' . PHP_EOL;
							$debug .= 'Shift End: ' . $shift_end . ' (' . $shift_end_time . ')' . PHP_EOL . PHP_EOL;
							if ( isset ( $current_show ) ) {
								$debug .= '[Current Shift] ' . print_r( $current_show, true ) . PHP_EOL;
							}
							$debug .= PHP_EOL;
							if ( $now >= $shift_start_time ) {$debug .= "!A!";}
							if ( $now < $shift_end_time ) {$debug .= "!B!";}
							echo $debug;
						}

					} elseif ( isset( $current_show['split'] ) && $current_show['split'] ) {

						// --- skip second part of split shift for current shift ---
						// (so that it is not set as the next show)
						unset( $current_show['split'] );

					}

					// 2.3.2: change to logic to allow for no current show found
					if ( !isset( $next_show ) ) {

						// --- get shift times ---
						// 2.3.2: replace strtotime with to_time for timezones
						// 2.3.2: fix to convert to 24 hour format first
						$shift_time = radio_station_convert_shift_time( $shift['start'] );
						$end_time = radio_station_convert_shift_time( $shift['end'] );
						$shift_start_time = radio_station_to_time( $weekdates[$day] . ' ' . $shift_start );
						$shift_end_time = radio_station_to_time( $weekdates[$day] . ' ' . $shift_end );
						if ( $shift_start_time > $shift_end_time ) {
							$shift_end_time = $shift_end_time + ( 24 * 60 * 60 );
						}

						if ( isset( $current_show )
						|| ( $prev_shift_end && ( $now > $prev_shift_end ) && ( $now < $shift_start_time ) ) ) {

							// --- set next show ---
							// 2.3.2: set date for widget
							$next_show['date'] = $weekdates[$day];
							$next_show = $shift;

						}
					}

					// 2.3.2: maybe set previous shift end value
					if ( $set_prev_shift ) {
						$prev_shift_end = $shift_end_time;
					}

					// 2.3.4: set previous shift value
					$prev_shift = $shift;

				}
			}
		}

		// --- maybe set next show transient ---
		// 2.3.2: check for (possibly first) next show found
		if ( !isset( $next_show ) && isset( $maybe_next_show ) ) {
			$next_show = $maybe_next_show;
		}
		if ( isset( $next_show ) ) {

			// 2.3.2: recombine split shift end times
			$shift = $next_show;
			if ( isset( $shift['split'] ) && $shift['split'] && isset( $shift['real_end'] ) ) {
				$next_show['end'] = $shift['real_end'];
				unset( $next_show['split'] );
			}

			// 2.3.2: added check that expires is set
			$next_expires = $shift_end_time - $now - 1;
			if ( isset( $expires ) && ( $next_expires > ( $expires + 3600 ) ) ) {
				$next_expires = $expires + 3600;
			}
			// 2.3.2: set temporary transient if time is specified
			if ( !$time ) {
				set_transient( 'radio_station_next_show', $next_show, $next_expires );
			} else {
				set_transient( 'radio_station_next_show_' . $time, $next_show, $next_expires );
			}
		}

		if ( RADIO_STATION_DEBUG ) {
			if ( !isset( $current_show ) ) {
				$current_show = 'Not found.';
			}
			echo '<span style="display:none;">Current Show: ' . print_r( $current_show, true ) . '</span>';
		}

		// --- get next show if we did not find one ---
		if ( !isset( $next_show ) ) {

			if ( RADIO_STATION_DEBUG ) {
				echo "No Next Show Found. Rechecking...";
			}

			// --- pass calculated shifts with limit of 1 ---
			// 2.3.2: added time argument to next shows retrieval
			// 2.3.2: set next show transient within next shows function
			$next_shows = radio_station_get_next_shows( 1, $show_shifts, $time );

		}

		// --- debug point ---
		if ( RADIO_STATION_DEBUG ) {
			$debug = "Show Schedule: " . print_r( $show_shifts, true ) . PHP_EOL;
			if ( isset( $current_show ) ) {
				$debug .= "Current Show: " . print_r( $current_show, true ) . PHP_EOL;
			}
			if ( isset( $next_show ) ) {
				$debug .= "Next Show: " . print_r( $next_show, true ) . PHP_EOL;
			}

			$next_shows = radio_station_get_next_shows( 5, $show_shifts );
			$debug .= "Next 5 Shows: " . print_r( $next_shows, true ) . PHP_EOL;

			radio_station_debug( $debug );
		}

		// --- cache current schedule data ---
		// 2.3.2: set temporary transient if time is specified
		// 2.3.3: also set global data for current schedule
		if ( !isset( $expires ) ) {
			$expires = 3600;
		}
		if ( !$time ) {
			$radio_station_data['current_schedule'] = $show_shifts;
			set_transient( 'radio_station_current_schedule', $show_shifts, $expires );
		} else {
			$radio_station_data['current_schedule_' . $time] = $show_shifts;
			set_transient( 'radio_station_current_schedule_' . $time, $show_shifts, $expires );
		}

		// --- filter and return ---
		// 2.3.2: added time argument to filter
		// 2.3.3: apply filter only once
		$show_shifts = apply_filters( 'radio_station_current_schedule', $show_shifts, $time );
	}

	return $show_shifts;
}

// ----------------
// Get Current Show
// ----------------
// 2.3.0: added new get current show function
// 2.3.2: added optional time argument
function radio_station_get_current_show( $time = false ) {

	global $radio_station_data;

	$current_show = false;

	// --- get cached current show value ---
	// 2.3.3: remove current show transient
	// 2.3.3: check for existing global data first
	if ( !$time && isset( $radio_station_data['current_show'] ) ) {
		return $radio_station_data['current_show'];
	} elseif ( isset( $radio_station_data['current_show_' . $time] ) ) {
		return $radio_station_data['current_show_' . $time];
	}

	// --- get all show shifts ---
	if ( !$time ) {
		$show_shifts = radio_station_get_current_schedule();
	} else {
		$show_shifts = radio_station_get_current_schedule( $time );
	}

	// --- get current time ---
	if ( $time ) {
		$now = $time;
	} else {
		$now = radio_station_get_now();
	}

	// --- get schedule for time ---
	// 2.3.3: use weekday name instead of number (w)
	// 2.3.3: add fix to start from previous day
	$today = radio_station_get_time( 'l', $now );
	$yesterday = radio_station_get_previous_day( $today );
	$weekdays = radio_station_get_schedule_weekdays( $yesterday );
	$weekdates = radio_station_get_schedule_weekdates( $weekdays, $now );

	if ( RADIO_STATION_DEBUG ) {
		echo '<span style="display:none;">';
		echo "Finding Current Show from " . $yesterday . PHP_EOL;
		print_r( $weekdays );
		print_r( $weekdates );
		echo '</span>';
	}

	// --- loop shifts to get current show ---
	$current_split = $prev_show = false;
	foreach ( $weekdays as $day ) {
		if ( isset( $show_shifts[$day] ) ) {
			$shifts = $show_shifts[$day];
			foreach ( $shifts as $start => $shift ) {

				// --- get this shift start and end times ---
				$shift_start = radio_station_convert_shift_time( $shift['start'] );
				$shift_end = radio_station_convert_shift_time( $shift['end'] );
				$shift_start_time = radio_station_to_time( $weekdates[$day] . ' ' . $shift_start );
				$shift_end_time = radio_station_to_time( $weekdates[$day] . ' ' . $shift_end );
				// 2.3.3: fix for shifts split over midnight
				if ( $shift_start_time > $shift_end_time ) {
					$shift_end_time = $shift_end_time + ( 24 * 60 * 60 );
				}

				if ( RADIO_STATION_DEBUG ) {
					echo '<span style="display:none;">';
					echo 'Current? ' . $now . ' - ' . $shift_start_time . ' - ' . $shift_end_time . PHP_EOL;
					echo print_r( $shift, true ) . PHP_EOL;
				}

				// --- set current show ---
				// 2.3.3: get current show directly and remove transient
				if ( ( $now > $shift_start_time ) && ( $now < $shift_end_time ) ) {
					if ( RADIO_STATION_DEBUG ) {
						echo '^^^ Current ^^^' . PHP_EOL;
					}
					// --- recombine possible split shift to set current show ---
					$current_show = $shift;

					// 2.3.4: also set previous shift data
					if ( $prev_shift ) {
						$expires = $shift_end_time - $now - 1;
						$previous_show = apply_filters( 'radio_station_previous_show', $prev_shift, $time );
						if ( !$time ) {
							$radio_station_data['previous_show'] = $previous_show;
							set_transient( 'radio_station_previous_show', $previous_show, $expires );
						} else {
							$radio_station_data['previous_show_' . $time] = $previous_show;
							set_transient( 'radio_station_previous_show_' . $time, $previous_show, $expires );
						}
					}

					/* if ( isset( $current_show['split'] ) && $current_show['split'] ) {
						if ( isset( $current_show['real_start'] ) ) {
							// 2.3.3: second shift half so set to previous day and date
							$current_show['day'] = radio_station_get_previous_day( $shift['day'] );
							$current_show['date'] = radio_station_get_previous_date( $shift['date'] );
							$current_show['start'] = $current_show['real_start'];
						} elseif ( isset( $current_show['real_end'] ) ) {
							$current_show['end'] = $current_show['real_end'];
						}
					} */
				}

				if ( RADIO_STATION_DEBUG ) {
					echo '</span>' . PHP_EOL;
				}

				// 2.3.4: store previous shift
				$prev_shift = $shift;
			}
		}
	}

	// --- filter current show ---
	// 2.3.2: added time argument to filter
	$current_show = apply_filters( 'radio_station_current_show', $current_show, $time );

	// --- set to global data ---
	if ( !$time ) {
		$radio_station_data['current_show'] = $current_show;
	} else {
		$radio_station_data['current_show_' . $time] = $current_show;
	}

	return $current_show;
}

// -----------------
// Get Previous Show
// -----------------
// 2.3.3: added get previous show function
function radio_station_get_previous_show( $time = false ) {

	global $radio_station_data;

	$prev_show = false;

	// --- get cached current show value ---
	if ( !$time ) {
		if ( isset ( $radio_station_data['previous_show'] ) ) {
			$prev_show = $radio_station_data['previous_show'];
		} else {
			$prev_show = get_transient( 'radio_station_previous_show' );
		}
	} else {
		if ( isset ( $radio_station_data['previous_show_' . $time] ) ) {
			$prev_show = $radio_station_data['previous_show_' . $time];
		} else {
			$prev_show = get_transient( 'radio_station_previous_show_' . $time );
		}
	}

	// --- if not set it has expired so recheck schedule ---
	if ( !$prev_show ) {
		if ( !$time ) {
			$schedule = radio_station_get_current_schedule();
			if ( isset( $radio_station_data['previous_show'] ) ) {
				$prev_show = $radio_station_data['previous_show'];
			}
		} else {
			$schedule = radio_station_get_current_schedule( $time );
			if ( isset( $radio_station_data['previous_show_' . $time] ) ) {
				$prev_show = $radio_station_data['previous_show_' . $time];
			}
		}
	}

	// note: already filtered when set
	return $prev_show;
}

// -------------
// Get Next Show
// -------------
// 2.3.0: added new get next show function
// 2.3.2: added optional time argument
function radio_station_get_next_show( $time = false ) {

	global $radio_station_data;

	$next_show = false;

	// --- get cached current show value ---
	if ( !$time ) {
		if ( isset ( $radio_station_data['next_show'] ) ) {
			return $radio_station_data['next_show'];
		} else {
			$next_show = get_transient( 'radio_station_next_show' );
		}
	} else {
		if ( isset ( $radio_station_data['next_show_' . $time] ) ) {
			return $radio_station_data['next_show_' . $time];
		} else {
			$next_show = get_transient( 'radio_station_next_show_' . $time );
		}
	}

	// --- if not set it has expired so recheck schedule ---
	if ( !$next_show ) {
		if ( !$time ) {
			$schedule = radio_station_get_current_schedule();
			if ( isset( $radio_station_data['next_show'] ) ) {
				$next_show = $radio_station_data['next_show'];
			} else {
				$next_show = get_transient( 'radio_station_next_show' );
			}
		} else {
			$schedule = radio_station_get_current_schedule( $time );
			if ( isset( $radio_station_data['next_show_' . $time] ) ) {
				$next_show = $radio_station_data['next_show_' . $time];
			} else {
				$next_show = get_transient( 'radio_station_next_show_' . $time );
			}
		}

		// 2.3.2: added time argument to filter
		// 2.3.4: moved filter to where data is set so only applied once
		// $next_show = apply_filters( 'radio_station_next_show', $next_show, $time );
	}

	return $next_show;
}

// --------------
// Get Next Shows
// --------------
// 2.3.0: added new get next shows function
// 2.3.2: added optional time argument
function radio_station_get_next_shows( $limit = 3, $show_shifts = false, $time = false ) {

	global $radio_station_data;

	// --- get all show shifts ---
	// (this check is needed to prevent an endless loop!)
	if ( !$show_shifts ) {
		if ( !$time ) {
			$show_shifts = radio_station_get_current_schedule();
		} else {
			$show_shifts = radio_station_get_current_schedule( $time );
		}
	}

	// --- loop (remaining) shifts to add show data ---
	$next_shows = array();
	// 2.3.2: maybe set provided time as now
	if ( $time ) {
		$now = $time;
	} else {
		$now = radio_station_get_now();
	}

	// 2.3.2: use get time function with timezone
	// 2.3.2: fix to pass week day start as numerical (w)
	// 2.3.3: revert to passing week day start as day (l)
	// 2.3.3: added fix to start from previous day
	$today = radio_station_get_time( 'l', $now );
	$yesterday = radio_station_get_previous_day( $today );
	$weekdays = radio_station_get_schedule_weekdays( $yesterday );
	$weekdates = radio_station_get_schedule_weekdates( $weekdays, $now );

	if ( RADIO_STATION_DEBUG ) {
		echo '<span style="display:none;">';
		echo "Next Shows from " . $yesterday . PHP_EOL;
		print_r( $weekdays );
		print_r( $weekdates );
		echo '</span>';
	}

	// --- loop shifts to find next shows ---
	$current_split = false;
	foreach ( $weekdays as $day ) {
		if ( isset( $show_shifts[$day] ) ) {
			$shifts = $show_shifts[$day];
			foreach ( $shifts as $start => $shift ) {

				// --- get this shift start and end times ---
				// 2.3.2: replace strtotime with to_time for timezones
				// 2.3.2: fix to convert to 24 hour format first
				$shift_start = radio_station_convert_shift_time( $shift['start'] );
				$shift_end = radio_station_convert_shift_time( $shift['end'] );
				$shift_start_time = radio_station_to_time( $weekdates[$day] . ' ' . $shift_start );
				$shift_end_time = radio_station_to_time( $weekdates[$day] . ' ' . $shift_end );

				if ( RADIO_STATION_DEBUG ) {
					echo '<span style="display:none;">';
					echo 'Next? ' . $now . ' - ' . $shift_start_time . ' - ' . $shift_end_time . PHP_EOL;
					echo print_r( $shift, true ) . PHP_EOL;
					echo '</span>' . PHP_EOL;
				}

				// --- set current show ---
				// 2.3.2: set current show transient
				// 2.3.3: remove current show transient

				// --- check if show is upcoming ---
				if ( $now < $shift_start_time ) {

					// --- reset skip flag ---
					$skip = false;

					if ( $current_split ) {

						$skip = true;
						$current_split = false;

					} elseif ( isset( $shift['split'] ) && $shift['split'] ) {

						// --- dedupe for shifts split overnight ---
						if ( isset( $shift['real_end'] ) ) {
							$shift['end'] = $shift['real_end'];
							$current_split = true;
						} elseif ( isset( $shift['real_start'] ) ) {
							// 2.3.3: skip this shift instead of setting
							// (because second half of current show!)
							$skip = true;
						}

					} else {
						// --- reset split shift flag ---
						$current_split = false;
					}

					if ( !$skip ) {

						// --- maybe set next show transient ---
						// 2.3.3: also set global data key
						// 2.3.4: moved next show filter here (before setting data)
						if ( !isset( $next_show ) ) {
							$next_show = $shift;
							$next_expires = $shift_end_time - $now - 1;

							$next_show = apply_filters( 'radio_station_next_show', $next_show, $time );
							if ( !$time ) {
								$radio_station_data['next_show'] = $next_show;
								set_transient( 'radio_station_next_show', $next_show, $next_expires );
							} else {
								$radio_station_data['next_show_' . $time] = $next_show;
								set_transient( 'radio_station_next_show_' . $time, $next_show, $next_expires );
							}
							if ( RADIO_STATION_DEBUG ) {
								echo '<span style="display:none;">^^^ Next Show ^^^</span>';
							}
						}

						// --- add to next shows data ---
						// 2.3.2: set date for widget display
						$shift['date'] = $weekdates[$day];
						$next_shows[] = $shift;
						if ( RADIO_STATION_DEBUG ) {
							echo '<span style="display:none;">Next Shows: ' . print_r( $next_shows, true ) . '</span>';
						}

						// --- return if we have reached limit ---
						if ( count( $next_shows ) == $limit ) {
							$next_shows = apply_filters( 'radio_station_next_shows', $next_shows, $limit, $show_shifts );
							return $next_shows;
						}
					}
				}
			}
		}
	}

	// --- filter and return ---
	$next_shows = apply_filters( 'radio_station_next_shows', $next_shows, $limit, $show_shifts );

	return $next_shows;
}

// --------------------
// Get Current Playlist
// --------------------
// 2.3.3.5: added get current playlist function
function radio_station_get_current_playlist() {

	$current_show = radio_station_get_current_show();
	$show_id = $current_show['show']['id'];
	$playlists = radio_station_get_show_playlists( $show_id );
	if ( !$playlists || !is_array( $playlists ) || ( count ( $playlists ) < 1 ) ) {
		return false;
	}

	$playlist_id = $playlists[0]['ID'];
	$tracks = get_post_meta( $playlist_id, 'playlist', true );
	if ( !$tracks || !is_array( $tracks ) || ( count( $tracks ) < 1 ) ) {
		return false;
	}

	// --- split off tracks marked as queued ---
	$entries = $queued = $played = array();
	foreach ( $tracks as $i => $track ) {
		foreach ( $track as $key => $value ) {
			unset( $track[$key] );
			$key = str_replace( 'playlist_entry_', '', $key );
			$track[$key] = $value;
			$entries[$i] = $track;
		}
		if ( 'queued' == $entry['status'] ) {
			$queued[] = $entry;
		} elseif ( 'played' == $entry['status'] ) {
			$played[] = $entry;
		}
	}

	// --- get the track list for display  ---
	$playlist = array(
		'tracks'   => $entries,
		'queued'   => $queued,
		'played'   => $played,
		'latest'   => array_pop( $entries ),
		'id'       => $playlist_id,
		'url'      => get_permalink( $playlist_id ),
		'show'     => $show_id,
		'show_url' => get_permalink( $show_id ),
	);

	return $playlist;
}

// -----------------------
// Get Blog Posts for Show
// -----------------------
// 2.3.0: added show blog post data grabber
function radio_station_get_show_posts( $show_id = false, $args = array() ) {
	return radio_station_get_show_data( 'posts', $show_id, $args );
}

// ----------------------
// Get Playlists for Show
// ----------------------
// 2.3.0: added show playlist data grabber
function radio_station_get_show_playlists( $show_id = false, $args = array() ) {
	return radio_station_get_show_data( 'playlists', $show_id, $args );
}

// ---------
// Get Genre
// ---------
// 2.3.0: added genre data grabber
function radio_station_get_genre( $genre ) {
	// 2.3.3.8: explicitly check for numberic genre term ID
	$id = absint( $genre );
	if ( $id < 1 ) {
		// $genre = sanitize_title( $genre );
		$term = get_term_by( 'slug', $genre, RADIO_STATION_GENRES_SLUG );
		if ( !$term ) {
			$term = get_term_by( 'name', $genre, RADIO_STATION_GENRES_SLUG );
		}
	} else {
		$term = get_term_by( 'id', $genre, RADIO_STATION_GENRES_SLUG );
	}
	if ( !$term ) {
		return false;
	}
	$genre_data = array();
	$genre_data[$term->name] = array(
		'id'            => $term->term_id,
		'name'          => $term->name,
		'slug'          => $term->slug,
		'description'   => $term->description,
		'url'           => get_term_link( $term, RADIO_STATION_GENRES_SLUG ),
	);

	return $genre_data;
}

// ----------
// Get Genres
// ----------
// 2.3.0: added genres data grabber
function radio_station_get_genres( $args = false ) {

	$defaults = array( 'taxonomy' => RADIO_STATION_GENRES_SLUG, 'orderby' => 'name', 'hide_empty' => true );
	if ( $args && is_array( $args ) ) {
		foreach ( $args as $key => $value ) {
			$defaults[$key] = $value;
		}
	}
	$terms = get_terms( $defaults );
	$genres = array();
	if ( $terms ) {
		foreach ( $terms as $term ) {
			$genres[$term->name] = array(
				'id'            => $term->term_id,
				'name'          => $term->name,
				'slug'          => $term->slug,
				'description'   => $term->description,
				'url'           => get_term_link( $term, RADIO_STATION_GENRES_SLUG ),
			);
		}
	}

	// --- filter and return ---
	$genres = apply_filters( 'radio_station_get_genres', $genres, $args );

	return $genres;
}

// -------------------
// Get Shows for Genre
// -------------------
// 2.3.0: added get shows for genre data grabber
function radio_station_get_genre_shows( $genre = false ) {

	if ( !$genre ) {
		// --- get shows without a genre assigned ---
		// ref: https://core.trac.wordpress.org/ticket/29181
		$tax_query = array(
			array(
				'taxonomy' => RADIO_STATION_GENRES_SLUG,
				'operator' => 'NOT EXISTS',
			),
		);
	} else {
		// --- get shows with specific genre assigned ---
		$tax_query = array(
			array(
				'taxonomy' => RADIO_STATION_GENRES_SLUG,
				'field'    => 'slug',
				'terms'    => $genre,
			),
		);
	}
	$args = array(
		'post_type'   => RADIO_STATION_SHOW_SLUG,
		'post_status' => 'publish',
		'tax_query'   => $tax_query,
		'meta_query'  => array(
			'relation' => 'AND',
			array(
				'key'		=> 'show_sched',
				'compare'	=> 'EXISTS',
			),
			array(
				'key'		=> 'show_active',
				'value'		=> 'on',
				'compare'	=> '=',
			),
		),
	);
	$args = apply_filters( 'radio_station_show_genres_query_args', $args, $genre );
	$shows = new WP_Query( $args );

	return $shows;
}

// ----------------------
// Get Shows for Language
// ----------------------
// 2.3.0: added get shows for language data grabber
function radio_station_get_language_shows( $language = false ) {

	if ( !$language ) {
		// --- get shows without a language assigned ---
		// ref: https://core.trac.wordpress.org/ticket/29181
		$tax_query = array(
			array(
				'taxonomy' => RADIO_STATION_LANGUAGES_SLUG,
				'operator' => 'NOT EXISTS',
			),
		);
	} else {
		// --- get shows with specific language assigned ---
		$tax_query = array(
			array(
				'taxonomy' => RADIO_STATION_LANGUAGES_SLUG,
				'field'    => 'slug',
				'terms'    => $language,
			),
		);
	}

	$args = array(
		'post_type'   => RADIO_STATION_SHOW_SLUG,
		'post_status' => 'publish',
		'tax_query'   => $tax_query,
		'meta_query'  => array(
			'relation' => 'AND',
			array(
				'key'		=> 'show_sched',
				'compare'	=> 'EXISTS',
			),
			array(
				'key'		=> 'show_active',
				'value'		=> 'on',
				'compare'	=> '=',
			),
		),
	);
	$args = apply_filters( 'radio_station_show_languages_query_args', $args, $language );
	$shows = new WP_Query( $args );

	return $shows;
}


// ----------------------
// === Shift Checking ===
// ----------------------

// -------------------------
// Schedule Conflict Checker
// -------------------------
// (checks all existing show shifts for schedule)
// 2.3.0: added show shift conflict checker
function radio_station_check_shifts( $all_shifts ) {

	// TODO: check for start of week and end of week shift conflicts?

	$now = radio_station_get_now();
	$weekdays = radio_station_get_schedule_weekdays();
	$weekdates = radio_station_get_schedule_weekdates( $weekdays, $now );

	$conflicts = $checked_shifts = array();
	if ( count( $all_shifts ) > 0 ) {
		$prev_shift = $prev_prev_shift = false;
		// foreach ( $all_shifts as $day => $shifts ) {
		foreach ( $weekdays as $day ) {

			if ( isset( $all_shifts[$day] ) ) {
				$shifts = $all_shifts[$day];

				// --- get previous and next days for comparisons ---
				// 2.3.2: fix to use week date schedule
				$thisdate = $weekdates[$day];
				$date_time = radio_station_to_time( $weekdates[$day] . ' 00:00' );

				// --- check for conflicts (overlaps) ---
				foreach ( $shifts as $key => $shift ) {

					// --- set first shift checked ---
					// 2.3.2: added for checking against last shift
					if ( !isset( $first_shift ) ) {
						$first_shift = $shift;
					}

					// --- reset shift switches ---
					$set_shift = true;
					$conflict = $disabled = false;
					if ( isset( $shift['disabled'] ) && ( 'yes' == $shift['disabled'] ) ) {
						$disabled = true;
					}

					// --- account for split midnight times ---
					// 2.3.2: replace strtotime with to_time for timezones
					if ( ( '00:00 am' == $shift['start'] ) || ( '12:00 am' == $shift['start'] ) ) {
						$start_time = radio_station_to_time( $thisdate . ' 00:00' );
					} else {
						$shift_start = radio_station_convert_shift_time( $shift['start'] );
						$start_time = radio_station_to_time( $thisdate . ' ' . $shift_start );
					}
					if ( ( '11:59:59 pm' == $shift['end'] ) || ( '12:00 am' == $shift['end'] ) ) {
						$end_time = radio_station_to_time( $thisdate . ' 11:59:59' ) + 1;
					} else {
						$shift_end = radio_station_convert_shift_time( $shift['end'] );
						$end_time = radio_station_to_time( $thisdate . ' ' . $shift_end );
					}

					if ( false != $prev_shift ) {

						// note: previous shift start and end times set in previous loop iteration
						if ( RADIO_STATION_DEBUG ) {
							echo "Shift Date: " . $thisdate . " - Day: " . $day . " - Time: " . $date_time . PHP_EOL;
							$prevdata = $prev_shift['shift'];
							$prevday = $prev_shift['day'];
							$prevdate = $prev_shift['date'];
							echo "Previous Shift Date: " . $prevdate . " - Shift Day: " . $prevday . PHP_EOL;
							echo "Shift: " . print_r( $shift, true );
							echo "Previous Shift: " . print_r( $prev_shift, true );
						}

						// --- detect shift conflicts ---
						// (and maybe *attempt* to fix them up)
						if ( isset( $prev_start_time ) && ( $start_time == $prev_start_time ) ) {
							if ( $shift['split'] || $prev_shift['split'] ) {
								$conflict = 'overlap';
								if ( $shift['split'] && $prev_shift['split'] ) {
									// - need to compare start times on previous day -
									// 2.3.2: replace strtotime with to_time for timezones
									// 2.3.2: fix to convert to 24 hour format first
									$data = $shift['shift'];
									$real_start = radio_station_convert_shift_time( $data['real_start'] );
									$shiftdate = radio_station_get_previous_date( $thisdate );
									// $real_start_time = radio_station_to_time( $prevdate . ' ' . $real_start );
									$real_start_time = radio_station_to_time( $shiftdate . ' ' . $real_start );

									// 2.3.2: fix to calculation of previous shift day start time
									$prevdata = $prev_shift['shift'];
									// $prevday = $prevdata['day'];
									// $prevdate = radio_station_get_previous_date( $thisdate, $prevday );
									$prevdate = $prev_shift['date'];
									$prev_real_start = radio_station_convert_shift_time( $prevdata['real_start'] );
									$prev_real_start_time = radio_station_to_time( $prevdate . ' ' . $prev_real_start );

									// --- compare start times ---
									if ( $real_start_time > $prev_real_start_time ) {
										// - current shift started later (overwrite from midnight) -
										$set_shift = true;
									} elseif ( $real_start_time == $prev_real_start_time ) {
										// - do not duplicate, already recorded -
										$conflict = false;
										// - total overlap, check last updated post time -
										$updated = strtotime( $shift['updated'] );
										$prev_updated = strtotime( $prev_shift['updated'] );
										if ( $updated < $prev_updated ) {
											$set_shift = false;
										}
									}
								} elseif ( $shift['split'] ) {
									// - the current shift has been split overnight -
									// assume previous shift is correct (ignore new shift time)
									$set_shift = false;
								} elseif ( $prev_shift['split'] ) {
									// the previous shift has been split overnight
									// so we will assume the new shift start is correct
									// (overwrites previous shift from midnight key)
									$set_shift = true;
								}
							} else {
								$conflict = 'same_start';
								// - we do not know which of these is correct -
								// no solution here, so check most recent last updated time
								// we will assume (without certainty) most recent is correct
								$updated = strtotime( $shift['updated'] );
								$prev_updated = strtotime( $prev_shift['updated'] );
								if ( $updated < $prev_updated ) {
									$set_shift = false;
								}
							}
						} elseif ( isset( $prev_end_time ) && ( $start_time < $prev_end_time ) ) {

							if ( ( $end_time > $prev_start_time ) || ( $end_time > prev_end_time ) ) {

								// --- set the previous shift end time to current shift start ---
								$conflict = 'overlap';

								// --- modify only if this shift is not disabled ---
								if ( !$disabled ) {
									// 2.3.2: variable type fix (from checked_shift)
									// 2.3.2: fix for midnight starting aplit shifts
									// 2.3.2: set checked shifts with day key directly
									if ( '00:00 am' == $prev_shift['start'] ) {
										$prev_shift['start'] = '12:00 am';
									}
									$checked_shifts[$day][$prev_shift['start']]['end'] = $shift['start'];
									$checked_shifts[$day][$prev_shift['start']]['trimmed'] = true;

									if ( RADIO_STATION_DEBUG ) {
										echo "Previous Previous Shift: " . print_r( $prev_prev_shift, true );
									}

									// --- fix for real end of first part of previous split shift ---
									if ( isset( $prev_shift['split'] ) && $prev_shift['split'] && isset( $prev_shift['real_start'] ) ) {
										if ( isset( $prev_prev_shift ) && isset( $prev_prev_shift['split'] ) && $prev_prev_shift['split'] ) {
											$checked_shifts[$prev_prev_shift['start']]['real_end'] = $shift['start'];
											$checked_shifts[$prev_prev_shift['start']]['trimmed'] = true;
										}
									}
								}

								// --- conflict debug output ---
								if ( RADIO_STATION_DEBUG ) {
									$debug = "Conflicting Start Time: " . date( "m-d l H:i", $start_time ) . " (" . $start_time . ")" . PHP_EOL;
									$debug .= '[ ' . radio_station_get_time( "m-d l H:i", $start_time ) . ' ]';
									$debug .= "Overlaps previous End Time: " .  date( "m-d l H:i", $prev_end_time ) . " (" . $prev_end_time . ")" . PHP_EOL;
									$debug .= '[ ' . radio_station_get_time( "m-d l H:i", $prev_end_time ) . ' ]';
									// $debug .= "Shift: " . print_r( $shift, true );
									// $debug .= "Previous Shift: " . print_r( $prev_shift, true );
									radio_station_debug( $debug );
								}
							}
						}
					}

					// --- maybe store shift conflict data ---
					if ( $conflict ) {

						// ---- set short shift time data ---
						$shift_start = $shift['shift']['start_hour'] . ':' . $shift['shift']['start_min'] . $shift['shift']['start_meridian'];
						$shift_end = $shift['shift']['end_hour'] . ':' . $shift['shift']['end_min'] . $shift['shift']['end_meridian'];
						$prev_shift_start = $prev_shift['shift']['start_hour'] . ':' . $prev_shift['shift']['start_min'] . $prev_shift['shift']['start_meridian'];
						$prev_shift_end = $prev_shift['shift']['end_hour'] . ':' . $prev_shift['shift']['end_min'] . $prev_shift['shift']['end_meridian'];

						// --- store conflict for this shift ---
						$conflicts[$shift['show']][] = array(
							'show'          => $shift['show'],
							'day'           => $shift['shift']['day'],
							'start'         => $shift_start,
							'end'           => $shift_end,
							'disabled'      => $disabled,
							'with_show'     => $prev_shift['show'],
							'with_day'      => $prev_shift['shift']['day'],
							'with_start'    => $prev_shift_start,
							'with_end'      => $prev_shift_end,
							'with_disabled' => $prev_disabled,
							'conflict'      => $conflict,
							'duplicate'     => false,
						);

						// --- store for previous shift only if a different show ---
						if ( $shift['show'] != $prev_shift['show'] ) {
							$conflicts[$prev_shift['show']][] = array(
								'show'          => $prev_shift['show'],
								'day'           => $prev_shift['shift']['day'],
								'start'         => $prev_shift_start,
								'end'           => $prev_shift_end,
								'disabled'      => $prev_disabled,
								'with_show'     => $shift['show'],
								'with_day'      => $shift['shift']['day'],
								'with_start'    => $shift_start,
								'with_end'      => $shift_end,
								'with_disabled' => $disabled,
								'conflict'      => $conflict,
								'duplicate'     => true,
							);
						}
					}

					// --- set current shift to previous for next iteration ---
					$prev_start_time = $start_time;
					$prev_end_time = $end_time;
					if ( $prev_shift ) {
						$prev_prev_shift = $prev_shift;
					}
					$prev_shift = $shift;
					$prev_disabled = $disabled;

					// --- set the now checked shift data ---
					// (...but only if not disabled!)
					if ( $set_shift && !$disabled ) {
						// - no longer need shift and post updated times -
						unset( $shift['shift'] );
						unset( $shift['updated'] );
						if ( '00:00 am' == $shift['start'] ) {
							$shift['start'] = '12:00 am';
						}
						// 2.3.2: set checked shifts with day key directly
						$checked_shifts[$day][$shift['start']] = $shift;
					}
				}
			}

			// --- set checked shifts for day ---
			// 2.3.2: set checked shifts with day key directly
			// $all_shifts[$day] = $checked_shifts;
		}
	}

	// --- check last shift against first shift ---
	// 2.3.2: added for possible overlap (split by weekly schedule dates)
	if ( isset( $shift ) && ( $shift != $first_shift ) ) {

		// --- use days for next week to compare ---
		$shift_start = radio_station_convert_shift_time( $shift['start'] );
		$shift_end = radio_station_convert_shift_time( $shift['end'] );
		$last_shift_start = radio_station_to_time( 'next ' . $shift['day'] . ' ' . $shift_start );
		$last_shift_end = radio_station_to_time( 'next ' . $shift['day'] . ' ' . $shift_end );
		$shift_start = radio_station_convert_shift_time( $first_shift['start'] );
		$shift_end = radio_station_convert_shift_time( $first_shift['end'] );
		$first_shift_start = radio_station_to_time( 'next ' . $first_shift['day'] . ' ' . $shift_start );
		$first_shift_end = radio_station_to_time( 'next ' . $first_shift['day'] . ' ' . $shift_end );

		if ( RADIO_STATION_DEBUG ) {
			echo 'Last Shift Start: ' . $shift['day'] . ' ' . $shift_start . ' - (' . $last_shift_start . ')' . PHP_EOL;
			echo 'First Shift End: ' . $first_shift['day'] . ' ' . $shift_end . ' - (' . $first_shift_end . ')' . PHP_EOL;
		}

		if ( $last_shift_start < $first_shift_end ) {

			// --- record a conflict ---
			if ( RADIO_STATION_DEBUG ) {
				echo "First/Last Shift Overlap Conflict" . PHP_EOL;
			}

			/*
			// --- store conflict for this shift ---
			$conflicts[$first_shift['show']][] = array(
				'show'          => $first_shift['show'],
				'day'           => $first_shift['shift']['day'],
				'start'         => $first_shift_start,
				'end'           => $first_shift_end,
				'disabled'      => $first_shift['shift']['disabled'],
				'with_show'     => $last_shift['show'],
				'with_day'      => $last_shift['shift']['day'],
				'with_start'    => $last_shift_start,
				'with_end'      => $last_shift_end,
				'with_disabled' => $last_shift['shift']['disabled'],
				'conflict'      => 'overlap',
				'duplicate'     => false,
			);

			// --- store for other shift if different show ---
			if ( $first_shift['show'] != $last_shift['show'] ) {
				$conflicts[$last_shift['show']][] = array(
					'show'          => $last_shift['show'],
					'day'           => $last_shift['shift']['day'],
					'start'         => $last_shift_start,
					'end'           => $last_shift_end,
					'disabled'      => $last_shift['shift']['disabled'],
					'with_show'     => $first_shift['show'],
					'with_day'      => $first_shift['shift']['day'],
					'with_start'    => $first_shift_start,
					'with_end'      => $first_shift_end,
					'with_disabled' => $first_shift['shift']['disabled'],
					'conflict'      => 'overlap',
					'duplicate'     => true,
				);
			} */
		}
	}

	// --- check if any conflicts found ---
	if ( count( $conflicts ) > 0 ) {

		// --- debug point ---
		if ( RADIO_STATION_DEBUG ) {
			$debug = "Shift Conflict Data: " . print_r( $conflicts, true ) . PHP_EOL;
			radio_station_debug( $debug );
		}

		// --- save any conflicts found ---
		update_option( 'radio_station_schedule_conflicts', $conflicts );

	} else {
		// --- clear conflicts data ---
		delete_option( 'radio_station_schedule_conflicts' );
	}

	return $checked_shifts;
}

// ------------------
// Show Shift Checker
// ------------------
// (checks shift being saved against other shows)
function radio_station_check_shift( $show_id, $shift, $context = 'all' ) {

	global $radio_station_data;

	// 2.3.2: bug out if day is empty
	if ( '' == $shift['day'] ) {
		return false;
	}

	// 2.3.2: manual bypass of shift checking
	if ( isset( $_REQUEST['check-bypass'] ) && ( '1' == $_REQUEST['check-bypass'] ) ) {
		return false;
	}

	// --- get all show shift times ---
	if ( isset( $radio_station_data['all-shifts'] ) ) {
		// --- get stored data ---
		$all_shifts = $radio_station_data['all-shifts'];
	} else {
		// (with conflict checking off as we are doing that now)
		$all_shifts = radio_station_get_show_shifts( false, false );

		// --- store this data for efficiency ---
		$radio_station_data['all-shifts'] = $all_shifts;
	}

	// --- convert days to dates for checking ---
	$now = radio_station_get_now();
	$weekdays = radio_station_get_schedule_weekdays();
	$weekdates = radio_station_get_schedule_weekdates( $weekdays, $now );

	// --- get shows to check against via context ---
	$check_shifts = array();
	if ( 'all' == $context ) {
		$check_shifts = $all_shifts;
	} elseif ( 'shows' == $context ) {
		// --- check only against other show shifts ---
		foreach ( $all_shifts as $day => $day_shifts ) {
			foreach ( $day_shifts as $start => $day_shift ) {
				// --- ...so remove any shifts for this show ---
				if ( $day_shift['show'] != $show_id ) {
					$check_shifts[$day][$start] = $day_shift;
				}
			}
		}
	}

	// 2.3.2: to doubly ensure shifts are set in schedule order
	$sorted_shifts = array();
	foreach ( $weekdays as $weekday ) {
		if ( isset( $check_shifts[$weekday] ) ) {
			$sorted_shifts[$weekday] = $check_shifts[$weekday];
		}
	}
	$check_shifts = $sorted_shifts;

	// --- get shift start and end time ---
	// 2.3.2: fix to convert to 24 hour times first
	$start_time =  $shift['start_hour'] . ':' . $shift['start_min'] . $shift['start_meridian'];
	$end_time = $shift['end_hour'] . ':' . $shift['end_min'] . $shift['end_meridian'];
	$start_time = radio_station_convert_shift_time( $start_time );
	$end_time = radio_station_convert_shift_time( $end_time );

	// 2.3.2: use next week day instead of date
	$shift_start_time = radio_station_to_time( $weekdates[$shift['day']] . ' ' . $start_time );
	$shift_end_time = radio_station_to_time( $weekdates[$shift['day']] . ' ' . $end_time );
	// $shift_start_time = radio_station_to_time( '+2 weeks ' . $shift['day'] . ' ' . $start_time );
	// $shift_end_time = radio_station_to_time( '+2 weeks ' . $shift['day'] . ' ' . $end_time );
	if ( $shift_end_time < $shift_start_time ) {
		$shift_end_time = $shift_end_time + ( 24 * 60 * 60 );
	}

	if ( RADIO_STATION_DEBUG ) {
		echo "Checking Shift for Show " . $show_id . ": ";
		echo $shift['day'] . " - " . $weekdates[$shift['day']] . " - " . $shift['start_hour'] . ":" . $shift['start_min'] . $shift['start_meridian'];
		echo "(" . $shift_start_time . ")";
		echo " to " . $weekdates[$shift['day']] . " - " . $shift['end_hour'] . ":" . $shift['end_min'] . $shift['end_meridian'];
		echo "(" . $shift_end_time . ")" . PHP_EOL;
	}

	// --- check for conflicts with other show shifts ---
	$conflicts = array();
	foreach ( $check_shifts as $day => $day_shifts ) {
		// 2.3.2: removed day match check
		// if ( $day == $shift['day'] ) {
			foreach ( $day_shifts as $i => $day_shift ) {

				if ( !isset( $first_shift ) ) {
					$first_shift = $day_shift;
				}

				// 2.3.2: replace strtotime with to_time for timezones
				// 2.3.2: fix to convert to 24 hour times first
				$shift_start = radio_station_convert_shift_time( $day_shift['start'] );
				$shift_end = radio_station_convert_shift_time( $day_shift['end'] );

				// 2.3.2: use next week day instead of date
				$day_shift_start_time = radio_station_to_time( $day_shift['date'] . ' ' . $shift_start );
				$day_shift_end_time = radio_station_to_time( $day_shift['date'] . ' ' . $shift_end );
				// $day_shift_start_time = radio_station_to_time( '+2 weeks ' . $day_shift['day'] . ' ' . $shift_start );
				// $day_shift_end_time = radio_station_to_time( '+2 weeks ' . $day_shift['day'] . ' ' . $shift_end );
				// 2.3.2: adjust for midnight with change to use non-split shifts
				if ( $day_shift_end_time < $day_shift_start_time ) {
					$day_shift_end_time = $day_shift_end_time + ( 24 * 60 * 60 );
				}

				// --- ignore if this is the same shift we are checking ---
				$check_shift = true;
				if ( $day_shift['show'] == $show_id ) {
					// ? only ignore same shift not same show ?
					// if ( ( $day_shift_start_time == $shift_start_time ) && ( $day_shift_end_time == $shift_end_time ) ) {
						$check_shift = false;
					// }
				}

				if ( $check_shift ) {

					if ( RADIO_STATION_DEBUG ) {
						echo "...with Shift for Show " . $day_shift['show'] . ": ";
						echo $day_shift['day'] . " - " . $day_shift['date'] . " - " . $day_shift['start'] . " (" . $day_shift_start_time . ")";
						echo " to " . $day_shift['end'] . " (" . $day_shift_end_time . ")" . PHP_EOL;
					}

					// 2.3.2: improved shift checking logic
					// 2.3.3.6: separated logic for conflict match code
					$conflict = false;
					if ( ( $shift_start_time < $day_shift_start_time ) && ( $shift_end_time > $day_shift_start_time ) ) {
						// if the new shift starts before existing shift but ends after existing shift starts
						$conflict = 'start overlap';
					} elseif ( ( $shift_start_time < $day_shift_start_time ) && ( $shift_end_time > $day_shift_end_time ) ) {
						// ...or starts before but ends after the existing shift end time
						$conflict = 'blockout overlap';
					} elseif ( ( $shift_start_time == $day_shift_start_time ) ) {
						// ...or new shift starts at the same time as the existing shift
						$conflict = 'equal start time';
					} elseif ( ( $shift_start_time > $day_shift_start_time ) && ( $shift_end_time < $day_shift_end_time ) ) {
						// ...or if the new shift starts after existing shift and ends before it ends
						$conflict = 'internal overlap';
					} elseif ( ( $shift_start_time > $day_shift_start_time ) && ( $shift_start_time < $day_shift_end_time ) ) {
						// ...or the new shift starts after the existing shift but before it ends
						$conflict = 'end overlap';
					}
					if ( $conflict ) {
						// --- if there is a shift overlap conflict ---
						$conflicts[] = $day_shift;
						if ( RADIO_STATION_DEBUG ) {
							echo '^^^ CONFLICT ( ' . $conflict . ' ) ^^^' . PHP_EOL;
						}
					}
				}
			}
		// }
	}

	// --- recheck for first shift overlaps ---
	// (not implemented as not needed)
	/* if ( isset( $first_shift ) ) {
		// --- check for first shift overlap using next week ---
		$shift_start = radio_station_convert_shift_time( $first_shift['start'] );
		$shift_end = radio_station_convert_shift_time( $first_shift['end'] );
		$first_shift_start_time = radio_station_to_time( $first_shift['date'] . ' ' . $shift_start ) + ( 7 * 24 * 60 * 60 );
		$first_shift_end_time = radio_station_to_time( $first_shift['date'] . ' ' . $shift_end ) + ( 7 * 24 * 60 * 60 );

		if ( RADIO_STATION_DEBUG ) {
			echo "...with First Shift for Show " . $first_shift['show'] . ": ";
			echo $first_shift['day'] . " - " . $first_shift['date'] . " - " . $first_shift['start'] . " (" . $first_shift_start_time . ")";
			echo " to " . $first_shift['end'] . " (" . $first_shift_end_time . ")" . PHP_EOL;
		}

		if ( ( ( $shift_start_time < $first_shift_start_time ) && ( $shift_end_time > $first_shift_start_time ) )
			 || ( ( $shift_start_time < $first_shift_start_time ) && ( $shift_end_time > $first_shift_end_time ) )
			 || ( $shift_start_time == $first_shift_start_time )
			 || ( ( $shift_start_time > $first_shift_start_time ) && ( $shift_end_time < $first_shift_end_time ) )
			 || ( ( $shift_start_time > $first_shift_start_time ) && ( $shift_start_time < $first_shift_end_time ) ) ) {
			$conflicts[] = $first_shift;
			if ( RADIO_STATION_DEBUG ) {
				echo "^^^ CONFLICT ^^^" . PHP_EOL;
			}
		}
	} */

	// --- recheck for last shift ---
	// (for date based schedule overflow rechecking)
	if ( isset( $day_shift ) ) {

		// 2.3.3.6: added check to not last check shift against itself
		if ( ( $day_shift['show'] != $show_id )
		  || ( $day_shift['day'] != $shift['day'] )
		  || ( $day_shift['start'] != $shift['start'] )
		  || ( $day_shift['end'] != $shift['end'] ) ) {

			// --- check for new shift overlap using next week ---
			$shift_start_time = $shift_start_time + ( 7 * 24 * 60 * 60 );
			$shift_end_time = $shift_end_time + ( 7 * 24 * 60 * 60 );

			if ( RADIO_STATION_DEBUG ) {
				echo "...with Last Shift (using next week):" . PHP_EOL;
				// echo radio_station_get_time( 'date', $shift_start_time ) . " - " . $shift['start'] . " (" . $shift_start_time . ")";
				// echo " to " . $shift['end'] . " (" . $shift_end_time . ")" . PHP_EOL;
				echo $day_shift['day'] . " - " . radio_station_get_time( 'date', $day_shift_start_time );
				echo " - " . $day_shift['start'] . " (" . $day_shift_start_time . ")";
				echo " to " . $day_shift['end'] . " (" . $day_shift_end_time . ")" . PHP_EOL;
			}

			// 2.3.3.6: separated logic for conflict match code
			$conflict = false;
			if ( ( $shift_start_time < $day_shift_start_time ) && ( $shift_end_time > $day_shift_start_time ) ) {
				$conflict = 'start overlap';
			} elseif ( ( $shift_start_time < $day_shift_start_time ) && ( $shift_end_time > $day_shift_end_time ) ) {
				$conflict = 'blockout overlap';
			} elseif ( $shift_start_time == $day_shift_start_time ) {
				$conflict = 'equal start time';
			} elseif ( ( $shift_start_time > $day_shift_start_time ) && ( $shift_end_time < $day_shift_end_time ) ) {
				$conflict = 'internal overlap';
			} elseif ( ( $shift_start_time > $day_shift_start_time ) && ( $shift_start_time < $day_shift_end_time ) ) {
				$conflict = 'end overlap';
			}
			if ( $conflict ) {
				$conflicts[] = $day_shift;
				if ( RADIO_STATION_DEBUG ) {
					echo '^^^ CONFLICT ( ' . $conflict . ') ^^^' . PHP_EOL;
				}
			}
		}
	}

	if ( count( $conflicts ) == 0 ) {
		return false;
	}

 	return $conflicts;
}

// ------------------
// New Shifts Checker
// ------------------
// (checks show shifts for conflicts with same show)
function radio_station_check_new_shifts( $new_shifts ) {

	if ( isset( $_REQUEST['check-bypass'] ) && ( '1' == $_REQUEST['check-bypass'] ) ) {
		if ( RADIO_STATION_DEBUG ) {
			echo "New Shift Checking Bypassed." . PHP_EOL;
		}
		return $new_shifts;
	}

	// --- debug point ---
	if ( RADIO_STATION_DEBUG ) {
		$debug = "New Shifts: " . print_r( $new_shifts, true );
		radio_station_debug( $debug );
	}

	// --- convert days to dates for checking ---
	$now = radio_station_get_now();
	$weekdays = radio_station_get_schedule_weekdays();
	$weekdates = radio_station_get_schedule_weekdates( $weekdays, $now );

	// --- double loop shifts to check against others ---
	foreach ( $new_shifts as $i => $shift_a ) {

		if ( '' != $shift_a['day'] ) {

			// --- get shift A start and end times ---
			// 2.3.2: replace strtotime with to_time for timezones
			// 2.3.2: fix to convert to 24 hour format first
			$shift_a_start = $shift_a['start_hour'] . ':' . $shift_a['start_min'] . $shift_a['start_meridian'];
			$shift_a_end = $shift_a['end_hour'] . ':' . $shift_a['end_min'] . $shift_a['end_meridian'];
			$shift_a_start = radio_station_convert_shift_time( $shift_a_start );
			$shift_a_end = radio_station_convert_shift_time( $shift_a_end );

			// 2.3.2: use next week day instead of date
			$shift_a_start_time = radio_station_to_time( $weekdates[$shift_a['day']] . ' ' . $shift_a_start );
			$shift_a_end_time = radio_station_to_time( $weekdates[$shift_a['day']] . ' ' . $shift_a_end );
			// $shift_a_start_time = radio_station_to_time( '+2 weeks ' . $shift_a['day'] . ' ' . $shift_a_start );
			// $shift_a_end_time = radio_station_to_time( '+2 weeks ' . $shift_a['day'] . ' ' . $shift_a_end );
			if ( $shift_a_end_time < $shift_a_start_time ) {
				$shift_a_end_time = $shift_a_end_time + ( 24 * 60 * 60 );
			}

			// --- debug point ---
			if ( RADIO_STATION_DEBUG ) {
				$a_start = $shift_a['day'] . ' ' . $shift_a['start_hour'] . ':' . $shift_a['start_min'] . $shift_a['start_meridian'] . ' (' . $shift_a_start_time . ')';
				$a_end = $shift_a['day'] . ' ' . $shift_a['end_hour'] . ':' . $shift_a['end_min'] . $shift_a['end_meridian'] . ' (' . $shift_a_end_time . ')';
				$debug = "Shift A Start: " . $a_start . PHP_EOL . 'Shift A End: ' . $a_end . PHP_EOL;
				radio_station_debug( $debug );
			}

			foreach ( $new_shifts as $j => $shift_b ) {

				if ( $i != $j ) {

					if ( RADIO_STATION_DEBUG ) {
						echo $i . ' ::: ' . $j . PHP_EOL;
					}

					if ( '' != $shift_b['day'] ) {
						// --- get shift B start and end times ---
						// 2.3.2: replace strtotime with to_time for timezones
						$shift_b_start = $shift_b['start_hour'] . ':' . $shift_b['start_min'] . $shift_b['start_meridian'];
						$shift_b_end = $shift_b['end_hour'] . ':' . $shift_b['end_min'] . $shift_b['end_meridian'];
						$shift_b_start = radio_station_convert_shift_time( $shift_b_start );
						$shift_b_end = radio_station_convert_shift_time( $shift_b_end );

						// 2.3.2: use next week day instead of date
						$shift_b_start_time = radio_station_to_time( $weekdates[$shift_b['day']] . ' ' . $shift_b_start );
						$shift_b_end_time = radio_station_to_time( $weekdates[$shift_b['day']] . ' ' . $shift_b_end );
						// $shift_b_start_time = radio_station_to_time( '+2 weeks ' . $shift_b['day'] . ' ' . $shift_b_start );
						// $shift_b_end_time = radio_station_to_time( '+2 weeks ' . $shift_b['day'] . ' ' . $shift_b_end );
						if ( $shift_b_end_time < $shift_b_start_time ) {
							$shift_b_end_time = $shift_b_end_time + ( 24 * 60 * 60 );
						}

						// --- debug point ---
						if ( RADIO_STATION_DEBUG ) {
							$b_start = $shift_b['day'] . ' ' . $shift_b_start . ' (' . $shift_b_start_time . ')';
							$b_end = $shift_b['day'] . ' ' . $shift_b_end . ' (' . $shift_b_end_time . ')';
							$debug = "with Shift B Start: " . $b_start . ' - Shift B End: ' . $b_end . PHP_EOL;
							// radio_station_debug( $debug, false, 'show-shift-save.log' );
							radio_station_debug( $debug );
						}

						// --- compare shift A and B times ---
						if ( ( ( $shift_a_start_time < $shift_b_start_time ) && ( $shift_a_end_time > $shift_b_start_time ) )
							 || ( ( $shift_a_start_time < $shift_b_start_time ) && ( $shift_a_end_time > $shift_b_end_time ) )
							 || ( $shift_a_start_time == $shift_b_start_time )
						     || ( ( $shift_a_start_time > $shift_b_start_time ) && ( $shift_a_end_time < $shift_b_end_time ) )
						     || ( ( $shift_a_start_time > $shift_b_start_time ) && ( $shift_a_start_time < $shift_b_end_time ) ) ) {

							// --- maybe disable shift B ---
							// 2.3.2: added check for isset on disabled key
							if ( ( !isset( $new_shifts[$i]['disabled'] ) || ( 'yes' != $new_shifts[$i]['disabled'] ) )
								&& ( !isset( $new_shifts[$j]['disabled'] ) || ( 'yes' != $new_shifts[$j]['disabled'] ) ) ) {

								// --- debug point ---
								if ( RADIO_STATION_DEBUG ) {
									$debug = PHP_EOL . "* Conflict Found! New Shift (B) Disabled ";
									if ( ( $shift_a_start_time < $shift_b_start_time ) && ( $shift_a_end_time > $shift_b_start_time ) ) {$debug .= "[A]";}
									if ( ( $shift_a_start_time < $shift_b_start_time ) && ( $shift_a_end_time > $shift_b_end_time ) ) {$debug .= "[B]";}
									if ( $shift_a_start_time == $shift_b_start_time ) {$debug .= "[C]";}
									if ( ( $shift_a_start_time > $shift_b_start_time ) && ( $shift_a_end_time < $shift_b_end_time ) ) {$debug .= "[D]";}
									if ( ( $shift_a_start_time > $shift_b_start_time ) && ( $shift_a_start_time < $shift_b_end_time ) ) {$debug .= "[E]";}
									$debug .= "*" . PHP_EOL;
									radio_station_debug( $debug );
								}

								$new_shifts[$j]['disabled'] = 'yes';

							} else {
								if ( RADIO_STATION_DEBUG ) {
									echo "[Conflict with disabled shift.]" . PHP_EOL;
								}
							}
						}
					}
				}
			}
		}
	}

	// --- debug point ---
	if ( RADIO_STATION_DEBUG ) {
		$debug = "Checked New Shifts: " . print_r( $new_shifts, true ) . PHP_EOL;
		radio_station_debug( $debug );
	}

	return $new_shifts;
}

// -------------------
// Validate Shift Time
// -------------------
// 2.3.0: added check for incomplete shift times
function radio_station_validate_shift( $shift ) {

	if ( '' == $shift['day'] ) {
		$shift['disabled'] = 'yes';
	}
	if ( ( '' == $shift['start_meridian'] ) || ( '' == $shift['end_meridian'] ) ) {
		$shift['disabled'] = 'yes';
	}
	if ( ( '' == $shift['start_hour'] ) || ( '' == $shift['end_hour'] ) ) {
		$shift['disabled'] = 'yes';
	}
	if ( '' == $shift['start_min'] ) {
		$shift['start_min'] = '00';
	}
	if ( '' == $shift['end_min'] ) {
		$shift['end_min'] = '00';
	}

	return $shift;
}


// -------------------
// === Show Avatar ===
// -------------------

// ------------------
// Update Show Avatar
// ------------------
// 2.3.0: trigger show avatar check/update when editing
add_action( 'replace_editor', 'radio_station_update_show_avatar', 10, 2 );
function radio_station_update_show_avatar( $replace_editor, $post ) {
	$show_id = $post->ID;
	radio_station_get_show_avatar_id( $show_id );

	return $replace_editor;
}

// ------------------
// Get Show Avatar ID
// ------------------
// 2.3.0: added get show avatar ID with thumbnail update
// note: existing thumbnail (featured image) ID is duplicated to the show avatar ID,
// allowing for handling of Show Avatars and Featured Images separately.
function radio_station_get_show_avatar_id( $show_id ) {

	// --- get thumbnail and avatar ID ---
	$avatar_id = get_post_meta( $show_id, 'show_avatar', true );

	// --- check thumbnail to avatar updated switch ---
	$updated = get_post_meta( $show_id, '_rs_image_updated', true );
	if ( !$updated ) {
		if ( !$avatar_id ) {
			$thumbnail_id = get_post_meta( $show_id, '_thumbnail_id', true );
			if ( $thumbnail_id ) {
				// --- duplicate the existing thumbnail to avatar meta ---
				$avatar_id = $thumbnail_id;
				add_post_meta( $show_id, 'show_avatar', $avatar_id );
			}
		}
		// --- add a flag indicating image has been updated ---
		add_post_meta( $show_id, '_rs_image_updated', true );
	}

	// --- filter and return ---
	$avatar_id = apply_filters( 'radio_station_show_avatar_id', $avatar_id, $show_id );

	return $avatar_id;
}

// -------------------
// Get Show Avatar URL
// -------------------
// 2.3.0: added to get the show avatar URL
function radio_station_get_show_avatar_url( $show_id, $size = 'thumbnail' ) {

	// --- get avatar ID ---
	$avatar_id = radio_station_get_show_avatar_id( $show_id );

	// --- get the attachment image source ---
	$avatar_url = false;
	if ( $avatar_id ) {
		$avatar_src = wp_get_attachment_image_src( $avatar_id, $size );
		$avatar_url = $avatar_src[0];
	}

	// --- filter and return ---
	$avatar_url = apply_filters( 'radio_station_show_avatar_url', $avatar_url, $show_id );
	return $avatar_url;
}

// ---------------
// Get Show Avatar
// ---------------
// 2.3.0: added this function for getting show avatar tag
function radio_station_get_show_avatar( $show_id, $size = 'thumbnail', $attr = array() ) {

	// --- get avatar ID ---
	$avatar_id = radio_station_get_show_avatar_id( $show_id );

	// --- get the attachment image tag ---
	$avatar = false;
	if ( $avatar_id ) {
		$avatar = wp_get_attachment_image( $avatar_id, $size, false, $attr );
	}

	// --- filter and return ---
	$avatar = apply_filters( 'radio_station_show_avatar', $avatar, $show_id );
	return $avatar;
}


// ---------------------
// === URL Functions ===
// ---------------------

// -----------------
// Get Streaming URL
// -----------------
// 2.3.0: added get streaming URL helper
function radio_station_get_stream_url() {
	$streaming_url = '';
	$stream = radio_station_get_setting( 'streaming_url' );
	if ( $stream && ( '' != $stream ) ) {
		$streaming_url = $stream;
	}
	$streaming_url = apply_filters( 'radio_station_stream_url', $streaming_url );

	return $streaming_url;
}

// -----------------
// Get Streaming URL
// -----------------
// 2.3.3.9: added get fallback URL helper
function radio_station_get_fallback_url() {
	$fallback_url = '';
	$fallback = radio_station_get_setting( 'fallback_url' );
	if ( $fallback && ( '' != $fallback ) ) {
		$fallback_url = $fallback;
	}
	$fallback_url = apply_filters( 'radio_station_fallback_url', $fallback_url );

	return $fallback_url;
}

// Get Stream Formats
// ------------------
// 2.3.3.7: added streaming format options
function radio_station_get_stream_formats() {

	// TODO: recheck amplitude formats ?
	// [Amplitude] HTML5 Support - mp3, aac ...?
	// ref: https://en.wikipedia.org/wiki/HTML5_audio#Supporting_browsers
	// [Howler] mp3, opus, ogg, wav, aac, m4a, mp4, webm
	// +mpeg, oga, caf, weba, webm, dolby, flac
	// [JPlayer] Audio: mp3, m4a - Video: m4v
	// +Audio: webma, oga, wav, fla, rtmpa +Video: webmv, ogv, flv, rtmpv
	// [Media Elements] Audio: mp3, wma, wav +Video: mp4, ogg, webm, wmv

	$formats = array(
		'aac'	=> 'AAC/M4A',		// A/H/J
		'mp3'	=> 'MP3',			// A/H/J
		'ogg'	=> 'OGG',			// H
		'oga'	=> 'OGA',			// H/J
		'webm'	=> 'WebM',			// H/J
		'rtmpa' => 'RTMPA',			// J
		'opus'  => 'OPUS',			// H
	);

	// --- filter and return ---
	$formats = apply_filters( 'radio_station_stream_formats', $formats );
	return $formats;
}

// ---------------
// Get Station URL
// ---------------
function radio_station_get_station_url() {
	$station_url = '';
	$page_id = radio_station_get_setting( 'station_page' );
	if ( $page_id && ( '' != $page_id ) ) {
		$station_url = get_permalink( $page_id );
	}
	$station_url = apply_filters( 'radio_station_station_url', $station_url );

	return $station_url;
}

// ---------------------
// Get Station Image URL
// ---------------------
// 2.3.3.8: added get station logo image URL
function radio_station_get_station_image_url() {
	$station_image = '';
	$attachment_id = radio_station_get_setting( 'station_image' );
	$image = wp_get_attachment_image_src( $attachment_id, 'full' );
	if ( is_array( $image ) ) {
		$station_image = $image[0];
	}
	$station_image = apply_filters( 'radio_station_station_image_url', $station_image );

	return $station_image;
}

// ----------------------------
// Get Master Schedule Page URL
// ----------------------------
// 2.3.0: added get master schedule URL permalink
function radio_station_get_schedule_url() {
	$schedule_url = '';
	$page_id = radio_station_get_setting( 'schedule_page' );
	if ( $page_id && ( '' != $page_id ) ) {
		$schedule_url = get_permalink( $page_id );
	}
	$schedule_url = apply_filters( 'radio_station_schedule_url', $schedule_url );

	return $schedule_url;
}

// -------------------------
// Get Radio Station API URL
// -------------------------
function radio_station_get_api_url() {
	$routes = radio_station_get_setting( 'enable_data_routes' );
	$feeds = radio_station_get_setting( 'enable_data_feeds' );
	$rest_url = get_rest_url( null, '/' );
	$api_url = false;
	if ( ( 'yes' == $routes ) && !empty( $rest_url ) ) {
		$api_url = radio_station_get_route_url( '' );
	} elseif ( 'yes' == $feeds ) {
		$api_url = radio_station_get_feed_url( 'radio' );
	}
	$api_url = apply_filters( 'radio_station_api_url', $api_url );

	return $api_url;
}

// -------------
// Get Route URL
// -------------
function radio_station_get_route_url( $route ) {

	global $radio_station_routes;

	// --- maybe return cached route URL ---
	if ( isset( $radio_station_routes[$route] ) ) {
		return $radio_station_routes[$route];
	}

	/// --- get route URL ---
	$base = apply_filters( 'radio_station_route_slug_base', 'radio' );
	if ( '' != $route ) {
		$route = apply_filters( 'radio_station_route_slug_' . $route, $route );
	}
	if ( '' == $route ) {
		$path = '/' . $base . '/';
	} elseif ( !$route ) {
		return false;
	} else {
		$path = '/' . $base . '/' . $route . '/';
	}

	// --- cache route URL ---
	// echo "<!-- Route: " . $route . " - Path: " . $path . " -->";
	$radio_station_routes[$route] = $route_url = get_rest_url( null, $path );

	return $route_url;
}

// ------------
// Get Feed URL
// ------------
function radio_station_get_feed_url( $feedname ) {

	global $radio_station_feeds;

	// --- maybe return cached feed URL ---
	if ( isset( $radio_station_feeds[$feedname] ) ) {
		return $radio_station_feeds[$feedname];
	}

	// --- get feed URL ---
	$feedname = apply_filters( 'radio_station_feed_slug_' . $feedname, $feedname );
	if ( !$feedname ) {
		return false;
	}

	// --- cache feed URL ---
	$radio_station_feeds[$feedname] = $feed_url = get_feed_link( $feedname );

	return $feed_url;
}

// ----------------
// Get Show RSS URL
// ----------------
function radio_station_get_show_rss_url( $show_id ) {
	// TODO: combine comments and full show content
	$rss_url = get_post_comments_feed_link( $show_id );
	$rss_url = add_query_arg( 'withoutcomments', '1', $rss_url );

	return $rss_url;
}

// -------------------------
// Get DJ / Host Profile URL
// -------------------------
// 2.3.0: added to get DJ / Host profile permalink
function radio_station_get_host_url( $host_id ) {
	$post_id = radio_station_get_profile_id( RADIO_STATION_HOST_SLUG, $host_id );
	if ( $post_id ) {
		$host_url = get_permalink( $post_id );
	} else {
		$host_url = get_author_posts_url( $host_id );
	}
	$host_url = apply_filters( 'radio_station_host_url', $host_url, $host_id );

	return $host_url;
}

// ------------------------
// Get Producer Profile URL
// ------------------------
// 2.3.0: added to get Producer profile permalink
function radio_station_get_producer_url( $producer_id ) {
	$post_id = radio_station_get_profile_id( RADIO_STATION_PRODUCER_SLUG, $producer_id );
	if ( $post_id ) {
		$producer_url = get_permalink( $post_id );
	} else {
		$producer_url = get_author_posts_url( $producer_id );
	}
	$producer_url = apply_filters( 'radio_station_producer_url', $producer_url, $producer_id );

	return $producer_url;
}

// ---------------
// Get Upgrade URL
// ---------------
// 2.3.0: added to get Upgrade to Pro link
function radio_station_get_upgrade_url() {

	// TODO: test Freemius upgrade to Pro URL
	// ...maybe it is -addons instead of -pricing ???
	// $upgrade_url = add_query_arg( 'page', 'radio-station-pricing', admin_url( 'admin.php' ) );

	$upgrade_url = RADIO_STATION_PRO_URL;

	return $upgrade_url;
}

// ------------------------
// Patreon Supporter Button
// ------------------------
// 2.2.2: added simple patreon supporter image button
// 2.3.0: added Patreon page argument
// 2.3.0: moved from radio-station-admin.php
function radio_station_patreon_button( $page, $title = '' ) {
	$image_url = plugins_url( 'images/patreon-button.jpg', RADIO_STATION_FILE );
	$button = '<a href="https://patreon.com/' . esc_attr( $page ) . '" target="_blank" title="' . esc_attr( $title ) . '">';
	$button .= '<img id="radio-station-patreon-button" src="' . esc_url( $image_url ) . '" border="0">';
	$button .= '</a>';

	// 2.3.0: add button styling to footer
	if ( is_admin() ) {
		add_action( 'admin_footer', 'radio_station_patreon_button_styles' );
	} else {
		add_action( 'wp_footer', 'radio_station_patreon_button_styles' );
	}

	// --- filter and return ---
	$button = apply_filters( 'radio_station_patreon_button', $button, $page );
	return $button;
}

// ---------------------
// Patreon Button Styles
// ---------------------
// 2.3.0: added separately in footer
function radio_station_patreon_button_styles() {
	// 2.2.7: added button hover opacity
	echo '<style>#radio-station-patreon-button {opacity:0.9;}
	#radio-station-patreon-button:hover {opacity:1 !important;}</style>';
}

// --------------------
// Queue Directory Ping
// --------------------
// 2.3.2: queue directory ping on saving
function radio_station_queue_directory_ping() {
	$do_ping = radio_station_get_setting( 'ping_netmix_directory' );
	if ( 'yes' != $do_ping ) {return;}
	update_option( 'radio_station_ping_directory', '1' );
}

// -------------------
// Send Directory Ping
// -------------------
// 2.3.1: added directory ping function prototype
function radio_station_send_directory_ping() {
	$do_ping = radio_station_get_setting( 'ping_netmix_directory' );
	if ( 'yes' != $do_ping ) {return;}

	// --- set the URL to ping ---
	// 2.3.2: fix url_encode to urlencode
	$site_url = site_url();
	$url = add_query_arg( 'ping', 'directory', RADIO_STATION_NETMIX_DIR );
	$url = add_query_arg( 'station-url', urlencode( $site_url ), $url );
	$url = add_query_arg( 'timestamp', time(), $url );

	// --- send the ping ---
	$args = array( 'timeout' => 10 );
	if ( !function_exists( 'wp_remote_get' ) ) {
	 	include_once ABSPATH . WPINC . '/http.php';
	}
	$response = wp_remote_get( $url, $args );
	if ( isset( $_GET['rs-test-ping'] ) && ( '1' == $_GET['rs-test-ping'] ) ) {
		echo '<span style="display:none;">Directory Ping Response:</span>';
		echo '<textarea style="display:none; float:right; width:700px; height:200px;">';
		echo print_r( $response, true ) . '</textarea>';
	}
	return $response;
}

// -------------------
// Check and Send Ping
// -------------------
// 2.3.2: send queued directory ping
add_action( 'admin_footer', 'radio_station_check_directory_ping', 99 );
function radio_station_check_directory_ping() {
	$ping = get_option( 'radio_station_ping_directory' );
	if ( $ping ) {
		$response = radio_station_send_directory_ping();
		if ( !is_wp_error( $response ) && isset( $response['response']['code'] ) && ( 200 == $response['response']['code'] ) ) {
			delete_option( 'radio_station_ping_directory' );
		}
	} elseif ( isset( $_GET['rs-test-ping'] ) && ( '1' == $_GET['rs-test-ping'] ) ) {
		$response = radio_station_send_directory_ping();
	}
}

// ------------------------
// === Time Conversions ===
// ------------------------

// -------
// Get Now
// -------
// 2.3.2: added for current time consistency
function radio_station_get_now( $gmt = true ) {

	if ( defined( 'RADIO_STATION_USE_SERVER_TIMES' ) && RADIO_STATION_USE_SERVER_TIMES ) {
		$now = strtotime( current_time( 'mysql' ) );
	} else {
		$datetime = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$now = $datetime->format( 'U' );
	}

	return $now;
}

// ------------
// Get Timezone
// ------------
// 2.3.2: added get timezone with fallback
function radio_station_get_timezone() {

	$timezone = radio_station_get_setting( 'timezone_location' );
	if ( !$timezone || ( '' == $timezone ) ) {

		// --- fallback to WordPress timezone ---
		$timezone = get_option( 'timezone_string' );
		if ( false !== strpos( $timezone, 'Etc/GMT' ) ) {
			$timezone = '';
		}

	}

	if ( '' == $timezone ) {
		$offset = get_option( 'gmt_offset' );
		$timezone = 'UTC' . $offset;
	}

	return $timezone;
}

// -----------------
// Get Timezone Code
// -----------------
// note: this should only be used for display purposes
// (as the actual code used is based on timezone/location)
function radio_station_get_timezone_code( $timezone ) {
	$datetime = new DateTime( 'now', new DateTimeZone( $timezone ) );
	return $datetime->format( 'T' );
}

// --------------------
// Get Date Time Object
// --------------------
// 2.3.2: added for consistent timezone conversions
function radio_station_get_date_time( $timestring, $timezone ) {

	if ( 'UTC' == $timezone ) {
		$utc = new DateTimeZone( 'UTC' );
		$datetime = new DateTime( $timestring, $utc );
	} elseif ( strstr( $timezone, 'UTC' ) ) {
		$offset = str_replace( 'UTC', '', $timezone );
		$offset = (int)$offset * 60 * 60;
		$utc = new DateTimeZone( 'UTC' );
		$datetime = new DateTime( $timestring, $utc );
		$timestamp = $datetime->format( 'U' );
		$timestamp = $timestamp + $offset;
		$datetime->setTimestamp( $timestamp );
	} else {
		$datetime = new DateTime( $timestring, new DateTimeZone( $timezone ) );
		// ...fix to set timestamp again just in case
		// echo "A: " . print_r( $datetime, true ) . PHP_EOL;
		// if ( '@' == substr( $timestring, 0, 1 ) ) {
		//	$timestamp = substr( $timestring, 1, strlen( $timestring ) );
		//	$datetime->setTimestamp( $timestamp );
		// }
		// echo "B: " . print_r( $datetime, true ) . PHP_EOL;
	}

	return $datetime;
}

// --------------
// String To Time
// --------------
// 2.3.2: added for timezone handling
function radio_station_to_time( $timestring ) {

	if ( defined( 'RADIO_STATION_USE_SERVER_TIMES' ) && RADIO_STATION_USE_SERVER_TIMES ) {
	 	$time = strtotime( $timestring );
	} else {
		$timezone = radio_station_get_timezone();
		if ( strstr( $timezone, 'UTC' ) && ( 'UTC' != $timezone ) ) {
			// --- fallback for UTC offsets ---
			$offset = str_replace( 'UTC', '', $timezone );
			$offset = (int)$offset * 60 * 60;
			$utc = new DateTimeZone( 'UTC' );
			$datetime = new DateTime( $timestring, $utc );
			$timestamp = $datetime->getTimestamp();
			$timestamp = $timestamp - $offset;
			$datetime->setTimestamp( $timestamp );
		} else {
			$datetime = radio_station_get_date_time( $timestring, $timezone );
		}
		$time = $datetime->format( 'U' );

	}

	return $time;
}

// --------
// Get Time
// --------
// 2.3.2: added for timezone adjustments
function radio_station_get_time( $key = false, $time = false ) {

	// --- get offset time and date ---
	if ( defined( 'RADIO_STATION_USE_SERVER_TIMES' ) && RADIO_STATION_USE_SERVER_TIMES ) {

		if ( !$time ) {
			$time = radio_station_get_now();
		}
		$day = date( 'l', $time );
		$date = date( 'Y-m-d', $time );
		$date_time = date( 'Y-m-d H:i:s', $time );
		$timestamp = date( 'U', $time );

	} else {

		if ( !$time ) {
			$timestring = 'now';
		} else {
			$timestring = '@' . $time;
		}

		// --- get timezone ---
		$timezone = radio_station_get_timezone();
		if ( strstr( $timezone, 'UTC' ) ) {
			$datetime = radio_station_get_date_time( $timestring, $timezone );
		} else {
			// ...and refix for location timezones
			$datetime = new DateTime( $timestring, new DateTimeZone( 'UTC' ) );
			$datetime->setTimezone( new DateTimeZone( $timezone ) );
		}

		// --- set formatted strings ---
		$day = $datetime->format( 'l' );
		$date = $datetime->format( 'Y-m-d' );
		$date_time = $datetime->format( 'Y-m-d H:i:s' );
		$timestamp = $datetime->format( 'U' );

	}

	$times = array(
		'day'       => $day,
		'date'      => $date,
		'datetime'  => $date_time,
		'timestamp' => $timestamp,
	);

	if ( $key ) {
		if ( array_key_exists( $key, $times ) ) {
			$time = $times[$key];
		} elseif ( isset( $datetime ) ) {
			$time = $datetime->format( $key );
		} else {
			$time = date( $key, $time );
		}
	} elseif ( RADIO_STATION_DEBUG ) {
		echo '<span style="display:none;">Time key not found: ' . $key . PHP_EOL;
		echo 'Times: ' . print_r( $times, true ) . '</span>';
	}

	return $time;
}

// --------------------
// Get Timezone Options
// --------------------
// ref: (based on) https://stackoverflow.com/a/17355238/5240159
function radio_station_get_timezone_options( $include_wp_timezone = false ) {

	// --- maybe get stored timezone options ---
	$options = get_transient( 'radio-station-timezone-options' );
	if ( !$options ) {

		// --- set regions ---
		$regions = array(
			DateTimeZone::AFRICA     => __( 'Africa', 'radio-station' ),
			DateTimeZone::AMERICA    => __( 'America', 'radio-station' ),
			DateTimeZone::ASIA       => __( 'Asia', 'radio-station' ),
			DateTimeZone::ATLANTIC   => __( 'Atlantic', 'radio-station' ),
			DateTimeZone::AUSTRALIA  => __( 'Australia', 'radio-station' ),
			DateTimeZone::EUROPE     => __( 'Europe', 'radio-station' ),
			DateTimeZone::INDIAN     => __( 'Indian', 'radio-station' ),
			DateTimeZone::PACIFIC    => __( 'Pacific', 'radio-station' ),
			DateTimeZone::ANTARCTICA => __( 'Antarctica', 'radio-station' ),
		);

		// --- loop regions ---
		foreach ( $regions as $region => $label ) {

			// --- option group by region ---
			$options['*OPTGROUP*' . $region] = $label;

			$timezones = DateTimeZone::listIdentifiers( $region );
			$timezone_offsets = array();
			foreach ( $timezones as $timezone ) {
				$datetimezone = new DateTimeZone( $timezone );
				$offset = $datetimezone->getOffset( new DateTime() );
				$timezone_offsets[$offset][] = $timezone;
			}
			ksort( $timezone_offsets );

			foreach ( $timezone_offsets as $offset => $timezones ) {
				foreach ( $timezones as $timezone ) {
					$prefix = $offset < 0 ? '-' : '+';
					$hour = gmdate( 'H', abs( $offset ) );
					$hour = gmdate( 'H', abs( $offset ) );
					$minutes = gmdate( 'i', abs( $offset ) );
					$code = radio_station_get_timezone_code( $timezone );
					$label = $code . ' (GMT' . $prefix . $hour . ':' . $minutes . ') - ';
					$timezone_split = explode( '/', $timezone );
					unset( $timezone_split[0] );
					$timezone_joined = implode( '/', $timezone_split );
					$label .= str_replace( '_', ' ', $timezone_joined );
					$options[$timezone] = $label;
				}
			}
		}
		$expiry = 7 * 24 * 60 * 60;
		set_transient( 'radio-station-timezone-options', $options, $expiry );
	}

	// --- maybe add WordPress timezone (default) option ---
	if ( $include_wp_timezone ) {
		$wp_timezone = array( '' => __( 'WordPress Timezone', 'radio-station' ) );
		$options = array_merge( $wp_timezone, $options );
	}

	$options = apply_filters( 'radio_station_get_timezone_options', $options, $include_wp_timezone );

	return $options;
}

// --------------
// Get Weekday(s)
// --------------
// 2.3.2: added get weekday from number helper
function radio_station_get_weekday( $day_number = null ) {

	$weekdays = array(
		'Sunday',
		'Monday',
		'Tuesday',
		'Wednesday',
		'Thursday',
		'Friday',
		'Saturday',
	);

	if ( !is_null( $day_number ) ) {
		return $weekdays[$day_number];
	}
	return $weekdays;
}

// ------------
// Get Month(s)
// ------------
// 2.3.2: added get weekday from number helper
function radio_station_get_month( $month_number = null ) {

	$months = array(
		'January',
		'February',
		'March',
		'April',
		'May',
		'June',
		'July',
		'August',
		'September',
		'October',
		'November',
		'December',
	);
	if ( !is_null( $month_number ) ) {
		return $months[$month_number];
	}
	return $months;
}

// ---------------------
// Get Schedule Weekdays
// ---------------------
// note: no translations here because used internally for sorting
// 2.3.0: added to get schedule weekdays from start of week
function radio_station_get_schedule_weekdays( $weekstart = false ) {

	// --- maybe get start of the week ---
	if ( !$weekstart ) {
		$weekstart = get_option( 'start_of_week' );
		$weekstart = apply_filters( 'radio_station_schedule_weekday_start', $weekstart );
	}

	$weekdays = array(
		'Sunday',
		'Monday',
		'Tuesday',
		'Wednesday',
		'Thursday',
		'Friday',
		'Saturday',
	);

	// 2.3.2: also accept string format for weekstart
	if ( is_string( $weekstart ) ) {
		// 2.3.3.5: accept today as valid week start
		if ( 'today' == $weekstart ) {
			$weekstart = radio_station_get_time( 'day' );
		}
		foreach ( $weekdays as $i => $weekday ) {
			if ( strtolower( $weekday ) == strtolower( $weekstart ) ) {
				$weekstart = $i;
			}
		}
	}

	// --- loop weekdays and reorder from start day ---
	$start = $before = $after = array();
	foreach ( $weekdays as $i => $weekday ) {
		// 2.3.2: allow matching of numerical index or weekday name
		if ( ( $i == $weekstart ) || ( $weekday == $weekstart ) ) {
			$start[] = $weekday;
		} elseif ( $i > $weekstart ) {
			$after[] = $weekday;
		} elseif ( $i < $weekstart ) {
			$before[] = $weekday;
		}
	}

	// --- put the days before the start day at the end ---
	$weekdays = array_merge( $start, $after );
	$weekdays = array_merge( $weekdays, $before );

	return $weekdays;
}

// ----------------------
// Get Schedule Weekdates
// ----------------------
// 2.3.0: added for date based calculations
function radio_station_get_schedule_weekdates( $weekdays, $time = false ) {

	if ( !$time ) {
		$time = radio_station_get_now();
	}

	// 2.3.2: use timezone setting to get offset date
	if ( defined( 'RADIO_STATION_USE_SERVER_TIMES' ) && RADIO_STATION_USE_SERVER_TIMES ) {
		$today = date( 'l', $time );
	} else {
		$timezone = radio_station_get_timezone();
		$datetime = radio_station_get_date_time( '@' . $time, $timezone );
		$today = $datetime->format( 'l' );
	}

	// --- get weekday index for today ---
	$weekdates = array();
	foreach ( $weekdays as $i => $weekday ) {
		if ( $weekday == $today ) {
			$index = $i;
		}
	}
	foreach ( $weekdays as $i => $weekday ) {
		$diff = $index - $i;
		$weekdate_time = $time - ( $diff * 24 * 60 * 60 );
		// 2.3.2: include timezone adjustment
		if ( defined( 'RADIO_STATION_USE_SERVER_TIMES' ) && RADIO_STATION_USE_SERVER_TIMES ) {
			$weekdate = date( 'Y-m-d', $weekdate_time );
		} else {
			$weekdatetime = radio_station_get_date_time( '@' . $weekdate_time, $timezone );
			$weekdate = $weekdatetime->format( 'Y-m-d' );
		}
		$weekdates[$weekday] = $weekdate;
	}

	if ( RADIO_STATION_DEBUG ) {
		echo '<span style="display:none;">';
		echo 'Time: ' . $time . PHP_EOL;
		echo 'Today: ' . $today . PHP_EOL;
		echo 'Today Object: ' . print_r( $datetime, true );
		echo 'Weekdays: ' . print_r( $weekdays, true );
		echo 'Weekdates: ' . print_r( $weekdates, true );
		echo '</span>';
	}

	return $weekdates;
}

// ------------
// Get Next Day
// ------------
// 2.3.0: added get next day helper
function radio_station_get_next_day( $day ) {
	// note: for internal use so not translated
	$day = trim( $day );
	if ( 'Sunday' == $day ) {
		return 'Monday';
	}
	if ( 'Monday' == $day ) {
		return 'Tuesday';
	}
	if ( 'Tuesday' == $day ) {
		return 'Wednesday';
	}
	if ( 'Wednesday' == $day ) {
		return 'Thursday';
	}
	if ( 'Thursday' == $day ) {
		return 'Friday';
	}
	if ( 'Friday' == $day ) {
		return 'Saturday';
	}
	if ( 'Saturday' == $day ) {
		return 'Sunday';
	}

	return '';
}

// ----------------
// Get Previous Day
// ----------------
// 2.3.0: added get previous day helper
function radio_station_get_previous_day( $day ) {
	// note: for internal use so not translated
	$day = trim( $day );
	if ( 'Sunday' == $day ) {
		return 'Saturday';
	}
	if ( 'Monday' == $day ) {
		return 'Sunday';
	}
	if ( 'Tuesday' == $day ) {
		return 'Monday';
	}
	if ( 'Wednesday' == $day ) {
		return 'Tuesday';
	}
	if ( 'Thursday' == $day ) {
		return 'Wednesday';
	}
	if ( 'Friday' == $day ) {
		return 'Thursday';
	}
	if ( 'Saturday' == $day ) {
		return 'Friday';
	}

	return '';
}

// -------------
// Get Next Date
// -------------
// 2.3.2: added for more reliable calculations
function radio_station_get_next_date( $date, $weekday = false ) {

	// note: this is used internally so timezone not used
	$timedate = strtotime( $date );
	$timedate = $timedate + ( 24 * 60 * 60 );
	if ( $weekday ) {
		$day = date( 'l', $timedate );
		if ( $day != $weekday ) {
			$i = 0;
			while ( $day != $weekday ) {
				$timedate = $timedate + ( 24 * 60 * 60 );
				$day = strtotime( 'l', $timedate );
				if ( 8 == $i ) {
					// - failback for while failure -
					$timedate = strtotime( $date );
					$next_date = date( 'next ' . $weekday, $timedate );
					return $next_date;
				}
				$i++;
			}
		}
	}
	$next_date = date( 'Y-m-d', $timedate );
	return $next_date;
}

// -----------------
// Get Previous Date
// -----------------
// 2.3.2: added for more reliable calculations
function radio_station_get_previous_date( $date, $weekday = false ) {

	// note: this is used internally so timezone not used
	$timedate = strtotime( $date );
	$timedate = $timedate - ( 24 * 60 * 60 );
	if ( $weekday ) {
		$day = date( 'l', $timedate );
		if ( $day != $weekday ) {
			$i = 0;
			while ( $day != $weekday ) {
				$timedate = $timedate - ( 24 * 60 * 60 );
				$day = strtotime( 'l', $timedate );
				if ( 8 == $i ) {
					// - failback for while failure -
					$timedate = strtotime( $date );
					$previous_date = date( 'previous ' . $weekday, $timedate );
					return $previous_date;
				}
				$i++;
			}
		}
	}
	$previous_date = date( 'Y-m-d', $timedate );
	return $previous_date;
}

// -------------
// Get All Hours
// -------------
function radio_station_get_hours( $format = 24 ) {
	$hours = array();
	if ( 24 === (int) $format ) {
		$hours = array( 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23 );
	} elseif ( 12 === (int) $format ) {
		$hours = array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 );
	}
	return $hours;
}

// ---------------------------
// Convert Hour to Time Format
// ---------------------------
// (note: used with suffix for on-the-hour times)
// 2.3.0: standalone function via master-schedule-default.php
// 2.3.0: optionally add suffix for both time formats
function radio_station_convert_hour( $hour, $timeformat = 24, $suffix = true ) {

	$hour = intval( $hour );

	// 2.3.0: handle next and previous hours (over 24 or below 0)
	if ( $hour < 0 ) {
		while ( $hour < 0 ) {
			$hour = $hour + 24;
		}
	}
	if ( $hour > 24 ) {
		while ( $hour > 24 ) {
			$hour = $hour - 24;
		}
	}

	if ( 24 === (int) $timeformat ) {
		// --- 24 hour time format ---
		if ( 24 == $hour ) {
			$hour = '00';
		} elseif ( $hour < 10 ) {
			$hour = '0' . $hour;
		}
		if ( $suffix ) {
			$hour .= ':00';
		}
	} elseif ( 12 === (int) $timeformat ) {
		// --- 12 hour time format ---
		// 2.2.7: added meridiem translations
		if ( ( $hour === 0 ) || ( 24 === $hour ) ) {
			// midnight
			$hour = '12';
			if ( $suffix ) {
				$hour .= ' ' . radio_station_translate_meridiem( 'am' );
			}
		} elseif ( $hour < 12 ) {
			// morning
			if ( $suffix ) {
				$hour .= ' ' . radio_station_translate_meridiem( 'am' );
			}
		} elseif ( 12 === $hour ) {
			// noon
			if ( $suffix ) {
				$hour .= ' ' . radio_station_translate_meridiem( 'pm' );
			}
		} elseif ( $hour > 12 ) {
			// after-noon
			$hour = $hour - 12;
			if ( $suffix ) {
				$hour .= ' ' . radio_station_translate_meridiem( 'pm' );
			}
		}
	}
	// 2.3.2: fix for possible double spacing
	$hour = str_replace( '  ', ' ', $hour );

	return $hour;
}

// ----------------------------
// Convert Shift to Time Format
// ----------------------------
// 2.3.0: added to convert shift time to 24 hours (or back)
function radio_station_convert_shift_time( $time, $timeformat = 24 ) {

	// note: timezone can be ignored here as just getting hours and minutes
	$timestamp = strtotime( date( 'Y-m-d' ) . $time );
	if ( 12 == (int) $timeformat ) {
		$time = date( 'g:i a', $timestamp );
		str_replace( 'am', radio_station_translate_meridiem( 'am' ), $time );
		str_replace( 'pm', radio_station_translate_meridiem( 'pm' ), $time );
	} elseif ( 24 == (int) $timeformat ) {
		$time = date( 'H:i', $timestamp );
	}

	return $time;
}

// ------------------
// Convert Show Shift
// ------------------
// 2.3.0: 24 format shift for broadcast data endpoint
function radio_station_convert_show_shift( $shift ) {

	// note: timezone can be  ignored here as getting hours and minutes
	if ( isset( $shift['start'] ) ) {
		$shift['start'] = date( 'H:i', strtotime( $shift['start'] ) );
	}
	if ( isset( $shift['end'] ) ) {
		$shift['end'] = date( 'H:i', strtotime( $shift['end'] ) );
	}
	return $shift;
}

// -------------------
// Convert Show Shifts
// -------------------
// 2.3.0: 24 format shifts for show data endpoints
function radio_station_convert_show_shifts( $show ) {

	if ( isset( $show['schedule'] ) ) {
		$schedule = $show['schedule'];
		foreach ( $schedule as $i => $shift ) {
			// 2.3.3.9: fixed to not use radio_station_convert_hour
			$start_hour = substr( radio_station_convert_shift_time( $shift['start_hour'] . $shift['start_meridian'], 24 ), 0, 2 );
			$end_hour = substr( radio_station_convert_shift_time( $shift['end_hour'] . $shift['end_meridian'], 24 ), 0, 2 );
			// 2.3.3.9: added missing shift encore designation
			$encore = ( 'on' == $shift['encore'] ) ? true : false;
			$schedule[$i] = array(
				'day'	 => $shift['day'],
				'start'	 => $start_hour . ':' . $shift['start_min'],
				'end'	 => $end_hour . ':' . $shift['end_min'],
				'encore' => $encore,				
			);
		}
		$show['schedule'] = $schedule;
	}
	return $show;
}

// -----------------------
// Convert Schedule Shifts
// -----------------------
// 2.3.0: 24 format shifts for schedule data endpoint
function radio_station_convert_schedule_shifts( $schedule ) {

	if ( is_array( $schedule ) && ( count( $schedule ) > 0 ) ) {
		foreach ( $schedule as $day => $shows ) {
			$new_shows = array();
			if ( is_array( $shows ) && ( count( $shows ) > 0 ) ) {
				foreach ( $shows as $time => $show ) {
					$new_show = $show;
					$new_show['start'] = radio_station_convert_shift_time( $show['start'], 24 );
					$new_show['end'] = radio_station_convert_shift_time( $show['end'], 24 );
					$new_shows[] = $new_show;
				}
			}
			$schedule[$day] = $new_shows;
		}
	}
	return $schedule;
}


// ------------------------
// === Helper Functions ===
// ------------------------

// --------------------
// Encode URI Component
// --------------------
// 2.3.2: added PHP equivalent of javascript encodeURIComponent
// ref: https://stackoverflow.com/a/1734255/5240159
function radio_station_encode_uri_component( $string ) {
    $revert = array( '%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')' );
    return strtr( rawurlencode( $string ), $revert);
}

// --------------
// Get Profile ID
// --------------
// 2.3.0: added to get host or producer profile post ID
function radio_station_get_profile_id( $type, $user_id ) {

	global $radio_station_data;

	if ( isset( $radio_station_data[$type . '-' . $user_id] ) ) {
		$post_id = $radio_station_data[$type . '-' . $user_id];
		return $post_id;
	}

	// --- get the post ID(s) for the profile ---
	global $wpdb;
	$query = "SELECT post_id FROM " . $wpdb->prefix . "postmeta
			  WHERE meta_key = '" . $type . "_user_id' AND meta_value = %d";
	$query = $wpdb->prepare( $query, $user_id );
	$results = $wpdb->get_results( $query, ARRAY_A );

	// --- check for and return published profile ID ---
	if ( $results && is_array( $results ) && ( count( $results ) > 0 ) ) {
		foreach ( $results as $result ) {
			$query = "SELECT ID FROM " . $wpdb->prefix . "posts
					  WHERE post_status = 'publish' AND post_id = %d";
			$query = $wpdb->prepare( $query, $result['ID'] );
			$post_id = $wpdb->get_var( $query );
			if ( $post_id ) {
				$radio_station_data[$type . '-' . $user_id] = $post_id;

				return $post_id;
			}
		}
	}

	return false;
}

// -------------
// Get Languages
// -------------
function radio_station_get_languages() {

	// --- get all language translations ---
	$translations = get_site_transient( 'available_translations' );
	if ( ( false === $translations ) || is_wp_error( $translations )
	     || !isset( $translations['translations'] ) || empty( $translations['translations'] ) ) {
		// --- fallback to language selection data ---
		// (note this file is a minified from translations API result)
		// http://api.wordpress.org/translations/core/1.0/
		$language_file = RADIO_STATION_DIR . '/languages/languages.json';
		if ( file_exists( $language_file ) ) {
			$contents = file_get_contents( $language_file );
			$translations = json_decode( $contents, true );
		}
	}
	if ( isset( $translations['translations'] ) ) {
		$translations = $translations['translations'];
	}

	// --- merge in default language (en_US) ---
	if ( is_array( $translations ) && ( count( $translations ) > 0 ) ) {
		$trans_before = $trans_after = array();
		$found = false;
		foreach ( $translations as $i => $translation ) {
			if ( '' == $translation['language'] ) {
				$found = true;
			}
			if ( !$found ) {
				$trans_before[] = $translation;
			} else {
				$trans_after[] = $translation;
			}
		}
		$trans_before[] = array(
			'language'     => 'en_US',
			'native_name'  => 'English (United States)',
			'english_name' => 'English (United States)',
		);
		$translations = array_merge( $trans_before, $trans_after );
	}

	// --- filter and return ---
	$translations = apply_filters( 'radio_station_get_languages', $translations );

	return $translations;
}

// --------------------
// Get Language Options
// --------------------
function radio_station_get_language_options( $include_wp_default = false ) {

	// --- maybe get stored timezone options ---
	$languages = get_transient( 'radio-station-language-options' );
	if ( !$languages ) {
		$languages = array();
		$translations = radio_station_get_languages();
		if ( $translations && is_array( $translations ) && ( count( $translations ) > 0 ) ) {
			foreach ( $translations as $translation ) {
				$lang = $translation['language'];
				$languages[$lang] = $translation['native_name'];
			}
		}
		// set_transient( 'radio-station-language-options', $languages, 24 * 60 * 60 );
	}

	// --- maybe include WordPress default language ---
	if ( $include_wp_default ) {
		// 2.3.3.6: fix to array for WordPress language setting
		$wp_language = array( '' => __( 'WordPress Setting', 'radio-station' ) );
		$languages = array_merge( $wp_language, $languages );
	}

	// --- filter and return ---
	$languages = apply_filters( 'radio_station_get_language_options', $languages, $include_wp_default );

	return $languages;
}

// ------------
// Get Language
// ------------
function radio_station_get_language( $lang = false ) {

	// --- maybe get the main language ---
	$main = false;
	if ( !$lang ) {
		$main = true;
		$lang = radio_station_get_setting( 'radio_language' );
		// 2.3.3.6: add fallback for value of 1 due to language options bug
		if ( !$lang || ( '' == $lang ) || ( '0' == $lang ) || ( '1' == $lang ) ) {
			$lang = get_option( 'WPLANG' );
			if ( !$lang ) {
				$lang = 'en_US';
			}
		}
	}
	if ( isset( $_REQUEST['lang-debug'] ) && ( '1' == $_REQUEST['lang-debug'] ) ) {
		echo PHP_EOL . "LANG: " . print_r( $lang, true ) . PHP_EOL;
	}

	// --- get the specified language term ---
	// 2.3.3.8: explicitly check for numberic language term ID
	$id = absint( $lang );
	if ( $id < 1 ) {
		$term = get_term_by( 'slug', $lang, RADIO_STATION_LANGUAGES_SLUG );
		if ( !$term ) {
			$term = get_term_by( 'name', $lang, RADIO_STATION_LANGUAGES_SLUG );
		}
	} else {
		$term = get_term_by( 'id', $lang, RADIO_STATION_LANGUAGES_SLUG );
	}

	// --- set language from term ---
	if ( $term ) {
		$language = array(
			'id'          => $term->term_id,
			'slug'        => $term->slug,
			'name'        => $term->name,
			'description' => $term->description,
			'url'         => get_term_link( $term, RADIO_STATION_LANGUAGES_SLUG ),
		);
	} else {
		// --- set main language info ---
		if ( $main ) {
			$languages = radio_station_get_languages();
			foreach ( $languages as $i => $lang_data ) {
				if ( $lang_data['language'] == $lang ) {
					$language = array(
						'id'          => 0,
						'slug'        => $lang,
						'name'        => $lang_data['native_name'],
						'description' => $lang_data['english_name'],
						// TODO: set URL for main language and filter archive page results ?
						// 'url'      => '',
					);
				}
			}
		} else {
			$language = false;
		}
	}
	if ( isset( $_REQUEST['lang-debug'] ) && ( '1' == $_REQUEST['lang-debug'] ) ) {
		echo PHP_EOL . "LANGUAGE: " . print_r( $language, true ) . PHP_EOL;
	}

	return $language;
}

// ------------
// Trim Excerpt
// ------------
// (modified copy of wp_trim_excerpt)
// 2.3.0: added permalink argument
function radio_station_trim_excerpt( $content, $length = false, $more = false, $permalink = false ) {

	$excerpt = '';
	$raw_content = $content;

	// 2.3.2: added check for content
	if ( '' != trim( $content ) ) {

		$content = strip_shortcodes( $content );

		if ( function_exists( 'excerpt_remove_blocks' ) ) {
			$content = excerpt_remove_blocks( $content );
		}
		// TODO: check for Gutenberg plugin-only equivalent ?
		// elseif ( function_exists( 'gutenberg_remove_blocks' ) {
		//	$content = gutenberg_remove_blocks( $content );
		// }
		$content = apply_filters( 'the_content', $content );
		$content = str_replace( ']]>', ']]&gt;', $content );

		if ( !$length ) {
			$length = 35;
			$length = (int) apply_filters( 'radio_station_excerpt_length', $length );
		}
		if ( !$more ) {
			$more = ' [&hellip;]';
			// $more = apply_filters( 'excerpt_more', $more);
			$more = apply_filters( 'radio_station_excerpt_more', ' [&hellip;]' );
		}
		// 2.3.0: added link wrapper
		if ( $permalink ) {
			$more = ' <a href="'. esc_url( $permalink ) . '">' . $more . '</a>';
		}
		$excerpt = wp_trim_words( $content, $length, $more );
	}

	$excerpt = apply_filters( 'radio_station_trim_excerpt', $excerpt, $raw_content, $length, $more, $permalink );
	return $excerpt;
}

// ---------------
// Sanitize Values
// ---------------
function radio_station_sanitize_values( $data, $keys ) {
	$sanitized = array();
	foreach ( $keys as $key => $type ) {
		if ( isset( $data[$key] ) ) {
			if ( 'boolean' == $type ) {
				if ( ( 0 == $data[$key] ) || ( 1 == $data[$key] ) ) {
					$sanitized[$key] = $data[$key];
				}
			} elseif ( 'integer' == $type ) {
				$sanitized[$key] = absint( $data[$key] );
			} elseif ( 'alphanumeric' == $type ) {
				$value = preg_match( '/^[a-zA-Z0-9_]+$/', $data[$key] );
				if ( $value ) {
					$sanitized[$key] = $value;
				}
			} elseif ( 'text' == $type ) {
				$sanitized[$key] = sanitize_text_field( $data[$key] );
			} elseif ( 'slug' == $type ) {
				$sanitized[$key] = sanitize_title( $data[$key] );
			}
		}
	}
	return $sanitized;
}

// -------------------------
// Sanitize Shortcode Values
// -------------------------
// 2.3.2: added for AJAX widget loading
function radio_station_sanitize_shortcode_values( $type, $extras = false ) {

	$atts = array();

	if ( 'current-show' == $type ) {

		// --- current show attribute keys ---
		// 2.3.3: added for_time value
		$keys = array(
			'title'          => 'text',
			'limit'          => 'integer',
			'show_avatar'    => 'boolean',
			'show_link'      => 'boolean',
			'show_sched'     => 'boolean',
			'show_playlist'  => 'boolean',
			'show_all_sched' => 'boolean',
			'show_desc'      => 'boolean',
			'time'           => 'integer',
			'default_name'   => 'text',
			'display_hosts'  => 'boolean',
			'link_hosts'     => 'boolean',
			'avatar_width'   => 'integer',
			'title_position' => 'slug',
			'ajax'           => 'boolean',
			'countdown'      => 'boolean',
			'dynamic'        => 'boolean',
			'widget'         => 'boolean',
			'id'             => 'integer',
			'instance'       => 'integer',
			'for_time'       => 'integer',
		);

	} elseif ( 'upcoming-shows' == $type ) {

		// --- upcoming shows attribute keys ---
		// 2.3.3: added for_time value
		$keys = array(
			'title'          => 'text',
			'limit'          => 'integer',
			'show_avatar'    => 'boolean',
			'show_link'      => 'boolean',
			'time'           => 'integer',
			'show_sched'     => 'boolean',
			'default_name'   => 'string',
			'display_hosts'  => 'boolean',
			'link_hosts'     => 'boolean',
			'avatar_width'   => 'integer',
			'title_position' => 'slug',
			'ajax'           => 'boolean',
			'countdown'      => 'boolean',
			'dynamic'        => 'boolean',
			'widget'         => 'boolean',
			'id'             => 'integer',
			'instance'       => 'integer',
			'for_time'       => 'integer',
		);

	} elseif ( 'current-playlist' == $type ) {

		// --- current playlist attribute keys ---
		// 2.3.3: added for_time value
		$keys = array(
			'title'     => 'text',
			'artist'    => 'boolean',
			'song'      => 'boolean',
			'album'     => 'boolean',
			'label'     => 'boolean',
			'comments'  => 'boolean',
			'ajax'      => 'boolean',
			'countdown' => 'boolean',
			'dynamic'   => 'boolean',
			'widget'    => 'boolean',
			'id'        => 'integer',
			'instance'  => 'integer',
			'for_time'  => 'integer',
		);
	}

	// --- handle extra keys ---
	if ( $extras && is_array( $extras ) && ( count( $extras ) > 0 ) ) {
		$keys = array_merge( $keys, $extras );
	}

	// --- sanitize values by key type ---
	$atts = radio_station_sanitize_values( $_REQUEST, $keys );
	return $atts;
}

// --------------------------
// Delete Prefixed Transients
// --------------------------
// 2.3.4: added helper for clearing transient data
function radio_station_delete_transients_with_prefix( $prefix ) {
	global $wpdb;

	$prefix = $wpdb->esc_like( '_transient_' . $prefix );
	$sql = "SELECT `option_name` FROM $wpdb->options WHERE `option_name` LIKE '%s'";
	$results = $wpdb->get_results( $wpdb->prepare( $sql, $prefix . '%' ), ARRAY_A );
	if ( !$results || !is_array( $results ) || ( count( $results ) < 1 ) ) {
		return;
	}

	foreach ( $results as $option ) {
		$key = ltrim( $option['option_name'], '_transient_' );
		delete_transient( $key );
	}
}


// --------------------
// === Translations ===
// --------------------

// -----------------
// Translate Weekday
// -----------------
// important note: translated individually as cannot translate a variable
// 2.2.7: use wp locale class to translate weekdays
// 2.3.0: allow for abbreviated and long version changeovers
// 2.3.2: default short to null for more flexibility
function radio_station_translate_weekday( $weekday, $short = null ) {

	// 2.3.0: return empty for empty select option
	if ( empty( $weekday ) ) {
		return '';
	}

	global $wp_locale;

	$days = radio_station_get_weekday();

	// --- translate weekday ---
	// 2.3.2: optimized weekday translations
	foreach ( $days as $i => $day ) {
		$abbr = substr( $day, 0, 3 );
		if ( ( $weekday == $day ) || ( $weekday == $abbr ) ) {
			if ( ( !$short && !is_null( $short ) ) || ( is_null( $short ) && ( $weekday == $day ) ) ) {
				return $wp_locale->get_weekday( $i );
			} elseif ( $short || ( is_null( $short ) && ( weekday == $abbr ) ) ) {
				return $wp_locale->get_weekday_abbrev( $wp_locale->get_weekday( $i ) );
			}
		}
	}

	// --- fallback if day number supplied ---
	// 2.3.2: optimized day number fallback
	$daynum = intval( $weekday );
	if ( ( $daynum > -1 ) && ( $daynum < 7 ) ) {
		if ( $short ) {
			return $wp_locale->get_weekday_abbrev( $wp_locale->get_weekday( $daynum ) );
		} else {
			return $wp_locale->get_weekday( $daynum );
		}
	}

	return $weekday;
}

// ----------------
// Replace Weekdays
// ----------------
// 2.3.2: to replace with translated weekdays in a time string
function radio_station_replace_weekday( $string ) {

	$days = radio_station_get_weekday();
	foreach( $days as $day ) {
		$abbr = substr( $day, 0, 3 );
		if ( strstr( $string, $day ) ) {
			$translated = radio_station_translate_weekday( $day );
			$string = str_replace( $day, $translated, $string );
		} elseif ( strstr( $string, $abbr ) ) {
			$translated = radio_station_translate_weekday( $abbr );
			$string = str_replace( $abbr, $translated, $string );
		}
	}

	return $string;
}

// ---------------
// Translate Month
// ---------------
// important note: translated individually as cannot translate a variable
// 2.2.7: use wp locale class to translate months
// 2.3.2: default short to null for more flexibility
function radio_station_translate_month( $month, $short = null ) {

	// 2.3.0: return empty for empty select option
	if ( empty( $month ) ) {
		return '';
	}

	global $wp_locale;

	$months = radio_station_get_month();

	// --- translate month ---
	// 2.3.2: optimized month translations
	foreach ( $months as $i => $fullmonth ) {
		$abbr = substr( $fullmonth, 0, 3 );
		if ( ( $month == $fullmonth ) || ( $month == $abbr ) ) {
			if ( ( !$short && !is_null( $short ) )
			  || ( is_null( $short ) && ( $month == $fullmonth ) ) ) {
				return $wp_locale->get_month( ( $i + 1 ) );
			} elseif ( $short || ( is_null( $short ) && ( weekday == $abbr ) ) ) {
				return $wp_locale->get_month_abbrev( $wp_locale->get_month( ( $i + 1 ) ) );
			}
		}
	}

	// --- fallback if month number supplied ---
	// 2.3.2: optimized month number fallback
	$monthnum = intval( $month );
	if ( ( $monthnum > 0 ) && ( $monthnum < 13 ) ) {
		if ( $short ) {
			return $wp_locale->get_month_abbrev( $wp_locale->get_month( $monthnum ) );
		} else {
			return $wp_locale->get_month( $monthnum );
		}
	}

	return $month;
}

// --------------
// Replace Months
// --------------
// 2.3.2: to replace with translated months in a time string
function radio_station_replace_month( $string ) {

	$months = radio_station_get_month();
	foreach( $months as $month ) {
		$abbr = substr( $month, 0, 3 );
		if ( strstr( $string, $month ) ) {
			$translated = radio_station_translate_month( $month );
			$string = str_replace( $month, $translated, $string );
		} elseif ( strstr( $string, $abbr ) ) {
			$translated = radio_station_translate_month( $abbr );
			$string = str_replace( $abbr, $translated, $string );
		}
	}

	return $string;
}

// ------------------
// Translate Meridiem
// ------------------
// 2.2.7: added meridiem translation function
function radio_station_translate_meridiem( $meridiem ) {
	global $wp_locale;

	return $wp_locale->get_meridiem( $meridiem );
}

// ----------------
// Replace Meridiem
// ----------------
// 2.3.2: added optimized meridiem replacement
function radio_station_replace_meridiem( $string ) {

	global $radio_station_data;
	if ( isset( $radio_station_data['meridiems'] ) ) {
		$meridiems = $radio_station_data['meridiems'];
	} else {
		$meridiems = array(
			'am'	=> radio_station_translate_meridiem( 'am' ),
			'pm'	=> radio_station_translate_meridiem( 'pm' ),
			'AM'	=> radio_station_translate_meridiem( 'AM' ),
			'PM'	=> radio_station_translate_meridiem( 'PM' ),
		);
		$radio_station_data['meridiems'] = $meridiems;
	}


	if ( strstr( $string, 'am' ) ) {
		$string = str_replace( 'am', $meridiems['am'], $string );
	}
	if ( strstr( $string, 'pm' ) ) {
		$string = str_replace( 'pm', $meridiems['pm'], $string );
	}
	if ( strstr( $string, 'AM' ) ) {
		$string = str_replace( 'AM', $meridiems['AM'], $string );
	}
	if ( strstr( $string, 'PM' ) ) {
		$string = str_replace( 'PM', $meridiems['PM'], $string );
	}

	return $string;
}

// ---------------------
// Translate Time String
// ---------------------
// 2.3.2: replace with translated month, day and meridiem in a string
function radio_station_translate_time( $string ) {

	$string = radio_station_replace_meridiem( $string );
	$string = radio_station_replace_weekday( $string );
	$string = radio_station_replace_month( $string );

	return $string;
}
