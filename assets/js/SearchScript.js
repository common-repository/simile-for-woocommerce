(function($) {
    jQuery.fn.simplePopup = function(event) {
        var simplePopup = {
            settings: {
                hashtag: "",
                url: "",
                event: event || "click"
            },

            initialize: function(link) {
                var popup = jQuery(".js__popup");
                var body = jQuery(".js__p_body");
                var close = jQuery(".js__p_close");

                var container_close = jQuery(".js__st_container_close");

                var routePopup = simplePopup.settings.hashtag + simplePopup.settings.url;

                var cssClasses = link[0].className;

                if (cssClasses.indexOf(" ") >= 0) {
                    cssClasses = cssClasses.split(" ");

                    for (key in cssClasses) {
                        if (cssClasses[key].indexOf("js__p_") === 0) {
                            cssClasses = cssClasses[key]
                        }
                    };
                }

                var name = cssClasses.replace("js__p_", "");

                if (name !== "start") {
                    name = name.replace("_start", "_popup");
                    popup = jQuery(".js__" + name);
                    routePopup = simplePopup.settings.hashtag + name;
                };

                link.on(simplePopup.settings.event, function() {
                    simplePopup.show(popup, body, routePopup);
                    return false;
                });

                jQuery(window).on("load", function() {

                });

                body.on("click", function() {
                    simplePopup.hide(popup, body);
                });

                close.on("click", function() {
                    simplePopup.hide(popup, body);
                    return false;
                });

                jQuery(window).keyup(function(e) {
                    if (e.keyCode === 27) {
                        simplePopup.hide(popup, body);
                    }
                });
            },


            centering: function(popup) {
                var marginLeft = -popup.width()/2;
                return popup.css("margin-left", marginLeft);
            },

            show: function(popup, body, routePopup) {
                simplePopup.centering(popup);
                body.removeClass("js__fadeout");
                popup.removeClass("js__slide_top");
            },

            hide: function(popup, body) {
                popup.addClass("js__slide_top");
                body.addClass("js__fadeout");
                location.hash = simplePopup.settings.hashtag;
            },

            hash: function(popup, body, routePopup) {
                if (location.hash === routePopup) {
                    simplePopup.show(popup, body, routePopup);
                }
            }
        };


        return this.each(function() {
            var link = jQuery(this);
            simplePopup.initialize(link);
        });
    };

    jQuery.fn.launchBtn = function(options) {
        var mainBtn, panel, clicks, settings, launchPanelAnim, closePanelAnim, openPanel, boxClick;

        mainBtn = jQuery(".st-button-main");
        panel = jQuery(".st-panel");
        clicks = 0;

        //default settings
        settings = jQuery.extend({
            openDuration: 600,
            closeDuration: 200,
            rotate: true
        }, options);

        //Open panel animation
        launchPanelAnim = function() {
            //     panel.animate({
            //     opacity: "toggle",
            //     height: "toggle"
            // }, settings.openDuration, function() {
            //     //document.getElementById("smart-search-bar-input").focus();
            //     });
            jQuery('#smart-image-search-button').click();
        };

        //Close panel animation
        closePanelAnim = function() {
            document.body.style.webkitOverflowScrolling = "auto";
            document.documentElement.style.webkitOverflowScrolling = "auto";

            panel.animate({
                opacity: "hide",
                height: "hide"
            }, settings.closeDuration, function() {
                jQuery("#p-table tr").remove();
                //var searchBar = document.getElementById("smart-search-bar-input");
                //searchBar.value = '';
                document.getElementsByClassName('popup-container')[0].style.height = "auto";

                if(/Mobi|Android/i.test(navigator.userAgent)) {
                    document.getElementsByClassName('popup-container')[0].style.width = "90vw";
                }
                else
                {
                    document.getElementsByClassName('popup-container')[0].style.width = "500px";
                }
            });
            stopBodyScroll(false);
            jQuery("#uploaded-image").html('');
            window.localStorage.removeItem('image-search');
        };

        //Open panel and rotate icon
        openPanel = function(e) {
            document.body.style.webkitOverflowScrolling = "touch";
            document.documentElement.style.webkitOverflowScrolling = "touch";

            if (true || clicks === 0) {
                if (true ||settings.rotate) {
                    jQuery(this).removeClass('rotateBackward').toggleClass('rotateForward');
                }

                launchPanelAnim();
                // clicks++;
            } else {
                if (settings.rotate) {
                    jQuery(this).removeClass('rotateForward').toggleClass('rotateBackward');
                }

                closePanelAnim();
                // clicks--;
            }
            e.preventDefault();

            return false;
        };

        //Allow clicking in panel
        boxClick = function(e) {
            e.stopPropagation();
        };

        //Main button click
        mainBtn.on('click', openPanel);

        //Prevent closing panel when clicking inside
        panel.click(boxClick);

        //Click away closes panel when clicked in document
        var isDragging = false;
        jQuery(document)
            .mousedown(function() {
                isDragging = false;
            })
            .mousemove(function() {
                isDragging = true;
            })
            .mouseup(function() {
                //isDragging = false;
            });

        jQuery(document).on("click", function() {
            if(isDragging)
            {
                isDragging = false;
                return;
            }
            //return false;
            if(!jQuery('#p-table').is(":hidden")){
                // return false;
                closePanelAnim();
                simile_search_reSetALL();
                if (true || clicks === 1) {
                    mainBtn.removeClass('rotateForward').toggleClass('rotateBackward');
                }
                // clicks = 0;
            }
        });
    };
})(jQuery);

