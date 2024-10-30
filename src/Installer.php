<?php
namespace ListarWP\Plugin;

class Installer {

    /**
     * Create tables
     */
    public static function create_tables()
    {
        self::create_booking_tables();
    }

    /**
     * Create new booking tables
     * @return bool
     */
    public static function create_booking_tables()
    {
        global $wpdb;

        // set the default character set and collation for the table
        $charset_collate = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Booking items
        $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}listar_booking_items` (
            `booking_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `booking_item_name` text COLLATE {$wpdb->collate} NOT NULL,
            `booking_item_type` varchar(200) COLLATE {$wpdb->collate} NOT NULL DEFAULT '',
            `booking_id` bigint(20) unsigned NOT NULL,
            PRIMARY KEY (`booking_item_id`) USING BTREE,
            KEY `order_id` (`booking_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 $charset_collate;";

        dbDelta( $sql );

        // Booking item meta
        $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}listar_booking_itemmeta` (
            `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `booking_item_id` bigint(20) unsigned NOT NULL,
            `meta_key` varchar(255) COLLATE {$wpdb->collate} DEFAULT NULL,
            `meta_value` longtext COLLATE {$wpdb->collate} DEFAULT NULL,
            PRIMARY KEY (`meta_id`),
            KEY `order_item_id` (`booking_item_id`),
            KEY `meta_key` (`meta_key`(32))
        ) ENGINE=InnoDB AUTO_INCREMENT=1 $charset_collate;";

        dbDelta( $sql );

        // OTP 
        $sql = "CREATE TABLE `{$wpdb->base_prefix}listar_otp` (
            `id` int(20) NOT NULL AUTO_INCREMENT,
            `user_email` varchar(100) NOT NULL,
            `user_id` int(11) NOT NULL DEFAULT 0,
            `code` varchar(10) NOT NULL,
            `expired` enum('1','0') NOT NULL DEFAULT '0',
            `created_on` datetime NOT NULL,
            `ip` varchar(45) DEFAULT NULL,
            `expired_on` datetime NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 $charset_collate;";     

        dbDelta( $sql );

        // Notificaiton
        $sql = "CREATE TABLE `{$wpdb->base_prefix}listar_notifications` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `title` varchar(500) DEFAULT NULL,
            `content` text DEFAULT NULL,
            `meta_data` text DEFAULT NULL,
            `read` enum('1','0') DEFAULT '0',
            `email_use` enum('0','1') DEFAULT '1',
            `email_content_type` varchar(255) DEFAULT NULL,
            `email_sent` enum('0','1') DEFAULT '0',
            `email_cc` varchar(500) DEFAULT NULL,
            `email_to` varchar(320) DEFAULT NULL,
            `email_sent_on` datetime DEFAULT NULL,
            `email_error` varchar(500) DEFAULT NULL,
            `message` varchar(500) DEFAULT NULL,
            `mobile_use` enum('0','1') DEFAULT '1',
            `mobile_sent` enum('0','1') DEFAULT '0',
            `mobile_token` text DEFAULT NULL,
            `mobile_sent_on` datetime DEFAULT NULL,
            `mobile_error` varchar(500) DEFAULT NULL,
            `created_on` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `notification_schedule` (`user_id`,`email_use`,`email_sent`,`mobile_use`,`mobile_sent`,`read`) USING BTREE
        ) ENGINE=InnoDB AUTO_INCREMENT=1 $charset_collate;";

        dbDelta( $sql );    

        // Job Queue
        $sql = "CREATE TABLE `{$wpdb->base_prefix}listar_jobs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `type` enum('curl','cmd') NOT NULL DEFAULT 'cmd',
            `task` varchar(512) NOT NULL COMMENT 'URL or command',
            `payload` text DEFAULT NULL,
            `response` text DEFAULT NULL,
            `status` enum('queued','running','failed','done') NOT NULL DEFAULT 'queued',
            `run_time` float(7,4) DEFAULT NULL,
            `run_usage` float(7,4) DEFAULT NULL,
            `created_on` datetime DEFAULT NULL,
            `updated_on` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 $charset_collate;";

        dbDelta( $sql ); 

        $is_error = empty( $wpdb->last_error );
        
        return $is_error;
    }
}
