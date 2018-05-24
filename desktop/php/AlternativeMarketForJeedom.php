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

require_once __DIR__ . '/../../core/class/AmfjMarket.class.php';

include_file('core', 'authentification', 'php');

if (!isConnect('admin')) {
    throw new \Exception('{{401 - Accès non autorisé}}');
}

$plugin = plugin::byId('AlternativeMarketForJeedom');
$eqLogics = eqLogic::byType($plugin->getId(), true);
\usort($eqLogics, array('AlternativeMarketForJeedom', 'cmpByOrder'));

$sourcesList = array();
foreach ($eqLogics as $eqLogic) {
    $source = [];
    $source['id'] = $eqLogic->getId();
    $source['name'] = $eqLogic->getName();
    $source['type'] = $eqLogic->getConfiguration()['type'];
    $source['data'] = $eqLogic->getConfiguration()['data'];
    array_push($sourcesList, $source);
}
sendVarToJs('sourcesList', $sourcesList);

// Affichage d'un message à un utilisateur
if (isset($_GET['message'])) {
    $messages = [
        __('La mise à jour du plugin a été effecutée.', __FILE__),
        __('Le plugin a bien été supprimé', __FILE__)
    ];

    $messageIndex = intval($_GET['message']);
    if ($messageIndex < count($messages)) {
        message::add('AlternativeMarketForJeedom', $messages[$messageIndex]);
    }
}

include_file('desktop', 'AlternativeMarketForJeedom', 'js', 'AlternativeMarketForJeedom');
include_file('desktop', 'AlternativeMarketForJeedom', 'css', 'AlternativeMarketForJeedom');
include_file('core', 'plugin.template', 'js');

?>
<div class="row">
    <div id="logo-nextdom" class="col-sm-1">
        <a href="https://nextdom.github.io/"><img
                    src="plugins/AlternativeMarketForJeedom/resources/NextDomSquareRound.png" alt="Site NextDom"/></a>
    </div>
    <div class="col-sm-11">
        <?php if (count($eqLogics) > 1 && config::byKey('show-sources-filters', 'AlternativeMarketForJeedom')) : ?>
            <div class="market-filters row">
                <div id="market-filter-src" class="btn-group col-sm-12">
                    <?php
                    foreach ($eqLogics as $eqLogic) {
                        $name = $eqLogic->getName();
                        echo '<button type="button" class="btn btn-primary" data-source="' . $name . '">' . $name . '</button >';
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="market-filters row">
            <div class="form-group btn-group col-sm-12 col-md-6 col-lg-3">
                <button id="market-filter-installed" class="btn btn-primary">{{Installés}}</button>
                <button id="market-filter-notinstalled" class="btn btn-primary">{{Non installés}}</button>
            </div>
            <div class="form-group col-sm-12 col-md-6 col-lg-3">
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-search"></i></div>
                    <input type="text" class="form-control" id="market-search" placeholder="{{Rechercher}}"/>
                </div>
            </div>
            <div class="form-group col-sm-12 col-md-6 col-lg-2">
                <select class="form-control" id="market-filter-category">
                    <option value="all">{{Toutes les Catégories}}</option>
                    <option value="security">{{Sécurité}}</option>
                    <option value="automation protocol">{{Protocole domotique}}</option>
                    <option value="programming">{{Programmation}}</option>
                    <option value="organization">{{Organisation}}</option>
                    <option value="weather">{{Météo}}</option>
                    <option value="communication">{{Communication}}</option>
                    <option value="devicecommunication">{{Objets communicants}}</option>
                    <option value="multimedia">{{Multimédia}}</option>
                    <option value="wellness">{{Bien-être}}</option>
                    <option value="monitoring">{{Monitoring}}</option>
                    <option value="health">{{Santé}}</option>
                    <option value="nature">{{Nature}}</option>
                    <option value="automatisation">{{Automatisme}}</option>
                    <option value="energy">{{Energie}}</option>
                </select>
            </div>
            <div class="form-group col-sm-12 col-md-6 col-lg-4">
                <div id="admin-buttons" class="btn-group">
                    <a href="index.php?v=d&p=plugin&id=AlternativeMarketForJeedom" class="btn btn-primary"><i
                                class="fa fa-wrench"></i> {{Configurer}}</a>
                    <button id="mass-update" class="btn btn-primary">
                        <i class="fa fa-download"></i> {{Mise(s) à jour}}
                        <span class="badge">0</span>
                    </button>
                    <button id="refresh-markets" class="btn btn-primary">
                        <i class="fa fa-refresh"></i> {{Rafraîchir}}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="market-div" class="row">

</div>
<div class="modal fade" id="market-modal" tabindex="-1" role="dialog" aria-labelledby="market-modal-title"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="market-modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="market-modal-content">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{Fermer}}</button>
                <button type="button" id="market-modal-valid" class="btn btn-primary"></button>
            </div>
        </div>
    </div>
</div>