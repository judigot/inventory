<?php

$months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
$customer = "Jude Francis Igot";
$numbers = [643, 2653, 754, 436, 253, 846, 679, 547, 235, 32541, 547, 568];
$iterators = [$months, $numbers];
$year = 2018;
$weeklyQuery = "SELECT CONCAT(\"₱ \", FORMAT(SUM(`app_order_product`.`product_price`*`app_order_product`.`quantity`) - SUM(`app_order_product`.`discount`), 2)) FROM `app_order` INNER JOIN `app_order_product` ON `app_order`.`order_id`=`app_order_product`.`order_id` WHERE `order_date` >= '%s' AND `order_date` < (DATE('%s') + INTERVAL 1 DAY)";
$monthlyQuery = "SELECT CONCAT(\"₱ \", FORMAT(SUM(`app_order_product`.`product_price`*`app_order_product`.`quantity`) - SUM(`app_order_product`.`discount`), 2)) FROM `app_order` INNER JOIN `app_order_product` ON `app_order`.`order_id`=`app_order_product`.`order_id` WHERE `order_date` >= '%s' AND `order_date` < (DATE('%s') + INTERVAL 1 DAY)";
$table = [
    "Month" => $months,
    "Customer Name" => $customer,
    "Custom Identifier" => $numbers,
    "Week 1 (1 - 7)" => function ($index) use ($year, $weeklyQuery) {
        $month = ($index + 1) < 10 ? "0" . ($index + 1) : ($index + 1);

        $start = "$year-$month-01";
        $end = "$year-$month-07";

        return sprintf(
            $weeklyQuery,

            // Replacements
            $start,
            $end
        );
    },
    "Week 2 (8 - 14)" => function ($index) use ($year, $weeklyQuery) {
        $month = ($index + 1) < 10 ? "0" . ($index + 1) : ($index + 1);
        $start = "$year-$month-08";
        $end = "$year-$month-14";

        return sprintf(
            $weeklyQuery,

            // Replacements
            $start,
            $end
        );
    },
    "Week 3 (15 - 21)" => function ($index) use ($year, $weeklyQuery) {
        $month = ($index + 1) < 10 ? "0" . ($index + 1) : ($index + 1);
        $start = "$year-$month-15";
        $end = "$year-$month-21";

        return sprintf(
            $weeklyQuery,

            // Replacements
            $start,
            $end
        );
    },
    "Week 4 (22 - 31)" => function ($index) use ($year, $weeklyQuery) {
        $month = ($index + 1) < 10 ? "0" . ($index + 1) : ($index + 1);
        $maxDaysInAMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $start = "$year-$month-22";
        $end = "$year-$month-$maxDaysInAMonth";

        return sprintf(
            $weeklyQuery,

            // Replacements
            $start,
            $end
        );
    },
    "Gross Sales" => function ($index) use ($year, $monthlyQuery) {
        $month = ($index + 1) < 10 ? "0" . ($index + 1) : ($index + 1);
        $maxDaysInAMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $start = "$year-$month-01";
        $end = "$year-$month-$maxDaysInAMonth";

        return sprintf(
            $monthlyQuery,

            // Replacements
            $start,
            $end
        );
    },
];


// joinBuilder()
$sql = "
SELECT (SELECT `app_order_product`.`product_id`) AS `Product ID`, `app_product`.`product_name` AS `Product Name`, `app_customer`.`first_name` AS `Customer`, 

(
	SELECT COALESCE(SUM(`quantity`), '-')
	FROM `app_order`
	JOIN `app_order_product` USING(`order_id`)
	JOIN `app_customer` USING(`customer_id`)
	
	WHERE `app_order_product`.`product_id` = `Product ID`
	AND `app_customer`.`customer_id` = 1
	AND `app_order_product`.`order_id` = `app_order`.`order_id`
	AND MONTH(`order_date`) = '01'
) AS `Feb`

FROM `app_order`
JOIN `app_order_product` USING(`order_id`)
JOIN `app_product` USING(`product_id`)
JOIN `app_customer` USING(`customer_id`)

