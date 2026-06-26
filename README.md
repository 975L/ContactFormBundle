# ContactFormBundle

A Symfony bundle that provides a fully-featured contact form with built-in spam protection, reCaptcha v3, rate limiting, event-driven customization, and multilingual support.

[![GitHub](https://img.shields.io/github/license/975L/ContactFormBundle)](https://github.com/975L/ContactFormBundle/blob/master/LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/c975l/contactform-bundle)](https://packagist.org/packages/c975l/contactform-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/c975l/contactform-bundle)](https://packagist.org/packages/c975l/contactform-bundle)

## Features

- Displays a contact form at `/contact` (or `/{_locale}/contact` for multilingual apps)
- Pre-fills name and email when a user is logged in
- Sends emails via Symfony Mailer (`TemplatedEmail`)
- Dispatches events to customize form behavior and email content
- Supports sending emails to other users without exposing their address (via event)
- **Anti-spam:** dynamic honeypot with randomized field names and labels per session
- **Anti-spam:** minimum submission delay check to reject bot submissions
- **Anti-spam:** reCaptcha v3 via [`karser/KarserRecaptcha3Bundle`](https://github.com/karser/KarserRecaptcha3Bundle)
- **Rate limiting:** optional server-side limits by IP and by email address
- GDPR consent checkbox (configurable)
- Optional "receive a copy" checkbox for the sender
- Subject pre-fill via URL parameter (`?s=My+Subject`)
- Configuration managed via [`c975L/ConfigBundle`](https://github.com/975L/ConfigBundle)

## Requirements

- PHP >= 8.0
- Symfony >= 7.0
- [`c975l/config-bundle`](https://github.com/975L/ConfigBundle)
- [`c975l/site-bundle`](https://github.com/975L/SiteBundle)
- [`karser/karser-recaptcha3-bundle`](https://github.com/karser/KarserRecaptcha3Bundle)

## Installation

### 1. Download the bundle

```bash
composer require c975l/contactform-bundle
```

### 2. Enable the routes

Add the following to your `/config/routes.yaml`:

```yaml
c975_l_contact_form:
    resource: "@c975LContactFormBundle/"
    type: attribute
    prefix: /
    # For multilingual websites:
    # prefix: /{_locale}
    # defaults: { _locale: '%locale%' }
    # requirements:
    #     _locale: en|fr|es
```

### 3. Load configuration values

This bundle uses [`c975L/ConfigBundle`](https://github.com/975L/ConfigBundle) to store its settings in the database. After installing it, run:

```bash
php bin/console c975l:config:load 'vendor/c975l/contactform-bundle/config/configs.json'
```

Then use the ConfigBundle dashboard to set the values for each key.

| Key | Description | Default |
| --- | --- | --- |
| `email-from` | Sender email address (e.g. `contact@example.com`) | — |
| `email-from-name` | Sender display name | — |
| `email-to` | Recipient email address | — |
| `email-to-name` | Recipient display name | — |
| `email-reply-to` | Reply-to email address | — |
| `email-reply-to-name` | Reply-to display name | — |
| `contact-form-delay` | Minimum seconds before a submission is accepted (bot check) | `7` |
| `contact-form-gdpr` | Show GDPR consent checkbox | `true` |
| `recaptcha3-site-key` | Google reCaptcha v3 site key | — |
| `recaptcha3-secret-key` | Google reCaptcha v3 secret key | — |

### 4. Configure reCaptcha v3

Create your keys on [Google reCaptcha](https://www.google.com/recaptcha) and set them either in your `.env.local`:

```env
RECAPTCHA3_KEY=your_site_key
RECAPTCHA3_SECRET=your_secret_key
```

Or store them via the ConfigBundle dashboard (the `recaptcha3-site-key` and `recaptcha3-secret-key` config keys above).

### 5. Override the layout template

It is strongly recommended to use [Symfony's template override feature](https://symfony.com/doc/current/bundles/override.html) to integrate the bundle with your application's design.

Create `/templates/bundles/c975LContactFormBundle/layout.html.twig` and extend your own layout:

```twig
{% extends 'layout.html.twig' %}

{% set title = 'Contact' %}

{% block content %}
    {% block contactform_content %}
    {% endblock %}
{% endblock %}
```

The email templates are loaded from `c975LSiteBundle`. Override them in `/templates/c975LSiteBundle/emails/`.

## Spam Protection

### Dynamic honeypot

A hidden field is injected into the form on each session. Its name and label are randomly generated (e.g. `account_data` labeled "Job title", `person_field` labeled "Phone number"). The field is hidden from real users via CSS. If it is filled, the request is silently discarded — the form appears to succeed, but no email is sent.

> **Note:** If you have disabled `unsafe-inline` for `style-src` in your Content Security Policy, add the following rule to your stylesheet to keep the honeypot hidden:
>
> ```css
> .sr-only {
>     position: absolute;
>     left: -9999px;
>     width: 1px;
>     height: 1px;
>     opacity: 0;
>     pointer-events: none;
> }
> ```

### Submission delay

If the form is submitted in less than `contact-form-delay` seconds (default: 7 s), it is treated as a bot submission and silently discarded.

### Rate limiting (optional)

If the following Symfony RateLimiter services are defined in your application, they are automatically applied before any email is sent:

- `limiter.contact_form_by_ip`
- `limiter.contact_form_by_email`

Example configuration (`config/packages/rate_limiter.yaml`):

```yaml
framework:
    rate_limiter:
        contact_form_by_ip:
            policy: sliding_window
            limit: 5
            interval: '10 minutes'

        contact_form_by_email:
            policy: sliding_window
            limit: 3
            interval: '10 minutes'
```

When a limit is exceeded, the email is not sent and a warning flash message is displayed. The `symfony/rate-limiter` package is optional — if the services are absent, no limiting is applied.

## Usage

The route name is `contactform_display`. Link to it from Twig with:

```twig
{{ path('contactform_display') }}
```

The form is available at `http://example.com/contact` (or `http://example.com/en/contact` for localized routes).

### Pre-filling the subject

Pass the `s` query parameter to pre-fill the subject field (the field is rendered as read-only):

```text
http://example.com/contact?s=My+Subject
```

The sanitized value is also available in your overriding template as the `subject` variable, so you can conditionally change the displayed text:

```twig
{% if 'My+Subject' in subject %}
    {# Adjust title or info text #}
{% endif %}
```

### Changing the info text

Override `infoText` in your layout template:

```twig
{% extends 'layout.html.twig' %}

{% set infoText = 'text.contact_info'|trans({'%site%': site}, 'contactForm') %}

{% if your_condition %}
    {% set infoText = 'Custom text to display above the form' %}
{% endif %}

{% block content %}
    {% block contactform_content %}
    {% endblock %}
{% endblock %}
```

## Events

ContactFormBundle dispatches two events that allow you to customize its behavior without modifying the bundle itself.

| Constant | Event name | Fired when |
| --- | --- | --- |
| `ContactFormEvent::CREATE_FORM` | `c975l_contactform.create.form` | The form is being built |
| `ContactFormEvent::SEND_FORM` | `c975l_contactform.send.form` | The form has been submitted and validated |

### Disable the "Receive a copy" checkbox

```php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use c975L\ContactFormBundle\Event\ContactFormEvent;

class ContactFormSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ContactFormEvent::CREATE_FORM => 'onCreateForm',
        ];
    }

    public function onCreateForm(ContactFormEvent $event): void
    {
        $subject = $event->getFormData()->getSubject();

        if (str_contains((string) $subject, 'some-keyword')) {
            $event->setReceiveCopy(false);
        }
    }
}
```

### Customize email data on submission

Use the `SEND_FORM` event to override the email subject, body template, or any other data. You can also set an error code to abort sending:

```php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use c975L\ContactFormBundle\Event\ContactFormEvent;

class ContactFormSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ContactFormEvent::SEND_FORM => 'onSendForm',
        ];
    }

    public function onSendForm(ContactFormEvent $event): void
    {
        $subject = $event->getFormData()->getSubject();

        if (str_contains((string) $subject, 'some-keyword')) {
            // Conditions are met — override email data
            $event->setEmailData([
                'subject'   => 'Custom email subject',
                'bodyEmail' => 'emails/custom_contact.html.twig',
                'bodyData'  => [
                    // Any data your template needs
                ],
            ]);

            // Or abort sending with an error code:
            // $event->setError('error.user_not_found');
        }
    }
}
```

### Override the redirect URL after submission

Set `redirectUrl` on the session inside the `CREATE_FORM` listener:

```php
public function onCreateForm(ContactFormEvent $event): void
{
    $event->getRequest()->getSession()->set('redirectUrl', 'https://example.com/thank-you');
}
```

## License

MIT — see the [LICENSE](LICENSE) file for details.

---

If this bundle **saves you development time**, consider sponsoring via the **Sponsor** button at the top of the repository.
