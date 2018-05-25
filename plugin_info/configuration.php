<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

include_file('core', 'authentification', 'php');

if (!isConnect()) {
    // @codeCoverageIgnoreStart
    include_file('desktop', '404', 'php');
    die();
    // @codeCoverageIgnoreEnd
}

$showDisclaimer = config::byKey('show-disclaimer', 'AlternativeMarketForJeedom');
sendVarToJs('showDisclaimer', $showDisclaimer);
if ($showDisclaimer) {
    config::save('show-disclaimer', false, 'AlternativeMarketForJeedom');
}

include_file('desktop', 'AlternativeMarketForJeedomConfig', 'css', 'AlternativeMarketForJeedom');
?>
    <form id="amfj-config" class="config-form form-horizontal">
        <div class="form-group">
            <label class="col-sm-3 control-label">{{Informations}} : </label>
            <div class="col-sm-8">
                <button id="show-disclaimer-modal" class="btn btn-danger"><i class="fa fa-info-circle"></i> {{Plugin / Disclaimer}}</button>
                <button id="show-nextdom-modal" class="btn btn-success"><i class="fa fa-users"></i> {{NextDom}}</button>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{Afficher les doublons}} <sup><i
                            class="fa fa-question-circle tooltips"
                            title="{{Autorise l'affichage multiple des plugins en cas de présence dans plusieurs dépots}}"></i></sup> : </label>
            <div id="div_show-duplicates" class="col-sm-2 tooltips">
                <input type="checkbox" id="show-duplicates" class="configKey" data-l1key="show-duplicates"
                       placeholder="{{Afficher les doublons}}"/>
                <label for="show-duplicates"> </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{Filtres des sources}} <sup><i
                            class="fa fa-question-circle tooltips"
                            title="{{Afficher les filtres des sources}}"></i></sup> : </label>
            <div id="div_show-sources-filters" class="col-sm-2 tooltips">
                <input type="checkbox" id="show-sources-filters" class="configKey" data-l1key="show-sources-filters"
                       placeholder="{{Afficher les filtres des sources}}"/>
                <label for="show-sources-filters"> </label>
            </div>
        </div>
        <div class="form-group">
            <label for="sources-manager" class="col-sm-3 control-label">{{Gestionnaire de sources}} : </label>
            <div class="col-sm-2">
                <button id="sources-manager" class="btn btn-primary"><i class="fa fa-th-large"></i> {{Gérer}}</button>
            </div>
        </div>
    </form>

<?php
include_file('desktop', 'AlternativeMarketForJeedomConfiguration', 'js', 'AlternativeMarketForJeedom');
