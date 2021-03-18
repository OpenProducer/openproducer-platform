<?php

/*
 * Radio Station Data Endpoints
 * Author: Tony Hayes
 * @Since: 2.3.0
 */

// === API Discovery ===
// - Add Data API Header Link
// - Add Data API Discovery Link
// - Add Data API to RSD List
// === Data Functions ===
// - Get Station Data
// - Add Station Data
// - Get Broadcast Data
// - Get Shows Data
// - Get Genres Data
// === Data Endpoints ===
// - Station Data Endpoint
// - Broadcast Data Endpoint
// - Schedule Data Endpoint
// - Shows Data Endpoint
// - Genres Data Endpoint
// - Languages Data Endpoint
// === REST Routes ===
// - Register Rest Routes
// - Get Route URLs
// - Station Route
// - Current Broadcast Route
// - Show Schedule Route
// - Show List Route
// - Genre List Route
// - Language List Route
// === Feeds ===
// - Add Feeds
// - Add Feed Links to Data
// - Radio Endpoints Feed
// - Station Feed
// - Current Broadcast Feed
// - Show Schedule Feed
// - Show List Feed
// - Genre List Feed
// - Language List Feed
// - Not Found Feed Error
// - Format Data to XML
// - Convert Array to XML


// ---------------------
// === API Discovery ===
// ---------------------

// ------------------------
// Add Data API Header Link
// ------------------------
add_action( 'template_redirect', 'radio_station_api_link_header', 11, 0 );
function radio_station_api_link_header() {
	if ( headers_sent() ) {
		return;
	}
	$api_url = radio_station_get_api_url();
	$header = 'Link: <' . esc_url_raw( $api_url ) . '>; rel="' . RADIO_STATION_API_DOCS_URL . '"';
	$header = apply_filters( 'radio_station_api_discovery_header', $header );
	if ( $header ) {
		header( $header, false );
	}
}

// ---------------------------
// Add Data API Discovery Link
// ---------------------------
add_action( 'wp_head', 'radio_station_api_discovery_link' );
function radio_station_api_discovery_link() {
	$api_url = radio_station_get_api_url();
	$link = "<link rel='" . RADIO_STATION_API_DOCS_URL . "' href='" . esc_url( $api_url ) . "' />";
	$link = apply_filters( 'radio_station_api_discovery_link', $link );
	if ( $link ) {
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo $link;
	}
}

// ------------------------
// Add Data API to RSD List
// ------------------------
add_action( 'xmlrpc_rsd_apis', 'radio_station_api_discovery_rsd' );
function radio_station_api_discovery_rsd() {
	$api_url = radio_station_get_api_url();
	$link = '<api name="RadioStation" blogID="1" preferred="false" apiLink="' . esc_url( $api_url ) . '" />';
	$link = apply_filters( 'radio_station_api_discovery_rsd', $link );
	if ( $link ) {
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo $link;
	}
}


// ----------------------
// === Data Functions ===
// ----------------------

// --------------
// Add Query Vars
// --------------
// add_filter( 'query_vars', 'radio_station_add_feed_query_vars' );
function radio_station_add_feed_query_vars( $query_vars ) {

	// --- set feed query vars ---
	$vars = array( 'weekday', 'show', 'genre', 'language', 'format' );

	// --- add query vars ---
	foreach ( $vars as $var ) {
		if ( !in_array( $var, $query_vars ) ) {
	    	$query_vars[] = $var;
	    }
    }

    return $query_vars;
}

// ----------------
// Get Station Data
// ----------------
function radio_station_get_station_data() {

	// --- get radio timezone ---
	$timezone = radio_station_get_setting( 'timezone_location' );
	if ( !$timezone || ( '' == $timezone ) ) {
		$timezone = get_option( 'timezone_string' );
		if ( false !== strpos( $timezone, 'Etc/GMT' ) ) {
			$timezone = '';
		}
		if ( '' == $timezone ) {
			$timezone = 'UTC' . get_option( 'gmt_offset' );
		}
	}

	// --- get stream data ---
	// 2.3.3.9: enabled format and fallback data
	$stream_url = radio_station_get_stream_url();
	$stream_format = radio_station_get_setting( 'streaming_format' );
	$fallback_url = radio_station_get_fallback_url();
	$fallback_format = radio_station_get_setting( 'fallback_format' );

	// --- get station data ---
	$station_url = radio_station_get_station_url();
	$schedule_url = radio_station_get_schedule_url();
	$language = radio_station_get_language();

	// 2.3.2: use get date function with timezone
	$now = radio_station_get_now();
	$date_time = radio_station_get_time( 'datetime', $now );

	// 2.3.2: get schedule last updated time
	$updated = get_option( 'radio_station_schedule_updated' );
	if ( !$updated ) {
		$updated = time();
		update_option( 'radio_station_schedule_updated', $updated );
	}

	// --- set station data array ---
	// 2.3.2: added schedule updated timestamp
	// 2.3.3.9: enabled format and fallback data
	$station_data = array(
		'timezone'        => $timezone,
		'stream_url'      => $stream_url,
		'stream_format'   => $stream_format,
		'fallback_url'    => $fallback_url,
		'fallback_format' => $fallback_format,
		'station_url'     => $station_url,
		'schedule_url'    => $schedule_url,
		'language'        => $language['slug'],
		'timestamp'       => $now,
		'date_time'       => $date_time,
		'updated'         => $updated,
		'success'         => true,
	);
	$station_data = apply_filters( 'radio_station_station_data', $station_data );

	return $station_data;
}

