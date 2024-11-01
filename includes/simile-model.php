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

defined( 'ABSPATH' ) or die( 'No direct access' ); // Exit if accessed directly

if ( ! class_exists( 'Simile_Model' ) ) {

    class Simile_Model {
        static function signup () {
            $restendpoint = get_rest_url();
            if(substr($restendpoint,-1)=="/"){
                $restendpoint = substr($restendpoint,0,strlen($restendpoint)-1);
            }
            $response = wp_remote_post( self::get_url( '/stylist/v2/widget/wpsignup' ), [
                'method' => 'POST',
                'data_format' => 'body',
                'headers' => [
                    'Authorization' => 'Basic bWVmb25fYXBwOlA5MDgyMGJiMTc0M2UxMGNlYQ==',
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode( [
                    'widgetName' => 'SIMILE_WP',
                    'website' => home_url(),
                    'email' => get_option('admin_email',''),
                    'restendpoint' => $restendpoint
                ] )
            ]);
            self::check_response_error( $response );
            return json_decode( $response[ 'body' ] );
        }
        static function VSsignup () {
            $restendpoint = get_rest_url();
            if(substr($restendpoint,-1)=="/"){
                $restendpoint = substr($restendpoint,0,strlen($restendpoint)-1);
            }
            $response = wp_remote_post( self::get_url( '/stylist/v2/widget/wpsignup' ), [
                'method' => 'POST',
                'data_format' => 'body',
                'headers' => [
                    'Authorization' => 'Basic bWVmb25fYXBwOlA5MDgyMGJiMTc0M2UxMGNlYQ==',
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode( [
                    'widgetName' => 'VS_WP',
                    'website' => home_url(),
                    'email' => get_option('admin_email',''),
                    'restendpoint' => $restendpoint
                ] )
            ]);
            self::check_response_error( $response );
            $user_info = json_decode( $response[ 'body' ] );
            $shop = $user_info->shop;
            $apiKeys = $shop->apiKeys[0];
            update_option(SIMILE_TEXT_DOMAIN . '_shop_credentials_vs', [
                'shop' => $shop->id,
                'access_token' => $shop->accessToken,
                'client_id' => $apiKeys->clientId,
                'client_secret' => $apiKeys->clientSecret
            ]);
            return json_decode( $response[ 'body' ] );
        }
        static function unintall () {
            global $wpdb;
            $wpdb->query("UPDATE {$wpdb->prefix}postmeta_simile_backup AS BACKUP, {$wpdb->prefix}postmeta AS ORG SET ORG.meta_value = BACKUP.meta_value WHERE BACKUP.post_id = ORG.post_id AND ORG.meta_key = '_upsell_ids'");

            $hmac = Simile_Model::hmac_encode_query();
            $response = wp_remote_post( self::get_url( "/stylist/v2/widget/shops/uninstall?{$hmac}" ), [
                'method' => 'DELETE',
                'data_format' => 'body',
                'headers' => [
                    'Authorization' => 'Basic bWVmb25fYXBwOlA5MDgyMGJiMTc0M2UxMGNlYQ==',
                    'Content-Type' => 'application/json'
                ]
            ]);
            //self::check_response_error( $response );
            return json_decode( $response[ 'body' ] );
        }

        static function initial_db () {
            global $wpdb;
            // create table for backup wp_postmeta
            $table_name_simile_backup = $wpdb->prefix . "postmeta_simile_backup";
            $wpdb->query("drop table IF EXISTS {$table_name_simile_backup};");
            $wpdb->query("CREATE TABLE {$table_name_simile_backup} SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = '_upsell_ids'");
            // end backup post_meta

            // create table for bundles
            $table_name_simile_bundles = $wpdb->prefix . "similie_product_detail";
//            $wpdb->query("drop table IF EXISTS {$table_name_simile_bundles};");
            $wpdb->query("CREATE TABLE IF NOT EXISTS {$table_name_simile_bundles} ( `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT , `product_id` BIGINT(20) UNSIGNED NOT NULL , `bundle` VARCHAR(200) NOT NULL , `discount_type` VARCHAR(200) NOT NULL , `discount_amount` VARCHAR(200) NOT NULL , `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, `status` INT NOT NULL , `disable` INT NOT NULL , `upsell` VARCHAR(200) NOT NULL , PRIMARY KEY (`id`), UNIQUE `product_id_index` (`product_id`))");
        }

        static function enable_simile () {
            $shop_credentials = get_option( SIMILE_TEXT_DOMAIN . '_shop_credentials' );
            if(isset($shop_credentials) && isset($shop_credentials[ 'access_token' ]) && $shop_credentials[ 'access_token' ]!='' ){
                $hmac = Simile_Model::hmac_encode_query();
                $body= json_encode( [
                    'widgetName' =>'SIMILE_WP',
                    'email' => get_option('admin_email',''),
                ]);
                $response = wp_remote_post( self::get_url( "/stylist/v2/widget/wpactivate?{$hmac}" ), [
                    'method' => 'POST',
                    'data_format' => 'body',
                    'headers' => [
                        'Authorization' => 'Basic bWVmb25fYXBwOlA5MDgyMGJiMTc0M2UxMGNlYQ==',
                        'Content-Type' => 'application/json'
                    ],
                    'body' => $body
                ]);
                if(isset($response[ 'response' ]) && isset($response[ 'response' ]['code']) && $response[ 'response' ]['code'] =='200' ){
                    return true;
                }
            }
            return false;
        }
        // signup & uninstall --finish

        static function get_shop_info () {
            $hmac = Simile_Model::hmac_encode_query();
            $response = wp_remote_post( self::get_url( "/stylist/v2/widget/shops?{$hmac}" ), [
                'method' => 'GET',
                'data_format' => 'body',
                'headers' => [
                    'Authorization' => 'Basic bWVmb25fYXBwOlA5MDgyMGJiMTc0M2UxMGNlYQ==',
                    'Content-Type' => 'application/json'
                ]
            ]);
            self::check_response_error( $response );
            return json_decode( $response[ 'body' ] );
        }

        static function update_shop_info () {
            $shop_credentials = get_option( SIMILE_TEXT_DOMAIN . '_shop_credentials' );
            $hmac = Simile_Model::hmac_encode_query();
            $restendpoint = get_rest_url();
            if(substr($restendpoint,-1)=="/"){
                $restendpoint = substr($restendpoint,0,strlen($restendpoint)-1);
            }

            $body= json_encode( [
                'id' => $shop_credentials['shop'],
                'domain' => home_url(),
                'website' => $restendpoint
            ]);
            $response = wp_remote_post( self::get_url( "/stylist/v2/widget/shops?{$hmac}" ), [
                'method' => 'PUT',
                'data_format' => 'body',
                'headers' => [
                    'Authorization' => 'Basic bWVmb25fYXBwOlA5MDgyMGJiMTc0M2UxMGNlYQ==',
                    'Content-Type' => 'application/json'
                ],
                'body' => $body
            ]);
            self::check_response_error( $response );
            return json_decode( $response[ 'body' ] );
        }

        static function similar_search ( $media_id, $product_id, $media_url ) {
            $shop_credentials = get_option( SIMILE_TEXT_DOMAIN . '_shop_credentials' );
//          $response = wp_remote_post( self::get_url( "/searchcache/similar/{$media_id}?product_id={$product_id}&media_url={$media_url}" ), [
            $response = wp_remote_post( LINODE_SERVER_URL .  '/similar/'.$media_id.'?size=10&product_id='.$product_id.'&media_url='.$media_url , [
                'method' => 'GET',
                'data_format' => 'body',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Client-Id' => $shop_credentials[ 'client_id' ],
                    'Client-Secret' => $shop_credentials[ 'client_secret' ]
                ]
            ] );
//            print_r($response);
            if( self::check_if_api_error($response) ){
                return  [];
            }
            self::check_response_error( $response );
            return json_decode( $response[ 'body' ] )->medias;
        }

        // send manualy modify result to SMI
        static function similar_result ( $media_id, $similar_result_media_id ) {
            $similar_result_media_id_string = implode(",",$similar_result_media_id);
            $hmac = Simile_Model::hmac_encode_query();
            $body= json_encode( [
                'medias'=>[[
                    'mediaId' => $media_id,
                    'metadata' => [
                        "definedSimilar"=> $similar_result_media_id_string
                    ]
                ]]
            ] );
            $response = wp_remote_request( self::get_url( "/stylist/v2/widget/shops/medias/similar-result?{$hmac}" ), [
                'method' => 'PUT',
                'data_format' => 'body',
                'headers' => [
                    'Authorization' => 'Basic bWVmb25fYXBwOlA5MDgyMGJiMTc0M2UxMGNlYQ==',
                    'Content-Type' => 'application/json',
                ],
                'body' => $body
            ] );
            return true;
        }

        // bundle series function start
        static function bundle_search ( $product_id ) {
            $shop_credentials = get_option( SIMILE_TEXT_DOMAIN . '_shop_credentials' );
            $response = wp_remote_post( self::get_url( "/simile/v2/bundles/{$product_id}" ), [
                'method' => 'GET',
                'data_format' => 'body',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Client-Id' => $shop_credentials[ 'client_id' ],
                    'Client-Secret' => $shop_credentials[ 'client_secret' ]
                ]
            ] );
            if( self::check_if_api_error($response) ){
                return  [];
            }
            self::check_response_error( $response );
            $response['body'] = json_decode( $response[ 'body' ] );
            $bundle_product_array[] = "".$product_id;
            foreach ($response['body'] as $value){
                $bundle_product_array[] = $value->itemId;
            }
            self::bundle_search_save($product_id,$bundle_product_array);
            return $bundle_product_array;
        }

        static private function bundle_search_save($productId=0, $bundle_product_array=''){
            try{
                global $wpdb;
                $bundle_product_content = json_encode($bundle_product_array);
                $sql = "INSERT INTO {$wpdb->prefix}similie_product_detail 
( product_id, bundle ) 
VALUES (%s, %s) 
ON DUPLICATE KEY UPDATE product_id = %s,bundle = %s,time = %s";
                $sql = $wpdb->prepare($sql,$productId,$bundle_product_content ,
                    $productId, $bundle_product_content,current_time( 'mysql' ));
                $wpdb->query($sql);
            }catch (Exception $e){
                return [];
            }
        }

        static  function bundle_get_from_table($productId=0){
            try{
                global $wpdb;
                $_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}similie_product_detail WHERE product_id = %d LIMIT 1", $productId ) );
                if ( ! $_row ) {
                    return false;
                }
                return $_row;

            }catch (Exception $e){
                return [];
            }
        }

        static private function bundle_customize_list($key='',$page=1,$size=10){
            // not used
            for ($i=1;$i<11;$i++){
                $a[] =['id'=>$i,'main_product'=>34,'bundles'=> [31,32]];
            }

            return $a;

            $shop_credentials = get_option( SIMILE_TEXT_DOMAIN . '_shop_credentials' );
            $response = wp_remote_post( self::get_url( "/simile/v2/bundles/{$product_id}" ), [
                'method' => 'GET',
                'data_format' => 'body',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Client-Id' => $shop_credentials[ 'client_id' ],
                    'Client-Secret' => $shop_credentials[ 'client_secret' ]
                ]
            ] );
            self::check_response_error( $response );
            $response['body'] = json_decode( $response[ 'body' ] );
            $bundle_product_array[] = "".$product_id;
            foreach ($response['body'] as $value){
                $bundle_product_array[] = $value->itemId;
            }
            self::bundle_search_save($product_id,$bundle_product_array);
            return $bundle_product_array;
        }

        // bundle series function finish

        // visual search start
        static function visual_search ( $params ) {
            $hmac = Simile_Model::hmac_encode_query();
            $shop_credentials = get_option( SIMILE_TEXT_DOMAIN . '_shop_credentials' );

            $response = wp_remote_post( self::get_url( "/simile/v2/search?{$hmac}" ), [
                'method' => 'POST',
                'data_format' => 'body',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Client-Id' => $shop_credentials[ 'client_id' ],
                    'Client-Secret' => $shop_credentials[ 'client_secret' ]
                ],
                'body' => json_encode( [
                    'checkThreshold' => true,
                    'base64' => $params['base64']
                ] )
            ]);
            self::check_response_error( $response );
            return json_decode( $response[ 'body' ] )->medias;
        }
        // visual search finish

        static function valid_hmac ( $params ) {
            return true;// todo temp debug
            if ( empty ($params[ 'signature' ]) ){
                return false;
            }
            $signature = $params[ 'signature' ];
            unset( $params[ 'signature' ] );

            if (empty($params['shop']) || strlen($params['shop']) > 255)
                return false;

            if (empty($params['timestamp']))
                return false;

            if (time() - $params['timestamp'] > 300)
                return false;

            if (isset($params['page'])) {
                if (!is_numeric( $params['page'] ) || intval( $params['page'] ) < 0)
                    return false;
            }
            if (isset($params['size'])) {
                if (!is_numeric( $params['size'] ) || intval( $params['size'] ) < 0)
                    return false;
            }

            if (isset($params['modified_after'])) {
                if (!is_numeric( $params['modified_after'] ) || intval( $params['modified_after'] ) < 0)
                    return false;
            }
            if (isset($params['modified_before'])) {
                if (!is_numeric( $params['modified_before'] ) || intval( $params['modified_before'] ) < 0)
                    return false;
            }
            return $signature === self::generate_hmac( $params );
        }

        static function hmac_encode_query ( $params = [] ) {
            $shop_credentials = get_option( SIMILE_TEXT_DOMAIN . '_shop_credentials' );

            $params[ 'shop' ] = $shop_credentials[ 'shop' ];
            $params[ 'timestamp' ] = time();

            $params[ 'signature' ] = self::generate_hmac( $params );
            return http_build_query( $params );
        }

        static function get_static_assets_url () {
            if(SIMILE_STATIC_ASSETS_ENABLE){
                $url = SIMILE_STATIC_ASSETS.SIMILE_STATIC_ASSETS_TAG."/";
            }else{
                $url = home_url() . "/wp-content/plugins/simile-for-woocommerce/";
            }
            return $url;
        }

        private static function generate_hmac ( $params ) {
            $shop_credentials = get_option( SIMILE_TEXT_DOMAIN . '_shop_credentials' );
            $tokens = [];
            foreach ( $params as $key => $value ) {
                $tokens[] = "$key=$value";
            }
            sort($tokens );
            return hash_hmac(
                'sha256',
                join( $tokens ),
                $shop_credentials[ 'access_token' ]
            );
        }


        static function hmac_encode_query_debug ( $shop,$access_token ) {
            $params[ 'shop' ] = $shop;
            $params[ 'timestamp' ] = time();
            $params[ 'signature' ] = self::generate_hmac_debug( $shop,$access_token );
            return http_build_query( $params,$access_token );
        }

        private static function generate_hmac_debug ( $params,$access_token ) {
            $tokens = [];
            foreach ( $params as $key => $value ) {
                $tokens[] = "$key=$value";
            }
            sort($tokens );
            return hash_hmac(
                'sha256',
                join( $tokens ),
                $access_token
            );
        }

        private static function get_url( $path ) {
            return SIMILE_API_URL . $path;
        }

        private static function check_response_error ( $response, $code_expected = 200 ) {
            if ( is_wp_error( $response ) ) {
                throw new Exception( json_encode( $response->get_error_message() ) );
            } else if ( $response[ 'response' ][ 'code' ] !== $code_expected ) {
                throw new Exception( json_encode( $response[ 'response' ] ) );
            }
        }

        private static function check_if_api_error ( $response, $code_expected = 200 ) {
            if (is_wp_error($response)) {
                return true;
            } else if ($response['response']['code'] !== $code_expected) {
                return true;
            }
        }

    } // end class

} // end if class exists

new Simile_Model();

?>