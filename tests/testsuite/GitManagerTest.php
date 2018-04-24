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

require_once('core/class/GitManager.class.php');

define('GITHUB_TEST_REPO', 'jeedom');

class Mocked_GitManager extends GitManager
{
    public function __construct($user)
    {
        parent::__construct($user);
    }

    public function downloadRepositoriesJsonList() {
        return parent::downloadRepositoriesJsonList();
    }
}

class GitManagerTest extends TestCase
{
    private $gitManager;
    private $destFile;

    public function setUp()
    {
        $this->gitManager = new Mocked_GitManager(GITHUB_TEST_REPO);
        $this->destFile = 'cache/' . GITHUB_TEST_REPO;
        mkdir('cache');
    }

    public function tearDown()
    {
        $filesList = scandir('cache');
        foreach ($filesList as $file) {
            if ($file != '.' && $file != '..') {
                unlink('cache/' . $file);
            }
        }
        rmdir('cache');
    }

    public function testIsUpdateNeededWithoutFile()
    {
        $this->assertTrue($this->gitManager->isUpdateNeeded());
    }

    public function testIsUpdateNeededWithNewFile()
    {
        file_put_contents('cache/' . GITHUB_TEST_REPO, 'test');
        $this->assertFalse($this->gitManager->isUpdateNeeded());
    }

    public function testIsUpdateNeededWithOldFile()
    {
        file_put_contents($this->destFile, 'test');
        touch($this->destFile, time() - 360000);
        $this->assertTrue($this->gitManager->isUpdateNeeded());
    }

    public function testUpdateRepositoriesJsonListGoodUser()
    {
        $result = $this->gitManager->updateRepositoriesJsonList();
        $this->assertTrue($result);
        $this->assertFileExists($this->destFile);
    }

    public function testUpdateRepositoriesJsonListBadUser()
    {
        $this->gitManager = new Mocked_GitManager('IHopeThatUserWillNeverExists');
        $result = $this->gitManager->updateRepositoriesJsonList();
        $this->assertFalse($result);
        $this->assertFileNotExists('tmp/' . 'IHopeThatUserWillNeverExists');
    }

    public function testDownloadRepositoriesJsonListGoodUser() {
        $result = $this->gitManager->downloadRepositoriesJsonList();
        $this->assertNotFalse($result);
        $this->assertContains('"owner":{"login":"'.GITHUB_TEST_REPO, $result);
    }

    public function testDownloadRepositoriesJsonListBadUser() {
        $this->gitManager = new Mocked_GitManager('IHopeThatUserWillNeverExists');
        $result = $this->gitManager->downloadRepositoriesJsonList();
        $this->assertContains('{"message":"Not Found"', $result);
    }

    public function testReadRepositoriesJsonListWithoutFile() {
        $result = $this->gitManager->readRepositoriesJsonList();
        $this->assertFalse($result);
    }

    public function testReadRepositoriesJsonListWithBadFile() {
        file_put_contents('cache/' . GITHUB_TEST_REPO, 'test');
        $result = $this->gitManager->readRepositoriesJsonList();
        $this->assertFalse($result);
    }

    public function testReadRepositoriesJsonListWithGoodFile() {
        file_put_contents('cache/' . GITHUB_TEST_REPO, '{"test": "a content"}');
        $result = $this->gitManager->readRepositoriesJsonList();
        $this->assertArrayHasKey('test', $result);
    }
}
