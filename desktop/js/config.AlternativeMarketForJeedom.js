$(document).ready(function () {
    var gitsListUl = $('#config-modal ul');
    console.log(gitsList);
    for (var gitIndex = 0; gitIndex < gitsList.length; ++gitIndex) {
        var item = $('<li class="list-group-item">' + gitsList[gitIndex] + '</li>');
        var deleteButton = $('<button class="badge btn btn-danger" data-gituser="' + gitsList[gitIndex] + '">Supprimer</button>');

        deleteButton.click(function() {
            removeGitUser($(this).data('gituser'));
        });
        item.append(deleteButton);
        gitsListUl.append(item);
    }
    $('#add-git').click(addGitUser);
});

function addGitUser() {
    var gitUser = $('#git-user').val();
    if (gitUser != '') {
            var data = {action: 'gitUser', params: 'add', data: gitUser};
            ajaxQuery(data);
    }
}

function removeGitUser(gitUser) {
    if (gitUser != '') {
        var data = {action: 'gitUser', params: 'remove', data: gitUser};
        ajaxQuery(data);
    }
}

function ajaxQuery(data) {
    $.post({
        url: 'plugins/AlternativeMarketForJeedom/core/ajax/AlternativeMarketForJeedom.ajax.php',
        data: data,
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