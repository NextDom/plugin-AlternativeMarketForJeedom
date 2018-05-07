// Point d'entrée du script
$(document).ready(function () {
    var shortcuts = ['NextDom', 'jeedom'];

    var gitsListUl = $('#config-modal ul');
    for (var sourceIndex = 0; sourceIndex < sourcesList.length; ++sourceIndex) {
        if (sourcesList[sourceIndex]['type'] === 'github') {
            var sourceData = sourcesList[sourceIndex]['data'];
            var item = getListItem(sourceData);
            var indexOfItem = shortcuts.indexOf(sourceData);
            if (indexOfItem !== -1) {
                shortcuts.splice(indexOfItem, 1);
            }
            gitsListUl.append(item);
        }
    }
    showShortcuts(shortcuts);
    $('#add-git').click(addGitId);
});

/**
 * Obtenir le code d'un élément de la liste
 *
 * @param itemData Données de l'élément
 *
 * @returns {jQuery|HTMLElement} Code de l'élément
 */
function getListItem(itemData) {
    var item = $('<li class="list-group-item">' + itemData + '</li>');
    var deleteButton = $('<button class="badge btn btn-danger" data-gitid="' + itemData + '">Supprimer</button>');

    deleteButton.click(function () {
        removeGitId($(this).data('gitid'));
    });
    item.append(deleteButton);
    return item;
}

function showShortcuts(shortcuts) {
    if (shortcuts.length > 0) {
        for (var shortcutIndex = 0; shortcutIndex < shortcuts.length; ++shortcutIndex) {
            var item = $('<button class="btn btn-primary">'+ shortcuts[shortcutIndex]+'</button>');
            item.click(function() {
                addGitId($(this).text());
                $(this).remove();
            });
            $('#shortcuts').append(item);
        }
    }
    else {
        $('#shortcuts').hide();
    }
}

/**
 * Ajouter un utilisateur à la liste
 */
function addGitId(gitId) {
    if (typeof gitId === 'undefined') {
        gitId = $('#git-id').val();
    }
    if (gitId !== '') {
        var data = {action: 'source', params: 'add', data: {'type': 'gitId', 'id': gitId}};
        ajaxQuery(data, function() {
            var gitsListUl = $('#config-modal ul');
            gitsListUl.append(getListItem(gitId));
        });
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
        ajaxQuery(data, function() {
            var gitsListUl = $('#config-modal ul');
            $('#config-modal ul li').each(function() {
                if ($(this).text().indexOf(gitId) !== -1) {
                    $(this).remove();
                }
            });
        });
    }
}

/**
 * Lancer une requête Ajax
 *
 * @param data Données de la requête
 */
function ajaxQuery(data, callbackFunc) {
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
                if (typeof callbackFunc !== "undefined") {
                    callbackFunc();
                }
            }
        },
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        }
    });
}