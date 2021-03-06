<?php
/**
 * View: Week View - Single Event Recurring Icon
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/v2/week/grid-body/events-day/event/date/recurring.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @since 5.1.1
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 *
 * @version 5.1.1
 */

if ( empty( $event->recurring ) ) {
	return;
}
?>
<em
	class="tribe-events-pro-week-grid__event-datetime-recurring-icon tribe-common-svgicon tribe-common-svgicon--recurring"
	aria-label="<?php esc_attr_e( 'Recurring', 'tribe-events-calendar-pro' ); ?>"
	title="<?php esc_attr_e( 'Recurring', 'tribe-events-calendar-pro' ); ?>"
>
</em>
