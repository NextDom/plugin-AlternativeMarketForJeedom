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

use PHPUnit\Framework\TestCase;

require_once('core/class/AmfjDownloadManager.class.php');
require_once('../../core/php/core.inc.php');

class Mocked_DownloadManager extends DownloadManager
{
    public function downloadContentWithCurl($url, $binary = false) {
        return parent::downloadContentWithCurl($url);
    }

    public function downloadContentWithFopen($url) {
        return parent::downloadContentWithFopen($url);
    }

    public function setConnectionStatus($status) {
        $this->connectionStatus = $status;
    }
}

class DownloadManagerTest extends TestCase
{
    private $downloadManager;

    public function setUp()
    {
        $this->downloadManager = new Mocked_DownloadManager();
    }

    public function testIsConnected() {
        $this->assertTrue($this->downloadManager->isConnected());
    }

    public function testIsConnectedWithoutConnection() {
        $this->downloadManager->setConnectionStatus(false);
        $this->assertFalse($this->downloadManager->isConnected());
    }

    public function testDownloadContent() {
        $content = $this->downloadManager->downloadContent('http://www.perdu.com');
        $this->assertContains('Perdu sur l\'Internet', $content);
    }

    public function testDownloadContentWithCurlGoodContent() {
        $content = $this->downloadManager->downloadContentWithCurl('http://www.perdu.com');
        $this->assertContains('Perdu sur l\'Internet', $content);
    }

    public function testDownloadContentWithCurlBadContent() {
        $content = $this->downloadManager->downloadContentWithCurl('https://www.google.frrandom');
        $this->assertFalse($content);
    }

    public function testDownloadBinary() {
        system('wget -q https://www.facebook.com/images/fb_icon_325x325.png');
        $this->downloadManager->downloadBinary('https://www.facebook.com/images/fb_icon_325x325.png', 'test.png');
        $this->assertFileEquals('fb_icon_325x325.png', 'test.png');
        unlink('fb_icon_325x325.png');
        unlink('test.png');
    }

    public function testDdwnloadContentWithFopenGoodContent() {
        $content = $this->downloadManager->downloadContentWithFopen('https://www.google.fr');
        $this->assertContains('J\'ai de la chance', $content);
    }

    public function testDdwnloadContentWithFopenBadContent() {
        $content = $this->downloadManager->downloadContentWithFopen('https://www.google.frrandom');
        $this->assertFalse($content);
    }
}
