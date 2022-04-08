<?php

if ($_POST || $_FILES) {
    session_status() == PHP_SESSION_NONE ? session_start() : false;

    require_once 'global.php';
    require_once '../Imports/phpDefaults.php';
    require_once 'Database.php';
    require_once 'Tools.php';

    $connection = Database::Connect($Host, $DatabaseName, $Username, $Password);

    if ($connection) {
        if (isset($_POST['read'])) {

            if ($_POST['read'] == "getActiveYears") {
                $data = array(
                    "currentYear" => date("Y"),
                    "activeYears" => Database::Read($connection, "SELECT DISTINCT DATE_FORMAT(`order_date`, '%Y') AS `year` FROM `$app_order`;"),
                    "currentWeek" => Tools::getCurrentWeekNumber(),
                    "maxWeekNumber" => 52,
                );
                echo json_encode($data);
            }

            if ($_POST['read'] == "getSalesSummary") {
                $data = [];
                $year = $_POST["data"]["selectedYear"];
                $week = $_POST["data"]["selectedWeek"];
                $selectedWeek = $_POST["data"]["selectedWeek"];
                $weekDates = Tools::getWeekDates($year, $selectedWeek);
                $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

                if ($_POST["data"]["salesType"] === "product") {
                    //======================================PRODUCT======================================//
                    //==============MONTHLY=PRODUCT=QUANTITY==============//
                    $productSalesSql = "SELECT COALESCE(SUM(`quantity`), '-') AS `%s` FROM `$app_order` INNER JOIN `$app_order_product` ON `$app_order`.`order_id`=`$app_order_product`.`order_id` WHERE `product_id` = '{$_POST["data"]["rowId"]}' AND `order_date` BETWEEN '%s' AND '%s'";
                    $tableDetails = [
                        "Month" => $months,
                        "Week 1 (1 - 7)" => $productSalesSql,
                        "Week 2 (8 - 14)" => $productSalesSql,
                        "Week 3 (15 - 21)" => $productSalesSql,
                        "Week 4 (22 - 31)" => $productSalesSql,
                        "Total Quantity" => $productSalesSql,
                    ];
                    $weekRange = [
                        ["%u-%s-01", "%u-%s-07"],
                        ["%u-%s-08", "%u-%s-14"],
                        ["%u-%s-15", "%u-%s-21"],
                        ["%u-%s-22", "%u-%s-31"],
                        ["%u-%s-01", "%u-%s-31"],
                    ];
                    $productSales = Database::read($connection, monthlyReportsBuilder($months, $tableDetails, $weekRange, $year));
                    $data[] = $productSales;
                    //==============MONTHLY=PRODUCT=QUANTITY==============//

                    // Weekly
                    $weekDates = Tools::getWeekDates($year, $_POST["data"]["selectedWeek"]);
                    $days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                    $daysString = "`" . implode("`, `", $days) . "`";

                    $sql = "SELECT '{$_POST["data"]["rowId"]}' AS `Product ID`, `product_name` AS `Product Name`, $daysString, `Total` FROM (SELECT `product_name` FROM `$app_product` WHERE `product_id` = '{$_POST["data"]["rowId"]}') AS `product_name`, ";
                    for ($i = 0; $i < count($days); $i++) {
                        $sql .= "(SELECT COALESCE(SUM(`quantity`), '-') AS `$days[$i]` FROM `$app_order_product` INNER JOIN `$app_order` ON `$app_order_product`.`order_id` = `$app_order`.`order_id` WHERE `product_id` = '{$_POST["data"]["rowId"]}' AND `$app_order`.`order_date` = '$weekDates[$i]') AS `$days[$i]`, ";
                    }
                    $sql .= "(SELECT COALESCE(SUM(`quantity`), '-') AS `total` FROM `$app_order_product` INNER JOIN `$app_order` ON `$app_order_product`.`order_id` = `$app_order`.`order_id` WHERE `product_id` = '{$_POST["data"]["rowId"]}' AND `$app_order`.`order_date` BETWEEN '{$weekDates[0]}' AND '{$weekDates[6]}') AS `total`;";
                    $result = Database::Read($connection, $sql);
                    $data[] = $result;
                    //======================================PRODUCT======================================//
                } else {
                    //======================================CUSTOMER======================================//
                    $sql = "";
                    $weeks = ["Week 1 (1 - 7)", "Week 2 (8 - 14)", "Week 3 (15 - 21)", "Week 4 (22 - 31)", "Gross Sales", "Gross Profit"];
                    $weekString = "`" . implode("`, `", $weeks) . "`";
                    $year = $_POST["data"]["selectedYear"];
                    $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                    for ($i = 0; $i < count($months); $i++) {
                        $month = ($i + 1) < 10 ? "0" . ($i + 1) : ($i + 1);
                        $sql .= "SELECT '{$months[$i]}, $year' AS `Month`, `Customer Name`, $weekString FROM";
                        $sql .= "(SELECT CONCAT(`first_name`, ' ', `last_name`) AS `Customer Name` FROM `$app_customer` WHERE `$app_customer`.`customer_id` = '{$_POST["data"]["rowId"]}') AS `Customer Name`,";
                        for ($j = 0; $j < count($weeks); $j++) {
                            $x = $j === 0 ? $j + 1 : $j;
                            $weekRange = [
                                ["$year-$month-01", "$year-$month-07"],
                                ["$year-$month-08", "$year-$month-14"],
                                ["$year-$month-15", "$year-$month-21"],
                                ["$year-$month-22", "$year-$month-31"],
                                ["$year-$month-01", "$year-$month-31"],
                                ["$year-$month-01", "$year-$month-31"],
                            ];
                            $week = [$weekRange[$j][0], $weekRange[$j][1]];
                            $columnAlias = $weeks[$j];
                            if ($j === count($weeks) - 1) {
                                // Last column index
                                $sql .= "(SELECT CONCAT(\"₱ \", FORMAT(SUM((`$app_order_product`.`product_price`*`$app_order_product`.`quantity`) - (`$app_order_product`.`product_cost`*`$app_order_product`.`quantity`)) - `$app_order_product`.`discount`, 2)) AS `Gross Profit` FROM `$app_order` INNER JOIN `$app_order_product` ON `$app_order`.`order_id`=`$app_order_product`.`order_id` WHERE `order_date` BETWEEN '{$weekRange[$j][0]}' AND '{$weekRange[$j][1]}' AND `$app_order`.`customer_id` = '{$_POST["data"]["rowId"]}') AS `$weeks[$j]`, ";
                            } else {
                                $sql .= "(SELECT CONCAT(\"₱ \", FORMAT(SUM(`$app_order_product`.`product_price`*`$app_order_product`.`quantity`) - `$app_order_product`.`discount`, 2)) AS `$weeks[$j]` FROM `$app_order` INNER JOIN `$app_order_product` ON `$app_order`.`order_id`=`$app_order_product`.`order_id` WHERE `order_date` BETWEEN '{$weekRange[$j][0]}' AND '{$weekRange[$j][1]}' AND `$app_order`.`customer_id` = '{$_POST["data"]["rowId"]}') AS `$weeks[$j]`, ";
                            }
                        }
                        $sql = substr($sql, 0, -2) . " UNION ";
                    }
                    $sql = substr($sql, 0, -8) . "`;";
                    $result = Database::Read($connection, $sql);
                    $data[] = $result;

                    // Weekly
                    $weekDates = Tools::getWeekDates($year, $_POST["data"]["selectedWeek"]);
                    $days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                    $daysString = "`" . implode("`, `", $days) . "`";

                    $sql = "SELECT '{$_POST["data"]["rowId"]}' AS `Customer ID`, `customer_name` AS `Customer Name`, $daysString, `Gross Sales`, `Gross Profit` FROM";
                    $sql .= "(SELECT CONCAT(`first_name`, ' ', `last_name`) AS `customer_name` FROM `$app_customer` WHERE `$app_customer`.`customer_id` = '{$_POST["data"]["rowId"]}') AS `customer_name`,";
                    for ($i = 0; $i < count($days); $i++) {
                        $sql .= "(SELECT CONCAT(\"₱ \", FORMAT(SUM(`$app_order_product`.`product_price`*`$app_order_product`.`quantity`) - `$app_order_product`.`discount`, 2)) AS `{$days[$i]}` FROM `$app_order` INNER JOIN `$app_order_product` ON `$app_order`.`order_id`=`$app_order_product`.`order_id` WHERE `order_date` = '{$weekDates[$i]}' AND `$app_order`.`customer_id` = '{$_POST["data"]["rowId"]}') AS `{$days[$i]}`, ";
                    }
                    $sql .= "(SELECT CONCAT(\"₱ \", FORMAT(SUM(`$app_order_product`.`product_price`*`$app_order_product`.`quantity`) - `$app_order_product`.`discount`, 2)) AS `Gross Sales` FROM `$app_order_product` INNER JOIN `$app_order` ON `$app_order_product`.`order_id` = `$app_order`.`order_id` WHERE `$app_order`.`order_date` BETWEEN '{$weekDates[0]}' AND '{$weekDates[6]}' AND `$app_order`.`customer_id` = '{$_POST["data"]["rowId"]}') AS `Gross Sales`, ";
                    $sql .= "(SELECT CONCAT(\"₱ \", FORMAT(SUM((`$app_order_product`.`product_price`*`$app_order_product`.`quantity`) - (`$app_order_product`.`product_cost`*`$app_order_product`.`quantity`)) - `$app_order_product`.`discount`, 2)) AS `Gross Profit` FROM `$app_order` INNER JOIN `$app_order_product` ON `$app_order`.`order_id`=`$app_order_product`.`order_id` WHERE `order_date` BETWEEN '{$weekDates[0]}' AND '{$weekDates[6]}' AND `$app_order`.`customer_id` = '{$_POST["data"]["rowId"]}') AS `Gross Profit`";
                    $result = Database::Read($connection, $sql);
                    $data[] = $result;
                    //======================================CUSTOMER======================================//
                }

                echo json_encode($data);
            }

            if ($_POST['read'] == "getBoughtProducts") {
                $customer = $_POST["data"]["rowId"];
                $year = $_POST["data"]["selectedYear"];
                $weekDays = Tools::getWeekDates($year, $_POST["data"]["selectedWeek"]);
                $data = [];

                //==============MONTHLY=BOUGHT=PRODUCTS==============//
                $products = Database::read($connection, "SELECT `$app_order_product`.`product_id` FROM `$app_order` INNER JOIN `$app_order_product` ON `$app_order`.`order_id` = `$app_order_product`.`order_id` WHERE YEAR(`order_date`) = $year AND `$app_order`.`customer_id` = '$customer' GROUP BY `product_id`");
                $unions = [];
                foreach ($products as $product) {
                    $monthlySql = "SELECT COALESCE(SUM(`quantity`), '-') AS `%s` FROM `$app_order_product` INNER JOIN `$app_order` ON `$app_order_product`.`order_id` = `$app_order`.`order_id` WHERE `product_id` = '{$product["product_id"]}' AND `$app_order`.`order_date` BETWEEN '$year-%u-01' AND '$year-%u-31' AND `$app_order`.`customer_id` = '$customer'";
                    $annual = "SELECT COALESCE(SUM(`quantity`), '-') AS `%s` FROM `$app_order_product` INNER JOIN `$app_order` ON `$app_order_product`.`order_id` = `$app_order`.`order_id` WHERE `product_id` = '{$product["product_id"]}' AND YEAR(`order_date`) = $year AND `$app_order`.`customer_id` = '$customer'";
                    $tableDetails = [
                        "Product ID" => "SELECT '{$product["product_id"]}' AS `%s` FROM `$app_product`",
                        "Product Name" => "SELECT `product_name` AS `%s` FROM `$app_product` WHERE `product_id` = '{$product["product_id"]}'",
                        "January" => $monthlySql,
                        "February" => $monthlySql,
                        "March" => $monthlySql,
                        "April" => $monthlySql,
                        "May" => $monthlySql,
                        "June" => $monthlySql,
                        "July" => $monthlySql,
                        "August" => $monthlySql,
                        "September" => $monthlySql,
                        "October" => $monthlySql,
                        "November" => $monthlySql,
                        "December" => $monthlySql,
                        "Total" => $annual,
                    ];

                    $unions[] = monthlyBuilder($tableDetails);
                }
                $sql = implode(" UNION ", $unions);
                $boughtProducts = Database::read($connection, $sql);
                if (!$boughtProducts) {
                    $boughtProducts = [[
                        "Product ID" => "",
                        "Product Name" => "",
                        "January" => "",
                        "February" => "",
                        "March" => "",
                        "April" => "",
                        "May" => "",
                        "June" => "",
                        "July" => "",
                        "August" => "",
                        "September" => "",
                        "October" => "",
                        "November" => "",
                        "December" => "",
                        "Total" => "",
                    ]];
                }
                $data[] = $boughtProducts;
                //==============MONTHLY=BOUGHT=PRODUCTS==============//

                //==============WEEKLY=BOUGHT=PRODUCTS==============//
                $products = Database::read($connection, "SELECT `$app_order_product`.`product_id` FROM `$app_order` INNER JOIN `$app_order_product` ON `$app_order`.`order_id` = `$app_order_product`.`order_id` WHERE `$app_order`.`order_date` BETWEEN '$weekDays[0]' AND '$weekDays[6]' AND `$app_order`.`customer_id` = '$customer' GROUP BY `product_id`");
                $unions = [];
                foreach ($products as $product) {
                    $weekDaysSql = [];
                    for ($i = 0; $i < count($weekDays); $i++) {
                        $weekDaysSql[] = "SELECT COALESCE(SUM(`quantity`), '-') AS `%s` FROM `$app_order_product` INNER JOIN `$app_order` ON `$app_order_product`.`order_id` = `$app_order`.`order_id` WHERE `product_id` = '{$product["product_id"]}' AND `$app_order`.`order_date` = '$weekDays[$i]' AND `$app_order`.`customer_id` = '$customer'";
                    }
                    $tableDetails = [
                        "Product ID" => "SELECT '{$product["product_id"]}' AS `%s` FROM `$app_product`",
                        "Product Name" => "SELECT `product_name` AS `%s` FROM `$app_product` WHERE `product_id` = '{$product["product_id"]}'",
                        "Sunday" => $weekDaysSql[0],
                        "Monday" => $weekDaysSql[1],
                        "Tuesday" => $weekDaysSql[2],
                        "Wednesday" => $weekDaysSql[3],
                        "Thursday" => $weekDaysSql[4],
                        "Friday" => $weekDaysSql[5],
                        "Saturday" => $weekDaysSql[6],
                        "Total" => "SELECT COALESCE(SUM(`quantity`), '-') AS `%s` FROM `$app_order_product` INNER JOIN `$app_order` ON `$app_order_product`.`order_id` = `$app_order`.`order_id` WHERE `product_id` = '{$product["product_id"]}' AND `$app_order`.`order_date` BETWEEN '$weekDays[0]' AND '$weekDays[6]' AND `$app_order`.`customer_id` = '$customer'",
                    ];
                    $unions[] = monthlyBuilder($tableDetails);
                }
                $sql = implode(" UNION ", $unions);
                $boughtProducts = Database::read($connection, $sql);
                if (!$boughtProducts) {
                    $boughtProducts = [[
                        "Product ID" => "",
                        "Product Name" => "",
                        "Sunday" => "",
                        "Monday" => "",
                        "Tuesday" => "",
                        "Wednesday" => "",
                        "Thursday" => "",
                        "Friday" => "",
                        "Saturday" => "",
                        "Total" => "",
                    ]];
                }
                $data[] = $boughtProducts;
                //==============WEEKLY=BOUGHT=PRODUCTS==============//

                echo json_encode($data);
            }

            if ($_POST['read'] == "getFinancialReports") {
                $year = $_POST["data"]["selectedYear"];
                $selectedWeek = isset($_POST["data"]["selectedWeek"]) ? $_POST["data"]["selectedWeek"] : Tools::getCurrentWeekNumber();
                $weekDates = Tools::getWeekDates($year, $selectedWeek);
                $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

                //==============MONTHLY=GROSS=SALES==============//
                $grossSalesSql = "SELECT CONCAT(\"₱ \", FORMAT(SUM(`$app_order_product`.`product_price`*`$app_order_product`.`quantity`) - SUM(`app_order_product`.`discount`), 2)) AS `%s` FROM `$app_order` INNER JOIN `$app_order_product` ON `$app_order`.`order_id`=`$app_order_product`.`order_id` WHERE `order_date` BETWEEN '%s' AND '%s'";
                $tableDetails = [
                    "Month" => $months,
                    "Week 1 (1 - 7)" => $grossSalesSql,
                    "Week 2 (8 - 14)" => $grossSalesSql,
                    "Week 3 (15 - 21)" => $grossSalesSql,
                    "Week 4 (22 - 31)" => $grossSalesSql,
                    "Gross Sales" => $grossSalesSql,
                ];
                $weekRange = [
                    ["%u-%s-01", "%u-%s-07"],
                    ["%u-%s-08", "%u-%s-14"],
                    ["%u-%s-15", "%u-%s-21"],
                    ["%u-%s-22", "%u-%s-%u"],
                    ["%u-%s-01", "%u-%s-%u"],
                ];
                $monthlyGrossSales = Database::read($connection, monthlyReportsBuilder($months, $tableDetails, $weekRange, $year));
                //==============MONTHLY=GROSS=SALES==============//

                //==============MONTHLY=GROSS=PROFIT==============//
                $grossProfitSql = "SELECT CONCAT(\"₱ \", FORMAT(SUM((`$app_order_product`.`product_price`*`$app_order_product`.`quantity`) - (`$app_order_product`.`product_cost`*`$app_order_product`.`quantity`)) - SUM(`$app_order_product`.`discount`), 2)) AS `%s` FROM `$app_order` INNER JOIN `$app_order_product` ON `$app_order`.`order_id`=`$app_order_product`.`order_id` WHERE `order_date` BETWEEN '%s' AND '%s'";
                $tableDetails = [
                    "Month" => $months,
                    "Week 1 (1 - 7)" => $grossProfitSql,
                    "Week 2 (8 - 14)" => $grossProfitSql,
                    "Week 3 (15 - 21)" => $grossProfitSql,
                    "Week 4 (22 - 31)" => $grossProfitSql,
                    "Gross Profit" => $grossProfitSql,
                ];
                $weekRange = [
                    ["%u-%s-01", "%u-%s-07"],
                    ["%u-%s-08", "%u-%s-14"],
                    ["%u-%s-15", "%u-%s-21"],
                    ["%u-%s-22", "%u-%s-%u"],
                    ["%u-%s-01", "%u-%s-%u"],
                ];
                $monthlyGrossProfit = Database::read($connection, monthlyReportsBuilder($months, $tableDetails, $weekRange, $year));
                //==============MONTHLY=GROSS=PROFIT==============//

                //==============WEEKLY=GROSS=SALES==============//
                $daySalesSql = "SELECT CONCAT(\"₱ \", FORMAT(SUM(`$app_order_product`.`product_price`*`$app_order_product`.`quantity`) - SUM(`$app_order_product`.`discount`), 2)) AS `%s` FROM `$app_order` INNER JOIN `$app_order_product` ON `$app_order`.`order_id`=`$app_order_product`.`order_id` WHERE `order_date` >= '%s' AND `order_date` < (DATE('%s') + 1)";
                $weeklyGrossSql = "SELECT CONCAT(\"₱ \", FORMAT(SUM(`$app_order_product`.`product_price`*`$app_order_product`.`quantity`) - SUM(`$app_order_product`.`discount`), 2)) AS `%s` FROM `$app_order` INNER JOIN `$app_order_product` ON `$app_order`.`order_id`=`$app_order_product`.`order_id` WHERE `order_date` BETWEEN '{$weekDates[0]}' AND '{$weekDates[6]}'";
                $tableDetails = [
                    date("l", strtotime($weekDates[0])) => $daySalesSql,
                    date("l", strtotime($weekDates[1])) => $daySalesSql,
                    date("l", strtotime($weekDates[2])) => $daySalesSql,
                    date("l", strtotime($weekDates[3])) => $daySalesSql,
                    date("l", strtotime($weekDates[4])) => $daySalesSql,
                    date("l", strtotime($weekDates[5])) => $daySalesSql,
                    date("l", strtotime($weekDates[6])) => $daySalesSql,
                    "Gross Sales" => $weeklyGrossSql,
                ];
                $weeklyGrossSales = Database::Read($connection, weeklyReportsBuilder($weekDates, $tableDetails));
                //==============WEEKLY=GROSS=SALES==============//

                //==============WEEKLY=GROSS=PROFIT==============//
                $dayProfitSql = "SELECT CONCAT(\"₱ \", FORMAT(SUM((`$app_order_product`.`product_price`*`$app_order_product`.`quantity`) - (`$app_order_product`.`product_cost`*`$app_order_product`.`quantity`)) - SUM(`$app_order_product`.`discount`), 2)) AS `%s` FROM `$app_order` INNER JOIN `$app_order_product` ON `$app_order`.`order_id`=`$app_order_product`.`order_id` WHERE `order_date` >= '%s' AND `order_date` < (DATE('%s') + 1)";
                $weeklyGrossSql = "SELECT CONCAT(\"₱ \", FORMAT(SUM((`$app_order_product`.`product_price`*`$app_order_product`.`quantity`) - (`$app_order_product`.`product_cost`*`$app_order_product`.`quantity`)) - SUM(`$app_order_product`.`discount`), 2)) AS `%s` FROM `$app_order` INNER JOIN `$app_order_product` ON `$app_order`.`order_id`=`$app_order_product`.`order_id` WHERE `order_date` BETWEEN '{$weekDates[0]}' AND '{$weekDates[6]}'";
                $tableDetails = [
                    date("l", strtotime($weekDates[0])) => $dayProfitSql,
                    date("l", strtotime($weekDates[1])) => $dayProfitSql,
                    date("l", strtotime($weekDates[2])) => $dayProfitSql,
                    date("l", strtotime($weekDates[3])) => $dayProfitSql,
                    date("l", strtotime($weekDates[4])) => $dayProfitSql,
                    date("l", strtotime($weekDates[5])) => $dayProfitSql,
                    date("l", strtotime($weekDates[6])) => $dayProfitSql,
                    "Gross Profit" => $weeklyGrossSql,
                ];
                $weeklyGrossProfit = Database::Read($connection, weeklyReportsBuilder($weekDates, $tableDetails));
                //==============WEEKLY=GROSS=PROFIT==============//

                // Get max week from selected year
                $maxWeek = $year === date("Y") ? Tools::getCurrentWeekNumber() : date("W", strtotime("$year-12-31") - 1);

                $data = [
                    "monthlyGrossSales" => $monthlyGrossSales,
                    "monthlyGrossProfit" => $monthlyGrossProfit,
                    "weeklyGrossSales" => $weeklyGrossSales,
                    "weeklyGrossProfit" => $weeklyGrossProfit,
                    "maxWeek" => (int)$maxWeek,
                    "currentWeek" => (int)Tools::getCurrentWeekNumber(),
                    "weekDates" => $weekDates
                ];

                die(json_encode($data));
            }
        }
        Database::disconnect($connection);
    } else {
        echo '<h1>Connection failed!</h1>';
    }
} else {
    header("Location: ..");
}

