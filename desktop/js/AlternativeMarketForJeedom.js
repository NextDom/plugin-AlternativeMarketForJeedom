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
            $('#market-div>div[data-category!=' + selectedCategory + ']').slideUp(400, function() {
                $('#market-div>div[data-category=' + selectedCategory + ']').slideDown();
            });
        }
        else {
            $('#market-div>div').slideDown();
        }
    });
}

/**
 * Rafraichit les éléments affichés
 */
function refresh() {
    $.post({
        url: 'plugins/AlternativeMarketForJeedom/core/ajax/AlternativeMarketForJeedom.ajax.php',
        data: {
            action: 'refresh',
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
    for (var index = 0; index < items.length; ++index) {
        container.append(getItemHtml(items[index]));
    }
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
    var result = '' +
        '<div class="col-xs-3 col-md-2" data-gituser="' + item['gitUser'] + '" data-category="' + item['category'] + '">' +
        '  <div class="thumbnail">' +
        '    <img src="' + img + '" />' +
        '    <div class="caption">' +
        '      <h4>' + title + '</h4>';
    if (item['installed']) {
        result += '<span>Installed</span>';
    }
    result += '' +
        '    </div>' +
        '  </div>' +
        '</div>';
    return result;
}