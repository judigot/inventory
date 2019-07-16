<?php require_once 'Imports/preload.php'; ?>
<?php
//==========SIZES==========//
require_once 'Classes/Database.php';
$connection = Database::connect($Host, $DatabaseName, $Username, $Password);
$result = Database::read($connection, "DESCRIBE `$app_customer`;");
$columns = [];
$sizes = [];
foreach ($result as $value) {
    $columns[] = str_replace(["_price", "_c"], ["", "-c"], $value["Field"]);
}
unset($columns[0], $columns[1], $columns[2], $columns[3], $columns[4], $columns[5]);
$sizes = array_values($columns);
//==========SIZES==========//

$notAllCaps = array("jumbo", "marble");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'Imports/top.php'; ?>
    <link href="Assets/css/home.css" rel="stylesheet" type="text/css" />
    <script src="Assets/js/home.js" type="text/javascript"></script>
    <script src="Assets/js/order.js" type="text/javascript"></script>
    <script src="Assets/js/customer.js" type="text/javascript"></script>

    <?php
    if ($_SESSION["user"]["user_type"] === "administrator") {
        ?>
        <script src="Assets/js/product.js" type="text/javascript"></script>
        <script src="Assets/js/productCategory.js" type="text/javascript"></script>
        <script src="Assets/js/report.js" type="text/javascript"></script>
        <script src="Assets/js/changePassword.js" type="text/javascript"></script>
    <?php } ?>

    <script src="Assets/js/sales.js" type="text/javascript"></script>
    <script src="Assets/js/quicksearch.js" type="text/javascript"></script>
    <link href="Vendor/Plugins/DataTables-1.10.16/css/jquery.dataTables.css" rel="stylesheet" type="text/css" />
    <script src="Vendor/Plugins/DataTables-1.10.16/js/jquery.dataTables.js" type="text/javascript"></script>
    <script src="Assets/js/animation.js" type="text/javascript"></script>
    <?php
    if ($_SESSION["user"]["user_type"] === "administrator") {
        ?>
        <script src="Assets/js/quickedit.js" type="text/javascript"></script>
        <script src="Assets/js/routes.js" type="text/javascript"></script>
    <?php } else { ?>
        <script src="Assets/js/routesSecretary.js" type="text/javascript"></script>
    <?php } ?>
</head>

