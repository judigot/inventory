//========================================LOG IN MODULE========================================//

//====================GLOBAL VARIABLES====================//
var login_titleInterval;
var login_titleTimeout;
//====================GLOBAL VARIABLES====================//

//====================GENERAL====================//
$(function () {

    $("#email").focus();

    $(document).on("click", "#login-button", function (e) {
        login_filter();
    });

    $(document).on("keydown", function (e) {
        if (e.keyCode === 13) {
            login_filter();
        }
    });

    $(document).ajaxStart(function () {
        $("input").css({ "background-color": "white" });
        $("input").attr("disabled", "true");
        $(".btn").hide();
        $("#login-result").text("");
        $("#login-result").append("<img style='zoom: 50%;' src='Assets/images/preloaders/1.gif' alt='Loading...'>");
    });

});
//====================GENERAL====================//

//====================FUNCTIONS====================//
function login_filter() {
    clearInterval(login_titleInterval);
    clearTimeout(login_titleTimeout);
    if ($("#email").val() && $("#password").val()) {
        $.ajax({
            url: "Classes/process",
            type: "POST",
            dataType: "json",
            data: {
                loginFilter: "",
                data: { email: $("#email").val(), password: $("#password").val() }
            }
        }).done(function (data) {
            var result = data[0];
            var name = data[1];
            if (result === "success") {
                $("body").fadeOut(500);
                setTimeout(function () {
                    location.reload();
                }, 500);
            } else if (result === "fail") {
                document.title = "Incorrect password! - Inventory";
                $("#login-result").text("The password that you entered is incorrect.");
                $("#first-name").text("Hello, " + name);
                $("input").removeAttr("disabled");
                $(".btn").show();
                $("#password").css({ "background-color": "#F0817B" });
                login_titleInterval = setInterval(function () {
                    if (document.title !== "Incorrect password! - Inventory") {
                        document.title = "Incorrect password! - Inventory";
                    } else {
                        document.title = "Log in to Inventory - Inventory";
                    }
                }, 1000);
                login_titleTimeout = setTimeout(function () {
                    clearInterval(login_titleInterval);
                }, 10000);
                $("#password").focus();
            } else if (result === "invalid") {
                document.title = "Invalid user! - Inventory";
                $("#login-result").text("Your computer does not have any privileges to use this app. Please contact the developers.");
                $("#first-name").text("");
                $("input").removeAttr("disabled");
                $("#email").css({ "background-color": "#F0817B" });
                login_titleInterval = setInterval(function () {
                    if (document.title !== "Invalid user! - Inventory") {
                        document.title = "Invalid user! - Inventory";
                    } else {
                        document.title = "Log in to Inventory - Inventory";
                    }
                }, 1000);
                login_titleTimeout = setTimeout(function () {
                    clearInterval(login_titleInterval);
                }, 10000);
                $("#email, #password, #signupLink").remove();
            } else if (result === "error") {
                document.title = "Nonexistent username! - Inventory";
                $("#login-result").text("The username that you entered did not match any account.");
                $("#first-name").text("");
                $("input").removeAttr("disabled");
                $(".btn").show();
                $("#email").css({ "background-color": "#F0817B" });
                login_titleInterval = setInterval(function () {
                    if (document.title !== "Nonexistent username! - Inventory") {
                        document.title = "Nonexistent username! - Inventory";
                    } else {
                        document.title = "Log in to Inventory - Inventory";
                    }
                }, 1000);
                login_titleTimeout = setTimeout(function () {
                    clearInterval(login_titleInterval);
                }, 10000);
                $("#email").focus();
            }
            setTimeout(function () {
                $("input").css({ "background-color": "white" });
            }, 5000);
        }).fail(function (data) {
            $("#login-result").text("There was an error occured.");
            $("#password").focus();
            $("input").removeAttr("disabled");
            $(".btn").show();
        });
    } else {
        $("#login-result").text("Please fill up all the fields.");
        $("input").each(function () {
            if ($(this).val()) {
                $(this).css({ "background-color": "white" });
            } else {
                $(this).css({ "background-color": "#F0817B" });
            }
        });
    }
}
//====================FUNCTIONS====================//

//========================================LOG IN MODULE========================================//