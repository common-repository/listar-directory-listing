<?php
namespace ListarWP\Plugin\Libraries;

class Notice  {

    /**
     * Error message
     * @var string
     */
    public static $message = '';

    /**
     * Admin notice
     * @param string $message
     * @param string $type
     * @param bool $transient
     */
    public static function admin_notice($message = '', $type = 'error', $transient = TRUE) {
        self::$message = $message;
        switch ($type) {
            case 'update':
                add_action( 'admin_notices', function() {
                    self:: admin_notice_update();
                });
                break;
            case 'reminder':
                add_action( 'admin_notices', function() {
                    self:: admin_notice_reminder();
                });
                break;
            default:
                add_action( 'admin_notices', function() {
                    self:: admin_notice_error();
                });
                break;
        }

    }

    public static function admin_notice_error() {
        ?>
        <div class="error notice">
            <p><?php echo self::$message;?></p>
        </div>
        <?php
    }

    public static function admin_notice_update() {
        ?>
        <div class="updated notice">
            <p><?php echo self::$message;?></p>
        </div>
        <?php
    }

    public static function admin_notice_reminder() {
        ?>
        <div class="update-nag notice">
            <p><?php echo self::$message;?></p>
        </div>
        <?php
    }
}
