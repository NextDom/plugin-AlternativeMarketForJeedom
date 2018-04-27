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
    <div id="market-filter-src" class="btn-group">
    <?php foreach ($eqLogics as $eqLogic) {
        $gitHub = $eqLogic->getConfiguration()['github'];
        echo '<button type="button" class="btn btn-primary" data-github="' . $gitHub . '">' . $gitHub . '</button >';
    }
    ?>
    </div>
    <div class="form-group">
        <select class="form-control" id="market-filter-category">
            <option value="all">{{Toutes les Catégories}}</option>
            <option value="security">{{Sécurité}}</option>
            <option value="automation protocol">automation protocol</option>
            <option value="programming">programming</option>
            <option value="organization">organization</option>
            <option value="weather">{{Météo}}</option>
            <option value="communication">{{Communication}}</option>
            <option value="devicecommunication">devicecommunication</option>
            <option value="multimedia">multimedia</option>
            <option value="wellness">wellness</option>
            <option value="monitoring">monitoring</option>
            <option value="health">health</option>
            <option value="nature">nature</option>
            <option value="automatisation">automatisation</option>
            <option value="energy">energy</option>
        </select>
    </div>
</div>
<div id="market-div" class="row">

</div>
