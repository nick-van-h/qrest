$(document).ready(function () {
    /**
     * Form actions
     */
    $("#createAccountForm").submit(function (event) {
        event.preventDefault(); // Prevent form submission

        // Get form data
        var username = $("#newusername").val();
        var password = $("#newpassword").val();

        // Perform AJAX request
        $.ajax({
            url: baseUrl + "/interfaces/createAccount.php",
            type: "POST",
            data: {
                username: username,
                password: password,
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    showAlertSuccess(
                        "Account created successfully!",
                        "Please proceed to login with your new credentials",
                        3
                    );
                    setTimeout(function () {
                        $("#modal-1").removeClass("show");
                    }, 500);
                } else {
                    showAlertWarning(
                        "Account with username " + username + " already exists"
                    );
                }
                console.log(response.message);
            },
            error: function (xhr, status, error) {
                showAlertError("Internal server error");
                console.log(xhr.responseText);
            },
        });
    });

    $(function () {
        // Submit the login form via AJAX
        $("#login-form").submit(function (e) {
            e.preventDefault();

            // Get form data
            var username = $('#login-form input[name="username"]').val();
            var password = $('#login-form input[name="password"]').val();
            var url = baseUrl + "/interfaces/login.php";

            // Send an AJAX request to the server
            $.ajax({
                type: "POST",
                url: url,
                data: {
                    username: username,
                    password: password,
                    redirect: false,
                },
                success: function (response) {
                    console.log(response);
                    respData = JSON.parse(response);

                    console.log(respData);
                    if (respData.success) {
                        /**
                         * User is authenticated, redirect to app
                         * To set cookie via PHP no other data has to be sent to the browser (i.e. loading the page)
                         * Create a dummy form with the username/password value and a redirect=yes value, then submit the form
                         * By submitting the form the browser is redirected to the login.php page,
                         * which first sets the cookie and then redirects the browser to the actual app
                         */
                        var form = $(
                            '<form action="' +
                                baseUrl +
                                '/interfaces/login.php" method="post">' +
                                '<input type="hidden" name="username" value="' +
                                username +
                                '">' +
                                '<input type="hidden" name="password" value="' +
                                password +
                                '">' +
                                '<input type="hidden" name="redirect" value="yes">' +
                                "</form>"
                        );
                        $("body").append(form);
                        form.submit();
                    } else {
                        showAlertWarning("Invalid username or password");
                        // Display an error message
                        // $("#error-message").text(
                        //     "Invalid username or password"
                        // );
                        // halfmoon.initStickyAlert({
                        //     title: "Invalid username or password",
                        //     alertType: "alert-secondary",
                        //     hasDismissButton: false,
                        //     timeShown: 10000,
                        // });
                    }
                },
                error: function (xhr, status, error) {
                    showAlertError("Internal server error");
                    // halfmoon.initStickyAlert({
                    //     title: "Server error",
                    //     alertType: "alert-danger",
                    //     hasDismissButton: false,
                    //     timeShown: 3000,
                    // });
                    console.log(xhr.message);
                },
            });
        });
    });

    $(function () {
        // Submit the password reset form via AJAX
        $("#recoverAccountForm").submit(function (e) {
            e.preventDefault();

            // Get the form data
            var formData = $(this).serialize();

            // Send an AJAX request to the server
            $.ajax({
                type: "POST",
                url: baseUrl + "/interfaces/recoverAccount.php",
                data: formData,
                success: function (response) {
                    console.log(response);
                    respData = JSON.parse(response);

                    console.log(respData);
                    if (respData.success) {
                        showAlertSuccess(
                            "Password reset successful!",
                            "Please proceed to login with your new credentials",
                            3
                        );
                        // halfmoon.initStickyAlert({
                        //     title: "Password reset successful!",
                        //     content:
                        //         "Please proceed to login with your new credentials",
                        //     alertType: "alert-success",
                        //     hasDismissButton: false,
                        //     timeShown: 3000,
                        // });
                    } else {
                        showAlertWarning("Invalid username or recovery key");
                        // halfmoon.initStickyAlert({
                        //     title: "Invalid username or recovery key",
                        //     alertType: "alert-secondary",
                        //     hasDismissButton: false,
                        //     timeShown: 10000,
                        // });
                    }
                },
                error: function (xhr, status, error) {
                    showAlertError("Internal server error");
                    // halfmoon.initStickyAlert({
                    //     title: "Server error",
                    //     alertType: "alert-danger",
                    //     hasDismissButton: false,
                    //     timeShown: 3000,
                    // });
                    console.log(xhr.message);
                },
            });
        });
    });

    /**
     * Button actions
     */
    $("#sign-in-dropdown-toggle-btn").click(function () {
        //Focus on username input when clicking the sign in button
        setTimeout(function () {
            $("#login-form input[name='username']").focus();
        }, 10);
    });

    $("#modal-recoverykey button").click(function () {
        $("#modal-recoverykey").removeClass("show");
    });
});

function showSignUpModal() {
    halfmoon.toggleModal("modal-1");
    $("#createAccountForm input[name='username']").focus();
}
