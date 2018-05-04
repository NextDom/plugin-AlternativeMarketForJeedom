// Point d'entrée du script
$(document).ready(function () {
    var gitsListUl = $('#config-modal ul');
    for (var gitIndex = 0; gitIndex < gitsList.length; ++gitIndex) {
        var item = $('<li class="list-group-item">' + gitsList[gitIndex] + '</li>');
        var deleteButton = $('<button class="badge btn btn-danger" data-gitid="' + gitsList[gitIndex] + '">Supprimer</button>');

        deleteButton.click(function() {
            removeGitId($(this).data('gitid'));
        });
        item.append(deleteButton);
        gitsListUl.append(item);
    }
    $('#add-git').click(addGitId);
});

/**
 * Ajouter un utilisateur à la liste
 */
function addGitId() {
    var gitId = $('#git-id').val();
    if (gitId != '') {
            var data = {action: 'gitId', params: 'add', data: gitId};
            ajaxQuery(data);
    }
}

/**
 * Supprimer un utilisateur de la liste
 *
 * @param string gitId Nom de l'utilisateur
 */
function removeGitId(gitId) {
    if (gitId != '') {
        var data = {action: 'gitId', params: 'remove', data: gitId};
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