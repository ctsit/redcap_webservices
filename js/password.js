if (typeof REDCapWebServices === 'undefined') {
    var REDCapWebServices = {};
}

REDCapWebServices.passwordFieldHandler = function($element) {
    $element.prop('type', 'password');
};
