# Changelog

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