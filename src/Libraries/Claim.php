<?php
namespace ListarWP\Plugin\Libraries;

use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Libraries\Product;
use ListarWP\Plugin\Libraries\Claim\Claim_Abstract;
use ListarWP\Plugin\Models\Claim_Model;
use ListarWP\Plugin\Models\Place_Model;
use ListarWP\Plugin\Models\Setting_Model;
use ListarWP\Plugin\Libraries\Payment\Payment_PayPal;
use ListarWP\Plugin\Libraries\Payment\Payment_Stripe;
use ListarWP\Plugin\Libraries\Claim\Claim_Free;
use ListarWP\Plugin\Libraries\Claim\Claim_Pay;
use Exception;

/**
 * Class Claim
 */
class Claim extends Order_Abstract {
    /**
     * @var Product
     */
    public $resource = NULL;

     /**
     * @var Claim_Abstract
     */
    public $method_charge = '';

    /**
     * Memo
     *
     * @var string
     */
    public $memo = '';

    /**
     * Construct
     * @param Claim_Model $claim
     * @throws Exception
     */
    public function __construct($claim = 0)
    {
        parent::__construct();
        $this->post_type = Claim_Model::post_type();
        return $this;
    }

    /**
     * Set payment methid
     *
     * @param string $payment_method
     * @throws Exception
     */
    public function set_payment_method($payment_method = '')
    {
        $base_url = get_site_url();
        parent::set_payment_method($payment_method);
        
        if($this->payment instanceof Payment_PayPal) {
            $this->payment->set_success_url($base_url."/index.php/wp-json/listar/v1/claim/return");
            $this->payment->set_cancel_url($base_url."/index.php/wp-json/listar/v1/claim/cancel");
        } else if($this->payment instanceof Payment_Stripe) {
            $this->payment->set_success_url($base_url."/index.php/wp-json/listar/v1/claim/return/?token={CHECKOUT_SESSION_ID}");
            $this->payment->set_cancel_url($base_url."/index.php/wp-json/listar/v1/claim/cancel/?token={CHECKOUT_SESSION_ID}");
        }
    }
    
    /**
     * Set method charge
     * @param string $claim_method_charge
     * @return Claim_Abstract|Claim_Free|Claim_Pay
     */
    public function set_method_charge($claim_method_charge = '')
    {
        switch ($claim_method_charge) {
            case 'free':
                $this->method_charge = new Claim_Free();
                break;
            case 'pay':
                $this->method_charge = new Claim_Pay();
                break;    
            default:
                throw new Exception(__('Undefine the claim method of charge', 'listar'));  
                break;
        }

        return $this->method_charge;
    }

    /**
     * Set resource
     * @param Claim_Model $claim Claim data modal
     * @throws Exception
     */
    public function set_resource($claim)
    {
        // Find related listing by post_parent
        $author = NULL;
        $listing = NULL;
        try {
            $listing_id = $claim->get_value('post_parent');
            $listing = Listar::valid_post($listing_id);
            // Get author email
            $author = get_user_by('id', $listing->post_author);
        } catch(Exception $e) {
            error_log('claim.set_resource.valid:'.$e->getMessage().'ID='.$claim->get_id());
        }

        $this->resource = new Product([
            'id' => $claim->get_id(),
            'name' => $claim->get_title(),
            'price' => $claim->get_claim_charge_fee(),
            'author' => !empty($listing) ? $listing->post_author : 0,
            'author_email' => !empty($author) ? $author->user_email : '',
        ]);
    }

    /**
     * Set listing
     * @param int $id Claim id
     * @throws Exception
     */
    public function set_listing_id($id) 
    {
        // Listing
        $listing = Listar::valid_post($id);
        // Get author email
        $author = get_user_by('id', $listing->post_author);

        $this->resource = new Product([
            'id' => $listing->ID,
            'name' => $listing->post_title,
            'price' => '',
            'author' => !empty($listing) ? $listing->post_author : 0,
            'author_email' => !empty($author) ? $author->user_email : '',
        ]);
    }

