var currentPlugin = null;
var filterHiddenSrc = [];
var filterCategory = '';

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
            filterHiddenSrc.push(github);
            $(this).removeClass('btn-primary');
            $(this).addClass('btn-secondary');
        }
        else {
            var itemIndex = -1;
            for (var index = 0; index < filterHiddenSrc.length; ++index) {
                if (filterHiddenSrc[index] == github) {
                    itemIndex = index;
                }
            }
            if (itemIndex > -1) {
                filterHiddenSrc.splice(itemIndex, 1);
            }
            $(this).removeClass('btn-secondary');
            $(this).addClass('btn-primary');
        }
        updateFilteredList();
    });
    $('#market-filter-category').change(function () {
        var selectedCategory = $("#market-filter-category option:selected").val();
        if (selectedCategory != 'all') {
            filterCategory = selectedCategory;
        }
        else {
            filterCategory = '';
        }
        updateFilteredList();
    });
    $('#refresh-markets').click(function () {
        refresh(true);
    });
}

/**
 * Met à jour la liste des éléments affichés
 */
function updateFilteredList() {
    $('#market-div>div').each(function () {
        var hide = false;
        var dataGitUser = $(this).data('gituser');
        var dataCategory = $(this).data('category');
        if (filterHiddenSrc.indexOf(dataGitUser) !== -1) {
            hide = true;
        }
        if (filterCategory != dataCategory) {
            hide = true;
        }
        if (hide) {
            $(this).slideUp();
        }
        else {
            $(this).slideDown();
        }
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
    $('.media').click(function () {
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
        '<div class="media-container col-xs-6 col-md-4" data-gituser="' + item['gitUser'] + '" data-category="' + item['category'] + '">' +
        '<div class="media" data-plugin="' + pluginData + '">' +
        '<div class="media-left media-middle">' +
        '<img src="' + img + '"/>' +
        '</div>' +
        '<div class="media-body">' +
        '<h4 class="media-heading">' + title + '</h4>' +
        '<p>' + item['description'] + '</p>';
    if (item['installed']) {
        result += '<p>Déjà installé</p>';
    }
    result += '</div>' +
        '</div>' +
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
