<?php
/**
 * Created by PhpStorm.
 * User: dangin
 * Date: 20/04/2018
 * Time: 16:42
 */

require_once ('DownloadManager.class.php');

class GitManager
{
    /**
     * @var int Temps de rafraichissement de la liste des plugins
     */
    private $REFRESH_TIME_LIMIT = 7200;
    /**
     * @var string Utilisateur du dépot
     */
    private $gitUser;
    /**
     * @var string Fichier contenant la liste des dépôts au format JSON
     */
    private $repositoriesFile;
    /**
     * @var DownloadManager Gestionnaire de téléchargement
     */
    private $downloadManager;

    /**
     * Constructeur du gestionnaire Git
     *
     * @param $gitUser Utilisateur du compte Git
     */
    public function __construct($gitUser)
    {
        $this->downloadManager = new DownloadManager();
        $this->gitUser = $gitUser;
        $this->repositoriesFile = dirname(__FILE__) . '/../../cache/' . $this->gitUser;
    }

    /**
     * Test si une mise à jour de la liste des dépôts est nécessaire
     *
     * @return bool True si une mise à jour est nécessaire
     */
    public function isUpdateNeeded()
    {
        $result = true;
        if (file_exists($this->repositoriesFile)) {
            if (time() - filemtime($this->repositoriesFile) < $this->REFRESH_TIME_LIMIT) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Met à jour la liste des dépôts
     *
     * @return bool True si l'opération a réussie
     */
    public function updateRepositoriesJsonList()
    {
        $result = false;
        $jsonList = $this->downloadRepositoriesJsonList();
        if ($jsonList !== false) {
            $jsonAnswer = json_decode($jsonList, true);
            if (array_key_exists('message', $jsonAnswer) && $jsonAnswer['message'] == 'Not Found') {
                $result = false;
            }
            else {
                file_put_contents($this->repositoriesFile, $jsonList);
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
    protected function downloadRepositoriesJsonList()
    {
        $content = $this->downloadManager->downloadContent('https://api.github.com/users/' . $this->gitUser . '/repos');
        return $content;
    }

    /**
     * Lire le contenu du fichier contenant la liste des dépôts
     *
     * @return bool|array Tableau associatifs contenant les données ou false en cas d'échec
     */
    public function readRepositoriesJsonList() {
        $result = false;
        if (file_exists($this->repositoriesFile)) {
            $rawContent = file_get_contents($this->repositoriesFile);
            $content = json_decode($rawContent, true);
            if (is_array($content)) {
                $result = $content;
            }
        }
        return $result;
    }
}