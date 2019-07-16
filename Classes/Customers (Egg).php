<?php

/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

require_once 'global.php';
require_once 'DatabasePublic.php';
require_once 'Tools.php';

// DB table to use
$table = $app_customer;

// Table's primary key
$primaryKey = 'customer_id';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes

$Connection = connect($Host, $DatabaseName, $Username, $Password);

$where = "`status` = 'active' ORDER BY `customer_id` DESC";
$columns = array(
    array(
        'db' => 'customer_id', 'dt' => 0,
        'formatter' => function ($d, $row) {
            $columnIndex = 0;
            return "<span data-table-type='customer' data-row-id='$d'>$d</span>";
        }
    ),
    array(
        'db' => 'first_name', 'dt' => 1,
        'formatter' => function ($d, $row) {
            $columnIndex = 1;
            return "<span data-table-type='customer' data-column-index='$columnIndex' value='$d'>$d</span>";
        }
    ),
    array(
        'db' => 'last_name', 'dt' => 2,
        'formatter' => function ($d, $row) {
            $columnIndex = 2;
            return "<span data-table-type='customer' data-column-index='$columnIndex' value='$d'>$d</span>";
        }
    ),
    array(
        'db' => 'client_address', 'dt' => 3,
        'formatter' => function ($d, $row) {
            $columnIndex = 3;
            return "<span data-table-type='customer' data-column-index='$columnIndex' value='$d'>$d</span>";
        }
    ),
    array(
        'db' => 'date_added', 'dt' => 4,
        'formatter' => function ($d, $row) {
            $columnIndex = 4;
            return "<span data-table-type='customer' data-column-index='$columnIndex' class='date' value='$d'> " . date('F d, Y', strtotime($d)) . " </span>";
        }
    ),
    array(
        'db' => 'jumbo_price', 'dt' => 5,
        'formatter' => function ($d, $row) {
            $columnIndex = 6;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'jumbo_c_price', 'dt' => 6,
        'formatter' => function ($d, $row) {
            $columnIndex = 7;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'xl_price', 'dt' => 7,
        'formatter' => function ($d, $row) {
            $columnIndex = 8;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'xl_c_price', 'dt' => 8,
        'formatter' => function ($d, $row) {
            $columnIndex = 9;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'l_price', 'dt' => 9,
        'formatter' => function ($d, $row) {
            $columnIndex = 10;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'l_c_price', 'dt' => 10,
        'formatter' => function ($d, $row) {
            $columnIndex = 11;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'm_price', 'dt' => 11,
        'formatter' => function ($d, $row) {
            $columnIndex = 12;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'm_c_price', 'dt' => 12,
        'formatter' => function ($d, $row) {
            $columnIndex = 13;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 's_price', 'dt' => 13,
        'formatter' => function ($d, $row) {
            $columnIndex = 14;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 's_c_price', 'dt' => 14,
        'formatter' => function ($d, $row) {
            $columnIndex = 15;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'p_price', 'dt' => 15,
        'formatter' => function ($d, $row) {
            $columnIndex = 16;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'p_c_price', 'dt' => 16,
        'formatter' => function ($d, $row) {
            $columnIndex = 17;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'pwe_price', 'dt' => 17,
        'formatter' => function ($d, $row) {
            $columnIndex = 18;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'pwe_c_price', 'dt' => 18,
        'formatter' => function ($d, $row) {
            $columnIndex = 19;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'd2_price', 'dt' => 19,
        'formatter' => function ($d, $row) {
            $columnIndex = 20;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'd2_c_price', 'dt' => 20,
        'formatter' => function ($d, $row) {
            $columnIndex = 21;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'marble_price', 'dt' => 21,
        'formatter' => function ($d, $row) {
            $columnIndex = 22;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'marble_c_price', 'dt' => 22,
        'formatter' => function ($d, $row) {
            $columnIndex = 23;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'd1b_price', 'dt' => 23,
        'formatter' => function ($d, $row) {
            $columnIndex = 24;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'd1b_c_price', 'dt' => 24,
        'formatter' => function ($d, $row) {
            $columnIndex = 25;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'd1s_price', 'dt' => 25,
        'formatter' => function ($d, $row) {
            $columnIndex = 26;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'd1s_c_price', 'dt' => 26,
        'formatter' => function ($d, $row) {
            $columnIndex = 27;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'b1_price', 'dt' => 27,
        'formatter' => function ($d, $row) {
            $columnIndex = 28;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'b1_c_price', 'dt' => 28,
        'formatter' => function ($d, $row) {
            $columnIndex = 29;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'b2_price', 'dt' => 29,
        'formatter' => function ($d, $row) {
            $columnIndex = 30;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'b2_c_price', 'dt' => 30,
        'formatter' => function ($d, $row) {
            $columnIndex = 31;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'b3_price', 'dt' => 31,
        'formatter' => function ($d, $row) {
            $columnIndex = 32;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'b3_c_price', 'dt' => 32,
        'formatter' => function ($d, $row) {
            $columnIndex = 33;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array(
        'db' => 'es_price', 'dt' => 33,
        'formatter' => function ($d, $row) {
            $columnIndex = 34;
            return $d ? "<span data-table-type='customer' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='customer' data-column-index='$columnIndex'>-</span>";
        }
    )
);

// SQL server connection information
$sql_details = array(
    'user' => $Username,
    'pass' => $Password,
    'db' => $DatabaseName,
    'host' => $Host
);


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

require('ssp.class.php');

echo json_encode(
    SSP::complex($_POST, $sql_details, $table, $primaryKey, $columns, $where)
);
