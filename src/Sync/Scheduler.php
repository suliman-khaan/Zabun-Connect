<?php

namespace ZabunConnect\Sync;

use ZabunConnect\Api\ZabunException;

defined( 'ABSPATH' ) || exit;

class Scheduler {

    /**
     * Cron hook name.
     */
    public const CRON_HOOK = 'zabun_connect_cron_sync';

    /**
     * Singleton instance.
     *
     * @var Scheduler|null
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * @return Scheduler
     */
    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize scheduler hooks.
     */
    public function init(): void {
        add_action( self::CRON_HOOK, [ $this, 'run_cron_sync' ] );
        add_action( 'update_option_zabun_connect_sync_interval', [ $this, 'on_interval_updated' ], 10, 2 );

        // Ensure event is scheduled if missing
        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            $this->schedule_event();
        }
    }

    /**
     * Schedule the background synchronization event.
     */
    public function schedule_event(): void {
        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            $interval = (string) get_option( 'zabun_connect_sync_interval', 'hourly' );
            wp_schedule_event( time(), $interval, self::CRON_HOOK );
        }
    }

    /**
     * Clear all scheduled synchronization events.
     */
    public static function clear_events(): void {
        $timestamp = wp_next_scheduled( self::CRON_HOOK );
        while ( $timestamp ) {
            wp_unschedule_event( $timestamp, self::CRON_HOOK );
            $timestamp = wp_next_scheduled( self::CRON_HOOK );
        }
        wp_clear_scheduled_hook( self::CRON_HOOK );
    }

    /**
     * Reschedule cron when interval setting is updated in admin.
     *
     * @param mixed $old_value
     * @param mixed $new_value
     */
    public function on_interval_updated( $old_value, $new_value ): void {
        if ( $old_value !== $new_value ) {
            self::clear_events();
            $this->schedule_event();
        }
    }

    /**
     * Cron execution callback.
     */
    public function run_cron_sync(): void {
        try {
            SyncListings::instance()->sync();
        } catch ( ZabunException $e ) {
            error_log( sprintf( '[Zabun Connect] Cron sync failed: %s', $e->getMessage() ) );
        } catch ( \Throwable $e ) {
            error_log( sprintf( '[Zabun Connect] Cron sync unexpected error: %s', $e->getMessage() ) );
        }
    }
}
