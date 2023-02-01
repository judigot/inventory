/* global order_ordersTable, customer_customersTable */

//========================================HOME========================================//

//====================GLOBAL VARIABLES====================//
var home_appSettings;
var home_categories = [];
//====================GLOBAL VARIABLES====================//

//====================GENERAL====================//
$(function () {
  home_initial();

  home_appSettings = JSON.parse($("body").attr("data-app-settings"));

  $(".modal-dialog").draggable({
    handle: ".title-bar",
  });

  $(document).on("click", "#logout-button", function (e) {
    $.ajax({
      url: "Classes/process",
      type: "POST",
      dataType: "text",
      data: {
        logoutUser: "",
      },
    })
      .done(function (data) {
        location.reload();
      })
      .fail(function (data) {
        window.location.replace("home");
      });
  });
});
//====================GENERAL====================//

//====================FUNCTIONS====================//

function home_initial() {
  home_loadCategories();
}

function home_loadCategories() {
  post(
    {
      read: "getCategories",
    },
    "Classes/ProductsController"
  ).done(function (data) {
    home_categories = data;
  });
}
//====================FUNCTIONS====================//

//========================================HOME========================================//
