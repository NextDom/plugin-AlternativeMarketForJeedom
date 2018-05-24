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

class Mocked_AmfjDownloadManager extends AmfjDownloadManager
{
    public static function downloadContent($url, $binary = false) {
        return parent::downloadContent($url, $binary);
    }

    public static function downloadContentWithCurl($url, $binary = false) {
        return parent::downloadContentWithCurl($url, $binary);
    }

    public static function downloadContentWithFopen($url) {
        return parent::downloadContentWithFopen($url);
    }

    public static function setConnectionStatus($status) {
        parent::$connectionStatus = $status;
    }
}

class DownloadManagerTest extends TestCase
{
    private $downloadManager;

    public function setUp()
    {
        Mocked_AmfjDownloadManager::init();
    }

    public function testIsConnected() {
        $this->assertTrue(Mocked_AmfjDownloadManager::isConnected());
    }

    public function testIsConnectedWithoutConnection() {
        Mocked_AmfjDownloadManager::setConnectionStatus(false);
        $this->assertFalse(Mocked_AmfjDownloadManager::isConnected());
    }

    public function testDownloadContent() {
        $content = Mocked_AmfjDownloadManager::downloadContent('http://www.perdu.com');
        $this->assertContains('Perdu sur l\'Internet', $content);
    }

    public function testDownloadContentWithCurlGoodContent() {
        $content = Mocked_AmfjDownloadManager::downloadContentWithCurl('http://www.perdu.com');
        $this->assertContains('Perdu sur l\'Internet', $content);
    }

    public function testDownloadContentWithCurlBadContent() {
        $content = Mocked_AmfjDownloadManager::downloadContentWithCurl('https://www.google.frrandom');
        $this->assertFalse($content);
    }

    public function testDownloadBinary() {
        system('wget -q https://www.facebook.com/images/fb_icon_325x325.png');
        Mocked_AmfjDownloadManager::downloadBinary('https://www.facebook.com/images/fb_icon_325x325.png', 'test.png');
        $this->assertFileEquals('fb_icon_325x325.png', 'test.png');
        unlink('fb_icon_325x325.png');
        unlink('test.png');
    }

    public function testDownloadWithoutGitHubToken() {
        config::addKeyToCore('github::token', '');
        Mocked_AmfjDownloadManager::init(true);
        Mocked_AmfjDownloadManager::downloadContent('http://github.com/Test/Test');
        $actions = MockedActions::get();
        $this->assertCount(1, $actions);
        $this->assertEquals('log_add', $actions[0]['action']);
        $this->assertEquals('Download http://github.com/Test/Test', $actions[0]['content']['msg']);
    }

    public function testDownloadBinaryWithGitHubToken() {
        config::addKeyToCore('github::token', 'SIMPLECHAIN');
        Mocked_AmfjDownloadManager::init(true);
        Mocked_AmfjDownloadManager::downloadContent('http://github.com/Test/Test', true);
        $actions = MockedActions::get();
        $this->assertCount(1, $actions);
        $this->assertEquals('log_add', $actions[0]['action']);
        $this->assertEquals('Download http://github.com/Test/Test', $actions[0]['content']['msg']);
    }

    public function testDownloadWithGitHubTokenSimpleUrl() {
        config::addKeyToCore('github::token', 'SIMPLECHAIN');
        Mocked_AmfjDownloadManager::init(true);
        Mocked_AmfjDownloadManager::downloadContent('http://github.com/Test/Test');
        $actions = MockedActions::get();
        $this->assertCount(1, $actions);
        $this->assertEquals('log_add', $actions[0]['action']);
        $this->assertEquals('Download http://github.com/Test/Test?access_token=SIMPLECHAIN', $actions[0]['content']['msg']);
    }

    public function testDownloadWithGitHubTokenComplexUrl() {
        config::addKeyToCore('github::token', 'SIMPLECHAIN');
        Mocked_AmfjDownloadManager::init(true);
        Mocked_AmfjDownloadManager::downloadContent('http://github.com/Test/Test?test=something');
        $actions = MockedActions::get();
        $this->assertCount(1, $actions);
        $this->assertEquals('log_add', $actions[0]['action']);
        $this->assertEquals('Download http://github.com/Test/Test?test=something&access_token=SIMPLECHAIN', $actions[0]['content']['msg']);
    }
}
