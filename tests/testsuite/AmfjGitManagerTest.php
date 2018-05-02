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

require_once('core/class/AmfjGitManager.class.php');
require_once('core/class/AmfjDataStorage.class.php');

define('GITHUB_TEST_REPO', 'jeedom');

class Mocked_GitManager extends GitManager
{
    public static $lastErrorMessage;

    public function __construct($user)
    {
        parent::__construct($user);
    }

    public function downloadRepositoriesList() {
        return parent::downloadRepositoriesList();
    }
}

class GitManagerTest extends TestCase
{
    private $gitManager;
    private $dataStorage;

    public function setUp()
    {
        DB::init(true);
        $this->gitManager = new Mocked_GitManager(GITHUB_TEST_REPO);
        $this->dataStorage = new AmfjDataStorage('amfj');
        $this->dataStorage->createDataTable();
    }

    public function tearDown()
    {
        $this->dataStorage->dropDataTable();
    }

    public function testIsUpdateNeededWithoutFile()
    {
        $this->assertTrue($this->gitManager->isUpdateNeeded());
    }

    public function testIsUpdateNeededWithRecentCall()
    {
        $this->dataStorage->storeRawData('repo_last_update_'.GITHUB_TEST_REPO, time() - 200);
        $this->assertFalse($this->gitManager->isUpdateNeeded());
    }

    public function testIsUpdateNeededWithOldCall()
    {
        $this->dataStorage->storeRawData('repo_last_update_'.GITHUB_TEST_REPO, time() - 360000);
        $this->assertTrue($this->gitManager->isUpdateNeeded());
    }

    public function testUpdateRepositoriesListGoodUser()
    {
        $result = $this->gitManager->updateRepositoriesList();
        $this->assertTrue($result);
        $this->assertNotNull($this->dataStorage->getRawData('repo_last_update_'.GITHUB_TEST_REPO));
    }

    public function testUpdateRepositoriesListBadUser()
    {
        $this->gitManager = new Mocked_GitManager('IHopeThatUserWillNeverExists');
        $result = $this->gitManager->updateRepositoriesList();
        $this->assertFalse($result);
        $this->assertNull($this->dataStorage->getRawData('repo_last_update_'.GITHUB_TEST_REPO));
    }

    public function testDownloadRepositoriesListGoodUser() {
        $result = $this->gitManager->downloadRepositoriesList();
        $this->assertNotFalse($result);
        $this->assertContains('"owner":{"login":"'.GITHUB_TEST_REPO, $result);
    }

    public function testDownloadRepositoriesListBadUser() {
        $this->gitManager = new Mocked_GitManager('IHopeThatUserWillNeverExists');
        $result = $this->gitManager->downloadRepositoriesList();
        $this->assertContains('{"message":"Not Found"', $result);
    }

    public function testReadRepositoriesListWithoutContent() {
        $result = $this->gitManager->getRepositoriesList();
        $this->assertFalse($result);
    }

    public function testReadRepositoriesListWithBadContent() {
        $this->dataStorage->storeRawData('repo_last_update_'.GITHUB_TEST_REPO, time());
        $this->dataStorage->storeRawData('repo_data_'.GITHUB_TEST_REPO, 'test');
        $result = $this->gitManager->getRepositoriesList();
        $this->assertFalse($result);
    }

    public function testReadRepositoriesListWithContent() {
        $this->dataStorage->storeRawData('repo_last_update_'.GITHUB_TEST_REPO, time());
        $this->dataStorage->storeRawData('repo_data_'.GITHUB_TEST_REPO, '{"test": "a content"}');
        $result = $this->gitManager->getRepositoriesList();
        $this->assertArrayHasKey('test', $result);
    }
}
