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
        ['name' => 'NextDom Stable', 'enabled' => 1, 'type' => 'json', 'order' => 1, 'data' => 'https://raw.githubusercontent.com/NextDom/AlternativeMarket-Lists/master/results/nextdom-stable.json'],
        ['name' => 'NextDom draft', 'enabled' => 0, 'type' => 'json', 'order' => 2, 'data' => 'https://raw.githubusercontent.com/NextDom/AlternativeMarket-Lists/master/results/nextdom-draft.json'],
        ['name' => 'Mika-nt28', 'enabled' => 1, 'type' => 'json', 'order' => 3, 'data' => 'https://raw.githubusercontent.com/NextDom/AlternativeMarket-Lists/master/results/mika-nt28-stable.json'],
        ['name' => 'Mika-nt28 draft', 'enabled' => 0, 'type' => 'json', 'order' => 4, 'data' => 'https://raw.githubusercontent.com/NextDom/AlternativeMarket-Lists/master/results/mika-nt28-draft.json'],
        ['name' => 'KiwiHC16', 'enabled' => 1, 'type' => 'json', 'order' => 5, 'data' => 'https://raw.githubusercontent.com/NextDom/AlternativeMarket-Lists/master/results/KiwiHC16.json'],
        ['name' => 'Jeedom', 'enabled' => 1, 'type' => 'json', 'order' => 999, 'data' => 'https://raw.githubusercontent.com/NextDom/AlternativeMarket-Lists/master/results/jeedom.json']
    ];

    foreach ($markets as $market) {
        $dataStorage->storeJsonData('source_'.$market['name'], $market);
    }

    config::save('github::enable', 1);
    config::save('show-disclaimer', true, 'AlternativeMarketForJeedom');
}

function AlternativeMarketForJeedom_update()
{

}

/**
 * Fonction appelée à la désactivation du plugin
 */
function AlternativeMarketForJeedom_remove()
{
    // Suppression des sources de la base de données
    $dataStorage = new AmfjDataStorage('amfj');
    $dataStorage->dropDataTable();

    // Suppresion des données de configuration
    config::remove('show-disclaimer', 'AlternativeMarketForJeedom');
    config::remove('show-duplicates', 'AlternativeMarketForJeedom');
    config::remove('show-sources-filters', 'AlternativeMarketForJeedom');
    // Suppression du cache depuis le répertoire core/ajax
    exec('rm -fr ../../plugins/AlternativeMarketForJeedom/cache/*');
}

