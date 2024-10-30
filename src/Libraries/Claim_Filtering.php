<?php
namespace ListarWP\Plugin\Libraries;
use ListarWP\Plugin\Models\Setting_Model;
use ListarWP\Plugin\Models\Claim_Model;
use ListarWP\Plugin\Listar;
use WP_REST_Request;
use WP_Error;
use WP_Post;
use WP_Query;

class Claim_Filtering {
    /**
     * @var WP_REST_Request
     */
    protected $request;

    /**
     * Page number
     * @var int
     */
    public $paged;

    /**
     * Item per pages
     * @var int
     */
    public $per_page;

    /**
     * Query user's listing
     * @var int
     */
    public $user_id;

    /**
     * Query author's listing
     * @var int
     */
    public $author_id;

    /**
     * max page
     * @var int
     */
    public $max_pages;

    /**
     * Total items
     * @var int
     */
    public $total_posts;

    /**
     * Listar_Filtering constructor.
     * @param WP_REST_Request $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Set author id
     * @param int $author_id
     */
    public function set_author_id($author_id = 0)
    {
        $this->author_id = $author_id;
    }

    /**
     * Set user id
     * @param int $user_id
     */
    public function set_user_id($user_id = 0)
    {
        $this->user_id = $user_id;
    }

    /**
     * Get result query
     * @return array|int[]|WP_Error|WP_Post[]
     */
    public function get_result()
    {
        $this->per_page = isset($this->request['per_page']) ? absint($this->request['per_page'])
            : absint(Setting_Model::get_option('per_page'));
        $this->paged = isset($this->request['page']) ? absint($this->request['page']) : 1;

        $status = array_keys(listar_claim_status());
        if(isset($this->request['post_status']) && in_array($this->request['post_status'], $status)) {
            $status = [$this->request['post_status']];
        }

        $args = [
            'post_status' => $status,
            'post_type' => Claim_Model::post_type(),
            'paged' =>  $this->paged,
            'posts_per_page' => $this->per_page
        ];

        // Keyword
        if(isset($this->request['s']) && $this->request['s'] != '') {
            $args['s'] = sanitize_text_field($this->request['s']);
        }

        // Sort
        if(isset($this->request['orderby']) && $this->request['orderby'] != ''
            && isset($this->request['order']) && $this->request['order'] != '') {
            $args['orderby'] = sanitize_sql_orderby($this->request['orderby']);
            $args['order'] = sanitize_text_field($this->request['order']);
        }

        // User ID
        if(isset($this->user_id) && absint($this->user_id) > 0) {
            if(is_array($this->user_id) && !empty($this->user_id)) {
                $args['author__in'] = $this->user_id;
            } else{
                $args['author'] = absint($this->user_id);
            }
        }

        // Query by author (Query by owner)
        if($this->author_id && $this->author_id > 0) {
            $args['meta_query'][] = [
                'key' => '_author',
                'value' => $this->author_id,
                'type' => 'numeric',
                'compare' => '=',
            ];
        }

        $query = new WP_Query($args);
        $posts = $query->get_posts();
        $this->total_posts= absint($query->found_posts);

        if ( $this->total_posts < 1 ) {
            // Out-of-bounds, run the query again without LIMIT for total count.
            unset( $args['paged'] );
            $count_query = new WP_Query();
            $count_query->query( $args );
            $this->total_posts = absint($count_query->found_posts);
        }

        $this->max_pages = ceil( $this->total_posts / absint($query->query_vars['posts_per_page']) );

        if ( $this->paged > $this->max_pages && $this->total_posts > 0 ) {
            return new WP_Error( 'listar_invalid_page_number',
                __( 'The page number requested is larger than the number of pages available.', 'listar' ),
                [
                    'status' => 400
                ]);
        }

        return $posts;
    }

    /**
     * Pagination props
     * @return array
     */
    public function get_pagination()
    {
        return [
            'page' => $this->paged,
            'per_page' => $this->per_page,
            'max_page' => $this->max_pages,
            'total' => $this->total_posts,
        ];
    }
}
