// Custom Metrics
// Index	Name
// 1	Widget click
// 2	Add to cart click
// 3	Buy it now click  (N/A)
// 4	Widget load
// 5	Widget Add to Cart click

jQuery( document ).ready( function( $ ) {

    simile_analytics_data.client_id=_Simile_plugin_function_getUUID();
 	//pageview
    $.ajax({
        type: 'get',
        url: simile_analytics_data.server+"pageview",
        data: {
            url: window.location.href,
            client_ip: simile_analytics_data.client_ip,
            client_id: simile_analytics_data.client_id
        }
    });
    // send event 4 for bundle widget load
    send_event(4,"BUNDLE_WP");
    // send event 4 for simile widget load
    send_event(4,"SIMILE_WP");

    // event 1  for simile --click
    $('section.up-sells ul.products li.product a.woocommerce-LoopProduct-link').on("click",function(){
        let item_id = _Simile_plugin_function_getProductIdByClassList($(this).parent('li').attr('class').split(/\s+/));
        send_event(1,"SIMILE_WP",item_id);
        this.href = _Simile_plugin_function_addHrefArgs(this.href);
    });

    // event 5 for simile -- add to cart
    $('section.up-sells ul.products li.product a.ajax_add_to_cart').on("click",function(){
        let item_id = _Simile_plugin_function_getProductIdByClassList($(this).parent('li').attr('class').split(/\s+/));
        send_event(5,"SIMILE_WP",item_id);
    });


    //event 1 for bundle --click
    $('div.sm-bundle-items-image-group-container a.woocommerce-LoopProduct-link').on("click",function(){
        let item_id = _Simile_plugin_function_getProductIdByClassList($(this).parent('li').attr('class').split(/\s+/));
        send_event(1,"BUNDLE_WP",item_id);
        this.href = _Simile_plugin_function_addHrefArgs(this.href);
    });

    //event 5 for bundle  --add to cart
    $("button[name='buy-bundle-now']").on("click",function(){
        send_event(5,"BUNDLE_WP");
    });

    // event 2  for product button, common action --add to cart
    $("button[name='add-to-cart']").on("click",function(){
        send_event(2,"SIMILE_WP");
    });

    function send_event(eventId,widget,item_id=''){
        if( item_id == '' ){
            item_id= simile_analytics_data.item_id
        }
        $.ajax({
            type: 'get',
            url: simile_analytics_data.server+"event",
            data: {
                shop_id: simile_analytics_data.shop_id,
                widget_name: widget,
                client_id: simile_analytics_data.client_id,
                client_ip: simile_analytics_data.client_ip,
                provider: simile_analytics_data.provider,
                from_widget: _Simile_plugin_function_fromWidget(),
                action_number: eventId,
                item_id: item_id,
            }
        });
    }
} );



// utils start
function _Simile_plugin_function_fromWidget() {
    return (window.location.search.indexOf("?sm") !== -1 || window.location.search.indexOf("&sm") !== -1).toString();
}

function _Simile_plugin_function_addHrefArgs(url) {
    return (url.indexOf("?") === -1)?url+'?sm=true':url+'&sm=true';
}

function _Simile_plugin_function_getProductIdByClassList(classList) {
    for (var i = 0; i < classList.length; i++) {
        if (classList[i].indexOf("post-") !== -1) {
            return classList[i].substr(5);
        }
    }
}
// utils finish

//get uuid
function _Simile_plugin_function_getUUID(){
    let uuid =  _Simile_plugin_function_getCookie("sm_ga");
    if(uuid==''){
        uuid = _Simile_plugin_function_generateUUID();
    }
    return uuid;
}
function _Simile_plugin_function_generateUUID() {
    let uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
        return v.toString(16);
    });
    _Simile_plugin_function_setCookie("sm_ga", uuid);
    return uuid;
}
function _Simile_plugin_function_setCookie(name, value) {
    var exp = new Date();
    exp.setTime(exp.getTime() + 63072000);
    document.cookie = name + "=" + escape(value) + ";path=/;expires=" + exp.toGMTString();
}
function _Simile_plugin_function_getCookie(name) {
    var strCookie = document.cookie;
    var arrCookie = strCookie.split("; ");
    for ( var i = 0; i < arrCookie.length; i++) {
        var arr = arrCookie[i].split("=");
        if (arr[0] == name) {
            return arr[1];
        }
    }
    return "";
}
//finish get uuid