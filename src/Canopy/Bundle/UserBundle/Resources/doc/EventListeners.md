# EventListeners
================

## UserListener

This event subscriber is responsible of sending an email to the user that just been created,
a call is done to the dashboard-api to create a notification.

It listens to the event ``postPersist`` of Doctrine (fired right after the flush of
a transaction, for insertions, not updates).

The postLoad event is called after the user is fetched from the database. It adds permissions to user depending on its roles. NB: there is no doctrine/sql relation between user and permissions

## ConstraintViolationListControllerListener

This event subscriber is responsible of the formating of the errors when it comes from a validation (Validator component) done by the ``fos_rest.body_converter``.

That way, when a validation is performed, the error always looks like the following:

	{
 	    "code":400,
 	    "message":"Validation Failed",
 	    "errors":[
 	    {
 		    "property_path":"custom_name",
 	        "message":"This value should not be blank."
 	        },
 	        {
 	            "property_path":"configurations[premium-support]",
 	            "message":"This field is missing."
 	        }
 	    ]
 	}


## AuthenticationHeaderListener

This event subscriber makes sure that the ``Authorization`` header is added to the request object.