// ----------------
// Add Station Data
// ----------------
function radio_station_add_station_data( $data ) {
	$station_data = radio_station_get_station_data();
	$data = array_merge( $data, $station_data );
	return $data;
}

// ------------------
// Get Broadcast Data
// ------------------
function radio_station_get_broadcast_data() {

	// --- get current show ---
	$current_show = radio_station_get_current_show();
	$current_show = radio_station_convert_show_shift( $current_show );

	// --- get next show ---
	$next_show = radio_station_get_next_show();
	$next_show = radio_station_convert_show_shift( $next_show );

	// 2.3.3.5: just in case transients are the same
	if ( $current_show == $next_show ) {
		$now = radio_station_get_time();
		$next_show = radio_station_get_next_show( $now );
		$next_show = radio_station_convert_show_shift( $next_show );
	}

	// 2.3.3.5: added current playlist to broadcast data
	$current_playlist = radio_station_get_current_playlist();

	// --- return broadcast info ---
	$broadcast = array(
		'current_show'     => $current_show,
		'next_show'        => $next_show,
		'current_playlist' => $current_playlist,
	);
	$broadcast = apply_filters( 'radio_station_broadcast_data', $broadcast );

	return $broadcast;
}

// --------------
// Get Shows Data
// --------------
function radio_station_get_shows_data( $show = false ) {

	$shows = array();
	if ( $show ) {
		// 2.3.3.8: add show description if show specified
		// 2.3.3.8: explicitly check if show is an ID or show name
		if ( strstr( $show, ',' ) ) {
			$show_ids = explode( ',', $show );
			foreach ( $show_ids as $show ) {
				$id = absint( $show );
				if ( $id < 1 ) {
					$show = sanitize_title( $show );
				} else {
					$show = $id;
				}
				$show = radio_station_get_show( $show );
				$show = radio_station_get_show_data_meta( $show, true );
				$show = radio_station_convert_show_shifts( $show );
				// $show = radio_station_get_show_description( $show );
				$shows[] = $show;
			}
		} else {
			$id = absint( $show );
			if ( $id < 1 ) {
				$show = sanitize_title( $show );
			} else {
				$show = $id;
			}
			$show = radio_station_get_show( $show );
			$show = radio_station_get_show_data_meta( $show, true );
			$show = radio_station_convert_show_shifts( $show );
			// $show = radio_station_get_show_description( $show );
			$shows = array( $show );
		}
	} else {
		$shows = radio_station_get_shows();
		if ( count( $shows ) > 0 ) {
			foreach ( $shows as $i => $show ) {
				$show = radio_station_get_show_data_meta( $show );
				$show = radio_station_convert_show_shifts( $show );
				$shows[$i] = $show;
			}
		}
	}
	// 2.3.3.8: add show querystring parameter as second filter argument
	$shows = apply_filters( 'radio_station_shows_data', $shows, $show );

	return $shows;
}

// ---------------
// Get Genres Data
// ---------------
function radio_station_get_genres_data( $genre = false ) {

	// -- get genre or genres ---
	// 2.3.3.8: removed sanitize_title usage for genre terms
	$genres = array();
	if ( $genre ) {
		if ( strstr( $genre, ',' ) ) {
			$genre_ids = explode( ',', $genre );
			foreach ( $genre_ids as $genre ) {
				// $genre = sanitize_title( $genre );
				$genre = radio_station_get_genre( $genre );
				$genres[] = $genre;
			}
		} else {
			// $genre = sanitize_title( $genre );
			$genres = radio_station_get_genre( $genre );
		}
	} else {
		$genres = radio_station_get_genres();
	}

	// --- loop genres to get shows ---
	if ( count( $genres ) > 0 ) {
		foreach ( $genres as $name => $genre ) {
			$shows = radio_station_get_genre_shows( $genre['slug'] );
			$genres[$name]['shows'] = array();
			$genres[$name]['show_count'] = 0;
			if ( is_object( $shows ) && property_exists( $shows, 'posts' )
			     && is_array( $shows->posts ) && ( count( $shows->posts ) > 0 ) ) {
				$genres[$name]['show_count'] = count( $shows->posts );
				foreach ( $shows->posts as $show ) {
					$show = radio_station_get_show_data_meta( $show );
					$show = radio_station_convert_show_shifts( $show );
					$genres[$name]['shows'][] = $show;
				}
			}
		}
	}
	// 2.3.3.8: add genre querystring parameter as second filter argument
	$genres = apply_filters( 'radio_station_genres_data', $genres, $genre );

	return $genres;
}