function DragImgUpload(id,options) {
    this.me = jQuery(id);
    var defaultOpt = {
        boxWidth:'auto',
        boxHeight:'150px'
    }
    this.preview = jQuery('<div id="preview"><img /></div>');
    this.opts=jQuery.extend(true, defaultOpt,{
    }, options);
    this.init();
    this.callback = this.opts.callback;
}

DragImgUpload.prototype = {
    init:function () {
        this.me.append(this.preview);
        this.me.append(this.fileupload);
        this.cssInit();
        this.eventClickInit();
    },
    cssInit:function () {
        this.me.css({
            'width':this.opts.boxWidth,
            'height':this.opts.boxHeight,
            'border':'1px solid #cccccc',
            'padding':'10px',
            'cursor':'pointer',
            'margin': '0 20px',
            'background': '#eee',
            'padding-top': '50px'
        })
        this.preview.css({
            'height':'100%',
            'overflow':'hidden'
        })

    },
    onDragover:function (e) {
        e.stopPropagation();
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
    },
    onDrop:function (e) {
        var self = this;
        e.stopPropagation();
        e.preventDefault();
        var fileList = e.dataTransfer.files;

        if(fileList.length == 0){
            return false;
        }
        if(fileList[0].type.indexOf('image') === -1){
            alert("Drag & Drop PNG or JPG File To Search");
            return false;
        }

        var img = window.URL.createObjectURL(fileList[0]);
        var filename = fileList[0].name;
        var filesize = Math.floor((fileList[0].size)/1024);
        if(filesize>500){
            alert("Image filesize 500K max.");
            return false;
        }

        if(this.callback){
            this.callback(fileList);
        }
    },
    eventClickInit:function () {
        var self = this;
        this.me.unbind().click(function () {
            self.createImageUploadDialog();
        });
        var dp = this.me[0];
        dp.addEventListener('dragover', function(e) {
            self.onDragover(e);
        });
        dp.addEventListener("drop", function(e) {
            self.onDrop(e);
        });
    },
    onChangeUploadFile:function (e) {
        var fileInput = this.fileInput;
        var files = fileInput.files;
        var file = files[0];
        if(this.callback){
            this.callback(files);
        }
        if(!/Mobi|Android/i.test(navigator.userAgent)) {
            fileInput.setAttribute('type', 'text');
            fileInput.setAttribute('type', 'file');
        }
    },
    createImageUploadDialog:function () {
        var fileInput = this.fileInput;
        if (!fileInput) {
            fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.name = 'ime-images';
            fileInput.multiple = false;
            //fileInput.accept = ".jpg,.jpeg,.png";
            fileInput.accept = "image/*; capture=camera";
            fileInput.onchange  = this.onChangeUploadFile.bind(this);
            this.fileInput = fileInput;
        }
        fileInput.click();
    }
};


