Configuration: Custom Data Method
=============

Allows creating dynamic custom data on runtime during error reporting.

Examples
------------------
Use globally defined function:

.. code-block:: yaml

    symfony_rollbar:
        # ...
        rollbar:
            # ...
            custom_data_method: 'custom_data_funcion'

Use custom ``CustomDataProvider`` class that should implements ``InterfaceCustomData``:

.. code-block:: yaml

    symfony_rollbar:
        # ...
        rollbar:
            # ...
            custom_data_method: '\SymfonyRollbarBundle\Tests\Fixtures\CustomDataProvider'

Use custom ``CustomDataProvider`` service that class should implements ``InterfaceCustomData``:

.. code-block:: yaml

    symfony_rollbar:
        # ...
        rollbar:
            # ...
            custom_data_method: 'awesome_app.rollbar_custom_data_method'
