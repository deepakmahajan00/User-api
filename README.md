canopy/user-api
===============

This project is managing the users of CANOPY.
It relies on UnboundID that provides user management (authentication and persistance of users).
This application also stores all users created in Canopy Enterprise Platform in database.

## Requirements

* A web server (Apache, Nginx…)
* PHP >= 5.4
* PostgreSQL >= 9.3.x
* Two databases
	* the *main* one (ex: canopy_user_api)
	* and the one for the *test* envioronment (ex: canopy_user_api_test) - the one for the test environment is always MAINNAME_test

------------------------------------------------------------------------

## Dependencies with other applications

* Media API (<https://bitbucket.org/canopy-cloud/media-api>)
* Dashboard API (<https://bitbucket.org/canopy-cloud/dashboard-api>)
* Catalog API (<https://bitbucket.org/canopy-cloud/catalog-api>)

------------------------------------------------------------------------

## Install with Docker

If you are using Docker, please refer to [this installation procedure](https://bitbucket.org/canopy-cloud/canopy-docker/src/86e68472c37ffc55776021971fd785fe95815d97/README.md?at=master)

## Install

You must install composer globally (<https://getcomposer.org/doc/00-intro.md#globally>) first.

### Step 1

Run the command ``./bin/reset.sh --force``.

This command run the ``composer install`` command, which asks you to fill all the parameters. Fill in those parameters (may be not up to date as the application is evolving):

```database_driver: pdo_pgsql```
> **Database driver**: Use Psql as the application is developed with PostgreSQL technology.

---

```database_host: 127.0.0.1```
> **Database host**: if your database is located somewhere else, the host must be changed here to make sure that Doctrine will be able to contact it.

---

```database_port: 5433```
> **Database port**: port used to contact your database. Make sure that you indicate the right port, so that Doctrine will be able to contact the database.

---

```database_name: canopy_user_api```
> **Database name**: the name of the database can be anything you want.

---

```database_user: postgres```
> **Database user**: Indicate the user that your application can use to perform insertions, deletions, updates and read the database.

---

```database_password: postgres```
> **Database password**: Password used to access the database, associated with the database user.

---

```
mailer_transport: smtp
mailer_host: 127.0.0.1
mailer_user: null
mailer_port: null
```
> **Mailer configuration**: transport, host user and port parameters must be configured if you have a mail server to send emails on your server.
> If you don't have one, you can put the configuration shown above. Don't forget to disable the sending of email by Symfony (<http://symfony.com/doc/current/cookbook/email/dev_environment.html#disabling-sending>).

---

``locale: en``
> **Locale**: This parameter indicates to Symfony wich language to fallback to.

---

``secret: C@nopyS3cr3t``
> **Secret**: This parameter could be anything you want. It is used as a salt for Symfony to generate hash such as CRSF token.

---

``debug_toolbar: true``
> **Debug toolbar**: this parameter can be ``true`` or ``false``. If you set it to `true` the web debug toolbar (the sticky bar of Symfony) is displayed. This parameter is used in the ``app/config/config_dev.yml`` to override the default value (at ``false``). In the other environments, this parameter is set at ``false`` (default value);

---

``debug_redirects: false``
> **Debug redirects**: this parameter can be ``true`` or ``false``. If you set it to ``true`` evrytime there is a redirection made by the application, an intermediary page is displayed with the clickable link of the next page the application needs to go.

---

``use_assetic_controller: true``
> **Use assetic controller**: assetic is an asset manager (pictures, css, javascripts…), this parameter is used to access the assets without dumping them (``app/console assetic:dump`` command).

---

``user_api_endpoint: http://my-user-api-domain-name/app_dev.php/api``
> **User api endpoint**: This parameter is the url to this application.
> The url is the domain name you configured in your vhost/host + the pattern ``/api``.
> You can get rid of the ``app_dev.php``. Use it only if you need the development environment.

---

``mailer_api_endpoint``: %user_api_endpoint%
> **Mailer api endpoint**: This parameter is used to indicate to the application wich url to contact to send emails.
> All email sending is centralized in the user api application. **You must let it as it is until there no new application having the responsability to send email**.

----

``media_api_endpoint: http://my-media-api-domain-name/app_dev.php/api``
> **Media api endpoint**: This parameter is used to indicate to the application which url to contact to persist the media (files).
> You must indicate a working url to access the media api application (this application can be local, on your machine, or anywhere else on internet). The repository of the media api application: <https://bitbucket.org/canopy-cloud/media-api>.
> You can get rid of the ``app_dev.php``. Use it only if you need the development environment.

----

``dashboard_api_endpoint: http://my-dashboard-api-domain-name/app_dev.php/api``
> **Dashboard api endpoint**: This parameter is used to indicate to the application which url to contact for all functionnalities linked to dashboard api (notifications, activities…).
> You must indicate a working url to access the dashboard api application (this application can be local, on your machine, or anywhere else on internet). The repository of the dashboard api application: <https://bitbucket.org/canopy-cloud/dashboard-api>.
> You can get rid of the ``app_dev.php``. Use it only if you need the development environment.

----

``catalog_api_endpoint: http://my-catalog-api-domain-name/app_dev.php/api``
> **Catalog api endpoint**: This parameter is used to indicate to the application which url to contact for all functionnalities linked to catalog api (services…).
> You must indicate a working url to access the catalog api application (this application can be local, on your machine, or anywhere else on internet). The repository of the catalog api application: <https://bitbucket.org/canopy-cloud/catalog-api>.
> You can get rid of the ``app_dev.php``. Use it only if you need the development environment.

----

``unboundid_api_endpoint: 'https://canopy.arnaudlacour.com:9443'``
> **UnboundID endpoint**: This parameter is used to indicate to the application which url to contact UnboundID. This url is pointing to the UnboundID instance installed for the developments.
> You will have to indicate the right one in production environment.

----

``unboundid_api_oauth_endpoint: '%unboundid_api_endpoint%/oauth'``
> **UnboundID oauth endpoint**: This parameter is used to indicate to the application which url to contact to perform all actions related to authentication with UnboundID.
> No need to change it, unless UnboundID change the url.

---

``unboundid_api_dataview_endpoint: '%unboundid_api_endpoint%/dataview'``
> **UnboundID data view endpoint**: This parameter is used to indicate to the application which url to contact to get information regarding a user stored in UnboundID.
> No need to change it, unless UnboundID change the url.

---

```
unboundid_client_api: f0660032-4edc-4492-bb71-87cc454ecd05
unboundid_client_secret: kdPxeMpDqW
```
> **UnboundID client api & UnboundID client secret**: These parameters are used to be able to communicate with UnboundID. This string is given by UnboundID and it is attached to the Oauth application created.
> Do not change it unless you created a new application in UnboundID.

---

``max_elements_per_page: 10``
> **Max elements per page**: This parameter is used in some services in the application to set the number of elements to display when a GET is performed to get a list.

---

``brands_canopy_email_bcc: your@email.com``
> **Canopy email bcc**: You can indicate an email address to receive all emails sent to all users attached to the Nokia company.
> The original receiver won't know that you received it.

---

``brands_nokia-ui_email_bcc: your@email.com``
> **Nokia email bcc**: You can indicate an email address to receive all emails sent to all users attached to all other Canopy company.
> The original receiver won't know that you received it.

---

``canopy_cordys_api_password: CordysC@nOpy1!``
> **Cordys api password**: This password is used to authenticate all calls to Cordys.
> NB: Can be wrong as ``cordys_api_password`` also has the same purpose.

---

``temp_media_path: http://my-user-api-domain-name/app_dev.php/images/logo/``
> **Media path**: Url to the application to get the pictures persisted.

---

``cordys_enabled: true``
> **Cordys enabled**: This parameter enable the real call to Cordys. If it is set to ``false`` no calls are perfomed to Cordys.

---

``cordys_api_endpoint: http://217.115.67.197:2080/api/``
> **Cordys Api endpoint**: Url to contact Cordys.

---

``cordys_api_user: apiuser``
> **Cordys Api user**: This parameter is used to authenticate the call to Cordys.

---

``cordys_api_password: Canopy1!``
> **Cordys Api password**: This password is used to authenticate all calls to Cordys.
> NB: Can be wrong as ``canopy_cordys_api_password`` also has the same purpose.

---

``policy_auto_accepted_days: 30``
> **Policy auto accepted days**: This parameter is used to indicate when the policies are auto accepted.

---
---


If you didn't install the other applications needed (media api, dashboard api and catalog api), you will be able to update the parameters in the ``app/config/parameters.yml``.

### Step 2

Configure your vhost for this application. Here an exemple with apache2:

	<VirtualHost *:8080>
    	ServerName my-user-api-domain-name

    	DocumentRoot /var/www/dev/CEP_V2/user-api/web
    	<Directory /var/www/dev/CEP_V2/user-api/web>
    		Allow from All
    	</Directory>
    </VirtualHost>

### Step 3

Check if the application is up by browsing the ``/api/doc`` url. You should get the following page:
![API Documentation](https://cloud.githubusercontent.com/assets/667519/5646784/13b8cef4-9680-11e4-95dc-86888033006d.png)

------------------------------------------------------------------------

### Environments

Run the ``./bin/reset.sh [env]`` script to install the project.
Where [env] is an optional parameter to choose the environment (if empty, the environment is 'dev').
Here are the available environments:

* ``dev``
* ``test``
* ``prod``

Use ``--force`` option in order to drop and recreate the database instead of updating the schema only.

NB: Fixtures cannot be loaded in dev & prod environments

------------------------------------------------------------------------

## Develop and the API

If you want to test the API's methods, you can use a Chrome's packaged app:

> Postman (<https://chrome.google.com/webstore/detail/postman-rest-client-packa/fhbjgbiflinjbdggehcddcbncdddomop>)

Most of the application is secured by authentication: for each requests, you will have to provide the ``Authorization`` HTTP header:

	Authorization: Bearer XXXXXXXX

NB: The ``XXXXXXXX`` string is a valid access token given by UnbounID.


How to get a valid access token:

1. First, check if you have the wanted profile (you can create a user) on :
<https://canopy.arnaudlacour.com:9443>
2. Then go to this URL with your browser:
	* <https://canopy.arnaudlacour.com:9443/oauth/authorize?response_type=token&client_id=f0660032-4edc-4492-bb71-87cc454ecd05&redirect_uri=http%3A%2Fwww.example.com%2FinternalApplication%2Fredirect&scope=openid%20full_access%20profile%20address%20phone%20ConsumerData%20address%20phone%20Billing>
3. Now, in the URL, you can get the access_token token parameter value.
4. Open the Postman application.
5. Add an header "Authorization" and the value must be ``[access token]``.

Now you can make authenticated API calls.

An access token is limited in time, so you will have to regenerate it.
If you get HTTP response code status ``401 (unauthorized)``, try to generate a new access token by following the steps above (from step #2).

------------------------------------------------------------------------

## CRON

There is a command which update the user's status on policy if there is a new policy to validate.
It must be executed one time a day, preferably in the morning, via a Cron.

To add it (Be careful to be logged as the user that can run the application) :
* crontab -e
* 00 5 * * * cd APPLICATION/PATH;app/console canopy:policy:update --env=ENV

APPLICATION/PATH : the path to the application's folder
ENV : name of the current environment (ex : prod)

------------------------------------------------------------------------

## Tests

Tests are implemented with PHPUnit.

```sh
bin/test.sh [env]
```

``[env]`` is the environment, if empty, the environment is 'test' by default.
Use ``--force`` option in order to drop and recreate the database instead of the schema only.
