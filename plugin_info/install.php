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
require_once(dirname(__FILE__) . '/../core/class/AmfjDataStorage.class.php');
require_once(dirname(__FILE__) . '/../core/class/AlternativeMarketForJeedom.class.php');

/**
 * Fonction appelée à l'activation du plugin
 */
function AlternativeMarketForJeedom_install()
{
    $dataStorage = new AmfjDataStorage('amfj');
    $dataStorage->createDataTable();

    $markets = [
        ['name' => 'NextDom Stable', 'url' => 'https://raw.githubusercontent.com/NextDom/AlternativeMarket-Lists/master/stable-result.json'],
        ['name' => 'NextDom Unstable', 'url' => 'https://raw.githubusercontent.com/NextDom/AlternativeMarket-Lists/master/unstable-result.json'],
        ['name' => 'Jeedom', 'url' => 'https://raw.githubusercontent.com/NextDom/AlternativeMarket-Lists/master/jeedom-result.json'],
        ['name' => 'Autres', 'url' => 'https://raw.githubusercontent.com/NextDom/AlternativeMarket-Lists/master/others-result.json']
    ];

    foreach ($markets as $market) {
        $defaultMarket = new AlternativeMarketForJeedom();
        $defaultMarket->setName($market['name']);
        $defaultMarket->setLogicalId($market['name']);
        $defaultMarket->setEqType_name('AlternativeMarketForJeedom');
        $defaultMarket->setConfiguration('type', 'json');
        $defaultMarket->setConfiguration('data', $market['url']);
        $defaultMarket->save();
    }

    config::save('github::enable', 1);
}

function AlternativeMarketForJeedom_update()
{

}

/**
 * Fonction appelée à la désactivation du plugin
 */
function AlternativeMarketForJeedom_remove()
{
    $dataStorage = new AmfjDataStorage('amfj');
    $dataStorage->dropDataTable();
    foreach (eqLogic::byType('AlternativeMarketForJeedom') as $eqLogic) {
        $eqLogic->remove();
    }
}