function monthlyReportsBuilder($months, $tableDetails, $weekRange, $year)
{
    $monthsData = [];
    $columnNames = array_keys($tableDetails);
    for ($i = 0; $i < count($months); $i++) {
        $month = ($i + 1) < 10 ? "0" . ($i + 1) : ($i + 1);
        $sql = "SELECT '$months[$i]' AS `" . implode("`, `", $columnNames) . "` FROM ";

        for ($weekIndex = 0; $weekIndex < count($tableDetails); $weekIndex++) {
            // Skip the "Month" column
            if ($weekIndex >= 1) {
                if ($weekIndex <= count($weekRange)) {
                    // Handle changing days in a month (Week 4 and gross amount)
                    $is4thWeekAndGross = in_array($weekIndex, [4, 5]);
                    if ($is4thWeekAndGross) {
                        $maxDaysInAMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                        $sql .= "(" . sprintf($tableDetails[$columnNames[$weekIndex]], $columnNames[$weekIndex], sprintf($weekRange[$weekIndex - 1][0], $year, $month), sprintf($weekRange[$weekIndex - 1][1], $year, $month, $maxDaysInAMonth)) . ") AS `{$columnNames[$weekIndex]}`, ";
                    } else {
                        $sql .= "(" . sprintf($tableDetails[$columnNames[$weekIndex]], $columnNames[$weekIndex], sprintf($weekRange[$weekIndex - 1][0], $year, $month), sprintf($weekRange[$weekIndex - 1][1], $year, $month)) . ") AS `{$columnNames[$weekIndex]}`, ";
                    }
                } else {
                    $sql .= "(" . sprintf($tableDetails[$columnNames[$weekIndex]], $columnNames[$weekIndex]) . ") AS `{$columnNames[$weekIndex]}`, ";
                }
            }
        }

        $monthsData[] = substr($sql, 0, -2);
    }
    return implode(" UNION ", $monthsData) . ";";
}
function weeklyReportsBuilder($weekDates, $tableDetails)
{
    $columnNames = array_keys($tableDetails);
    $sql = "SELECT `" . implode("`, `", $columnNames) . "` FROM ";
    for ($i = 0; $i < count($columnNames); $i++) {
        if ($i < count($weekDates)) {
            $sql .= "(" . sprintf($tableDetails[$columnNames[$i]], $columnNames[$i], $weekDates[$i], $weekDates[$i]) . ") AS `{$columnNames[$i]}`, ";
        } else {
            $sql .= "(" . sprintf($tableDetails[$columnNames[$i]], $columnNames[$i]) . ") AS `{$columnNames[$i]}`, ";
        }
    }
    return substr($sql, 0, -2) . ";";
}

function monthlyBuilder($tableDetails)
{
    $columnNames = array_keys($tableDetails);
    $sql = "SELECT DISTINCT `" . implode("`, `", $columnNames) . "` FROM ";
    for ($i = 0; $i < count($columnNames); $i++) {
        if ($i === 0 || $i === 1 || $i === count($columnNames) - 1) {
            $sql .= "(" . sprintf($tableDetails[$columnNames[$i]], $columnNames[$i]) . ") AS `{$columnNames[$i]}`, ";
        } else {
            $month = ($i - 1) < 10 ? "0" . ($i - 1) : ($i - 1);
            $sql .= "(" . sprintf($tableDetails[$columnNames[$i]], $columnNames[$i], $month, $month) . ") AS `{$columnNames[$i]}`, ";
        }
    }
    return substr($sql, 0, -2);
}
