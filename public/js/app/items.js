$(document).ready(function () {
    /**
     * Items handling
     */

    //Add new item
    $("#newItemForm").submit(function (event) {
        createNewItem(event);
    });

    //Update changed item
    $("form.item").on("input", function () {
        updateItem(this);
    });
    $("form.item").submit(function (event) {
        event.preventDefault();
    });

    //limit length

    enableListInteraction();
});

$(document).keydown(function (event) {
    if (event.keyCode === 27) {
        //Esc
        stopEditing();
    }
    if (event.keyCode === 9 || event.keyCode === 13) {
        //Tab or enter
        if ($(".editable .list-item-text").is(":focus")) {
            event.preventDefault();

            if (event.shiftKey) {
                var nextItem = $(".editable").prev("li");
            } else {
                var nextItem = $(".editable").next("li");
            }
            // Blur editing item on Esc, tab, enter
            // stopEditing();
            if (nextItem.length) {
                stopEditing();
                editItem(nextItem);
                placeCursorAtEnd(nextItem.find(".list-item-text"));
                nextItem.find(".list-item-text").focus();
            } else {
                stopEditing();
            }
        }
    }
});

function createNewItem(event) {
    event.preventDefault(); // Prevent form submission
    // Get form data
    var name = $('#newItemForm input[name="itemName"]').val();
    var collectionUid = $("#collections .selected").data("uid");

    // Perform AJAX request
    $.ajax({
        url: baseUrl + "/interfaces/createItem.php",
        type: "POST",
        data: {
            itemName: name,
            collectionUid: collectionUid,
        },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                //Add the new item to the list & add click handler
                $("#list").prepend(response.item_li);
                $("#newItemForm")[0].reset();
                addListItemTriggers();
            } else {
                showAlertError(
                    "Error while creating collection",
                    response.message
                );
                logError(response.message);
            }
        },
        error: function (xhr, status, error) {
            showAlertError("Internal server error");
            logError(xhr.responseText);
        },
    });
}

function enableListInteraction() {
    //List name triggers
    if ($("#listName").data("isInited") === undefined) {
        //Trigger on title change
        $("#listName").on("input", function () {
            //Limit max length
            var maxCharacters = 100;
            editableDiv = $(this);
            var text = editableDiv.text();
            if (text.length > maxCharacters) {
                editableDiv.text(text.substring(0, maxCharacters)); // Truncate the excess characters
                placeCursorAtEnd(editableDiv);
            }

            //Update details header simultaniously
            $("#collections .selected span.name").text(text);
        });

        //Save name on blur

        $("#listName").on("blur", function () {
            listUid = $("#collections .selected").attr("data-uid");
            listName = $("#listName").text();

            $.ajax({
                url: baseUrl + "/interfaces/updateCollection.php",
                type: "POST",
                data: {
                    collectionName: listName,
                    collectionUid: listUid,
                },
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        /**
                         * If the item is now checked move it to the checked list,
                         * else move it to the active list
                         */
                    } else {
                        showAlertError(
                            "Error while creating collection",
                            response.message
                        );
                        logError(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    showAlertError("Internal server error");
                    logError(xhr.responseText);
                },
            });
        });

        //Set inited true
        $("#listName").data("isInited", true);
    }

    //List items triggers
    $(".items-list").each(function () {
        if ($(this).data("isInited") === undefined) {
            $(".items-list").sortable({
                placeholder: "placeholder",
                connectWith: ".connectedSortable",
                scroll: true,
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

                    //Get current sortOrders and sort them descending
                    var sortOrders = [];

                    $("#list li").each(function () {
                        var order = $(this).attr("data-sortOrder");
                        sortOrders.push(order);
                    });
                    sortedSortOrders = [...sortOrders];

                    sortedSortOrders.sort(function (a, b) {
                        return b - a;
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
                            $('#list li[data-uid="' + value + '"]').attr(
                                "data-sortOrder",
                                sortedSortOrders[index]
                            );
                        }
                    });

                    // Make AJAX request to update the sort order on the server
                    $.ajax({
                        url: baseUrl + "/interfaces/updateItemOrders.php",
                        type: "POST",
                        data: { updateItems: updateItems },
                        success: function (response) {},
                        error: function (xhr, status, error) {
                            logError("Error updating sort order: " + error);
                        },
                    });
                },
            });

            // Update sort order display when sorting changes
            $(this).on("sortupdate", function (event, ui) {
                $(".sort-order", this).each(function (index) {
                    $(this).text(index + 1);
                });
            });

            //Set inited true
            $(this).data("isInited", true);
        }
    });

    //Individual item triggers
    addListItemTriggers();
}

