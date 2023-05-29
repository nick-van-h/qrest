var scriptFilename = "/js/functions.js";
var rootPath = ""; // Will contain the path to your file

$(document).ready(function () {
    //Set the root path of the project based on the script src path
    $("script").each(function () {
        var $script = $(this);

        if (
            $script.attr("src") &&
            $script.attr("src").indexOf(scriptFilename) > -1
        ) {
            baseUrl = $script.attr("src").split(scriptFilename)[0];
            return false;
        }
    });

    //Add click handler to close the popup menu
    $(document).on("mousedown", function (event) {
        var $element = $("#popup-menu"); // Replace with the actual ID of your element

        // Check if the clicked target is the element or its descendants
        if (
            !$element.is(event.target) &&
            $element.has(event.target).length === 0 &&
            $element.hasClass(".show")
        ) {
            // Clicked outside the element, hide it
            // hidePopup();
        }
    });

    //Add click handler for database update

    $("#admin-updateDatabase").submit(function (event) {
        event.preventDefault(); // Prevent form submission

        // Get form data
        var username = $("#username").val();
        var password = $("#password").val();

        // Perform AJAX request
        $.ajax({
            url: baseUrl + "/interfaces/updateDatabase.php",
            type: "POST",
            data: {
                username: username,
                password: password,
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    showAlertSuccess("Database succesfully updated!");
                    $(".content-wrapper").html(response.content);
                } else {
                    showAlertWarning("Error while updating database");
                    console.log(response.message);
                }
            },
            error: function (xhr, status, error) {
                showAlertError("Internal server error");
                console.log(xhr.responseText);
            },
        });
    });
});
function showAlertMessage(title, message) {
    if (message == "" || message == undefined) {
        message = title;
        title = "";
    }
    halfmoon.initStickyAlert({
        title: title,
        content: message,
        alertType: "alert-default",
        hasDismissButton: false,
        timeShown: 5000,
    });
}
function showAlertSuccess(title, message) {
    if (message == "" || message == undefined) {
        message = title;
        title = "";
    }
    halfmoon.initStickyAlert({
        title: title,
        content: message,
        alertType: "alert-success",
        hasDismissButton: false,
        timeShown: 5000,
    });
}

function showAlertWarning(title, message) {
    if (message == "" || message == undefined) {
        message = title;
        title = "";
    }
    halfmoon.initStickyAlert({
        title: title,
        content: message,
        alertType: "alert-secondary",
        hasDismissButton: false,
        timeShown: 5000,
    });
}

function showAlertError(title, message, time) {
    if (message == "" || message == undefined) {
        message = title;
        title = "";
    }
    if (typeof sec === "undefined") {
        var timeShown = 5000;
    } else {
        var timeShown = time * 1000;
    }
    halfmoon.initStickyAlert({
        title: title,
        content: message,
        alertType: "alert-danger",
        hasDismissButton: false,
        timeShown: timeShown,
    });
}

function logError(message) {
    const error = new Error();

    console.log(message);
    console.log(error);
}

function replaceUrl(url) {
    if (window.history && window.history.replaceState) {
        window.history.replaceState({}, document.title, url);
    }
}

function showPopupFromButton(elm) {
    var popup = $("#popup-menu");
    //Get the elements
    var parentElement = $(elm).closest("li.has-uid");
    var parentRect = parentElement[0].getBoundingClientRect();
    var parentId = parentElement.attr("data-uid");
    var containerType = parentElement.closest("ul").attr("id");

    //Calculate X/Y position
    var windowHeight = $(window).height();
    var popupHeight = popup.outerHeight();
    var maxTop = windowHeight - popupHeight - 10;
    var topPosition = Math.min(parentRect.top, maxTop);
    var leftPosition = parentRect.right;

    //Hide the popup when clicking on the belonging menu button again, else show the popup
    if (popup.hasClass("show") && popup.attr("data-uid") === parentId) {
        hidePopup();
    } else {
        //Fill popup content
        $.ajax({
            url: baseUrl + "/interfaces/getPopupContent.php",
            type: "POST",
            data: {
                itemUid: parentId,
                itemType: containerType,
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    //Load content
                    $("#popup-menu").html(response.content);

                    //Show popup on correct position
                    popup
                        .css({
                            top: topPosition + "px",
                            left: leftPosition + "px",
                        })
                        .addClass("show")
                        .attr("data-uid", parentId)
                        .attr("data-container", containerType);
                } else {
                    showAlertError(
                        "Error while retrieving collection content",
                        response.message
                    );
                }
            },
            error: function (xhr, status, error) {
                showAlertError("Internal server error");
                logError(xhr.responseText);
            },
        });
    }
}

function hidePopup() {
    var popup = $("#popup-menu");
    popup.removeClass("show");
    popup.attr("data-uid", null);
    popup.html("");
}
