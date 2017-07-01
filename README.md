ContactFormBundle
=================

ContactFormBundle does the following:

- Display a form to contact a website,
- Pre-fills data if user is logged in,
- Sends the email via [c975LEmailBundle](https://github.com/975L/EmailBundle) as `c975LEmailBundle` provides the possibility to save emails in a database, there is an option to NOT do so via this Bundle,
- Sends a copy to the email provided.

[ContactForm Bundle dedicated web page](https://975l.com/en/pages/contact-form-bundle).

Bundle installation
===================

Step 1: Download the Bundle
---------------------------
Add the following to your `composer.json > require section`
```
"require": {
    ...
    "c975L/contactform-bundle": "1.*"
},
```
Then open a command console, enter your project directory and update composer, by executing the following command, to download the latest stable version of this bundle:

```bash
$ composer update
```

This command requires you to have Composer installed globally, as explained in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

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
            new c975L\ContactFormBundle\c975LContactFormBundle(),
        ];

        // ...
    }

    // ...
}
```

Step 3: Configure the Bundle
----------------------------

Then, in the `app/config.yml` file of your project, define `site` as the name of the website that will appear on the form, `sentTo` as the email address that will receive the email, `database` as `true|false` if you wish to save the emails in a database MySql, see [c975LEmailBundle](https://github.com/975L/EmailBundle) to setup the corresponding table.

```yml
#app/config/config.yml

c975_l_contact_form:
    site: 'example.com'
    sentTo: 'contact@example.com'
    database: false
```

Step 4: Enable the Routes
-------------------------

Then, enable the routes by adding them to the `app/config/routing.yml` file of your project:

```yml
#app/config/routing.yml

...
c975_l_contact_form:
    resource: "@c975LContactFormBundle/Controller/"
    type:     annotation
    #Multilingual website use: prefix: /{_locale}
    prefix:   /
```

Step 5: Override templates
--------------------------

It is strongly recommend to use the [Override Templates from Third-Party Bundles feature](http://symfony.com/doc/current/templating/overriding.html) to integrate fully with your site.

For this, simply, create the following structure `app/Resources/c975LContactFormBundle/views/` in your app and then duplicate the file `layout.html.twig` in it, to override the existing Bundle file.

In the overridding file, just add `{% block contactform_content %}{% endblock %}` where you want the form to appear.

You may also want to override the template used for building the email sent, simply add a folder `emails` in the preceeding structure and simply keep `{% block contactform_content %}{% endblock %}` to have the content.

Step 6: How to use
------------------

The Route name is `contactform_display` so you can add link in Twig via ̀`{{ path('contactform_display') }}`.

The url path is `/contact` (`/{_locale}/contact`), so simply access to `http://example.com/contact` to display the form or `http://example.com/en/contact`.
