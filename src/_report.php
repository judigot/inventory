<?php
if (isset($_POST['print'])) {
    require_once 'Imports/preload.php';
    require_once 'Classes/Database.php';
    require_once 'Classes/Tools.php';
    $connection = Database::connect($Host, $DatabaseName, $Username, $Password);
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <title>Print - <?php echo $appName; ?></title>
        <style>
            @page {
                padding: 0%;
                margin: 0%;
                size: portrait;
            }

            html,
            body {
                padding: 0%;
                margin: 0%;
                width: 100%;
                font-family: Lucida Console;
                -webkit-print-color-adjust: exact;
            }

            .header-section {
                padding-bottom: 10px;
                margin-bottom: 20px;
                border-bottom: 1px solid black;
            }

            hr {
                border-top: dashed 1px;
            }

            .order-section {
                margin: auto;
                width: max-content;
            }

            #ordersTable {
                width: 100%;
            }

            #reportsTable {
                font-size: 14px;
                width: 90%;
            }

            table {
                margin: auto;
                border-collapse: collapse;
            }

            th,
            td {
                border: 1px solid black;
            }

            body,
            table {
                text-align: center;
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
        </style>
    </head>

    <body>
        <div class="content-box">
            <div class="header-section">
                <h1><?php echo $appName; ?></h1>


                <!--
                    <div>
                            <div>Bawod, San Isidro, Leyte</div>
                            <div>Proprietor: Reden James A. Remorta</div>
                            <div>TIN 470-919-323-000</div>
                            <div>Non-VAT</div>
                            <div>Contact:
                                <li>0917-109-3338</li>
                                <li>0912-307-9069</li>
                            </div>
                        </div> -->




            </div>
            <small>Printed on <?php echo date("F j, Y"); ?></small>
            <?php
            if ($_POST["print"] === "printOrders") {
                $orderIds = json_decode($_POST["data"]["orderIds"]);
                $result = Database::read($connection, "SELECT `$app_order`.`order_id`, `$app_order`.`customer_id`, CONCAT(`$app_customer`.`first_name`, ' ', `$app_customer`.`last_name`) AS `customer_name`, `$app_order`.`order_date` FROM `$app_order` INNER JOIN `$app_customer` ON `$app_order`.`customer_id`=`$app_customer`.`customer_id` WHERE `order_id` IN ('" . implode("', '", $orderIds) . "') ORDER BY `order_id` ASC;");
                ?>
                <div class="content-box">
                    <div class="content-body">
                        <div class="order-section">
                            <?php
                            foreach ($result as $value) {
                                $products = Database::read($connection, "SELECT `$app_order_product`.*, `$app_product`.`product_name` FROM `$app_order_product` INNER JOIN `$app_product` ON `$app_order_product`.`product_id`=`$app_product`.`product_id` WHERE `order_id` = '{$value["order_id"]}'");
                                ?>
                                <div>
                                    <div>
                                        <span class="description">Order ID: </span>
                                        <span><?php echo $value["order_id"]; ?></span>
                                    </div>
                                    <span class="description">Customer: </span>
                                    <span><?php echo $value["customer_name"]; ?></span>
                                </div>
                                <div class="transaction">
                                    <table id="ordersTable">
                                        <thead>
                                            <tr>
                                                <td>Quantity</td>
                                                <td>Product</td>
                                                <td>Price</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $totalCost = 0;
                                            foreach ($products as $item) {
                                                $totalCost += $item["quantity"] * $item["product_price"];
                                                ?>
                                                <tr>
                                                    <td><?php echo $item["quantity"]; ?></td>
                                                    <td><?php echo $item["product_name"]; ?></td>
                                                    <td>₱ <?php echo Tools::monetize(true, $item["product_price"]) ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                    <span>Total: ₱ <?php echo Tools::monetize(true, $totalCost); ?></span>
                                    <div>
                                        <span class="description">Order Date: </span>
                                        <span><?php echo $value["order_date"]; ?></span>
                                    </div>
                                </div>
                                <hr>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php
            if ($_POST["print"] === "printReports") {
                ?>
                <div class="content-box">
                    <div class="content-body">
                        <div class="report-section">
                            <?php echo $_POST["data"]["reportData"]; ?>
                        </div>
                    </div>
                </div>
            <?php
        }
        ?>
            <br><br>
            <div id="acknowledgement"><span>Acknowledged by: __________</span></div>
        </div>
    </body>

    </html>
<?php
} else {
    header("Location: index");
}
?>