# UPGRADE

v2.x > v3.x
-----------
`c975LEmailBundle` now use `Symfony\Component\Mailer\MailerInterface`and `Symfony\Component\Mime\Email` which are NOT compatible with Symfony 3.x.

v1.x > v2.x
-----------
When upgrading from v1.x to v2.x you should(must) do the following if they apply to your case:

- The parameters entered in `config.yml` are not used anymore as they are managed by c975L/ConfigBundle, so you can delete them.
- As the parameters are not in `config.yml`, we can't access them via `$this[->container]->getParameter()`, so you have to replace `$this->getParameter('c975_l_contactform.XXX')` by `$configService->getParameter('c975LContactForm.XXX')`, where `$configService` is the injection of `c975L\ConfigBundle\Service\ConfigServiceInterface`.
- Before the first use of parameters, you **MUST** use the console command `php bin/console config:create` to create the config files with default data.
