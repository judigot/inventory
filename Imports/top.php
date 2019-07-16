<?php
require_once 'phpDefaults.php';
require_once 'default.php';
require_once 'custom.php';

$CurrentPage = substr(basename($_SERVER['PHP_SELF']), 0, -4);
$SiteName = "Inventory";
$PageTitle;

// Page Title Declarations
switch ($CurrentPage) {
    case 'index':
        $PageTitle = "$SiteName";
        break;

    case 'login':
        $PageTitle = "Log in to $SiteName - $SiteName";
        break;

    case 'signup':
        $PageTitle = "Sign up for $SiteName - $SiteName";
        break;

    case 'home':
        $PageTitle = "Home - $SiteName";
        break;

    case 'print':
        $PageTitle = "Print - $SiteName";
        break;

    default:
        $PageTitle = "Untitled page!";
}
?>
<title><?php echo $PageTitle; ?></title>