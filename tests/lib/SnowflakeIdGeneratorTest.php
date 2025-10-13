<?php

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Test;

use OC\SnowflakeId;
use OC\SnowflakeIdGenerator;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\ICacheFactory;
use OCP\ISnowflakeId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @package Test
 */
class SnowflakeIdGeneratorTest extends TestCase {
	private ITimeFactory|MockObject $timeFactory;
	private ICacheFactory|MockObject $cacheFactory;

	public function setUp(): void {
		$this->timeFactory = $this->createMock(ITimeFactory::class);
		$this->cacheFactory = $this->createMock(ICacheFactory::class);
	}

	public function testGenerator(): void {
		$generator = new SnowflakeIdGenerator($this->timeFactory, $this->cacheFactory);
		$snowflakeId = ($generator)();
		$this->assertGreaterThan(0x100000000, $snowflakeId);
		if (PHP_INT_SIZE === 8) {
			$this->assertIsInt($snowflakeId);
		} else {
			$this->assertIsString($snowflakeId);
		}
	}

	#[DataProvider('provideSnowflakeData')]
	public function testGeneratorWithFixedTime(string $date): void {
		$dt = new \DateTimeImmutable($date);
		$this->timeFactory->method('now')->willReturn($dt);
		$generator = new SnowflakeIdGenerator($this->timeFactory, $this->cacheFactory);
		$snowflakeId = new SnowflakeId(($generator)());

		$this->assertEquals($dt->getTimestamp() - ISnowflakeId::TS_OFFSET, $snowflakeId->seconds());
		$this->assertEquals((int)$dt->format('v'), $snowflakeId->milliseconds());
	}

	public static function provideSnowflakeData(): array {
		return  [
			['2025-10-01 00:00:00.000000'],
			['2039-12-31 23:59:59.999999'],
			['2027-08-06 03:08:30.000975'],
		];
	}
}