// ------------------
// Get Languages Data
// ------------------
function radio_station_get_languages_data( $language = false ) {

	// -- get language or languages ---
	// 2.3.3.8: removed sanitize_title usage for language terms
	$languages_data = array();
	if ( $language ) {
		if ( strstr( $language, ',' ) ) {
			$language_ids = explode( ',', $language );
			foreach ( $language_ids as $language ) {
				// $language = sanitize_title( $language );
				$language_data = radio_station_get_language( $language );
				if ( $language_data ) {
					$languages_data[$language] = $language_data;
				}
			}
		} else {
			// $language = sanitize_title( $language );
			$language_data = radio_station_get_language( $language );
			$languages_data[$language] = $language_data;
		}
	} else {
		// --- get main site language ---
		$main_language = radio_station_get_language();
		$languages_data = array( $main_language['slug'] => $main_language );

		// --- get all assigned language terms ---
		$args = array( 'taxonomy' => RADIO_STATION_LANGUAGES_SLUG, 'hide_empty' => true );
		$terms = get_terms( $args );

		if ( count( $terms ) > 0 ) {
			$all_langs = radio_station_get_languages();
			foreach ( $terms as $term ) {
				$languages_data[$term->slug] = array(
					'id'          => $term->term_id,
					'slug'        => $term->slug,
					'name'        => $term->name,
					'description' => $term->description,
					'url'         => get_term_link( $term->term_id, RADIO_STATION_LANGUAGES_SLUG ),
				);
			}
		}
	}

	// --- loop languages to get shows ---
	if ( count( $languages_data ) > 0 ) {
		// 2.3.3.8: fix show assignment to variable languages_data
		foreach ( $languages_data as $slug => $lang ) {

			// --- get shows for this language slug ---
			$shows = radio_station_get_language_shows( $slug );
			$languages_data[$slug]['shows'] = array();
			$languages_data[$slug]['show_count'] = 0;
			if ( is_object( $shows ) && property_exists( $shows, 'posts' )
			     && is_array( $shows->posts ) && ( count( $shows->posts ) > 0 ) ) {
				$languages_data[$slug]['show_count'] = count( $shows->posts );
				foreach ( $shows->posts as $show ) {
					$show = radio_station_get_show_data_meta( $show );
					$show = radio_station_convert_show_shifts( $show );
					$languages_data[$slug]['shows'][] = $show;
				}
			}

			// --- maybe get shows for main language slug ---
			// 2.3.3.8: fix to add shows for main language
			if ( 0 == $lang['id'] ) {
				$shows = radio_station_get_language_shows( false );
				if ( is_object( $shows ) && property_exists( $shows, 'posts' )
					 && is_array( $shows->posts ) && ( count( $shows->posts ) > 0 ) ) {
					$languages_data[$slug]['show_count'] = $languages_data[$slug]['show_count'] + count( $shows->posts );
					foreach ( $shows->posts as $show ) {
						$show = radio_station_get_show_data_meta( $show );
						$show = radio_station_convert_show_shifts( $show );
						$languages_data[$slug]['shows'][] = $show;
					}
				}
			}
		}
	}

	$languages_data = apply_filters( 'radio_station_languages_data', $languages_data, $language );

	return $languages_data;
}

// ----------------------
// === Data Endpoints ===
// ----------------------

// ---------------------
// Station Data Endpoint
// ---------------------
function radio_station_station_endpoint() {

	if ( RADIO_STATION_DEBUG ) {
		header( 'Content-Type: text/plain' );
	}

	$station = array();
	
	// --- broadcast ---
	$broadcast = radio_station_get_broadcast_data();
	$station['broadcast'] = $broadcast;
	
	// --- schedule ---
	$schedule = radio_station_get_current_schedule();
	$schedule = radio_station_convert_schedule_shifts( $schedule );
	if ( count( $schedule ) > 0 ) {
		$station['schedule'] = $schedule;
	} else {
		$station['schedule'] = array();
	}
	
	// --- shows ---
	$shows = radio_station_get_shows_data();
	if ( count( $shows ) > 0 ) {
		$station['shows'] = $shows;
	} else {
		$station['shows'] = array();
	}
	
	// --- genres ---
	$genres = radio_station_get_genres_data();
	if ( count( $genres ) > 0 ) {
		$station['genres'] = $genres;
	} else {
		$station['genres'] = array();
	}

	// --- languages ---
	$languages = radio_station_get_languages_data();
	if ( count( $languages ) > 0 ) {
		$station['languages'] = $languages;
	} else {
		$station['languages'] = array();
	}

	return $station;
}

// -----------------------
// Broadcast Data Endpoint
// -----------------------
function radio_station_broadcast_endpoint() {

	if ( RADIO_STATION_DEBUG ) {
		header( 'Content-Type: text/plain' );
	}

	$broadcast = radio_station_get_broadcast_data();
	if ( RADIO_STATION_DEBUG ) {
		echo "Broadcast: " . print_r( $broadcast, true );
	}
	return $broadcast;
}

