# ContactFormBundle

Symfony bundle that provides a fully-featured contact form with built-in spam protection, reCaptcha v3, rate limiting, event-driven customization, and multilingual support.

[![GitHub](https://img.shields.io/github/license/975L/ContactFormBundle)](https://github.com/975L/ContactFormBundle/blob/master/LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/c975l/contactform-bundle)](https://packagist.org/packages/c975l/contactform-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/c975l/contactform-bundle)](https://packagist.org/packages/c975l/contactform-bundle)

---

> [!WARNING]
> **This bundle is no longer maintained.** Everything it provided - custom fields, honeypot/rate-limiting/reCaptcha anti-spam, GDPR consent, "receive a copy", and email sending - is now available generically in [c975L/UiBundle](https://github.com/975L/UiBundle) (`Form`/`FormField` entities, the `form` Block, `FormPrefillHelper` for subject-style pre-filling) together with [c975L/SiteBundle](https://github.com/975L/SiteBundle). It still works as-is and existing installs are unaffected, but no new features or fixes are planned - new projects should build their contact form with UiBundle's generic Form system instead.

---

## Features

- Contact form at `/contact` (or `/{_locale}/contact` for multilingual apps)
- Pre-fills name and email when a user is logged in
- Sends emails via Symfony Mailer (`TemplatedEmail`)
- Dispatches events to customize form behavior and email content
- **Anti-spam:** dynamic honeypot with randomized field names and labels per session
- **Anti-spam:** minimum submission delay check to reject bot submissions
- **Anti-spam:** reCaptcha v3 via [`karser/KarserRecaptcha3Bundle`](https://github.com/karser/KarserRecaptcha3Bundle)
- **Rate limiting:** optional limits by IP and by email address
- GDPR consent checkbox (configurable)
- Optional "receive a copy" checkbox for the sender
- Subject pre-fill via URL parameter (`?s=My+Subject`)
- Configuration managed via [c975L/ConfigBundle](https://github.com/975L/ConfigBundle)
- Selectable as a navbar/footer menu item in [c975L/SiteBundle](https://github.com/975L/SiteBundle) (via `LinkableRouteProvider`), with no dependency on SiteBundle itself

---

## Requirements

- PHP >= 8.0
- Symfony >= 7.0
- [c975L/ConfigBundle](https://github.com/975L/ConfigBundle)
- [c975L/SiteBundle](https://github.com/975L/SiteBundle)
- [karser/karser-recaptcha3-bundle](https://github.com/karser/KarserRecaptcha3Bundle)

---

## Installation

### Download

```bash
composer require c975l/contactform-bundle
```

### Enable routes

Add the following to `config/routes.yaml`:

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

### Load configuration values

```bash
php bin/console c975l:config:load-all
```

Then use the ConfigBundle dashboard to set the values for each key.

### Configure reCaptcha v3

Create your keys on [Google reCaptcha](https://www.google.com/recaptcha) and set them in your `.env.local`:

```env
RECAPTCHA3_KEY=your_site_key
RECAPTCHA3_SECRET=your_secret_key
```

Or store them via the ConfigBundle dashboard (`recaptcha3-site-key` and `recaptcha3-secret-key`).

### Override the layout template

Create `templates/bundles/c975LContactFormBundle/layout.html.twig` and extend your own layout:

```twig
{% extends 'layout.html.twig' %}

{% set title = 'Contact' %}

{% block content %}
    {% block contactform_content %}
    {% endblock %}
{% endblock %}
```

The email templates are loaded from SiteBundle. Override them in `templates/c975LSiteBundle/emails/`.

---

## Usage

The route name is `contactform_display`. Link to it from Twig:

```twig
{{ path('contactform_display') }}
```

### Pre-filling the subject

Pass the `s` query parameter to pre-fill the subject field (rendered as read-only):

```text
https://example.com/contact?s=My+Subject
```

### Anti-bot delay and GDPR checkbox

Both are driven by two config keys shared with the rest of the c975L ecosystem (e.g. c975l/site-bundle's scaffolded registration/reset-password forms), so every public form agrees on one setting instead of one per bundle. Seeded by this bundle's own `config/configs.json` too, so they exist even without c975l/site-bundle installed:

- `site-form-delay` (int, seconds, default `3`) - below this delay between displaying and submitting the form, the submission is treated as a bot and silently dropped.
- `site-form-gdpr` (bool, default `true`) - shows the mandatory GDPR consent checkbox.

> Renamed from `contact-form-delay`/`contact-form-gdpr` - see UPGRADE.md if you had customized either.

### Rate limiting (optional)

If the following Symfony RateLimiter services are defined, they are automatically applied before any email is sent:

- `limiter.contact_form_by_ip`
- `limiter.contact_form_by_email`

Example (`config/packages/rate_limiter.yaml`):

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

### Honeypot and CSP

If you have disabled `unsafe-inline` for `style-src` in your Content Security Policy, add this rule to keep the honeypot hidden:

```css
.sr-only {
    position: absolute;
    left: -9999px;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
}
```

---

## Events

Two events allow customization without modifying the bundle:

| Constant | Event name | Fired when |
| --- | --- | --- |
| `ContactFormEvent::CREATE_FORM` | `c975l_contactform.create.form` | The form is being built |
| `ContactFormEvent::SEND_FORM` | `c975l_contactform.send.form` | The form has been submitted and validated |

### Example: customize email on submission

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
            $event->setEmailData([
                'subject'   => 'Custom email subject',
                'bodyEmail' => 'emails/custom_contact.html.twig',
                'bodyData'  => [],
            ]);

            // Or abort sending with an error code:
            // $event->setError('error.user_not_found');
        }
    }
}
```

### Override the redirect URL after submission

```php
public function onCreateForm(ContactFormEvent $event): void
{
    $event->getRequest()->getSession()->set('redirectUrl', 'https://example.com/thank-you');
}
```

---

> [!TIP]
> If this bundle **saves you development time**:
>
> - [**star** it on GitHub](https://github.com/975L/ContactFormBundle) — helps others find it
> - [**open an issue**](https://github.com/975L/ContactFormBundle/issues/new) to share how you use it — genuinely useful feedback
>
> And if you'd like to support the work directly, the **Sponsor** button at the top of the repository is there for that. Thank you!
