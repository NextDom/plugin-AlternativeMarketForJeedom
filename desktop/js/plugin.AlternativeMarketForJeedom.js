$(document).ready(function() {
    $('#install-plugin').click(function() {
        installPlugin();
    });
});

/**
 * Lance l'installation du plugin
 */
function installPlugin() {
    $.post({
        url: 'core/ajax/update.ajax.php',
        data: {
            action: 'save',
            update: '{"logicalId":"'+currentPlugin['id']+'","configuration":{"url":"'+currentPlugin['url']+'/archive/master.zip"},"source":"url"}'
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