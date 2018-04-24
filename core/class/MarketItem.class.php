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
    private static $REFRESH_TIME_LIMIT = 7200;

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
     * @var string URL du dépôt Git
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
     * @var string Lien de l'icône
     */

    /**
     * Constructeur initialisant les informations de base
     *
     * @param $repositoryInformations Informations obtenus par GitHub
     */
    public function __construct($repositoryInformations)
    {
        $this->initWithGlobalInformations($repositoryInformations);
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
        $this->gitUser = $repositoryInformations['owner']['login'];
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
    public static function isNeedUpdate($repositoryInformations)
    {
        $result = true;
        $cacheFilename = static::getRepositoryCacheFilename($repositoryInformations['full_name']);
        if (file_exists($cacheFilename)) {
            if (time() - filemtime($cacheFilename) < static::$REFRESH_TIME_LIMIT) {
                $result = false;
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
        return $dataArray;
    }

    /**
     * Ecrire le fichier de cache au format JSON
     */
    public function writeCacheFile()
    {
        $dataArray = $this->getDataInArray();
        file_put_contents(static::getRepositoryCacheFilename($this->fullName), json_encode($dataArray));
    }

    /**
     * Lire le fichier de cache
     *
     * @param $cacheFilename Nom du fichier de cache
     */
    public function readCacheFile($cacheFilename)
    {
        $content = file_get_contents($cacheFilename);
        $jsonContent = json_decode($content, true);
        if (array_key_exists('name', $jsonContent)) $this->name = $jsonContent['name'];
        if (array_key_exists('gitName', $jsonContent)) $this->gitName = $jsonContent['gitName'];
        if (array_key_exists('gitUser', $jsonContent)) $this->gitUser = $jsonContent['gitUser'];
        if (array_key_exists('fullName', $jsonContent)) $this->fullName = $jsonContent['fullName'];
        if (array_key_exists('description', $jsonContent)) $this->description = $jsonContent['description'];
        if (array_key_exists('url', $jsonContent)) $this->url = $jsonContent['url'];
        if (array_key_exists('id', $jsonContent)) $this->id = $jsonContent['id'];
        if (array_key_exists('author', $jsonContent)) $this->author = $jsonContent['author'];
        if (array_key_exists('category', $jsonContent)) $this->category = $jsonContent['category'];
    }

    /**
     * Créer l'élément à partir du cache
     *
     * @param $repositoryInformations Informations du dépôt
     *
     * @return MarketItem|null Elément lu ou null
     */
    public static function createFromCacheFile($repositoryInformations)
    {
        $marketItem = null;
        $cacheFilename = static::getRepositoryCacheFilename($repositoryInformations['full_name']);
        if (file_exists($cacheFilename)) {
            $marketItem = new MarketItem($repositoryInformations);
            $marketItem->readCacheFile($cacheFilename);
        }
        return $marketItem;
    }

    /**
     * Obtenir le chemin du fichier de cache
     *
     * @param $fullName Nom du dépôt
     *
     * @return string Chemin du fichier de cache
     */
    public static function getRepositoryCacheFilename($fullName)
    {
        return dirname(__FILE__) . '/../../cache/' . str_replace('/', '_', $fullName);
    }

    /**
     * @return mixed
     */
    public function getGitName()
    {
        return $this->gitName;
    }

    /**
     * @return mixed
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getGitUser()
    {
        return $this->gitUser;
    }
}