<?php
namespace ListarWP\Plugin\Controllers;
use ListarWP\Plugin\Listar;
use WP_Post;
use Exception;

class Admin_Banner_Controller
{
    /**
     * Post type support
     * @var string
     */
    protected $post_type = '';

    /**
     * Meta keys
     * @var array
     */
    protected $meta_keys = [];

    public function __construct()
    {
        $this->post_type = Listar::$post_type . '_banner';
        $this->meta_keys = ['ads_url'];

        // Customize post metadata
        add_action('add_meta_boxes', [$this, 'add']);

        // Handler when save post data (Fires once a post has been saved)
        add_action('save_post_listar_banner', [$this, 'save'], 10, 3);
    }

    /**
     * Add metadata box
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.26
     */
    public function add()
    {
        add_meta_box(
            'listar_banner_meta_box', // Unique ID
            __('Ads Information', 'listar'), // Box title
            [$this, 'form'], // Content callback, must be of type callable
            [$this->post_type], // Post type
            'advanced',
            'high'
        );
    }

    /**
     * Render value & html
     *
     * @param WP_Post $post
     * @author Paul <paul.passionui@gmail.com>
     * @throws Exception
     * @since 1.0.25
     */
    public function form($post) {
        $listar = Listar::get_instance();

        if(is_object($post) && $post->ID) { // Edit case
            try {
                foreach ($this->meta_keys as $key) {
                    $post->{$key} = get_post_meta($post->ID, $key, TRUE);
                }
            } catch (Exception $e) {
                error_log($e->getMessage());
                $data = [];
            }
        }

        include_once $listar->plugin_path() . '/views/banner/form.php';
    }

    /**
     * Update > Exist Data
     * @param int $post_id
     * @param WP_Post $post
     * @param bool $update
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.24
     */
    public function save($post_id, $post, $update)
    {
        // Prevent auto save
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Filter again post status
        if($post->post_type === $this->post_type) {
            foreach($this->meta_keys as $key) {
                if(isset($_POST[$key])) {
                    update_post_meta($post_id, $key, sanitize_text_field($_POST[$key]));
                }
            }
        }
    }
}
