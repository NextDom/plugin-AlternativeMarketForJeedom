// Point d'entrée du script
$(document).ready(function () {
    var gitsListUl = $('#config-modal ul');
    for (var sourceIndex = 0; sourceIndex < sourcesList.length; ++sourceIndex) {
        if (sourcesList[sourceIndex]['type'] === 'github') {
            var item = $('<li class="list-group-item">' + sourcesList[gitIndex]['data'] + '</li>');
            var deleteButton = $('<button class="badge btn btn-danger" data-gitid="' + sourcesList[sourceIndex]['data'] + '">Supprimer</button>');

            deleteButton.click(function () {
                removeGitId($(this).data('gitid'));
            });
            item.append(deleteButton);
            gitsListUl.append(item);

        }
    }
    $('#add-git').click(addGitId);
});

/**
 * Ajouter un utilisateur à la liste
 */
function addGitId() {
    var gitId = $('#git-id').val();
    if (gitId !== '') {
        var data = {action: 'source', params: 'add', data: {'type': 'gitId', 'id': gitId}};
        ajaxQuery(data);
    }
}

/**
 * Supprimer un utilisateur de la liste
 *
 * @param gitId Nom de l'utilisateur
 */
function removeGitId(gitId) {
    if (gitId !== '') {
        var data = {action: 'source', params: 'remove', data: {'type': 'gitId', 'id': gitId}};
        ajaxQuery(data);
    }
}

/**
 * Lancer une requête Ajax
 *
 * @param data Données de la requête
 */
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