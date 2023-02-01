/* global home_appSettings */

//========================================ORDER========================================//

//====================GLOBAL VARIABLES====================//
var order_ordersTable;
var order_customerPrices = [];
var order_totalOrderCost;
var order_dateRange = [];
var order_customPrice;
var order_isPermitted;
//====================GLOBAL VARIABLES====================//

//====================GENERAL====================//
$(function () {
  order_customPrice = home_appSettings["customPrice"];

  $(document).on("input change", ".order-range", function (e) {
    var start = $("#startDate").val();
    var end = $("#endDate").val();
    order_dateRange = [];

    if (start && end) {
      order_dateRange = [start, end];
    } else if (start && !end) {
      order_dateRange = [start, null];
    } else if (!start && end) {
      order_dateRange = [null, end];
    } else if (!start && !end) {
      order_dateRange = null;
    }

    $.ajax({
      url: "Classes/Orders",
      type: "POST",
      dataType: "json",
      data: {
        read: "getDateRange",
        data: {
          dateRange: JSON.stringify(order_dateRange),
        },
      },
    })
      .done(function (data) {
        order_ordersTable.clear().draw();
        var newRows = [];
        for (var i = 0; i < data["data"].length; i++) {
          newRows.push(Object.values(data["data"][i]));
        }
        order_ordersTable.rows.add(newRows).draw();
      })
      .fail(function (data) {});
  });

  $(document).on("click", ".click-search", function (e) {
    var clickType = $(this).attr("data-click-type");
    var searchInput;

    switch (clickType) {
      case "id":
        searchInput = `orderID='${$(this).attr("data-row-id")}'`;
        break;
      case "customer":
        searchInput = `customer='${$(this).html()}'`;
        break;
      case "date":
        searchInput = `orderDate='${$(this).attr("data-click-search")}'`;
        break;
      default:
        break;
    }

    order_ordersTable.search(searchInput).draw();
    $("#ordersTable_filter input").select();
  });

  $(document).on("click", "#printOrders", function (e) {
    var customerIds = [];
    $("*[data-row-id]").each(function (e) {
      customerIds.unshift($(this).attr("data-row-id"));
    });
    order_print(customerIds);
  });
  $(document).on("click", "#newOrder", function (e) {
    order_totalOrderCost = 0;
    search_searchable("searchableCustomer");
    $(".total-order-cost").text(custom_monetize(order_totalOrderCost));
    $("#addProductBox").css({ display: "none" });
    $("#newOrderModal").modal({ backdrop: "static" });
    $("#mainOrder, #pricesBox").empty();
    $("#searchQuery").focus();
  });

  $(document).on("input", ".product-quantity", function (e) {
    var max = parseInt($(this).attr("max"));
    var quantity = parseInt($(this).val());

    // Check if quantity is invalid or if quantity exceeds the stock
    if (isNaN(quantity) || quantity <= max) {
      $(".product-quantity").each(function () {
        if (parseInt($(this).val()) % 1 !== 0) {
          $(this).val(Math.round(parseInt($(this).val())));
        }
      });
    } else {
      $(this).val(max);
      custom_notify(
        "Product quantity should not exceed the remaining stock.",
        null
      );
    }
    order_computeCost();
  });

  $(document).on("input", ".item-discount", function (e) {
    order_computeCost();
  });

  $(document).on("click", "#addProduct", function (e) {
    if ($("#searchableCustomerSelected").attr("data-option-id")) {
      var itemId = $("#mainOrder").children().length + 1;
      $("#mainOrder").append(
        $("#fieldSource").children(".order-field").clone()
      );
      $("#mainOrder")
        .children()
        .eq(itemId - 1)
        .children(".searchable-product")
        .attr("id", "orderProduct" + itemId);
      $("#mainOrder")
        .children()
        .eq(itemId - 1)
        .children(".product-quantity")
        .attr("id", "orderProduct" + itemId + "Quantity");
      search_searchable(
        $("#mainOrder")
          .children()
          .eq(itemId - 1)
          .children(".searchable-product")
          .attr("id")
      );
      $("#mainOrder")
        .stop()
        .animate({ scrollTop: $("#mainOrder").prop("scrollHeight") }, 500);
    } else {
      custom_notify("Customer field is required.");
    }
  });

  $(document).on("click", "#removeProduct", function (e) {
    $(this).parent(".order-field").remove();
    order_computeCost();
  });

  $(document).on("click", "#confirmNewOrder", function (e) {
    var customerId = $("#searchableCustomerSelected").attr("data-option-id");
    var errors = false;
    var emptyFields = $("#mainOrder .search-query").filter(function () {
      return !$(this).val();
    }).length;
    if (
      !customerId ||
      $("#mainOrder").children().length === 0 ||
      emptyFields > 0
    ) {
      errors = true;
    }
    $("#mainOrder .product-quantity").each(function () {
      if ($(this).val() === "" || $(this).val() === "0") {
        errors = true;
        return false;
      }
    });
    if (!errors) {
      $("#newOrderModal").modal("toggle"); // Close modal
      var mainOrder = [];
      for (var i = 0; i < $("#mainOrder").children().length; i++) {
        var order = {};
        var optionValues = JSON.parse(
          $("#mainOrder")
            .children()
            .eq(i)
            .find(".selected-result")
            .attr("data-option-values")
        );

        var productId = optionValues["row_id"];
        var cost = optionValues["row_cost"] ? optionValues["row_cost"] : 0;
        var price = order_customPrice
          ? order_customerPrices[
              optionValues["row_identifier"].replace("-", "_").toLowerCase() +
                "_price"
            ]
          : optionValues["row_price"];
        var quantity = $("#mainOrder")
          .children()
          .eq(i)
          .find(".product-quantity")
          .val();
        var discount = $("#mainOrder")
          .children()
          .eq(i)
          .find(".item-discount")
          .val();

        order["productId"] = productId;
        order["cost"] = cost;
        order["price"] = price;
        order["quantity"] = quantity;
        order["discount"] = discount;

        mainOrder.push(order);
      }
      $.ajax({
        url: "Classes/process",
        type: "POST",
        dataType: "text",
        data: {
          create: "insertOrder",
          data: {
            customer: customerId,
            order: JSON.stringify(mainOrder),
            customerPrices: JSON.stringify(order_customerPrices),
          },
        },
      })
        .done(function (data) {
          search_listedProducts.length = 0;
          $("[data-content-trigger='orders'").trigger("click");
          order_ordersTable.ajax.reload();
          custom_notify("Order was successfully added.", null);
          custom_playAudio(
            "#audioName",
            "Assets/sounds/cha-ching SoundEffectsFactory.wav"
          );
        })
        .fail(function (data) {});
    } else {
      custom_notify("All fields are required.");
    }
  });
});
//====================GENERAL====================//