WHERE `app_order`.`customer_id` = 1 AND
YEAR(`app_order`.`order_date`) = $year
GROUP BY `app_product`.`product_id`;
";
$customerID = 1;
$monthlyQuery = "SELECT COALESCE(SUM(`quantity`), '-') FROM `app_order` JOIN `app_order_product` USING(`order_id`) JOIN `app_customer` USING(`customer_id`) WHERE `app_order_product`.`product_id` = `Product ID` AND `app_customer`.`customer_id` = $customerID AND `app_order_product`.`order_id` = `app_order`.`order_id` AND MONTH(`order_date`) = '%u'";
$table = [
    "Product ID" => function ($index) {
        return "SELECT `app_order_product`.`product_id`";
    },
    "Product Name" => function ($index) {
        return "SELECT `app_product`.`product_name`";
    },
    "FROM `app_order` JOIN `app_order_product` USING(`order_id`) JOIN `app_product` USING(`product_id`) JOIN `app_customer` USING(`customer_id`) WHERE `app_order`.`customer_id` = 1 AND YEAR(`app_order`.`order_date`) = $year GROUP BY `app_product`.`product_id`"
];
//====================ASSIGN MONTHS TO CLOSURE====================//
$lastIndex = array_keys($table)[count(array_keys($table)) - 1];
$lastElement = $table[$lastIndex];
unset($table[$lastIndex]);
for ($i = 0; $i < count($months); $i++) {
    $monthsFormatter = [
        "$months[$i]" => function ($index) use ($monthlyQuery) {
            // Adjust index based on "January" index (how many elements are there before January)
            $index = $index - 2;
            $month = ($index + 1) < 10 ? "0" . ($index + 1) : ($index + 1);
            return sprintf(
                $monthlyQuery,
                // Replacements
                $month,
            );
        },
    ];
    $table[$months[$i]] = $monthsFormatter[$months[$i]];
}
array_push($table, $lastElement);
//====================ASSIGN MONTHS TO CLOSURE====================//

$sql = joinBuilder($table);

echo $sql;

function joinBuilder($table)
{
    $columnNames = array_keys($table);

    // Build query
    $sql = "SELECT ";
    for ($i = 0; $i < count($table); $i++) {

        $columnName = $columnNames[$i];
        $value = $table[$columnName];
        $type = gettype($value);

        if ($type === "object") {
            // Loop through the main months e.g. months
            $query = $value($i);
            $sql .= "($query) AS `$columnName`, ";
        }
    }
    return substr($sql, 0, -2) . $table[$columnNames[count($columnNames) - 1]] . ";";
}

function reportBuilder($rowCount, $table)
{
    // Check first if $iterators have the same number of elements

    $columnNames = array_keys($table);

    // Combine iterators e.g. month and customer name into a single array
    $static = [];
    for ($i = 0; $i < count($table); $i++) {
        $columnName = $columnNames[$i];
        $value = $table[$columnName];
        $type = gettype($value);
        // Filter iterators in table
        if ($type === "array") {
            // Loop through the main months e.g. months
            for ($j = 0; $j < count($value); $j++) {
                $static[$j][$columnNames[$i]] = $value[$j];
            }
        }
    }

    // Build query
    $rows = [];
    for ($i = 0; $i < $rowCount; $i++) {

        $sql = "SELECT ";

        for ($j = 0; $j < count($table); $j++) {
            $columnName = $columnNames[$j];
            $value = $table[$columnName];
            $type = gettype($value);

            if ($type === "array") {
                $sql .= "'" . $static[$i][$columnName] . "' AS `$columnName`, ";
            }

            if ($type === "object") {
                // Loop through the main months e.g. months
                $query = $value($i);
                $sql .= "($query) AS `$columnName`, ";
            }

            if ($type === "string") {
                $query = $value;
                $sql .= "('$query') AS `$columnName`, ";
            }
        }

        $rows[] = substr($sql, 0, -2);
    }
    return implode(" UNION ", $rows) . ";";
}
