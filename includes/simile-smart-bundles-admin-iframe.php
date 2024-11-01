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

if ( ! class_exists( 'Smart_Bundles_Admin_Customize' ) ) {

    class Smart_Bundles_Admin_Customize
    {
        public function __construct()
        {
            add_action( 'admin_menu', array( $this, 'add_plugin_page' ),81 );
        }

        public function add_plugin_page(){
            add_submenu_page(
                SIMILE_TEXT_DOMAIN,
                SIMILE_PLUGIN_NAME,
                'Customize Product',
                'manage_options',
                'simile-smart-bundles-customize',
                [ $this, 'add_submenu_main_page' ]
            );
        }

        function add_submenu_main_page () {
            $shop_credentials = get_option( SIMILE_TEXT_DOMAIN . '_shop_credentials' );
            ?>
            <style>
                #wpfooter {
                    display: none !important;
                }
                #wpbody {
                    position: fixed !important;
                    left: 160px !important;
                    right: 0px;
                    bottom: 0px;
                    top: 32px;
                }
                @media all and (max-width: 960px) {
                    #wpbody {
                        left: 36px !important;
                    }
                }
                @media all and (max-width: 782px) {
                    #wpbody {
                        top: 46px;
                        left: 0px !important;
                    }
                }
                @media all and (max-width: 600px) {
                    #wpbody {
                        padding-top: 0px;
                    }
                }
                #wpbody-content {
                    padding: 0px;
                    height: 100%;
                }
                #wpbody-content iframe {
                    z-index: 0;
                    width: 100%;
                    height: 100%;
                    border: 0;
                }
            </style>
            <iframe name="simileAdmin" src="<?php echo SIMILE_ADMIN_URL_CONFIG; ?>?<?php echo Simile_Model::hmac_encode_query(); ?>" frameborder="0"></iframe>

            <script type="text/javascript" >
                // refresh hmac regularly using admin ajax
                var simileAdminAuthentication = {
                    action: 'refresh_hmac',
                    hmac: '<?php echo Simile_Model::hmac_encode_query(); ?>'
                }

                setInterval(function () {
                    jQuery.post(ajaxurl, simileAdminAuthentication, function (response) {
                        if (response !== '-1') {
                            simileAdminAuthentication.hmac = response
                            window.frames.simileAdmin.postMessage({ hmac: response }, '<?php echo SIMILE_ADMIN_URL; ?>')
                        }
                    })
                }, 5 * 60 * 1000) // refresh token every 5 minutes
            </script>
            <?php
        }




    }
} // end if class exists

new Smart_Bundles_Admin_Customize();

?>