//====================FUNCTIONS====================//
function order_initial() {
  $.ajax({
    url: "Classes/process",
    type: "POST",
    dataType: "json",
    data: {
      read: "getAccessPermission",
    },
  })
    .done(function (data) {
      if (data) {
        order_isPermitted = true;
      }

      //==========CONTEXT MENU==========//
      var contextMenuItems = {
        print: { name: "Print receipt" },
      };
      if (order_isPermitted) {
        contextMenuItems["edit"] = { name: "Edit" };
        contextMenuItems["delete"] = { name: "Delete" };
      }
      $.contextMenu({
        selector: "#ordersTable tr",
        items: contextMenuItems,
        callback: function (key, options) {
          var rowId = $(this).children("td").children("div").attr("data-row-id")
            ? $(this).children("td").children("div").attr("data-row-id")
            : $(this)
                .closest("table")
                .closest("td")
                .siblings("td")
                .eq(0)
                .children("div")
                .attr("data-row-id");
          if (key === "print") {
            var orderId = [
              $(this).find("[data-row-id]").attr("data-row-id")
                ? $(this).find("[data-row-id]").attr("data-row-id")
                : $(this)
                    .closest("table")
                    .closest("tr")
                    .find("[data-row-id]")
                    .attr("data-row-id"),
            ];
            order_print(orderId);
          }
          if (key === "edit") {
            order_editOrder(rowId);
          }
          if (key === "delete") {
            tableType = "order";
            if (confirm("Are you sure you want to delete this order?")) {
              deleteTableRow(rowId);
            }
          }
        },
      });
      //==========CONTEXT MENU==========//
    })
    .fail(function (data) {});

  order_loadOrders();
}

function order_print(customerIds) {
  $.ajax({
    url: !order_customPrice ? "_receipt" : "_receipt",
    type: "POST",
    dataType: "text",
    data: {
      print: "printOrders",
      data: {
        orderIds: JSON.stringify(customerIds),
      },
    },
  })
    .done(function (data) {
      var win = window.open();
      win.document.write(data);
      win.print();
      win.close();
    })
    .fail(function (data) {});
}

function order_editOrder(orderId) {
  post(
    {
      read: "getOrderdItems",
      data: {
        orderId: orderId,
      },
    },
    "Classes/process"
  ).done(function (data) {
    var productsTable = order_editOrderTable("editOrderTable", data);
    $("#editOrderModal").modal({ backdrop: "static" });
    $("#orderedProductsContainer").html(productsTable);
  });
}

