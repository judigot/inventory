<?php
$static = false;
if (isset($_POST['print']) || $static) {

    require_once 'Imports/preload.php';
    require_once 'Classes/Database.php';
    require_once 'Classes/Tools.php';

    $connection = Database::connect($Host, $DatabaseName, $Username, $Password);
    if ($static) {
        $orderIds = [1, 2, 3, 4, 5];
    } else {

        $orderIds = json_decode($_POST["data"]["orderIds"]);
    }

?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <title>Print - <?php echo $appName; ?></title>
        <link rel="stylesheet" href="Assets/css/print.css">
        <style>
            @page {
                padding: 0%;
                margin: 0%;
                size: portrait;
            }

            @font-face {
                font-family: "FakeReceipt-Regular";
                src: url("Assets/fonts/fakereceipt.ttf");
            }

            html,
            body {
                padding: 0%;
                margin: 0%;
                width: 100%;
                text-align: center;
                font-size: 13px;
                font-family: Lucida Console;
                /* font-family: "FakeReceipt-Regular"; */
                -webkit-print-color-adjust: exact;
            }

            .description {
                font-weight: bold;
            }

            small {
                left: calc(80% - 10px);
                position: fixed;
                font-size: 10px;
                font-style: italic;
            }

            .grid-container {
                /* padding: 0px 10px 0px 10px; */
                padding: 2%;
                display: grid;
                grid-gap: 10px;
                grid-template-columns: repeat(2, 1fr);
            }

            .lefty {
                float: left;
            }

            .righty {
                float: right;
            }

            table {
                width: 100%;
                margin: auto;
                border-collapse: collapse;
            }

            th {
                border-top: 1px dashed black;
                border-bottom: 1px dashed black;
            }

            tr {
                border-bottom: 1px dashed black;
            }

            td {
                padding: 10px 0px 10px 0px;
            }

            .ordered-products>thead>tr>th,
            .ordered-products>tbody>tr>td {
                border: 1px dashed black;
            }
        </style>
    </head>

    <body>
        <div class="grid-container">

            <?php
            for ($i = 0; $i < 2; $i++) {
            ?>
                <div class="grid-column">
                    <div class="header">
                        <div>
                            <span>PD FARM</span>
                            <br>
                            <span>ORDER SLIP</span>
                        </div>
                        <br>
                        <div class="lefty"><?php echo $i === 0 ? "Original" : "Duplicate" ?></div>
                        <br>
                        <br>
                        <div>
                            <span class="righty">Printed on: <?php echo date("F j, Y"); ?></span>
                        </div>
                    </div>
                    <br><br>
                    <div class="content">
                        <table>
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Order</th>
                                    <th>Order Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                $result = Database::read($connection, "SELECT `$app_order`.`order_id`, `$app_order`.`customer_id`, CONCAT(`$app_customer`.`first_name`, ' ', `$app_customer`.`last_name`) AS `customer_name`, `$app_order`.`order_date` FROM `$app_order` INNER JOIN `$app_customer` ON `$app_order`.`customer_id`=`$app_customer`.`customer_id` WHERE `order_id` IN ('" . implode("', '", $orderIds) . "') ORDER BY `order_id` ASC;");

                                foreach ($result as $value) {

                                ?>
                                    <tr>
                                        <td><?php echo $value["customer_name"]; ?></td>
                                        <td>

                                            <table class="ordered-products">
                                                <thead>
                                                    <tr>
                                                        <th>Quantity</th>
                                                        <th>Product</th>
                                                        <th>Price</th>
                                                        <th>Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php

                                                    $products = Database::read($connection, "SELECT `$app_order_product`.*, `$app_product`.`product_name` FROM `$app_order_product` INNER JOIN `$app_product` ON `$app_order_product`.`product_id`=`$app_product`.`product_id` WHERE `order_id` = '{$value["order_id"]}'");

                                                    $totalQuantity = 0;
                                                    $totalAmount = 0;

                                                    foreach ($products as $item) {
                                                        $totalQuantity += $item["quantity"];
                                                        $totalAmount += $item["product_price"] * $item["quantity"];

                                                    ?>
                                                        <tr>
                                                            <td><?php echo $item["quantity"] ?></td>
                                                            <td><?php echo $item["product_name"] ?></td>
                                                            <td><?php echo "₱" . Tools::monetize(true, $item["product_price"]) ?></td>
                                                            <td><?php echo "₱" . Tools::monetize(true, $item["product_price"] * $item["quantity"]) ?></td>
                                                        </tr>

                                                    <?php } ?>
                                                    <tr>
                                                        <td><?php echo $totalQuantity; ?></td>
                                                        <td>-</td>
                                                        <td>-</td>
                                                        <td><?php echo "₱" . Tools::monetize(true, $totalAmount); ?></td>
                                                    </tr>

                                                </tbody>
                                            </table>

                                        </td>
                                        <td><?php echo date('F j, Y', strtotime($value["order_date"])); ?></td>
                                    </tr>

                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <br>
                    <div id="acknowledgement"><span>Checked & Received By: __________</span></div>
                </div>
            <?php } ?>
        </div>
    </body>

    </html>
<?php
} else {
    header("Location: index");
}
?>