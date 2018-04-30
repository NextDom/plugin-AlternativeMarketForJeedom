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


require_once 'AmfjMarket.class.php';

class AjaxParser
{
    /**
     * Point d'entrée des requêtes Ajax
     *
     * @param $action Action de la requête
     * @param $params Paramètres de la requête
     * @param $data Données de la requête
     *
     * @return array|bool Résultat
     */
    public static function parse($action, $params, $data)
    {
        $result = false;
        switch ($action) {
            case 'add':
                $result = static::add($params, $data);
                break;
            case 'refresh':
                $result = static::refresh($params, $data);
                break;
            case 'get':
                $result = static::get($params, $data);
                break;
        }
        return $result;
    }

    /**
     * Actions de refraichissement
     *
     * @param string $params Type de rafraichissement
     * @param mixed $data Données en fonction du paramètre
     *
     * @return bool True si l'action a réussie
     */
    public static function refresh($params, $data)
    {
        $result = false;
        switch ($params) {
            case 'list':
                $result = static::refreshList($data, false);
                break;
            case 'list-force':
                $result = static::refreshList($data, true);
                break;
        }
        return $result;
    }

    private static function refreshList($markets, $force)
    {
        $result = false;
        if (is_array($markets)) {
            foreach ($markets as $git) {
                $market = new Market($git);
                $market->refresh($force);
            }
            $result = true;
        }

        return $result;
    }

    /**
     * Obtenir une information
     *
     * @param string $params Identifiant de l'information
     * @param mixed $data Données en fonction du paramètre
     *
     * @return array|bool Information demandée
     */
    public static function get($params, $data)
    {
        $result = false;
        switch ($params) {
            case 'list':
                if (is_array($data)) {
                    $result = array();
                    foreach ($data as $git) {
                        $market = new Market($git);
                        $items = $market->getItems();
                        foreach ($items as $item) {
                            array_push($result, $item->getDataInArray());
                        }
                    }
                    \usort($result, function ($item1, $item2) {
                        return $item1['name'] > $item2['name'];
                    });
                }
                break;
        }
        return $result;
    }

    public static function add($params, $data)
    {
        $result = false;
        switch ($params) {
            case 'gitUser':
                if ($data != '') {
                    $pluginExtra = new AlternativeMarketForJeedom();
                    $pluginExtra->setName($data);
                    $pluginExtra->setEqType_name('AlternativeMarketForJeedom');
                    $pluginExtra->setConfiguration('github', $data);
                    $pluginExtra->save();
                    $result = true;
                }
                break;
        }
        return $result;
    }
}