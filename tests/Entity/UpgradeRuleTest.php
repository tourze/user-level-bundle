<?php

namespace UserLevelBundle\Tests\Entity;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Entity\UpgradeRule;

class UpgradeRuleTest extends TestCase
{
    public function testGetId_whenNewInstance_returnsNull(): void
    {
        $rule = new UpgradeRule();
        $this->assertNull($rule->getId());
    }

    public function testSetTitle_withValidTitle_storesTitle(): void
    {
        $rule = new UpgradeRule();
        $title = '消费金额';
        
        $rule->setTitle($title);
        
        $this->assertSame($title, $rule->getTitle());
    }

    public function testSetValue_withInteger_storesValue(): void
    {
        $rule = new UpgradeRule();
        $value = 10000;
        
        $rule->setValue($value);
        
        $this->assertSame($value, $rule->getValue());
    }

    public function testSetValue_withNull_storesNull(): void
    {
        $rule = new UpgradeRule();
        $rule->setValue(100);
        
        $rule->setValue(null);
        
        $this->assertNull($rule->getValue());
    }

    public function testSetLevel_withLevelObject_storesLevel(): void
    {
        $rule = new UpgradeRule();
        $level = new Level();
        
        $rule->setLevel($level);
        
        $this->assertSame($level, $rule->getLevel());
    }

    public function testIsValid_withDefaultValue_returnsFalse(): void
    {
        $rule = new UpgradeRule();
        $this->assertFalse($rule->isValid());
    }

    public function testSetValid_withTrue_storesTrue(): void
    {
        $rule = new UpgradeRule();
        
        $rule->setValid(true);
        
        $this->assertTrue($rule->isValid());
    }

    public function testSetValid_withFalse_storesFalse(): void
    {
        $rule = new UpgradeRule();
        $rule->setValid(true);
        
        $rule->setValid(false);
        
        $this->assertFalse($rule->isValid());
    }

    public function testSetCreatedBy_withValidString_storesCreatedBy(): void
    {
        $rule = new UpgradeRule();
        $createdBy = 'admin';
        
        $rule->setCreatedBy($createdBy);
        
        $this->assertSame($createdBy, $rule->getCreatedBy());
    }

    public function testSetUpdatedBy_withValidString_storesUpdatedBy(): void
    {
        $rule = new UpgradeRule();
        $updatedBy = 'admin';
        
        $rule->setUpdatedBy($updatedBy);
        
        $this->assertSame($updatedBy, $rule->getUpdatedBy());
    }

    public function testSetCreateTime_withDateTime_storesCreateTime(): void
    {
        $rule = new UpgradeRule();
        $datetime = new DateTimeImmutable();
        
        $rule->setCreateTime($datetime);
        
        $this->assertSame($datetime, $rule->getCreateTime());
    }

    public function testSetUpdateTime_withDateTime_storesUpdateTime(): void
    {
        $rule = new UpgradeRule();
        $datetime = new DateTimeImmutable();
        
        $rule->setUpdateTime($datetime);
        
        $this->assertSame($datetime, $rule->getUpdateTime());
    }

    public function testSetCreatedBy_withNull_storesNull(): void
    {
        $rule = new UpgradeRule();
        
        $rule->setCreatedBy(null);
        
        $this->assertNull($rule->getCreatedBy());
    }

    public function testSetUpdatedBy_withNull_storesNull(): void
    {
        $rule = new UpgradeRule();
        
        $rule->setUpdatedBy(null);
        
        $this->assertNull($rule->getUpdatedBy());
    }
} 