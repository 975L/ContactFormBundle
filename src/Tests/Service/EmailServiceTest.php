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
use c975L\ContactFormBundle\Service\EmailService;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;
use Twig\Environment;

// Lives under src/Tests (not a sibling tests/ dir) so it stays autoloadable by consuming apps,
// whose attribute route loader recursively reflects every class under the bundle root
class EmailServiceTest extends TestCase
{
    // Builds a fresh Request carrying its own session, as the RequestStack would provide at runtime
    private function createRequest(): Request
    {
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        return $request;
    }

    // Builds a MailerInterface double that records every message it is asked to send
    private function createRecordingMailer(): object
    {
        return new class implements MailerInterface {
            /** @var TemplatedEmail[] */
            public array $sent = [];

            public function send(RawMessage $message, ?Envelope $envelope = null): void
            {
                $this->sent[] = $message;
            }
        };
    }

    // Builds an EmailService bound to the given Request, mailer and config values
    private function createService(Request $request, object $mailer, array $configValues = []): EmailService
    {
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $configService = $this->createStub(ConfigServiceInterface::class);
        $configService->method('hasParameter')->willReturnCallback(
            static fn (string $parameter) => \array_key_exists($parameter, $configValues)
        );
        $configService->method('get')->willReturnCallback(
            static fn (string $parameter) => $configValues[$parameter] ?? null
        );

        return new EmailService(
            $requestStack,
            $configService,
            $mailer,
            $this->createStub(Environment::class),
        );
    }

    public function testSendBuildsEmailFromEmailDataAndCallsMailerOnce(): void
    {
        $request = $this->createRequest();
        $mailer = $this->createRecordingMailer();
        $service = $this->createService($request, $mailer);

        $formData = (new ContactForm())->setSubject('Hello')->setReceiveCopy(false);
        $event = new ContactFormEvent($request, $formData, [
            'email-from' => 'from@example.com',
            'email-to' => 'to@example.com',
            'email-reply-to' => 'visitor@example.com',
        ]);

        $result = $service->send($event, $formData);

        $this->assertTrue($result);
        $this->assertCount(1, $mailer->sent);
        $sentEmail = $mailer->sent[0];
        $this->assertSame('Hello', $sentEmail->getSubject());
        $this->assertSame('from@example.com', $sentEmail->getFrom()[0]->getAddress());
        $this->assertSame('to@example.com', $sentEmail->getTo()[0]->getAddress());
        $this->assertSame('visitor@example.com', $sentEmail->getReplyTo()[0]->getAddress());
    }

    public function testSendFallsBackToConfigServiceWhenEmailDataIsEmpty(): void
    {
        $request = $this->createRequest();
        $mailer = $this->createRecordingMailer();
        $service = $this->createService($request, $mailer, [
            'email-from' => 'config-from@example.com',
            'email-to' => 'config-to@example.com',
        ]);

        $formData = (new ContactForm())->setSubject('Hello')->setReceiveCopy(false);
        $event = new ContactFormEvent($request, $formData);

        $service->send($event, $formData);

        $sentEmail = $mailer->sent[0];
        $this->assertSame('config-from@example.com', $sentEmail->getFrom()[0]->getAddress());
        $this->assertSame('config-to@example.com', $sentEmail->getTo()[0]->getAddress());
    }

    public function testSendSendsCopyToSenderWhenReceiveCopyIsChecked(): void
    {
        $request = $this->createRequest();
        $mailer = $this->createRecordingMailer();
        $service = $this->createService($request, $mailer);

        $formData = (new ContactForm())->setSubject('Hello')->setReceiveCopy(true);
        $event = new ContactFormEvent($request, $formData, [
            'email-from' => 'from@example.com',
            'email-to' => 'to@example.com',
            'email-reply-to' => 'visitor@example.com',
        ]);

        $service->send($event, $formData);

        $this->assertCount(2, $mailer->sent);
        $copy = $mailer->sent[1];
        $this->assertSame('visitor@example.com', $copy->getTo()[0]->getAddress());
        $this->assertFalse($copy->getHeaders()->has('Reply-To'));
    }

    public function testSendReturnsFalseAndRecordsErrorWhenMailerThrows(): void
    {
        $request = $this->createRequest();
        $mailer = new class implements MailerInterface {
            public function send(RawMessage $message, ?Envelope $envelope = null): void
            {
                throw new TransportException('SMTP connection refused');
            }
        };
        $service = $this->createService($request, $mailer);

        $formData = (new ContactForm())->setSubject('Hello')->setReceiveCopy(false);
        $event = new ContactFormEvent($request, $formData, [
            'email-from' => 'from@example.com',
            'email-to' => 'to@example.com',
        ]);

        $result = $service->send($event, $formData);

        $this->assertFalse($result);
        $this->assertSame('SMTP connection refused', $event->getError());
    }

    public function testSendReturnsFalseAndRecordsErrorWhenFromOrToIsMissing(): void
    {
        $request = $this->createRequest();
        $service = $this->createService($request, $this->createRecordingMailer());

        $formData = (new ContactForm())->setSubject('Hello')->setReceiveCopy(false);
        $event = new ContactFormEvent($request, $formData);

        $result = $service->send($event, $formData);

        $this->assertFalse($result);
        $this->assertSame('Missing email parameter(s)', $event->getError());
    }
}