// ----------------------
// Schedule Data Endpoint
// ----------------------
function radio_station_schedule_endpoint() {

	if ( RADIO_STATION_DEBUG ) {
		header( 'Content-Type: text/plain' );
	}

	// --- get current schedule ---
	$schedule = radio_station_get_current_schedule();
	$schedule = radio_station_convert_schedule_shifts( $schedule );

	// --- check for weekday query ---
	$weekdays = array();
	$weekday = $singular = $multiple = false;
	if ( isset( $_GET['weekday'] ) ) {

		$weekday = $_GET['weekday'];

		if ( strstr( $_GET['weekday'], ',' ) ) {
			$multiple = true;
			$weekdays = explode( ',', $weekday );
		} else {
			$singular = true;
			$weekdays = array( $weekday );
		}

		// --- remove all shifts not on specified weekdays ---
		foreach ( $weekdays as $i => $day ) {
			$weekdays[$i] = strtolower( trim( $day ) );
		}
		$shiftcount = 0;
		if ( count( $schedule ) > 0 ) {
			foreach ( $schedule as $day => $shifts ) {
				if ( !in_array( strtolower( $day ), $weekdays ) ) {
					unset( $schedule[$day] );
				} else {
					$shiftcount = $shiftcount + count( $shifts );
				}
			}
		}
		
		// --- set no shifts error ---
		if ( ( 0 == count( $schedule ) ) || ( 0 == $shiftcount ) ) {
			$code = 'no_scheduled_shifts';
			$message = 'No Show shifts were found for the days specified.';
			$error = new WP_Error( $code, $message, array( 'status' => 400 ) );
		}

	} elseif ( isset( $_GET['date'] ) ) {

		// TODO: get schedule for specific date ?

	} else {
	
		// --- check there are any shifts ---
		$shiftcount = 0;
		if ( count( $schedule ) > 0 ) {
			foreach ( $schedule as $day => $shifts ) {
				$shiftcount = $shiftcount + count( $shifts );
			}
		}
		
		// --- set no shifts error ---
		if ( ( 0 == count( $schedule ) ) || ( 0 == $shiftcount ) ) {
			$code = 'no_schedule';
			$message = 'No Show shifts were found in the Schedule.';
			$error = new WP_Error( $code, $message, array( 'status' => 400 ) );
		}
	
	}
	
	// --- maybe set request error ---
	if ( isset( $error ) ) {
		$schedule = $error;
	}

	// --- maybe output debug info ---
	if ( RADIO_STATION_DEBUG ) {
		echo "Weekday: " . $weekday . PHP_EOL;
		echo "Weekdays: " . print_r( $weekdays, true ) . PHP_EOL;
		echo "Schedule: " . print_r( $schedule, true );
	}

	return $schedule;
}

// -------------------
// Shows Data Endpoint
// -------------------
function radio_station_shows_endpoint() {

	if ( RADIO_STATION_DEBUG ) {
		header( 'Content-Type: text/plain' );
	}

	// --- get show query parameter ---
	$show = $singular = $multiple = false;
	if ( isset( $_GET['show'] ) ) {
		$show = $_GET['show'];
		if ( strstr( $show, ',' ) ) {
			$multiple = true;
		} else {
			$singular = true;
		}
	}

	// --- get show list data ---
	$shows = radio_station_get_shows_data( $show );

	// --- maybe set request error ---
	if ( 0 === count( $shows ) ) {
		if ( $singular ) {
			$code = 'show_not_found';
			$message = 'Requested Show was not found.';
		} elseif ( $multiple ) {
			$code = 'shows_not_found';
			$message = 'No Requested Shows were found.';
		} else {
			$code = 'no_shows';
			$message = 'No Shows were found.';
		}
		$shows = new WP_Error( $code, $message, array( 'status' => 400 ) );
	}

	// --- maybe output debug info ---
	if ( RADIO_STATION_DEBUG ) {
		echo "Show: " . $show . PHP_EOL;
		echo "Shows: " . print_r( $shows, true );
	}
	
	return $shows;
}

// --------------------
// Genres Data Endpoint
// --------------------
function radio_station_genres_endpoint() {

	if ( RADIO_STATION_DEBUG ) {
		header( 'Content-Type: text/plain' );
	}

	// --- get genre query parameter ---
	$genre = $singular = $multiple = false;
	if ( isset( $_GET['genre'] ) ) {
		$genre = $_GET['genre'];
		if ( strstr( $genre, ',' ) ) {
			$multiple = true;
		} else {
			$singular = true;
		}
	}

	// --- get genre list data ---
	$genres = radio_station_get_genres_data( $genre );

	// --- maybe set request error ---
	if ( 0 === count( $genres ) ) {
		if ( $singular ) {
			$code = 'genre_not_found';
			$message = 'Requested Genre was not found.';
		} elseif ( $multiple ) {
			$code = 'genres_not_found';
			$message = 'No Requested Genres were found.';
		} else {
			$code = 'no_genres';
			$message = 'No Genres were found.';
		}
		$genres = new WP_Error( $code, $message, array( 'status' => 400 ) );
	}

	// --- maybe output debug info ---
	if ( RADIO_STATION_DEBUG ) {
		echo "Genre: " . $genre . PHP_EOL;
		echo "Genres: " . print_r( $genres, true );
	}
	
	return $genres;
}

// -----------------------
// Languages Data Endpoint
// -----------------------
function radio_station_languages_endpoint() {

	if ( RADIO_STATION_DEBUG ) {
		header( 'Content-Type: text/plain' );
	}

	// --- get language query parameter ---
	$language = $singular = $multiple = false;
	if ( isset( $_GET['language'] ) ) {
		$language = $_GET['language'];
		if ( strstr( $language, ',' ) ) {
			$multiple = true;
		} else {
			$singular = true;
		}
	}

	// --- get language list data ---
	$languages = radio_station_get_languages_data( $language );
	if ( RADIO_STATION_DEBUG ) {
		echo "Language: " . $language . PHP_EOL;
		echo "Languages: " . print_r( $languages, true );
	}

	// --- maybe return route error ---
	if ( 0 === count( $languages ) ) {
		if ( $singular ) {
			$code = 'language_not_found';
			$message = 'Requested Language was not found.';
		} elseif ( $multiple ) {
			$code = 'languages_not_found';
			$message = 'No Requested Languages were found.';
		} else {
			$code = 'no_languages';
			$message = 'No Languages were found.';
		}
		$languages = new WP_Error( $code, $message, array( 'status' => 400 ) );
	}
	
	return $languages;
}


