/* global product_productsTable, customer_customersTable */

//========================================MODULE/APP NAME========================================//

//====================GLOBAL VARIABLES====================//
var editor = "valueEditor";
var targetColumn;
var newValue;
var referenceValue;
var oldValue;
var selectedCell;
var editor;
var tableType = null;
//====================GLOBAL VARIABLES====================//

//====================GENERAL====================//
$(function () {
  $(document).on(
    "dblclick",
    "#customersTable td:not(:first-child), #productsTable td:not(:first-child), #categoryTable td:not(:first-child)",
    function (e) {
      tableType = $(this).find("span").attr("data-table-type");
      oldValue = $(this).find("span").attr("value");
      referenceValue = $(this).children("span").attr("data-row-id")
        ? $(this).children("span").attr("data-row-id")
        : $(this).siblings("td:has(span)").children("span").attr("data-row-id");
      targetColumn = $(this).find("span").attr("data-column-index");

      if ($(this).find("span").hasClass("date")) {
        $("body").append(`<input id='${editor}' type='date'>`);
      } else if ($(this).find("span").hasClass("category-name")) {
        var categories = product_loadCategories();

        $("body").append(categories);
      } else {
        $("body").append(`<textarea id='${editor}' row='1'></textarea>`);
      }

      $("#" + editor).focus();
      $("#" + editor).val(oldValue);
      $("#" + editor).css({
        "text-align": "center",
        position: "fixed",
        "max-height": "50px",
        "max-width": "200px",
        left: e.pageX + "px",
        top: e.pageY + "px",
        "box-shadow": "0px 0px 5px",
        transform: "translate(-50%, -50%) scale(2)",
      });
    }
  );

  $(document).on("focusout keydown", $("#" + editor), function (e) {
    if (!$("#" + editor).hasClass()) {
      $("#" + editor).is(":visible")
        ? (newValue = $("#" + editor)
            .val()
            .replace(/(\r\n|\n|\r)/gm, ""))
        : false;
    } else {
      newValue = $("#" + editor).val();
    }

    if (
      e.keyCode === 13 ||
      ($("#" + editor).is(":visible") && !$("#" + editor).is(":focus"))
    ) {
      confirmValue(targetColumn, newValue, referenceValue, oldValue);
      $("#" + editor).hide(100, function () {
        $("#" + editor).remove();
      });
    }
  });
});
//====================GENERAL====================//

//====================FUNCTIONS====================//
function confirmValue(targetColumn, newValue, referenceValue, oldValue) {
  if (newValue !== oldValue && newValue.replace(/\s/g, "").length !== 0) {
    update(targetColumn, newValue, referenceValue);
  }
}

function update(targetColumn, newValue, referenceValue) {
  $.ajax({
    url: "Classes/process",
    type: "POST",
    dataType: "text",
    data: {
      update: "updateTable",
      tableType: tableType,
      targetColumn: targetColumn,
      newValue: newValue,
      referenceValue: referenceValue,
    },
  })
    .done(function (data) {
      var targetTable;

      custom_notify(
        `${custom_capitalizeFirstLetter(tableType)} was successfully updated.`,
        null
      );

      switch (tableType) {
        case "product":
          targetTable = product_productsTable;
          break;

        case "customer":
          targetTable = customer_customersTable;
          break;

        case "category":
          targetTable = category_categoryTable;
          break;

        default:
          break;
      }

      targetTable.ajax.reload();
    })
    .fail(function (data) {});
}

function deleteTableRow(referenceValue) {
  $.ajax({
    url: "Classes/process",
    type: "POST",
    dataType: "text",
    data: {
      delete: "toggleRowStatus",
      tableType: tableType,
      referenceValue: referenceValue,
    },
  })
    .done(function (data) {
      custom_notify(
        custom_capitalizeFirstLetter(tableType) + " was successfully deleted.",
        null
      );
      switch (tableType) {
        case "order":
          order_ordersTable.ajax.reload();
          break;
        case "customer":
          customer_customersTable.ajax.reload();
          break;
        case "product":
          product_productsTable.ajax.reload();
          break;
        default:
          break;
      }
    })
    .fail(function (data) {});
}
//====================FUNCTIONS====================//

//========================================MODULE/APP NAME========================================//
