<?php
/**
 * Created by PhpStorm.
 * User: dangin
 * Date: 21/04/2018
 * Time: 01:07
 */

require_once('GitManager.class.php');
require_once('DownloadManager.class.php');
require_once('MarketItem.class.php');

class Market
{
    /**
     * @var DownloadManager Gestionnaire de téléchargement
     */
    private $downloadManager;

    /**
     * @var Utilisateur Git des depôts
     */
    private $gitUser;

    /**
     * Constructeur initialisant le gestionnaire de téléchargement
     *
     * @param $gitUser Utilisateur Git des dépôts
     */
    public function __construct($gitUser)
    {
        $this->downloadManager = new DownloadManager();
        $this->gitUser = $gitUser;
    }

    /**
     * Rafraichit les dépots de l'utilisateur
     */
    public function refresh()
    {
        $gitManager = new GitManager($this->gitUser);
        if ($this->downloadManager->isConnected()) {
            $ignoreList = $this->getIgnoreList();
            if ($gitManager->isUpdateNeeded()) {
                log::add('AlternativeMarketForJeedom', 'info', 'Mise à jour des données globales');
                $gitManager->updateRepositoriesJsonList();
                $ignoreList = array();
            }
            $repositories = $gitManager->readRepositoriesJsonList();
            foreach ($repositories as $repository) {
                $repositoryName = $repository['name'];
                if (MarketItem::isNeedUpdate($repository) && !in_array($repositoryName, $ignoreList)) {
                    log::add('AlternativeMarketForJeedom', 'info', 'Mise à jour des informations pour '.$repositoryName);
                    $marketItem =$this->refreshMarketItem($repository);
                    if ($marketItem === null) {
                        array_push($ignoreList, $repositoryName);
                    }
                    else {
                        $iconUrl = $this->getPluginIconURL($repositoryName, $marketItem->getId());
                        log::add('AlternativeMarketForJeedom', 'debug', $iconUrl);
                        log::add('AlternativeMarketForJeedom', 'debug', MarketItem::getRepositoryCacheFilename($marketItem->getFullName()).'.png');
                        $this->downloadManager->downloadBinary($iconUrl, MarketItem::getRepositoryCacheFilename($marketItem->getFullName()).'.png');
                    }
                }
            }
            $this->saveIgnoreList($ignoreList);
        }
    }

    /**
     * Obtenir la liste des dépots ignorés
     *
     * @return array|mixed
     */
    protected function getIgnoreList() {
        $result = array();
        if (file_exists(dirname(__FILE__).'/../../cache/ignore_list')) {
            $result = json_decode(file_get_contents(dirname(__FILE__).'/../../cache/ignore_list'), true);
        }
        return $result;
    }

    /**
     * Sauvegarder la liste des dépôts ignorés
     *
     * @param $ignoreList Liste des dépôts ignorés
     */
    protected function saveIgnoreList($ignoreList) {
        file_put_contents(dirname(__FILE__).'/../../cache/ignore_list', json_encode($ignoreList, true));
    }

    /**
     * Obtenir la liste des éléments du dépot
     *
     * @return array Liste des éléments
     */
    public function getItems()
    {
        $result = array();
        $gitManager = new GitManager($this->gitUser);
        $repositories = $gitManager->readRepositoriesJsonList();
        foreach ($repositories as $repository) {
            $item = MarketItem::createFromCacheFile($repository);
            if ($item !== null) {
                array_push($result, $item);
            }
        }
        return $result;
    }

    /**
     * Rafraichit les informations d'un dépot
     *
     * @param $repository Informations du dépôt
     *
     * @return MarketItem|null Informations de l'objet ou null
     */
    protected function refreshMarketItem($repository)
    {
        $marketItem = null;
        $infoJson = $this->downloadManager->downloadContent($this->getPluginInfoJsonURL($repository));
        if (strpos($infoJson, '404: Not Found') === false) {
            $marketItem = new MarketItem($repository);
            $pluginData = json_decode($infoJson, true);
            $marketItem->addPluginInformations($pluginData);
            $marketItem->writeCacheFile();
        }
        return $marketItem;
    }

    /**
     * Obtenir le lien vers le fichier d'information d'un plugin
     *
     * @param $repository Informations du dépôt
     *
     * @return string Lien vers le fcihier d'information du plugin
     */
    protected function getPluginInfoJsonURL($repository)
    {
        return 'https://raw.githubusercontent.com/' . $this->gitUser . '/' . $repository['name'] . '/master/plugin_info/info.json';
    }

    /**
     * Obtenir le lien de l'icône du plugin
     *
     * @param $repositoryName Nom du dépôt
     * @param $pluginId Identifiant du plugin
     *
     * @return string Lien de l'icône du plugin
     */
    protected function getPluginIconURL($repositoryName, $pluginId)
    {
        return 'https://raw.githubusercontent.com/' . $this->gitUser . '/' . $repositoryName . '/master/plugin_info/'.$pluginId.'_icon.png';
    }
}