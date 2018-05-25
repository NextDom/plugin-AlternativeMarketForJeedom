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

include_file('core', 'authentification', 'php');

if (!isConnect('admin')) {
    throw new \Exception('{{401 - Accès non autorisé}}');
}
?>
    <div id="div_pluginAlternativeMarketForJeedomAlert"></div>
    <div id="disclaimer-modal">
        <div class="alert alert-danger" role="alert">
            <div class="row">
            <span>
                <i class="fa fa-5x fa-exclamation-triangle"></i>
            </span>
                <span>
                <p>{{L'utilisation de ce plugin et des plugins qu'il installe n'a aucun lien avec la société Jeedom SAS. Si vous avez fait l'acquisition d'un Service Pack, la société Jeedom SAS pourra vous refuser le support.}}</p>
            </span>
            </div>
        </div>
        <div class="panel panel-info" style="height: 100%;">
            <div class="panel-heading" role="tab">
                <h4 class="panel-title">
                    {{Présentation}}
                </h4>
            </div>
            <div class="panel-body">
                <p>{{<b>Alternative Market For Jeedom</b> vous permettra d'installer des plugins directement depuis les sources des développeurs.}}</p>
                <p>{{Ceci vous donnera la possibilité de : }}</p>
                <ul>
                    <li>{{Accéder à des versions en développement de plugins,}}</li>
                    <li>{{Installer des plugins de développeurs gardant leur indépendance,}}</li>
                    <li>{{Installer des plugins ne respectant pas la charte du Market Jeedom.}}</li>
                </ul>
            </div>
        </div>
        <div id="choices-row" class="row">
            <div class="col-md-6">
                <button id="disclaimer-close" class="btn btn-lg btn-primary"><i class="fa fa-check"></i> {{Continuer}}</button>
            </div>
            <div class="col-md-6">
                <button id="delete-plugin" class="btn btn-lg btn-danger"><i class="fa fa-close"></i> {{Supprimer ce plugin}}</button>
            </div>
        </div>
    </div>
<?php
include_file('desktop', 'disclaimer.AlternativeMarketForJeedom', 'js', 'AlternativeMarketForJeedom');
