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
     * @var string Message d'erreur
     */
    private static $errorMsg;

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
            case 'gitUser':
                $result = static::gitUser($params, $data);
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
            $result = true;
            foreach ($markets as $git) {
                $market = new Market($git);
                if (!$market->refresh($force)) {
                    $error = GitManager::getLastErrorMessage();
                    // Véririfaction que c'est une erreur et pas un refresh avant l'heure
                    if ($error !== false) {
                        static::$errorMsg = $error;
                        $result = false;
                    }
                }
            }
        } else {
            static::$errorMsg = 'Aucun utilisateur GitHub défini';
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

    public static function gitUser($params, $data)
    {
        $result = false;
        switch ($params) {
            case 'add':
                if ($data != '') {
                    $gitUser = new AlternativeMarketForJeedom();
                    $gitUser->setName($data);
                    $gitUser->setLogicalId($data);
                    $gitUser->setEqType_name('AlternativeMarketForJeedom');
                    $gitUser->setConfiguration('github', $data);
                    $gitUser->save();
                    $result = true;
                }
                break;
            case 'remove':
                if ($data != '') {
                    $gitUser = eqLogic::byLogicalId($data, 'AlternativeMarketForJeedom');
                    $gitUser->remove();
                    $result = true;
                }
                break;
        }
        return $result;
    }

    public static function getErrorMsg()
    {
        return static::$errorMsg;
    }
}