jQuery(function(){
    jQuery(document).ready(function() {
        jQuery('st-actionContainer').launchBtn( { openDuration: 500, closeDuration: 300 } );
        jQuery(".js__st_container_close").on("click", function() {
            simile_search_reSetALL();
            return false;
        });
    });

    var searchIcon = '<i class="fa fa-search"></i>';
    //var iconSvg = '<svg width="25px" height="21px" viewBox="0 0 82 68" aria-hidden="true" focusable="false" role="presentation" class="icon-smart-search"><path d="M29,0 L24,10 L7,10 C3.1211,10 0,13.1211 0,17 L0,61 C0,64.8789 3.1211,68 7,68 L75,68 C78.8789,68 82,64.8789 82,61 L82,17 C82,13.1211 78.8789,10 75,10 L58,10 L53,0 L29,0 Z M41,17 C53.102,17 63,26.8984 63,39 C63,51.1016 53.1016,61 41,61 C28.8984,61 19,51.1016 19,39 C19,26.8984 28.8984,17 41,17 Z M41,25 C33.2227,25 27,31.2227 27,39 C27,46.7812 33.2227,53 41,53 C48.7812,53 55,46.7812 55,39 C55,31.2188 48.7812,25 41,25 Z" id="Shape"></path></svg>'
    //jQuery('.st-button-main').append(iconSvg);

    if(/Mobi|Android/i.test(navigator.userAgent)) {
        document.getElementsByClassName('popup-container')[0].style.width = "90vw";
        document.getElementsByClassName('left-bottom')[0].style.bottom = "1em";
        document.getElementsByClassName('left-bottom')[0].style.left = "1em";

        jQuery('head:last').append('<style>.popup {  position:fixed; z-index:2000; top:10px; left:0%;  width:370px; height:420px; background:#fff;    -moz-box-shadow:4px 4px 30px #130507;    -webkit-box-shadow:4px 4px 30px #130507;  box-shadow:4px 4px 30px #130507;    -moz-transition:top 800ms;    -o-transition:top 800ms;    -webkit-transition:top 800ms;  transition:top 800ms;}  .p_content { width:100%; text-align:center; font-size:18px; padding:0px 0 0;background-color: white }  .p_anch {    float:left; position:relative; z-index:1999; width:100%;    margin:80px 0 0 0; text-align:center; font-size:18px;  }  .p_anch a { color:#000; }  .p_anch a:hover { text-decoration:none; }  .p_anch_bottom { margin:1500px 0 0 0; padding:0 0 50px 0; }  .p_close { position:absolute; top:0px; right:15px; width:16px; height:12px; padding:15px 11px 0 0; }  .p_close span { display:block; width:18px; height:5px; background:#333; }  .p_close span:first-child {      -ms-transform:rotate(45deg);      -webkit-transform:rotate(45deg);x    transform:rotate(45deg);  }  .p_close span:last-child {    margin:-5px 0 0;      -ms-transform:rotate(135deg);      -webkit-transform:rotate(135deg);    transform:rotate(135deg);  }  .p_body {    position:fixed; top:0; left:0; width:100%; height:100%;    background:#000; opacity:0.7;      -moz-transition:opacity 800ms;      -o-transition:opacity 800ms;      -webkit-transition:opacity 800ms;    transition:opacity 800ms;  }/* JS-styles — declaratively */.js__popup {}  .js__p_start {}  .js__p_close {}  .js__p_body {z-index: 1000;}  .js__slide_top { height:0; overflow:hidden; top:0; }  .js__fadeout { height:0; overflow:hidden; opacity:0; }</style>');
        jQuery('head:last').append('<style>.smart-search-bar-uploaded-image { width:20%;margin-left:30px;}  </style>');
        jQuery('head:last').append('<style>.smart-image-search-button{ margin-left:-22px;}  </style>');
        jQuery('.popup-container').append('<div class="p_body js__p_body js__fadeout"></div><div class="popup js__popup js__slide_top"><a href="#" class="p_close js__p_close" title="Закрыть"><span></span><span></span></a><div class="p_content"><h2>UPLOAD IMAGE</h2><div id="drop_area">Drag & Drop PNG or JPG File To Search</div><br/><button id="input_image_file" class="btn btn--has-icon-after cart__continue-btn">Browse Files</button></div></div>');

        let cropper_html = '<div class="p_body js__p_body js__fadeout"></div>' +
            '<div class="popup js__popup js__slide_top">' +
            '<a href="#" class="p_close js__p_close" title=""><span></span><span></span></a>' +
            '<div class="p_content">Crop Your Image' +
            '<img  id="cropperContainerImg" class="cropperContainerImg">' +
            '<br/><button id="cropper_submit" class="btn btn--has-icon-after cart__continue-btn">Submit</button>' +
            ' <button id="cropper_reset" class="btn btn--has-icon-after cart__continue-btn">Reset</button>' +
            '</div>' +
            '</div>';
        jQuery('#smart-search-image-cropper-popup').append(cropper_html);
    }
    else
    {
        jQuery('head:last').append('<style>.popup {  position:fixed; z-index:2000; top:160px; left:50%;  width:560px; height:auto; background:#fff;    -moz-box-shadow:4px 4px 30px #130507;    -webkit-box-shadow:4px 4px 30px #130507;  box-shadow:4px 4px 30px #130507;    -moz-transition:top 800ms;    -o-transition:top 800ms;    -webkit-transition:top 800ms;  transition:top 800ms;}  .p_content { width:100%; text-align:center; font-size:18px; padding:5px 0 0; }  .p_anch {    float:left; position:relative; z-index:1999; width:100%;    margin:80px 0 0 0; text-align:center; font-size:18px;  }  .p_anch a { color:#000; }  .p_anch a:hover { text-decoration:none; }  .p_anch_bottom { margin:1500px 0 0 0; padding:0 0 50px 0; }  .p_close { position:absolute; top:5px; right:15px; width:16px; height:12px; padding:15px 11px 0 0; }  .p_close span { display:block; width:18px; height:5px; background:#333; }  .p_close span:first-child {      -ms-transform:rotate(45deg);      -webkit-transform:rotate(45deg);x    transform:rotate(45deg);  }  .p_close span:last-child {    margin:-5px 0 0;      -ms-transform:rotate(135deg);      -webkit-transform:rotate(135deg);    transform:rotate(135deg);  }  .p_body {    position:fixed; top:0; left:0; width:100%; height:100%;    background:#000; opacity:0.7;      -moz-transition:opacity 800ms;      -o-transition:opacity 800ms;      -webkit-transition:opacity 800ms;    transition:opacity 800ms;  }/* JS-styles — declaratively */.js__popup {}  .js__p_start {}  .js__p_close {}  .js__p_body {z-index: 1000;}  .js__slide_top { height:0; overflow:hidden; top:0; }  .js__fadeout { height:0; overflow:hidden; opacity:0; }</style>');
        jQuery('head:last').append('<style>.smart-search-bar-uploaded-image { width:20%;margin-left:30px; }  </style>');
        jQuery('head:last').append('<style>.smart-image-search-button{ margin-left:-30px;}  </style>');
        jQuery('#smart-search-image-popup').append('<div class="p_body js__p_body js__fadeout"></div><div class="popup js__popup js__slide_top"><a href="#" class="p_close js__p_close" title=""><span></span><span></span></a><div class="p_content"><h2>UPLOAD IMAGE</h2><div id="drop_area">Drag & Drop PNG or JPG File To Search</div><br/><button id="input_image_file" class="btn btn--has-icon-after cart__continue-btn">Browse Files</button></div></div>');
        jQuery("#smart-image-search-button").simplePopup();

        let cropper_html = '<div class="p_body js__p_body js__fadeout"></div>' +
            '<div class="popup js__popup js__slide_top">' +
            '<a href="#" class="p_close js__p_close" title=""><span></span><span></span></a>' +
            '<div class="p_content"><h2>Crop Your Image</h2>' +
            '<img  id="cropperContainerImg" class="cropperContainerImg">' +
            '<br/><button id="cropper_submit" class="btn btn--has-icon-after cart__continue-btn">Submit</button>' +
            ' <button id="cropper_reset" class="btn btn--has-icon-after cart__continue-btn">Reset</button>' +
            '</div>' +
            '</div>';
        jQuery('#smart-search-image-cropper-popup').append(cropper_html);

    }

    var dragImgUpload = new DragImgUpload("#drop_area",{
        callback:function (files) {
            var file = files[0];
            var reader = new FileReader();
            reader.readAsDataURL(file);
            var resizedImage;

            reader.onloadend = function () {
                var image = new Image();
                image.src = reader.result;

                image.onload = function (imageEvent) {
                    var canvas = document.createElement('canvas'),
                        min_size = 299,
                        width = image.width,
                        height = image.height;

                    if (width > height) {
                        width = width / height * min_size;
                        height = min_size;
                    } else {
                        height = height / width * min_size;
                        width = min_size;
                    }
                    canvas.width = width;
                    canvas.height = height;
                    canvas.getContext('2d').drawImage(image, 0, 0, width, height);
                    resizedImage = canvas.toDataURL("image/jpeg", 1);

                    window.localStorage.setItem('image-search', resizedImage);

                    jQuery(".js__popup").addClass("js__slide_top");
                    jQuery(".js__p_body").addClass("js__fadeout");

                    //var searchBar = document.getElementById("search-bar-input");
                    //searchBar.value = 'image done...';

                    cropper(resizedImage);
                    //searchByImage();
                }
            };
        }
    });

    if(/Mobi|Android/i.test(navigator.userAgent)) {
        jQuery('#smart-image-search-button').click(function(){
            jQuery('#drop_area').click();
        });
    }
    else
    {
        jQuery('#input_image_file').click(function(){
            jQuery('#drop_area').click();
        });
    }
    jQuery("#cropper_submit").click(function () {
        var  canvasCropped = cropperObj.getCroppedCanvas();
        var resizedImageCropped = canvasCropped.toDataURL("image/png", 1);
        window.localStorage.setItem('image-search', resizedImageCropped);
        searchByImage();
    });

    jQuery("#cropper_reset").click(function () {
        cropperObj.reset();
    });

    jQuery("#smart-search-image-cropper-popup .js__p_close").click(function () {
        simile_search_reSetALL();
    });
});

