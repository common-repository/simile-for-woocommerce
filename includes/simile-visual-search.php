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

if ( ! class_exists( 'Simile_Visual_Search' ) ) {

    class Simile_Visual_Search {
        public  $vs_config = [];

        public function __construct () {
            // short code  simile_vs
            add_shortcode('simile_vs',  array( $this,'vs_shortcode'));

            if(get_option( SIMILE_TEXT_DOMAIN.'_simile_vs_enable',0)==0){
                // visual search disabled
                return;
            }
            $this->get_vs_config();
            if( $this->vs_config['simile_vs_active_page_all'] == "1"){
                add_action( 'get_footer', array( $this, 'load_vs_widget_footer' ),99 );
            }else{
                if ($this->vs_config['simile_vs_active_page_homepage'] == "1"){
                    add_action( 'get_footer', array( $this, 'load_vs_widget_footer' ),99 );
                }
                if($this->vs_config['simile_vs_active_page_category'] == "1"){
                    add_action( 'woocommerce_archive_description', array( $this, 'load_vs_widget' ),99);
                }
                if ($this->vs_config['simile_vs_active_page_product'] == "1"){
                    add_action( 'woocommerce_product_meta_end', array( $this, 'load_vs_widget' ),99 );
                }
            }
        }

        public function get_vs_config(){
            $this->vs_config= array(
                'simile_vs_position' => get_option( SIMILE_TEXT_DOMAIN.'_'.'simile_vs_position','left_bottom'),
                'simile_vs_active_page_all' => get_option( SIMILE_TEXT_DOMAIN.'_'.'simile_vs_active_page_all','1'),
                'simile_vs_active_page_homepage' => get_option( SIMILE_TEXT_DOMAIN.'_'.'simile_vs_active_page_homepage','0'),
                'simile_vs_active_page_category' => get_option( SIMILE_TEXT_DOMAIN.'_'.'simile_vs_active_page_category','0'),
                'simile_vs_active_page_product' => get_option( SIMILE_TEXT_DOMAIN.'_'.'simile_vs_active_page_product','0')
            );
        }

        public function load_vs_widget_footer(){
            if($this->vs_config['simile_vs_active_page_all'] != "1" && $this->vs_config['simile_vs_active_page_homepage'] == "1"){
                if($this->get_slug()!="homepage"){
                    return;
                }
            }
            $this->load_vs_widget();
        }

        public function load_vs_widget(){
            wp_enqueue_style( 'cropperCss', "https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.min.css");
            wp_enqueue_script('cropperJS',"https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.min.js");


            wp_enqueue_style( 'vsWidgetBody',Simile_Model::get_static_assets_url(). "assets/css/searchwidget.css");
            wp_enqueue_script('vsLoaderJS',Simile_Model::get_static_assets_url() . "assets/js/visualSearchLoader.js");
            wp_enqueue_script('vsSearchJS',Simile_Model::get_static_assets_url() . "assets/js/SearchScript.js");
            $vsSearchJS_data = [
                'restAPI'  => rest_url().SIMILE_TEXT_DOMAIN."/",
                'position' => $this->vs_config['simile_vs_position']
            ];
            wp_localize_script('vsLoaderJS', 'vsSearchJS', $vsSearchJS_data);
        }

        public function get_slug(){
            global $post;
            $post_slug=$post->post_name;
            return $post_slug;
        }


        function vs_shortcode()
        {
            if(get_option( SIMILE_TEXT_DOMAIN.'_simile_vs_enable',1)==1){
                $this->load_vs_widget();
            }
        }


    } // end class
} // end if class exists

new Simile_Visual_Search();

?>