<?php

namespace App\Tests\Controller;

class ContactControllerTest extends FunctionalTestCase
{
    public function testContactPageIsSuccessful(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/contact');

        $this->assertResponseIsSuccessful();
    }

    public function testSubmittingContactFormRedirects(): void
    {
        $client = $this->createAuthenticatedClient();
        $crawler = $client->request('GET', '/contact');

        $form = $crawler->filter('form[action$="/contact"]')->form();
        $formName = $form->getName();

        $form[$formName . '[name]'] = 'Test Client';
        $form[$formName . '[email]'] = 'test-client@example.test';
        $form[$formName . '[subject]'] = 'Sujet de test';
        $form[$formName . '[message]'] = 'Message envoyé par le test fonctionnel.';

        $client->submit($form);

        $this->assertResponseRedirects();
    }
}
