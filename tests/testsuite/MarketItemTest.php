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

require_once('core/class/MarketItem.class.php');

class MarketItemTest extends TestCase
{
    /**
     * @var MarketItem
     */
    private $marketItem;

    private $initialData = array(
        'name' => 'core',
        'full_name' => 'jeedom/core',
        'description' => 'A small description',
        'html_url' => 'https://github.com/jeedom/core'
    );

    public function setUp()
    {
        $this->marketItem = new MarketItem($this->initialData);
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

    public function testInitWithGlobalInformations() {
        $this->assertEquals('core', $this->marketItem->getGitName());
        $this->assertEquals('jeedom/core', $this->marketItem->getFullName());
        $this->assertEquals('A small description', $this->marketItem->getDescription());
        $this->assertEquals('https://github.com/jeedom/core', $this->marketItem->getUrl());
    }

    public function testAddPluginInformations() {
        $pluginInformations = array(
            'id' => 'Core',
            'name' => 'core',
            'author' => 'Someone',
            'category' => 'programming'
        );
        $this->marketItem->addPluginInformations($pluginInformations);
        $this->assertEquals('Core', $this->marketItem->getId());
        $this->assertEquals('Someone', $this->marketItem->getAuthor());
        $this->assertEquals('programming', $this->marketItem->getCategory());
    }

    public function testIsNeedUpdateWithNothing() {
        $result = $this->marketItem->isNeedUpdate($this->initialData);
        $this->assertTrue($result);
    }

    public function testIsNeedUpdateWithRecentFile() {
        file_put_contents('cache/jeedom_core', 'data');
        $result = $this->marketItem->isNeedUpdate($this->initialData);
        $this->assertFalse($result);
    }

    public function testIsNeedUpdateWithOldFile() {
        file_put_contents('cache/jeedom_core', 'data');
        touch('cache/jeedom_core', time() - 360000);
        $result = $this->marketItem->isNeedUpdate($this->initialData);
        $this->assertTrue($result);
    }

    public function testGetDataInArray() {
        $result = $this->marketItem->getDataInArray();
        $this->assertArrayHasKey('category', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('core', $result['gitName']);
    }

    public function testWriteCache() {
        $this->marketItem->writeCacheFile();
        $this->assertFileExists('cache/jeedom_core');
        $content = file_get_contents('cache/jeedom_core');
        $this->assertContains('"fullName":"jeedom\/core"', $content);
    }

    public function testReadCacheFile() {
        file_put_contents('cache/jeedom_core', json_encode($this->initialData));
        $this->marketItem->readCacheFile('cache/jeedom_core');
        $this->assertEquals('core', $this->marketItem->getGitName());
        $this->assertEquals('', $this->marketItem->getId());
    }

    public function testCreateFromCacheFileWithoutCache() {
        $result = MarketItem::createFromCacheFile($this->initialData);
        $this->assertNull($result);
    }

    public function testCreateFromCacheFileWithCache() {
        file_put_contents('cache/jeedom_core', json_encode($this->initialData));
        $result = MarketItem::createFromCacheFile($this->initialData);
        $this->assertEquals('core', $result->getGitName());
        $this->assertEquals('', $result->getId());
    }

    public function testGetRepositoryCacheFilename() {
        $result = realpath(dirname(__FILE__) . '/../cache/jeedom_core');
        $this->assertEquals($result, realpath(MarketItem::getRepositoryCacheFilename('jeedom/core')))   ;
    }
}
