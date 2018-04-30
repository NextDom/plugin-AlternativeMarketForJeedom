$(document).ready(function () {
    var gitsListUl = $('#config-modal ul');
    for (var gitIndex = 0; gitIndex < gitsList.length; ++gitIndex) {
        var item = $('<li class="list-group-item">' + gitsList[gitIndex] + '</li>');
        gitsListUl.append(item);
    }
    $('#add-git').click(function() {
        var gitUser = $('#git-user').val();
        if (gitUser != '') {
            $.post({
                url: 'plugins/AlternativeMarketForJeedom/core/ajax/AlternativeMarketForJeedom.ajax.php',
                data: {
                    action: 'add',
                    params: 'gitUser',
                    data: gitUser
                },
                dataType: 'json',
                success: function (data, status) {
                    // Test si l'appel a échoué
                    if (data.state !== 'ok' || status !== 'success') {
                        $('#div_alert').showAlert({message: data.result, level: 'danger'});
                    }
                    else {
                        location.reload();
                    }
                },
                error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                }
            });
        }
    });
});
