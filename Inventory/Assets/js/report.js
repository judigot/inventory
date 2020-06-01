//========================================MODULE/APP NAME========================================//

//====================GLOBAL VARIABLES====================//
var report_monthlyGrossSalesData = [];
var report_weeklyGrossSalesData = [];
var report_monthlyGrossProfitData = [];
var report_weeklyGrossProfitData = [];
var report_selectedYear;
var report_selectedWeek;
var report_yearAndWeekData = [];

var report_selectedItem;
var report_reportType;
//====================GLOBAL VARIABLES====================//

//====================GENERAL====================//
$(function () {
    report_loadYearAndWeek();

    $(document).on("change", "#activeYears, #activeWeeks", function (e) {
        report_selectedYear = $("#activeYears").children("option:selected").attr("value");
        report_selectedWeek = $("#activeWeeks").children("option:selected").attr("value");
        report_loadFinancialReports();
    });

    $(document).on("change", "#activeYears1, #activeWeeks1", function (e) {
        report_selectedYear = $("#activeYears1").children("option:selected").attr("value");
        report_selectedWeek = $("#activeWeeks1").children("option:selected").attr("value");
        switch (report_reportType) {
            case "productSalesSummary":
                report_loadSalesSummary(report_selectedItem, "product");
                break;
            case "customerSalesSummary":
                report_loadSalesSummary(report_selectedItem, "customer");
                break;
            case "boughtProducts":
                report_loadBoughtProducts(report_selectedItem);
                break;
            default:
                break;
        }
    });
});

$(document).on("click", ".print-report", function (e) {
    var reportType = $(this).attr("id");
    report_print(reportType);
});
//====================GENERAL====================//

//====================FUNCTIONS====================//
function report_initial() {
    report_loadYearAndWeek().done(function (data) {
        report_loadFinancialReports();
    });
}

function report_loadYearAndWeek() {
    return post({
        read: "getActiveYears"
    }, "Classes/ReportsController").done(function (data) {
        report_yearAndWeekData = data;
        $(".year-selector").empty();
        var currentYear = report_selectedYear ? report_selectedYear : report_yearAndWeekData["currentYear"];
        var yearOptions = "";
        for (var i = 0; i < report_yearAndWeekData["activeYears"].length; i++) {
            yearOptions += "<option value='" + report_yearAndWeekData["activeYears"][i]["year"] + "'>" + report_yearAndWeekData["activeYears"][i]["year"] + "</option>";
        }
        $(".year-selector").append(yearOptions);
        $(".year-selector").find("option[value='" + currentYear + "']").attr("selected", "true");

        $(".week-selector").empty();
        var max = 52;
        var currentWeek = report_selectedWeek ? report_selectedWeek : report_yearAndWeekData["currentWeek"];
        var weekOptions = "";
        for (var i = 0; i < max; i++) {
            weekOptions += "<option value='" + (i + 1) + "'>Week " + (i + 1) + "</option>";
        }
        $(".week-selector").append(weekOptions);
        $(".week-selector").find("option[value='" + currentWeek + "']").attr("selected", "true");

        report_selectedYear = $(".year-selector").children("option:selected").attr("value");
        report_selectedWeek = $(".week-selector").children("option:selected").attr("value");
    });
}

