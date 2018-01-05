# Authorisation
===============

The authorisation mecanism in Symfony is explained here:
http://symfony.com/doc/current/book/security.html#authorization

The authorisation according to logic needed for the Canopy Entreprise Store
can be implemented in many ways: the way we recommend is to use voters.

A voter is a service allowing the developper to do this kind of thing:
http://symfony.com/doc/current/cookbook/security/voters_data_permission.html#how-to-use-the-voter-in-a-controller

You can implement any logic in the ``XxxxVoter::vote()`` then call the method 
``$this->isGranted('ROLE_XXX')`` (you need to extend the ``Symfony\Bundle\FrameworkBundle\Controller\Controller`` class).

NB: Never call the voter service directly. By calling the ``security.authorization_checker``
service, you actually call all voters. This is really important because one voter
could vote for granting access and another one could vote for denying it. That way,
you make sure that the needs coming from the management keep being maintainable.

There is an example of implementation of a simple voter in file ``UserBunde/Security/Authorisation/GroupVoter.php``.
It is used in the ``GroupController::getGroupAction()`` with the following code:
```php
if (!$this->isGranted('VIEW_GROUP', $group)) {
    throw $this->createAccessDeniedException('You are not authorized to see this group.');
}
```
