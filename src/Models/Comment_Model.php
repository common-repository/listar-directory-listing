<?php
namespace ListarWP\Plugin\Models;
use ListarWP\Plugin\Listar;
use Exception;
use WP_Comment;

class Comment_Model {
    /**
     * The average of total comments
     */
    static $meta_avg_key = 'rating_avg';

    /**
     * Total rating count
     */
    static $meta_count_key = 'rating_count';

    /**
     * Count by rating value
     */
    static $meta_rating_key = 'rating_meta';


    public function after_save_comment($comment_id) {
        try {
            if(isset($_POST['comment_post_ID'])) {
                $post_id = absint($_POST['comment_post_ID']);
            } else if(isset($_POST['post'])) {
                $post_id = absint($_POST['post']);
            } else {
                $post_id = 0;
            }
            $rating  = isset($_POST['rating']) ? absint($_POST['rating']) : 0;

            // Check valid post
            $post = Listar::valid_post($post_id);

            /**
             * Save comment meta
             * Only calculate rating base on approved comment
             */
            if(add_comment_meta( $comment_id, 'rating', $rating )) {
                self::set_rating_meta($post->ID);
            }
        } catch (Exception $e) {
            // Do nothing
            if(WP_DEBUG) {
                error_log('after_save_comment.error: '. $e->getMessage());
            }
        }
    }

    /**
     * Set rating meta for WP_Post
     *
     * @param integer $post_id
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    static function set_rating_meta($post_id) {
        $rating_data = self::get_ratings_data($post_id);
        update_post_meta($post_id, self::$meta_avg_key, $rating_data['avg']);
        update_post_meta($post_id, self::$meta_count_key, $rating_data['count']);
        update_post_meta($post_id, self::$meta_rating_key, json_encode($rating_data['meta']));
    }

    /**
     * Get rating meta data
     * - this data has been cached & calculated
     *
     * @param int $post_id
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0
     *
     */
    public static function get_rating_meta($post_id) {
        $rating_avg = (float)get_post_meta($post_id, self::$meta_avg_key, TRUE);
        $rating_count = (int)get_post_meta($post_id, self::$meta_count_key, TRUE);
        $rating_meta = json_decode(get_post_meta($post_id, self::$meta_rating_key, TRUE));
        if(!$rating_meta) {
            for($i=1;$i<=5;$i++) {
                $rating_meta[$i] = 0;
            }
        }
        return [
            'rating_avg' => $rating_avg,
            'rating_count' => $rating_count,
            'rating_meta' => $rating_meta
        ];
    }

    /**
     * Calculate rating data
     * - total rating
     * - total user comment (approved)
     * - average rating
     *
     * @param integer $post_id
     * @param boolean $approved
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public static function get_ratings_data( $post_id , $approved = true) {
        if($approved) {
            $comments = get_approved_comments( $post_id );
        } else {
            $comments = get_comments([
                'post_id' => $post_id
            ]);
        }

        $count = sizeof($comments);
        $meta = array_fill_keys(['1', '2', '3', '4', '5'], 0);

        if ( $comments ) {
            $i = 0;
            $total = 0;
            foreach( $comments as $comment ){
                $rate = get_comment_meta( $comment->comment_ID, 'rating', true );
                if( isset( $rate ) && '' !== $rate ) {
                    $i++;
                    // Total raring
                    $total += $rate;
                    // Update meta counter by rating number
                    if(isset($meta[$rate])) {
                        $meta[$rate]++;
                    }
                }
            }

            if ( 0 === $i ) {
                return ['count' => $count, 'avg' => 0, 'meta' => $meta];
            } else {
                return ['count' => $count, 'avg' => round( $total / $i, 1 ), 'meta' => $meta];
            }
        } else {
            return ['count' => 0, 'avg' => 0, 'meta' => $meta];
        }
    }

    /**
     * Assign data list with basic information
     * - Author Image, Rating
     *
     * @param WP_Comment $comment
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.7
     */
    public static function assign_data_list(&$comment)
    {
        if (is_object($comment) && $comment->comment_ID) {
            $comment->comment_author_image = User_Model::get_user_photo_url($comment->user_id, $comment->comment_author_email);
            $comment->rate = absint(get_comment_meta( $comment->comment_ID, 'rating', true ));
        }
    }

    /**
     * Get total feedback from my post
     * @param int $uid Author ID
     * @return string|null
     * @version 1.0.9
     */
    public static function get_total_feedback( $uid = 0) {
        global $wpdb;

        $sql = "SELECT COUNT(*) as total 
            FROM {$wpdb->comments} as c  
            JOIN {$wpdb->posts} as p ON p.ID = c.comment_post_ID 
            WHERE c.comment_approved = '1' 
            AND p.post_status = 'publish'  
            AND p.post_type ='".Listar::$post_type."'  
            AND p.post_author = %d";

        return absint($wpdb->get_var( $wpdb->prepare( $sql, $uid )));
    }

    /**
     * Get average feedback
     * @param int $author_id
     * @return int
     */
    public static function get_author_avg_feedback($author_id = 0)
    {
        global $wpdb;

        $sql = "SELECT AVG(meta_value) as avg_feedback 
            FROM {$wpdb->posts} as p
            JOIN {$wpdb->postmeta} as m ON p.ID = m.post_id 
            WHERE m.meta_key = '".self::$meta_avg_key."'  
                AND p.post_status = 'publish'  
                AND p.post_type ='".Listar::$post_type."'  
                AND p.post_author = %d";
        $result = $wpdb->get_var( $wpdb->prepare( $sql, $author_id ));        
        return $result ? number_format($result, 1) : '';
    }
}