    /**
     * Set memo
     * @param string $memo
     */
    public function set_memo($memo = '') 
    {
        $this->memo = $memo;        
    }

    /**
     * Request to claimn
     *
     * @return int 
     */
    public function request()
    {   
        if(!($this->resource instanceof Product)) {
            throw new Exception(__('Undefine listing to claim', 'listar'));
        }

        if(!($this->customer instanceof Customer)) {
            throw new Exception(__('Undefine author', 'listar'));
        }

        $data = [
            'post_title' => $this->resource->get_name(),
            'post_type' => $this->post_type,
            'post_status' => $this->status,
            'post_parent' => $this->resource->get_id(), // link with real listing
        ];

        $claim_id = wp_insert_post($data, TRUE);

        if($claim_id) {
            // Meta data history
            $listing = get_post( (int) $this->resource->get_id());
            Place_Model::assign_address_data($listing);

            // Feature image
            $img_id = get_post_thumbnail_id($listing->ID);
            if($img_id) {
                update_term_meta($claim_id, 'featured_image', $img_id);
            }

            // Addition medata data
            $this->payment = false;
            $data = [
                '_listing_address' => $listing->full_address,
                '_customer_user' => $this->customer->get_id(),
                '_order_key' => $this->generate_order_key(), // Order ID (Local System)
                '_created_via' => $this->created_via,
                '_billing_first_name' => $this->customer->first_name,
                '_billing_last_name' => $this->customer->last_name,
                '_billing_email' => $this->customer->email,
                '_billing_phone' => $this->customer->phone,
                '_author' => $this->resource->get_author(),
                '_author_email' => $this->resource->get_author_email(),
                '_memo' => $this->memo,
            ];

            // Set claim fee
            $claim_method_charge = Place_Model::get_claim_method_charge($listing->ID);
            $data['claim_method_charge'] = $claim_method_charge;
            if($claim_method_charge == 'free') {
                $data['claim_price'] = 0;
                $data['claim_unit_price'] = '';
            } else {
                $data['claim_price'] = Place_Model::get_claim_charge_fee($listing->ID);
                $data['claim_unit_price'] = Setting_Model::get_option('unit_price');
            }

            $this->insert_meta_data($data, $claim_id);

            // Send notification
            $notification_data = [];
            foreach($data as $key => $value) {
                $key = ltrim($key, '_');
                $notification_data[$key] = $value;
            }
            $notification_data = array_merge($notification_data, (array)$listing);
            
            // Hook
            do_action('request_listar_claim', $listing->ID, $claim_id, $notification_data);
        }

        return $claim_id;
    }

    /**
     * Verify the allow submit to claim or not
     * - Check marked as claim
     * - Check pending review 
     * ....
     * @since 1.0.32
     * @throws Exception
     * @return boolean
     */
    public function verify()
    {
        // Check setting on/off
        if(!Setting_Model::get_option('claim_listing_use')) {
            throw new Exception(__('Request to claim is disabled. Please contract the administatrator.'));
        }
        
        // Check owner
        if(Place_Model::is_claimed($this->resource->get_id())) {
            throw new Exception(__('The listing was owned. Request to claim is not allowed.'));
        }

        // Check pending request
        $posts = get_posts([
            'include' => $this->resource->get_id(),
            'post_type' => Claim_Model::post_type(),
            'post_status' => ['pending', 'publish'], // Being review or just approved
            'posts_per_page' => 1,
        ]);
        
        if(is_array($posts) && sizeof($posts) > 0) {
            throw new Exception(__('The listing has been claimed and being to reviewed.'));
        }

        return true;
    }
    
