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

include_file('desktop', 'AlternativeMarketForJeedomConfig', 'css', 'AlternativeMarketForJeedom');

?>
    <div class="alert alert-danger" role="alert">
        {{L'utilisation de ce plugin et des plugins qu'il installe n'est pas autorisé par la société Jeedom SAS. Si vous avec fait l'acquisition d'un Service Pack, vous ne pourrez demander une assistance en cas de problèmes.}}
    </div>
    <div class="panel panel-info" style="height: 100%;">
        <div class="panel-heading" role="tab">
            <h4 class="panel-title">
                Présentation
                <a class="btn btn-primary btn-xs pull-right" id="btn-nextdom-info" target="_blank" href="https://nextdom.github.io/"><i class="fa fa-info-circle"></i> Pour en savoir plus</a>
            </h4>
        </div>
        <div class="panel-body">
            Ce plugin mis à disposition par l'équipe NextDom vous permet d'accéder à une multitude de plugins développés pour la solution Jeedom. 
</br>
En plus du dépot officiel NextDom contenant des plugins développés et validés par l'équipe, ce plugin vous permet d'ajouter n'importe quelle source en provenance de GitHub.
        </div>
    </div>
    <form id="amfj-config" class="config-form form-horizontal">
        <div class="form-group">
            <label class="col-sm-4 control-label">{{Afficher les doublons}} <sup><i
                            class="fa fa-question-circle tooltips"
                            title="{{Autorise l'affichage multiple des plugins en cas de présence dans plusieurs dépots}}"></i></sup> : </label>
            <div id="div_show-duplicates" class="col-sm-2 tooltips">
                <input type="checkbox" id="show-duplicates" class="configKey" data-l1key="show-duplicates"
                       placeholder="{{Afficher les doublons}}"/>
                <label for="show-duplicates"> </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-4 control-label">{{Filtres des sources}} <sup><i
                            class="fa fa-question-circle tooltips"
                            title="{{Afficher les filtres des sources}}"></i></sup> : </label>
            <div id="div_show-sources-filters" class="col-sm-2 tooltips">
                <input type="checkbox" id="show-sources-filters" class="configKey" data-l1key="show-sources-filters"
                       placeholder="{{Afficher les filtres des sources}}"/>
                <label for="show-sources-filters"> </label>
            </div>
        </div>
        <div class="form-group">
            <label for="sources-manager" class="col-sm-4 control-label">{{Gestionnaire de sources}} : </label>
            <div class="col-sm-2">
                <button id="sources-manager" class="btn btn-primary"><i class="fa fa-th-large"></i> {{Gérer}}</button>
            </div>
        </div>
    </form>

<?php
include_file('desktop', 'AlternativeMarketForJeedomConfiguration', 'js', 'AlternativeMarketForJeedom');
include_file('desktop', 'AlternativeMarketForJeedomConfiguration', 'css', 'AlternativeMarketForJeedom');
