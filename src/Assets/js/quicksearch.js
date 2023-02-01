//========================================QUICKSEARCH========================================//

//====================GLOBAL VARIABLES====================//
var search_initial =
  "<input class='search-query' placeholder='Search'><div class='result-list'></div>";
var search_result = "<div class='search-result'></div>";
var search_selected =
  "<div class='selected-result-box'><span class='selected-result'></span></div>";
var search_queryTimeout;
var search_listedProducts = [];
//====================GLOBAL VARIABLES====================//

//====================GENERAL====================//
$(function () {
  $(document).on("keyup", ".search-query", function (e) {
    var elementId = $(this).parent("div").attr("id");
    var element = $("#" + $(this).parent("div").attr("id"));
    element.find(".result-list").css({ display: "none" });
    var query = $(this).val();
    element.find(".result-list").empty();
    clearInterval(search_queryTimeout);
    if (!custom_isEmptyInput(query)) {
      search_queryTimeout = setTimeout(function () {
        search_getResults(elementId, query);
      }, 200);
    } else {
      $(this).val("");
      search_toggleResult(element);
    }
  });

  $(document).on("mousedown", ".list-item", function (e) {
    var elementId = $(this).parent("div").parent("div").attr("id");
    var element = $("#" + elementId);

    var rowValues = JSON.parse($(this).attr("data-row-values"));
    search_setResult(elementId, rowValues);

    //=====CUSTOM=====//
    if (element.attr("class") === "searchable-customer") {
      order_loadPrices();
    }
    //=====CUSTOM=====//
  });

  $(document).on("click", ".selected-result-box", function (e) {
    var elementId = $(this).parent("div").attr("id");
    search_removeResult(elementId);
  });

  $(document).on("focusout", ".search-query", function (e) {
    $(".result-list").css({ display: "none" });
  });

  $(document).on("mouseover", ".list-item", function (e) {
    $(this).css({ "background-color": "#EEEEEE" });
  });

  $(document).on("mouseout", ".list-item", function (e) {
    $(this).css({ "background-color": "white" });
  });
});
//====================GENERAL====================//

//====================FUNCTIONS====================//
function search_searchable(elementId) {
  var element = $("#" + elementId);
  if (!element.is(":empty")) {
    element.empty();
  }
  element.append(search_initial);
  element.find(".result-list").attr("id", elementId + "Datalist");
  element
    .find("input")
    .attr("list", elementId + "Datalist")
    .css({
      "max-width": "350px",
    });

  element.append(search_result);
  $(".result-list").css({
    "box-shadow": "0px 2px 5px 1px rgba(0, 0, 0, 0.2)",
    width: "max-content",
    "min-width": "200px",
    margin: "0% auto 5px auto",
    cursor: "pointer",
    "border-radius": "5px",
    position: "absolute",
    left: "50%",
    transform: "translate(-50%, 0%)",
    "background-color": "white",
    display: "none",
    overflow: "hidden",
  });
}

function search_toggleResult(element) {
  if (element.find(".search-result").length) {
    element.find(".search-result").fadeOut(100, function () {
      $(this).remove();
      element.append(search_result);
    });
  }
}

function search_setResult(elementId, rowValues) {
  var element = $("#" + elementId);
  var elementClass = element.attr("class");
  element.empty().append(search_selected);
  element
    .find(".selected-result")
    .attr({
      id: `${elementId}Selected`,
      "data-option-id": rowValues["row_id"],
      "data-option-identifier": rowValues["row_identifier"],
      "data-option-values": JSON.stringify(rowValues),
    })
    .text(rowValues["row_identifier"]);
  element.find(".selected-result-box").css({
    margin: "auto",
    padding: "10px",
    width: "max-content",
    "font-size": "20px",
    "font-weight": "bold",
    cursor: "pointer",
    "border-radius": "20px",
    "background-color": "#e0b60b",
    "box-shadow": "0px 2px 5px 1px rgba(0, 0, 0, 0.2)",
  });

  //=====CUSTOM=====//
  if (elementClass === "searchable-customer") {
    $("#addProductBox").css({ display: "initial" });
  }
  if (elementClass === "searchable-product") {
    order_computeCost();
    $("#" + element.attr("id") + "Quantity").attr(
      "max",
      rowValues["row_stock"]
    );
    element
      .find(".selected-result")
      .append(`<br><code>₱ ${rowValues["row_price"]}</code>`);
  }
  //=====CUSTOM=====//
}

function search_removeResult(elementId) {
  var element = $("#" + elementId);
  element.empty();
  search_searchable(elementId);

  //=====CUSTOM=====//
  if (element.hasClass("searchable-customer")) {
    $("#pricesListTable").empty();
  }
  if (element.hasClass("searchable-customer")) {
    $("#addProductBox").css({ display: "none" });
  }
  order_computeCost();
  //=====CUSTOM=====//
}

function search_getResults(elementId, query) {
  var resultsContainer = $("#" + elementId);
  var elementClass = resultsContainer.attr("class");
  var searchIdentifier;

  //=====CUSTOM=====//
  if (elementClass === "searchable-customer") {
    searchIdentifier = "customer";
  } else if (elementClass === "searchable-product") {
    searchIdentifier = "product";
  }
  //=====CUSTOM=====//

  var searchData = {
    searchIdentifier: searchIdentifier,
    query: query,
  };
  if (search_listedProducts && searchIdentifier === "product") {
    searchData["listedProducts"] = search_listedProducts;
  }
  $.ajax({
    url: "Classes/process.php",
    type: "POST",
    dataType: "json",
    data: {
      read: "quickSearch",
      data: searchData,
    },
  })
    .done(function (data) {
      if (!data.length) {
        resultsContainer
          .find(".search-result")
          .css({
            color: "white",
            width: "max-content",
            margin: "auto",
            padding: "0px 5px 5px 5px",
            "background-color": "#aaa",
            "border-radius": "0px 0px 10px 10px",
            transition: "50ms",
          })
          .text("No results");
      } else {
        var searchResults = "";
        for (var i = 0; i < data.length; i++) {
          var resultItem = document.createElement("div");

          resultItem.setAttribute("class", "list-item");
          resultItem.setAttribute("value", data[i]["row_identifier"]);

          resultItem.dataset.rowId = data[i]["row_id"];
          resultItem.dataset.rowValues = JSON.stringify(data[i]);

          // Set result identifier as div's text
          resultItem.innerHTML = data[i]["row_identifier"];

          resultsContainer.find(".result-list").append(resultItem);
        }
        search_toggleResult(resultsContainer);
        resultsContainer.find(".result-list").css({ display: "block" });
        $(".list-item").css({
          padding: "5px 10px 5px 10px",
          "font-weight": "bold",
          "text-align": "left",
        });
      }
    })
    .fail(function (data) {});
}
//====================FUNCTIONS====================//

//========================================QUICKSEARCH========================================//