var order_orderProducts = [];
var order_deletedProducts = [];

$(function () {
  $(document).on("click", ".delete-order-product", function (e) {
    var rowId = $(this).attr("data-row-id");
    order_deletedProducts.push(rowId);
    $(this).parent("td").parent("tr").remove();
  });

  $(document).on("change", ".editOrderTable-item", function (e) {
    order_markEditedItems();
  });

  $(document).on("click", "#confirmOrderEdits", function (e) {
    $("#editOrderModal").modal("toggle"); // Close modal
    editedItems =
      order_getEditedItems().length !== 0 ? order_getEditedItems() : "false";
    deletedItems =
      order_deletedProducts.length !== 0 ? order_deletedProducts : "false";

    if (editedItems !== "false" || deletedItems !== "false") {
      post(
        {
          update: "updateOrderItems",
          data: {
            editedItems: editedItems,
            deletedItems: deletedItems,
          },
        },
        "Classes/process"
      )
        .done(function (data) {
          order_ordersTable.ajax.reload();
          order_orderProducts.length = 0;
          order_deletedProducts.length = 0;
          custom_notify("Order was successfully edited.", null);
        })
        .fail(function (data) {
          alert(JSON.stringify(data));
        });
    }
  });
});

function order_getEditedItems() {
  var editedItems = [];
  Array.prototype.forEach.call(
    document.querySelectorAll(".editOrderTable-item"),
    function (element, i) {
      var value = parseInt(element.getAttribute("value"));
      var remainingStocks = parseInt(
        element.getAttribute("data-product-stock")
      );
      var newQuantity = parseInt(element.value);
      if (value !== newQuantity) {
        var itemAttributes = {};
        itemAttributes["id"] = element.getAttribute("data-row-id");
        itemAttributes["oldQuantity"] = element.getAttribute("value");
        itemAttributes["newQuantity"] = newQuantity;
        editedItems.push(itemAttributes);
      }
    }
  );
  return editedItems;
}

function order_markEditedItems() {
  Array.prototype.forEach.call(
    document.querySelectorAll(".editOrderTable-item"),
    function (element, i) {
      var itemId = element.getAttribute("data-row-id");
      var value = parseInt(element.getAttribute("value"));
      var newQuantity = parseInt(element.value);
      var itemName = document.querySelector(`.item-${itemId}-name`);
      if (
        (value !== newQuantity && !itemName.hasAttribute("itemEdited")) ||
        (value === newQuantity && itemName.hasAttribute("itemEdited"))
      ) {
        itemName.toggleAttribute("itemEdited");
      }
    }
  );
}

function order_editOrderTable(tableIdentifier, data, tfoot) {
  var hiddenRows = [0];
  var columnNames = Object.keys(data[0]);
  let theadHTML = (tbodyHTML = tfootHTML = "");
  var tableElements = {
    table: tableIdentifier + "-tbody-qt tbody-qt",
    th: tableIdentifier + "-th-qt th-qt",
    tr: tableIdentifier + "-tr-qt tr-qt",
    td: tableIdentifier + "-td-qt td-qt",
    tfoot: tableIdentifier + "-tfoot-qt tfoot-qt",
  };
  for (var i = 0; i < columnNames.length; i++) {
    if (!hiddenRows.includes(i)) {
      theadHTML += `<th class='${tableElements["th"]}'>${columnNames[i]}</th>`;
    }
  }
  theadHTML += `<th class='${tableElements["th"]}'><i class="fa fa-wrench"></i></th>`;
  for (var i = 0; i < data.length; i++) {
    tbodyHTML += `<tr class='${tableElements["tr"]}'>`;
    for (var j = 0; j < columnNames.length; j++) {
      var rowData = data[i][columnNames[j]] ? data[i][columnNames[j]] : "-";
      var productStock = data[i][columnNames[2]];

      if (!hiddenRows.includes(j)) {
        if (j === 3) {
          // Quantity

          if (productStock == 0) {
            max = rowData;
          } else {
            max = parseInt(productStock) + parseInt(rowData);
          }

          tbodyHTML += `<td class='${
            tableElements["td"]
          }'><input class="${tableIdentifier}-item" data-product-stock="${productStock}" data-row-id="${
            data[i][columnNames[0]]
          }" type="number" min="1" max="${max}" value="${rowData}"></td>`;
        } else if (j === 1) {
          // Add class identifier to item name
          tbodyHTML += `<td class='${tableElements["td"]} item-${
            data[i][columnNames[0]]
          }-name'>${rowData}</td>`;
        } else {
          tbodyHTML += `<td class='${tableElements["td"]}'>${rowData}</td>`;
        }
      } else if (j === 0) {
        order_orderProducts.push(rowData);
      }
    }
    tbodyHTML += `<td class='${tableElements["td"]}'><button data-row-id="${
      data[i][columnNames[0]]
    }" class="btn delete-order-product">Delete</button></td>`;
    tbodyHTML += "</tr>";
  }
  if (tfoot) {
    tfootHTML += `<tfoot><tr class='${tableElements["tr"]}'>`;
    for (var i = 0; i < tfoot.length; i++) {
      tfootHTML += `<td class='${tableElements["td"]}'>${tfoot[i]}</td>`;
    }
    tfootHTML += "</tr></tfoot>";
  }
  return `<table id='${tableIdentifier}' class='${tableElements["table"]}'>\n\
            <thead><tr class='${tableElements["tr"]}'>${theadHTML}</tr></thead>\n\
            <tbody>${tbodyHTML}</tbody>${tfootHTML}\
            </table>`;
}

