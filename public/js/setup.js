$(document).ready(function () {
    $("#setupUpdateTablesForm").submit(function (event) {
        event.preventDefault(); // Prevent form submission

        // Get form data
        var username = $("#username").val();
        var password = $("#password").val();

        // Perform AJAX request
        $.ajax({
            url: baseUrl + "/interfaces/updateTables.php",
            type: "POST",
            data: {
                username: username,
                password: password,
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    halfmoon.initStickyAlert({
                        title: "Tables updated succesfully!",
                        alertType: "alert-success",
                        hasDismissButton: false,
                        timeShown: 3000,
                    });
                    setTimeout(function () {
                        location.reload();
                    }, 3000);
                } else {
                    halfmoon.initStickyAlert({
                        title: "Error",
                        content: response.message,
                        alertType: "alert-danger",
                        hasDismissButton: false,
                        timeShown: 3000,
                    });
                }
            },
            error: function (xhr, status, error) {
                halfmoon.initStickyAlert({
                    title: "Error",
                    content: xhr.responseText,
                    alertType: "alert-danger",
                    hasDismissButton: false,
                    timeShown: 3000,
                });
            },
        });
    });
});
