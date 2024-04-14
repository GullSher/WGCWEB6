<?php

declare(strict_types=1);

namespace Staatic\WordPress\Migrations;

interface MigrationInterface
{
    /**
     * @param \wpdb $wpdb
     */
    public function up($wpdb);

    /**
     * @param \wpdb $wpdb
     */
    public function down($wpdb);
}
