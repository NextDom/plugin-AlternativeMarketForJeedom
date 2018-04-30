<?php
/**
 * Created by PhpStorm.
 * User: dangin
 * Date: 20/04/2018
 * Time: 16:42
 */

require_once('AmfjDownloadManager.class.php');
require_once('AmfjDataStorage.class.php');

class GitManager
{
    /**
     * @var int Temps de rafraichissement de la liste des plugins
     */
    private $REFRESH_TIME_LIMIT = 86400;
    /**
     * @var string Utilisateur du dépot
     */
    private $gitUser;
    /**
     * @var DownloadManager Gestionnaire de téléchargement
     */
    private $downloadManager;
    /**
     * @var DataStorage Gestionnaire de base de données
     */
    private $dataStorage;

    /**
     * Constructeur du gestionnaire Git
     *
     * @param $gitUser Utilisateur du compte Git
     */
    public function __construct($gitUser)
    {
        $this->downloadManager = new DownloadManager();
        $this->gitUser = $gitUser;
        $this->dataStorage = new AmfjDataStorage('amfj');
    }

    /**
     * Test si une mise à jour de la liste des dépôts est nécessaire
     *
     * @return bool True si une mise à jour est nécessaire
     */
    public function isUpdateNeeded()
    {
        $result = true;
        $lastUpdate = $this->dataStorage->getRawData('repo_last_update_'.$this->gitUser);
        if ($lastUpdate !== null) {
            if (\time() - $lastUpdate < $this->REFRESH_TIME_LIMIT) {
                return false;
            }
        }
        return $result;
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
            if (\array_key_exists('message', $jsonAnswer) && $jsonAnswer['message'] == 'Not Found') {
                $result = false;
            }
            else {
                $dataToStore = array();
                foreach ($jsonAnswer as $repository) {
                    $data = array();
                    $data['name'] = $repository['name'];
                    $data['full_name'] = $repository['full_name'];
                    $data['description'] = $repository['description'];
                    $data['html_url'] = $repository['html_url'];
                    $data['git_user'] = $this->gitUser;
                    $data['default_branch'] = $repository['default_branch'];
                    \array_push($dataToStore, $data);
                }
                $this->dataStorage->storeRawData('repo_last_update_'.$this->gitUser, \time());
                $this->dataStorage->storeJsonData('repo_data_'.$this->gitUser, $dataToStore);
                $result = true;
            }
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
        $content = $this->downloadManager->downloadContent('https://api.github.com/users/' . $this->gitUser . '/repos');
        return $content;
    }

    /**
     * Lire le contenu du fichier contenant la liste des dépôts
     *
     * @return bool|array Tableau associatifs contenant les données ou false en cas d'échec
     */
    public function getRepositoriesList() {
        $result = false;
        $jsonStrList = $this->dataStorage->getJsonData('repo_data_'.$this->gitUser);
        if ($jsonStrList !== null) {
            $result = $jsonStrList;
        }
        return $result;
    }
}