<body data-app-settings='<?php echo json_encode($appSettings); ?>'>

    <div id="fieldSource" hidden>
        <div class="order-field">
            <span>Product: </span>
            <i id="removeProduct" class="fas fa-times"></i>
            <div class="searchable-product"></div>
            <div>Quantity: </div><input class="product-quantity" type="number" min="1" pattern="[0-9]" />
            <div>Discount: </div><input class="item-discount" type="number" min="0.01" step="0.01">
        </div>
    </div>

    <div id="changePasswordModal" class="modal" role="dialog">
        <div class="modal-dialog">
            <div class="title-bar">
                <div>
                    <span class="window-title"></span>
                    <span class="title-bar-controls">
                        <span data-dismiss="modal"><i class="fas fa-window-close" aria-hidden="true"></i></span>
                    </span>
                </div>
            </div>
            <div class="modal-content">
                <div class="modal-body">
                    <div><span>Old password: </span><input id="oldPassword" class="password-field" type="password" /></div>
                    <div><span>New password: </span><input id="newPassword" class="password-field" type="password" /></div>
                    <div><span>Confirm new password: </span><input id="confirmPassword" class="password-field" type="password" /></div>
                </div>
                <div class="modal-footer">
                    <button id="confirmNewPassword" class="btn">Confirm</button>
                    <button class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="salesSummaryModal" class="modal" role="dialog">
        <div class="modal-dialog">
            <div class="title-bar">
                <div>
                    <span class="window-title">Sales Summary</span>
                    <span class="title-bar-controls">
                        <span data-dismiss="modal"><i class="fas fa-window-close" aria-hidden="true"></i></span>
                    </span>
                </div>
            </div>
            <div class="modal-content">
                <div class="modal-body">
                    <div id="monthlySalesSummaryBox">
                        <div>
                            <h4>Year <select class="year-selector" id="activeYears1"></select></h4>
                        </div>
                        <h4>Monthly Sales</h4>
                    </div>
                    <div id="weeklySalesSummaryBox">
                        <div>
                            <h4><select class="week-selector" id="activeWeeks1"></select> of <span id="selectedYear1"></span></h4>
                        </div>
                        <h4>Weekly Sales</h4>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="newOrderModal" class="modal" role="dialog">
        <div class="modal-dialog">
            <div class="title-bar">
                <div>
                    <span class="window-title">New Order</span>
                    <span class="title-bar-controls">
                        <span data-dismiss="modal"><i class="fas fa-window-close" aria-hidden="true"></i></span>
                    </span>
                </div>
            </div>
            <div class="modal-content">
                <div class="modal-body">
                    <span>Customer: </span>
                    <div id="searchableCustomer" class="searchable-customer"></div>
                    <br>
                    <div id="pricesBox"></div>
                    <div id="addProductBox">
                        <hr>
                        <button id="addProduct" class="btn red"><i class="fas fa-plus"></i>&nbspAdd product</button>
                    </div>
                    <br><br>
                    <div id="mainOrder"></div>
                </div>
                <div class="modal-footer">
                    <div id="totalCostBox">
                        <span>Total: ₱ <span class="total-order-cost">0.00</span>&nbsp&nbsp&nbsp&nbsp&nbsp</span>
                    </div>
                    <button id="confirmNewOrder" class="btn">Confirm</button>
                    <button class="btn" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div id="editOrderModal" class="modal" role="dialog">
        <div class="modal-dialog">
            <div class="title-bar">
                <div>
                    <span class="window-title">Edit Order</span>
                    <span class="title-bar-controls">
                        <span data-dismiss="modal"><i class="fas fa-window-close" aria-hidden="true"></i></span>
                    </span>
                </div>
            </div>
            <div class="modal-content">
                <div class="modal-body">
                    <div id="orderedProductsContainer"></div>
                </div>
                <div class="modal-footer">
                    <button id="confirmOrderEdits" class="btn">Confirm</button>
                    <button class="btn" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="newProductModal" class="modal" role="dialog">
        <div class="modal-dialog">
            <div class="title-bar">
                <div>
                    <span class="window-title">New Product</span>
                    <span class="title-bar-controls">
                        <span data-dismiss="modal"><i class="fas fa-window-close" aria-hidden="true"></i></span>
                    </span>
                </div>
            </div>
            <div class="modal-content">
                <div class="modal-body">
                    <input id="newProductName" placeholder="Product name">
                    <br><br>
                    <div>
                        <span>Category: </span><select id="productCategoryName"></select>
                    </div>
                    <hr>
                    <span>Cost: <span class="currency-symbol">₱</span></span>
                    <input id="newProductCost" type="number" min="0.01" step="0.01">
                    <?php
                    if (!$appSettings["customPrice"]) { ?>
                        <br>
                        <span>Price: <span class="currency-symbol">₱</span></span>
                        <input id="newProductPrice" type="number" min="0.01" step="0.01">
                    <?php } ?>
                    <br><br>
                    <span>Initial stock: </span>
                    <input id="newProductInitStock" type="number" min="1">
                </div>
                <div class="modal-footer">
                    <button id="confirmNewProduct" class="btn">Confirm</button>
                    <button class="btn" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="newProductCategoryModal" class="modal" role="dialog">
        <div class="modal-dialog">
            <div class="title-bar">
                <div>
                    <span class="window-title">New Product Category</span>
                    <span class="title-bar-controls">
                        <span data-dismiss="modal"><i class="fas fa-window-close" aria-hidden="true"></i></span>
                    </span>
                </div>
            </div>
            <div class="modal-content">
                <div class="modal-body">
                    <input id="newProductCategoryName" placeholder="Category name">
                </div>
                <div class="modal-footer">
                    <button id="confirmNewProductCategory" class="btn">Confirm</button>
                    <button class="btn" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="newCustomerModal" class="modal" role="dialog">
        <div class="modal-dialog">
            <div class="title-bar">
                <div>
                    <span class="window-title">New Customer</span>
                    <span class="title-bar-controls">
                        <span data-dismiss="modal"><i class="fas fa-window-close" aria-hidden="true"></i></span>
                    </span>
                </div>
            </div>
            <div class="modal-content">
                <div class="modal-body">
                    <h4>Customer details:</h4>
                    <div><span>First name: </span><input id="customerFirstName" /></div>
                    <br>
                    <div><span>Last name: </span><input id="customerLastName" /></div>
                    <br>
                    <div><span>Address: </span><input id="customerAddress" /></div>
                    <hr>
                    <!------------------------------------Egg Inventory------------------------------------>
                    <!-- <h4>Prices:</h4> -->
                    <?php
                    for ($i = 0; $i < count($sizes); $i++) {
                        $title = "";
                        if (!in_array($sizes[$i], $notAllCaps)) {
                            $title = strtoupper($sizes[$i]);
                        } else {
                            $title = ucwords($sizes[$i]);
                        }
                        ?>
                        <!-- <div><span><?php echo $title ?>: </span> <input type="number" min="0.01" step="0.01" id="<?php echo $sizes[$i]; ?>Price"/></div> -->
                    <?php } ?>
                    <!------------------------------------Egg Inventory------------------------------------>
                </div>
                <div class="modal-footer">
                    <button id="confirmNewCustomer" class="btn">Confirm</button>
                    <button class="btn" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div id="sidebar">
            <div id="sidebar-content">
                <div class="dropdown">
                    <div class="new-box">New</div>
                    <div class="dropdown-content">
                        <button id="newOrder" class="btn">Order</button>
                        <?php
                        if ($_SESSION["user"]["user_type"] === "administrator") {
                            ?>
                            <button id="newProduct" class="btn">Product</button>
                            <button id="newProductCategory" class="btn">Product Category</button>
                        <?php } ?>
                        <button id="newCustomer" class="btn">Customer</button>
                    </div>
                </div>
                <hr>
                <div data-content-trigger="orders" class="content-selector">Orders</div>
                <?php
                if ($appSettings["customPrice"]) {
                    ?>
                    <!-- <div data-content-trigger="custom-prices" class="content-selector">Custom Prices</div> -->
                <?php } ?>
                <?php
                if ($_SESSION["user"]["user_type"] === "administrator") {
                    ?>
                    <div data-content-trigger="products" class="content-selector">
                        <span>Products</span>
                        <br>
                        <span data-placement="right" data-html="true" id="lowStockNotification" class="stock-notification"></span>
                        <span data-placement="right" data-html="true" id="stockNotification" class="stock-notification"></span>
                    </div>
                    <div data-content-trigger="product-categories" class="content-selector">Product Categories</div>
                <?php } ?>
                <div data-content-trigger="customers" class="content-selector">Customers</div>
                <?php
                if ($_SESSION["user"]["user_type"] === "administrator") {
                    ?>
                    <div data-content-trigger="reports" class="content-selector">Reports</div>
                <?php } ?>

            </div>
        </div>
        <div class="main-content">
            <div class="content-box orders-content">
                <button id="printOrders" class="btn red"><i class="fas fa-print"></i>&nbspPrint All</button>
                <div id="dateRangeBox">
                    <span>Range: </span>
                    <input id="startDate" class="order-range" type="date"><span> - </span><input id="endDate" class="order-range" type="date">
                </div>
                <!--------------------MAIN TABLE-------------------->
                <table id="ordersTable" class="display">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Order</th>
                            <th>Order Date</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>

                <?php
                if ($_SESSION["user"]["user_type"] === "administrator") {
                    ?>
                    <div id="financialSummaryBox"></div>
                <?php } ?>
                <!--------------------MAIN TABLE-------------------->
            </div>
            <div class="content-box products-content">
                <br>
                <!--------------------MAIN TABLE-------------------->
                <table id="productsTable" class="display">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Cost</th>
                            <th>Price</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <!--------------------MAIN TABLE-------------------->
            </div>
            <div class="content-box product-categories-content">
                <br>
                <!--------------------MAIN TABLE-------------------->
                <table id="categoryTable" class="display">
                    <thead>
                        <tr>
                            <th>Category ID</th>
                            <th>Category Name</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <!--------------------MAIN TABLE-------------------->
            </div>
            <div class="content-box customers-content">
                <!--<label class="switch"><input type="checkbox" checked="true"><span class="slider round"></span></label>-->
                <br>
                <!--------------------MAIN TABLE-------------------->
                <table id="customersTable" class="display">
                    <thead>
                        <tr>
                            <th>Customer ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Address</th>
                            <th>Date Added</th>
                            <?php
                            if ($appSettings["customPrice"]) {
                                for ($i = 0; $i < count($sizes); $i++) {
                                    $title = "";
                                    if (!in_array($sizes[$i], $notAllCaps)) {
                                        $title = strtoupper($sizes[$i]);
                                    } else {
                                        $title = ucwords($sizes[$i]);
                                    }
                                    ?>
                                    <th><?php echo $title; ?></th>
                                <?php }
                        } ?>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <!--------------------MAIN TABLE-------------------->
            </div>
            <?php
            if ($appSettings["customPrice"]) {
                ?>
                <div class="content-box custom-prices-content">
                    <!--<label class="switch"><input type="checkbox" checked="true"><span class="slider round"></span></label>-->
                    <br>
                    <!--------------------MAIN TABLE-------------------->
                    <table id="customPricesTable" class="display">
                        <thead>
                            <tr>
                                <th>Customer ID</th>
                                <th>Customer Name</th>
                                <th>Product</th>
                                <th>Custom Price</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <!--------------------MAIN TABLE-------------------->
                </div>
            <?php } ?>
            <div class="content-box reports-content">
                <div class="tab-content">
                    <button id="printAllReports" class="btn red print-report">
                        <i class="fas fa-print"></i><span>&nbspPrint all reports</span>
                    </button>
                    <!-------------------------QuickTable------------------------->
                    <div id="financialReportsBox">
                        <div>
                            <h4>Year <select class="year-selector" id="activeYears"></select></h4>
                        </div>

                        <span class="report-type">Monthly Gross Sales Report</span>
                        <button id="printMonthlyGrossSales" class="btn red print-report">
                            <i class="fas fa-print"></i><span>&nbspPrint</span>
                        </button>
                        <br><br>
                        <div id="monthlyGrossSalesBox"></div>
                        <hr>

                        <span class="report-type">Monthly Gross Profit Report</span>
                        <button id="printMonthlyGrossProfit" class="btn red print-report">
                            <i class="fas fa-print"></i><span>&nbspPrint</span>
                        </button>
                        <br><br>
                        <div id="monthlyGrossProfitBox"></div>
                        <hr>

                        <div>
                            <h4><select class="week-selector" id="activeWeeks"></select> of <span id="selectedYear"></span></h4>
                        </div>

                        <span class="report-type">Weekly Gross Sales Report</span>
                        <button id="printWeeklyGrossSales" class="btn red print-report">
                            <i class="fas fa-print"></i><span>&nbspPrint</span>
                        </button>
                        <br><br>
                        <div id="weeklyGrossSalesBox"></div>
                        <hr>

                        <span class="report-type">Weekly Gross Profit Report</span>
                        <button id="printWeeklyGrossProfit" class="btn red print-report">
                            <i class="fas fa-print"></i><span>&nbspPrint</span>
                        </button>
                        <br><br>
                        <div id="weeklyGrossProfitBox"></div>
                        <br>
                    </div>
                    <!-------------------------QuickTable------------------------->
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="home"><?php echo $appName; ?></a>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav navbar-right">
                    <?php
                    if ($_SESSION["user"]["user_type"] === "administrator") {
                        ?>
                        <li class="dropdown">
                            <a href="home" class="dropdown-toggle" data-toggle="dropdown">Settings<span>&nbsp;</span><b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="settings-item" data-user-type="admin">Change admin password</a>
                                </li>
                                <li>
                                    <a class="settings-item" data-user-type="secretary">Change secretary password</a>
                                </li>
                            </ul>
                        </li>
                    <?php } ?>
                    <li>
                        <a id="logout-button" href="#">Log Out</a>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>

    <?php include 'Imports/bottom.php'; ?>
</body>

</html>