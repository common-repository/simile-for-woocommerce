function loadScript() {
    addCSSResources();
    addHTML();
}

function addCSSResources() {
return;
    if(!document.getElementById('font-roboto')) {
        var script = document.createElement('link');
        script.id = 'font-roboto';
        script.href = 'https://fonts.googleapis.com/css?family=Roboto';
        script.rel = 'stylesheet';
        script.type = 'text/css';
        document.head.appendChild(script);
    }

    if(!document.getElementById('font-awesome')) {
        var script = document.createElement('link');
        script.id = 'font-awesome';
        script.href = 'https://netdna.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css';
        script.rel = 'stylesheet';
        document.head.appendChild(script);
    }

    if(!document.getElementById('font-awesome-min')) {
        var script = document.createElement('link');
        script.id = 'font-awesome-min';
        script.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css';
        script.rel = 'stylesheet';
        document.head.appendChild(script);
    }
}

function addHTML() {

    var smartSearchBody = document.createElement('div');
    smartSearchBody.innerHTML ='<div id="smart-search-body" class="st_body" style="display: none"></div>';

    var actionContainer = document.createElement('div');
    let widgetPosition = 'left-bottom';
    if(vsSearchJS.position!=''){
        widgetPosition = vsSearchJS.position;
    }
    actionContainer.className = "st-actionContainer "+widgetPosition;

    const innerHTML = '<div class="st-panel">' +
        '<div class="popup-container">' +
        '<form class="smart-search-bar" action="javascript:;" onsubmit="searchByLanguage()" style="margin:auto;max-width:500px">' +
        // '<div id="st-search-topbar"></div>' +
        '<div id="uploaded-image" class="smart-search-bar-uploaded-image"></div>' +
        '<button type="button" id="smart-image-search-button" class="smart-image-search-button js__p_start">' +
        '<svg width="20px" height="17px" viewBox="0 0 82 68" aria-hidden="true" focusable="false" role="presentation" class="smart-icon-search"><path d="M29,0 L24,10 L7,10 C3.1211,10 0,13.1211 0,17 L0,61 C0,64.8789 3.1211,68 7,68 L75,68 C78.8789,68 82,64.8789 82,61 L82,17 C82,13.1211 78.8789,10 75,10 L58,10 L53,0 L29,0 Z M41,17 C53.102,17 63,26.8984 63,39 C63,51.1016 53.1016,61 41,61 C28.8984,61 19,51.1016 19,39 C19,26.8984 28.8984,17 41,17 Z M41,25 C33.2227,25 27,31.2227 27,39 C27,46.7812 33.2227,53 41,53 C48.7812,53 55,46.7812 55,39 C55,31.2188 48.7812,25 41,25 Z" id="Shape"></path></svg>' +
        '</button>' +
        '<a href="#" class="st_container_close js__st_container_close" title=""><span></span><span></span></a>'+
        '</form>' +
        '<div id="smart-loading">Loading...</div>' +
        '<table id="p-table" class="result-table"></table>' +
        '</div>' +
        '</div>' +
        '<div class="st-btn-container left-bottom"><div class="st-button-main">' +
        '<svg width="30px" height="55px" viewBox="0 0 82 68" aria-hidden="true" focusable="false" role="presentation" class="smart-icon-search"><path d="M29,0 L24,10 L7,10 C3.1211,10 0,13.1211 0,17 L0,61 C0,64.8789 3.1211,68 7,68 L75,68 C78.8789,68 82,64.8789 82,61 L82,17 C82,13.1211 78.8789,10 75,10 L58,10 L53,0 L29,0 Z M41,17 C53.102,17 63,26.8984 63,39 C63,51.1016 53.1016,61 41,61 C28.8984,61 19,51.1016 19,39 C19,26.8984 28.8984,17 41,17 Z M41,25 C33.2227,25 27,31.2227 27,39 C27,46.7812 33.2227,53 41,53 C48.7812,53 55,46.7812 55,39 C55,31.2188 48.7812,25 41,25 Z" id="Shape"></path></svg>' +
        '</div></div>' +
        '<div id="smart-search-image-popup"></div>'+
        '<div id="smart-search-image-cropper-popup"></div>';
    actionContainer.innerHTML = innerHTML;

    if(document.body != null)
    {
        document.body.appendChild(smartSearchBody);
        document.body.appendChild(actionContainer);
    }
}

loadScript();