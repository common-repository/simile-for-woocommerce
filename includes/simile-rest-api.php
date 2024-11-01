<?php
/**
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @author    scopemedia
 * @copyright Copyright © 2019, ScopeMedia, Inc. (contact@scopemedia.com)
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 * This file is part of Simile for WooCommerce.

 * Simile for WooCommerce is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * Simile for WooCommerce is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with Simile for WooCommerce.  If not, see <https://www.gnu.org/licenses/>.
 */

/*
 * This REST API implementation is heavily based on WooCommerce's REST API code
 * Refer to /woocommerce/includes/api/v2/class-wc-rest-products-v2-controller.php for detail
 */

defined( 'ABSPATH' ) or die( 'No direct access' ); // Exit if accessed directly

if ( ! class_exists( 'Simile_Rest_Api' ) ) {

    class Simile_Rest_Api {

        public function __construct () {


            add_action( 'rest_api_init', function () {
                // product list
                register_rest_route( SIMILE_TEXT_DOMAIN . '/v1', '/products', [
                    'methods' => 'GET',
                    'callback' => [ $this, 'prepare_products' ],
                    'args' => [
                        'page' => [
                            'validate_callback' => function ( $param, $request, $key ) {
                                return is_numeric( $param ) && intval( $param ) > 0;
                            }
                        ],
                        'size' => [
                            'validate_callback' => function ( $param, $request, $key ) {
                                return is_numeric( $param ) && intval( $param ) > 0 && intval( $param ) <= 250;
                            }
                        ],
                        'modified_after' => [
                            'validate_callback' => function ( $param, $request, $key ) {
                                return is_numeric( $param ) && intval( $param ) >= 0;
                            }
                        ],
                        'modified_before' => [
                            'validate_callback' => function ( $param, $request, $key ) {
                                return is_numeric( $param ) && intval( $param ) >= 0;
                            }
                        ]
                    ],
                    'permission_callback' => [ $this, 'verify_request' ]
                ] );

                // order list
                register_rest_route( SIMILE_TEXT_DOMAIN . '/v1', '/orders.json', [
                    'methods' => 'GET',
                    'callback' => [ $this, 'prepare_orders' ],
                    'args' => [
                        'page' => [
                            'validate_callback' => function ( $param, $request, $key ) {
                                return is_numeric( $param ) && intval( $param ) > 0;
                            }
                        ],
                        'size' => [
                            'validate_callback' => function ( $param, $request, $key ) {
                                return is_numeric( $param ) && intval( $param ) > 0 && intval( $param ) <= 250;
                            }
                        ],
                        'modified_after' => [
                            'validate_callback' => function ( $param, $request, $key ) {
                                return is_numeric( $param ) && intval( $param ) >= 0;
                            }
                        ],
                        'modified_before' => [
                            'validate_callback' => function ( $param, $request, $key ) {
                                return is_numeric( $param ) && intval( $param ) >= 0;
                            }
                        ]
                    ],
                    'permission_callback' => [ $this, 'verify_request' ]
                ] );

                // get widget settings
                register_rest_route( SIMILE_TEXT_DOMAIN . '/v1', '/getWidgetSettings', [
                    'methods' => 'GET',
                    'callback' => [ $this, 'get_widget_settings' ],
                    'args' => [

                    ],
                    'permission_callback' => [ $this, 'verify_request' ]
                ] );

                // set widget settings
                register_rest_route( SIMILE_TEXT_DOMAIN . '/v1', '/setWidgetSettings', [
                    'methods' => 'GET',
                    'callback' => [ $this, 'set_widget_settings' ],
                    'args' => [
                        'items' => [
                            'validate_callback' => function ( $param, $request, $key ) {
                                return is_numeric( $param ) && intval( $param ) >= 0 && intval( $param ) <= 70;
                            }
                        ],
                        'colums' => [
                            'validate_callback' => function ( $param, $request, $key ) {
                                return is_numeric( $param ) && intval( $param ) > 0 && intval( $param ) <= 10;
                            }
                        ]
                    ],
                    'permission_callback' => [ $this, 'verify_request' ]
                ] );

                // product count
                register_rest_route( SIMILE_TEXT_DOMAIN . '/v1', '/products/count', [
                    'methods' => 'GET',
                    'callback' => [ $this, 'prepare_product_count' ],
                    'args' => [
                        'modified_after' => [
                            'validate_callback' => function ( $param, $request, $key ) {
                                return is_numeric( $param ) && intval( $param ) >= 0;
                            }
                        ],
                        'modified_before' => [
                            'validate_callback' => function ( $param, $request, $key ) {
                                return is_numeric( $param ) && intval( $param ) >= 0;
                            }
                        ]
                    ],
                    'permission_callback' => [ $this, 'verify_request' ]
                ] );

                // product status
                register_rest_route( SIMILE_TEXT_DOMAIN . '/v1', '/products/status', [
                    'methods' => 'POST',
                    'callback' => [ $this, 'prepare_products_status' ],
                    'permission_callback' => [ $this, 'verify_request' ]
                ] );

                // widget version
                register_rest_route( SIMILE_TEXT_DOMAIN . '/v1', '/version', [
                    'methods' => 'GET',
                    'callback' => [ $this, 'get_widget_version' ],
//                    'permission_callback' => [ $this, 'verify_request' ]
                ] );

                // get tags
                register_rest_route( SIMILE_TEXT_DOMAIN . '/v1', '/gettags', [
                    'methods' => 'GET',
                    'callback' => [ $this, 'get_tags' ],
                    'args' => [

                    ],
                    'permission_callback' => [ $this, 'verify_request' ]
                ] );

                // visual search
                register_rest_route( SIMILE_TEXT_DOMAIN . '/v1', '/visualSearch', [
                    'methods' => 'POST',
                    'callback' => [ $this, 'visualSearch' ],
                    'args' => []
                ] );

            } );
        }

        function verify_request ( $request ) {
            $params = $request->get_params();
            if("POST" == $request->get_method()){
                $tokens = [];
                $allowed_keys = ['shop', 'timestamp', 'signature', 'page', 'size', 'modified_after', 'modified_before'];
                foreach ( $request->get_params() as $key => $value ) {
                    if (in_array(sanitize_key($key), $allowed_keys, true)) {
                        $tokens[$key] = $value;
                    }
                }
                $params = $tokens;
            }
            return Simile_Model::valid_hmac( $params );
        }

        function  visualSearch( $request ) {
            $response = Simile_Model::visual_search($request->get_params());
            return array_map( function ( $response ) {
                $productId = $response->itemId;
                $p = wc_get_product($productId);
                if($p != null){
                    return [
                        'productId'  => $productId,
                        'name' => $p->get_name(),
                        'link' => get_permalink( $productId ),
                        'image' => get_the_post_thumbnail_url($p->get_id()),
                        'price' => $p->get_price_html()
                    ];
                }
            }, $response );
            return $response;
        }

        function prepare_product_count ( $request ) {
            // any, publish, private, panding, draft
            $status = $request->get_param( 'status' ) ?: 'any';
            $modified_after = $request->get_param( 'modified_after' ) ?: '0';
            $modified_before = $request->get_param( 'modified_before' ) ?: '99999999999';

            $args = array(
                'post_type' => 'product',
                'post_status' => $status,
                'posts_per_page' => 1,
                'date_query' => [
                    [
                        'column' => 'post_modified_gmt',
                        'before' => date( 'c' , $modified_before ),
                        'after' => date( 'c' , $modified_after )
                    ]
                ]
            );
            $the_query = new WP_Query( $args );
            return [ 'count' => (int) $the_query->found_posts ];
        }

        function prepare_products_status ( $request ) {
            $productsList = json_decode( $request->get_body() );
            $res_array = [];
            foreach ( $productsList as $key => &$product_id ) {
                $product_id = $this->get_product_status_data($product_id);
                if($product_id){
                    $res_array[] = $product_id;
                }
            }
            return $res_array;
        }

        function get_widget_version(){
            $upsell = ( get_option( SIMILE_TEXT_DOMAIN.'_simile_enable') == "1")?true:false;
            $bundle = ( get_option( SIMILE_TEXT_DOMAIN.'_bundles_enable') !="")?true:false;
            $vs = ( get_option( SIMILE_TEXT_DOMAIN.'_simile_vs_enable') !='')?true:false;

            return [
                'widgetName'  => SIMILE_PLUGIN_NAME,
                'version'     => SIMILE_VERSION,
                'upsell' => $upsell,
                'bundle'=> $bundle,
                'visualSearch' =>$vs
            ];
        }

        private function get_product_status_data ($product_id ) {
            $product = wc_get_product($product_id);
            if(!$product) return;
            if(get_post_status( $product->get_id()) =="trash") return;
            $status = false;
            if(get_post_status( $product->get_id()) =="publish" && $product->is_in_stock() ){
                $status = true;
            }
            return [
                'productId'                    => $product->get_id(),
                'availableForSale'                => $status,
            ];
        }

        function prepare_products ( $request ) {
            $page = $request->get_param( 'page' ) ?: 1;
            $size = $request->get_param( 'size' ) ?: 20;
            // any, publish, private, panding, draft
            $status = $request->get_param( 'status' ) ?: 'any';
            // date_modified query is inclusive. need to offset by one second
            // for example after 124 and before 256 === 125 ~ 255
            $modified_after = $request->get_param( 'modified_after' ) ? intval( $request->get_param( 'modified_after' ) ) + 1 : '0';
            $modified_before = $request->get_param( 'modified_before' ) ? intval( $request->get_param( 'modified_before' ) ) - 1 : '99999999999';

            $products = wc_get_products( [
                'page' => $page,
                'limit' => $size,
                'status' => $status,
                'orderby' => 'modified',
                'date_modified' => "$modified_after...$modified_before"
            ] );
            foreach ( $products as &$product ) {
                $product = $this->get_product_data( $product );
            }
            return $products;
        }

        function prepare_orders ( $request ) {
            $page = $request->get_param( 'page' ) ?: 1;
            $size = $request->get_param( 'size' ) ?: 20;
            // any, publish, private, panding, draft
            $status = $request->get_param( 'status' ) ?: 'any';
            // date_modified query is inclusive. need to offset by one second
            // for example after 124 and before 256 === 125 ~ 255
            $modified_after = $request->get_param( 'modified_after' ) ? intval( $request->get_param( 'modified_after' ) ) + 1 : '0';
            $modified_before = $request->get_param( 'modified_before' ) ? intval( $request->get_param( 'modified_before' ) ) - 1 : '99999999999';

            $orders = wc_get_orders( [
                'page' => $page,
                'limit' => $size,
                'status' => $status,
                'orderby' => 'modified',
                'date_modified' => "$modified_after...$modified_before"
            ] );
            foreach ( $orders as &$order ) {
                $order = $this->get_order_data( $order );
            }
            return ['orders' => $orders];
        }

        function get_widget_settings(){
            $conifg= get_option( SIMILE_TEXT_DOMAIN . '_widget_config');
            return ['widgetSettings'=> $conifg];
        }

        function set_widget_settings($request){
            $items  = $request->get_param( 'items' ) ?: 8;
            $colums = $request->get_param( 'colums' ) ?: 4;
            $removeBanding = $request->get_param( 'removeBanding' ) ?: false;
            update_option( SIMILE_TEXT_DOMAIN . '_widget_config', [
                'items' => $items,
                'columns' => $colums,
                'removeBanding' => $removeBanding
            ] );
            return $this->get_widget_settings();
        }

        function get_tags(){
            $terms = get_terms(array('taxonomy' => 'product_tag', 'hide_empty' => false));
            return $terms;
        }

        private function get_order_data ( $order ) {
            // Generate a complete order object
            // Refer to function prepare_object_for_response and get_order_data
            $o = $order->get_data();
            return [
                    "id"=> $o['id'],
                    "email"=> $o['billing']['email'],
                    "created_at"=> $order->get_date_created()->format( 'Y-m-d\TH:i:sP') ,
                    "updated_at"=>  $order->get_date_modified()->format('Y-m-d\TH:i:sP') ,
                    "number"=> $o['id'],
                    "note"=> $o['customer_note'],
                    "token"=> $o['order_key'],
                    "gateway"=> $o['payment_method'],
                    "test"=> false,
                    "refunds" => null,
                    "total_price"=> $o['total'],
                    "subtotal_price"=> $o['total'],
                    "currency"=> $o['currency'],
                    "financial_status"=> $o['payment_method'],
                    "total_discounts"=> $o['discount_total'],
                    "total_line_items_price"=> $o['total'],
                    "name"=> "#".$o['id'],
                    "user_id"=> $o['customer_id'],
                    "phone"=> $o['billing']['phone'],
                    "browser_ip"=> $o['customer_ip_address'],
                    "order_number"=> $o['id'],
                    "contact_email"=> $o['billing']['email'],
                    "presentment_currency"=>  $o['currency'],
                    "total_line_items_price_set"=> [
                        "shop_money"=> [
                            "amount"=>  $o['total'],
                            "currency_code"=>  $o['currency']
                        ],
                        "presentment_money"=> [
                            "amount"=>  $o['total'],
                            "currency_code"=>  $o['currency']
                        ]
                    ],
                    "total_discounts_set"=> [
                        "shop_money"=> [
                            "amount"=> $o['discount_total'],
                            "currency_code"=> $o['currency']
                        ],
                        "presentment_money"=> [
                            "amount"=> $o['discount_total'],
                            "currency_code"=> $o['currency']
                        ]
                    ],
                    "total_shipping_price_set"=> [
                        "shop_money"=> [
                            "amount"=> $o['shipping_total'],
                            "currency_code"=> $o['currency']
                        ],
                        "presentment_money"=> [
                            "amount"=> $o['shipping_total'],
                            "currency_code"=> $o['currency']
                        ]
                    ],
                    "subtotal_price_set"=> [
                        "shop_money"=> [
                            "amount"=>  $o['total'],
                            "currency_code"=>  $o['currency']
                        ],
                        "presentment_money"=> [
                            "amount"=>  $o['total'],
                            "currency_code"=>  $o['currency']
                        ]
                    ],
                    "total_price_set"=> [
                        "shop_money"=> [
                            "amount"=>  $o['total'],
                            "currency_code"=>  $o['currency']
                        ],
                        "presentment_money"=> [
                            "amount"=>  $o['total'],
                            "currency_code"=>  $o['currency']
                        ]
                    ],
                    "total_tax_set"=> [
                        "shop_money"=> [
                            "amount"=>  $o['total_tax'],
                            "currency_code"=>  $o['currency']
                        ],
                        "presentment_money"=> [
                            "amount"=>  $o['total_tax'],
                            "currency_code"=>  $o['currency']
                        ]
                    ],
                    "line_items"=> $this->get_line_items($o),
                    "shipping_lines"=> $o['shipping_lines'],
                    "shipping_address"=> [
                        "first_name"=> $o['shipping']['first_name'],
                        "address1"=> $o['shipping']['address_1'],
                        "phone"=> $o['shipping']['phone'],
                        "city"=> $o['shipping']['city'],
                        "zip"=> $o['shipping']['postcode'],
                        "province"=> $o['shipping']['state'],
                        "country"=> $o['shipping']['country'],
                        "last_name"=> $o['shipping']['last_name'],
                        "address2"=> $o['shipping']['address_2'],
                        "company"=> $o['shipping']['company'],
                        "latitude"=> 0,
                        "longitude"=> 0,
                        "name"=> $o['shipping']['first_name']." ".$o['shipping']['last_name'],
                        "country_code"=> $o['shipping']['country'],
                        "province_code"=> $o['shipping']['state']
                    ],
                    "client_details"=> [
                        "browser_ip"=> $o['customer_ip_address'],
                        "accept_language"=> null,
                        "user_agent"=> $o['customer_user_agent'],
                        "session_hash"=> null,
                        "browser_width"=> null,
                        "browser_height"=> null
                    ]
            ];
        }

        private  function get_line_items($o) {
            $line_items_array =[];
            foreach ($o['line_items'] as &$item){
                $item_data =$item->get_data();
                if($item_data['product_id'] == null){
                    continue;
                }
                $product_data = $this->get_product_data(wc_get_product($item_data['product_id']));
                $line_items_array[]=[
                    "id"=> $item_data['product_id'],
                    "variant_id"=> $item_data['variation_id'],
                    "title"=> $item_data['name'],
                    "quantity"=> $item_data['quantity'],
                    "sku"=> $product_data['sku'],
                    "variant_title"=> $item_data['name'],
                    "product_id"=> $item_data['product_id'],
                    "requires_shipping"=> true,
                    "name"=> $item_data['name'],
                    "properties"=> $this->get_line_item_meta_data($item),
                    "price"=> $item_data['total'],
                    "total_discount"=> "0.00",
                    "price_set"=> [
                        "shop_money"=> [
                            "amount"=> $item_data['subtotal'],
                            "currency_code"=> $o['currency']
                        ],
                        "presentment_money"=> [
                            "amount"=> $item_data['subtotal'],
                            "currency_code"=> $o['currency']
                        ]
                    ]
                ];
            }
            return $line_items_array;

        }
        private function get_line_item_meta_data($meta_data){
            $item_meta_data_array =[];
            $meata_data_data  = $meta_data->get_data();
            if( isset($meata_data_data['meta_data']) && count($meata_data_data['meta_data'])>0 ) {
                foreach ($meata_data_data['meta_data'] as &$meta) {
                    $meta = $meta->get_data();
                    $item_meta_data_array[] = [
                        "name" => $meta['key'],
                        "value" => $meta['value'],
                    ];
                }
            }
            return $item_meta_data_array;

        }

        private function get_product_data ( $product ) {
            // Generate a complete product object
            // Refer to function prepare_object_for_response and get_product_data
            $context = 'view';
            return [
                'id'                    => $product->get_id(),
                'status'                => get_post_status( $product->get_id() ),
                'name'                  => $product->get_name( $context ),
                'slug'                  => $product->get_slug( $context ),
                'permalink'             => $product->get_permalink(),
                'date_created'          => wc_rest_prepare_date_response( $product->get_date_created( $context ), false ),
                'date_created_gmt'      => wc_rest_prepare_date_response( $product->get_date_created( $context ) ),
                'date_modified'         => wc_rest_prepare_date_response( $product->get_date_modified( $context ), false ),
                'date_modified_gmt'     => wc_rest_prepare_date_response( $product->get_date_modified( $context ) ),
                'type'                  => $product->get_type(),
                'featured'              => $product->is_featured(),
                'catalog_visibility'    => $product->get_catalog_visibility( $context ),
                'description'           => 'view' === $context ? wpautop( do_shortcode( $product->get_description() ) ) : $product->get_description( $context ),
                'short_description'     => 'view' === $context ? apply_filters( 'woocommerce_short_description', $product->get_short_description() ) : $product->get_short_description( $context ),
                'sku'                   => $product->get_sku( $context ),
                'price'                 => $product->get_price( $context ),
                'regular_price'         => $product->get_regular_price( $context ),
                'sale_price'            => $product->get_sale_price( $context ) ? $product->get_sale_price( $context ) : '',
                'date_on_sale_from'     => wc_rest_prepare_date_response( $product->get_date_on_sale_from( $context ), false ),
                'date_on_sale_from_gmt' => wc_rest_prepare_date_response( $product->get_date_on_sale_from( $context ) ),
                'date_on_sale_to'       => wc_rest_prepare_date_response( $product->get_date_on_sale_to( $context ), false ),
                'date_on_sale_to_gmt'   => wc_rest_prepare_date_response( $product->get_date_on_sale_to( $context ) ),
                'price_html'            => $product->get_price_html(),
                'on_sale'               => $product->is_on_sale( $context ),
                'purchasable'           => $product->is_purchasable(),
                'total_sales'           => $product->get_total_sales( $context ),
                'virtual'               => $product->is_virtual(),
                'downloadable'          => $product->is_downloadable(),
                'downloads'             => $this->get_downloads( $product ),
                'download_limit'        => $product->get_download_limit( $context ),
                'download_expiry'       => $product->get_download_expiry( $context ),
                'external_url'          => $product->is_type( 'external' ) ? $product->get_product_url( $context ) : '',
                'button_text'           => $product->is_type( 'external' ) ? $product->get_button_text( $context ) : '',
                'tax_status'            => $product->get_tax_status( $context ),
                'tax_class'             => $product->get_tax_class( $context ),
                'manage_stock'          => $product->managing_stock(),
                'stock_quantity'        => $product->get_stock_quantity( $context ),
                'in_stock'              => $product->is_in_stock(),
                'backorders'            => $product->get_backorders( $context ),
                'backorders_allowed'    => $product->backorders_allowed(),
                'backordered'           => $product->is_on_backorder(),
                'sold_individually'     => $product->is_sold_individually(),
                'weight'                => $product->get_weight( $context ),
                'dimensions'            => array(
                    'length' => $product->get_length( $context ),
                    'width'  => $product->get_width( $context ),
                    'height' => $product->get_height( $context ),
                ),
                'shipping_required'     => $product->needs_shipping(),
                'shipping_taxable'      => $product->is_shipping_taxable(),
                'shipping_class'        => $product->get_shipping_class(),
                'shipping_class_id'     => $product->get_shipping_class_id( $context ),
                'reviews_allowed'       => $product->get_reviews_allowed( $context ),
                'average_rating'        => 'view' === $context ? wc_format_decimal( $product->get_average_rating(), 2 ) : $product->get_average_rating( $context ),
                'rating_count'          => $product->get_rating_count(),
                'related_ids'           => array_map( 'absint', array_values( wc_get_related_products( $product->get_id() ) ) ),
                'upsell_ids'            => array_map( 'absint', $product->get_upsell_ids( $context ) ),
                'cross_sell_ids'        => array_map( 'absint', $product->get_cross_sell_ids( $context ) ),
                'parent_id'             => $product->get_parent_id( $context ),
                'purchase_note'         => 'view' === $context ? wpautop( do_shortcode( wp_kses_post( $product->get_purchase_note() ) ) ) : $product->get_purchase_note( $context ),
                'categories'            => $this->get_taxonomy_terms( $product ),
                'tags'                  => $this->get_taxonomy_terms( $product, 'tag' ),
                'images'                => $this->get_images( $product ),
                'attributes'            => $this->get_attributes( $product ),
                'default_attributes'    => $this->get_default_attributes( $product ),
                'variations'            => $product->is_type( 'variable' ) && $product->has_child() ? $product->get_available_variations() : [],
                'grouped_products'      => $product->is_type( 'grouped' ) && $product->has_child() ? $product->get_children() : [],
                'menu_order'            => $product->get_menu_order( $context ),
                'meta_data'             => $product->get_meta_data()
            ];
        }

        private function get_images( $product ) {
            $images         = array();
            $attachment_ids = array();

            // Add featured image.
            if ( $product->get_image_id() ) {
                $attachment_ids[] = $product->get_image_id();
            }

            // Add gallery images.
            $attachment_ids = array_merge( $attachment_ids, $product->get_gallery_image_ids() );

            // Build image data.
            foreach ( $attachment_ids as $position => $attachment_id ) {
                $attachment_post = get_post( $attachment_id );
                if ( is_null( $attachment_post ) ) {
                    continue;
                }

                $attachment = wp_get_attachment_image_src( $attachment_id, 'full' );
                if ( ! is_array( $attachment ) ) {
                    continue;
                }

                $images[] = array(
                    'id'                => (int) $attachment_id,
                    'date_created'      => wc_rest_prepare_date_response( $attachment_post->post_date, false ),
                    'date_created_gmt'  => wc_rest_prepare_date_response( strtotime( $attachment_post->post_date_gmt ) ),
                    'date_modified'     => wc_rest_prepare_date_response( $attachment_post->post_modified, false ),
                    'date_modified_gmt' => wc_rest_prepare_date_response( strtotime( $attachment_post->post_modified_gmt ) ),
                    'src'               => current( $attachment ),
                    'name'              => get_the_title( $attachment_id ),
                    'alt'               => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
                    'position'          => (int) $position,
                );
            }

            // Set a placeholder image if the product has no images set.
            if ( empty( $images ) ) {
                $images[] = array(
                    'id'                => 0,
                    'date_created'      => wc_rest_prepare_date_response( current_time( 'mysql' ), false ), // Default to now.
                    'date_created_gmt'  => wc_rest_prepare_date_response( current_time( 'timestamp', true ) ), // Default to now.
                    'date_modified'     => wc_rest_prepare_date_response( current_time( 'mysql' ), false ),
                    'date_modified_gmt' => wc_rest_prepare_date_response( current_time( 'timestamp', true ) ),
                    'src'               => wc_placeholder_img_src(),
                    'name'              => __( 'Placeholder', 'woocommerce' ),
                    'alt'               => __( 'Placeholder', 'woocommerce' ),
                    'position'          => 0,
                );
            }

            return $images;
        }

        private function get_taxonomy_terms( $product, $taxonomy = 'cat' ) {
            $terms = array();

            foreach ( wc_get_object_terms( $product->get_id(), 'product_' . $taxonomy ) as $term ) {
                $terms[] = array(
                    'id'   => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                );
            }

            return $terms;
        }

        private function get_downloads( $product ) {
            $downloads = array();

            if ( $product->is_downloadable() ) {
                foreach ( $product->get_downloads() as $file_id => $file ) {
                    $downloads[] = array(
                        'id'   => $file_id, // MD5 hash.
                        'name' => $file['name'],
                        'file' => $file['file'],
                    );
                }
            }

            return $downloads;
        }

        private function get_default_attributes( $product ) {
            $default = array();

            if ( $product->is_type( 'variable' ) ) {
                foreach ( array_filter( (array) $product->get_default_attributes(), 'strlen' ) as $key => $value ) {
                    if ( 0 === strpos( $key, 'pa_' ) ) {
                        $default[] = array(
                            'id'     => wc_attribute_taxonomy_id_by_name( $key ),
                            'name'   => $this->get_attribute_taxonomy_name( $key, $product ),
                            'option' => $value,
                        );
                    } else {
                        $default[] = array(
                            'id'     => 0,
                            'name'   => $this->get_attribute_taxonomy_name( $key, $product ),
                            'option' => $value,
                        );
                    }
                }
            }

            return $default;
        }

        private function get_attributes( $product ) {
            $attributes = array();

            if ( $product->is_type( 'variation' ) ) {
                $_product = wc_get_product( $product->get_parent_id() );
                foreach ( $product->get_variation_attributes() as $attribute_name => $attribute ) {
                    $name = str_replace( 'attribute_', '', $attribute_name );

                    if ( ! $attribute ) {
                        continue;
                    }

                    // Taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`.
                    if ( 0 === strpos( $attribute_name, 'attribute_pa_' ) ) {
                        $option_term  = get_term_by( 'slug', $attribute, $name );
                        $attributes[] = array(
                            'id'     => wc_attribute_taxonomy_id_by_name( $name ),
                            'name'   => $this->get_attribute_taxonomy_name( $name, $_product ),
                            'option' => $option_term && ! is_wp_error( $option_term ) ? $option_term->name : $attribute,
                        );
                    } else {
                        $attributes[] = array(
                            'id'     => 0,
                            'name'   => $this->get_attribute_taxonomy_name( $name, $_product ),
                            'option' => $attribute,
                        );
                    }
                }
            } else {
                foreach ( $product->get_attributes() as $attribute ) {
                    $attributes[] = array(
                        'id'        => $attribute['is_taxonomy'] ? wc_attribute_taxonomy_id_by_name( $attribute['name'] ) : 0,
                        'name'      => $this->get_attribute_taxonomy_name( $attribute['name'], $product ),
                        'position'  => (int) $attribute['position'],
                        'visible'   => (bool) $attribute['is_visible'],
                        'variation' => (bool) $attribute['is_variation'],
                        'options'   => $this->get_attribute_options( $product->get_id(), $attribute ),
                    );
                }
            }

            return $attributes;
        }

        private function get_attribute_taxonomy_name( $slug, $product ) {
            $attributes = $product->get_attributes();

            if ( ! isset( $attributes[ $slug ] ) ) {
                return str_replace( 'pa_', '', $slug );
            }

            $attribute = $attributes[ $slug ];

            // Taxonomy attribute name.
            if ( $attribute->is_taxonomy() ) {
                $taxonomy = $attribute->get_taxonomy_object();
                return $taxonomy->attribute_label;
            }

            // Custom product attribute name.
            return $attribute->get_name();
        }

        private function get_attribute_options( $product_id, $attribute ) {
            if ( isset( $attribute['is_taxonomy'] ) && $attribute['is_taxonomy'] ) {
                return wc_get_product_terms(
                    $product_id, $attribute['name'], array(
                        'fields' => 'names',
                    )
                );
            } elseif ( isset( $attribute['value'] ) ) {
                return array_map( 'trim', explode( '|', $attribute['value'] ) );
            }

            return array();
        }

    } // end class

} // end if class exists

new Simile_Rest_Api();

?>