// -------------------
// === REST Routes ===
// -------------------

// --------------------
// Register Rest Routes
// --------------------
add_action( 'rest_api_init', 'radio_station_register_rest_routes' );
function radio_station_register_rest_routes() {

	// --- check rest routes are enabled ---
	$enabled = radio_station_get_setting( 'enable_data_routes' );
	if ( 'yes' != $enabled ) {
		return;
	}

	// --- filter route slugs ---
	// (can disable individual routes by returning false from filters)
	$base = apply_filters( 'radio_station_route_slug_base', 'radio' );
	$station = apply_filters( 'radio_station_route_slug_station', 'station' );
	$broadcast = apply_filters( 'radio_station_route_slug_broadcast', 'broadcast' );
	$schedule = apply_filters( 'radio_station_route_slug_schedule', 'schedule' );
	$shows = apply_filters( 'radio_station_route_slug_shows', 'shows' );
	$genres = apply_filters( 'radio_station_route_slug_genres', 'genres' );
	$languages = apply_filters( 'radio_station_route_slug_languages', 'languages' );

	// --- set request method ---
	$args = array( 'methods' => 'GET' );

	// --- Station Route ---
	// default URL: /wp-json/radio/station/
	if ( $station ) {
		$args['callback'] = 'radio_station_route_station';
		register_rest_route( $base, '/' . $station . '/', $args );
	}

	// --- Show Broadcast Route ---
	// default URL: /wp-json/radio/broadcast/
	if ( $broadcast ) {
		$args['callback'] = 'radio_station_route_broadcast';
		register_rest_route( $base, '/' . $broadcast . '/', $args );
	}

	// --- Master Schedule Route ---
	// default URL: /wp-json/radio/schedule/
	// (?P<weekday>\d+)
	if ( $schedule ) {
		$args['callback'] = 'radio_station_route_schedule';
		// TODO: maybe add endpoint parameters (eg. weekday for schedule) ?
		// ref: https://stackoverflow.com/q/53126137/5240159
		// $args['args'] => array(
		//	'weekday' => array(
		//		'validate_callback' => function($param, $request, $key) {
		//			return is_numeric( $param );
		//		}
		//	),
		// ),
		register_rest_route( $base, '/' . $schedule . '/', $args );
		// unset( $args['args'] );
	}

	// --- Show List Route ---
	// default URL: /wp-json/radio/shows/
	$args['callback'] = 'radio_station_route_shows';
	if ( $shows ) {
		register_rest_route( $base, '/' . $shows . '/', $args );
	}

	// --- Show Genre List Route ---
	// default URL: /wp-json/radio/genres/
	$args['callback'] = 'radio_station_route_genres';
	if ( $genres ) {
		register_rest_route( $base, '/' . $genres . '/', $args );
	}

	// --- Language List Route ---
	// default URL: /wp-json/radio/languages/
	$args['callback'] = 'radio_station_route_languages';
	if ( $languages ) {
		register_rest_route( $base, '/' . $languages . '/', $args );
	}

}

// --------------
// Get Route URLs
// --------------
function radio_station_get_route_urls() {

	// --- get and add route links ---
	$routes = array();
	$station = radio_station_get_route_url( 'station' );
	if ( $station ) {
		$routes['station'] = $station;
	}
	$broadcast = radio_station_get_route_url( 'broadcast' );
	if ( $broadcast ) {
		$routes['broadcast'] = $broadcast;
	}
	$schedule = radio_station_get_route_url( 'schedule' );
	if ( $schedule ) {
		$routes['schedule'] = $schedule;
	}
	$shows = radio_station_get_route_url( 'shows' );
	if ( $shows ) {
		$routes['shows'] = $shows;
	}
	$genres = radio_station_get_route_url( 'genres' );
	if ( $genres ) {
		$routes['genres'] = $genres;
	}
	$languages = radio_station_get_route_url( 'languages' );
	if ( $languages ) {
		$routes['languages'] = $languages;
	}

	// --- maybe get and add pro route links ---
	$routes = apply_filters( 'radio_station_route_urls', $routes );

	return $routes;
}

// ------------------
// Radio Route Filter
// ------------------
// note: handled different to other routes as this is the base /radio route
add_filter( 'rest_request_after_callbacks', 'radio_station_route_radio', 11, 3 );
function radio_station_route_radio( $response, $handler, $request ) {

	if ( !is_wp_error( $response ) ) {
		$base = apply_filters( 'radio_station_route_slug_base', 'radio' );
		$route = $request->get_route();
		if ( '/' . $base == $route ) {
			$data = $response->data;
			$date['success'] = true;
			$data['endpoints'] = radio_station_get_route_urls();
			$response->data = $data;
		}
	}

	return $response;
}