function addListItemTriggers() {
    /**
     * Loop through each list item and check if it is inited already
     * If not, init all individual items and update data item
     */
    $(".items-list li.has-uid").each(function () {
        if ($(this).attr("data-isInited") === undefined) {
            var elm = $(this);

            /**
             * Use Mousedown event to beat the sortable trigger
             * If the list is sortable before making it editable the cursor isn´t placed in the text field
             * Check if the clicked item is the actual text, if so make it editable
             * If not remove editable from the other item; if an item is editable
             * and another item is being dragged will mess with the editable states
             */
            $(elm).mousedown(function (event) {
                var target = event.target;

                if ($(target).is(".list-item-text")) {
                    // Perform action if the target is a <span>
                    $("#list").sortable("disable");

                    editItem(this);
                } else {
                    $("#list")
                        .find("li.editable")
                        .find(".list-item-text")
                        .blur();
                }
            });

            /**
             * When user releases the mouse button on an item it means they want to edit it
             * MouseDown might already have made the item editable (because clicked on the text) so only if not yet editable
             * Set mouse cursor position to the end, in this case the user didn´t click on the actual text
             */
            $(elm).mouseup(function (event) {
                var target = event.target;
                if (!$(this).hasClass("editable")) {
                    editItem(this);

                    var listItemText = $(this).find(".list-item-text");
                    placeCursorAtEnd(listItemText);
                }
            });

            /**
             * Enable sortable on the list item, will be resolved after the mousedown event
             * If the user also clicked the text area the cursor is already at the right position
             */
            $(elm).on("click", function () {
                $(elm).parent().sortable("enable");
            });

            //Trigger on checkbox change
            $(elm)
                .find('input[type="checkbox"]')
                .change(function () {
                    updateItem(this);
                });

            //Trigger on list item change
            $(elm)
                .find(".list-item-text")
                .on("input", function () {
                    //Limit max length
                    var maxCharacters = 100;
                    editableDiv = $(this);
                    var text = editableDiv.text();
                    if (text.length > maxCharacters) {
                        editableDiv.text(text.substring(0, maxCharacters)); // Truncate the excess characters
                        placeCursorAtEnd(editableDiv);
                    }

                    //Update details header simultaniously
                    $("#itemDetails-itemName").text(text);
                });

            // Process edits on blur
            $(elm).on("blur", ".list-item-text", function () {
                var listItem = $(this).closest("li");

                //Disable editing
                $("#list").sortable("enable");
                // listItem.removeClass("editable");
                $(this).prop("contenteditable", false);

                updateItem(this);
            });

            //Show popup
            $(elm)
                .find(".icon-more")
                .click(function () {
                    showPopupFromButton(this);
                });

            //Set inited true for this element
            $(this).attr("data-isInited", "ok");
        }
    });
}

