$(document).ready(function () {
    initModal();
    $('#install-plugin').click(function () {
        installPlugin(currentPlugin['defaultBranch']);
    });
});

/**
 * Initialise la fenêtre modale du plugin
 */
function initModal() {
    $('#plugin-icon').attr('src', currentPlugin['iconPath']);
    $('#default-branch-information').text('Branche '+currentPlugin['defaultBranch']);
    var branchesList = currentPlugin['branchesList'];
    if (branchesList.length > 1) {
        var ulList = $('#install-plugin-advanced .dropdown-menu');
        for (var branchIndex = 0; branchIndex < branchesList.length; ++branchIndex) {
            var branchName = branchesList[branchIndex];
            if (branchName != currentPlugin['defaultBranch']) {
                var liItem = $('<li data-branch="'+branchName+'"><a href="#">Installer la branche ' + branchesList[branchIndex] + '</a></li>');
                liItem.click(function () {
                    installPlugin($(this).data('branch'));
                });
                ulList.append(liItem);
            }
        }
    }
}

/**
 * Lance l'installation du plugin
 */
function installPlugin(branch) {
    console.log(branch);
    $.post({
        url: 'core/ajax/update.ajax.php',
        data: {
            action: 'save',
            update: '{"logicalId":"' + currentPlugin['id'] + '","configuration":{"url":"' + currentPlugin['url'] + '/archive/' + branch + '.zip"},"source":"url"}'
        },
        dataType: 'json',
        success: function (data, status) {
            window.location.replace('/index.php?v=d&p=plugin');
        },
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        }
    });
}