//========================================CUSTOMER========================================//

//====================GLOBAL VARIABLES====================//
var customer_customersTable;
//====================GLOBAL VARIABLES====================//

//==========CONTEXT MENU==========//
$(function () {
    $.ajax({
        url: "Classes/process",
        type: "POST",
        dataType: "json",
        data: {
            read: "getAccessPermission"
        }
    }).done(function (data) {
        if (data) {
            order_isPermitted = true;
        }

        //==========CONTEXT MENU==========//
        var contextMenuItems = {
            "print": { name: "Print receipt" }
        };
        if (order_isPermitted) {
            $.contextMenu({
                selector: "#customersTable td",
                items: {
                    "viewSalesSummary": {
                        name: "Sales summary"
                    },
                    "viewBoughtProducts": {
                        name: "Ordered products"
                    },
                    "delete": {
                        name: "Delete"
                    }
                },
                callback: function (key, options) {
                    var rowId = $(this).children("span").attr("data-row-id") ? $(this).children("span").attr("data-row-id") : $(this).siblings("td:has(span)").children("span").attr("data-row-id");
                    report_selectedItem = rowId;
                    if (key === "delete") {
                        tableType = $(this).find("span").attr("data-table-type");
                        if (confirm("Are you sure you want to delete this " + tableType + "?")) {
                            deleteTableRow(rowId);
                        }
                    }
                    if (key === "viewSalesSummary") {
                        report_reportType = "customerSalesSummary";
                        report_loadSalesSummary(report_selectedItem, "customer");
                        $("#salesSummaryModal").modal({ backdrop: 'static' });
                    }
                    if (key === "viewBoughtProducts") {
                        report_reportType = "boughtProducts";
                        report_loadBoughtProducts(report_selectedItem);
                        $("#salesSummaryModal").modal({ backdrop: 'static' });
                    }
                }
            });
        }
    }).fail(function (data) { });
});
//==========CONTEXT MENU==========//

//====================GENERAL====================//
$(function () {
    $(document).on("click", "#newCustomer", function (e) {
        $("#newCustomerModal .modal-body input").val("");
        $("#newCustomerModal").modal({ backdrop: 'static' });
    });

    $(document).on("click", "#confirmNewCustomer", function (e) {
        if ($("#customerFirstName").val() || $("#customerLastName").val()) {
            $('#newCustomerModal').modal('toggle'); // Close modal
            var newCustomerValues = [];
            $("#newCustomerModal .modal-body *").filter(':input').each(function () {
                newCustomerValues.push($(this).val());
            });
            $.ajax({
                url: "Classes/process",
                type: "POST",
                dataType: "text",
                data: {
                    create: "insertCustomer",
                    data: {
                        customerValues: JSON.stringify(newCustomerValues)
                    }
                }
            }).done(function (data) {
                $("[data-content-trigger='customers'").trigger("click");
                customer_customersTable.ajax.reload();
                custom_notify("Customer was successfully added.", null);
            }).fail(function (data) { });
        } else {
            custom_notify("Customer must have a name.");
        }
    });
});
//====================GENERAL====================//

//====================FUNCTIONS====================//
function customer_initial() {
    customer_loadCustomers();
}

function customer_loadCustomers() {
    var url = home_appSettings["customPrice"] ? "Customers (Egg)" : "Customers (Default)";
    if (!$.fn.DataTable.isDataTable("#customersTable")) {
        customer_customersTable = $("#customersTable").DataTable({
            "pageLength": 10,
            "lengthMenu": [
                [10, 20, 50, 100],
                ["10", "20", "50", "100"]
            ],
            "ordering": false,
            "pagingType": "full",
            "serverSide": false,
            "scrollY": 300,
            "scrollX": true,
            "ajax": {
                url: "Classes/" + url + "",
                type: "POST"
            },
            "initComplete": function () { }
        });
    } else {
        customer_customersTable.ajax.reload();
    }

}
//====================FUNCTIONS====================//

//========================================CUSTOMER========================================//