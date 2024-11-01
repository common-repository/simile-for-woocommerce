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

if ( ! class_exists( 'Simile_Admin' ) ) {

    class Simile_Admin {

        public function __construct () {
            add_action( 'admin_menu', [ $this, 'init_admin_menu' ], 80 ); // Let WooCommerce insert admin menu first
            add_action( 'wp_ajax_refresh_hmac', [ $this, 'refresh_hmac' ] );
            add_filter( 'plugin_action_links',[ $this,'add_action_plugin'], 10, 2 );

            include_once('simile-smart-bundles-admin-settings.php');
            include_once('simile-smart-bundles-admin-iframe.php');
        }

        function refresh_hmac () {
            $hmac = sanitize_text_field( $_POST[ 'hmac' ] );
            if ( isset( $hmac ) ) {
                parse_str( $hmac, $params );

                if (Simile_Model::valid_hmac( $params) ) {
                    echo Simile_Model::hmac_encode_query();
                }
                else {
                    echo -1;
                }
            } else {
                echo -1;
            }
            wp_die();
        }

        function init_admin_menu () {
//            add_submenu_page(
//                'woocommerce', // parent
//                SIMILE_PLUGIN_NAME, // page title
//                SIMILE_PLUGIN_NAME, // menu title
//                'manage_options',
//                SIMILE_TEXT_DOMAIN, // slug
//                [ $this, 'add_submenu_page' ]
//            );
            add_menu_page(
                SIMILE_PLUGIN_NAME,
                SIMILE_PLUGIN_NAME,
                'manage_options',
                SIMILE_TEXT_DOMAIN,
                [ $this, 'add_submenu_main_page' ],
                '',
                58
            );
            add_submenu_page(
                SIMILE_TEXT_DOMAIN,
                SIMILE_PLUGIN_NAME,
                'Smile',
                'manage_options',
                SIMILE_TEXT_DOMAIN,
                [ $this, 'add_submenu_main_page' ]
            );
            unset($GLOBALS['submenu']['smart-bundles'][0]); // del duplicate submenu
        }

        function add_action_plugin( $actions, $plugin_file )
        {
            static $plugin;
            if (!isset($plugin))
                $plugin = plugin_basename(__FILE__);
            if (strrpos($plugin_file,SIMILE_TEXT_DOMAIN)) {
                $settings = array('settings' => '<a href="admin.php?page=simile-smart-bundles-config">' . __('Settings', 'General') . '</a>');
                $resync = array('resync' => '<a href="admin.php?page=simile-for-woocommerce&tab=sync"">' . __('Resync', 'General') . '</a>');
                $actions = array_merge($settings, $actions);
                $actions = array_merge($resync, $actions);
            }
            return $actions;
        }

        function add_submenu_main_page () {
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
            <iframe name="simileAdmin" src="<?php echo SIMILE_ADMIN_URL; ?>?<?php echo Simile_Model::hmac_encode_query(); ?>&website=<?php echo home_url() ?>&tab=<?php echo isset($_REQUEST['tab'])?$_REQUEST['tab']:"" ?>" frameborder="0"></iframe>

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

    } // end class

} // end if class exists

new Simile_Admin();

?>