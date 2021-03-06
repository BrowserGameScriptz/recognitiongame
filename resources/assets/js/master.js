
function master_notification_proc(notification_text, notification_type, header) {
    // Toastr notifications counter, more than one from a type at the same time not allowed
    // notification_type: info(0), success(1), warning(2), error(3)
    howlong_stay = "3000";
    toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": true,
        "positionClass": "toast-bottom-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "500",
        "hideDuration": "500",
        "timeOut": howlong_stay,
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }
    if (notification_type === 0) toastr["info"](notification_text, header);
    if (notification_type === 1) toastr["success"](notification_text, header);
    if (notification_type === 2) toastr["warning"](notification_text, header);
    if (notification_type === 3) toastr["error"](notification_text, header);
}