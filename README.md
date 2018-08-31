ContactFormBundle
=================

ContactFormBundle does the following:

- Display a form to contact a website,
- Pre-fills data if user is logged in,
- Dispatch events to modify form/email,
- Sends the email via [c975LEmailBundle](https://github.com/975L/EmailBundle) as `c975LEmailBundle` provides the possibility to save emails in a database, there is an option to NOT do so via this Bundle,
- Sends a copy to the email provided,
- Allows the possibility to send email to other user, related to your app specification, i.e. contact another user without giving its email. This is achieved via event dispatch (see below),
- Provides honeypot and delay before real submission, to avoid spam and not need to request captcha (see below),

[ContactFormBundle dedicated web page](https://975l.com/en/pages/contact-form-bundle).

[ContactFormBundle API documentation](https://975l.com/apidoc/c975L/ContactFormBundle.html).

Bundle installation
===================

Step 1: Download the Bundle
---------------------------
Use [Composer](https://getcomposer.org) to install the library
```bash
    composer require c975l/contactform-bundle
```

Step 2: Enable the Bundle
-------------------------
Then, enable the bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:

```php
<?php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new c975L\ContactFormBundle\c975LContactFormBundle(),
        ];
    }
}
```

Step 3: Configure the Bundle
----------------------------
Check dependencies for their configuration:
- [Swiftmailer](https://github.com/symfony/swiftmailer-bundle)
- [c975LEmailBundle](https://github.com/975L/EmailBundle)

v2.0+ of c975LContactFormBundle uses [c975L/ConfigBundle](https://github.com/975L/ConfigBundle) to manage configuration parameters. Use the Route "/contact/config" with the proper user role to modify them.

**Upgrading from v1.x? Check [UPGRADE.md](UPGRADE.md).**

Step 4: Declaration of Twig_Extensions_Extension_Text
-----------------------------------------------------
You have to config `Twig_Extensions_Extension_Text` in your `app/config/services.yml`, if not already the case, with the following code:

```yml
    twig.text_extension:
        class: Twig_Extensions_Extension_Text
        tags:
            - name: twig.extension
```
Step 5: Enable the Routes
-------------------------
Then, enable the routes by adding them to the `app/config/routing.yml` file of your project:

```yml
c975_l_contact_form:
    resource: "@c975LContactFormBundle/Controller/"
    type:     annotation
    prefix:   /
    #Multilingual website use the following
    #prefix: /{_locale}
    #defaults:   { _locale: '%locale%' }
    #requirements:
    #    _locale: en|fr|es
```

Step 6: Override templates
--------------------------
It is strongly recommended to use the [Override Templates from Third-Party Bundles feature](http://symfony.com/doc/current/templating/overriding.html) to integrate fully with your site.

For this, simply, create the following structure `app/Resources/c975LContactFormBundle/views/` in your app and then duplicate the file `layout.html.twig` in it, to override the existing Bundle file.

In `layout.html.twig`, it will mainly consist to extend your layout and define specific variables, i.e. :
```twig
{% extends 'layout.html.twig' %}

{# Defines specific variables #}
{% set title = 'Contact' %}

{% block content %}
    {% block contactform_content %}
    {% endblock %}
{% endblock %}
```

The template used for sending emails is the one of c975LEmailBundle. Override it in `app/Resources/c975LEmailBundle/views/emails/layout.html.twig`.

How to use
----------
The Route name is `contactform_display` so you can add link in Twig via ̀`{{ path('contactform_display') }}`.

The url path is `/contact` or `/{_locale}/contact`, so simply access to `http://example.com/contact` or `http://example.com/en/contact` to display the form.

You can set the subject by using the url parameter `s` i.e. `http://example.com/contact?s=Subject`, the field will be readonly in the form, **but, of course it can be changed via the url**. The value is sanitized and given (as `subject`) to the form in order to be able to change title and/or info text based on this value, i.e.

```twig
{% if 'Subject' in subject %}
    {# Do some stuff #}
{% endif %}
```

HoneyPot and delay to avoid spam
--------------------------------
To avoid ContactFormBundle serving as an entry point to send spam, the field `username` is a honeypot. It's only displayed to robots and is hidden via css to normal users. If it's filled, then it's not a user, but a bot. There is also a test for the delay used to submit the form. If the form is submitted before the defined delay then it may has not been filled by a human.

For both cases, ContactFormBundle will act as if the mail was sent, but it will not be the case.

Changing infoText
-----------------
You can change the text displayed at the top of the Contact Form with the following code in your overriding template `app/Resources/c975LContactFormBundle/views/layout.html.twig`:

```twig
{% extends 'layout.html.twig' %}

{% set infoText = 'text.contact_info'|trans({'%site%': site}, 'contactForm') %}

{% if YOUR_CONDITION_IS_MET %}
    {% set infoText = 'YOUR_TEXT_TO_DISPLAY' %}
{% endif %}

{% block content %}
    {% block contactform_content %}
    {% endblock %}
{% endblock %}
```

Events dispatch
===============

Disable "Receive copy" checkbox
-------------------------------
You can disable the checkbox to allow user receiving a copy of the email sent, by catching the event `CREATE_FORM` with the following code. It's useful, for example if the contact form is used to contact another user and you want to preserve its email address.
```php
namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use c975L\ContactFormBundle\Event\ContactFormEvent;

class ContactFormSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            ContactFormEvent::CREATE_FORM => 'createForm',
        );
    }

    public function createForm($event)
    {
        //Gets data
        $formData = $event->getFormData();
        $subject = $formData->getSubject();

        //For example, you can check if a string is present in the subject
        if (stripos($subject, 'THE_STRING_YOU_WANT_TO_MATCH_IN_THE_SUBJECT') === 0) {
            $event->setReceiveCopy(false);
        }
    }
}
```

Set specific data in email sent
-------------------------------
In relation with your app specification, it is possible to set specific email data (body, subject, etc.) based on the data sent in form. For this you have to create a listener with the following code:
```php
namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use c975L\ContactFormBundle\Event\ContactFormEvent;

class ContactFormSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            ContactFormEvent::SEND_FORM => 'sendForm',
        );
    }

    public function sendForm($event)
    {
        //Gets data
        $formData = $event->getFormData();
        $subject = $formData->getSubject();

        //For example, you can check if a string is present in the subject
        if (stripos($subject, 'THE_STRING_YOU_WANT_TO_MATCH_IN_THE_SUBJECT') === 0) {
            //Do the stuff...

            //Conditions to send email are met
            if (1 == 1) {
                //Defines data for email
                $bodyEmail = 'YOUR_EMAIL_TEMPLATE.html.twig';
                $bodyData = array(
                     //Any needed data for your template
                    );
                //The following array keys are mandatory, but you can set the other keys defined in c975L\EmailBundle
                $emailData = array(
                    'subject' => 'YOUR_EMAIL_SUBJECT',
                    'bodyData' => $bodyData,
                    'bodyEmail' => $bodyEmail,
                    );

                //Updates event
                $event->setEmailData($emailData);
            //Stop sending by setting an error code, it will create a flash including your error code
            } else {
                $event->setError('YOUR_ERROR_CODE');
            }
        }
    }
}

```

Update redirect url
-------------------
You can update the url to be redirected to, after submission of the form, with the following code:
```php
namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use c975L\ContactFormBundle\Event\ContactFormEvent;

class ContactFormSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            ContactFormEvent::CREATE_FORM => 'createForm',
        );
    }

    public function createForm($event)
    {
        //Updates url to redirect
        $event->getRequest()->getSession()->set('redirectUrl', 'https://example.com');
    }
}
```

**If this project help you to reduce time to develop, you can [buy me a coffee](https://www.buymeacoffee.com/LaurentMarquet) :)**