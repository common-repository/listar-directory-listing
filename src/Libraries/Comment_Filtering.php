<?php
namespace ListarWP\Plugin\Libraries;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Models\Setting_Model;
use WP_REST_Request;
use WP_Error;
use WP_Post;
use WP_Comment_Query;

class Comment_Filtering {
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
            'status' => 'approve',
            'post_type' => Listar::$post_type,
            'paged' => $this->paged,
            'number' => $this->per_page,
            'offset' => $this->offset,
            //'user_id' =>
        ];

        // User ID
        if(isset($this->request['post_author']) && absint($this->request['post_author']) > 0) {
            if(is_array($this->request['post_author']) && !empty($this->request['post_author'])) {
                $args['post_author__in'] = $this->request['post_author'];
            } else if(absint($this->request['post_author']) > 0) {
                $args['post_author'] = absint($this->request['post_author']);
            }
        }

        $comments_query = new WP_Comment_Query($args);
        $result = $comments_query->comments;

        if(!empty($result)) {
            // Out-of-bounds, run the query again without LIMIT for total count.
            unset( $args['paged'] );
            unset( $args['number'] );
            unset( $args['offset'] );
            $count_query = new WP_Comment_Query($args);
            $this->total_posts = absint(sizeof($count_query->comments));
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
            'page' => (int)$this->paged,
            'per_page' => (int)$this->per_page,
            'max_page' => (int)$this->max_pages,
            'total' => (int)$this->total_posts,
        ];
    }
}
