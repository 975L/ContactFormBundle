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
use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Event\ContactFormEvent;
use c975L\ContactFormBundle\Form\ContactFormFactoryInterface;
use c975L\ContactFormBundle\Service\ContactFormService;
use c975L\ContactFormBundle\Service\EmailServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Contracts\Translation\TranslatorInterface;

// Lives under src/Tests (not a sibling tests/ dir) so it stays autoloadable by consuming apps,
// whose attribute route loader recursively reflects every class under the bundle root
class ContactFormServiceTest extends TestCase
{
    private const DELAY = 5;

    // Builds a fresh Request carrying its own session, as the RequestStack would provide at runtime
    private function createRequest(): Request
    {
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        return $request;
    }

    // Builds a ContactFormService bound to the given Request
    private function createService(
        Request $request,
        ?bool $sendResult = true,
        ?object $ipLimiterFactory = null,
        ?object $emailLimiterFactory = null,
    ): ContactFormService {
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $configService = $this->createStub(ConfigServiceInterface::class);
        $configService->method('get')->willReturn(self::DELAY);

        $emailService = $this->createStub(EmailServiceInterface::class);
        if (null !== $sendResult) {
            $emailService->method('send')->willReturn($sendResult);
        }

        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        return new ContactFormService(
            $requestStack,
            $configService,
            $emailService,
            $this->createStub(ContactFormFactoryInterface::class),
            $translator,
            $this->createStub(Security::class),
            $ipLimiterFactory,
            $emailLimiterFactory,
        );
    }

    // Builds a Form double whose honeypot field is absent, as it would be for a real submitter
    private function createFormWithoutHoneypot(): Form
    {
        $form = $this->createStub(Form::class);
        $form->method('has')->willReturn(false);
        $form->method('getData')->willReturn((new ContactForm())->setEmail('visitor@example.com'));

        return $form;
    }

    // Simulates a bot that submits immediately, before contact-form-delay has elapsed
    private function makeDelayElapsed(Request $request): void
    {
        $request->getSession()->set('time', time() - self::DELAY - 1);
    }

    public function testIsNotBotReturnsFalseWhenHoneypotFilled(): void
    {
        $request = $this->createRequest();
        $service = $this->createService($request);

        $this->assertFalse($service->isNotBot('a value only a bot would fill'));
    }

    public function testIsNotBotReturnsFalseWhenDelayNotElapsed(): void
    {
        $request = $this->createRequest();
        $service = $this->createService($request);
        $service->create();

        $this->assertFalse($service->isNotBot(null));
    }

    public function testIsNotBotReturnsTrueWhenHoneypotEmptyAndDelayElapsed(): void
    {
        $request = $this->createRequest();
        $service = $this->createService($request);
        $service->create();
        $this->makeDelayElapsed($request);

        $this->assertTrue($service->isNotBot(null));
    }

    public function testSendEmailSendsMessageOnHumanSubmission(): void
    {
        $request = $this->createRequest();
        $service = $this->createService($request, sendResult: true);
        $service->create();
        $this->makeDelayElapsed($request);

        $form = $this->createFormWithoutHoneypot();
        $event = new ContactFormEvent($request, new ContactForm());

        $service->sendEmail($form, $event);

        $this->assertSame(
            ['text.message_sent'],
            $request->getSession()->getFlashBag()->get('success')
        );
    }

    public function testSendEmailDoesNotSendMessageWhenHoneypotFilled(): void
    {
        $request = $this->createRequest();
        $service = $this->createService($request, sendResult: null);
        $service->create();
        $this->makeDelayElapsed($request);

        $honeypotFieldName = $request->getSession()->get('honeypotField');
        $formField = $this->createStub(FormInterface::class);
        $formField->method('getData')->willReturn('filled by a bot');

        $form = $this->createStub(Form::class);
        $form->method('has')->willReturnCallback(static fn ($name) => $name === $honeypotFieldName);
        $form->method('get')->willReturn($formField);
        $form->method('getData')->willReturn(new ContactForm());

        $service->sendEmail($form, new ContactFormEvent($request, new ContactForm()));

        $this->assertSame([], $request->getSession()->getFlashBag()->get('success'));
        $this->assertSame([], $request->getSession()->getFlashBag()->get('danger'));
    }

    public function testSendEmailRejectsSubmissionOverRateLimit(): void
    {
        $rejectedLimit = new class {
            public function isAccepted(): bool
            {
                return false;
            }
        };
        $limiter = new class($rejectedLimit) {
            public function __construct(private object $limit)
            {
            }

            public function consume(int $tokens): object
            {
                return $this->limit;
            }
        };
        $limiterFactory = new class($limiter) {
            public function __construct(private object $limiter)
            {
            }

            public function create(string $key): object
            {
                return $this->limiter;
            }
        };

        $request = $this->createRequest();
        $service = $this->createService($request, sendResult: null, ipLimiterFactory: $limiterFactory);
        $service->create();
        $this->makeDelayElapsed($request);

        $form = $this->createFormWithoutHoneypot();
        $event = new ContactFormEvent($request, new ContactForm());

        $service->sendEmail($form, $event);

        $this->assertSame(
            ['text.too_many_attempts'],
            $request->getSession()->getFlashBag()->get('warning')
        );
        $this->assertSame([], $request->getSession()->getFlashBag()->get('success'));
    }
}