function simile_search_reSetALL() {
    changeLoadingStatus("none");
    jQuery("#p-table tr").remove();
    jQuery(".st-panel").hide();
    jQuery(".st-btn-container").show();
    stopBodyScroll(false);
    jQuery("#smart-search-body").hide();
    jQuery("#smart-search-image-cropper-popup").hide();
    cropperObj.destroy();
}

function searchByLanguage() {
    var searchBar = document.getElementById("smart-search-bar-input");

    if(searchBar.value.length < 1)
    {
        return;
    }

    searchBar.blur();

    if(/Mobi|Android/i.test(navigator.userAgent)) {
        document.activeElement.blur();

        if(document.getElementsByClassName('popup-container')[0].style.height != '70vh')
        {
            jQuery('.popup-container').animate({height:'70vh'});
        }
    }
    else
    {
        if(document.getElementsByClassName('popup-container')[0].style.height != '80vh')
        {
            jQuery('.popup-container').animate({height:'80vh'});
        }
    }

    jQuery("#p-table tr").remove();
    changeLoadingStatus("block");

    jQuery.ajax({
        type:"post",
        url:"/apps/search/language",
        headers : {
            "Content-Type" : "application/json"
        },
        data:'{"text" : "' + searchBar.value + '", "domain" : "' + location.origin + '"}',
        processData: false,
        dataType:'json',
        crossDomain: true,
        success:function(result){
            // console.log(result)
            changeLoadingStatus("none");
            jQuery("#p-table tr").remove();
            jQuery(result).each(function(){
                addNewProduct(this);
            });
        },
        error:function(result){
            changeLoadingStatus("none");
        }
    });
}

