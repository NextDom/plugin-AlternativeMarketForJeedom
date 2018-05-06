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

class AmfjMarket
{
    /**
     * @var int Temps de rafraichissement de la liste des plugins
     */
    private $REFRESH_TIME_LIMIT = 86400;
    /**
     * @var AmfjDownloadManager Gestionnaire de téléchargement
     */
    private $downloadManager;

    /**
     * @var Utilisateur Git des depôts
     */
    private $source;

    /**
     * @var DataStorage Gestionnaire de base de données
     */
    private $dataStorage;

    /**
     * Constructeur initialisant le gestionnaire de téléchargement
     *
     * @param $gitId Utilisateur Git des dépôts
     */
    public function __construct($source)
    {
        $this->downloadManager = new AmfjDownloadManager();
        $this->source = $source;
        $this->dataStorage = new AmfjDataStorage('amfj');
    }

    /**
     * Met à jour la liste des dépôts
     *
     * @param bool $force Forcer la mise à jour
     *
     * @return True si une mise à jour a été réalisée
     */
    public function refresh($force = false)
    {
        $result = false;
        if ($this->source['type'] == 'github') {
            $result = $this->refreshGitHub($force);
        } else if ($this->source['type'] == 'json') {
            $result = $this->refreshJson($force);
        }
        return $result;
    }

    public function refreshGitHub($force) {
        $result = false;
        $gitManager = new AmfjGitManager($this->source['data']);
        if ($this->downloadManager->isConnected()) {
            $ignoreList = array();
            if ($force || $this->isUpdateNeeded($this->source['data'])) {
                if (!$gitManager->updateRepositoriesList()) {
                    $result = false;
                } else {
                    $result = true;
                }
            } else {
                $ignoreList = $this->getIgnoreList();
            }
            $repositories = $gitManager->getRepositoriesList();
            foreach ($repositories as $repository) {
                $repositoryName = $repository['name'];
                $marketItem = AmfjMarketItem::createFromGit($this->source['name'], $repository);
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

    public function refreshJson($force) {
        $result = false;
        if ($this->downloadManager->isConnected()) {
            $content = null;
            if ($force || $this->isUpdateNeeded($this->source['name'])) {
                $content = $this->downloadManager->downloadContent($this->source['data']);
                if ($content !== false) {
                    $marketData = json_decode($content, true);
                    $lastChange = $this->dataStorage->getRawData('repo_last_change_' . $this->source['name']);
                    if ($lastChange == null || $marketData['version'] > $lastChange) {
                        foreach ($marketData['plugins'] as $plugin) {
                            $marketItem = AmfjMarketItem::createFromJson($this->source['name'], $plugin, $this->downloadManager);
                            $marketItem->writeCache();
                        }
                        $result = true;
                        $this->dataStorage->storeJsonData('repo_data_' . $this->source['name'], $marketData['plugins']);
                        $this->dataStorage->storeRawData('repo_last_change_' . $this->source['name'], $marketData['version']);
                    }
                    $this->dataStorage->storeRawData('repo_last_update_' . $this->source['name'], \time());
                }
            }
        }
        return $result;
    }

    /**
     * Test si une mise à jour de la liste des dépôts est nécessaire
     *
     * @return bool True si une mise à jour est nécessaire
     */
    public function isUpdateNeeded($id)
    {
        $result = true;
        $lastUpdate = $this->dataStorage->getRawData('repo_last_update_' . $id);
        if ($lastUpdate !== null) {
            if (\time() - $lastUpdate < $this->REFRESH_TIME_LIMIT) {
                return false;
            }
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
        $jsonList = $this->dataStorage->getJsonData('repo_ignore_' . $this->source['data']);
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
        $this->dataStorage->storeJsonData('repo_ignore_' . $this->source['data'], $ignoreList);
    }

    /**
     * Obtenir la liste des éléments du dépot
     *
     * @return AmfjMarketItem[] Liste des éléments
     */
    public function getItems()
    {
        $result = array();
        if ($this->source['type'] == 'github') {
            $result = $this->getItemsFromGitHub();
        }
        else if ($this->source['type'] == 'json') {
            $result = $this->getItemsFromJson();
        }
        return $result;
    }

    public function getItemsFromGitHub() {
        $result = array();
        $gitManager = new AmfjGitManager($this->source['data']);
        $repositories = $gitManager->getRepositoriesList();
        $ignoreList = $this->getIgnoreList();
        foreach ($repositories as $repository) {
            if (!\in_array($repository['name'], $ignoreList)) {
                $marketItem = AmfjMarketItem::createFromCache($this->source['name'], $repository['full_name']);
                array_push($result, $marketItem);
            }
        }
        return $result;
    }

    public function getItemsFromJson() {
        $result = array();
        $plugins = $this->dataStorage->getJsonData('repo_data_' . $this->source['name']);
        foreach ($plugins as $plugin) {
            $marketItem = AmfjMarketItem::createFromCache($this->source['name'], $plugin['gitId'].'/'.$plugin['repository']);
            array_push($result, $marketItem);
        }
        return $result;
    }
}
