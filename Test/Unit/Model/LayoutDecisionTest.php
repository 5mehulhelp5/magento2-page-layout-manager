<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageLayoutManager\Test\Unit\Model;

use Hryvinskyi\PageLayoutManager\Api\LayoutDecisionInterface;
use Hryvinskyi\PageLayoutManager\Model\LayoutDecision;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for LayoutDecision value object
 */
class LayoutDecisionTest extends TestCase
{
    public function testLayoutDecisionImplementsInterface(): void
    {
        $decision = new LayoutDecision(true, [], null);

        $this->assertInstanceOf(LayoutDecisionInterface::class, $decision);
    }

    public function testLayoutDecisionWithAllowedTrue(): void
    {
        $parameters = ['test' => 'value', 'key' => 'data'];
        $defaultHandle = 'test_handle';

        $decision = new LayoutDecision(true, $parameters, $defaultHandle);

        $this->assertTrue($decision->isAllowed());
        $this->assertEquals($parameters, $decision->getParameters());
        $this->assertEquals($defaultHandle, $decision->getDefaultHandle());
    }

    public function testLayoutDecisionWithAllowedFalse(): void
    {
        $parameters = ['error' => 'value'];
        $defaultHandle = 'error_handle';

        $decision = new LayoutDecision(false, $parameters, $defaultHandle);

        $this->assertFalse($decision->isAllowed());
        $this->assertEquals($parameters, $decision->getParameters());
        $this->assertEquals($defaultHandle, $decision->getDefaultHandle());
    }

    public function testLayoutDecisionWithNullDefaultHandle(): void
    {
        $parameters = ['test' => 'value'];

        $decision = new LayoutDecision(true, $parameters, null);

        $this->assertTrue($decision->isAllowed());
        $this->assertEquals($parameters, $decision->getParameters());
        $this->assertNull($decision->getDefaultHandle());
    }

    public function testLayoutDecisionWithEmptyParameters(): void
    {
        $decision = new LayoutDecision(false, [], 'handle');

        $this->assertFalse($decision->isAllowed());
        $this->assertEquals([], $decision->getParameters());
        $this->assertEquals('handle', $decision->getDefaultHandle());
    }

    public function testLayoutDecisionIsImmutable(): void
    {
        $parameters = ['test' => 'value'];
        $defaultHandle = 'test_handle';

        $decision = new LayoutDecision(true, $parameters, $defaultHandle);

        // Modify the original arrays
        $parameters['new'] = 'added';
        $defaultHandle = 'modified';

        // Decision should remain unchanged
        $this->assertEquals(['test' => 'value'], $decision->getParameters());
        $this->assertEquals('test_handle', $decision->getDefaultHandle());
    }
}