jQuery( document ).ready( function( $ ) {


    $("button[name='buy-bundle-now']").on("click",function(){
        var $thisbutton = $(this);
            // $form = $thisbutton.closest('form.cart'),
            // id = $thisbutton.val();
            // product_qty = $form.find('input[name=quantity]').val() || 1,
            // product_id = $form.find('input[name=product_id]').val() || id,
            // variation_id = $form.find('input[name=variation_id]').val() || 0;

        var variation_array = [];
        $('select.sm-bundle-items-variants-select').each(function(){
            variation_array[this.id] =  $(this).val();
        });

        let data = {
            action: 'woocommerce_ajax_add_to_cart',
            product_id: bundlesWidgetJS_data.productId,
            product_sku: '',
            quantity: 1,
            variation_id: variation_array,
            bundle:bundlesWidgetJS_data.bundle,
            bundle_config:bundlesWidgetJS_data.bundle_config
        };

        $.ajax({
            type: 'post',
            url: wc_add_to_cart_params.ajax_url,
            data: data,
            beforeSend: function (response) {
                $thisbutton.removeClass('added').addClass('loading');
            },
            complete: function (response) {
                $thisbutton.addClass('added').removeClass('loading');
            },
            success: function (response) {
                if (response.error & response.product_url) {
                  //  window.location = response.product_url;
                    return;
                } else {
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
                    $("button[name='buy-bundle-now']").hide();
                    $("#bundle-tips").html(bundlesWidgetJS_data['bundle_config']['bundles_discount_info_message_after_added_cart_message']);
                }
            },
        });
    });

    $("select.sm-bundle-items-variants-select").on("change",function(event) {
        var $parent_product = event.target.id;
        var select_product = $( event.target ).val();
        var data = eval('bundlesWidgetJS_data_variations_'+$parent_product);
        data = data[select_product];
        // image
        $( event.target).closest('li').find('img').attr('src',data.img_src);
        $( event.target).closest('li').find('img').attr('srcset',data.img_srcset);
        $( event.target).closest('li').find('span.sm-bundle-items-has-compare-at-price').html(simile_format_price(count_single_product_price(data.display_price)));
        $( event.target).closest('li').find('span.sm-bundle-items-compare-at-price').html(simile_format_price(data.display_price));
        // recount amount price
        count_total_price();

    });

    var count_single_product_price = function (display_price) {
        var percent_discount = (bundlesWidgetJS_data['bundle_config']['percent_discount'])?bundlesWidgetJS_data['bundle_config']['percent_discount']:0;
        var bundle_price = ( 100 - percent_discount ) * display_price / 100;
        return bundle_price.toFixed(2);

    };

    var count_total_price = function(){
         if(bundlesWidgetJS_data['bundle'].length<2) return;
         var total_price = 0.00;
         var bundle_price = 0.00;

         $('span.sm-bundle-items-has-compare-at-price').each(function(){
             var price = $(this).text().substr(1);
             bundle_price = parseFloat(bundle_price)+parseFloat(price);
             bundle_price =bundle_price.toFixed(2);
         });

         $('span.sm-bundle-items-compare-at-price').each(function(){
             var price = $(this).text().substr(1);
             total_price = parseFloat(total_price)+parseFloat(price);
             total_price = total_price.toFixed(2);
         });

         $('#sm-bundle-price').html(simile_format_price(bundle_price));
         $('#sm-bundle-total-price').html(simile_format_price(total_price));
    };

    function simile_format_amount(amount, decimalCount = 2, decimal = ".", thousands = ",") {
        try {
            decimalCount = Math.abs(decimalCount);
            decimalCount = isNaN(decimalCount) ? 2 : decimalCount;

            const negativeSign = amount < 0 ? "-" : "";

            let i = parseInt(amount = Math.abs(Number(amount) || 0).toFixed(decimalCount)).toString();
            let j = (i.length > 3) ? i.length % 3 : 0;

            return negativeSign + (j ? i.substr(0, j) + thousands : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands) + (decimalCount ? decimal + Math.abs(amount - i).toFixed(decimalCount).slice(2) : "");
        } catch (e) {
            console.log(e)
        }
    };

    var simile_format_price = function(price){
        price_html = '';
        price = simile_format_amount(price,bundlesWidgetJS_data.number_of_decimals,bundlesWidgetJS_data.decimal_separator,bundlesWidgetJS_data.thousand_separator);
        switch (bundlesWidgetJS_data.currency_position) {
            case "left":
                price_html = bundlesWidgetJS_data.currency_symbol+price;
                break;
            case "right":
                price_html = price+bundlesWidgetJS_data.currency_symbol;
                break;
            case "left_space":
                price_html = bundlesWidgetJS_data.currency_symbol+' '+price;
                break;
            case "right_space":
                price_html = price+' '+bundlesWidgetJS_data.currency_symbol;
                break;
            default:
                price_html = bundlesWidgetJS_data.currency_symbol+price;
            
        }
        return price_html;
    };

    count_total_price();

});

