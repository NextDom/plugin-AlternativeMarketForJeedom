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

require_once(__DIR__.'/../../core/class/AmfjDataStorage.class.php');
$dataStorage = new AmfjDataStorage('amfj');
$sourcesListRaw = $dataStorage->getAllByPrefix('source_');

$sourcesList = array();
$idCount = 1;
foreach ($sourcesListRaw as $sourceRaw) {
    $source = json_decode($sourceRaw['data'], true);
    $source['id'] = $idCount++;
    \array_push($sourcesList, $source);
}

\usort($sourcesList, array('AlternativeMarketForJeedom', 'cmpByOrder'));

sendVarToJs('sourcesList', $sourcesList);

?>
    <div id="div_pluginAlternativeMarketForJeedomAlert"></div>
    <div id="config-modal" class="config-form">
        <div class="container">
            <h3>{{Gestionnaire des sources}}</h3>
        </div>
        <div class="container">
            <ul id="sources-list" class="list-group">
                <?php foreach ($sourcesList as $source) {
                    if ($source['type'] !== 'github') {
                        echo '<li class="list-group-item"><span class="pull-right"><input data-name="'.$source['name'].'" id="check-source-'.$source['id'].'" type="checkbox"';
                        if ($source['enabled'] == 1) {
                            echo ' checked="checked"';
                        }
                        echo '><label for="check-source-'.$source['id'].'"></label></span><span>'.$source['name'].'</span></li>';
                    }
                }
                ?>
                <a class="btn btn-success btn-sm pull-right" id="sources-list-save"><i class="fa fa-check-circle icon-white"></i> {{Sauvegarder}}</a>
            </ul>
        </div>
        <div class="container">
            <h3>{{Gestionnaire des sources personnalisées}}</h3>
        </div>
        <div class="container">
            <label>{{Ajouter : }}</label>
            <div class="input-group">
                <input id="git-id" type="text" class="form-control" placeholder="{{Identifiant GitHub...}}"/>
                <span class="input-group-btn">
                    <button id="add-git" class="btn btn-nextdom" type="button"><i class="fa fa-plus"></i></button>
                </span>
            </div>
        </div>
        <div id="github-list-container" class="container">
            <label>{{Liste des dépots configurés : }}</label>
            <ul id="gitid-list" class="list-group">
            </ul>
        </div>
    </div>
<?php
    include_file('desktop', 'config.AlternativeMarketForJeedom', 'js', 'AlternativeMarketForJeedom');
    include_file('desktop', 'AlternativeMarketForJeedom', 'css', 'AlternativeMarketForJeedom');