var cropperObj;
function cropper(resizedImage) {
    stopBodyScroll(false);
    if(!/Mobi|Android/i.test(navigator.userAgent)) {
        marginLeft = jQuery("#smart-search-image-popup .js__popup").css("margin-left");
        jQuery("#smart-search-image-cropper-popup .js__popup").css("margin-left",marginLeft);
    }

    jQuery("#smart-search-image-cropper-popup .js__popup").removeClass("js__slide_top");
    jQuery("#smart-search-image-cropper-popup .js__p_body").removeClass("js__fadeout");
    jQuery("#smart-search-image-cropper-popup").show();

    jQuery("#cropperContainerImg").attr("src",resizedImage);

    cropperObj = new Cropper(document.getElementById('cropperContainerImg'), {
        // initialAspectRatio: NaN,
        viewMode:0,
        autoCropArea:0.9,
        background:false,
        // getCroppedCanvas:{fillColor: '#fff'},
        // autoCrop:false,
        crop(event) {
        }
    });



}

function searchByImage() {
    //var searchBar = document.getElementById("smart-search-bar-input");
    //searchBar.value = '';
    simile_search_reSetALL();
    if(/Mobi|Android/i.test(navigator.userAgent)) {
        if(document.getElementsByClassName('popup-container')[0].style.height != '70vh')
        {
            jQuery('.popup-container').animate({height:'70vh'});
        }
    }
    else
    {
        if(document.getElementsByClassName('popup-container')[0].style.height != '80vh')
        {
            jQuery('.popup-container').animate({height:'80vh'});
        }
    }

    jQuery("#p-table tr").remove();
    changeLoadingStatus("block");

    var current_image = window.localStorage.getItem('image-search');

    jQuery.ajax({
        type:"post",
        url: vsSearchJS.restAPI+"v1/visualSearch",
        headers : {
            "Content-Type" : "application/json"
        },
        data:'{"base64" : "' + current_image + '", "domain" : "' + location.origin + '","timestamp" :"'+Date.parse( new Date())+'"}',
        processData: false,
        dataType:'json',
        crossDomain: true,
        success:function(result){
            // console.log(result)
            jQuery(".st-panel").animate({
                opacity: "toggle",
                height: "toggle"
            }, 600, function() {
                jQuery(".st-btn-container").hide();
                jQuery("#smart-search-body").show();
            });

            changeLoadingStatus("none");

            jQuery("#p-table tr").remove();

            // add image
            jQuery("#uploaded-image").html('<img style="display:block; width:auto;height:40px;" src="'+current_image+'" alt="" />');
            // stop body scroll
            stopBodyScroll(true);

            if(result==null ||  result.length>0){
                jQuery(result).each(function(){
                    addNewProduct(this);
                });
            }else{
                notFoundSearchResult();
            }
        },
        error:function(result){
            // console.log(result)
            changeLoadingStatus("none");
        }
    });
}

