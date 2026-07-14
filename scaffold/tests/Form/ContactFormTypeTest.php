<?php

namespace App\Tests\Form;

use c975L\ContactFormBundle\Entity\ContactForm;
use c975L\ContactFormBundle\Form\ContactFormType;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use Symfony\Component\Form\Test\TypeTestCase;

class ContactFormTypeTest extends TypeTestCase
{
    //TypeTestCase::setUp() mocks EventDispatcherInterface without expectations, not our code
    #[AllowMockObjectsWithoutExpectations]
    public function testContact(): void
    {
        $formData = array(
            'name' => 'Laurent',
            'email' => 'contact@example.com',
            'subject' => 'Message subject',
            'message' => 'Contents for the message, long enough to pass the test.',
            'config' => [
                'receiveCopy' => false,
                'gdpr' => true
            ],
        );

        // Creates the object
        $object = new ContactForm();
        $object
            ->setName($formData['name'])
            ->setEmail($formData['email'])
            ->setSubject($formData['subject'])
            ->setMessage($formData['message'])
        ;

        // Creates the form
        $form = $this->factory->create(ContactFormType::class, $object, ['config' => $formData['config']]);

        // Submits the data to the form directly
        $form->submit($formData);

        // Tests the contents of the form
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($object, $form->getData());

        // Tests the view
        $view = $form->createView();
        $children = $view->children;
        unset($formData['config']);
        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
