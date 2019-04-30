function displayMessage(msgType, message, targetElem) {
    targetElem.html(message);
    switch (msgType) {
        case "success":
            targetElem.removeClass("error").addClass("success");
            break;
        case "error":
            targetElem.removeClass("success").addClass("error");
            break;
        default:
    }
    targetElem.slideDown(400).delay(2000).slideUp(400);
}
