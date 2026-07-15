<?php
/*
 * (c) 2026: 975L <contact@975l.com>
 * (c) 2026: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Tests\Form;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\ContactFormBundle\Form\ContactFormFactory;
use c975L\ContactFormBundle\Repository\ContactFormFieldRepository;
use c975L\ContactFormBundle\Tests\RequestWithSessionTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ContactFormFactoryTest extends TestCase
{
    use RequestWithSessionTrait;

    // Builds the "display" form's config array for a given "site-form-gdpr" config value
    private function resolveDisplayConfig(mixed $gdprConfigValue): array
    {
        $configService = $this->createStub(ConfigServiceInterface::class);
        $configService->method('get')->willReturnCallback(
            static fn (string $parameter): mixed => 'site-form-gdpr' === $parameter ? $gdprConfigValue : null
        );

        $repository = $this->createStub(ContactFormFieldRepository::class);
        $repository->method('findAllOrdered')->willReturn([]);

        $requestStack = new RequestStack();
        $requestStack->push($this->createRequest());

        $capturedOptions = [];
        $formFactory = $this->createStub(FormFactoryInterface::class);
        $formFactory->method('create')->willReturnCallback(
            function (string $type, mixed $data, array $options) use (&$capturedOptions): Form {
                $capturedOptions = $options;

                return $this->createStub(Form::class);
            }
        );

        $factory = new ContactFormFactory($configService, $formFactory, $requestStack, $repository);
        $factory->create('display', new ContactForm(), new ContactFormEvent(new Request(), new ContactForm()));

        return $capturedOptions['config'];
    }

    // "site-form-gdpr" isn't seeded when c975l/site-bundle isn't installed - the checkbox must still show
    public function testGdprFallsBackToTrueWhenConfigNotSeeded(): void
    {
        $this->assertTrue($this->resolveDisplayConfig(null)['gdpr']);
    }

    public function testGdprUsesConfiguredValueWhenSeeded(): void
    {
        $this->assertFalse($this->resolveDisplayConfig(false)['gdpr']);
    }
}
