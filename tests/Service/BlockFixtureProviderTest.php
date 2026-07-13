<?php
/*
 * (c) 2026: 975L <contact@975l.com>
 * (c) 2026: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Tests\Service;

use c975L\ContactFormBundle\Service\BlockFixtureProvider;
use PHPUnit\Framework\TestCase;

class BlockFixtureProviderTest extends TestCase
{
    public function testGetFixturesCoversContactFormWithASingleUnlabelledVariant(): void
    {
        $fixtures = (new BlockFixtureProvider())->getFixtures();

        $this->assertSame(['contact_form'], array_keys($fixtures));
        $this->assertSame([''], array_keys($fixtures['contact_form']));
    }
}
