<?php

declare(strict_types=1);

namespace Staatic\WordPress\Service;

use RuntimeException;

final class Scheduler
{
    public function getSchedules() : array
    {
        return \array_map(function ($schedule) {
            return [
                'label' => $schedule['display'],
                'interval' => $schedule['interval']
            ];
        }, \wp_get_schedules());
    }

    public function isScheduled(string $event) : bool
    {
        //!COMPAT: Introduced in WordPress 5.1.0 - replace with something else...
        // https://developer.wordpress.org/reference/functions/wp_get_scheduled_event/
        $nextEvent = Polyfill::wp_get_scheduled_event($event);

        return $nextEvent !== \false;
    }

    public function unschedule(string $event) : void
    {
        $nextEventTimestamp = \wp_next_scheduled($event);
        if (!\is_int($nextEventTimestamp)) {
            throw new RuntimeException("Unable to unschedule event '{$event}'; event does not exist");
        }
        $result = Polyfill::wp_unschedule_event($nextEventTimestamp, $event);
        if ($result !== \true) {
            throw new RuntimeException(
                "Unable to unschedule event '{$event}' scheduled at timestamp '{$nextEventTimestamp}'; unknown error"
            );
        }
    }

    public function schedule(string $event, string $schedule) : void
    {
        $result = Polyfill::wp_schedule_event(\time(), $schedule, $event, []);
        if ($result !== \true) {
            // TODO: maybe schedule does not exist? Add error message if available.
            throw new RuntimeException("Unable to schedule event '{$event}' with schedule '{$schedule}'");
        }
    }
}
