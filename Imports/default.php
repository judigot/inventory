<?php
$rootPath = $_SERVER['DOCUMENT_ROOT'];
$projectPath = str_replace(chr(92), "/", getcwd());
$base = str_replace($rootPath, "", $projectPath) . "/";
?>

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<base href="<?php echo $base; ?>">

<!--Favicon-->
<link rel="icon" href="Assets/images/favicon.png" />
<!--Bootstrap-->
<link href="Vendor/bootstrap-3.3.7-dist/css/bootstrap.css" rel="stylesheet" type="text/css" />

<!--Font-Awesome-->
<link href="Vendor/fontawesome-free-5.8.2-web/css/all.css" rel="stylesheet" type="text/css" />

<!--jQuery 3.2.1-->
<script src="Vendor/jquery-3.2.1/jquery-3.2.1.js"></script>

<!-------------------Javascript Plugins------------------->

<!--jQuery UI 1.12.1-->
<script src="Vendor/Plugins/jquery-ui-1.12.1/jquery-ui.js"></script>

<!--Bootstrap Core JavaScript-->
<script src="Vendor/bootstrap-3.3.7-dist/js/bootstrap.js"></script>

<!--Notify.js-->
<script src="Vendor/Plugins/Notify.js/notify.js"></script>

<!--Bootstrap-Confirmation.js-->
<script src="Vendor/Plugins/Bootstrap-Confirmation.js/bootstrap-confirmation.min.js"></script>

<!--jQuery-ContextMenu-->
<link href="Vendor/Plugins/jQuery-contextMenu/jquery.contextMenu.css" rel="stylesheet" type="text/css" />
<script src="Vendor/Plugins/jQuery-contextMenu/jquery.contextMenu.js"></script>

<!--jquery.sorttable.js-->
<script src="Vendor/Plugins/sorttable-master/jquery.sorttable.js" type="text/javascript"></script>

<!--Bootstrap Timepicker-->
<link href="Vendor/Plugins/bootstrap-timepicker/css/bootstrap-timepicker.css" rel="stylesheet" type="text/css" />
<script src="Vendor/Plugins/bootstrap-timepicker/js/bootstrap-timepicker.js" type="text/javascript"></script>

<!-------------------Javascript Plugins------------------->