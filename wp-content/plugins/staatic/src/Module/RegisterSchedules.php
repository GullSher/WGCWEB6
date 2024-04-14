<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module;

final class RegisterSchedules implements ModuleInterface
{
    public function hooks() : void
    {
        \add_filter('cron_schedules', [$this, 'registerMaintenanceSchedule']);
    }

    public function registerMaintenanceSchedule(array $schedules) : array
    {
        $schedules['staatic_maintenance_cron_interval'] = [
            'interval' => 43200,
            'display' => \esc_html__('Twice Daily')
        ];

        return $schedules;
    }

    public static function getDefaultPriority() : int
    {
        return 40;
    }
}
