# UPGRADE

## v7.17.9

`contact-form-delay` and `contact-form-gdpr` are replaced by the shared `site-form-delay` and `site-form-gdpr` config keys, so every public form across the c975L ecosystem (contact, registration, password reset...) shares one setting instead of one per bundle. Both keys are still seeded even if `c975l/site-bundle` isn't installed. The seeded default for the delay also changes from `7` to `3` seconds; the GDPR default stays `true`.

If you had customized `contact-form-delay` or `contact-form-gdpr` in the dashboard, that value is no longer read - re-apply it to `site-form-delay`/`site-form-gdpr` after upgrading. The old rows are left untouched in the database (not deleted), you can remove them manually from the dashboard.

The GDPR consent checkbox, when enabled, is now also enforced server-side (`Assert\IsTrue`) - `required => true` alone was HTML5-only and let an unchecked box through. If anything submits this form without checking it (a custom JS flow, an automated test), that submission is now rejected.

## v5.x > v6.x

Changed compatibility to PHP 8

## v4.x > v5.x

Changed compatibility to Symfony 6

## v3.x > v4.x

Changed `localizeddate` to `format_datetime`

## v2.x > v3.x

`c975LEmailBundle` now use `Symfony\Component\Mailer\MailerInterface`and `Symfony\Component\Mime\Email` which are NOT compatible with Symfony 3.x.

## v1.x > v2.x

When upgrading from v1.x to v2.x you should(must) do the following if they apply to your case:

- The parameters entered in `config.yml` are not used anymore as they are managed by c975L/ConfigBundle, so you can delete them.
- As the parameters are not in `config.yml`, we can't access them via `$this[->container]->getParameter()`, so you have to replace `$this->getParameter('c975_l_contactform.XXX')` by `$configService->getParameter('c975LContactForm.XXX')`, where `$configService` is the injection of `c975L\ConfigBundle\Service\ConfigServiceInterface`.
- Before the first use of parameters, you **MUST** use the console command `php bin/console config:create` to create the config files with default data.
