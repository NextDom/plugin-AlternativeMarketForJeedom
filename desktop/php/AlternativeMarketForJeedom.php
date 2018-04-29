<?php

require_once(dirname(__FILE__) . '/../../core/class/AmfjMarket.class.php');

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

$plugin = plugin::byId('AlternativeMarketForJeedom');
$eqLogics = eqLogic::byType($plugin->getId());

$gitLists = array();
foreach ($eqLogics as $eqLogic) {
    array_push($gitLists, $eqLogic->getConfiguration()['github']);
}
sendVarToJs('gitLists', $gitLists);

include_file('desktop', 'AlternativeMarketForJeedom', 'js', 'AlternativeMarketForJeedom');
include_file('desktop', 'AlternativeMarketForJeedom', 'css', 'AlternativeMarketForJeedom');
include_file('core', 'plugin.template', 'js');

?>
<div id="market-filters" class="row">
    <div id="market-filter-src" class="btn-group col-sm-6">
    <?php foreach ($eqLogics as $eqLogic) {
        $gitHub = $eqLogic->getConfiguration()['github'];
        echo '<button type="button" class="btn btn-primary" data-github="' . $gitHub . '">' . $gitHub . '</button >';
    }
    ?>
    </div>
    <div class="form-group col-sm-5">
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
    <div class="col-sm-1">
        <a class="btn btn-default">
            <i id="refresh-markets" class="fa fa-refresh"></i>
        </a>
    </div>
</div>
<div id="market-div" class="row">

</div>
