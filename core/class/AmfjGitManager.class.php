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


require_once('AmfjDownloadManager.class.php');
require_once('AmfjDataStorage.class.php');

/**
 * Gestion des informations liées à GitHub
 */
class AmfjGitManager
{
    /**
     * @var string Utilisateur du dépot
     */
    private $gitId;
    /**
     * @var AmfjDownloadManager Gestionnaire de téléchargement
     */
    private $downloadManager;
    /**
     * @var DataStorage Gestionnaire de base de données
     */
    private $dataStorage;
    /**
     * @var string Dernier message d'erreur
     */
    private static $lastErrorMessage = false;

    /**
     * Constructeur du gestionnaire Git
     *
     * @param $gitId Utilisateur du compte Git
     */
    public function __construct($gitId)
    {
        $this->downloadManager = new AmfjDownloadManager();
        $this->gitId = $gitId;
        $this->dataStorage = new AmfjDataStorage('amfj');
    }

    /**
     * Met à jour la liste des dépôts
     *
     * @return bool True si l'opération a réussie
     */
    public function updateRepositoriesList()
    {
        $result = false;
        $jsonList = $this->downloadRepositoriesList();
        if ($jsonList !== false) {
            $jsonAnswer = \json_decode($jsonList, true);
            $dataToStore = array();
            foreach ($jsonAnswer as $repository) {
                $data = array();
                $data['name'] = $repository['name'];
                $data['full_name'] = $repository['full_name'];
                $data['description'] = $repository['description'];
                $data['html_url'] = $repository['html_url'];
                $data['git_id'] = $this->gitId;
                $data['default_branch'] = $repository['default_branch'];
                \array_push($dataToStore, $data);
            }
            $this->dataStorage->storeRawData('repo_last_update_' . $this->gitId, \time());
            $this->dataStorage->storeJsonData('repo_data_' . $this->gitId, $dataToStore);
            $result = true;
        }
        return $result;
    }

    /**
     * Télécharge la liste des dépôts au format JSON
     *
     * @return string|bool Données au format JSON ou False en cas d'échec
     */
    protected function downloadRepositoriesList()
    {
        $result = false;
        $content = $this->downloadManager->downloadContent('https://api.github.com/orgs/' . $this->gitId . '/repos?per_page=100');
        log::add('AlternativeMarketForJeedom', 'debug', $content);
        // Limite de l'API GitHub atteinte
        if (\strstr($content, 'API rate limit exceeded')) {
            $content = $this->downloadManager->downloadContent('https://api.github.com/rate_limit');
            log::add('AlternativeMarketForJeedom', 'debug', $content);
            $gitHubLimitData = json_decode($content, true);
            $refreshDate = date('H:i', $gitHubLimitData['resources']['core']['reset']);
            static::$lastErrorMessage = 'Limite de l\'API GitHub atteinte. Le rafraichissement sera accessible à ' . $refreshDate;
        } else {
            // Test si c'est un dépôt d'organisation
            if (\strstr($content, '"message":"Not Found"')) {
                // Test d'un téléchargement pour un utilisateur
                $content = $this->downloadManager->downloadContent('https://api.github.com/users/' . $this->gitId . '/repos?per_page=100');
                log::add('AlternativeMarketForJeedom', 'debug', $content);
                // Test si c'est un dépot d'utilisateur
                if (\strstr($content, '"message":"Not Found"') || strlen($content) < 10) {
                    static::$lastErrorMessage = 'Le dépôt ' . $this->gitId . ' n\'existe pas.';
                    $result = false;
                } else {
                    $result = $content;
                }
            } else {
                $result = $content;
            }
        }
        return $result;
    }

    /**
     * Lire le contenu du fichier contenant la liste des dépôts
     *
     * @return bool|array Tableau associatifs contenant les données ou false en cas d'échec
     */
    public function getRepositoriesList()
    {
        $result = false;
        $jsonStrList = $this->dataStorage->getJsonData('repo_data_' . $this->gitId);
        if ($jsonStrList !== null) {
            $result = $jsonStrList;
        }
        return $result;
    }

    /**
     * Obtenir le dernier message d'erreur
     *
     * @return string Message de l'erreur
     */
    public static function getLastErrorMessage()
    {
        $result = static::$lastErrorMessage;
        static::$lastErrorMessage = false;
        return $result;
    }
}