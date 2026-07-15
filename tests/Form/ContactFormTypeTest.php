<?php
/*
 * (c) 2026: 975L <contact@975l.com>
 * (c) 2026: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\Tests\Form;

use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Form\ContactFormType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Validation;

class ContactFormTypeTest extends TestCase
{
    private function formFactory(): FormFactoryInterface
    {
        return Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->getFormFactory();
    }

    private function baseConfig(): array
    {
        return [
            'customFields' => [],
            'recaptcha3SiteKey' => null,
            'recaptcha3SecretKey' => null,
            'receiveCopy' => false,
            'gdpr' => false,
        ];
    }

    private function validFormData(): array
    {
        return [
            'name' => 'John Doe',
            'email' => 'john@example.test',
            'subject' => 'Hello there',
            'message' => 'This is a long enough message for validation.',
        ];
    }

    // 'required' => true on the gdpr CheckboxType alone enforces nothing server-side - only the explicit IsTrue constraint does
    public function testGdprCheckboxIsRequiredServerSideWhenEnabled(): void
    {
        $form = $this->formFactory()->create(ContactFormType::class, new ContactForm(), [
            'config' => array_merge($this->baseConfig(), ['gdpr' => true]),
        ]);
        $form->submit($this->validFormData());

        $this->assertFalse($form->isValid());
    }

    // Checking the box satisfies the IsTrue constraint, so the rest of the form still validates normally
    public function testFormIsValidWhenGdprCheckedAndEnabled(): void
    {
        $form = $this->formFactory()->create(ContactFormType::class, new ContactForm(), [
            'config' => array_merge($this->baseConfig(), ['gdpr' => true]),
        ]);
        $form->submit($this->validFormData() + ['gdpr' => true]);

        $this->assertTrue($form->isValid());
    }

    // No field should be built at all when the "gdpr" config option is off
    public function testGdprFieldIsAbsentWhenDisabled(): void
    {
        $form = $this->formFactory()->create(ContactFormType::class, new ContactForm(), [
            'config' => $this->baseConfig(),
        ]);

        $this->assertFalse($form->has('gdpr'));
    }
}
