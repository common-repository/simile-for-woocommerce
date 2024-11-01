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
 * Also check these to understand how WooCommerce single product page works
 * 1) https://businessbloomer.com/woocommerce-visual-hook-guide-single-product-page/
 * 2) https://docs.woocommerce.com/document/introduction-to-hooks-actions-and-filters/
 */

defined( 'ABSPATH' ) or die( 'No direct access' ); // Exit if accessed directly

if ( ! class_exists( 'Simile_Related_Products' ) ) {

    class Simile_Related_Products {

        public function __construct () {
            /* WooCommerce hooks available for displaying product recommendations
             * woocommerce_before_single_product (action): Hijack global product object in the beginning of single product page and update the product object.
             * woocommerce_related_products (filter): Alter related products array by returning new array.
             * woocommerce_product_upsell_ids (deprecated filter): Deprecated
             */
            // add_filter( 'woocommerce_related_products', [ $this, 'get_related_products' ], 10, 1 );
            add_filter( 'woocommerce_upsell_display_args', [ $this, 'get_up_sell_products' ], 10, 1 );
            add_action( 'woocommerce_before_product_object_save', [ $this,'update_manual_edit_products'], 10, 1 );
            add_action( 'woocommerce_after_template_part', [ $this,'template_fliter'], 10, 1 );
        }

        function template_fliter( $template_name ){
            if( $template_name == 'single-product/up-sells.php'){
                global $product;
                if( get_option( SIMILE_TEXT_DOMAIN . '_simile_remove_branding',0) != 1 &&
                    count($product->get_upsell_ids()) > 0 &&
                    get_option( SIMILE_TEXT_DOMAIN . '_simile_enable',1) == 1
                ) {
                    echo "<p style='font-size: 12px;text-align: right;margin-right: 20px;'>powered by <a href=\"https://www.simile.ai\" target=\"_blank\" style='color: inherit;text-decoration: none;'>Simile.ai</a></p>";
                }

                // relay server
                $this->simileAnalytics();
            }
        }

        function simileAnalytics(){
            global $product;
            $shop_credentials = get_option(SIMILE_TEXT_DOMAIN . '_shop_credentials');
            $simile_analytics_data = [
                'server'     => SIMILE_RELAY_URL,
                'shop_id'   => $shop_credentials['shop'],
                'widget_name' => 'SIMILE_WP',
                'client_ip' => Simile_Related_Products::get_the_user_ip(),
                'provider' => 'WP',
                'item_id'=> $product->get_id()
            ];
            wp_enqueue_script('simileAnalytics',Simile_Model::get_static_assets_url() . "assets/js/simileAnalytics.js");
            wp_localize_script('simileAnalytics', 'simile_analytics_data', $simile_analytics_data);
        }

        function get_up_sell_products ( $config ) {
            // if product page
            if (is_product() && get_option( SIMILE_TEXT_DOMAIN . '_simile_enable',1) == 1 ) {
                global $product;
                $similar_product_ids = $this->get_similar_upsell_product_ids( $product );
                if(count($similar_product_ids)>0){
                    // save to upsell field
                    if(!empty(array_diff($similar_product_ids,$product->get_upsell_ids()))){
                        update_post_meta( $product->get_id(), '_upsell_ids', $similar_product_ids );
                    }
                }
                // order by similar
                if(count($similar_product_ids)<=0){
                    $similar_product_ids = $product->get_upsell_ids();
                }
                $product->set_upsell_ids( array_reverse($similar_product_ids));
                $config['orderby']="none";
                $config['posts_per_page']=get_option( SIMILE_TEXT_DOMAIN . '_simile_items_in_page',6);
                $config['columns']=get_option( SIMILE_TEXT_DOMAIN . '_simile_columns_in_page',3);
            }
            return $config;
        }

        // not used ,legacy code
        function get_related_products_orginal ( $related_products ) {
            // if product page
            if (is_product()) {
                global $product;
                $similar_product_ids = $this->get_similar_product_ids( $product );
                if ( count( $similar_product_ids ) > 0 ) {
                    return wc_get_products([
                        'include' => $similar_product_ids
                    ]);
                }
            }
            // use default recommendation
            return $related_products;
        }

        // original function ,legacy code
        private function get_similar_product_ids ( $product ) {
            try {
                $feature_image = $this->get_product_feature_image( $product );
                $medias = Simile_Model::similar_search( $feature_image[ 'id' ], $product->get_id(), $feature_image[ 'src' ] );
                return array_map( function ( $media ) {
                    return $media->metadata->itemId;
                }, $medias );
            } catch (Exception $e) {
                return [];
            }
        }


        function update_manual_edit_products ( $product) {
            if(!$product->get_id()) return;
            $orginal_products_array = get_post_meta($product->get_id(),"_upsell_ids",true);
            $edited_products_array = $product->get_upsell_ids();

            // get product's media_id
            $feature_image = $this->get_product_feature_image( $product );

            if(is_null($edited_products_array)) {
                if (!is_null($orginal_products_array)) {
                    Simile_Model::similar_result($feature_image['id'], []);
                }
            }else{
                //  translate from product_id to media_id
                $edited_media_array=array_map( "Simile_Related_Products::get_media_id_by_product_id", $edited_products_array );
                if(is_null($orginal_products_array)){
                    Simile_Model::similar_result($feature_image[ 'id' ],$edited_media_array);
                }else{
                    if(is_array($orginal_products_array)  && !empty(array_diff($orginal_products_array,$edited_products_array))){
                        Simile_Model::similar_result($feature_image[ 'id' ],$edited_media_array);
                    }
                }
            }
        }

        private function get_similar_upsell_product_ids ( $product ) {
            try {
                $feature_image = $this->get_product_feature_image( $product );
                if(!$product->get_id()) return [];
                $medias = Simile_Model::similar_search( $feature_image[ 'id' ], $product->get_id(), $feature_image[ 'src' ] );
                $array_medias = array_map( function ( $media ) {
                    return $media->metadata->itemId;
                }, $medias );
                if(count($array_medias)>0){
                    // save to upsell field
                    if(!empty(array_diff($array_medias,$product->get_upsell_ids()))){
                        $product->set_upsell_ids($array_medias);
                        update_post_meta( $product->get_id(), '_upsell_ids', $array_medias );
                    }
                }
                return $array_medias;
            } catch (Exception $e) {
                return [];
            }
        }

        private function save_similar_product_table($imageId=0,$productId=0, $imgUrl='',$medias=''){
            try{
                global $wpdb;
                $medias = json_encode($medias);
                // save to table wp_woocommerce_simile_related_product_cache
                $sql = "INSERT INTO {$wpdb->prefix}woocommerce_simile_related_product_cache 
( media_id, product_id, media_url, response ) 
VALUES (%s, %s, %s, %s) 
ON DUPLICATE KEY UPDATE product_id = %s,media_url = %s,response = %s,update_time = %s,count= count+1";
                $sql = $wpdb->prepare($sql,$imageId, $productId, $imgUrl,$medias,
                    $productId, $imgUrl, $medias,current_time( 'mysql' ));
                $wpdb->query($sql);
            }catch (Exception $e){
                return [];
            }
        }

        private function get_product_feature_image ( $product ) {
            if ( $attachment_id = $product->get_image_id() ) {
                if ( $attachment_post = get_post( $attachment_id ) ) {
                    if ( is_array( $attachment_image_src = wp_get_attachment_image_src( $attachment_id, 'full' ) ) ) {
                        return [
                            'id' => (int) $attachment_id,
                            'src' => $attachment_image_src[ 0 ]
                        ];
                    }
                }
            }

            return null;
        }

        public function get_media_id_by_product_id($productId){
            // translate productId to Media_id
            $product_instance= new WC_Product($productId);
            $feature_image = $this->get_product_feature_image( $product_instance );
            return $feature_image[ 'id' ];
        }

        public function get_the_user_ip() {
            if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
                //check ip from share internet
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
                //to check ip is pass from proxy
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            return apply_filters( 'wpb_get_ip', $ip );
        }

    } // end class

} // end if class exists

new Simile_Related_Products();

?>