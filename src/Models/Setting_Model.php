<?php
namespace ListarWP\Plugin\Models;
use ListarWP\Plugin\Listar;
use WP_Post;

class Setting_Model {
    /**
     * Storing default options
     */
    static $combine_options = [];

    /**
     * default image
     * @var null | string
     */
    static $default_image = NULL;

    /**
     * Get Options
     * - get by id
     * - get by id & sections
     * @param string $id setting id
     * @param string $section section
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public static function get_options($id = '', $section = '') {
        $options = [
            'mobile' => [
                'title' => __('Mobile App', 'listar'),
                'default_section' => 'general',
                'sections' => [
                    'general' => [
                        'title' => __('General', 'listar'),
                        'options' => [                                                      
                            [
                                'name' => __('Image Slider', 'listar'),
                                'desc' => __('On the mobile home screen, display an image slider with an aspect ratio of 2:3. For example, use an image size of 375px by 250px.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'mobile_slider',
                                'type' => 'gallery',
                                'default' => ''
                            ],                                                        
                            [
                                'name' => __('Widget Layout', 'listar'),
                                'desc' => __('Use the Widget Settings feature on the backend to format the mobile layout', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'layout_widget',
                                'type' => 'checkbox',
                                'default' => false,
                            ],
                            [
                                'name' => __('Widget Header', 'listar'),
                                'desc' => __('Header style of home screen', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'layout_widget_header',
                                'type' => 'select',
                                'default' => 'basic',
                                'options' => [
                                    'basic' => __('Basic', 'listar'),
                                    'slider' => __('Select Option', 'listar'),                                    
                                ]
                            ],
                            [
                                'name' => __('Widget Header Option', 'listar'),
                                'desc' => __('When Widget Header settings with Select Option mode. Select type of taxonomy for use via setting Appearance > Menu', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'select_option_widget',
                                'type' => 'select',
                                'default' => '',
                                'options' => [
                                    '' => __('Not use', 'listar'),
                                    'location' => __('Mobile Dashboard Location', 'listar'),
                                    'category' => __('Mobile Dashboard Category', 'listar'),                                    
                                ]
                            ],
                            [
                                'name' => __('Layout Detail Screen', 'listar'),
                                'desc' => __('Select what style for detail screen', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'layout_mode',
                                'type' => 'select',
                                'default' => 'default',
                                'options' => [
                                    'default' => __('Default', 'listar'),
                                    'professional_1' => __('Food', 'listar'),
                                    'professional_2' => __('Event', 'listar'),
                                    'professional_3' => __('RealEstate', 'listar'),
                                    'professional_4' => __('Claim Listing', 'listar'),
                                ]
                            ],
                            [
                                'name' => __('Layout Listing Screen', 'listar'),
                                'desc' => __('Select what mobile card style for display the listing data', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'list_mode',
                                'type' => 'select',
                                'default' => 'list',
                                'options' => [
                                    'list' => __('List', 'listar'),
                                    'block' => __('Block', 'listar'),
                                    'grid' => __('Grid', 'listar')
                                ]
                            ],
                            [
                                'name' => __('Total Per Page', 'listar'),
                                'desc' => __('The total number of listings that can be loaded through the API once', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'per_page',
                                'type' => 'text',
                                'default' => 20,
                            ], 
                            [
                                'name' => __('Total Categories Search', 'listar'),
                                'desc' => __('The total number of categories on filter screens. Set the value 0 if there is no limit.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'category_per_page',
                                'type' => 'text',
                                'default' => 10,
                            ], 
                            [
                                'name' => __('Total Features Search', 'listar'),
                                'desc' => __('The total number of features on filter screens. Set the value 0 if there is no limit.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'feature_per_page',
                                'type' => 'text',
                                'default' => 10,
                            ],
                        ]
                    ],   
                    'account' => [
                        'title' => __('Account', 'listar'),
                        'options' => [                                                      
                            [
                                'name' => __('Delete account', 'listar'),
                                'desc' => __('The user account data will be deleted from the database. If this option is not checked, the account will be blocked only', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'deactivate_account',
                                'type' => 'checkbox',
                                'default' => false,
                            ],
                            [
                                'name' => __('Assign Listing Data', 'listar'),
                                'desc' => __('Enter the user ID who will be owner of listing when the account is deleted. Ex: steve, john', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'deactivate_account_id',
                                'type' => 'text',
                                'default' => '',
                            ],
                            [
                                'name' => __('Review & Rating', 'listar'),
                                'desc' => __('The user is allowed when has claimed or booked the listing.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'user_comment_validate',
                                'type' => 'checkbox',
                                'default' => false,
                            ],
                        ]
                    ],
                    'auth' => [
                        'title' => __('Authentication', 'listar'),
                        'options' => [                                                      
                            [
                                'name' => __('JWT Expired Token', 'listar'),
                                'desc' => __('Allows you to change the value expire date before the token is created. Input number of days. Default is 7 days from login time.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'jwt_auth_expire',
                                'type' => 'text',
                                'default' => '7',
                            ],
                            [
                                'name' => __('OTP Use', 'listar'),
                                'desc' => __('OTP code will be sent via the email for authorization when the user login, register and forgot password.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'otp_use',
                                'type' => 'checkbox',
                                'default' => false,
                                
                            ],
                            [        
                                'name' => __('OTP Code Expire', 'listar'),
                                'desc' => __('Total time for avaible use when the code is sent. Typically, the expire time within 30~240 seconds.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'otp_expire_time',
                                'type' => 'text',
                                'default' => 60,
                            ],
                            [
                                'name' => __('OTP Email Subject', 'listar'),
                                'desc' => __('Available placeholders: {site_title}, {site_url}, {expire_time}, {code}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'otp_email_subject',
                                'type' => 'text',
                                'default' => '[{site_title}] Verification Code'
                            ],
                            [
                                'name' => __('OTP Email Content', 'listar'),
                                'desc' => __('Text to appear below the main email content. Available placeholders: {site_title}, {site_url}, {expire_time}, {code}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'otp_email_content',
                                'type' => 'wysiwyg',
                                'default' => 'Please use the verification code {code} to sign in. It will be expired after {expire_time} seconds'
                            ]                            
                        ]
                    ]                 
                ]
            ],
            'claim' => [
                'title' => __('Claim Listing', 'listar'),
                'default_section' => 'general',
                'sections' => [
                    'general' => [
                        'title' => __('General', 'listar'),
                        'options' => [
                            [
                                'name' => __('Enable', 'listar'),
                                'desc' => __('Allow user requests to claim the existing listing.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'claim_listing_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],                            
                            [
                                'name' => __('Claim Title', 'listar'),
                                'desc' => '',
                                'id'   =>  Listar::$option_prefix. 'claim_widget_title',
                                'type' => 'text',
                                'default' => __('Claim this listing as your own?', 'listar'),
                            ],
                            [
                                'name' => __('Claim Desc', 'listar'),
                                'desc' => '',
                                'id'   =>  Listar::$option_prefix. 'claim_widget_desc',
                                'type' => 'text',
                                'default' => __('Claim this listing and conttrol your own busines listing', 'listar'),
                            ],
                            [
                                'name' => __('Claim Button', 'listar'),
                                'desc' => '',
                                'id'   =>  Listar::$option_prefix. 'claim_button_text',
                                'type' => 'text',
                                'default' => __('Request to claim', 'listar'),
                            ],
                            [
                                'name' => __('Verified Text', 'listar'),
                                'desc' => '',
                                'id'   =>  Listar::$option_prefix. 'claim_verified_text',
                                'type' => 'text',
                                'default' => __('Verified', 'listar'),
                            ],
                            [
                                'name' => __('Display Claimed Badget', 'listar'),
                                'desc' => '',
                                'id'   =>  Listar::$option_prefix. 'claim_badget',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Method Of Charging', 'listar'),
                                'desc' => '',
                                'id'   =>  Listar::$option_prefix. 'claim_method_charge',
                                'type' => 'select',                                                                
                                'options' => [
                                    'free' => __('Claim for free', 'listar'),
                                    'pay' => __('Set a claim fee', 'listar'),
                                    //'plan' => __('Pricing Plan', 'listar')
                                ],
                                'default' => 'free'
                            ],
                            [
                                'name' => __('Claim Fee', 'listar'),
                                'desc' => '',
                                'id'   =>  Listar::$option_prefix. 'claim_price',
                                'type' => 'text',
                                'default' => 39,
                                'desc' => __('The default claim fee value. Use for set a claim fee option.', 'listar')
                            ],
                            // [
                            //     'name' => __('Duration', 'listar'),
                            //     'desc' => 'Total of days will be available after user has processed payment and admin approved',
                            //     'id'   =>  Listar::$option_prefix. 'claim_duration',
                            //     'type' => 'text',
                            //     'default' => 7,
                            // ],                            
                        ]
                    ],
                    'submit' => [
                        'title' => __('Submission', 'listar'),
                        'options' => [
                            [
                                'name' => __('Enable', 'listar'),
                                'desc' => __('Allow users to create new listings or update directory data using a mobile application.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'submit_listing_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Approval', 'listar'),
                                'desc' => __('The listing must be approved before publishing.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'submit_listing_approval_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ]
                        ]
                    ]
                ]
            ],
            'booking' => [
                'title' => __('Booking', 'listar'),
                'default_section' => 'general',
                'sections' => [
                    'general' => [
                        'title' => __('General', 'listar'),
                        'options' => [
                            [
                                'name' => __('Enable', 'listar'),
                                'desc' => __('Booking Use', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'booking_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Booking Title Format', 'listar'),
                                'desc' => __('Format Use', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'booking_title_format',
                                'type' => 'checkbox',
                                'default' => false
                            ],
                            [
                                'name' => __('Booking Title Pattern', 'listar'),
                                'desc' => __('Available placeholders: {site_title}, {site_url}, {created_on}, {booking_id}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'booking_title_pattern',
                                'type' => 'text',
                                'default' => "Booking {booking_id} - {billing_first_name} {billing_last_name} ({billing_phone})"
                            ]
                        ]
                    ],
                    'standard' => [
                        'title' => __('Standard', 'listar'),
                        'options' => [
                            [
                                'name' => __('Max persons', 'listar'),
                                'desc' => __('How many persons for select booking (include adults and children)', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'booking_standard_max_person',
                                'type' => 'text',
                                'default' => 7
                            ]
                        ]
                    ],
                    'daily' => [
                        'title' => __('Daily', 'listar'),
                        'options' => [
                            [
                                'name' => __('Max persons', 'listar'),
                                'desc' => __('How many persons for select booking (include adults and children)', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'booking_daily_max_person',
                                'type' => 'text',
                                'default' => 7
                            ],
                            [
                                'name' => __('Max days', 'listar'),
                                'desc' => __('How many days for select from start date to end date. Please input the number of total days', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'booking_daily_max_day',
                                'type' => 'text',
                                'default' => 7
                            ],
                            [
                                'name' => __('Start Time Require', 'listar'),
                                'desc' => __('Required when create booking', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'booking_daily_start_time_require',
                                'type' => 'checkbox',
                                'default' => false,
                            ],
                            [
                                'name' => __('End Date Require', 'listar'),
                                'desc' => __('Required when create booking', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'booking_daily_end_date_require',
                                'type' => 'checkbox',
                                'default' => false,
                            ],
                            [
                                'name' => __('End Time Require', 'listar'),
                                'desc' => __('Required when create booking', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'booking_daily_end_time_require',
                                'type' => 'checkbox',
                                'default' => false,
                            ],
                        ]
                    ],
                    'hourly' => [
                        'title' => __('Hourly', 'listar'),
                        'options' => [
                            [
                                'name' => __('Max persons', 'listar'),
                                'desc' => __('How many persons for select booking (include adults and children)', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'booking_hourly_max_person',
                                'type' => 'text',
                                'default' => 7
                            ],
                            [
                                'name' => __('Max duration', 'listar'),
                                'desc' => __('How many hours for select from start time to end time. Please input the number of duration (hours)', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'booking_hourly_max_duration',
                                'type' => 'text',
                                'default' => 7
                            ],
                            [
                                'name' => __('Start Time', 'listar'),
                                'desc' => __('The start time of time slots', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'booking_hourly_start_time',
                                'type' => 'text',
                                'default' => '08:00'
                            ],
                            [
                                'name' => __('End Time', 'listar'),
                                'desc' => __('The end time of time slots', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'booking_hourly_end_time',
                                'type' => 'text',
                                'default' => '18:00'
                            ],
                            [
                                'name' => __('Duration Interval', 'listar'),
                                'desc' => __('The duration between start time and end time for generate the time slots', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'booking_hourly_duration',
                                'default' => 60,
                                'type' => 'select',
                                'options' => [
                                    '60' => __('1 hour', 'listar'),
                                    '120' => __('2 hours', 'listar'),
                                    '180' => __('3 hours', 'listar'),
                                    '240' => __('4 hours', 'listar')
                                ]
                            ],
                        ]
                    ],
                    'slot' => [
                        'title' => __('Slot', 'listar'),
                        'options' => [
                            [
                                'name' => __('Max persons', 'listar'),
                                'desc' => __('How many persons for select booking (include adults and children)', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'booking_slot_max_person',
                                'type' => 'text',
                                'default' => 7
                            ]
                        ]
                    ],
                    /*
                    'table' => [
                        'title' => __('Table', 'listar'),
                        'options' => [
                            [
                                'name' => __('Max persons', 'listar'),
                                'desc' => __('How many persons for select booking (include adults and children)', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'booking_table_max_person',
                                'type' => 'text',
                                'default' => 7
                            ],
                            [
                                'name' => __('Total tables', 'listar'),
                                'desc' => __('The list total number of tables for select booking', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'booking_total_table',
                                'type' => 'text',
                                'default' => 15
                            ],
                        ]
                    ]
                    */
                ]
            ],
            'payment' => [
                'title' => __('Payment', 'listar'),
                'default_section' => 'general', // default
                'sections' => [
                    'general' => [
                        'title' => __('General', 'listar'),
                        'options' => [
                            [
                                'name' => __('Enable', 'listar'),
                                'desc' => __('Payment Use', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'payment_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Terms and conditions', 'listar'),
                                'desc' => __('If you define a "Terms" page the customer will be asked if they accept them when checking out.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'payment_term_condition_page',
                                'type' => 'text',
                                'default' => 'term-of-use',
                            ],
                            [
                                'name' => __('Currency Code', 'listar'),
                                'desc' => __('The price of a single product or service', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'unit_price', // currency_unit
                                'type' => 'text',
                                'default' => 'USD',
                            ],
                            [
                                'name' => __('Currency Symbol', 'listar'),
                                'desc' => __('The symbol of currency', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'unit_symbol',
                                'type' => 'text',
                                'default' => '$',
                            ],
                            [
                                'name' => __('Currency Position', 'position'),
                                'desc' => __('This controls the position of the currency symbol.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'unit_position',
                                'type' => 'select',
                                'default' => 'left',
                                'options' => [
                                    'left' => __('Left', 'listar'),
                                    'right' => __('Right', 'listar'),
                                    'left_space' => __('Left with space', 'listar'),
                                    'right_space' => __('Right with space', 'listar')
                                ]
                            ],
                            [
                                'name' => __('Thousand separator', 'listar'),
                                'desc' => __('This sets the thousand separator of displayed prices. Example for one thousand USD Dollars is $1,000', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'unit_thousand_separator',
                                'type' => 'select',
                                'default' => 'none',
                                'options' => [
                                    'comma' => __('Comma ","', 'listar'),
                                    'dot' => __('Dot "."', 'listar'),
                                    'none' => __('Non set', 'listar')
                                ]
                            ],
                        ]
                    ],
                    'paypal' => [
                        'title' => __('Paypal', 'listar'),
                        'options' => [
                            [
                                'name' => __('Enable', 'listar'),
                                'desc' => __('Use PayPal Checkout v2', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'paypal_use',
                                'type' => 'checkbox'
                            ],
                            [
                                'name' => __('Title', 'listar'),
                                'desc' => __('This controls the title which the user sees during checkout.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'paypal_title',
                                'type' => 'text',
                                'default' => 'PayPal Checkout'
                            ],
                            [
                                'name' => __('Description', 'listar'),
                                'desc' => __('Payment method description that the customer will see on your checkout.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'paypal_desc',
                                'type' => 'text',
                                'default' => 'Pay via PayPal. You can pay with your credit card if you do not have a PayPal account.'
                            ],
                            [
                                'name' => __('Instructions', 'listar'),
                                'desc' => __('Instructions that will be added to the thank you page and emails.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'paypal_ins',
                                'type' => 'textarea',
                                'default' => 'Instructions that will be added to the thank you page and emails.'
                            ],
                            [
                                'name' => __('Environment', 'listar'),
                                'desc' => __('This setting specifies whether you will process live transactions, or whether you will process simulated transactions using the PayPal Sandbox.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'paypal_env',
                                'type' => 'select',
                                'default' => 'sandbox',
                                'options' => [
                                    'sandbox' => __('Sandbox', 'listar'),
                                    'live' => __('Live', 'listar')
                                ]
                            ],
                            [
                                'name' => __('Client ID', 'listar'),
                                'desc' => __('Go to PayPal Developers Website home page and get the Client ID key', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'paypal_client_id',
                                'type' => 'text',
                                'default' => ''
                            ],
                            [
                                'name' => __('Client Secret', 'listar'),
                                'desc' => __('Go to PayPal Developers Website home page and get the Client Secret key', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'paypal_client_secret',
                                'type' => 'text',
                                'default' => ''
                            ]
                        ],
                    ],
                    'stripe' => [
                        'title' => __('Stripe', 'listar'),
                        'options' => [
                            [
                                'name' => __('Enable', 'listar'),
                                'desc' => __('Use Stripe Payment (Credit Card)', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'stripe_use',
                                'type' => 'checkbox'
                            ],
                            [
                                'name' => __('Title', 'listar'),
                                'desc' => __('This controls the title which the user sees during checkout.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'stripe_title',
                                'type' => 'text',
                                'default' => 'Stripe (Credit Card)'
                            ],
                            [
                                'name' => __('Description', 'listar'),
                                'desc' => __('Pay with your credit card via Stripe.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'stripe_desc',
                                'type' => 'text',
                                'default' => 'Pay with your credit card via Stripe.'
                            ],
                            [
                                'name' => __('Instructions', 'listar'),
                                'desc' => __('Instructions that will be added to the thank you page and emails.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'stripe_ins',
                                'type' => 'textarea',
                                'default' => 'Instructions that will be added to the thank you page and emails.'
                            ],
                            [
                                'name' => __('Environment', 'listar'),
                                'desc' => __('Place the payment gateway in test mode using test API keys.   ', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'stripe_env',
                                'type' => 'select',
                                'default' => 'sandbox',
                                'options' => [
                                    'sandbox' => __('Test Mode', 'listar'),
                                    'live' => __('Live Mode', 'listar')
                                ]
                            ],
                            [
                                'name' => __('API Key', 'listar'),
                                'desc' => __('Stripe authenticates your API requests using your account\'s API keys', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'stripe_api_key',
                                'type' => 'text',
                                'default' => ''
                            ]
                        ],
                    ],
                    'cash' => [
                        'title' => __('Cash', 'listar'),
                        'options' => [
                            [
                                'name' => __('Enable', 'listar'),
                                'desc' => __('Use cash on delivery', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'cash_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Title', 'listar'),
                                'desc' => __('This controls the title which the user sees during checkout.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'cash_title',
                                'type' => 'text',
                                'default' => 'Cash On Delivery'
                            ],
                            [
                                'name' => __('Description', 'listar'),
                                'desc' => __('Payment method description that the customer will see on your checkout.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'cash_desc',
                                'type' => 'text',
                                'default' => 'Pay with cash upon delivery.'
                            ],
                            [
                                'name' => __('Instructions', 'listar'),
                                'desc' => __('Instructions that will be added to the thank you page and emails.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'cash_ins',
                                'type' => 'textarea',
                                'default' => 'Pay with cash upon delivery.'
                            ],
                        ]
                    ],
                    'bank' => [
                        'title' => __('Bank Transfer', 'listar'),
                        'options' => [
                            [
                                'name' => __('Enable', 'listar'),
                                'desc' => __('Use bank transfer', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'bank_use',
                                'type' => 'checkbox',
                            ],
                            [
                                'name' => __('Title', 'listar'),
                                'desc' => __('This controls the title which the user sees during checkout.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'bank_title',
                                'type' => 'text',
                                'default' => 'Direct Bank Transfer'
                            ],
                            [
                                'name' => __('Description', 'listar'),
                                'desc' => __('Payment method description that the customer will see on your checkout.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'bank_desc',
                                'type' => 'text',
                                'default' => 'Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.'
                            ],
                            [
                                'name' => __('Instructions', 'listar'),
                                'desc' => __('Instructions that will be added to the thank you page and emails.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'bank_ins',
                                'type' => 'textarea',
                                'default' => 'Instructions that will be added to the thank you page and emails.'
                            ],
                            [
                                'name' => __('Account Details', 'listar'),
                                'desc' => '',
                                'id'   =>  Listar::$option_prefix. 'bank_account_list',
                                'type' => 'sortable',
                                'headers' => [
                                    'acc_name' => __('Account Name'),
                                    'acc_number' => __('Account Number'),
                                    'bank_name' => __('Bank Name'),
                                    'bank_sort_code' => __('Sort code'),
                                    'bank_iban' => __('IBAN'),
                                    'bank_swift' => __('Swift Code'),
                                ],
                                'default' => ''
                            ],
                        ]
                    ],
                ]
            ],
            'notification' => [
                'title' => __('Notification', 'listar'),
                'default_section' => 'general', // default
                'sections' => [
                    'general' => [
                        'title' => __('General', 'listar'),
                        'options' => [
                            [
                                'name' => __('Email', 'listar'),
                                'desc' => __('Send email notification', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Email Recipient(s)', 'listar'),
                                'desc' => __('The email recipients who will receive a notification when the user processes a booking can be customized. Enter the email addresses separated by commas.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_recipient',
                                'type' => 'text',
                                'default' => ''
                            ],
                            [
                                'name' => __('Email Debug', 'listar'),
                                'desc' => __('Log the history what email data were sent.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_debug_use',
                                'type' => 'checkbox',
                                'default' => false
                            ],
                            [
                                'name' => __('Mobile Push', 'listar'),
                                'desc' => __('Send a push notification to user\'s mobile device', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'mobile_push_use',
                                'type' => 'checkbox',
                                'default' => false,
                            ],
                            [
                                'name' => __('Single Device Use', 'listar'),
                                'desc' => __('The account only gets the push notification with the last device login.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_single_device',
                                'type' => 'checkbox',
                            ],
                            [
                                'name' => __('Firebase Project ID', 'listar'),
                                'desc' => __('This is a user-defined unique identifier for the project across all of Firebase', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'fcm_project_id',
                                'type' => 'text',
                            ],
                            [
                                'name' => __('Firebase Debug', 'listar'),
                                'desc' => __('Use the debug mode', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'mobile_push_debug_use',
                                'type' => 'checkbox',
                                'default' => false,
                            ],
                            [
                                'name' => __('Firebase Debug Token', 'listar'),
                                'desc' => __('The notification will be sent to the token. Please enter the mobile push token key, separated by commas. Ex: abc,xyz', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'mobile_push_debug_token',
                                'type' => 'textarea',
                            ],
                            [
                                'name' => __('Queueing Notifications', 'listar'),
                                'desc' => __('Messages are stored on the queue until they are processed.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'queue_notification',
                                'type' => 'checkbox',
                                'default' => false,
                            ]
                        ]
                    ],
                    'order' => [
                        'title' => __('New Booking', 'listar'),
                        'options' => [
                            [
                                'name' => __('Email', 'listar'),
                                'desc' => __('Send to the owner who submitted the listing when user make a new booking.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_order_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Email To User', 'listar'),
                                'desc' => __('Send to the user when they make a new booking.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_order_user_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('CC', 'listar'),
                                'desc' => __('Please enter the email addresses separated by commas.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_order_recipient',
                                'type' => 'text',
                                'default' => ''
                            ],
                            [
                                'name' => __('Subject', 'listar'),
                                'desc' => __('Available placeholders: {site_title}, {site_url}, {created_on}, {booking_id}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_order_subject',
                                'type' => 'text',
                                'default' => 'New booking {title}'
                            ],
                            [
                                'name' => __('Content', 'listar'),
                                'desc' => __('Text to appear below the main email content. Available placeholders: {site_title}, {site_url}, {created_on}, {booking_id}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_order_content',
                                'type' => 'wysiwyg',
                                'default' => 'Congratulations on the sale: {title}'
                            ],
                            [
                                'name' => __('Email Type', 'listar'),
                                'desc' => __('Choose which format of email to send.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_order_type',
                                'type' => 'select',
                                'default' => 'html',
                                'options' => [
                                    'html' => __('HTML', 'listar'),
                                    'text' => __('Plain Text', 'listar')
                                ]
                            ],
                            [
                                'name' => __('Mobile Push', 'listar'),
                                'desc' => __('Send to the owner who submitted the listing when user make a new booking.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_order_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Mobile Push Content', 'listar'),
                                'desc' => __('Input content with plain text format', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_order_content',
                                'type' => 'textarea',
                                'default' => 'Congratulations on the sale {title} {total} {currency}'
                            ]
                        ],
                    ],
                    'cancel' => [
                        'title' => __('Canceled Booking', 'listar'),
                        'options' => [
                            [
                                'name' => __('Email', 'listar'),
                                'desc' => __('Send push notifications both to the owner & the user when the booking is cancelled', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_cancel_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('CC', 'listar'),
                                'desc' => __('Please enter the email addresses separated by commas.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_cancel_recipient',
                                'type' => 'text',
                                'default' => ''
                            ],
                            [
                                'name' => __('Subject', 'listar'),
                                'desc' => __('Available placeholders: {site_title}, {site_url}, {created_on}, {booking_id}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_cancel_subject',
                                'type' => 'text',
                                'default' => 'Booking {title} has been canceled'
                            ],
                            [
                                'name' => __('Content', 'listar'),
                                'desc' => __('Email content are sent to customers when their orders are marked canceled. Available placeholders: {site_title}, {site_url}, {created_on}, {booking_id}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_cancel_content',
                                'type' => 'wysiwyg',
                                'default' => 'Unfortunately, The booking {title} has been canceled'
                            ],
                            [
                                'name' => __('Email Type', 'listar'),
                                'desc' => __('Choose which format of email to send.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_cancel_type',
                                'type' => 'select',
                                'default' => 'html',
                                'options' => [
                                    'html' => __('HTML', 'listar'),
                                    'text' => __('Plain Text', 'listar')
                                ]
                            ],
                            [
                                'name' => __('Mobile Push', 'listar'),
                                'desc' => __('Send push notifications both to the owner & the user when the booking is cancelled', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_cancel_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Mobile Push Content', 'listar'),
                                'desc' => __('Input content with plain text format', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_cancel_content',
                                'type' => 'textarea',
                                'default' => 'Unfortunately, The booking {title} has been canceled'
                            ]
                        ],
                    ],
                    'fail' => [
                        'title' => __('Failed Booking', 'listar'),
                        'options' => [
                            [
                                'name' => __('Email', 'listar'),
                                'desc' => __('Send push notifications both to the owner & the user when the complete payment step fails.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_fail_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('CC', 'listar'),
                                'desc' => __('Please enter the email addresses separated by commas.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_fail_recipient',
                                'type' => 'text',
                                'default' => ''
                            ],
                            [
                                'name' => __('Subject', 'listar'),
                                'desc' => __('Available placeholders: {site_title}, {site_url}, {created_on}, {booking_id}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_fail_subject',
                                'type' => 'text',
                                'default' => 'Booking {title} has failed'
                            ],
                            [
                                'name' => __('Content', 'listar'),
                                'desc' => __('Email content are sent to customers when their orders are marked failed. Available placeholders: {site_title}, {site_url}, {created_on}, {booking_id}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_fail_content',
                                'type' => 'wysiwyg',
                                'default' => 'Unfortunately, The booking ID {booking_id} has been failed'
                            ],
                            [
                                'name' => __('Email Type', 'listar'),
                                'desc' => __('Choose which format of email to send.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_fail_type',
                                'type' => 'select',
                                'default' => 'html',
                                'options' => [
                                    'html' => __('HTML', 'listar'),
                                    'text' => __('Plain Text', 'listar')
                                ]
                            ],
                            [
                                'name' => __('Mobile Push', 'listar'),
                                'desc' => __('Send push notifications both to the owner & the user when the complete payment step fails.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_fail_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Mobile Push Content', 'listar'),
                                'desc' => __('Input content with plain text format', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_fail_content',
                                'type' => 'textarea',
                                'default' => 'Unfortunately, The booking {title} has been failed'
                            ]
                        ],
                    ],
                    'complete' => [
                        'title' => __('Completed Booking', 'listar'),
                        'options' => [
                            [
                                'name' => __('Email', 'listar'),
                                'desc' => __('Send push notifications both to the owner & the user', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_complete_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('CC', 'listar'),
                                'desc' => __('Please enter the email addresses separated by commas.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_complete_recipient',
                                'type' => 'text',
                                'default' => ''
                            ],
                            [
                                'name' => __('Subject', 'listar'),
                                'desc' => __('Available placeholders: {site_title}, {site_url}, {created_on}, {booking_id}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_complete_subject',
                                'type' => 'text',
                                'default' => 'Completed booking: {title}'
                            ],
                            [
                                'name' => __('Content', 'listar'),
                                'desc' => __('Email content are sent to customers when their orders are marked completed. Available placeholders: {site_title}, {site_url}, {created_on}, {booking_id}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_complete_content',
                                'type' => 'wysiwyg',
                                'default' => 'The booking ID {booking_id} have completed for the payment'
                            ],
                            [
                                'name' => __('Email Type', 'listar'),
                                'desc' => __('Choose which format of email to send.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_complete_type',
                                'type' => 'select',
                                'default' => 'html',
                                'options' => [
                                    'html' => __('HTML', 'listar'),
                                    'text' => __('Plain Text', 'listar')
                                ]
                            ],
                            [
                                'name' => __('Mobile Push', 'listar'),
                                'desc' => __('Send push notifications both to the owner & the user', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_complete_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Mobile Push Content', 'listar'),
                                'desc' => __('Input content with plain text format', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_complete_content',
                                'type' => 'textarea',
                                'default' => 'The booking {title} have completed for the payment'
                            ]
                        ],
                    ],
                    'status' => [
                        'title' => __('Status Booking', 'listar'),
                        'options' => [
                            [
                                'name' => __('Email', 'listar'),
                                'desc' => __('Send to the user when the booking status is changed', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_status_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('CC', 'listar'),
                                'desc' => __('Please enter the email addresses separated by commas. The default is receiver who requested booking', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_status_recipient',
                                'type' => 'text',
                                'default' => ''
                            ],
                            [
                                'name' => __('Subject', 'listar'),
                                'desc' => __('Available placeholders: {site_title}, {site_url}, {created_on}, {booking_id}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_status_subject',
                                'type' => 'text',
                                'default' => 'Change booking status {booking_id}'
                            ],
                            [
                                'name' => __('Content', 'listar'),
                                'desc' => __('Email content are sent to customers when their orders are changed status. Available placeholders: {site_title}, {site_url}, {created_on}, {booking_id}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_status_content',
                                'type' => 'wysiwyg',
                                'default' => 'Your booking status has changed from {from_status} to {to_status}'
                            ],
                            [
                                'name' => __('Email Type', 'listar'),
                                'desc' => __('Choose which format of email to send.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_status_type',
                                'type' => 'select',
                                'default' => 'html',
                                'options' => [
                                    'html' => __('HTML', 'listar'),
                                    'text' => __('Plain Text', 'listar')
                                ]
                            ],
                            [
                                'name' => __('Mobile Push', 'listar'),
                                'desc' => __('Send to the user when the booking status is changed', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_status_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Mobile Push Content', 'listar'),
                                'desc' => __('Input content with plain text format', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_status_content',
                                'type' => 'textarea',
                                'default' => 'Your booking status has changed from {from_status} to {to_status}'
                            ],
                        ],
                    ],     
                    'claim_submit' => [
                        'title' => __('Claim Submit', 'listar'),
                        'options' => [
                            [
                                'name' => __('Email', 'listar'),
                                'desc' => __('Send to administrators when the new listing is submitted', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_submit_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Email Recipient(s)', 'listar'),
                                'desc' => __('Please enter the email addresses of administrators separated by commas.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_submit_recipient',
                                'type' => 'text',
                                'default' => ''
                            ],
                            [
                                'name' => __('Subject', 'listar'),
                                'desc' => __('Available placeholders: {site_title}, {site_url}, {created_on}, {post_title}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_submit_subject',
                                'type' => 'text',
                                'default' => 'Submit Listing - {post_title}'
                            ],
                            [
                                'name' => __('Content', 'listar'),
                                'desc' => __('Text to appear below the main email content. Available placeholders: {site_title}, {site_url}, {created_on}, {post_title}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_submit_content',
                                'type' => 'wysiwyg',
                                'default' => 'The new listing is submitted: {post_title}.'
                            ],
                            [
                                'name' => __('Email Type', 'listar'),
                                'desc' => __('Choose which format of email to send.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_submit_type',
                                'type' => 'select',
                                'default' => 'html',
                                'options' => [
                                    'html' => __('HTML', 'listar'),
                                    'text' => __('Plain Text', 'listar')
                                ]
                            ],
                            [
                                'name' => __('Mobile Push', 'listar'),
                                'desc' => __('Send to administrators when the new listing is submitted', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_claim_submit_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Mobile Push Content', 'listar'),
                                'desc' => __('Input content with plain text format', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_claim_submit_content',
                                'type' => 'textarea',
                                'default' => 'The new listing is submitted: {post_title}.'
                            ],
                        ],
                    ],               
                    'claim_request' => [
                        'title' => __('Claim Request', 'listar'),
                        'options' => [
                            [
                                'name' => __('Email', 'listar'),
                                'desc' => __('Send to the owner who submitted the listing when user make a claim request.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_request_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Email To User', 'listar'),
                                'desc' => __('Send to the user when they make a request to claim.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_request_user_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('CC', 'listar'),
                                'desc' => __('Please enter the email addresses separated by commas.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_request_recipient',
                                'type' => 'text',
                                'default' => ''
                            ],
                            [
                                'name' => __('Subject', 'listar'),
                                'desc' => __('Available placeholders: {site_title}, {site_url}, {created_on}, {post_title}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_request_subject',
                                'type' => 'text',
                                'default' => '[Claim Listing] {post_title}'
                            ],
                            [
                                'name' => __('Content', 'listar'),
                                'desc' => __('Text to appear below the main email content. Available placeholders: {site_title}, {site_url}, {created_on}, {post_title}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_request_content',
                                'type' => 'wysiwyg',
                                'default' => 'The new claim listing is submitted: {post_title}.
                                    - Name: {billing_first_name} {billing_last_name}
                                    - Phone: {billing_phone}
                                    - Email: {author_email}
                                    - Memo: {memo}
                                '
                            ],
                            [
                                'name' => __('Email Type', 'listar'),
                                'desc' => __('Choose which format of email to send.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_request_type',
                                'type' => 'select',
                                'default' => 'html',
                                'options' => [
                                    'html' => __('HTML', 'listar'),
                                    'text' => __('Plain Text', 'listar')
                                ]
                            ],
                            [
                                'name' => __('Mobile Push', 'listar'),
                                'desc' => __('Send to administrators when the new listing is submitted', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_claim_request_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Mobile Push Content', 'listar'),
                                'desc' => __('Input content with plain text format', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_claim_request_content',
                                'type' => 'textarea',
                                'default' => 'The new claim listing is submitted: {post_title}'
                            ],
                        ],
                    ],
                    'claim_approve' => [
                        'title' => __('Claim Approved', 'listar'),
                        'options' => [
                            [
                                'name' => __('Email', 'listar'),
                                'desc' => __('Send to the user when their submitted listing is approved', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_approve_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Subject', 'listar'),
                                'desc' => __('Available placeholders: {site_title}, {site_url}, {created_on}, {post_title}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_approve_subject',
                                'type' => 'text',
                                'default' => 'Approved Listing - {post_title}'
                            ],
                            [
                                'name' => __('Content', 'listar'),
                                'desc' => __('Text to appear below the main email content. Available placeholders: {site_title}, {site_url}, {created_on}, {post_title}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_approve_content',
                                'type' => 'wysiwyg',
                                'default' => 'The administrator has approved your listing: {post_title}.'
                            ],
                            [
                                'name' => __('Email Type', 'listar'),
                                'desc' => __('Choose which format of email to send.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_approve_type',
                                'type' => 'select',
                                'default' => 'html',
                                'options' => [
                                    'html' => __('HTML', 'listar'),
                                    'text' => __('Plain Text', 'listar')
                                ]
                            ],
                            [
                                'name' => __('Mobile Push', 'listar'),
                                'desc' => __('Send push notification to user when the listing has approved', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_claim_approve_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Mobile Push Content', 'listar'),
                                'desc' => __('Input content with plain text format', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_claim_approve_content',
                                'type' => 'textarea',
                                'default' => 'The administrator has approved your listing: {post_title}.'
                            ],
                        ],
                    ],
                    'claim_complete' => [
                        'title' => __('Claim Completed', 'listar'),
                        'options' => [
                            [
                                'name' => __('Email', 'listar'),
                                'desc' => __('Send push notifications both to the owner & the user', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_complete_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('CC', 'listar'),
                                'desc' => __('Please enter the email addresses separated by commas.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_complete_recipient',
                                'type' => 'text',
                                'default' => ''
                            ],
                            [
                                'name' => __('Subject', 'listar'),
                                'desc' => __('Available placeholders: {site_title}, {site_url}, {created_on}, {post_title}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_complete_subject',
                                'type' => 'text',
                                'default' => 'Completed payment: {post_title}'
                            ],
                            [
                                'name' => __('Content', 'listar'),
                                'desc' => __('Email content are sent to customers when their orders are marked completed. Available placeholders: {site_title}, {site_url}, {created_on}, {post_title}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_complete_content',
                                'type' => 'wysiwyg',
                                'default' => 'The claim: {post_title} have completed for the payment'
                            ],
                            [
                                'name' => __('Email Type', 'listar'),
                                'desc' => __('Choose which format of email to send.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_complete_type',
                                'type' => 'select',
                                'default' => 'html',
                                'options' => [
                                    'html' => __('HTML', 'listar'),
                                    'text' => __('Plain Text', 'listar')
                                ]
                            ],
                            [
                                'name' => __('Mobile Push', 'listar'),
                                'desc' => __('Send push notifications both to the owner & the user', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_claim_complete_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Mobile Push Content', 'listar'),
                                'desc' => __('Input content with plain text format', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_claim_complete_content',
                                'type' => 'textarea',
                                'default' => 'Completed payment: {post_title}'
                            ]
                        ],
                    ],
                    'claim_cancel' => [
                        'title' => __('Claim Cancelled', 'listar'),
                        'options' => [
                            [
                                'name' => __('Email', 'listar'),
                                'desc' => __('Send push notifications both to the owner & the user when the claim is cancelled', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_cancel_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('CC', 'listar'),
                                'desc' => __('Please enter the email addresses separated by commas.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_cancel_recipient',
                                'type' => 'text',
                                'default' => ''
                            ],
                            [
                                'name' => __('Subject', 'listar'),
                                'desc' => __('Available placeholders: {site_title}, {site_url}, {created_on}, {post_title}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_cancel_subject',
                                'type' => 'text',
                                'default' => 'The claim request for {title} has been canceled'
                            ],
                            [
                                'name' => __('Content', 'listar'),
                                'desc' => __('Email content are sent to customers when their orders are marked canceled. Available placeholders: {site_title}, {site_url}, {created_on}, {post_title}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_cancel_content',
                                'type' => 'wysiwyg',
                                'default' => 'Unfortunately, The claim request for {title} has been canceled'
                            ],
                            [
                                'name' => __('Email Type', 'listar'),
                                'desc' => __('Choose which format of email to send.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_claim_cancel_type',
                                'type' => 'select',
                                'default' => 'html',
                                'options' => [
                                    'html' => __('HTML', 'listar'),
                                    'text' => __('Plain Text', 'listar')
                                ]
                            ],
                            [
                                'name' => __('Mobile Push', 'listar'),
                                'desc' => __('Send push notifications both to the owner & the user when the claim is cancelled', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_claim_cancel_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Mobile Push Content', 'listar'),
                                'desc' => __('Input content with plain text format', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_claim_cancel_content',
                                'type' => 'textarea',
                                'default' => 'Unfortunately, The claim request for {title} has been canceled'
                            ]
                        ],
                    ],                    
                    'publish_new' => [
                        'title' => __('Published Listing', 'listar'),
                        'options' => [
                            [
                                'name' => __('Mobile Push', 'listar'),
                                'desc' => __('Send a mobile push notification to all users when the new listing is published', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_create_new',
                                'type' => 'checkbox'
                            ],
                            [
                                'name' => __('Title', 'listar'),
                                'desc' => __("The mobile push notification title", 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_create_new_title',
                                'type' => 'text'
                            ],
                            [
                                'name' => __('Content', 'listar'),
                                'desc' => __("The mobile push notification content", 'listar')
                                ,
                                'id'   =>  Listar::$option_prefix. 'push_create_new_content',
                                'type' => 'textarea'
                            ]
                        ]
                    ],
                    'update_exist' => [
                        'title' => __('Updated Listing', 'listar'),
                        'options' => [
                            [
                                'name' => __('Mobile Push', 'listar'),
                                'desc' => __('Send a mobile push notification to all users when the listing is updated', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_update_exist',
                                'type' => 'checkbox'
                            ],
                            [
                                'name' => __('Title', 'listar'),
                                'desc' => __("The mobile push notification title", 'listar'),
                                'id'   =>  Listar::$option_prefix. 'push_update_exist_title',
                                'type' => 'text'
                            ],
                            [
                                'name' => __('Content', 'listar'),
                                'desc' => __("The mobile push notification content", 'listar')
                                ,
                                'id'   =>  Listar::$option_prefix. 'push_update_exist_content',
                                'type' => 'textarea'
                            ],
                        ]
                    ],                      
                    'reset_password' => [
                        'title' => __('Reset Password', 'listar'),
                        'options' => [
                            [
                                'name' => __('Email', 'listar'),
                                'desc' => __('Send to the user when they reset the password. It will replace the default content reset password of the system.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_reset_password_use',
                                'type' => 'checkbox',
                                'default' => false,
                            ],
                            [
                                'name' => __('Subject', 'listar'),
                                'desc' => __('Available placeholders: {site_title}, {site_url}, {created_on}, {post_title}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_reset_password_subject',
                                'type' => 'text',
                                'default' => '[{site_title}] Password Reset'
                            ],
                            [
                                'name' => __('Content', 'listar'),
                                'desc' => __('Text to appear below the main email content. Available placeholders: {site_title}, {site_url}, {user_login}, {user_email}, {user_nicename}, {display_name}', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email_reset_password_content',
                                'type' => 'wysiwyg',
                                'default' => 'Someone requested that the password be reset for the following account: {user_login} - {user_email}.'
                            ]
                        ],
                    ]                                                 
                ]
            ],
            'option' => [
                'title' => __('Settings', 'listar'),
                'default_section' => 'general', // default
                'sections' => [
                    'general' => [
                        'title' => __('General', 'listar'),
                        'options' => [
                            [
                                'name' => __('Default Image', 'listar'),
                                'desc' => __('When user do not set the featured image then this image will be used as default for show.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'default_image',
                                'type' => 'image',
                                'default' => Listar::plugin_url(). '/assets/images/default-featured-image.png',
                            ],  
                            [
                                'name' => __('Label', 'listar'),
                                'desc' => __('The label of left side menu', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'label',
                                'type' => 'text',
                                'default' => 'Listar'
                            ],
                            [
                                'name' => __('URL', 'listar'),
                                'desc' => __('The rewrite URL of directory listing page. Example http://yourdomain.com/listing', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'rewrite',
                                'type' => 'text',
                                'default' => 'listar-listing'
                            ],
                            [
                                'name' => __('Category URL', 'listar'),
                                'desc' => __('The rewrite URL of category page. Example http://yourdomain.com/subject', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'category_rewrite',
                                'type' => 'text',
                                'default' => 'listar-subject'
                            ],
                            [
                                'name' => __('Location URL', 'listar'),
                                'desc' => __('The rewrite URL of location page. Example http://yourdomain.com/location', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'location_rewrite',
                                'type' => 'text',
                                'default' => 'listar-location'
                            ],
                            [
                                'name' => __('Feature URL', 'listar'),
                                'desc' => __('The rewrite URL of feature page. Example http://yourdomain.com/feature', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'feature_rewrite',
                                'type' => 'text',
                                'default' => 'listar-feature'
                            ],
                            [
                                'name' => __('Booking URL', 'listar'),
                                'desc' => __('The rewrite URL of booking page. Example http://yourdomain.com/booking', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'booking_rewrite',
                                'type' => 'text',
                                'default' => 'listar-booking'
                            ],
                            [
                                'name' => __('Team URL', 'listar'),
                                'desc' => __('The rewrite URL of booking page. Example http://yourdomain.com/team', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'team_rewrite',
                                'type' => 'text',
                                'default' => 'listar-team'
                            ],
                            [
                                'name' => __('Blog Style', 'listar'),
                                'desc' => __('Blog style on the web theme', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'blog_list_mode',
                                'type' => 'select',
                                'default' => 'list',
                                'options' => [
                                    'list' => __('List', 'listar'),
                                    'grid' => __('Grid', 'listar'),
                                    'block' => __('Block', 'listar'),
                                ]
                            ],
                            [
                                'name' => __('Listing Style', 'listar'),
                                'desc' => __('Listing style on the web theme', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'listing_list_mode',
                                'type' => 'select',
                                'default' => 'list',
                                'options' => [
                                    'list' => __('List', 'listar'),
                                    'grid' => __('Grid', 'listar'),
                                ]
                            ],
                            [
                                'name' => __('Default Icon', 'listar'),
                                'desc' => __('The default icon will be used', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'icon',
                                'type' => 'text',
                                'default' => 'fa fa-star',
                            ],                                                                                   
                            [
                                'name' => __('Color Default', 'listar'),
                                'desc' => __('Then default color will be used', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'color',
                                'type' => 'text',
                                'default' => '#E5634D',
                            ],
                            [
                                'name' => __('Color Option', 'listar'),
                                'desc' => __('The list color for select when user add new listing. Color code is separated by the comma.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'color_option',
                                'type' => 'text',
                                'default' => '#E5634D, #5DADE2, #A569BD, #58D68D, #FDC60A, #3C5A99, #5D6D7E',
                            ],
                            [
                                'name' => __('Hide Media Menu', 'listar'),
                                'desc' => __('Hide the Media menu in admin page if the user logged in is not administrator.'),
                                'id'   =>  Listar::$option_prefix. 'hide_media',
                                'type' => 'checkbox'
                            ], 
                        ]
                    ],                    
                    'map' => [
                        'title' => __('Google Map', 'listar'),
                        'options' => [
                            [
                                'name' => __('Use Google Map', 'listar'),
                                'desc' => __('Select gps coordinator via Google Maps on the web browser.'),
                                'id'   =>  Listar::$option_prefix. 'map_use',
                                'type' => 'checkbox'
                            ],
                            [
                                'name' => __('Google Map API Key', 'listar'),
                                'desc' => __('Creating API keys (https://developers.google.com/maps/documentation/embed/get-api-key)', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'gmap_key',
                                'default' => '',
                                'type' => 'text',
                            ],
                            [
                                'name' => __('Zoom', 'listar'),
                                'desc' => __('The resolution of the current view. Zoom levels are between 0 (the entire world can be seen on one map) and 21+', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'gmap_zoom',
                                'type' => 'text',
                                'default' => 10,
                            ],
                            [
                                'name' => __('Latitude', 'listar'),
                                'desc' => __('Geographic coordinate that specifies the north–south ', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'gmap_center_lat',
                                'type' => 'text',
                                'default' => 36.15387576915702,
                            ],
                            [
                                'name' => __('Longitude', 'listar'),
                                'desc' => __('Geographic coordinate that specifies the east–west', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'gmap_center_long',
                                'type' => 'text',
                                'default' => -115.15051603317261,
                            ],
                            [
                                'name' => __('Measure Distance Unit', 'listar'),
                                'desc' => __('The unit of distant from current user location to listing location', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'measure_distance_unit',
                                'default' => 'K',
                                'type' => 'select',
                                'options' => [
                                    'k' => __('km', 'listar'),
                                    'm' => __('miles', 'listar'),
                                ]
                            ],
                        ]
                    ],
                    'view' => [
                        'title' => __('Display Fields', 'listar'),
                        'options' => [
                            [
                                'name' => __('Address', 'listar'),
                                'desc' => __('', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'view_address_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Phone', 'listar'),
                                'desc' => __('', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'view_phone_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Fax', 'listar'),
                                'desc' => __('', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'view_fax_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Email', 'listar'),
                                'desc' => __('', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'view_email_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Website', 'listar'),
                                'desc' => __('', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'view_website_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Status', 'listar'),
                                'desc' => __('', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'view_status_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Date Establish', 'listar'),
                                'desc' => __('', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'view_date_establish_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Galleries', 'listar'),
                                'desc' => __('', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'view_galleries_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Attachment', 'listar'),
                                'desc' => __('', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'view_attachment_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Map', 'listar'),
                                'desc' => __('', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'view_map_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Price', 'listar'),
                                'desc' => __('', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'view_price_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Open Hours', 'listar'),
                                'desc' => __('', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'view_opening_hour_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Tags', 'listar'),
                                'desc' => __('', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'view_tags_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Facilities', 'listar'),
                                'desc' => __('', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'view_feature_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Video', 'listar'),
                                'desc' => __('', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'view_video_url_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Admob', 'listar'),
                                'desc' => __('', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'view_admob_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                            [
                                'name' => __('Social Network', 'listar'),
                                'desc' => __('', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'social_network_use',
                                'type' => 'checkbox',
                                'default' => true,
                            ],
                        ]
                    ],
                    'open_hour' => [
                        'title' => __('Open Hours', 'listar'),
                        'options' => [
                            [
                                'name' => __('Check Current Time', 'listar'),
                                'desc' => __('Check the current time to show the status name for opening/closed.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'open_hour_status',
                                'type' => 'checkbox',
                                'default' => false,
                            ],
                            [
                                'name' => __('Status Opening', 'listar'),
                                'desc' => __('The status name is used for the current time in the open time period.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'open_hour_status_open',
                                'type' => 'text',
                                'default' => __('Opening', 'listar'),
                            ],
                            [
                                'name' => __('Status Closed', 'listar'),
                                'desc' => __('The status name is used for the current time out of the open time period.', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'open_hour_status_close',
                                'type' => 'text',
                                'default' => __('Closed', 'listar'),
                            ],
                        ]
                    ],
                    'search' => [
                        'title' => __('Searching'),
                        'options' => [
                            [
                                'name' => __('Min Price', 'listar'),
                                'desc' => __('Searching min price', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'price_min',
                                'type' => 'text',
                                'default' => 1,
                            ],
                            [
                                'name' => __('Max Price', 'listar'),
                                'desc' => __('Searching max price', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'price_max',
                                'type' => 'text',
                                'default' => 100,
                            ],
                            [
                                'name' => __('Min Time', 'listar'),
                                'desc' => __('Searching min time', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'time_min',
                                'type' => 'text',
                                'default' => '09:00',
                            ],
                            [
                                'name' => __('Max Time', 'listar'),
                                'desc' => 'Searching max time',
                                'id'   =>  Listar::$option_prefix. 'time_max',
                                'type' => 'text',
                                'default' => '18:00',
                            ],
                        ]
                    ],
                    'contact' => [
                        'title' => __('Contact Info', 'listar'),
                        'options' => [
                            [
                                'name' => __('Instruction', 'listar'),
                                'desc' => __('Enter the instruction text', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'instruction',
                                'type' => 'textarea'
                            ],
                            [
                                'name' => __('Phone', 'listar'),
                                'desc' => __('Enter the phone number', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'phone',
                                'type' => 'text'
                            ],
                            [
                                'name' => __('Email', 'listar'),
                                'desc' => __('Enter the email address number', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'email',
                                'type' => 'text',
                            ],
                            [
                                'name' => __('Address', 'listar'),
                                'desc' => __('Enter the address', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'address',
                                'type' => 'text',
                            ],
                            [
                                'name' => __('Copyright', 'listar'),
                                'desc' => __('Enter the footer copyright text', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'copyright',
                                'type' => 'text',
                                'default' => '@Copyright 2020. All rights reserved.'
                            ],
                            [
                                'name' => __('Facebook', 'listar'),
                                'desc' => __('Social network Facebook url', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'url_facebook',
                                'type' => 'text',
                                'default' => 'https://www.facebook.com/passionui',
                            ],
                            [
                                'name' => __('Instagram', 'listar'),
                                'desc' => __('Social network Instagram url', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'url_instagram',
                                'type' => 'text',
                                'default' => 'https://www.instagram.com/passionui',
                            ],
                            [
                                'name' => __('Youtube', 'listar'),
                                'desc' => __('Social network Youtube url', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'url_youtube',
                                'type' => 'text',
                                'default' => 'https://www.youtube.com/channel/UCt_7rXE3zgj_a_UbGCFUz6Q',
                            ],
                            [
                                'name' => __('Pinterest', 'listar'),
                                'desc' => __('Social network Pinterest url', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'url_pinterest',
                                'type' => 'text',
                                'default' => 'https://pinterest.com/passionui',
                            ],
                            [
                                'name' => __('Twitter', 'listar'),
                                'desc' => __('Social network Twitter url', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'url_twitter',
                                'type' => 'text',
                                'default' => 'https://pinterest.com/passionui',
                            ],
                            [
                                'name' => __('Adndroid App', 'listar'),
                                'desc' => __('Google Play url', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'url_android',
                                'type' => 'text',
                                'default' => 'https://play.google.com/store/apps/developer?id=Passion+UI',
                            ],
                            [
                                'name' => __('IOS App', 'listar'),
                                'desc' => __('AppStore url', 'listar'),
                                'id'   =>  Listar::$option_prefix. 'url_ios',
                                'type' => 'text',
                                'default' => 'https://play.google.com/store/apps/developer?id=Passion+UI',
                            ]
                        ]
                    ],
                ]
            ]
        ];

        $result = isset($options[$id]) ? $options[$id] : $options;

        if($section && !empty($result['sections']) && isset($result['sections'][$section])) {
            $result = $result['sections'][$section];
        }

        return $result;
    }

    /**
     * Get combine options
     * - by all
     * - by tab id
     * @param string $tab_id [If there have tab id > Just getting setting tab]
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    static function get_combine_options($tab_id = '') {
        $options = [];

        if($tab_id) {
            $data = self::get_options($tab_id);

            // Options
            if(isset($data['options']) && !empty($data['options'])) {
                foreach($data['options'] as $setting) {
                    $options[$setting['id']] = $setting;
                }
            }

            // Sections
            if(isset($data['sections']) && !empty($data['sections'])) {
                foreach ($data['sections'] as $section_id => $section) {
                    foreach($section['options'] as $setting) {
                        $options[$setting['id']] = $setting;
                    }
                }
            }
        } else {
            $data = self::get_options();
            foreach($data as $tab_id => $tab_data) {
                // Options
                if(isset($tab_data['options']) && !empty($tab_data['options'])) {
                    foreach ($tab_data['options'] as $setting) {
                        $options[$setting['id']] = $setting;
                    }
                }

                // Sections
                if(isset($tab_data['sections']) && !empty($tab_data['sections'])) {
                    foreach ($tab_data['sections'] as $section_id => $section) {
                        foreach($section['options'] as $setting) {
                            $options[$setting['id']] = $setting;
                        }
                    }
                }
            }
        }

        return $options;
    }

    /**
     * Install default value when the plugin is activated
     *
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    static function install() {
        $options = self::get_combine_options();

        // Check and insert default
        foreach($options as $option) {
            $value = get_option($option['id']);
            if(!$value) {
                update_option($option['id'], $option['default']);
            }
        }
    }

    /**
     * Get single option without prefix
     * Ex: id = 'payment_use'
     * Prefix 'listar_' auto add 'listar_payment_use'
     *
     * @param string $id
     * @return string
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    static function get_option($id = '') {
        $key = Listar::$option_prefix.$id;
        $option = get_option($key);

        // Get default option if can't found data setting
        if(!$option) {
            if(empty(self::$combine_options)) {
                self::$combine_options = self::get_combine_options();
            }

            $option = isset(self::$combine_options[$key]['default']) ?
                self::$combine_options[$key]['default'] : '';
        }

        // Fix boolean with string
        if($option === 'true' || $option === '1') {
            return TRUE;
        } else if($option === 'false') {
            return FALSE;
        }

        return $option;
    }

    /**
     * Get single option prefix
     * Ex: id = 'listar_payment_use'
     *
     * @param string $id
     * @return mixed
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    static function get_option_prefix($id = '') {
        $option = get_option($id);

        // Get default option if can't found data setting
        if(!$option) {
            if(empty(self::$combine_options)) {
                self::$combine_options = self::get_combine_options();
            }
            $option = isset(self::$combine_options[$id]['default']) ?
                self::$combine_options[$id]['default'] : '';
        }

        // Fix boolean with string
        if($option === 'true') {
            return TRUE;
        } else if($option === 'false') {
            return FALSE;
        }

        return $option;
    }

    /**
     * Get color option as array
     *
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     */
    static function get_color_option() {
        $color_option = self::get_option('color_option');
        $color_option = explode(',', $color_option);
        if(!empty($color_option)) {
            foreach($color_option as &$color) {
                $color = trim($color);
            }
        }

        return $color_option;
    }

    /**
     * Check condition set push notification
     * @return bool
     * @since 1.0.3
     */
    static function push_notification_on() {
        return self::get_option('push_status') && self::get_option('push_status') !== '';
    }

    /**
     * Check condition when create new
     * @return bool
     * @since 1.0.3
     */
    static function push_notification_crate_new() {
        return self::get_option('push_create_new');
    }

    /**
     * Check condition when update exist
     * @return bool
     * @since 1.0.3
     */
    static function push_notification_update_exit() {
        return self::get_option('push_update_exist');
    }

    /**
     * Push notification with single device (last device login same account
     * @since 1.0.12
     * @return bool|string
     */
    static function push_single_device() {
        return self::get_option('push_single_device');
    }

    /**
     * Get push notification
     * @param WP_Post $post
     * @param string $key setting title key
     * @return string
     * @since 1.0.3
     */
    static function push_notification_title($post, $key) {
        $push_title = self::get_option($key);
        if($push_title) {
            return self::push_notification_pattern($post, $push_title);
        }
        return $post->post_title;
    }

    /**
     * Get push notification
     * @param WP_Post $post
     * @param string $key setting title key
     * @return string
     * @since 1.0.3
     */
    static function push_notification_content($post, $key) {
        $push_content = self::get_option($key);
        if($push_content) {
            return self::push_notification_pattern($post, $push_content);
        }
        return sprintf('%s - %s', $post->address, $post->phone);
    }

    /**
     * Map data with string pattern
     *
     * @param WP_Post $post
     * @param string $string
     * @return string
     * @since 1.0.3
     */
    static function push_notification_pattern($post, $string = '') {
        $array = (array) $post;
        $string = @str_replace(array_map(function ($v) {return "{{$v}}";},
            array_keys($array)), $array, $string);
        return $string;
    }

    /**
     * get feature image
     * @return string
     */
    static function default_image()
    {
        // Check & set without recall in loop
        if(is_null(self::$default_image)) {
            $image = self::get_option('default_image');
            $image_value = (int)$image;
            if ($image_value > 0) { // has set value as int
                $image_large = wp_get_attachment_image_src($image_value, 'medium_large');
                if(!empty($image_large)) {
                    $image_medium = wp_get_attachment_image_src($image_value, 'medium');
                    $image_thumb = wp_get_attachment_image_src($image_value, );
                    $image = [
                        'id' => 0,
                        'full' => ['url' => $image_large[0]],
                        'medium' => ['url' => $image_medium[0]],
                        'thumb' => ['url' => $image_thumb[0]],
                    ];
                } else {
                    $default_image  = self::get_option('default_image');
                    $image = [
                        'id' => 0,
                        'full' => ['url' => $default_image],
                        'medium' => ['url' => $default_image],
                        'thumb' => ['url' => $default_image],
                    ];
                }
            } else {
                $image = [
                    'id' => 0,
                    'full' => ['url' => $image],
                    'medium' => ['url' => $image],
                    'thumb' => ['url' => $image],
                ];
            }
            self::$default_image = $image;
        }

        return self::$default_image;
    }

    /**
     * Check payment use
     * @return bool
     */
    static function payment_use()
    {
        return self::get_option('payment_use');
    }

    /**
     * Get payment method support list
     * @return array
     */
    static function payment_support_list()
    {
        $result = [];
        $list = ['paypal', 'stripe', 'bank', 'cash'];
        foreach($list as $payment_method) {
            if(self::get_option($payment_method.'_use')) {
                $result[] = [
                    'method' => $payment_method,
                    'title' => self::get_option($payment_method.'_title'),
                    'desc' => self::get_option($payment_method.'_desc'),
                    'instruction' => self::get_option($payment_method.'_ins')
                ];
            }
        }
        return $result;
    }

    /**
     * Check booking use
     * @return bool|string
     */
    static function booking_use()
    {
        return self::get_option('booking_use');
    }

    /**
     * Get OTP use
     * @return boolean
     */
    static function otp_use() 
    {
        return self::get_option('otp_use');
    }

    /**
     * Use queue message
     * @return bool
     */
    static function queue_notification()
    {
        return self::get_option('queue_notification');
    }

    /**
     * Format currency display
     * @param int $amount
     * @param string $symbol
     * @return int|string
     */
    static function currency_format($amount = 0, $symbol = '')
    {
        $value = $amount;
        $thousand_separator = self::get_option('unit_thousand_separator');
        $decimals = 2;
        $dec_point = ',';
        $separator = '';

        if($amount > 999 && $thousand_separator != 'none') {
            if($thousand_separator === 'dot') {
                $separator = '.';
                $dec_point = ',';
            } else if($thousand_separator === 'comma') {
                $separator = ',';
                $dec_point = '.';
            }

            if(is_float($amount)) {
                $value = number_format($amount, $decimals, $dec_point, $separator);
            } else if(is_int($amount)) {
                $value = number_format($amount, 0, '', $separator);
            }
        }

        $unit_position = self::get_option('unit_position');
        if($symbol) {
            $unit_symbol = $symbol;
        } else {
            $unit_symbol = self::get_option('unit_symbol');
        }

        switch ($unit_position) {
            case 'left':
                $value = sprintf('%s%s', $unit_symbol, $value);
                break;
            case 'right':
                $value = sprintf('%s%s', $value, $unit_symbol);
                break;
            case 'left_space':
                $value = sprintf('%s %s', $unit_symbol, $value);
                break;
            case 'right_space':
                $value = sprintf('%s %s', $value, $unit_symbol);
                break;
        }

        return $value;
    }

    /**
     * Allow user submit listing directory
     * @since 1.0.13
     * @return bool|string
     */
    public static function submit_listing_use()
    {
        return self::get_option('submit_listing_use');
    }

    /**
     * Check approval flow
     * - Default status is pending
     * @since 1.0.13
     * @return bool|string
     */
    public static function submit_listing_approval_use()
    {
        return self::get_option('submit_listing_approval_use');
    }


    /**
     * Get setting option
     * @param string $option
     * @return bool|string
     */
    public static function get_view_option($option = '') {
        return self::get_option($option);
    }
}
