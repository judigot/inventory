//========================================MODULE/APP NAME========================================//

//====================GLOBAL VARIABLES====================//
var routes_pageName = "home";
var routes_homePages = ["orders", "customers"];
//====================GLOBAL VARIABLES====================//

//====================GENERAL====================//
$(function () {
    routes_loadState();
    $(document).on("click", ".content-selector", function (e) {
        //==========CUSTOM==========//
        var state = $(this).attr("data-content-trigger");
        //==========CUSTOM==========//
        if (state !== routes_getCurrentState()) {
            routes_setState(state);
            routes_loadState(state);
        }
    });
    window.onpopstate = function () {
        routes_loadContent(routes_getCurrentState());
    }
});
//====================GENERAL====================//

//====================FUNCTIONS====================//
function routes_loadState(chosenState) {
    var state = chosenState ? chosenState : routes_getCurrentState();
    var validatedState;
    var isValidState = routes_homePages.indexOf(state) !== -1;

    if (isValidState) {
        validatedState = state;
    } else {
        // Default landing page
        validatedState = routes_homePages[0];
        history.replaceState({}, "", `${routes_pageName}/${validatedState}`);
    }

    routes_loadContent(validatedState);
    document.title = validatedState[0].toUpperCase() + validatedState.substr(1) + " - Inventory";
}
function routes_getCurrentState() {
    var state = location.pathname.substring(location.pathname.lastIndexOf("/") + 1);
    return state;
}

function routes_setState(state) {
    history.pushState({}, "", `${routes_pageName}/${state}`);
}

function routes_loadContent(currentState) {
    //==========CUSTOM==========//
    $(".selected-content").removeClass("selected-content");
    $(`[data-content-trigger=${currentState}]`).addClass("selected-content");

    // Change content
    $(".content-box").hide();
    $("." + currentState + "-content").show();
    //==========CUSTOM==========//
    switch (currentState) {
        case routes_homePages[0]:
            order_initial();
            break;
        case routes_homePages[1]:
            customer_initial();
            break;
        default:
            order_initial();
            break;
    }
}
//====================FUNCTIONS====================//

//========================================MODULE/APP NAME========================================//