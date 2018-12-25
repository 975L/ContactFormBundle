# Changelog

v2.0.4
------
- Added rector to composer dev part (23/12/2018)
- Modified required versions in composer (23/12/2018)

v2.0.3
------
- Corrected `UPGRADE.md` for `php bin/console config:create` (03/12/2018)
- Made use of parameter `c975LCommon.site` in place of `c975LContactForm.site` (03/12/2018)

v2.0.2
------
- Updated `README.md` (31/08/2018)
- Updated `UPGRADE.md` (01/09/2018)
- Updated composer.json (01/09/2018)
- Added check if site is defined (02/09/2018)

v2.0.1
------
- Fixed `UPGRADE.md` (31/08/2018)
- Fixed `bundle.yaml` (31/08/2018)

v2.0
----
**Upgrading from v1.x? Check UPGRADE.md**
- Created branch 1.x (30/08/2018)
- Made use of c975L/ConfigBundle (30/08/2018)
- Added Route `contactform_config` (30/08/2018)
- Removed declaration of parameters in Configuration class as they are end-user parameters and defined in c975L/ConfigBundle (30/08/2018)
- Added `bundle.yaml` (30/08/2018)
- Updated `README.md` (30/08/2018)
- Added `UPGRADE.md` (30/08/2018)
- Added `ContactFormVoter.php` (30/08/2018)
- Added toolbar (30/08/2018)
- Added Route `contactform_dashboard` (30/08/2018)


v1.x
====

v1.10.5
-------
- Fixed missing break (30/08/2018)

v1.10.4.5
---------
- Fixed file access rights (30/08/2018)

v1.10.4.4
---------
- Used a `switch()` for the FormFactory more readable (27/08/2018)

v1.10.4.3
---------
- Moved Creation of flash from ContactFormEmail `send()` to ContactForFormService `sendMail()` (27/08/2018)

v1.10.4.2
---------
- Added missing property in ContactFormFactory (27/08/2018)

v1.10.4.1
---------
- Renamed "contactFormConfig" to "config" for ContactFormType (27/08/2018)

v1.10.4
-------
- Added ContactFormFactory + Interface (27/08/2018)

v1.10.3
-------
- Removed 'true ===' as not needed (25/08/2018)
- Added dependency on "c975l/config-bundle" and "c975l/services-bundle" (26/08/2018)
- Removed un-needed translations (26/08/2018)
- Moved `isNotBot()` method  to `ContactFormService` (26/08/2018)
- Removed un-needed Services (26/08/2018)

v1.10.2.2
---------
- Removed unused use calls (22/08/2018)

v1.10.2.1
---------
- Defined ContactForm form config as an array (to be coherent with other bundles developed by 975L) (22/08/2018)

