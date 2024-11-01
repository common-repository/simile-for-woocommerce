=== Simile for WooCommerce ===
Contributors: scopemedia
Donate link: https://scopemedia.com
Tags: woocommerce, upsell, AI, dynamic, product, recommendation, similar, cross-sell, cross, sell
Stable tag: trunk
Requires at least: 4.8
Tested up to: 5.2
WooCommerce tested up to: 3.6.5
Requires PHP: 5.6
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Simile for WooCommerce applies Deep Learning AI technology to recommend related products and product bundles and offer visual search results to your customers.

== Description ==

Simile for WooCommerce offers 3 different AI-powered features to transform your eCommerce store experience and drive more conversions:
1. AI-generated product suggestions in your WooCommerce related products section
2. Frequently Bought Together product bundles in your WooCommerce related products section
3. Visual search where customers can search for products in your store using their own photos

These features enable more dynamic product discovery across your store and boost sales as a result.

##Special Features

Similar Product Suggestions
* Visually similar product suggestions powered by advanced computer vision and AI technology
* Improvement to the static WooCommerce related products slider with more relevant suggestions based on product images
* Customizable layout
* Product variant selection directly on widget
* Ability to edit product suggestions

Smart Product Bundles
* Automated "Frequently Bought Together" bundles powered by AI technology
* Apply discounts to product bundles to encourage impulse purchases
* Flexibility to manually edit bundle products and bundle size
* Customizable text on widget

Visual Search
* Visually similar product search powered by advanced computer vision and AI technology
* Enables customers to upload their own photos to search for similar items in your store's catalogue
* Dedicated mobile optimized window
* Floating Search Icon added to your placement of choice on your eCommerce store

Additional Features
* Background product sync to keep up with changes in your catalogue 
* Quick customer support 


##Simile improves your customer experience!
Thousands of retailers use Simile to deliver dynamic recommendations, product bundles and visual search results that are highly relevant to their customers. Simile's features enhance the opportunity for conversion as a customer browses your store, resulting in increased sales and revenues. 

##Where are the recommendations, product bundles and visual search displayed?
The recommendation and product bundle widgets appear on single product pages under the "Related Products" section. You can select the order in which the widgets appear, or disable/enable either of the widgets at anytime. Product bundles can also show up as a pop-up after a customer adds a product to their cart.

The visual search widget will appear as a floating camera icon in the corner of your store. You can select which corner the icon will appear in. Search results will appear above/below the floating icon. 

##Do I need to do any configuration after install?
Yes, just a little bit! After you install the application, you must sync your existing products and activate the features separately. At any time, you can deactivate the widgets or re-sync your products. If the plugin stops working for any reason, try to do a manual re-sync. 

##How long does it take till the widget goes live?
Depending on the size of your catalog, it may take up to FIVE MINUTES for your first sync to start working on your site.
This is because we are building a neural network on our servers specifically for your store. Once synced, recommendations will happen instantly and your store's AI will get better as your store grows.

##How does Simile generate recommendations?
Simile analyzes your product catalogue images through our cloud services and sends recommendations to your store via our API.

##How does Simile generate product bundles?
Simile analyzes the previous purchases in your store database and uses an Amazon-like AI algorithm to generate relevant product bundles automatically. Each bundle usually contains up to 3 products from different categories. The algorithm has also been optimized for new stores with no orders.

##How does Simile generate visual search results?
Simile analyzes your product catalogue images through our cloud services. Simile will also analyze the image being uploaded by your customer and match it to visually similar products in your catalogue.

##Third-party service
Simile uses [ScopeMedia Inc.](https://scopemedia.com/)'s AI-powered API services to generate lists of visually related products. Simile only needs to process and analyze the product images in your store. It does not share or sell any information to any third parties. 
By activating Simile, you agree to our [Terms of Service](https://scopemedia.com/terms-of-service/) and [Privacy Policy](https://scopemedia.com/privacy-policy/).

##Third-party service links
Store owner admin dashboard url = 'https://apps.scopemedia.com/simile-wc/admin/#/'
Cloud services url = 'https://api.scopemedia.com'


== Installation ==

1. Upload Simile files to the `/wp-content/plugins/simile-for-woocommerce` directory, or install the plugin through the WordPress plugins screen directly.
2. Make sure you have WooCommerce installed and activated.
2. Activate Simile through the 'Plugins' screen in WordPress.
3. Once activated, Simile automatically links to the AI cloud services to process and generate product suggestions, bundles and visual search results.


== Frequently Asked Questions ==

= What do I do if the plugin is not showing up?=

Re-sync Simile and clear your browser cache.

= I have added or removed products, do I need to do anything? =

There is a daily background product sync but you can re-sync your products and clear your browser cache for immediate updates.

= What do I do if the plugin has stopped working? =

Try Re-syncing Simile and clear your browser cache.


== Screenshots ==

1. After installation, Simile will add a new menu item named "Simile" on your Admin Panel.
2. Under "Product Sync" on your Simile dashboard, your can resync your product catalog at any time and set custom sync options.
3. Under the Simile tab, there are various settings for the Similar Product Suggestions widget such as layout and removal of Powered by Simile branding.
4. The Similar Product Suggestion widget will appear below your product listing when enabled.
5. Simile's AI-powered product suggestions are stored in each product's Upsell field in "Linked Products" and can be edited to your preferences.
6. Under the Bundles tab, there are various settings for the Product Bundle widget such as location, text, discount and removal of Powered by Simile branding.
7. In the Bundles tab, there is an option to manually edit bundles to your liking.
8. Edit items in specific bundles and change the size of the bundle (between 2 - 4 products per bundle).
9. The Product Bundle widget will appear below your product listing when enabled. Customers can select the product variant directly from the widget before adding to cart.
10. After adding a bundle to their cart, customers can view their cart.
11. If a discount is applied to your bundle, it will appear as one group in the customer's cart. 
12. In the Visual Search tab, there are various settings for the Visual Search widget such as icon placement and location.
13. The Visual Search icon will appear in the corner of your store and a window will pop up when pressed, prompting the user to upload an image. 
14. Visual Search results will show up above the icon and also display the original image uploaded. 



== Changelog ==
= 1.0.6 =
* New: optimize performance by adding cache server

= 1.0.5 =
* New: Optimize resource loading speed
* Fix: some bugs

= 1.0.4 =
* New: Customize the zoom of the upload image for visual search

= 1.0.3 =
* Fix: Enhanced synchronization compatibility

= 1.0.2 =
* Fix: some bugs

= 1.0.1 =
* New: Visual Search feature
* Fix: some bugs

= 1.0.0 =
* New: bundle feature
* New: customize bundle productions and items
* Update: show simile productions at Upsell section
* New: customize upsell products manually
* Update: add sync settings
* Update: add Simile settings
* Fix: some bugs

= 0.9.3 =
* New: bundle feature
* New: customize bundle productions and items
* Update: show simile productions at Upsell section
* New: customize upsell products manually
* Update: add sync settings
* Update: add Simile settings
* Fix: some bugs


= 0.9.2 =
* Add billing and payment

= 0.9.1 =
* Update: GPL v3 license
* Fix: Sanitize, escape, and validate input data

== Upgrade Notice ==

= 0.9.0 =
Initial Release
