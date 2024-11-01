<?php
/** 
 * Plugin Name: Simile for WooCommerce
 * Plugin URI:  https://wordpress.org/plugins/simile-for-woocommerce/
 * Description: Simile applies Deep Learning AI technology to discover & recommend visually related products to your customers.
 * Author: Scopemedia
 * Author URI: https://scopemedia.com
 * Version: 1.0.6
 * Text Domain: simile-for-woocommerce

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

if ( ! class_exists( 'Simile' ) ) {

  final class Simile {
  
    protected static $_instance = null;
  
    public static function instance () {
      if ( is_null( self::$_instance ) ) {
        self::$_instance = new Simile;
      }
      return self::$_instance;
    }
  
    public function __clone () {
      // Cloning instances of the class is forbidden
      _doing_it_wrong( __FUNCTION__, __( 'No clone', SIMILE_TEXT_DOMAIN ), SIMILE_VERSION );
    }
  
    public function __wakeup () {
      // Unserializing instances of the class is forbidden
      _doing_it_wrong( __FUNCTION__, __( 'No wakeup', SIMILE_TEXT_DOMAIN ), SIMILE_VERSION );
    }
  
    public function __construct () {
        $this->includes();
        register_activation_hook(__FILE__, [ $this, 'on_activation' ]);
        register_deactivation_hook(__FILE__, [ $this, 'on_deactivation' ]);
    }

    private function includes () {
      include_once( 'includes/simile-config.php' );
      include_once( 'includes/simile-model.php' );
      include_once( 'includes/simile-on-activation.php' );
      include_once( 'includes/simile-on-deactivation.php' );
      include_once( 'includes/simile-rest-api.php' );
      include_once( 'includes/simile-related-products.php' );
      include_once( 'includes/simile-admin.php' );
      include_once( 'includes/simile-smart-bundles-products-view.php' );
      include_once( 'includes/simile-visual-search.php');
    }

    public function on_activation () {
      Simile_On_Activation::on_activation();
    }

    public function on_deactivation () {
      Simile_On_Deactivation::on_deactivation();
    }
  
  } // end class
  
} // end if class exists
  
Simile::instance();

?>