var bodyEl = document.body
var top = 0

function stopBodyScroll (isFixed) {
    if (isFixed) {
        top = window.scrollY

        bodyEl.style.position = 'fixed'
        bodyEl.style.top = -top + 'px'
    } else {
        bodyEl.style.position = ''
        bodyEl.style.top = ''

        window.scrollTo(0, top)
    }
}

function addNewProduct(product) {
    var productName = product.name;
    var productLink = product.link;
    var productImage = product.image;
    var productPrice = product.price;

    var numOfCellInRow = 2;

    if(/Mobi|Android/i.test(navigator.userAgent)) {
        numOfCellInRow = 1;
    }
    const cellHTML = '<a target="_blank" rel="noopener noreferrer" href="' + productLink + '">' +
        '<div class="p-cell">' +
        '<img class="p-img" src="' + productImage + '"/>'+
        '<a target="_blank" rel="noopener noreferrer" class="p-name" href="' + productLink + '">' + productName + '</a>' +
        '<div class="p-price">' + productPrice + '</div>' +
        '</div></a>';

    var table = document.getElementById("p-table");
    var rows = table.getElementsByTagName("tr");

    if(rows.length === 0)
    {
        var row = table.insertRow(-1);
        var cell = row.insertCell(-1);
        cell.innerHTML = cellHTML;
    }
    else
    {
        var row = rows[rows.length - 1]

        var cells = row.getElementsByTagName("td")

        if(cells.length < numOfCellInRow)
        {
            var cell = row.insertCell(-1);
            cell.innerHTML = cellHTML;
        }
        else
        {
            var row = table.insertRow(-1);
            var cell = row.insertCell(-1);
            cell.innerHTML = cellHTML;
        }
    }
}

function notFoundSearchResult() {
    document.getElementById("smart-loading").innerHTML="0 SEARCH RESULTS";
    changeLoadingStatus("block");
}

function changeLoadingStatus(status) {
    var x = document.getElementById("smart-loading");
    x.style.display = status;
}