// -------------
// Station Route
// -------------
// (combined data from all routes)
function radio_station_route_station( $request ) {

	// --- get station endpoint data ---
	$station = radio_station_station_endpoint();
	$station = radio_station_add_station_data( $station );
	$station['endpoints'] = radio_station_get_route_urls();
	$station = apply_filters( 'radio_station_route_station', $station, $request );

	// --- maybe output debug display ---
	if ( RADIO_STATION_DEBUG ) {
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo "Output: " . print_r( $station, true ) . PHP_EOL;
		exit;
	}

	return $station;
}

// -----------------------
// Current Broadcast Route
// -----------------------
function radio_station_route_broadcast( $request ) {

	// --- get broadcast endpoint data ---
	$broadcast = radio_station_broadcast_endpoint();
	$broadcast = array( 'broadcast' => $broadcast );
	$broadcast = radio_station_add_station_data( $broadcast );
	$broadcast['endpoints'] = radio_station_get_route_urls();
	$broadcast = apply_filters( 'radio_station_route_broadcast', $broadcast, $request );

	// --- maybe output debug display ---
	if ( RADIO_STATION_DEBUG ) {
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo "Output: " . print_r( $broadcast, true ) . PHP_EOL;
		exit;
	}

	return $broadcast;
}

// -------------------
// Show Schedule Route
// -------------------
function radio_station_route_schedule( $request ) {

	// --- get schedule endpoint data ---
	$schedule = radio_station_schedule_endpoint();
	$schedule = array( 'schedule' => $schedule );
	$schedule = radio_station_add_station_data( $schedule );
	$schedule['endpoints'] = radio_station_get_route_urls();
	$schedule = apply_filters( 'radio_station_route_schedule', $schedule, $request );

	// --- maybe output debug display ---
	if ( RADIO_STATION_DEBUG ) {
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo "Output: " . print_r( $schedule, true ) . PHP_EOL;
		exit;
	}
	
	return $schedule;
}

// ---------------
// Show List Route
// ---------------
function radio_station_route_shows( $request ) {

	// --- get shows endpoint data ---
	$show_list = radio_station_shows_endpoint();
	if ( !is_wp_error( $show_list ) ) {
		$show_list = array( 'shows' => $show_list );
		$show_list = radio_station_add_station_data( $show_list );
		$show_list['endpoints'] = radio_station_get_route_urls();
	}
	$show_list = apply_filters( 'radio_station_route_shows', $show_list, $request );

	// --- maybe output debug display ---
	if ( RADIO_STATION_DEBUG ) {
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo "Output: " . print_r( $show_list, true ) . PHP_EOL;
		exit;
	}

	return $show_list;
}

// ----------------
// Genre List Route
// ----------------
function radio_station_route_genres( $request ) {

	// --- return genre list ---
	$genre_list = radio_station_genres_endpoint();
	if ( !is_wp_error( $genre_list ) ) {
		$genre_list = array( 'genres' => $genre_list );
		$genre_list = radio_station_add_station_data( $genre_list );
		$genre_list['endpoints'] = radio_station_get_route_urls();
	}
	$genre_list = apply_filters( 'radio_station_route_genres', $genre_list, $request );

	// --- maybe output debug display ---
	if ( RADIO_STATION_DEBUG ) {
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo "Output: " . print_r( $genre_list, true ) . PHP_EOL;
		exit;
	}
	
	return $genre_list;
}

// -------------------
// Language List Route
// -------------------
function radio_station_route_languages( $request ) {

	// --- return language list ---
	$language_list = radio_station_languages_endpoint();
	if ( !is_wp_error( $language_list ) ) {
		$language_list = array( 'languages' => $language_list );
		$language_list = radio_station_add_station_data( $language_list );
		$language_list['endpoints'] = radio_station_get_route_urls();
	}
	$language_list = apply_filters( 'radio_station_route_languages', $language_list, $request );

	// --- maybe output debug display ---
	if ( RADIO_STATION_DEBUG ) {
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo "Output: " . print_r( $language_list, true ) . PHP_EOL;
		exit;
	}
	
	return $language_list;
}


// =============
// --- Feeds ---
// =============

// --------
// Add Feed
// --------
// (modified version of WordPress add_feed function)
function radio_station_add_feed( $feedname, $function ) {

	// note: removed as this is overwriting normal page slugs...
	// so /feed/schedule/ overwrites /schedule/ - which is no good!
	// global $wp_rewrite;
	// if ( ! in_array( $feedname, $wp_rewrite->feeds ) ) {
	//     $wp_rewrite->feeds[] = $feedname;
	// }

	$hook = 'do_feed_' . $feedname;
	remove_action( $hook, $hook );
	add_action( $hook, $function, 10, 2 );

	return $hook;
}

