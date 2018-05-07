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

/**
 * Fonction appelée à l'activation du plugin
 */
function AlternativeMarketForJeedom_install()
{
    $dataStorage = new AmfjDataStorage('amfj');
    $dataStorage->createDataTable();

    $defaultMarket = new AlternativeMarketForJeedom();
    $defaultMarket->setName('NextDom Market');
    $defaultMarket->setLogicalId('NextDom Market');
    $defaultMarket->setEqType_name('AlternativeMarketForJeedom');
    $defaultMarket->setConfiguration('type', 'json');
    $defaultMarket->setConfiguration('data', 'https://raw.githubusercontent.com/NextDom/AlternativeMarket-Lists/master/result.json');
    $defaultMarket->save();

    config::save('url::enable', 1);
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

