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

if ( ! class_exists( 'Simile_Smart_Bundles_Products' ) ) {

    class Simile_Smart_Bundles_Products {

        public function __construct () {


            if(get_option( SIMILE_TEXT_DOMAIN.'_bundles_enable',1)==0){
                // bundles disabled
                return;
            }
            $this->get_bundle_config();

            // display section
            $show_position= get_option( SIMILE_TEXT_DOMAIN.'_bundles_position','before_upsell');
            if( $show_position =='before_upsell'){
                add_action( 'woocommerce_before_template_part', [ $this,'template_fliter'], 10, 1 );
            }elseif( $show_position =='after_upsell'){
                add_action( 'woocommerce_after_template_part', [ $this,'template_fliter'], 10, 1 );
            }else{
                // hide
            }

            // add to cart
            add_action('wp_ajax_woocommerce_ajax_add_to_cart', [ $this,'woocommerce_ajax_add_to_cart']);
            add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', [ $this,'woocommerce_ajax_add_to_cart']);

            // cart display
            add_filter( 'woocommerce_cart_item_remove_link', [ $this,'bundle_cart_item_remove_delete_icon'], 10, 2 );
            add_filter( 'woocommerce_cart_item_quantity', [ $this, 'bundle_cart_item_quantity_disable'], 10, 3 );

            // cart action
            add_action( 'woocommerce_remove_cart_item', [ $this, 'bundle_remove_all' ], 10, 2 );
            add_action( 'woocommerce_after_cart_item_quantity_update', [ $this, 'bundle_update_cart_item_quantity'], 10, 4 );
            add_action( 'woocommerce_before_cart_item_quantity_zero', [ $this, 'bundle_update_cart_item_quantity_zero'], 10 ,2);

            // subtotal & total
            add_filter( 'woocommerce_before_calculate_totals', array( $this, 'bundle_cart_get_cart_contents' ), 10, 1 );

            // undo cart delete
            add_action( 'woocommerce_restore_cart_item', array( $this, 'bundle_undo_cart_item' ), 10, 2 );

        }

        public  $bundle_config = [];

        public function get_bundle_config(){
            $this->bundle_config= array(
                'widget_title' => get_option( SIMILE_TEXT_DOMAIN.'_bundles_widget_title','PEOPLE BUY TOGETHER:'),
                'total_price_text' => get_option( SIMILE_TEXT_DOMAIN.'_bundles_total_price_text','Total price:'),
                'add_to_cart_button_text' => get_option( SIMILE_TEXT_DOMAIN.'_bundles_add_to_cart_button_text','BUY BUNDLE NOW'),
                'bundles_discount_info_message_after_added_cart_message' => get_option( SIMILE_TEXT_DOMAIN.'_bundles_discount_info_message_after_added_cart_message','Bundle has been added to your cart.'),
                'apply_discount_for_all_bundled_products' => get_option( SIMILE_TEXT_DOMAIN.'_bundles_apply_discount_for_all_bundled_products',1),
                'percent_discount' => get_option( SIMILE_TEXT_DOMAIN.'_bundles_percent_discount','0'),
                'discount_message' => get_option( SIMILE_TEXT_DOMAIN.'_bundles_discount_message','Buy together and save!'),
                'discount_info_message' => get_option( SIMILE_TEXT_DOMAIN.'_bundles_discount_info_message','Discounts will be applied at cart.'),
                'cancel_bundle_message'=> get_option( SIMILE_TEXT_DOMAIN.'_bundles_cancel_bundle_message','No Thanks, just the one.'),
                'remove_branding'=> get_option( SIMILE_TEXT_DOMAIN.'_bundles_remove_branding',0),
            );
        }


        // display section begin
        function template_fliter( $template_name ){
            if( $template_name == 'single-product/up-sells.php'){
                $bundle_product_array = $this->get_bundle_products();
                $this->show_bundle_section($bundle_product_array);
            }
        }

        function get_bundle_products ( ) {
            $bundle_product_array = [];
            // if product page
            if (is_product()) {
                global $product;
                $bundle_product_array = Simile_Model::bundle_search($product->get_id());
            }
            return $bundle_product_array;
        }

        function show_bundle_section ($product_array){
            global $product;
            if(count($product_array)<2) return;

            // load css
            wp_enqueue_style( 'bundlesWidgetBody',Simile_Model::get_static_assets_url() . "assets/css/widgetBody.css");
            // load JS
            wp_enqueue_script('bundlesWidgetJS',Simile_Model::get_static_assets_url() . "assets/js/bundlesWidget.js");
            $bundlesWidgetJS_data = [
                'productId'=> $product->get_id(),
                'bundle_config'=> $this->bundle_config,
                'bundle'=>$product_array,
                'currency'=> get_woocommerce_currency(),
                'currency_symbol'=> get_woocommerce_currency_symbol(),
                'currency_position' => get_option( 'woocommerce_currency_pos' ),
                'thousand_separator'=>wc_get_price_thousand_separator(),
                'decimal_separator'=>wc_get_price_decimal_separator(),
                'number_of_decimals' =>wc_get_price_decimals()
            ];
            wp_localize_script('bundlesWidgetJS', 'bundlesWidgetJS_data', $bundlesWidgetJS_data);

            echo '
<div>
<h2 id="sm-bundle-items-title">'.$this->bundle_config['widget_title'].'</h2>
 <div class="sm-bundle-items-image-group-container">
    <ul class="sm-bundle-items sm-bundle-items-discount"style="margin: 0 0 0em 0em">
        ';
            woocommerce_product_loop_start();
            foreach ($product_array as $p){
                $p = wc_get_product($p);
                $post_object = get_post( $p->get_id() );
                setup_postdata( $GLOBALS['post'] =& $post_object );
                $p_url = $p->get_permalink();
                ?>
                <li  style="margin-bottom: 0em" <?php wc_product_class( '', $product ); ?>>
                    <a href="<?php echo $p_url;?>">
                    <?php
                     woocommerce_template_loop_product_thumbnail();
                    echo '</a>
                         <div class="sm-bundle-items-discount-list-group">
                            <div class="sm-bundle-item-caption">
                                <input type="checkbox" checked="true" disabled="disabled"> ' . $p->get_name() . '
                            </div> 
                    ';
                    $this->get_variable_options($p);
                    $show_origin_price = ($this->get_discount_percent()>0)?"":"style='display:none'";
                    echo '
                            <div class="sm-bundle-items-price-tags">
                                <span class="sm-bundle-items-price sm-bundle-items-has-compare-at-price">'.wc_price($this->calculate_after_bundle_price($p->get_price())).' </span> <s>
                                <span class="sm-bundle-items-compare-at-price" '.$show_origin_price .'>' . wc_price( wc_get_price_to_display( $p )) . '</span></s>
                            </div>
                         </div>
                    ';
                    ?>
                </li>
                <?php
            }
            woocommerce_product_loop_end();
            echo '</ul></div>';
            echo '     
 <h3 class="sm-bundle-items-discount-text">'.$this->bundle_config['discount_message'].'</h3>   
 <div class="sm-bundle-items-list-group-container">
    <div class="sm-bundle-items-form sm-bundle-items-discount-form">
        <div class="sm-bundle-items-total-price">
            <div class="sm-bundle-items-total-price-title" style="font-weight: 600;">'.$this->bundle_config['total_price_text'].'</div>
            <div class="sm-bundle-items-total-price-pricetag sm-bundle-items-has-compare-at-price" id="sm-bundle-price">'.wc_price($this->countPrice($product_array,"bundle")).' </div>
             <s><div class="sm-bundle-items-total-compare-at-price-pricetag" id="sm-bundle-total-price" '.$show_origin_price .'>'. wc_price($this->countPrice($product_array,'total')).' </div></s>
        </div>
        <div id="bundle-tips">'.$this->bundle_config['discount_info_message'].'</div>
      <button name="buy-bundle-now" class="btn">'.$this->bundle_config['add_to_cart_button_text'].'</button>
    </div>
 </div>
';
            if( $this->bundle_config['remove_branding']!=1 && count($product_array) > 0) {
                echo "<p style='font-size: 12px;text-align: right;margin-right: 20px;'>powered by <a href=\"https://www.simile.ai\" target=\"_blank\" style='color: inherit;text-decoration: none;'>Simile.ai</a></p>";
            }
            echo '</div>';

        }// end function show_bundle_section

        // if product is variable  show select input
        function get_variable_options($p){
            if('variable'!=$p->get_type()){
                return;
            }
            $attributes = $p->get_available_variations();
            $product_object   = new WC_Product_Variable( $p->get_id() );
            $available_variations = $product_object->get_available_variations();
            if ( empty( $available_variations ) && false !== $available_variations ){
                echo '<p class="stock out-of-stock"><?php echo esc_html( apply_filters( \'woocommerce_out_of_stock_message\', __( \'This product is currently out of stock and unavailable.\', \'woocommerce\' ) ) ); ?></p>';
            }else{
                echo '<div>';
                echo '<select id="'.$p->get_id().'" class="sm-bundle-items-variants-select">';
                $product_variations = [];

                foreach ( $attributes as $attribute_name => $options ){
                    $variations_name = '';

                    foreach ($options['attributes'] as $k => $v){
                        $variations_name .= "/".$v;
                    }
                    $variations_name = substr($variations_name,1);
                    echo '<option value="'.$options['variation_id'].'">'.$variations_name.'</option>';

                    $product_variations[ $options['variation_id']] = array(
                        'variation_id' => $options['variation_id'],
                        'display_price' => sprintf('%.2f',$options['display_price']),
                        'img_src' => $options['image']['thumb_src'],
                        'img_srcset' => $options['image']['thumb_src']
                    );
                }//end foreach

                echo '</select>';
                echo '</div>';
                wp_localize_script('bundlesWidgetJS', 'bundlesWidgetJS_data_variations_'.$p->get_id(), $product_variations);
            }
        }// end function get variable opinon

        function  get_discount_percent(){
            $discount_percent = ($this->bundle_config['apply_discount_for_all_bundled_products'])?$this->bundle_config['percent_discount']:0;
            if(!is_numeric($discount_percent))$discount_percent =0;
            if($discount_percent < 0 || $discount_percent >99 )$discount_percent =0;
            return $discount_percent;
        }

        function calculate_after_bundle_price($original_price){
            $original_price = (float) $original_price;
            $discount_percent = $this->get_discount_percent() ;
            $sale_price = (float) ( 100 - $discount_percent ) * $original_price / 100;
            return sprintf('%.2f',$sale_price);
        }

        function countPrice($product_array,$type='total'){
            $total_price = 0.00;
            $bundle_price = 0.00;
            foreach ( $product_array as $p ) {
                $p = wc_get_product($p);
                $total_price += $p->get_price();
                $bundle_price += $this->calculate_after_bundle_price($p->get_price());
            }
            if($type == 'bundle'){
                return sprintf('%.2f',$bundle_price);
            }else{
                return sprintf('%.2f',$total_price);
            }
        }
        // display section end

        // add to cart begin

        function woocommerce_ajax_add_to_cart() {
            $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id']));
            // get bundle products from table
            $bundle_products_array = [];
            $b_obj = Simile_Model::bundle_get_from_table($product_id);
            if(isset($b_obj->{'bundle'})) {
                $bundle_products_array = json_decode($b_obj->{'bundle'});
            }
            if(count($bundle_products_array)<2) return;
            if ($this -> add_product_to_cart_transaction($bundle_products_array)) {
                $data = array(
                    'error' => true,
                    'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id));
                echo wp_send_json($data);wp_die();return;
                WC_AJAX :: get_refreshed_fragments();
            } else {
                $data = array(
                    'error' => true,
                    'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id));
               echo wp_send_json($data);
            }
            wp_die();
        }

        function add_product_to_cart_transaction($bundle_products_array) {
            $transaction = true;
            //create a bundle id. rule: mainProduct-p2-p3
            $bundle_id = '';
            foreach ( $bundle_products_array as $p ) {
                $bundle_id = $bundle_id."-".$p;
            }
            $bundle_id =substr($bundle_id,1);
            $bundle_discount_type = 'discount';
            $bundle_discount_amount = $this->get_discount_percent();

            $this->cart_clean_bundle_by_id($bundle_id);

            foreach ( $bundle_products_array as $p ) {
                $p = wc_get_product($p);
                $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($p->get_id()));
                $quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
                $price = $p->get_price();
                $variation_id = 0;

                if( isset($_POST['variation_id']) && isset($_POST['variation_id'][$product_id]) ){
                    $variation_id = absint($_POST['variation_id'][$product_id]);
                    $variation_optinon = $this->get_variable_price($p,$variation_id);
                    $price = isset($variation_optinon['display_price'])?$variation_optinon['display_price']:$price;
                }

                $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
                $cart_item_data = array(
                    'bundle_id'             => $bundle_id,
                    'bundle_sales_price'    => $this->calculate_after_bundle_price($price),
                    'bundle_orginal_price'  => $price,
                    'bundle_quantity'       => $quantity,
                    'bundle_variation_id'   => $variation_id,
                    'bundle_main_product_id'=> $bundle_products_array[0],
                    'bundle_discount'       => $bundle_discount_type,
                    'bundle_discount_amount'=> $bundle_discount_amount,
                    'bundle_products_array' => $bundle_products_array
                );
                $product_status = get_post_status($product_id);
                if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id,array(),$cart_item_data) && 'publish' === $product_status) {
                   do_action('woocommerce_ajax_added_to_cart', $product_id);
                    if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
                        wc_add_to_cart_message(array($product_id => $quantity), true);
                    }
                } else {
                    $transaction = false;
                }
            } // end foreach

            if(!$transaction){
                // undo add cart items
                $this->cart_clean_bundle_by_id($bundle_id);
                return false;
            }
            return true;
        }

        // if product is variable  get price
        function get_variable_price($p,$v_id){
            if('variable'!=$p->get_type()){
                return;
            }
            $attributes = $p->get_available_variations();
            $product_object   = new WC_Product_Variable( $p->get_id() );
            $available_variations = $product_object->get_available_variations();
            if ( empty( $available_variations ) && false !== $available_variations ){
                return [];
            }else{
                $p_v_p = [];
                foreach ( $attributes as $attribute_name => $options ){
                    if($options['variation_id'] == $v_id){
                        $p_v_p = array(
                            'variation_id' => $options['variation_id'],
                            'display_price' => sprintf('%.2f',$options['display_price']),
                        );
                        break;
                    }
                }//end foreach
                return $p_v_p;
            }
        }// end function get_variable_price

        function cart_clean_bundle_by_id($bundle_id){
            foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
                $cart_product = WC()->cart->get_cart_item($cart_item_key);
                if ( $cart_product && isset($cart_product['bundle_id']) && $cart_product['bundle_id'] == $bundle_id ) {
                    wc()->cart->remove_cart_item($cart_item_key);
                }
            }
        }

        // add to cart end



        //  cart begin

        function bundle_cart_item_remove_delete_icon( $link, $cart_item_key ) {
            if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['bundle_main_product_id'] ) ) {
                $bundle_main_product_id = WC()->cart->cart_contents[ $cart_item_key ]['bundle_main_product_id'];
                if ( $bundle_main_product_id != WC()->cart->cart_contents[ $cart_item_key ]['product_id']  ) {
                    return '';
                }
            }
            return $link;
        }

        function bundle_cart_item_quantity_disable( $quantity, $cart_item_key, $cart_item ) {
            if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['bundle_main_product_id'] ) ) {
                $bundle_main_product_id = WC()->cart->cart_contents[ $cart_item_key ]['bundle_main_product_id'];
                if ( $bundle_main_product_id != WC()->cart->cart_contents[ $cart_item_key ]['product_id']  ) {
                    return $cart_item['quantity'];
                }
            }
            return $quantity;
        }

        function bundle_remove_all( $cart_item_key, $cart ) {
            if ( isset( $cart->removed_cart_contents[ $cart_item_key ]['bundle_id'] ) ) {
                $bundle_id = $cart->removed_cart_contents[ $cart_item_key ]['bundle_id'];
                $removed_cart_contents= [];
                $removed_cart_contents[ $cart_item_key ]= $cart->removed_cart_contents[ $cart_item_key ];

                foreach ( WC()->cart->get_cart() as $cart_item_key_s => $values ) {
                    $cart_product = WC()->cart->get_cart_item($cart_item_key_s);
                    if ( $cart_product && isset($cart_product['bundle_id']) && $cart_product['bundle_id'] == $bundle_id ) {
                        $removed_cart_contents[$cart_item_key]['undo'][$cart_product['product_id']] =array(
                                'product_id' => $cart_product['product_id'],
                                'variation_id' => $cart_product['variation_id'],
                                'quantity' => $cart_product['quantity'],
                                'price' => $cart_product['bundle_orginal_price'],
                        );
                        unset( $cart->cart_contents[ $cart_item_key_s ] );
                    }
                }

                $cart->set_removed_cart_contents($removed_cart_contents);
            }
        }

        function bundle_update_cart_item_quantity( $cart_item_key, $quantity = 0 ,$old_quantity,$cart) {
            if (isset(WC()->cart->cart_contents[$cart_item_key]['bundle_id'])) {
                $bundle_id = WC()->cart->cart_contents[$cart_item_key]['bundle_id'];
                foreach (WC()->cart->get_cart() as $cart_item_key_s => $values) {
                    $cart_product = WC()->cart->get_cart_item($cart_item_key_s);
                    if ($cart_product &&
                        isset($cart_product['bundle_id']) &&
                        $cart_product['bundle_id'] == $bundle_id &&
                        $cart_item_key != $cart_item_key_s) {
                        $cart->cart_contents[$cart_item_key_s]['quantity'] = $quantity;
                    }
                }
            }
        }

        function bundle_update_cart_item_quantity_zero( $cart_item_key, $cart) {
            if (isset(WC()->cart->cart_contents[$cart_item_key]['bundle_id'])) {
                $bundle_id = WC()->cart->cart_contents[$cart_item_key]['bundle_id'];
                foreach (WC()->cart->get_cart() as $cart_item_key_s => $values) {
                    $cart_product = WC()->cart->get_cart_item($cart_item_key_s);
                    if ($cart_product &&
                        isset($cart_product['bundle_id']) &&
                        $cart_product['bundle_id'] == $bundle_id &&
                        $cart_item_key != $cart_item_key_s) {
                            $cart->remove_cart_item($cart_item_key_s);
                    }
                }
            }
        }

        function bundle_cart_get_cart_contents($cart){
            $bundle_in_cart =[];
            foreach ($cart->get_cart() as $cart_item_key_s => $values) {
                $cart_product = WC()->cart->get_cart_item($cart_item_key_s);
                if ($cart_product && isset($cart_product['bundle_id']) ) {
                    $bundle_in_cart[$cart_product['bundle_id']] = $cart_product['bundle_products_array'];
                }
            }// end foreach

            if($bundle_in_cart) {
                // foreach all bundles combine in cart ,double check them, if has changed
                // if changed, delete the bundle which has changed
                foreach ($bundle_in_cart as $bundle_id => $bundle_member) {
                    // get bundle products from table
                    $bundle_products_array = [];
                    $b_obj = Simile_Model::bundle_get_from_table($bundle_member[0]);
                    if (isset($b_obj->{'bundle'})) {
                        $bundle_products_array = json_decode($b_obj->{'bundle'});
                    }
                    if ($bundle_products_array !== $bundle_member) {
                        $this->cart_clean_bundle_by_id($bundle_id); // Some thing changed, delete them
                    }
                }

                // recount the price again
                foreach ($cart->get_cart() as $cart_item_key_s => $values) {
                    $cart_product = WC()->cart->get_cart_item($cart_item_key_s);
                    if ($cart_product && isset($cart_product['bundle_id'])) {
                        $cart_product['data']->set_price($cart_product['bundle_sales_price']);
                    }
                }// end foreach
            }
        }

        function bundle_undo_cart_item( $cart_item_key,$cart ) {
            $contents= $cart->removed_cart_contents[ $cart_item_key ];
            $undo =isset($contents['undo'])?$contents['undo']:[];

            $bundle_id =$contents['bundle_id'];
            $bundle_discount_type = 'discount';
            $bundle_discount_amount = $this->get_discount_percent();

            foreach ( $contents['bundle_products_array'] as $p ) {
                if ($p == $contents['product_id']) continue;
                $p = wc_get_product($p);
                $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($p->get_id()));
                $undo_s = $undo[$product_id];
                $quantity = $undo_s['quantity'];
                $price = $undo_s['price'];
                $variation_id = $undo_s['variation_id'];
                $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
                $cart_item_data = array(
                    'bundle_id' => $bundle_id,
                    'bundle_sales_price' => $this->calculate_after_bundle_price($price),
                    'bundle_orginal_price' => $price,
                    'bundle_quantity'       => $quantity,
                    'bundle_variation_id'   => $variation_id,
                    'bundle_main_product_id' => $contents['bundle_products_array'][0],
                    'bundle_discount' => $bundle_discount_type,
                    'bundle_discount_amount' => $bundle_discount_amount,
                    'bundle_products_array' => $contents['bundle_products_array']
                );
                $product_status = get_post_status($product_id);
                if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id, array(), $cart_item_data) && 'publish' === $product_status) {
                    do_action('woocommerce_ajax_added_to_cart', $product_id);
                    if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
                        wc_add_to_cart_message(array($product_id => $quantity), true);
                    }
                }
            }



        }

        // cart end



    } // end class
} // end if class exists

new Simile_Smart_Bundles_Products();

?>