// ---------
// Add Feeds
// ---------
add_action( 'init', 'radio_station_add_feeds', 11 );
function radio_station_add_feeds() {

	// --- check feeds are enabled ---
	$enabled = radio_station_get_setting( 'enable_data_feeds' );
	if ( 'yes' != $enabled ) {
		return;
	}

	// --- filter feed slugs ---
	$base = apply_filters( 'radio_station_feed_slug_base', 'radio' );
	$station = apply_filters( 'radio_station_feed_slug_station', 'station' );
	$broadcast = apply_filters( 'radio_station_feed_slug_broadcast', 'broadcast' );
	$schedule = apply_filters( 'radio_station_feed_slug_schedule', 'schedule' );
	$shows = apply_filters( 'radio_station_feed_slug_shows', 'shows' );
	$genres = apply_filters( 'radio_station_feed_slug_genres', 'genres' );
	$languages = apply_filters( 'radio_station_feed_slug_languages', 'languages' );

	// --- add feeds ---
	if ( $base ) {
		radio_station_add_feed( $base, 'radio_station_feed_radio' );
	}
	if ( $station ) {
		radio_station_add_feed( $station, 'radio_station_feed_station' );
	}
	if ( $broadcast ) {
		radio_station_add_feed( $broadcast, 'radio_station_feed_broadcast' );
	}
	if ( $schedule ) {
		radio_station_add_feed( $schedule, 'radio_station_feed_schedule' );
	}
	if ( $shows ) {
		radio_station_add_feed( $shows, 'radio_station_feed_shows' );
	}
	if ( $genres ) {
		radio_station_add_feed( $genres, 'radio_station_feed_genres' );
	}
	if ( $languages ) {
		radio_station_add_feed( $languages, 'radio_station_feed_languages' );
	}

	// --- add single feed rewrite rule ---
	// (without risking overriding standard permalink slugs)
	// https://wordpress.stackexchange.com/questions/351576/add-feed-rewrite-overwriting-standard-permalinks/351603#351603
	$feeds = array( $base, $station, $broadcast, $schedule, $shows, $genres, $languages );
	$feeds = apply_filters( 'radio_station_feed_slugs', $feeds );
	foreach ( $feeds as $i => $feed ) {
		if ( !$feed ) {
			unset( $feeds[$i] );
		}
	}
	$feedstring = implode( '|', $feeds );
	$baserule = '^feed/' . $base . '/?$';
	$feedrule = '^feed/' . $base . '/(' . $feedstring . ')/?$';
	add_rewrite_rule( $baserule, 'index.php?feed=' . $base, 'top' );
	add_rewrite_rule( $feedrule, 'index.php?feed=$matches[1]', 'top' );

	// --- check if feeds are registered ---
	// 2.3.1: add check for empty rewrite rules
	$rewrite_rules = get_option( 'rewrite_rules' );
	// note this should provide an array not a string
	if ( !$rewrite_rules || !is_array( $rewrite_rules )
	  || !array_key_exists( $baserule, $rewrite_rules )
	  || !array_key_exists( $feedrule, $rewrite_rules ) ) {
		flush_rewrite_rules( false );
	}

}

// -------------
// Get Feed URLs
// -------------
function radio_station_get_feed_urls() {

	// --- get all feed URLs ---
	$feeds = array();
	$station = radio_station_get_feed_url( 'station' );
	if ( $station ) {
		$feeds['station'] = $station;
	}
	$broadcast = radio_station_get_feed_url( 'broadcast' );
	if ( $broadcast ) {
		$feeds['broadcast'] = $broadcast;
	}
	$schedule = radio_station_get_feed_url( 'schedule' );
	if ( $schedule ) {
		$feeds['schedule'] = $schedule;
	}
	$shows = radio_station_get_feed_url( 'shows' );
	if ( $shows ) {
		$feeds['shows'] = $shows;
	}
	$genres = radio_station_get_feed_url( 'genres' );
	if ( $genres ) {
		$feeds['genres'] = $genres;
	}
	$languages = radio_station_get_feed_url( 'languages' );
	if ( $languages ) {
		$feeds['languages'] = $languages;
	}

	// --- filter and return ---
	$feeds = apply_filters( 'radio_station_feed_urls', $feeds );

	return $feeds;
}

// --------------------
// Radio Endpoints Feed
// --------------------
function radio_station_feed_radio( $comment_feed, $feed_name ) {

	$base = apply_filters( 'radio_station_feed_slug_base', 'radio' );
	$radio = array( 'success' => true );
	$radio['namespace'] = $base;
	$radio['endpoints'] = radio_station_get_feed_urls();

	// --- reflect route format used in REST API ---
	$routes = array();
	foreach ( $radio['endpoints'] as $endpoint => $url ) {
		$key = '/' . $base . '/' . $endpoint;
		$routes[$key] = array(
			'namespace'	=> $base,
			'methods'	=> array ( 'GET' ),
			// 'endpoints'	=> array(),
			// 'url'	=> $url,
			'_links' => array(
				'self' => $url,
			),
		);
	}
	$radio['routes'] = $routes;
	$radio = apply_filters( 'radio_station_feed_radio', $radio );

	// --- output debug display or feed data ---
	if ( RADIO_STATION_DEBUG ) {
		header( 'Content-Type: text/plain' );
		echo "Output: " . print_r( $radio, true ) . PHP_EOL;
	} else {
		header( 'Content-Type: application/json' );
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo json_encode( $radio );
	}
}

// ------------
// Station Feed
// ------------
// (combined data from all feeds)
function radio_station_feed_station( $comment_feed, $feed_name ) {

	// --- get station endpoint data ---
	$station = radio_station_station_endpoint();
	$station = radio_station_add_station_data( $station );
	$station['endpoints'] = radio_station_get_feed_urls();
	$station = apply_filters( 'radio_station_feed_station', $station );

	// --- output debug display or feed data ---
	if ( RADIO_STATION_DEBUG ) {
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo "Output: " . print_r( $station, true ) . PHP_EOL;
	} else {
		header( 'Content-Type: application/json' );
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo json_encode( $station );
	}
}

