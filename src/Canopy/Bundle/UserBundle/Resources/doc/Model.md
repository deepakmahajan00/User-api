# Model
=======

The model around the user is centralized in the UserBundle.

Note that the users are stored in UnboundID and in a local database because the
logic needed for the Canopy Enterprise Store needs more table than given by default
by UnboundID.
Once it will be possible, UnboundID will persist all the information in its database.

The model is in the folder ``UserBundle/Entity``.

Here is a schema of the model: ![user-model](http://bit.ly/1JXz1MD)

## Prototype Group

A prototype group is a default group used to help a user during the creation of
a group in an organisation.

Those groups are not meant to be attached to any user. It just a set of permissions
by default.

All prototype groups are currently in fixtures. The goal (not) is to allow an
organisation to create their own with a set of permissions corresponding to their needs.

## Permissions

The permission can be seen as feature flags (http://marc.weistroff.net/2012/01/09/simple-feature-flags-symfony2):
A set of permissions are in fixtures. Those permissions were applied in the version
1.0 of Canopy Enterprise Store.

A permission can be linked to a protoype group or an actual group.

Nota Bene: To know more about the authorization through permissions, please read
the Authorisation.md documentation.

## Organisation

An organisation represents a company. An organisation has groups. A user can be attached
to an organisation.

## User

A user is the main entity in this bundle. All users in the database are also in UnboundID.

The field ``fromCompany`` is used to brand the emails / the dashboard-ui for the
user: for instance, the user has a ``fromCompany=nokia``. This user will receive
all emails branded with the Nokia Logo, and see the dashboard with the Nokia logo.

## Currency

This is linked to a user but not used in the code for now.
