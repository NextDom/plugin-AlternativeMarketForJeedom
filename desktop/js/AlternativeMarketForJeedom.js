var currentPlugin = null;

// Point d'entrée du script
$(document).ready(function () {
    refresh();
    initFilters();
});

/**
 * Initialise les évènements des filtres
 */
function initFilters() {
    $('#market-filter-src button').click(function () {
        var github = $(this).data('github');
        if ($(this).hasClass('btn-primary')) {
            $('#market-div>div[data-gituser=' + github + ']').slideUp();
            $(this).removeClass('btn-primary');
            $(this).addClass('btn-secondary');
        }
        else {
            $('#market-div>div[data-gituser=' + github + ']').slideDown();
            $(this).removeClass('btn-secondary');
            $(this).addClass('btn-primary');
        }
    });
    $('#market-filter-category').change(function () {
        var selectedCategory = $("#market-filter-category option:selected").val();
        if (selectedCategory != 'all') {
            $('#market-div>div[data-category!=' + selectedCategory + ']').slideUp(400, function () {
                $('#market-div>div[data-category=' + selectedCategory + ']').slideDown();
            });
        }
        else {
            $('#market-div>div').slideDown();
        }
    });
    $('#refresh-markets').click(function () {
        refresh(true);
    });
}

/**
 * Rafraichit les éléments affichés
 */
function refresh(force) {
    var params = 'list';
    if (typeof force !== undefined && force === true) {
        params = 'list-force';
    }
    $.post({
        url: 'plugins/AlternativeMarketForJeedom/core/ajax/AlternativeMarketForJeedom.ajax.php',
        data: {
            action: 'refresh',
            params: params,
            data: gitLists
        },
        dataType: 'json',
        success: function (data, status) {
            // Test si l'appel a échoué
            if (data.state !== 'ok' || status !== 'success') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
            }
            else {
                refreshItems();
            }
        },
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        }
    });
}

/**
 * Rafraichit un elément
 */
function refreshItems() {
    $.post({
        url: 'plugins/AlternativeMarketForJeedom/core/ajax/AlternativeMarketForJeedom.ajax.php',
        data: {
            action: 'get',
            params: 'list',
            data: gitLists
        },
        dataType: 'json',
        success: function (data, status) {
            // Test si l'appel a échoué
            if (data.state !== 'ok' || status !== 'success') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
            }
            else {
                showItems(data['result']);
            }
        },
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        }
    });
}

/**
 * Affiche les élements
 *
 * @param items Liste des éléments
 */
function showItems(items) {
    var container = $('#market-div');
    container.empty();
    for (var index = 0; index < items.length; ++index) {
        container.append(getItemHtml(items[index]));
    }
    $('.thumbnail').click(function () {
        showPluginModal($(this).data('plugin'));
    });

}

/**
 * Obtenir le code HTML d'un élément
 *
 * @param item Informations de l'élément à créer
 *
 * @returns {string} Code HTML
 */
function getItemHtml(item) {
    var img = 'plugins/AlternativeMarketForJeedom/cache/' + item['fullName'].replace(/\//g, '_') + '.png';
    var title = item['name'];
    title = title.replace('plugin-', '');
    title = title.replace(/([a-z])([A-Z][a-z])/g, '\$1 \$2');
    var pluginData = JSON.stringify(item);
    pluginData = pluginData.replace(/"/g, '&quot;');
    var result = '' +
        '<div class="col-xs-3 col-md-2" data-gituser="' + item['gitUser'] + '" data-category="' + item['category'] + '">' +
        '  <div class="thumbnail" data-plugin="'+pluginData+'">' +
        '    <img src="' + img + '" />' +
        '    <div class="caption">' +
        '      <h4>' + title + '</h4>';
    if (item['installed']) {
        result += '<span>Installed</span>';
    }
    /*
    else {
        result += '<button data-plugin="'+pluginData+'" class="btn btn-default show-plugin-modal">Installer</button>';
    }
    */
    result += '' +
        '    </div>' +
        '  </div>' +
        '</div>';
    return result;
}

/**
 * Initialise les fenêtres modales
 */
function showPluginModal(pluginData) {
    $('#md_modal').dialog({title: pluginData['name']});
    $('#md_modal').load('index.php?v=d&plugin=AlternativeMarketForJeedom&modal=plugin.AlternativeMarketForJeedom').dialog('open');
    currentPlugin = pluginData;
}