// ----------------------
// Current Broadcast Feed
// ----------------------
function radio_station_feed_broadcast( $comment_feed, $feed_name ) {

	// --- get broadcast endpoint data ---
	// 2.3.3.8: fix to broadcast array nesting
	$broadcast = radio_station_broadcast_endpoint();
	$broadcast = array( 'broadcast' => $broadcast );
	$broadcast = radio_station_add_station_data( $broadcast );
	$broadcast['endpoints'] = radio_station_get_feed_urls();
	$broadcast = apply_filters( 'radio_station_feed_broadcast', $broadcast );

	// --- output debug display or feed data ---
	if ( RADIO_STATION_DEBUG ) {
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo "Output: " . print_r( $broadcast, true ) . PHP_EOL;
	} else {
		header( 'Content-Type: application/json' );
		echo json_encode( $broadcast );
	}
}

// ------------------
// Show Schedule Feed
// ------------------
function radio_station_feed_schedule( $comment_feed, $feed_name ) {

	// --- get schedule endpoint data ---
	$schedule = radio_station_schedule_endpoint();
	$schedule = array( 'schedule' => $schedule );
	$schedule = radio_station_add_station_data( $schedule );
	$schedule['endpoints'] = radio_station_get_feed_urls();
	$schedule = apply_filters( 'radio_station_feed_schedule', $schedule );

	// --- output debug display or feed data ---
	if ( RADIO_STATION_DEBUG ) {
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo "Output: " . print_r( $schedule, true ) . PHP_EOL;
	} else {
		header( 'Content-Type: application/json' );
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo json_encode( $schedule );
	}
}

// --------------
// Show List Feed
// --------------
function radio_station_feed_shows( $comment_feed, $feed_name ) {

	// --- get shows data endpoint ---
	$show_list = radio_station_shows_endpoint();
	if ( is_wp_error( $show_list ) ) {
		$show_list = radio_station_feed_not_found( $show_list );
	} else {
		$show_list = array( 'shows' => $show_list );
		$show_list = radio_station_add_station_data( $show_list );
		$show_list['endpoints'] = radio_station_get_feed_urls();
	}
	$show_list = apply_filters( 'radio_station_feed_shows', $show_list );

	// --- output debug display or feed data ---
	if ( RADIO_STATION_DEBUG ) {
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo "Output: " . print_r( $show_list, true ) . PHP_EOL;
	} else {
		header( 'Content-Type: application/json' );
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo json_encode( $show_list );
	}
}

// ---------------
// Genre List Feed
// ---------------
function radio_station_feed_genres( $comment_feed, $feed_name ) {

	// --- get genres data endpoint ---
	$genre_list = radio_station_genres_endpoint();
	if ( is_wp_error( $genre_list ) ) {
		$genre_list = radio_station_feed_not_found( $genre_list );
	} else {
		$genre_list = array( 'genres' => $genre_list );
		$genre_list = radio_station_add_station_data( $genre_list );
		$genre_list['endpoints'] = radio_station_get_feed_urls();
	}
	$genre_list = apply_filters( 'radio_station_feed_genres', $genre_list );

	// --- output debug display or feed data ---
	if ( RADIO_STATION_DEBUG ) {
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo "Output: " . print_r( $genre_list, true ) . PHP_EOL;
	} else {
		header( 'Content-Type: application/json' );
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo json_encode( $genre_list );
	}
	
}

// -------------------
// Languages List Feed
// -------------------
function radio_station_feed_languages( $comment_feed, $feed_name ) {

	$language_list = radio_station_languages_endpoint();
	if ( is_wp_error( $language_list ) ) {
		$language_list = radio_station_feed_not_found( $language_list );
	} else {
		$language_list = array( 'languages' => $language_list );
		$language_list = radio_station_add_station_data( $language_list );
		$language_list['endpoints'] = radio_station_get_feed_urls();
	}
	$language_list = apply_filters( 'radio_station_feed_languages', $language_list );

	// --- output debug display or feed data ---
	if ( RADIO_STATION_DEBUG ) {
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo "Output: " . print_r( $language_list, true ) . PHP_EOL;
	} else {
		header( 'Content-Type: application/json' );
		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo json_encode( $language_list );
	}
}


// --------------------
// Not Found Feed Error
// --------------------
// 2.3.3.8: reformat WP Error instead of using message only
function radio_station_feed_not_found( $error ) {

	if ( is_wp_error( $error ) ) {
		$code = $error->get_error_code();
		$message = $error->get_error_message();
	} else {
		$code = str_replace( ' ', '-', strtolower( $error ) );
		$message = $error;
	}

	// 2.3.3.8: change status code to 400 bad request from 404 not found
	$error = array(
		'success' => false,
		'errors'  => array(
			'status'  => 400,
			'code'    => $code,
			'title'   => __( 'Error 400 No Requested Data', 'radio-station' ),
			'message' => __( 'The requested data could not be found.', 'radio-station' ),
			'detail'  => $message,
		),
	);

	return $error;
}


