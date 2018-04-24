<?php
/**
 * Created by PhpStorm.
 * User: dangin
 * Date: 21/04/2018
 * Time: 01:09
 */

class DownloadManager
{
    /**
     * @var bool Statut de la connexion
     */
    protected $connectionStatus;

    /**
     * Constructeur testant le statut de la connexion.
     */
    public function __construct()
    {
        $this->connectionStatus = false;
        $this->testConnection();
    }

    /**
     * Test le statut de la connexion.
     */
    protected function testConnection()
    {
        try {
            $sock = fsockopen('www.google.fr', 80);
            if ($sock) {
                $this->connectionStatus = true;
                fclose($sock);
            }
        } catch (Exception $e) {
            $this->connectionStatus = false;
        }
    }

    /**
     * Obtenir le statut de la connexion
     *
     * @return bool True si la connexion fonctionne
     */
    public function isConnected()
    {
        return $this->connectionStatus;
    }

    /**
     * Télécharge un contenu à partir de son lien
     *
     * @param $url Lien du contenu à télécharger.
     * @param $binary Télécharger un binaire
     *
     * @return string|bool Données téléchargées ou False en cas d'échec
     */
    public function downloadContent($url, $binary = false)
    {
        $result = false;
        if ($this->isCurlEnabled()) {
            $result = $this->downloadContentWithCurl($url, $binary);
        } elseif ($this->isUrlFopenEnabled()) {
            $result = $this->downloadContentWithFopen($url);
        }
        return $result;
    }

    /**
     * Télécharge un fichier binaire
     *
     * @param $url Lien du fichier
     * @param $dest Destination du fichier
     */
    public function downloadBinary($url, $dest)
    {
        $imgData = $this->downloadContent($url, true);
        if (file_exists($dest)) {
            unlink($dest);
        }
        log::add('AlternativeMarketForJeedom', 'debug', $imgData);
        $filePointer = fopen($dest, 'wb');
        fwrite($filePointer, $imgData);
        fclose($filePointer);
    }

    /**
     * Test si la fonctionnalité cURL est activée
     *
     * @return bool True si la fonctionnalité est activée
     */
    protected function isCurlEnabled()
    {
        return function_exists('curl_version');
    }

    /**
     * Télécharge un contenu à partir de son lien avec la méthode cURL
     *
     * @param $url Lien du contenu à télécharger.
     * @param $binary Télécharger un binaire
     *
     * @return string|bool Données téléchargées ou False en cas d'échec
     */
    protected function downloadContentWithCurl($url, $binary = false)
    {
        $content = false;
        $curlSession = curl_init();
        if ($curlSession !== false) {
            curl_setopt($curlSession, CURLOPT_URL, $url);
            curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
            if ($binary) {
                curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
            }
            curl_setopt($curlSession, CURLOPT_USERAGENT, 'AlternativeMarketForJeedom');
            $content = curl_exec($curlSession);
            curl_close($curlSession);
        }
        return $content;
    }

    /**
     * Test si fopen peut être utilisé pour télécharger le contenu d'un lien
     *
     * @return bool True si c'est possible
     */
    protected function isUrlFopenEnabled()
    {
        return ini_get('allow_fopen_url');
    }

    /**
     * Télécharge un contenu à partir de son lien avec la méthode fopen
     *
     * @param $url Lien du contenu à télécharger.
     *
     * @return string|bool Données téléchargées ou False en cas d'échec
     */
    protected function downloadContentWithFopen($url)
    {
        $result = false;
        try {
            $result = file_get_contents($url);
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }
}