    /**
     * Request to claim
     * @throws Exception
     * @return void
     */
    public function create_order()
    {
        global $wpdb;

        try {
            if(!$this->resource) {
                throw new Exception(__('Undefined listing to claim'));
            }

            // begin transaction
            $wpdb->query('START TRANSACTION');

            // Create billing & payment
            parent::create_billing_payment();

            // parent::insert_meta_data([
            //     'claim_use' => true,
            // ], $this->resource->get_id());

            $thumbnail_id = get_post_thumbnail_id($this->resource->get_id());
            if($thumbnail_id) {
                set_post_thumbnail($this->resource->get_id(), $thumbnail_id);
            }            

            // Transaction commit
            $wpdb->query('COMMIT');

            // Data view
            $model = new Claim_Model();
            $data = $model->get_item_view($this->order_id);
        } catch (Exception $e) {
            // roll back everything
            $wpdb->query('ROLLBACK');
            // Log error
            error_log($e->getMessage());
            throw $e;
        }
    }

    /**
     * Request to claim
     * @throws Exception
     * @since 1.0.31
     */
    public function update_order()
    {

    }

    /**
     * Accept payment by id
     * @param Claim_Model $claim
     * @throws Exception
     * @since 1.0.31
     */
    public function accept($claim)
    {
        try {

            $data_view = $claim->get_item_view($claim->get_id());
            /**
             * Approve the request 
             * - First request: pending
             * - Admin approve: publish (Approved)
             */

            // Set method of charge
            $this->set_method_charge($claim->get_claim_method_charge());

            $this->method_charge->set_claim($claim);
            $this->method_charge->accept();

            /**
             * Notification
             * Notify::notify_claim_approve($data_view);
             * > Move to use hook
             * @since 1.0.31
             */
            return $data_view;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Cancel payment by booking id
     * @param int $claim_id
     * @throws Exception
     */
    public function cancel($claim_id = 0)
    {
        try {
            $claim_model = new Claim_Model();
            $claim = $claim_model->item($claim_id);
            $claim_data = $claim_model->get_item_view($claim_id);

            if($claim->get_status() === 'pending' || $claim->get_status() === 'publish') {
                // Update status order
                wp_update_post([
                    'ID' => $claim_id,
                    'post_status' => 'canceled'
                ]);
                /**
                 * Send notification
                 * Notify::notify_claim_cancel($claim_data);
                 * > Move to use on ListarWP\Plugin\Hooks\Claim
                 * @since 1.0.31
                 */
            } else {
                throw new Exception(__('Undefined status for cancelling. The status must be pending.', 'listar'));
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Complete payment
     * - When admin approved (publish)
     * @param string $transaction_id
     * @throws Exception
     */
    public function complete_payment($transaction_id = '')
    {
        if(!Setting_Model::payment_use()) {
            throw new Exception('The payment feature has been disabled');
        }

        $claim = new Claim_Model();
        $order = $claim->item_condition('_txn_id', $transaction_id, 'publish');
        $this->set_payment_method($order->get_payment_method());
        $this->payment->order_id = $order->get_id();
        
        try {
            // Payment process
            $this->payment->complete($order, $transaction_id);

            // Method of charge process
            $this->set_method_charge($claim->get_claim_method_charge());
            $this->method_charge->set_claim($claim);
            $this->method_charge->complete();

            /**
             * @since 1.0.31
             * Move to \Hooks\Claim
             * Notify::notify_claim_complete($claim_data);
             */
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Cancel payment
     * - When admin approved (publish)
     * @param string $transaction_id
     * @throws Exception
     */
    public function cancel_payment($transaction_id = '')
    {
        if(!Setting_Model::payment_use()) {
            throw new Exception('The payment feature has been disabled');
        }

        $claim_model = new Claim_Model();
        $order = $claim_model->item_condition('_txn_id', $transaction_id, 'publish');
        $this->set_payment_method($order->get_payment_method());
        $this->payment->order_id = $order->get_id();
        $claim_data = $claim_model->get_item_view($order->get_id());
        try {
            $this->payment->cancel($order, $transaction_id);
            /**
             * Send notification
             * Notify::notify_claim_cancel($claim_data);
             * > Move to use on ListarWP\Plugin\Hooks\Claim
             * @since 1.0.31
             */
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}