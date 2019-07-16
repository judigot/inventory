//========================================CUSTOMER========================================//

//====================GLOBAL VARIABLES====================//
var product_productsTable;
var product_resultHashes = [];
//====================GLOBAL VARIABLES====================//

//==========CONTEXT MENU==========//
$(function () {
    $.contextMenu({
        selector: "#productsTable td",
        items: {
            "viewSalesSummary": {
                name: "Sold quantity"
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
                report_reportType = "productSalesSummary";
                report_loadSalesSummary(report_selectedItem, "product");
                $("#salesSummaryModal").modal({ backdrop: 'static' });
            }
        }
    });
});
//==========CONTEXT MENU==========//

$(function () {
    // Check stocks
    var notifications = ["lowStockNotification", "stockNotification"];
    var notificationLabel = ["Low in stock", "Out of stock"];
    $(".stock-notification").tooltip();
    setInterval(function () {
        post({
            read: "checkStocks"
        }, "Classes/ProductsController").done(function (data) {

            for (var i = 0; i < data.length; i++) {
                var hash = data[i]["hash"];
                var count = data[i]["count"];
                var result = data[i]["result"];

                if (product_resultHashes[i] !== hash) {
                    product_resultHashes[i] = hash;
                    if (count !== 0) {
                        var products = `<strong style='text-decoration: underline;'>${notificationLabel[i]}</strong>:<br>`;
                        $(`#${notifications[i]}`).html(count).css({ "display": "initial" });
                        for (j = 0; j < result.length; j++) {
                            products += "\n" + result[j]["product_name"];
                        }
                        $(`#${notifications[i]}`).attr("data-original-title", products + "<br><br>");
                    } else {
                        $(`#${notifications[i]}`).html("").css({ "display": "none" });
                    }
                }
            }
        }).fail(function (data) { });
    }, 1000);
});

//====================GENERAL====================//
$(function () {
    $(document).on("click", "#newProduct", function (e) {
        $("#newProductModal .modal-body input").val("");
        $("#newProductModal").modal({ backdrop: 'static' });

        var categories = [];

        for (i = 0; i < home_categories.length; i++) {
            var categoryId = home_categories[i]["category_id"];
            var categoryName = home_categories[i]["category_name"];
            categories.push(`<option value="${categoryId}">${categoryName}</option>`);
        }

        $("#productCategoryName").html(categories.join(""));
    });

    $(document).on("click", "#newProductCategory", function (e) {
        $("#newProductCategoryModal .modal-body input").val("");
        $("#newProductCategoryModal").modal({ backdrop: 'static' });
    });

    $(document).on("click", "#confirmNewProductCategory", function (e) {
        var categoryName = $("#newProductCategoryName").val();
        if (categoryName) {

            // Check existing category
            post({
                "read": "checkExistingCategory",
                "data": {
                    "categoryName": categoryName
                }
            }, "Classes/ProductsController").done(function (data) {
                if (data) {
                    $('#newProductCategoryModal').modal('toggle'); // Close modal
                    $("[data-content-trigger='product-categories'").trigger("click");
                    category_categoryTable.ajax.reload();
                    home_loadCategories();
                    custom_notify("Product category was successfully added.", null);
                } else {
                    custom_notify("Product category already exists.");
                }
            });
        }
    });

    $(document).on("click", "#confirmNewProduct", function (e) {
        if ($("#newProductName").val() && $("#newProductInitStock").val()) {
            $('#newProductModal').modal('toggle'); // Close modal
            var newProductValues = {};
            newProductValues["productName"] = $("#newProductName").val();
            newProductValues["productCategory"] = $("#productCategoryName").val();
            newProductValues["productCost"] = $("#newProductCost").val();
            newProductValues["productPrice"] = !order_customPrice ? $("#newProductPrice").val() : false;
            newProductValues["productInitStock"] = $("#newProductInitStock").val();
            $.ajax({
                url: "Classes/process",
                type: "POST",
                dataType: "text",
                data: {
                    create: "insertProduct",
                    data: {
                        productValues: JSON.stringify(newProductValues)
                    }
                }
            }).done(function (data) {
                $("[data-content-trigger='products'").trigger("click");
                product_productsTable.ajax.reload();
                custom_notify("Product was successfully added.", null);
            }).fail(function (data) { });
        } else {
            custom_notify("Product name and initial stock should be filled up.");
        }
    });
});
//====================GENERAL====================//

//====================FUNCTIONS====================//
function product_initial() {
    product_loadProducts();
}

function product_loadCategories() {
    var categories = [];

    for (i = 0; i < home_categories.length; i++) {
        var categoryId = home_categories[i]["category_id"];
        var categoryName = home_categories[i]["category_name"];
        categories.push(`<option value="${categoryId}">${categoryName}</option>`);
    }

    return `<select id='${editor}' class="category-editor" row='1'>${categories.join("")}</select>`;
}

function product_loadProducts() {
    if (!$.fn.DataTable.isDataTable("#productsTable")) {
        product_productsTable = $("#productsTable").DataTable({
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
                url: "Classes/Products",
                type: "POST"
            },
            "initComplete": function () { }
        });
    } else {
        product_productsTable.ajax.reload();
    }

}
//====================FUNCTIONS====================//

//========================================CUSTOMER========================================//