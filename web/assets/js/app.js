$(document).ready(function() {

    // Wireless networks connect button
    var connectForm = $('#connect-modal-form');

    $('.connect-button').click(function () {
        var ssid = $(this).attr('data-ssid');
        var bssid = $(this).attr('data-bssid');
        var security = $(this).attr('data-security');
        var keyManagement = $(this).attr('data-key-management');

        connectForm.find('#ssid').val(ssid);
        connectForm.find('#bssid').val(bssid);
        connectForm.find('#security').val(security);
        connectForm.find('#key-management').val(keyManagement);

        if (security === 'Open') {
            connectForm.find('#connect-modal-form-password').hide();
            connectForm.find('#password').prop('disabled', true);
        } else {
            connectForm.find('#connect-modal-form-password').show();
            connectForm.find('#password').prop('disabled', false);
        }
    });

    $('#connect-modal').on('shown.bs.modal', function () {
        connectForm.find('#password').focus();
    })

});
