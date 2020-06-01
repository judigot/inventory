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
$table = $app_product;

// Table's primary key
$primaryKey = 'product_id';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes

$Connection = connect($Host, $DatabaseName, $Username, $Password);

$where = "`status` = 'active' ORDER BY `product_category` ASC, `product_stock` ASC";
$columns = array(
    array('db' => 'product_id', 'dt' => 0,
        'formatter' => function( $d, $row ) {
            return "<span data-table-type='product' data-row-id='$d'>$d</span>";
        }
    ),
    array('db' => 'product_name', 'dt' => 1,
        'formatter' => function( $d, $row ) {
            $columnIndex = 1;
            return "<span data-table-type='product' data-column-index='$columnIndex' value='$d' class='product-name" . ($row["product_stock"] === "0" ? " no-stock" : "") ."'>$d</span>";
        }
    ),
    array('db' => 'product_category', 'dt' => 2,
        'formatter' => function( $d, $row ) use ($Connection, $app_product_category) {
            $columnIndex = 2;

            $result = read($Connection, "SELECT `category_name` FROM $app_product_category WHERE `category_id` = $d;");

            $categoryName = $result[0]["category_name"];

            return "<span data-table-type='product' data-column-index='$columnIndex' value='$d' class='category-name'>$categoryName</span>";
        }
    ),
    array('db' => 'product_cost', 'dt' => 3,
        'formatter' => function( $d, $row ) {
            $columnIndex = 3;
            return $d ? "<span data-table-type='product' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='product' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array('db' => 'product_price', 'dt' => 4,
        'formatter' => function( $d, $row ) {
            $columnIndex = 4;
            return $d ? "<span data-table-type='product' data-column-index='$columnIndex' value='" . Tools::monetize(false, $d) . "'>₱ " . Tools::monetize(true, $d) . "</span> " : "<span data-table-type='product' data-column-index='$columnIndex'>-</span>";
        }
    ),
    array('db' => 'product_stock', 'dt' => 5,
        'formatter' => function( $d, $row ) {
            $columnIndex = 5;
            return "<span data-table-type='product' data-column-index='$columnIndex' class='" . ($row["product_stock"] === "0" ? " no-stock" : "") ."' value='$d'>$d</span>";
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

require( 'ssp.class.php' );

echo json_encode(
        SSP::complex($_POST, $sql_details, $table, $primaryKey, $columns, $where)
);
