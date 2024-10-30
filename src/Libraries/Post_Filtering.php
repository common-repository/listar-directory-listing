<?php
namespace ListarWP\Plugin\Libraries;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Models\Setting_Model;
use WP_REST_Request;
use WP_Query;
use WP_Error;
use WP_Post;

class Post_Filtering {
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
     * post status
     * @var string
     */
    public $post_status;

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
     * Count post author
     * @param int $user_id
     */
    public static function author_count($user_id = 0, $status = 'publish')
    {
        $query = new WP_Query([
            'post_type' => Listar::$post_type,
            'post_status'  => $status,
            'author' => $user_id
        ]);
        return absint($query->found_posts);
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
        $this->post_status = isset($this->request['post_status']) ? $this->request['post_status'] : '';

        $args = [
            'post_type'         => Listar::$post_type,
            'post_status'       => !$this->post_status ? 'publish' : $this->post_status,
            'paged'             =>  $this->paged,
            'posts_per_page'    => $this->per_page
        ];

        // Keyword
        if(isset($this->request['s']) && $this->request['s'] != '') {
            $search_term = sanitize_text_field($this->request['s']);


            /**
             * @since 1.0.23
             * - Search address
             */
            if($location = get_term_by( 'name', $search_term, Listar::$post_type.'_location' )) {
                $args['tax_query'][] = [
                    'taxonomy' => Listar::$post_type.'_location',
                    'field'    => 'term_id',
                    'terms'    => $location->term_id
                ];
            } else if($category = get_term_by( 'name', $search_term, Listar::$post_type.'_category' )) {
                $args['tax_query'][] = [
                    'taxonomy' => Listar::$post_type.'_category',
                    'field'    => 'term_id',
                    'terms'    => $category->term_id
                ];
            } else {
                $args['s'] = $search_term;
                /*
                $args['meta_query'][] = [
                    [
                        'key'     => 'address',
                        'value'   => $search_term,
                        'compare' => 'LIKE',
                    ]
                ];
                */
            }
        }

        // Sort
        if(isset($this->request['orderby']) && $this->request['orderby'] != ''
            && isset($this->request['order']) && $this->request['order'] != '') {
            $args['orderby'] = sanitize_sql_orderby($this->request['orderby']);
            $args['order'] = sanitize_text_field($this->request['order']);
        }

        // Author ID
        if(isset($this->request['user_id']) && absint($this->request['user_id']) > 0) {
            if(is_array($this->request['user_id']) && !empty($this->request['user_id'])) {
                $args['author__in'] = $this->request['user_id'];
            } else if(absint($this->request['user_id']) > 0) {
                $args['author'] = absint($this->request['user_id']);
            }
        }

        // Terms : Tags
        if(isset($this->request['tag']) && $this->request['tag'] != '') {
            if(is_array($this->request['tag'])) {
                $args['tag__in'] = $this->request['tag'];
            } else {
                $args['tag_id'] = (int)$this->request['tag'];
            }
        }

        // Terms : Category
        if(isset($this->request['category']) && $this->request['category'] != '') {
            $args['tax_query'][] = [
                'taxonomy' => Listar::$post_type.'_category',
                'field'    => 'term_id',
                'terms'    => is_array($this->request['category']) ? $this->request['category'] : absint($this->request['category'])
            ];
        }

        // Terms : Feature
        if(isset($this->request['feature']) && $this->request['feature'] != '') {
            $args['tax_query'][] = [
                'taxonomy' => Listar::$post_type.'_feature',
                'field'    => 'term_id',
                'terms'    => is_array($this->request['feature']) ? $this->request['feature'] : absint($this->request['feature'])
            ];
        }

        // Terms : Location (Country/City/State
        if(isset($this->request['location']) && $this->request['location'] != '') {
            $args['tax_query'][] = [
                'taxonomy' => Listar::$post_type.'_location',
                'field'    => 'term_id',
                'terms'    => is_array($this->request['location']) ? $this->request['location'] : absint($this->request['location'])
            ];
        }

        // Pricing
        if(isset($this->request['price_min']) && absint($this->request['price_min']) > 0) {
            $args['meta_query'][] = [
                'key'     => 'price_min',
                'value'   => absint($this->request['price_min']),
                'type'    => 'numeric',
                'compare' => '>=',
            ];
        }

        if(isset($this->request['price_max']) && absint($this->request['price_max']) > 0) {
            $args['meta_query'][] = [
                'key'     => 'price_max',
                'value'   => absint($this->request['price_max']),
                'type'    => 'numeric',
                'compare' => '<=',
            ];
        }

        // Color search
        if(isset($this->request['color']) && $this->request['color'] != '') {
            $hash = substr($this->request['color'], 0, 1);
            if($hash !== '#') {
                $this->request['color'] = sprintf('#%s', $this->request['color']);
            }

            $args['meta_query'][] = [
                'key'     => 'color',
                'value'   => sanitize_text_field($this->request['color']),
                'compare' => '=',
            ];
        }

        // Open Hours
        if(isset($this->request['time']) && $this->request['time'] != '') {
            $args['meta_query'][] = [
                'key'     => 'opening_hour_1_start',
                'value'   => sanitize_text_field($this->request['time']),
                'compare' => '>=',
            ];
        }
        
        /**
         * @since 1.0.23
         * - Search address
         */
        // add_filter( 'get_meta_sql', [$this, 'posts_search'], 10, 6 );

        $query = new WP_Query($args);
        $posts = $query->get_posts();
        $this->total_posts= absint($query->found_posts);
        // $query->request; // Wordpress Query SQL

    
        if ( $this->total_posts < 1 ) {
            // Out-of-bounds, run the query again without LIMIT for total count.
            unset( $args['paged'] );
            $count_query = new WP_Query();
            $count_query->query( $args );
            $this->total_posts = absint($count_query->found_posts);
        }

        $this->max_pages = ceil( $this->total_posts / absint($query->query_vars['posts_per_page']) );

        /**
         * @since 1.0.23
         * - Search address
         */
        // remove_filter( 'get_meta_sql', [$this, 'posts_search'], 10, 6 );

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
     * @param $sql
     * @param $queries
     * @param $type
     * @param $primary_table
     * @param $primary_id_column
     * @param $context
     * @return string
     */
    public function posts_search($sql, $queries, $type, $primary_table, $primary_id_column, $context)
    {   
        if ( $context !== null && $context->is_search() ) {
            $sql['where'] = preg_replace( '/AND/', 'OR', $sql['where'], 1 );
        }

        return $sql;
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
