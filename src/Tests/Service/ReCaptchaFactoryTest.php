<?php
/*
 * (c) 2026: 975L <contact@975l.com>
 * (c) 2026: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Tests\Service;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\ContactFormBundle\Service\ReCaptchaFactory;
use Karser\Recaptcha3Bundle\ReCaptcha\RequestMethod\Post;
use PHPUnit\Framework\TestCase;

// Lives under src/Tests (not a sibling tests/ dir) so it stays autoloadable by consuming apps,
// whose attribute route loader recursively reflects every class under the bundle root
class ReCaptchaFactoryTest extends TestCase
{
    // Reads a private property of ReCaptcha, as it exposes no getter for secret/threshold
    private function readProperty(object $object, string $property): mixed
    {
        $reflection = new \ReflectionProperty($object, $property);

        return $reflection->getValue($object);
    }

    // Builds a ReCaptchaFactory bound to the given config values
    private function createFactory(array $configValues): ReCaptchaFactory
    {
        $configService = $this->createStub(ConfigServiceInterface::class);
        $configService->method('hasParameter')->willReturnCallback(
            static fn (string $parameter) => \array_key_exists($parameter, $configValues)
        );
        $configService->method('get')->willReturnCallback(
            static fn (string $parameter) => $configValues[$parameter] ?? null
        );

        return new ReCaptchaFactory($configService);
    }

    public function testCreateUsesFallbacksWhenConfigServiceHasNoParameters(): void
    {
        $factory = $this->createFactory([]);

        $reCaptcha = $factory->create('fallback-secret', new Post(), 0.5);

        $this->assertSame('fallback-secret', $this->readProperty($reCaptcha, 'secret'));
        $this->assertSame(0.5, $this->readProperty($reCaptcha, 'threshold'));
    }

    public function testCreateUsesConfigServiceValuesWhenSet(): void
    {
        $factory = $this->createFactory([
            'recaptcha3-secret-key' => 'config-secret',
            'recaptcha3-score-threshold' => '0.05',
        ]);

        $reCaptcha = $factory->create('fallback-secret', new Post(), 0.5);

        $this->assertSame('config-secret', $this->readProperty($reCaptcha, 'secret'));
        $this->assertSame(0.05, $this->readProperty($reCaptcha, 'threshold'));
    }

    public function testCreateFallsBackToDefaultsWhenConfigServiceValuesAreEmpty(): void
    {
        $factory = $this->createFactory([
            'recaptcha3-secret-key' => '',
            'recaptcha3-score-threshold' => '',
        ]);

        $reCaptcha = $factory->create('fallback-secret', new Post(), 0.5);

        $this->assertSame('fallback-secret', $this->readProperty($reCaptcha, 'secret'));
        $this->assertSame(0.5, $this->readProperty($reCaptcha, 'threshold'));
    }
}