function updateItem(elm) {
    var listItem = $(elm).closest("li");

    //Get item values
    var checkBox = listItem.find('input[type="checkbox"]');
    var newText = listItem.find(".list-item-text").text().trim();

    if (newText == "") {
        listItem.find(".list-item-text").text("<empty>");
        newText = "<empty>";
    }

    itemUid = listItem.data("uid");
    itemChecked = checkBox.is(":checked");

    $.ajax({
        url: baseUrl + "/interfaces/updateItem.php",
        type: "POST",
        data: {
            itemName: newText,
            itemChecked: itemChecked,
            itemUid: itemUid,
        },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                /**
                 * If the item is now checked move it to the checked list,
                 * else move it to the active list
                 */
                if (itemChecked) {
                    if (listItem.parent().is("#list"))
                        listItem.appendTo("#list-completed");
                } else {
                    if (listItem.parent().is("#list-checked"))
                        listItem.appendTo("#list");
                }
            } else {
                showAlertError(
                    "Error while creating collection",
                    response.message
                );
                logError(response.message);
            }
        },
        error: function (xhr, status, error) {
            showAlertError("Internal server error");
            logError(xhr.responseText);
        },
    });

    //Save the note

    // saveNote();
}

function stopEditing() {
    var elm = $(".editable");
    $(".editable .list-item-text").blur();

    var selectedCollection = $("#collections .selected");
    if (selectedCollection.length != 0) {
        collectionUid = $("#collections .selected").attr("data-uid");
    } else {
        collectionUid = "all";
    }

    //Place only collection in the address bar
    replaceUrl(baseUrl + "/app/list/" + collectionUid);

    //Deselect item
    elm.removeClass("editable");

    //Hide details pane
    hideItemDetails();
}

function editItem(elm) {
    //Update the element to editable
    $(".editable").each(function () {
        $(this).removeClass("editable");
    });
    $(elm).addClass("editable");
    var listItemText = $(elm).find(".list-item-text");
    listItemText.prop("contenteditable", true);

    //Get variables
    var selectedCollection = $("#collections .selected");
    if (selectedCollection.length != 0) {
        collectionUid = $("#collections .selected").attr("data-uid");
    } else {
        collectionUid = "all";
    }
    itemUid = $(elm).attr("data-uid");
    itemName = $(elm).text();

    //Place the UID in the address bar
    replaceUrl(baseUrl + "/app/list/" + collectionUid + "/" + itemUid);

    //Get the item content & place in sidebar

    $.ajax({
        url: baseUrl + "/interfaces/getItemDetails.php",
        type: "POST",
        data: {
            itemUid: itemUid,
        },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                //Update the header
                $("#itemDetails-itemName")
                    .attr("data-itemUid", itemUid)
                    .text(itemName);

                //Save the note as-is in a variable
                originalNote = response.content.note;

                // //Paste note in editor
                // $("#editor .ProseMirror").html(originalNote);

                //Paste the note in summernote
                $("#summernote").summernote("reset");
                // $("#summernote").summernote("pasteHTML", response.content.note);
                // $("#summernote").summernote("code", originalNote);
                $("#summernote").html(originalNote);

                //Unhide the details tab
                showItemDetails();
            } else {
                showAlertError(
                    "Error while retrieving item details",
                    response.message
                );
                logError(response.message);
            }
        },
        error: function (xhr, status, error) {
            showAlertError("Internal server error");
            logError(xhr.responseText);
        },
    });
}

function placeCursorAtEnd(elm) {
    var textNode = $(elm)[0].firstChild;
    var range = document.createRange();
    var sel = window.getSelection();
    range.setStart(textNode, textNode.length);
    range.collapse(true);
    sel.removeAllRanges();
    sel.addRange(range);
}

function showItemDetails() {
    $("#list-itemDetails").removeClass("hidden");
}
function hideItemDetails() {
    $("#list-itemDetails").addClass("hidden");
}

function deleteItem(uid) {
    var tgt = $('#list-items li[data-uid="' + uid + '"]');
    var itemName = $(tgt).find(".list-item-text").text();

    $.ajax({
        url: baseUrl + "/interfaces/deleteItem.php",
        type: "POST",
        data: {
            itemUid: itemUid,
        },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                showAlertMessage("Item " + itemName + " deleted");
                console.log(tgt);
                $(tgt).remove();
            } else {
                showAlertError("Error while deleting item", response.message);
                logError(response.message);
            }
        },
        error: function (xhr, status, error) {
            showAlertError("Internal server error");
            logError(xhr.responseText);
        },
    });
}
