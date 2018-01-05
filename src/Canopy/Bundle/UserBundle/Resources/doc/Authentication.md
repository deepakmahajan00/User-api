# Authentication
================

The authentication mechanism is the way the Symfony application knows which user
he/she is.

To know more on how it works, please take time to learn about it here:
http://symfony.com/doc/current/book/security.html#basic-example-http-authentication

The way the authentication is implemented on this API is inspired by this cookbook
http://symfony.com/doc/current/cookbook/security/api_key_authentication.html


## Authenticator & Provider

In the folder ``src/UserBundle/Security/Authentication``, you will find two classes:
    - ``ApiTokenAuthenticator``, responsible of getting access or not to the user that needs
access to a resource;
    - ``UnboundIDUserProvider``, responsible of fetching the user object from UnboundID.

### Configuration

Those classes are services declared in the ``UserBundle/Resources/config/security.xml``.

By default, the security is activated on the whole application thanks to the following
configuration:
```yaml
security:
    #…
    firewalls:
        secured_area:
            pattern: ^/
            stateless: true
            simple_preauth:
                authenticator: canopy.apitoken_authenticator # This is the service ApiTokenAuthenticator
```

The authenticator must have a provider associated, that's why there is the
following configuration in the ``UserBundle/Resources/config/security.xml`` file:
```yaml
security:
    #…
    providers:
        unboundid:
            id: canopy.unboundid.user_provider
```

### Code explanation

As explained here http://symfony.com/doc/current/cookbook/security/api_key_authentication.html#createtoken,
the first method called by Symfony is ``ApiTokenAuthenticator::createToken()`` method.

Thanks to our implementation, it extracts the ``Authorization`` header that comes
from the request. In this header, there is the credential of the user
sent from the consumer of the API to authenticate the user. The form of the header
must be ``Bearer XXXXXX``.

Then, ``authenticateToken::authenticateToken()`` method is called to validate the token
by calling the REST api from UnboundID.
The method ``UnboundidUserProvider::loadUserByUsername`` is called to get the user:
as you can see in this method, we check if the user is in the local database, if the user
is not found, a 401 (unauthorized) is thrown.

Once it's done, the ``PreAuthenticatedToken`` object
is instanciated and returned with the user got from the database.

Nota Bene: To know more about the model around the User, please refer to the Model.md
documentation.