v1.10.2
-------
- Moved send Email logic from controller to ContactFormService (22/08/2018) Thanks [Kalpesh Mahida](https://github.com/kalpeshmahida) :)
- Made delay to test bot submission as an optional config value (22/08/2018)

v1.10.1.2
---------
- Added extra-information for documentation (21/08/2018)

v1.10.1.1
---------
- Removed FQCN in docblocks (21/08/2018)
- Added information for documentation (21/08/2018)

v1.10.1
-------
- Removed ContactFormDispatcher + Interface (20/08/2018)
- Removed FQCN and made use of "use" (20/08/2018)
- Removed injection of Request (20/08/2018)

v1.10
-----
- Made use of EmailServiceInterface (04/08/2018)
- Added link to BuyMeCoffee (19/08/2018)
- Added IP field display (19/08/2018)
- Added phpdoc (19/08/2018)
- Added link to apidoc (19/08/2018)
- Injected TranslatorInterface (19/08/2018)
- Split ContactFormService in multiple Services for SOLID compliance (20/08/2018)
- Created related services interfaces (20/08/2018)

v1.9.5.1
--------
- Corrected `README.md` to indicate EventSubscriber in place of Listener (01/08/2018)
- Corrected Events dispatch (01/08/2018)

v1.9.5
------
- Updated `README.md` (31/07/2018)
- Moved `defineReferer()` call at the top of `ContactFormController->display()` (31/07/2018)

v1.9.4
------
- Added `Request` to Event (30/07/2018)

v1.9.3
------
- Corrected https://github.com/975L/ContactFormBundle/issues/4, by adding Twig_Extensions (30/07/2018)

v1.9.2
------
- Added honeypot to avoid spam (27/07/2018)
- Added test delay before submission to avoid spam (27/07/2018)

v1.9.1
------
- Injected EmailService (21/07/2018)

v1.9
----
- Added `ContactFormService.php` to make `ContactFormController.php` more SOLID compliant (21/07/2018)

v1.8.3
------
- Removed Bootstrap javascript request as not needed (21/07/2018)
- Corrected gdpr checkbox display as it was disaplyed even if false is set (21/07/2018)
- Removed `SubmitType` in FormType and replaced by adding button in template as it's not a "Best Practice" (Revert of v1.8.1) (21/07/2018)
- Removed `Action` in controller method name as not requested anymore (21/07/2018)
- Renamed variable `$contact` to `$contactForm` in Controller (21/07/2018)
- Corrected meta in `layout.html.twig` (21/07/2018)

v1.8.2.1
--------
- Removed required in composer.json (22/05/2018)

v1.8.2
------
- Added GDPR agreement (17/05/2018)
- Modified presentation of email sent (17/05/2018)

v1.8.1
------
- Replaced submit button by `SubmitType` (16/04/2018)
- Added info in `README.md` about changing `infotext` (16/04/2018)

v1.8
----
- Corrected `Resources\views\emails\contact.html.twig` as it was not not including the message sent... (30/03/2018)
- Added property in event to retrieve error (30/03/2018)
- Added flash if email has not been sent by c975L/EmailBundle, as it now returns a boolean (30/03/2018)
- Added checkbox to allow user receiving copy of message sent, in place of always sending it (31/03/2018)
- Added dispatch event `CREATE_FORM` to allow disabling display of receiving copy checkbox (31/03/2018)

v1.7.2
------
- Changed submit icon (20/03/2018)
- Updated `README.md` (21/03/2018)

v1.7.1
------
- Updated `README.md` (18/03/2018)

v1.7
----
- Removed the possibility to override Controller as it's not supported anymore (18/03/2018)
- Converted `testSuject()` method to event dispatch (18/03/2018)

v1.6.2
------
- Added "_locale requirement" part for multilingual prefix in `routing.yml` in `README.md` (04/03/2018)
- Suppressed site + email info sent from Controller for c975L/EmailBundle as theyr are set in Twig overriding `layout.html.twig` (17/03/2018)

v1.6.1.1
--------
- Corrected Controller (03/03/2018)

v1.6.1
------
- Corrected Controller (28/02/2018)

v1.6
----
- Changes in `README.md` (16/08/2017)
- Change about composer download in `README.md` (04/02/2018)
- Add support in `composer.json`+ use of ^ for versions request (04/02/2018)
- Updated  `README.md` (05/02/2018)
- Removed email layout and styles to use those defined in c975L\EmailBundle (22/02/2018)
- Abandoned Glyphicon and replaced by fontawesome (22/02/2018)
- Removed method setDataFromArray() in Entity (22/02/2018)
- Added c957L/IncludeLibrary to include libraries in layout.html.twig (27/02/2018)
- Removed assetic bundle from composer (27/02/2018)

v1.5.4
------
- Change to 256 the max size of the subject (20/07/2017)
- Change to 128 the max size of the name

v1.5.3
------
- Run PHP CS-Fixer (18/07/2017)
- Remove of .travis.yml as tests have to be defined before

v1.5.2
------
- Add of `$request` in call of `testSubject()` method (15/07/2017)

v1.5.1
------
- Add of Request in `testSubject()` Controller's method (15/07/2017)

v1.5
----
- Update of README.md (04/07/2017)
- Update of c975L/EmailBundle, send email as service (15/07/2017)

v1.4
----
- Add explanations about setting the subject via the url (02/07/2017)
- Add sanitizing on subject set via url parameter `s`
- Set the default text explanation above the form as a Twig variable, in order to be able to change it depending on the subject value
- Add possibility to choose email data (body, subject etc.) from Controller, related to subject + explanations to override function from own Controller

v1.3
----
- Change route naming to contactform_display (01/07/2017)
- Add information about multilingual website

v1.2.1
------
- Add of translation domain to ContactFormType.php (20/06/2017)

v1.2
----
- Add of validators.XX.xlf (20/06/2017)
- Add of translation domain to avoid other texts coming from other bundle with same keyword

v1.1.1
------
- Correction in ContactFormController.php (05/06/2017)

v1.1
----
- Add of code files (05/06/2017)

v1.0
----
- Creation of bundle (05/06/2017)
