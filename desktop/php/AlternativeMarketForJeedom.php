<?php

require_once(dirname(__FILE__) . '/../../core/class/Market.class.php');

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
    <div id="market-filter-src" class="btn-group" role="group">
    <?php foreach ($eqLogics as $eqLogic) {
        $gitHub = $eqLogic->getConfiguration()['github'];
        echo '<button type="button" class="btn btn-primary" data-github="' . $gitHub . '">' . $gitHub . '</button >';
    }
    ?>
</div>
<div id="market-div" class="row">

</div>