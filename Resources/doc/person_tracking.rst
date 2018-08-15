Configuration: Person Tracking
=============

There are few of the options that can be used to track user:

``person_fn``: A function reference (string, etc. - anything that `call_user_func()`_ can handle) returning an array like the one for 'person'.

Use globally defined function:

.. code-block:: yaml

    symfony_rollbar:
        # ...
        rollbar:
            # ...
            person_fn: 'function_name_here'

Use custom ``PersonProvider`` class that should implements ``InterfacePersonProvider``:

.. code-block:: yaml

    symfony_rollbar:
        # ...
        rollbar:
            # ...
            person_fn: '\SymfonyRollbarBundle\Tests\Fixtures\PersonProvider'

Use custom ``PersonProvider`` service that class should implements ``InterfacePersonProvider``:

.. code-block:: yaml

    symfony_rollbar:
        # ...
        rollbar:
            # ...
            person_fn: 'awesome_app.rollbar_person_provider'

Than in your ``PersonProvider`` class/service or function you have to return user data as array:

.. code-block:: php
    // ..
    return [
        'id'       => 'user_id',
        'username' => 'username',
        'email'    => 'email',
    ];

.. _`call_user_func()`: http://php.net/call_user_func
