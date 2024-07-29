<?php

/**
 * This file is used to optimize the plugin
 */

class Simple_Content_Scraper_Optimizer
{
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Create Action Schedule for cleaning
     */
    public function simco_cleaning_schedules()
    {
        // Timestamp for the next day 12:00
        $next_day_schedule = strtotime('tomorrow 12:00');

        // Get 1 day in seconds
        $one_day_interval = 60 * 60 * 24;

        if (false === as_has_scheduled_action('simco_clean_old_action_scheduler_records')) {
            as_schedule_recurring_action($next_day_schedule, $one_day_interval, 'simco_clean_old_action_scheduler_records');
        }
    }

    /**
     * Clean old action scheduler records
     */
    public function simco_clean_old_action_scheduler_records()
    {
        global $wpdb;

        // Calculate the timestamp for actions older than two weeks
        $two_weeks_ago = time();

        // Delete actions older than two weeks
        $wpdb->query("
            DELETE actions, logs
            FROM {$wpdb->prefix}actionscheduler_actions AS actions
            INNER JOIN {$wpdb->prefix}actionscheduler_logs AS logs ON actions.action_id = logs.action_id
            WHERE actions.scheduled_date_gmt < DATE_SUB(NOW(), INTERVAL 14 DAY) AND actions.status = 'complete';
        ");
    }
}
