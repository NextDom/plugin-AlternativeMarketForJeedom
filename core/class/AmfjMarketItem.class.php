<?php
/**
 * Created by PhpStorm.
 * User: dangin
 * Date: 21/04/2018
 * Time: 01:27
 */

class MarketItem
{
    /**
     * @var int Temps de rafraichissement d'un dépôt
     */
    private $REFRESH_TIME_LIMIT = 7200;

    /**
     * @var string Identifiant du plugin
     */
    private $id;
    /**
     * @var string Nom du plugin sur GitHub
     */
    private $gitName;
    /**
     * @var string Utilisateur GitHub
     */
    private $gitUser;
    /**
     * @var string Nom complet de son dépôt
     */
    private $fullName;
    /**
     * @var string Description
     */
    private $description;
    /**
     * @var string URL Git
     */
    private $url;
    /**
     * @var string Nom du plug
     */
    private $name;
    /**
     * @var string Auteur du plugin
     */
    private $author;
    /**
     * @var string Catégorie du plugin
     */
    private $category;
    /**
     * @var DataStorage Gestionnaire de base de données
     */
    private $dataStorage;

    /**
     * Constructeur initialisant les informations de base
     *
     * @param $repositoryInformations Informations obtenus par GitHub
     */
    public function __construct($repositoryInformations)
    {
        $this->initWithGlobalInformations($repositoryInformations);
        $this->dataStorage = new AmfjDataStorage('amfj');
    }

    /**
     * Lire les informations obtenus par GitHub
     *
     * @param $repositoryInformations Informations de GitHub
     */
    public function initWithGlobalInformations($repositoryInformations)
    {
        $this->gitName = $repositoryInformations['name'];
        $this->fullName = $repositoryInformations['full_name'];
        $this->description = $repositoryInformations['description'];
        $this->url = $repositoryInformations['html_url'];
        $this->gitUser = $repositoryInformations['git_user'];
    }

    /**
     * Ajouter les informations contenu dans le fichier info.json du plugin
     *
     * @param array $pluginInfo Contenu du fichier info.json
     */
    public function addPluginInformations($pluginInfo)
    {
        $this->id = $pluginInfo['id'];
        $this->name = $pluginInfo['name'];
        $this->author = $pluginInfo['author'];
        $this->category = $pluginInfo['category'];
    }

    /**
     * Test si une mise à jour est nécessaire
     *
     * @param $repositoryInformations Informations de GitHub
     *
     * @return bool True si une mise à jour est nécessaire
     */
    public function isNeedUpdate($repositoryInformations)
    {
        $result = true;
        $lastUpdate = $this->dataStorage->getRawData('repo_last_update_'.\str_replace('/', '_', $repositoryInformations['full_name']));
        if ($lastUpdate !== null) {
            if (\time() - $lastUpdate < $this->REFRESH_TIME_LIMIT) {
                return false;
            }
        }
        return $result;
    }

    /**
     * Obtenir l'ensemble des informations dans un tableau associatif
     *
     * @return array Tableau des données
     */
    public function getDataInArray()
    {
        $dataArray = array();
        $dataArray['name'] = $this->name;
        $dataArray['gitName'] = $this->gitName;
        $dataArray['gitUser'] = $this->gitUser;
        $dataArray['fullName'] = $this->fullName;
        $dataArray['description'] = $this->description;
        $dataArray['url'] = $this->url;
        $dataArray['id'] = $this->id;
        $dataArray['author'] = $this->author;
        $dataArray['category'] = $this->category;
        $dataArray['installed'] = $this->isInstalled();
        return $dataArray;
    }

    /**
     * Ecrire le fichier de cache au format JSON
     */
    public function writeCache()
    {
        $dataArray = $this->getDataInArray();
        $this->dataStorage->storeJsonData('repo_data_'.str_replace('/', '_', $this->fullName), $dataArray);
        $this->dataStorage->storeRawData('repo_last_update_'.str_replace('/', '_', $this->fullName), \time());
    }

    /**
     * Lire le fichier de cache
     *
     * @return bool True si la lecture a réussi
     */
    public function readCache()
    {
        $result = false;
        $jsonContent = $this->dataStorage->getJsonData('repo_data_'.str_replace('/', '_', $this->fullName));
        if ($jsonContent !== null) {
            if (\array_key_exists('name', $jsonContent)) $this->name = $jsonContent['name'];
            if (\array_key_exists('gitName', $jsonContent)) $this->gitName = $jsonContent['gitName'];
            if (\array_key_exists('gitUser', $jsonContent)) $this->gitUser = $jsonContent['gitUser'];
            if (\array_key_exists('fullName', $jsonContent)) $this->fullName = $jsonContent['fullName'];
            if (\array_key_exists('description', $jsonContent)) $this->description = $jsonContent['description'];
            if (\array_key_exists('url', $jsonContent)) $this->url = $jsonContent['url'];
            if (\array_key_exists('id', $jsonContent)) $this->id = $jsonContent['id'];
            if (\array_key_exists('author', $jsonContent)) $this->author = $jsonContent['author'];
            if (\array_key_exists('category', $jsonContent)) $this->category = $jsonContent['category'];
            $result = true;
        }
        return $result;
    }

    /**
     * Met à jour les données de l'élement
     *
     * @param AmfjDownloadManager $downloadManager Gestionnaire de téléchargement
     * @return bool True si la mise à jour a été effectuée.
     */
    public function refresh($downloadManager) {
        $result = false;
        $infoJsonUrl = 'https://raw.githubusercontent.com/' . $this->fullName . '/master/plugin_info/info.json';
        $infoJson = $downloadManager->downloadContent($infoJsonUrl);
        if (strpos($infoJson, '404: Not Found') === false) {
            $pluginData = \json_decode($infoJson, true);
            $this->addPluginInformations($pluginData);
            $this->writeCache();
            $result = true;
        }
        return $result;
    }

    /**
     * Test si le plugin est installée
     *
     * @return bool True si le plugin est installée
     */
    public function isInstalled() {
        $result = false;
        if (\file_exists(\dirname(__FILE__).'/../../../'.$this->id)) {
            $result = true;
        }
        return $result;
    }

    /**
     * Obtenir le nom du dépot.
     *
     * @return string Nom du dépot
     */
    public function getGitName()
    {
        return $this->gitName;
    }

    /**
     * Obtenir le nom complet
     *
     * @return string Nom complet
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Obtenir la description du dépot
     *
     * @return string Description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Obtenir le lien
     *
     * @return string Lien
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Obtenir l'identifiant
     *
     * @return string Identifiant
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Obtenir l'auteur
     * 
     * @return string Auteur
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Obtenir la catégorie
     *
     * @return string Catégorie
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Obtenir le nom
     *
     * @return string Nom
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Obtenir l'utilisateur GitHub
     *
     * @return string Utilisateur GitHub
     */
    public function getGitUser()
    {
        return $this->gitUser;
    }
}