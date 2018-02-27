ContactFormBundle
=================

ContactFormBundle does the following:

- Display a form to contact a website,
- Pre-fills data if user is logged in,
- Sends the email via [c975LEmailBundle](https://github.com/975L/EmailBundle) as `c975LEmailBundle` provides the possibility to save emails in a database, there is an option to NOT do so via this Bundle,
- Sends a copy to the email provided,
- Allows the possibility to send email to other user, related to your app specification, i.e. contact another user without giving its email. this is achieved by overriding part of the Controller (see below)

[ContactForm Bundle dedicated web page](https://975l.com/en/pages/contact-form-bundle).

Bundle installation
===================

Step 1: Download the Bundle
---------------------------
Use [Composer](https://getcomposer.org) to install the library
```bash
    composer require c975l/contactform-bundle
```

Step 2: Enable the Bundles
--------------------------
Then, enable the bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:

```php
<?php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new c975L\EmailBundle\c975LEmailBundle(),
            new c975L\ContactFormBundle\c975LContactFormBundle(),
        ];
    }
}
```

Step 3: Configure the Bundles
-----------------------------
Check [Swiftmailer](https://github.com/symfony/swiftmailer-bundle), [Doctrine](https://github.com/doctrine/DoctrineBundle) and [c975LEmailBundle](https://github.com/975L/EmailBundle) for their specific configuration

Then, in the `app/config.yml` file of your project, define the following:

```yml
#ContactFormBundle
c975_l_contact_form:
    #The site name that will appear on the contact form
    site: 'example.com'
    #The email address that will receive the email sent by the contact form
    sentTo: 'contact@example.com'
    #If you want to save the email sent to the database linked to c975L/EmailBundle, see https://github.com/975L/EmailBundle
    database: true #false(default)
```

Step 4: Enable the Routes
-------------------------
Then, enable the routes by adding them to the `app/config/routing.yml` file of your project:

```yml
c975_l_contact_form:
    resource: "@c975LContactFormBundle/Controller/"
    type:     annotation
    #Multilingual website use: prefix: /{_locale}
    prefix:   /
```

Step 5: Override templates
--------------------------
It is strongly recommended to use the [Override Templates from Third-Party Bundles feature](http://symfony.com/doc/current/templating/overriding.html) to integrate fully with your site.

For this, simply, create the following structure `app/Resources/c975LContactFormBundle/views/` in your app and then duplicate the file `layout.html.twig` in it, to override the existing Bundle file.

In `layout.html.twig`, it will mainly consist to extend your layout and define specific variables, i.e. :
```twig
{% extends 'layout.html.twig' %}

{# Defines specific variables #}
{% set title = 'Contact' %}

{% block content %}
    <div class="container">
        {% block contactform_content %}
        {% endblock %}
    </div>
{% endblock %}
```

The template used for sending emails is the one of c975LEmailBundle. Override it in `app/Resources/c975LEmailBundle/views/emails/layout.html.twig`.

How to use
----------
The Route name is `contactform_display` so you can add link in Twig via ̀`{{ path('contactform_display') }}`.

The url path is `/contact` (`/{_locale}/contact`), so simply access to `http://example.com/contact` to display the form or `http://example.com/en/contact`.

You can set the subject by using the url parameter `s` i.e. `http://example.com/contact?s=Subject`, the field will be readonly in the form, **but, of course it can be changed via the url**. The value is sanitized and given (as `subject`) to the form in order to be able to change title and/or info text based on this value, i.e.

```twig
{% if 'Subject' in subject %}
    {# Do some stuff #}
{% endif %}
```
Override Controller to set specific email data
----------------------------------------------
It is possible to set specific email data (body, subject, etc.) based on the `subject` value. For this, the function `testSubject()` from the Controller must be overriden by doing the following:

In your `src` folder, create the structure `ContactFormBundle/Controller` and set the follwing code

In `/src/ContactFormBundle/ContactFormBundle.php`
```php
<?php

namespace ContactFormBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ContactFormBundle extends Bundle
{
    public function getParent()
    {
        return 'c975LContactFormBundle';
    }
}
```

In `/src/ContactFormBundle/Controller/ContactFormController.php`
```php
<?php

namespace AppBundle\Controller;

use c975L\ContactFormBundle\Controller\ContactFormController as BaseController;

class ContactFormController extends BaseController
{
    public function testSubject($subject, $formData)
    {
        //Any condition to fulfill
        if (1 == 2) {
            //Defines data for email
            $bodyEmail = 'AnyTemplate.html.twig';
            $bodyData = array(
                'AnyDataNeededByTemplate Or empty array',
                );
            //The following array, with keys, MUST be returend by the function to hydrate email
            $emailData = array(
                'subject' => 'subjectEmail',
                'sentTo' => 'sentToEmail',
                'sentCc' => 'sentCcEmail',
                'replyTo' => 'replyToEmail',
                'body' => $this->renderView($bodyEmail, $bodyData),
                );

            return $emailData;
        }

        //No subject found
        return false;
    }
```

Then, enable the bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new ContactFormBundle\ContactFormBundle(),
        ];

        // ...
    }

    // ...
}
```
