Configuration: Check Ignore
=============

The function called before sending payload to Rollbar, return true to stop the error from being sent to Rollbar.

Examples
------------------
Use globally defined function:

.. code-block:: yaml

    symfony_rollbar:
        # ...
        rollbar:
            # ...
            check_ignore: 'function_name_here'

Use custom ``CheckIgnoreProvider`` class that should implements ``InterfaceCheckIgnore``:

.. code-block:: yaml

    symfony_rollbar:
        # ...
        rollbar:
            # ...
            check_ignore: '\SymfonyRollbarBundle\Tests\Fixtures\CheckIgnoreProvider'

Use custom ``CheckIgnoreProvider`` service that class should implements ``InterfaceCheckIgnore``:

.. code-block:: yaml

    symfony_rollbar:
        # ...
        rollbar:
            # ...
            check_ignore: 'awesome_app.rollbar_check_ignore_provider'

