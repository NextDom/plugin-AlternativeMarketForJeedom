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
require_once 'AmfjDownloadManager.class.php';

/**
 * Analyseur des requêtes Ajax
 */
class AmfjAjaxParser
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
        switch ($action) {
            case 'source':
                $result = static::source($params, $data);
                break;
            case 'refresh':
                $result = static::refresh($params, $data);
                break;
            case 'get':
                $result = static::get($params, $data);
                break;
            default :
                $result = false;
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
        switch ($params) {
            case 'list':
                $result = static::refreshList($data, false);
                break;
            case 'list-force':
                $result = static::refreshList($data, true);
                break;
            default :
                $result = false;
        }
        return $result;
    }

    /**
     * Rafraichir la liste des dépôts.
     *
     * @param array $markets Liste des utilisateur GitHub.
     * @param bool $force Force la mise à jour.
     *
     * @return bool True si une mise à jour a été réalisée ou que la mise à jour n'est pas nécessaire.
     */
    private static function refreshList($sources, $force)
    {
        $result = false;
        if (is_array($sources)) {
            $result = true;
            foreach ($sources as $source) {
                $market = new AmfjMarket($source);
                if (!$market->refresh($force)) {
                    $error = AmfjGitManager::getLastErrorMessage();
                    // Vérification que c'est une erreur et pas un refresh avant l'heure
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
        switch ($params) {
            case 'list':
                if (is_array($data)) {
                    $result = [];
                    $idList = [];
                    $showDuplicates = config::byKey('duplicate', 'AlternativeMarketForJeedom');
                    foreach ($data as $source) {
                        $market = new AmfjMarket($source);
                        $items = $market->getItems();
                        foreach ($items as $item) {
                            if ($showDuplicates) {
                                array_push($result, $item->getDataInArray());
                            } else {
                                if (!\in_array($item->getId(), $idList)) {
                                    array_push($result, $item->getDataInArray());
                                    array_push($idList, $item->getId());
                                }
                            }
                        }
                    }
                    \usort($result, function ($item1, $item2) {
                        return $item1['name'] > $item2['name'];
                    });
                }
                break;
            case 'branches':
                if (is_array($data)) {
                    $downloaderManager = new AmfjDownloadManager();
                    $marketItem = AmfjMarketItem::createFromCache($data['sourceName'], $data['fullName']);
                    if ($marketItem->downloadBranchesInformations($downloaderManager)) {
                        $result = $marketItem->getBranchesList();
                        $marketItem->writeCache();
                    }
                }
                break;
            default :
                $result = false;
        }
        return $result;
    }

    /**
     * Gestion des utilisateurs GitHub
     *
     * @param string $params Type de modification
     * @param array $data Nom de l'utilisateur
     * @return bool True si une action a été effectuée
     */
    public static function source($params, array $data)
    {
        switch ($params) {
            case 'add':
                $source = new AlternativeMarketForJeedom();
                $source->setName($data['id']);
                $source->setLogicalId($data['id']);
                $source->setEqType_name('AlternativeMarketForJeedom');
                $source->setConfiguration('type', 'github');
                $source->setConfiguration('data', $data['id']);
                $source->save();
                $result = true;
                break;
            case 'remove':
                $source = eqLogic::byLogicalId($data['id'], 'AlternativeMarketForJeedom');
                $source->remove();
                $result = true;
                break;
            default :
                $result = false;
        }
        return $result;
    }

    /**
     * Obtenir le dernier message d'erreur
     *
     * @return string Message de l'erreur
     */
    public static function getErrorMsg()
    {
        return static::$errorMsg;
    }
}
