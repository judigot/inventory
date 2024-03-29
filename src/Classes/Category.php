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

// DB table to use
$table = $app_product_category;

// Table's primary key
$primaryKey = 'category_id';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes

$where = "";
$columns = array(
    array('db' => 'category_id', 'dt' => 0,
        'formatter' => function( $d, $row ) {
            return "<span data-table-type='category' data-row-id='$d'>$d</span>";
        }
    ),
    array('db' => 'category_name', 'dt' => 1,
        'formatter' => function( $d, $row ) {
            $columnIndex = 1;
            return "<span data-table-type='category' data-column-index='$columnIndex' value='" . htmlspecialchars($d, ENT_QUOTES, "UTF-8") . "'>$d</span>";
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
