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
        $result = false;
        $gitManager = new GitManager($this->gitUser);
        if ($this->downloadManager->isConnected()) {
            $ignoreList = array();
            if ($force || $gitManager->isUpdateNeeded()) {
                if (!$gitManager->updateRepositoriesList()) {
                    $result = false;
                }
                else {
                    $result = true;
                }
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
        return $result;
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