function report_print(reportType) {
    var data = "";
    switch (reportType) {
        case "printAllReports":
            data += "<h4>Year " + report_selectedYear + "</h4>";
            data += "<h2>Monthly Gross Sales</h2>";
            data += custom_quickTable("reportsTable", report_monthlyGrossSalesData, null);

            data += "<h2>Monthly Gross Profit</h2>";
            data += custom_quickTable("reportsTable", report_monthlyGrossProfitData, null);

            data += "<h4>Week " + report_selectedWeek + " of " + report_selectedYear + "</h4>";
            data += "<h2>Weekly Gross Sales</h2>";
            data += custom_quickTable("reportsTable", report_weeklyGrossSalesData, null);

            data += "<h2>Weekly Gross Profit</h2>";
            data += custom_quickTable("reportsTable", report_weeklyGrossProfitData, null);
            break;
        case "printMonthlyGrossSales":
            data += "<h2>Monthly Gross Sales</h2>";
            data += custom_quickTable("reportsTable", report_monthlyGrossSalesData, null);
            break;
        case "printMonthlyGrossProfit":
            data += "<h2>Monthly Gross Profit</h2>";
            data += custom_quickTable("reportsTable", report_monthlyGrossProfitData, null);
            break;
        case "printWeeklyGrossSales":
            data += "<h2>Weekly Gross Sales</h2>";
            data += "<h4>Week " + report_selectedWeek + " of " + report_selectedYear + "</h4>";
            data += custom_quickTable("reportsTable", report_weeklyGrossSalesData, null);
            break;
        case "printWeeklyGrossProfit":
            data += "<h2>Weekly Gross Profit</h2>";
            data += "<h4>Week " + report_selectedWeek + " of " + report_selectedYear + "</h4>";
            data += custom_quickTable("reportsTable", report_weeklyGrossProfitData, null);
            break;
        default:
            alert("Error!");
            break;
    }

    $.ajax({
        url: "_report",
        type: "POST",
        dataType: "text",
        data: {
            print: "printReports",
            data: {
                reportData: data
            }
        }
    }).done(function (data) {
        var win = window.open();
        win.document.write(data);
        win.print();
        win.close();
    }).fail(function (data) { });
}

function report_loadBoughtProducts(rowId) {
    $("#selectedYear1").html(report_selectedYear);
    $.ajax({
        url: "Classes/ReportsController",
        type: "POST",
        dataType: "json",
        data: {
            read: "getBoughtProducts",
            data: {
                "rowId": rowId,
                "selectedYear": report_selectedYear,
                "selectedWeek": report_selectedWeek,
            }
        }
    }).done(function (data) {
        var monthlyTableId = "monthlySalesSummaryTable";
        var weeklyTableId = "weeklySalesSummaryTable";
        $("#" + monthlyTableId + ", #" + weeklyTableId).remove();
        $("#monthlySalesSummaryBox").append(custom_quickTable(monthlyTableId, data[0], false));
        $("#weeklySalesSummaryBox").append(custom_quickTable(weeklyTableId, data[1], false));
    }).fail(function (data) {
        alert(JSON.stringify(data));
    });
}

function report_loadSalesSummary(rowId, type) {
    $("#selectedYear1").html(report_selectedYear);
    $.ajax({
        url: "Classes/ReportsController",
        type: "POST",
        dataType: "json",
        data: {
            read: "getSalesSummary",
            data: {
                "rowId": rowId,
                "selectedYear": report_selectedYear,
                "selectedWeek": report_selectedWeek,
                "salesType": type
            }
        }
    }).done(function (data) {
        var monthlyTableId = "monthlySalesSummaryTable";
        var weeklyTableId = "weeklySalesSummaryTable";
        $("#" + monthlyTableId + ", #" + weeklyTableId).remove();
        $("#monthlySalesSummaryBox").append(custom_quickTable(monthlyTableId, data[0], false));
        $("#weeklySalesSummaryBox").append(custom_quickTable(weeklyTableId, data[1], false));
    }).fail(function (data) { });
}

function report_loadFinancialReports() {
    $("#selectedYear").html(report_selectedYear);
    var data = {
        selectedYear: report_selectedYear
    };
    if (report_selectedWeek) {
        data["selectedWeek"] = report_selectedWeek;
    }
    $.ajax({
        url: "Classes/ReportsController",
        type: "POST",
        dataType: "json",
        data: {
            read: "getFinancialReports",
            data: data
        }
    }).done(function (data) {
        report_monthlyGrossSalesData = data[0];
        report_monthlyGrossProfitData = data[1];
        report_weeklyGrossSalesData = data[2];
        report_weeklyGrossProfitData = data[3];

        $("#monthlyGrossSalesBox").html(custom_quickTable("monthlyGrossSalesTable", data[0], false));
        $("#monthlyGrossProfitBox").html(custom_quickTable("monthlyGrossProfitTable", data[1], false));
        $("#weeklyGrossSalesBox").html(custom_quickTable("weeklyGrossSalesTable", data[2], false));
        $("#weeklyGrossProfitBox").html(custom_quickTable("weeklyGrossProfitTable", data[3], false));
    }).fail(function (data) { });
}
//====================FUNCTIONS====================//

//========================================MODULE/APP NAME========================================//