function order_loadPrices() {
  $.ajax({
    url: "Classes/process",
    type: "POST",
    dataType: "json",
    data: {
      read: "getCustomerPrices",
      customerId: $("#searchableCustomerSelected").attr("data-option-id"),
    },
  })
    .done(function (data) {
      if (order_customPrice) {
        order_customerPrices = data[0][0];
        $("#pricesBox")
          .empty()
          .append(custom_quickTable("pricesListTable", data[1], false));
      }
      order_computeCost();
    })
    .fail(function (data) {});
}

function order_loadOrders() {
  if (!$.fn.DataTable.isDataTable("#ordersTable")) {
    order_ordersTable = $("#ordersTable").DataTable({
      drawCallback: function (settings) {
        var total = 0;
        var totalProfit = 0;
        Array.prototype.forEach.call(
          document.querySelectorAll(".order-price"),
          function (element, i) {
            total += parseFloat(element.getAttribute("value"));
            totalProfit += parseFloat(
              element.getAttribute("data-total-profit")
            );
          }
        );

        if (order_isPermitted) {
          var tableId = "financialSummary";
          var tableData = [
            {
              "Gross Sales": "₱ " + custom_monetize(total),
              "Gross Profit": "₱ " + custom_monetize(totalProfit),
            },
          ];
          document.querySelector("#financialSummaryBox").innerHTML =
            custom_quickTable(tableId, tableData, false);
        }
      },
      pageLength: 10,
      lengthMenu: [
        [10, 20, 50, -1],
        ["10", "20", "50", "all"],
      ],
      ordering: false,
      pagingType: "full",
      serverSide: false,
      scrollY: 300,
      scrollX: true,
      ajax: {
        url: "Classes/Orders",
        type: "POST",
        dataType: "json",
      },
      columns: [
        null,
        null,
        {
          searchable: true,
        },
        {
          searchable: true,
        },
        {
          visible: false,
        },
        {
          visible: false,
        },
        {
          visible: false,
        },
        {
          visible: false,
        },
        {
          visible: false,
        },
        {
          visible: false,
        },
        {
          visible: false,
        },
        {
          visible: false,
        },
        {
          visible: false,
        },
      ],
      initComplete: function () {},
    });
  } else {
    order_ordersTable.ajax.reload();
  }
}

function order_computeCost() {
  var customerId = $("#searchableCustomerSelected").attr("data-option-id");
  order_totalOrderCost = 0;
  search_listedProducts.length = 0;
  if (customerId) {
    $("#mainOrder .order-field").each(function () {
      var product = $(this)
        .find(".selected-result")
        .attr("data-option-identifier")
        ? $(this).find(".selected-result").attr("data-option-identifier") +
          (order_customPrice ? "_price" : "")
        : null;
      var quantity = $(this).find(".product-quantity").val();
      var discount = $(this).find(".item-discount").val();
      if (product) {
        var optionValues = JSON.parse(
          $(this).find(".selected-result").attr("data-option-values")
        );
        if (quantity) {
          var productPrice;
          if (order_customPrice) {
            product = product.replace("-", "_").toLowerCase();
            productPrice = order_customerPrices[product]
              ? parseFloat(order_customerPrices[product])
              : 0;
          } else {
            productPrice = optionValues["row_price"]
              ? optionValues["row_price"]
              : 0;
          }
          order_totalOrderCost +=
            productPrice * quantity - (discount ? discount : 0);
        }
        search_listedProducts.push(optionValues["row_id"]);
      }
    });
  }
  $(".total-order-cost").text(custom_monetize(order_totalOrderCost));
}
//====================FUNCTIONS====================//

//========================================ORDER========================================//
