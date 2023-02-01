//========================================MODULE/APP NAME========================================//

//====================GLOBAL VARIABLES====================//
var category_categoryTable;
//====================GLOBAL VARIABLES====================//

//====================GENERAL====================//
$(function () {});
//====================GENERAL====================//

//====================FUNCTIONS====================//
function category_initial() {
  category_loadCategories();
}

function category_loadCategories() {
  var url = "Category";
  if (!$.fn.DataTable.isDataTable("#categoryTable")) {
    category_categoryTable = $("#categoryTable").DataTable({
      pageLength: 10,
      lengthMenu: [
        [10, 20, 50, 100],
        ["10", "20", "50", "100"],
      ],
      ordering: false,
      pagingType: "full",
      serverSide: false,
      scrollY: 300,
      scrollX: true,
      ajax: {
        url: "Classes/" + url + "",
        type: "POST",
      },
      initComplete: function () {},
    });
  } else {
    category_categoryTable.ajax.reload();
  }
}
//====================FUNCTIONS====================//

//========================================MODULE/APP NAME========================================//
