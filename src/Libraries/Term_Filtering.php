<?php
namespace ListarWP\Plugin\Libraries;
use ListarWP\Plugin\Models\Setting_Model;

use WP_REST_Request;
use WP_Term_Query;
use WP_Error;
use WP_Post;

/**
 * Class Term_Filtering
 * @desc Get term pagination
 */
class Term_Filtering {
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
     * Offset
     * @var int
     */
    public $offset;

    /**
     * Default term query name
     * @var string
     */
    public $taxonomy = 'post_tag';
    /**
     * Listar_Filtering constructor.
     * @param WP_REST_Request $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Get result query
     * @return array|int[]|WP_Error|WP_Post[]
     */
    public function get_result()
    {
        $result = [];
        $this->per_page = isset($this->request['per_page']) ? absint($this->request['per_page'])
            : absint(Setting_Model::get_option('per_page'));
        $this->paged = isset($this->request['page']) ? absint($this->request['page']) : 1;
        $this->offset = ( $this->paged - 1 ) * $this->per_page;

        $args = [
            'taxonomy'  => $this->taxonomy,
            'number' => $this->per_page,
            'offset' => $this->offset,
            'orderby'   => 'name',
            'order'     => 'ASC',
            'hide_empty'    => false,
        ];

        // Keyword
        if(isset($this->request['s']) && $this->request['s'] != '') {
            $args['search'] = sanitize_text_field($this->request['s']);
        }

        $terms_query = new WP_Term_Query($args);
        $result = $terms_query->get_terms();

        if(!empty($result)) {
            // Out-of-bounds, run the query again without LIMIT for total count.
            unset( $args['number'] );
            unset( $args['offset'] );
            $terms_query = new WP_Term_Query($args);
            $this->total_posts = absint(sizeof($terms_query->get_terms()));
        }

        $this->max_pages = ceil( $this->total_posts / absint($this->per_page));

        if ( $this->paged > $this->max_pages && $this->total_posts > 0 ) {
            return new WP_Error( 'listar_invalid_page_number',
                __( 'The page number requested is larger than the number of pages available.', 'listar' ),
                [
                    'status' => 400
                ]);
        }

        return $result;
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
