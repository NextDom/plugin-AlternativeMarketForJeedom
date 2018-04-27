<?php
/**
 * Created by PhpStorm.
 * User: dangin
 * Date: 22/04/2018
 * Time: 13:08
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
                if (is_array($data)) {
                    foreach ($data as $git) {
                        $market = new Market($git);
                        $market->refresh();
                    }
                    $result = true;
                }
                break;
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
                    \usort($result, function($item1, $item2) {
                        return $item1['name'] > $item2['name'];
                    });
                }
                break;
        }
        return $result;
    }
}