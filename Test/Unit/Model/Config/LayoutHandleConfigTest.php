<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageLayoutManager\Test\Unit\Model\Config;

use Hryvinskyi\PageLayoutManager\Model\Config\LayoutHandleConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for LayoutHandleConfig
 */
class LayoutHandleConfigTest extends TestCase
{
    private LayoutHandleConfig $layoutHandleConfig;
    private ScopeConfigInterface|MockObject $scopeConfigMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->layoutHandleConfig = new LayoutHandleConfig($this->scopeConfigMock);
    }

    /**
     * @dataProvider booleanProvider
     */
    public function testIsEntityLayoutEnabled(bool $expected): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                'hryvinskyi_page_layout/entity_specific/enabled'
            )
            ->willReturn($expected);

        $result = $this->layoutHandleConfig->isEntityLayoutEnabled();

        $this->assertSame($expected, $result);
    }

    /**
     * @dataProvider booleanProvider
     */
    public function testIsOnlySpecificValidatorsEnabled(bool $expected): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                'hryvinskyi_page_layout/entity_specific/enabled_only_specific'
            )
            ->willReturn($expected);

        $result = $this->layoutHandleConfig->isOnlySpecificValidatorsEnabled();

        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array<bool>>
     */
    public static function booleanProvider(): array
    {
        return [
            'enabled' => [true],
            'disabled' => [false],
        ];
    }
}