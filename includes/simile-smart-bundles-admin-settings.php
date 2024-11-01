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

 * Simile Smart bundles for WooCommerce is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * Simile Smart bundles for WooCommerce is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with Simile Smart bundles for WooCommerce.  If not, see <https://www.gnu.org/licenses/>.
 */

defined( 'ABSPATH' ) or die( 'No direct access' ); // Exit if accessed directly

if ( ! class_exists( 'Smart_Bundles_Admin_Settings' ) ) {

    class Smart_Bundles_Admin_Settings
    {
        public $option_string = '';
        public function __construct () {
            add_action( 'admin_menu', array( $this, 'add_plugin_page' ),81 );
        }

        public function add_plugin_page(){
            add_submenu_page(
                SIMILE_TEXT_DOMAIN,
                SIMILE_PLUGIN_NAME,
                'Smart Config',
                'manage_options',
                'simile-smart-bundles-config',
                [ $this, 'bundles_settings' ]
            );
        }

        public function template_config_html($option_name='',$type='inputText',$title='',$desc='',$default_value='',$select_array=[]){
            $option_name = SIMILE_TEXT_DOMAIN.'_'.$option_name;
            $this->option_string = $this->option_string.",".$option_name;
            $option_value = get_option( $option_name, $default_value );
            echo '
                <tr>
                    <th>
                        <strong class="name">'.$title .':</strong>
                    </th>
                    <td>
            ';
            if($type == "multi-checkbox"){
                foreach($select_array as $key => $value ) {
                    $_option_name = SIMILE_TEXT_DOMAIN.'_'.$key;
                    $this->option_string = $this->option_string.",".$_option_name;
                    $_option_value = get_option( $_option_name, $value['default'] );

                    echo '<fieldset><label>';
                    echo '<input name="' . $_option_name . '" id="' . $_option_name . '" type="checkbox" value="1" ';
                    echo checked($_option_value, '1');
                    echo '/>'.$value['desc']."<br />";
                    echo '</label></fieldset>';
                }
            }elseif($type=='checkbox') {
                    echo '<input name="' . $option_name . '" id="' . $option_name . '" type="checkbox" value="1" ';
                    echo checked($option_value, '1');
                    echo '/>';
            }elseif($type=='select'){
                echo '<select name="'.$option_name.'">';
                foreach($select_array as $key => $value ){
                    echo '<option value="'.$value.'"';
                    if($option_value == $value ){
                        echo'selected';
                    }
                    echo '>'.$key.'</option>';
                }
                echo '</select>';
            }elseif($type=='inputTextarea'){
                echo '
                <textarea class="large-text" name="'.$option_name .'">'.$option_value.'</textarea>
                ';
            }else{
                echo '<input type="text" name="'.$option_name .'" value="'.$option_value.'"/>';
            }
            if($desc !='') {
                echo '<p><span class="description">' . $desc . '</span></p>';
            }
            echo '</td></tr>';
        }

        public  function bundles_settings() {
            add_thickbox();
            $active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'bundles';
            ?>
            <div class=" wrap">
                <h1 class=""><?php echo SIMILE_PLUGIN_NAME . ' Config ' . SIMILE_VERSION; ?></h1>
                <?php
                 $this->check_shop_info();
                ?>
                <div class="wpclever_settings_page_nav">
                    <h2 class="nav-tab-wrapper">
                        <a href="<?php echo admin_url( 'admin.php?page=simile-smart-bundles-config&tab=bundles' ); ?>"
                           class="<?php echo $active_tab === 'bundles' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
                            <?php esc_html_e( 'Bundles', 'simile-smart' ); ?>
                        </a>
                        <a href="<?php echo admin_url( 'admin.php?page=simile-smart-bundles-config&tab=simile' ); ?>"
                           class="<?php echo $active_tab === 'simile' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
                            <?php esc_html_e( 'Simile', 'simile-smart' ); ?>
                        </a>
                        <a href="<?php echo admin_url( 'admin.php?page=simile-smart-bundles-config&tab=vs' ); ?>"
                           class="<?php echo $active_tab === 'vs' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
                            <?php esc_html_e( 'Visual Search', 'simile-smart' ); ?>
                        </a>
                    </h2>
                </div>
                <div class="">
                    <?php if ( $active_tab === 'bundles' ) { ?>
                        <form method="post" action="options.php">
                            <?php wp_nonce_field( 'update-options' ) ?>
                            <table class="form-table">
<!--                                <tr class="heading">-->
<!--                                    <th colspan="2">-->
<!--                                        --><?php //esc_html_e( 'General', SIMILE_TEXT_DOMAIN ); ?>
<!--                                    </th>-->
<!--                                </tr>-->
                                <?php
                                $this->template_config_html('bundles_enable','checkbox','Enable Bundles','Enable or Disable Smart Bundles feature',1);
//                                $this->template_config_html('bundles_enable_cart_popup','checkbox','Display in a popup while user add item to cart','show an popup after customer add product to cart',1);
                                $this->template_config_html('bundles_position','select','Position','The position display for Bunndle section','before_upsell',['Before Upsell Section'=>'before_upsell','After Upsell Section'=>'after_upsell','Hide'=>'hide']);
                                $this->template_config_html('bundles_widget_title','inputTextarea','Widget Title','','PEOPLE BUY TOGETHER:');
                                $this->template_config_html('bundles_total_price_text','inputTextarea','Total Price Text','','Total price:');
                                $this->template_config_html('bundles_add_to_cart_button_text','inputTextarea','Add to cart button text','','BUY BUNDLE NOW');
//                                $this->template_config_html('bundles_current_item_text','inputTextarea','Current Item text','','This item:');
                                $this->template_config_html('bundles_apply_discount_for_all_bundled_products','checkbox','Apply discount for all bundled products','Enable this setting will apply discounts for all bundled products',1);
                                $this->template_config_html('bundles_percent_discount','inputText','%off discount','','0');
                                $this->template_config_html('bundles_discount_message','inputTextarea','Discount Message','','Buy together and save!');
                                $this->template_config_html('bundles_discount_info_message','inputTextarea','Discount Info Message','','Discounts will be applied at cart.');
                                $this->template_config_html('bundles_discount_info_message_after_added_cart_message','inputTextarea','After Added Cart Message','','Bundle has been added to your cart.');
                                $this->template_config_html('bundles_cancel_bundle_message','inputTextarea','Cancel Bundle Message','','No Thanks, just the one.');
                                $this->template_config_html('bundles_remove_branding','checkbox','Remove branding','Remove "Powered by Simile" branding.',0);


                                ?>
                                <tr>
                                    <th>
                                        <strong class="name"><?php esc_html_e( 'CUSTOMIZE BUNDLE', SIMILE_TEXT_DOMAIN ); ?></strong>
                                    </th>
                                    <td>
                                        <a class="button button-large"
                                           href="<?php echo admin_url( 'admin.php?page=simile-smart-bundles-customize' ); ?>">
                                            <?php esc_html_e( 'Customize bundle products by Manually', SIMILE_TEXT_DOMAIN ); ?>
                                        </a>
                                        <p class="description">
                                            <?php esc_html_e( '', SIMILE_TEXT_DOMAIN ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <strong class="name"><?php esc_html_e( 'Sync products', SIMILE_TEXT_DOMAIN ); ?></strong>
                                    </th>
                                    <td>
                                        <a class="button button-large"
                                           href="<?php echo admin_url( 'admin.php?page=simile-for-woocommerce&tab=sync' ); ?>">
                                            <?php esc_html_e( 'Sync products by Manually', SIMILE_TEXT_DOMAIN ); ?>
                                        </a>
                                        <p class="description">
                                            <?php esc_html_e( '', SIMILE_TEXT_DOMAIN ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr class="submit">
                                    <th colspan="2">
                                        <input type="submit" name="submit" class="button button-primary"
                                               value="<?php esc_html_e( 'Save', SIMILE_TEXT_DOMAIN ); ?>"/>
                                        <input type="hidden" name="action" value="update"/>
                                        <input type="hidden" name="page_options"
                                               value="<?php echo $this->option_string; ?>"/>
                                    </th>
                                </tr>
                            </table>
                        </form>
                    <?php } elseif ( $active_tab === 'vs' ) { ?>
                        <form method="post" action="options.php">
                            <?php wp_nonce_field( 'update-options' ) ?>
                            <table class="form-table">
                                <?php
                                $this->template_config_html('simile_vs_enable','checkbox','Enable Visual Search','Enable or Disable Visual Search feature',0);
                                $this->template_config_html('simile_vs_position','select','Position','The position display for Visual Search section','left_bottom',['Left Bottom'=>'left-bottom','Right Bottom'=>'right-bottom','Left Top'=>'left-top','Right Top'=>'right-top']);
                                $this->template_config_html('simile_vs_active_page','multi-checkbox','Widget Active Page','The page which will display for Visual Search section','',
                                        [
                                        'simile_vs_active_page_all'     => ['desc'=>'All','default'=>1],
                                        'simile_vs_active_page_homepage'=> ['desc'=>'Homepage','default'=>0],
                                        'simile_vs_active_page_category'=> ['desc'=>'Category','default'=>0],
                                        'simile_vs_active_page_product' => ['desc'=>'Product','default'=>0]
                                        ]);
                                ?>
                                <tr>
                                    <th>
                                        <strong class="name"><?php esc_html_e( 'Short Code', SIMILE_TEXT_DOMAIN ); ?></strong>
                                    </th>
                                    <td>
                                        <strong class="name">[simile_vs]</strong>
                                        <br>
                                        <p class="description">
                                            <?php esc_html_e( 'You can add ShortCode [simile_vs] at any page where you want to add.', SIMILE_TEXT_DOMAIN ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <strong class="name"><?php esc_html_e( 'Sync products', SIMILE_TEXT_DOMAIN ); ?></strong>
                                    </th>
                                    <td>
                                        <a class="button button-large"
                                           href="<?php echo admin_url( 'admin.php?page=simile-for-woocommerce&tab=sync' ); ?>">
                                            <?php esc_html_e( 'Sync products by Manually', SIMILE_TEXT_DOMAIN ); ?>
                                        </a>
                                        <p class="description">
                                            <?php esc_html_e( '', SIMILE_TEXT_DOMAIN ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr class="submit">
                                    <th colspan="2">
                                        <input type="submit" name="submit" class="button button-primary"
                                               value="<?php esc_html_e( 'Save', SIMILE_TEXT_DOMAIN ); ?>"/>
                                        <input type="hidden" name="action" value="update"/>
                                        <input type="hidden" name="page_options"
                                               value="<?php echo $this->option_string; ?>"/>
                                    </th>
                                </tr>
                            </table>
                        </form>
                    <?php } elseif ( $active_tab === 'simile' ) { ?>
                    <form method="post" action="options.php">
                        <?php wp_nonce_field( 'update-options' ) ?>
                        <table class="form-table">
                            <tr>
                                <th>
                                    <strong class="name"><?php esc_html_e( 'Enable Simile', SIMILE_TEXT_DOMAIN ); ?></strong>
                                </th>
                                <td>
                                    <select name='<?php echo SIMILE_TEXT_DOMAIN;?>_simile_enable'>
                                        <option
                                                value="1" <?php echo( get_option( SIMILE_TEXT_DOMAIN.'_simile_enable', '1' ) === '1' ? 'selected' : '' ); ?>>
                                            <?php esc_html_e( 'Enable', SIMILE_TEXT_DOMAIN ); ?>
                                        </option>
                                        <option
                                                value="0" <?php echo( get_option( SIMILE_TEXT_DOMAIN.'_simile_enable', '1' ) === '0' ? 'selected' : '' ); ?>>
                                            <?php esc_html_e( 'Disable', SIMILE_TEXT_DOMAIN ); ?>
                                        </option>
                                    </select>
                                    <p>
                                    <span class="description">
                                        <?php esc_html_e( 'Enable this option to show the Similar products in each product.', SIMILE_TEXT_DOMAIN ); ?>
                                    </span>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <strong class="name"><?php esc_html_e( 'Items show in Page', SIMILE_TEXT_DOMAIN ); ?></strong>
                                </th>
                                <td>
                                    <select name="<?php echo SIMILE_TEXT_DOMAIN;?>_simile_items_in_page">
                                        <?php
                                        $i_selected = get_option( SIMILE_TEXT_DOMAIN.'_simile_items_in_page', 6 );
                                        for ( $i=1;$i<21;$i++) {
                                            ?><option value="<?php echo $i;?>" <?php echo( $i_selected == $i ? 'selected' : '' );?>><?php echo $i; ?></option><?php
                                        }
                                        ?>
                                    </select>
                                    <p>
                                    <span class="description">
                                        <?php esc_html_e( 'Number of upsells products is shown in product page.', SIMILE_TEXT_DOMAIN ); ?>
                                    </span>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <strong class="name"><?php esc_html_e( 'columns show in Page', SIMILE_TEXT_DOMAIN ); ?></strong>
                                </th>
                                <td>
                                    <select name="<?php echo SIMILE_TEXT_DOMAIN;?>_simile_columns_in_page">
                                        <?php
                                        $i_selected = get_option( SIMILE_TEXT_DOMAIN.'_simile_columns_in_page', 3 );
                                        for ( $i=1;$i<6;$i++) {
                                            ?><option value="<?php echo $i;?>" <?php echo( $i_selected == $i ? 'selected' : '' );?>><?php echo $i; ?></option><?php
                                        }
                                        ?>
                                    </select>
                                    <p>
                                    <span class="description">
                                        <?php esc_html_e( 'Columns of upsells section show in product page.', SIMILE_TEXT_DOMAIN ); ?>
                                    </span>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <strong class="name"><?php esc_html_e( 'Remove branding', SIMILE_TEXT_DOMAIN ); ?></strong>
                                </th>
                                <td>
                                    <?php
                                        $option_value = get_option( SIMILE_TEXT_DOMAIN.'_simile_remove_branding', 0 );
                                    ?>
                                    <input
                                            name="<?php echo SIMILE_TEXT_DOMAIN;?>_simile_remove_branding"
                                            id="<?php echo SIMILE_TEXT_DOMAIN;?>_simile_remove_branding"
                                            type="checkbox"
                                            class=""
                                            value="1"
                                        <?php checked( $option_value, '1' ); ?>
                                    />
                                    <span class="description">
                                        <?php esc_html_e( 'Remove "Powered by Simile" branding.', SIMILE_TEXT_DOMAIN ); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <strong class="name"><?php esc_html_e( 'Sync products', SIMILE_TEXT_DOMAIN ); ?></strong>
                                </th>
                                <td>
                                    <a class="button button-large"
                                       href="<?php echo admin_url( 'admin.php?page=simile-for-woocommerce&tab=sync' ); ?>">
                                        <?php esc_html_e( 'Sync products by Manually', SIMILE_TEXT_DOMAIN ); ?>
                                    </a>
                                    <p class="description">
                                        <?php esc_html_e( '', SIMILE_TEXT_DOMAIN ); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr class="submit">
                                <th colspan="2">
                                    <input type="submit" name="submit" class="button button-primary"
                                           value="<?php esc_html_e( 'Save', SIMILE_TEXT_DOMAIN ); ?>"/>
                                    <input type="hidden" name="action" value="update"/>
                                    <input type="hidden" name="page_options"
                                           value="<?php echo SIMILE_TEXT_DOMAIN;?>_simile_enable,<?php echo SIMILE_TEXT_DOMAIN;?>_simile_items_in_page,<?php echo SIMILE_TEXT_DOMAIN;?>_simile_columns_in_page,<?php echo SIMILE_TEXT_DOMAIN;?>_simile_remove_branding"/>
                                </th>
                            </tr>
                        </table>
                    </form>
                    <?php } ?>
                </div>
            </div>
            <?php

        }

        function check_shop_info(){
            $registered_shop_info = Simile_Model::get_shop_info();
            $restendpoint = get_rest_url();
            if(substr($restendpoint,-1)=="/"){
                $restendpoint = substr($restendpoint,0,strlen($restendpoint)-1);
            }
            if(isset($_REQUEST['updateDomain']) && $_REQUEST['updateDomain']==="true"){
                $update_shop_info = Simile_Model::update_shop_info();
                if($update_shop_info->website === $restendpoint) {
                    $back_url = get_admin_url()."admin.php?page=simile-smart-bundles-config";
                    ?>
                    <script>
                       alert("Update Success!");
                       window.location = "<?php echo $back_url;?>";
                    </script>
                    <?php
                }
            }
            if(isset($registered_shop_info) && isset($registered_shop_info->website) && $registered_shop_info->website !== $restendpoint){
                ?>
                <p>
                    It seems something about the website's domain has been changed since your last singuped our product.<br>If your confirm update this domain,please Check the button below<br>
                    <a class="button button-large"
                       href="<?php echo admin_url( 'admin.php?page=simile-smart-bundles-config&updateDomain=true' ); ?>">
                        <?php esc_html_e( 'Update to this Domain', SIMILE_TEXT_DOMAIN ); ?>
                    </a>
                </p>
                <?php
            }


        }

    }
} // end if class exists

new Smart_Bundles_Admin_Settings();

?>