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

if ( ! class_exists( 'Simile_On_Activation' ) ) {

  class Simile_On_Activation {

    public function __construct () {
        register_activation_hook(__FILE__, [ $this, 'on_activation' ]);
    }

    static function on_activation () {
        // create table if not exist
        Simile_Model::initial_db();

        $enable_simile = Simile_Model::enable_simile();
        if(!$enable_simile) {
            $user_info = Simile_Model::signup();
            $shop = $user_info->shop;
            $apiKeys = $shop->apiKeys[0];
            update_option(SIMILE_TEXT_DOMAIN . '_shop_credentials', [
                'shop' => $shop->id,
                'access_token' => $shop->accessToken,
                'client_id' => $apiKeys->clientId,
                'client_secret' => $apiKeys->clientSecret
            ]);
        }

    }

  } // end class
  
} // end if class exists

new Simile_On_Activation();

?>