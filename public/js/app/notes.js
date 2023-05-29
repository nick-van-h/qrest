var originalNote = "";

$(document).ready(function () {
    initSummernote();
});
var node = document.createElement("input");
node.type = "checkbox";
var checkbox = function (context) {
    var ui = $.summernote.ui;

    // create button
    var button = ui.button({
        contents: "▢",
        tooltip: "Checkbox",
        click: function () {
            // invoke insertText method with 'hello' on editor module.
            context.invoke("editor.pasteHTML", '<input type="checkbox">');
        },
    });

    return button.render(); // return button as jquery object
};
function initSummernote() {
    $("#summernote").summernote({
        toolbar: [
            // [groupName, [list of button]]
            ["style", ["style", "bold", "italic", "underline", "clear"]],
            ["font", ["fontname", "fontsize", "height"]],
            ["fontdecoration", ["strikethrough", "superscript", "subscript"]],
            ["color", ["color"]],
            ["para", ["checkbox", "ul", "ol", "paragraph"]],
            ["table", ["table", "hr"]],
            ["insert", ["link", "picture", "video"]],
            // ["view", ["codeview", "undo", "redo", "help"]],
            ["view", ["undo", "redo", "help"]],
            ["mybutton", ["hello"]],
        ],

        buttons: {
            checkbox: checkbox,
        },
        placeholder: "Write something...",
        callbacks: {
            onInit: function () {
                $("#summernote").removeClass("raw");
                $(".summernote-wrapper .note-statusbar").remove();
                $(".summernote-wrapper .note-status-output").remove();
            },

            onBlur: function () {
                saveNote();
            },
            onBlurCodeview: function () {
                saveNote();
            },
            onChange: function (contents, $editable) {
                if (contents.substring(0, 11) == "<p><br></p>") {
                    $editable.html(contents.substring(11));
                }
            },
        },
    });

    //Trigger on title change
    $("#itemDetails-itemName").on("input", function () {
        //Limit max length
        var maxCharacters = 100;
        editableDiv = $(this);
        var text = editableDiv.text();
        if (text.length > maxCharacters) {
            editableDiv.text(text.substring(0, maxCharacters)); // Truncate the excess characters
            placeCursorAtEnd(editableDiv);
        }

        //Update details header simultaniously
        $(".editable .list-item-text").text(text);
    });

    //Trigger item update on blur
    $("#itemDetails-itemName").on("blur", function () {
        updateItem($(".editable .list-item-text"));
    });
}

function saveNote() {
    //Get UID & note content
    var note = $("#summernote").summernote("code");
    // var note = $("#editor .ProseMirror").html();

    var itemUid = $("#itemDetails-itemName").attr("data-itemUid");

    //Check if note has changed
    if (originalNote == note) {
        return;
    }

    if (itemUid) {
        //Post
        $.ajax({
            url: baseUrl + "/interfaces/saveNote.php",
            type: "POST",
            data: {
                itemUid: itemUid,
                note: note,
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    //Nothing to be done (yet)
                    showAlertMessage("Note saved");
                } else {
                    showAlertError("Error while saving note", response.message);
                    logError(response.message);
                }
            },
            error: function (xhr, status, error) {
                showAlertError("Internal server error");
                logError(xhr.responseText);
            },
        });
    }
}
