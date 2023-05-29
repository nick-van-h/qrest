$(document).ready(function () {
    /**
     * Popup handling
     */
    $("#collections .icon-more").click(function () {
        showPopupFromButton(this);
    });

    //Hide popup when clicking outside the popup
    $(document).click(function (e) {
        if (!$(e.target).closest(".icon-more, #popup").length) {
            hidePopup();
        }
    });

    // Hide popup when pressing the escape key
    $(document).keydown(function (e) {
        if (e.keyCode === 27) {
            hidePopup();
        }
    });

    //Hide popup when scrolling the parent container
    $("#list-outer-container").scroll(function () {
        hidePopup();
    });

    /**
     * Collection handling
     */

    //Add new collection
    $("#newCollectionForm").submit(function (event) {
        event.preventDefault(); // Prevent form submission

        // Get form data
        var collectionName = $(
            "#newCollectionForm input[name='collectionName']"
        ).val();
        // Perform AJAX request
        $.ajax({
            url: baseUrl + "/interfaces/createCollection.php",
            type: "POST",
            data: {
                collectionName: collectionName,
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    //Show message
                    showAlertSuccess(
                        "Collection created",
                        "New collection " + collectionName + " created",
                        3
                    );
                    console.log(response);
                    //Close & reset the modal
                    $("#modal-newCollection").removeClass("show");
                    $("#newCollectionForm")[0].reset();

                    //Add the new item to the list & add click handler
                    $("#collections").append(response.collection_li);
                    $(
                        "#collections li[data-uid=" +
                            response.uid +
                            "] .icon-more"
                    ).click(function () {
                        showPopupFromButton(this);
                    });
                } else {
                    showAlertError(
                        "Error while creating collection",
                        response.message
                    );
                }
            },
            error: function (xhr, status, error) {
                showAlertError("Internal server error");
                logError(xhr.responseText);
            },
        });
    });

    //Remove collection
    $("#removeCollectionForm").submit(function (event) {
        event.preventDefault(); // Prevent the form from submitting normally

        var uid = $("#removeCollectionForm").attr("data-collectionUid");
        halfmoon.toggleModal("modal-removeCollection");
        deleteCollectionAcknowledged(uid);
    });

    //Show collection items on clicking (but not when clicking the dots) and when clicked collection is not yet active
    $("#collections")
        .on("click", "li", function (event) {
            if (
                !$(event.target).is("i.icon-more") &&
                !(
                    $(this).hasClass("selected") ||
                    $(this)
                        .closest('li[data-type="collection"]')
                        .hasClass("selected")
                )
            ) {
                var collectionUid = $(this).data("uid");
                var name = $(this).find(".name").text();
                document.title = name + " - Qrest";

                //Check if items template is already loaded, if not request full page otherwise request items list only
                var fullpage = $("#content #content-list").length === 0;
                if (collectionUid) {
                    // Make an AJAX request and pass the attribute value
                    $.ajax({
                        url: baseUrl + "/interfaces/getCollectionItems.php",
                        type: "POST",
                        data: {
                            collectionUid: collectionUid,
                            fullpage: fullpage,
                        },
                        dataType: "json",
                        success: function (response) {
                            if (response.success) {
                                //Replace content
                                if (fullpage) {
                                    var tgt = $("#content");
                                } else {
                                    var tgt = $("#list-items");
                                }
                                tgt.html(response.content);

                                //Replace URL
                                replaceUrl(
                                    baseUrl + "/app/list/" + collectionUid
                                );

                                //Hide details pane
                                hideItemDetails();

                                //Add form submission handler
                                $("#newItemForm").submit(function (event) {
                                    createNewItem(event);
                                });

                                // addItemTriggers();
                                enableListInteraction();

                                //Enable summerNote if full page was loaded
                                if (fullpage) initSummernote();
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
                $("#collections .selected").removeClass("selected");
                $(this).addClass("selected");
            }
        })
        .sortable({
            // placeholder: "placeholder",
            scroll: true,
            axis: "y",
            scrollSpeed: 3,
            distance: 5,
            delay: 10,
            tolerance: "pointer",
            opacity: 0.5,
            receive: function (event, ui) {
                //Get properties
                var receivedItem = ui.item;
                var checkbox = receivedItem.find('input[type="checkbox"]');
                itemUid = receivedItem.data("uid");
                itemChecked = checkbox.is(":checked");

                //Toggle the checkbox
                checkbox.prop("checked", !checkbox.prop("checked"));

                //Update the item in the database
                updateItem(receivedItem);
            },
            update: function (event, ui) {
                //Get new list item order
                var sortedOrder = $(this).sortable("toArray", {
                    attribute: "data-uid",
                });

                //Get current sortOrders and sort them ascending
                var sortOrders = [];

                $("#collections li.has-uid").each(function () {
                    var order = $(this).attr("data-sortOrder");
                    sortOrders.push(order);
                });
                sortedSortOrders = [...sortOrders];

                sortedSortOrders.sort(function (a, b) {
                    return a - b;
                });

                //Create array of items to receive a new sort order
                var updateItems = [];
                $.each(sortedOrder, function (index, value) {
                    if (sortedSortOrders[index] != sortOrders[index]) {
                        //Add the item to the array of items to be updated
                        updateItems.push({
                            uid: value,
                            newSortOrder: sortedSortOrders[index],
                        });

                        //Update the sort order on the element itself
                        $('#collections li[data-uid="' + value + '"]').attr(
                            "data-sortOrder",
                            sortedSortOrders[index]
                        );
                    }
                });

                // Make AJAX request to update the sort order on the server
                $.ajax({
                    url: baseUrl + "/interfaces/updateCollectionOrders.php",
                    type: "POST",
                    data: { updateItems: updateItems },
                    success: function (response) {},
                    error: function (xhr, status, error) {
                        logError("Error updating sort order: " + error);
                    },
                });
            },
        });

    //Collections sub list for receiving tasks
    $(".collection-wrapper")
        .sortable({
            // connectWith: ".sortable-list",
            items: "li:not(.ui-state-disabled)",
            receive: function (event, ui) {
                //Check if the item was dropped on its own collection
                var targetCollectionUid = $(this)
                    .closest("li")
                    .attr("data-uid");
                var targetCollectionName = $(this).find(".name").text();
                var selectedCollectionUid = $("#collections .selected").attr(
                    "data-uid"
                );
                if (targetCollectionUid == selectedCollectionUid) {
                    // Cancel the receive event
                    $(ui.sender).sortable("cancel");
                    return;
                }
                // var sourceList = ui.sender.attr("id");
                // var targetList = $(this).attr("id");

                var receivedItem = ui.item;
                receivedItem.addClass("sudo-hide");
                var receivedItemUid = ui.item.attr("data-uid");
                // Make an AJAX request and pass the attribute value
                $.ajax({
                    url: baseUrl + "/interfaces/moveItem.php",
                    type: "POST",
                    data: {
                        itemUid: receivedItemUid,
                        newCollectionUid: targetCollectionUid,
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response.success) {
                            showAlertMessage(
                                "Item moved",
                                ui.item.find(".list-item-text").text() +
                                    " moved to " +
                                    targetCollectionName
                            );

                            //Item moved succesfully, remove it from the list
                            ui.item.remove();
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
            },
        })
        .disableSelection();

    $(".collection-wrapper")
        .sortable({
            items: "li:not(.ui-state-disabled)",
        })
        .disableSelection();
});

function renameCollection() {
    var tgt = $("#listName");
    tgt.focus();
    placeCursorAtEnd(tgt);
}

function deleteCollection(uid) {
    //Get the number of items on the list
    $.ajax({
        url: baseUrl + "/interfaces/getNrItemsOnCollection.php",
        type: "POST",
        data: { collectionUid: uid },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                if (response.content.count > 0) {
                    halfmoon.toggleModal("modal-removeCollection");

                    collectionName = $(
                        "#collections .selected span.name"
                    ).text();

                    $("#modal-removeCollection p").text(
                        "Delete collection " +
                            collectionName +
                            " and the " +
                            response.content.count +
                            " items within this collection?"
                    );
                    $("#removeCollectionForm").attr("data-collectionUid", uid);
                } else {
                    deleteCollectionAcknowledged(uid);
                }
            } else {
                showAlertError(
                    "Error while retrieving item details",
                    response.message
                );
                logError(response.message);
            }
        },
        error: function (xhr, status, error) {
            logError("Internal server error: " + error);
        },
    });
}

function deleteCollectionAcknowledged(uid) {
    var selectedUid = $("#collections .selected").attr("data-uid");
    var collectionName = $(
        '#collections li[data-uid="' + uid + '"] span.name'
    ).text();

    //Remove/hide the list/details if removed collection is selected
    if (selectedUid == uid) {
        $("#list-items").text("");
        $("#list-itemDetails").addClass("hidden");
    }

    //Remove the collection li element
    var tgt = $('#collections li[data-uid="' + uid + '"]');
    tgt.remove();

    // Make AJAX request to remove the list
    $.ajax({
        url: baseUrl + "/interfaces/deleteCollection.php",
        type: "POST",
        data: { collectionUid: uid },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                showAlertMessage("Collection " + collectionName + " removed");
            } else {
                showAlertError(
                    "Error while retrieving item details",
                    response.message
                );
                logError(response.message);
            }
        },
        error: function (xhr, status, error) {
            logError("Error deleting collection: " + error);
        },
    });
}

function showNewCollectionModal() {
    halfmoon.toggleModal("modal-newCollection");
    $("#newCollectionForm input[name='collectionName']").focus();
}

function showNewTagModal() {
    halfmoon.toggleModal("modal-newTag");
    $("#newTagForm input[name='tagName']").focus();
}
