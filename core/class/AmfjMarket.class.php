<?php
/**
 * Created by PhpStorm.
 * User: dangin
 * Date: 21/04/2018
 * Time: 01:07
 */

require_once('AmfjGitManager.class.php');
require_once('AmfjDownloadManager.class.php');
require_once('AmfjMarketItem.class.php');

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
     * @var DataStorage Gestionnaire de base de données
     */
    private $dataStorage;

    /**
     * Constructeur initialisant le gestionnaire de téléchargement
     *
     * @param $gitUser Utilisateur Git des dépôts
     */
    public function __construct($gitUser)
    {
        $this->downloadManager = new DownloadManager();
        $this->gitUser = $gitUser;
        $this->dataStorage = new AmfjDataStorage('amfj');
    }

    /**
     * Met à jour la liste des dépôts
     *
     * @param bool $force Forcer la mise à jour
     */
    public function refresh($force = false)
    {
        $gitManager = new GitManager($this->gitUser);
        if ($this->downloadManager->isConnected()) {
            $ignoreList = array();
            if ($force || $gitManager->isUpdateNeeded()) {
                $gitManager->updateRepositoriesList();
                $ignoreList = array();
            }
            else {
                $ignoreList = $this->getIgnoreList();
            }
            $repositories = $gitManager->getRepositoriesList();
            foreach ($repositories as $repository) {
                $repositoryName = $repository['name'];
                $marketItem = new MarketItem($repository);
                if (($force || $marketItem->isNeedUpdate($repository)) && !\in_array($repositoryName, $ignoreList)) {
                    if (!$marketItem->refresh($this->downloadManager)) {
                        \array_push($ignoreList, $repositoryName);
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
    protected function getIgnoreList()
    {
        $result = array();
        $jsonList = $this->dataStorage->getJsonData('repo_ignore_'.$this->gitUser);
        if ($jsonList !== null) {
            $result = $jsonList;
        }
        return $result;
    }

    /**
     * Sauvegarder la liste des dépôts ignorés
     *
     * @param array $ignoreList Liste des dépôts ignorés
     */
    protected function saveIgnoreList($ignoreList)
    {
        $this->dataStorage->storeJsonData('repo_ignore_'.$this->gitUser, $ignoreList);
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
        $repositories = $gitManager->getRepositoriesList();
        $ignoreList = $this->getIgnoreList();
        foreach ($repositories as $repository) {
            if (!\in_array($repository['name'], $ignoreList)) {
                $marketItem = new MarketItem($repository);
                $marketItem->readCache();
                array_push($result, $marketItem);
            }
        }
        return $result;
    }
}
