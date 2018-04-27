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

function AlternativeMarketForJeedom_install() {
    $dataStorage = new AmfjDataStorage('amfj');
    $dataStorage->createDataTable();

    $pluginExtra = new AlternativeMarketForJeedom();
    $pluginExtra->setName('plugins-extra');
    $pluginExtra->setEqType_name('AlternativeMarketForJeedom');
    $pluginExtra->setConfiguration('github', 'Jeedom-Plugins-Extra');
    $pluginExtra->save();

    $jeedom = new AlternativeMarketForJeedom();
    $jeedom->setName('jeedom');
    $jeedom->setEqType_name('AlternativeMarketForJeedom');
    $jeedom->setConfiguration('github', 'jeedom');
    $jeedom->save();
}

function AlternativeMarketForJeedom_update() {
    
}


function AlternativeMarketForJeedom_remove() {
    $dataStorage = new AmfjDataStorage('amfj');
    $dataStorage->dropDataTable();
    foreach (eqLogic::byType('AlternativeMarketForJeedom') as $eqLogic) {
        $eqLogic->remove();
    }
}

