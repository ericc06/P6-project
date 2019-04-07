function displayMessage(msgType, message, targetElem) {
    targetElem.html(message).css('margin-top', '15px');
    switch (msgType) {
        case "success":
            targetElem.removeClass('error').addClass('success').slideDown(400).delay(2000).slideUp(400);
            break;
        case "error":
            targetElem.removeClass('success').addClass('error').slideDown(400).delay(2000).slideUp(400);
            break;
        default:
    }
};

$(document).ready(function () {
    displayMessage("success",
        "{{'cover_image_change_success'|trans }}",
        $('.cover-change-msg')
    );
});