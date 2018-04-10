$(document).ready(function() {
    $('.query-url-clipboard button').click(function() {
        $(this).parent().parent().find('.query-url').select();
        document.execCommand('copy');
    });
});
