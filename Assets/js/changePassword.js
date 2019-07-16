/* global order_ordersTable, customer_customersTable */

//========================================HOME========================================//

//====================GLOBAL VARIABLES====================//
//====================GLOBAL VARIABLES====================//

//====================GENERAL====================//
$(function () {

    $(document).on("click", ".settings-item", function (e) {
        $("#oldPassword, #newPassword, #confirmPassword").val("");

        var userType = $(this).attr("data-user-type");
        document.getElementById("confirmNewPassword").data = { "userType": userType };
        $("#changePasswordModal").modal({ backdrop: 'static' });
        $("#changePasswordModal").find(".window-title").html(`Change ${custom_capitalizeFirstLetter(userType)} Password`);
    });

    $(document).on("click", "#confirmNewPassword", function (e) {
        var userType = document.getElementById("confirmNewPassword").data["userType"];
        var oldPassword = $("#oldPassword").val();
        var newPassword = $("#newPassword").val();
        var confirmPassword = $("#confirmPassword").val();

        if (oldPassword && newPassword && confirmPassword) {
            if (newPassword === confirmPassword) {
                $.ajax({
                    url: "Classes/Authenticate",
                    type: "POST",
                    dataType: "json",
                    data: {
                        read: "changeUserPassword",
                        data: {
                            userType: userType,
                            oldPassword: oldPassword,
                            newPassword: newPassword
                        }
                    }
                }).done(function (data) {
                    var updateResult = data;
                    if (updateResult === "success") {
                        $('#changePasswordModal').modal('toggle'); // Close modal
                        delete document.getElementById("confirmNewPassword").data["userType"];
                        custom_notify(`${custom_capitalizeFirstLetter(userType)} password was successfully changed.`, null);
                    } else if (updateResult === "error") {
                        custom_notify("The new password must be different from the old one.");
                    } else if (updateResult === "fail") {
                        custom_notify("The old password that you entered is incorrect.");
                    }
                }).fail(function (data) {
                });
            } else {
                custom_notify("The new passwords that you have entered do not match.");
            }
        } else {
            custom_notify("All fields are required.");
        }
    });
});
//====================GENERAL====================//

//====================FUNCTIONS====================//
//====================FUNCTIONS====================//

//========================================HOME========================================//