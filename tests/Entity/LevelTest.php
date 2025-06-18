<?php

namespace UserLevelBundle\Tests\Entity;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Entity\UpgradeRule;

class LevelTest extends TestCase
{
    public function testGetId_whenNewInstance_returnsNull(): void
    {
        $level = new Level();
        $this->assertNull($level->getId());
    }

    public function testSetTitle_withValidTitle_storesTitle(): void
    {
        $level = new Level();
        $title = 'VIP会员';
        
        $level->setTitle($title);
        
        $this->assertSame($title, $level->getTitle());
    }

    public function testSetLevel_withInteger_storesLevel(): void
    {
        $level = new Level();
        $levelValue = 3;
        
        $level->setLevel($levelValue);
        
        $this->assertSame($levelValue, $level->getLevel());
    }

    public function testIsValid_withDefaultValue_returnsFalse(): void
    {
        $level = new Level();
        $this->assertFalse($level->isValid());
    }

    public function testSetValid_withTrue_storesTrue(): void
    {
        $level = new Level();
        
        $level->setValid(true);
        
        $this->assertTrue($level->isValid());
    }

    public function testSetValid_withFalse_storesFalse(): void
    {
        $level = new Level();
        $level->setValid(true);
        
        $level->setValid(false);
        
        $this->assertFalse($level->isValid());
    }

    public function testSetCreatedBy_withValidString_storesCreatedBy(): void
    {
        $level = new Level();
        $createdBy = 'admin';
        
        $level->setCreatedBy($createdBy);
        
        $this->assertSame($createdBy, $level->getCreatedBy());
    }

    public function testSetUpdatedBy_withValidString_storesUpdatedBy(): void
    {
        $level = new Level();
        $updatedBy = 'admin';
        
        $level->setUpdatedBy($updatedBy);
        
        $this->assertSame($updatedBy, $level->getUpdatedBy());
    }

    public function testSetCreateTime_withDateTime_storesCreateTime(): void
    {
        $level = new Level();
        $datetime = new DateTimeImmutable();
        
        $level->setCreateTime($datetime);
        
        $this->assertSame($datetime, $level->getCreateTime());
    }

    public function testSetUpdateTime_withDateTime_storesUpdateTime(): void
    {
        $level = new Level();
        $datetime = new DateTimeImmutable();
        
        $level->setUpdateTime($datetime);
        
        $this->assertSame($datetime, $level->getUpdateTime());
    }

    public function testAddUpgradeRule_withNewRule_addsRuleToCollection(): void
    {
        $level = new Level();
        $upgradeRule = new UpgradeRule();
        
        $level->addUpgradeRule($upgradeRule);
        
        $this->assertCount(1, $level->getUpgradeRules());
        $this->assertTrue($level->getUpgradeRules()->contains($upgradeRule));
        $this->assertSame($level, $upgradeRule->getLevel());
    }

    public function testAddUpgradeRule_withDuplicateRule_doesNotAddAgain(): void
    {
        $level = new Level();
        $upgradeRule = new UpgradeRule();
        
        $level->addUpgradeRule($upgradeRule);
        $level->addUpgradeRule($upgradeRule);
        
        $this->assertCount(1, $level->getUpgradeRules());
    }

    public function testRemoveUpgradeRule_withExistingRule_removesRuleFromCollection(): void
    {
        $level = new Level();
        $upgradeRule = new UpgradeRule();
        $level->addUpgradeRule($upgradeRule);
        
        $level->removeUpgradeRule($upgradeRule);
        
        $this->assertCount(0, $level->getUpgradeRules());
        $this->assertFalse($level->getUpgradeRules()->contains($upgradeRule));
    }

    public function testRemoveUpgradeRule_withNonExistingRule_doesNothing(): void
    {
        $level = new Level();
        $upgradeRule = new UpgradeRule();
        
        $level->removeUpgradeRule($upgradeRule);
        
        $this->assertCount(0, $level->getUpgradeRules());
    }

    public function testRetrieveAdminArray_returnsExpectedArray(): void
    {
        $level = new Level();
        $level->setTitle('VIP会员');
        $level->setLevel(3);
        
        $result = $level->retrieveAdminArray();
        $this->assertArrayHasKey('level', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals(3, $result['level']);
        $this->assertEquals('VIP会员', $result['title']);
        $this->assertNull($result['id']);
    }
} 