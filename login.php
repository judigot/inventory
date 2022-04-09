<?php require_once 'Imports/preload.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once 'Imports/top.php'; ?>
    <link href="Assets/css/login.css" rel="stylesheet" type="text/css" />
    <?php

    $easyLogin = true;

    if ($easyLogin) { ?>

        <script src="Assets/js/easyLogin.js" type="text/javascript"></script>

    <?php } else { ?>

        <script src="Assets/js/login.js" type="text/javascript"></script>

    <?php } ?>
</head>

<body id="login-body">
    <div id="modal-backdrop">
        <div id="modal-body">
            <div id="form-body">
                <h1><?php echo $appName; ?></h1><br>

                <?php
                if ($easyLogin) { ?>

                    <div id="userTypesBox">

                        <div id="adminBox">
                            <i id="admin" class="user-icon fas fa-user-tie"></i>
                            <h4>Admin</h4>
                        </div>

                        <div id="secretaryBox">
                            <i id="secretary" class="user-icon fas fa-user"></i>
                            <h4>POS</h4>
                        </div>

                    </div>
                    <br>
                    <div id="easyPassword">
                        <input id="email" class="field" type="hidden">
                        <input id="password" class="field" type="password" placeholder="Password">
                        <div id="login-result"></div>
                        <button class="btn submit-button" id="login-button">LOG IN</button>
                    </div>

                <?php } else { ?>
                    <span id="first-name"></span>
                    <div id="credentialBox">
                        <input id="email" class="field" type="text" placeholder="User" <?php echo isset($_SESSION["username"]) ? " value=\"{$_SESSION["username"]}\"" : false; ?>>
                        <input id="password" class="field" type="password" placeholder="Password">
                        <div id="login-result"></div>
                        <button class="btn submit-button" id="login-button">LOG IN</button>
                    </div>

                <?php } ?>
            </div>
        </div>
    </div>
    <?php require_once 'Imports/bottom.php'; ?>
</body>

</html>