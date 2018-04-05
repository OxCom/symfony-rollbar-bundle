Configuration
=============

Simple configuration of bundle:

.. code-block:: yaml

    symfony_rollbar:
        enable: true
        exclude:
            - \Symfony\Component\Debug\Exception\FatalErrorException
        rollbar:
            access_token: 'some-secret-token-here'
            environment: '%kernel.environment%'
            agent_log_location: '%kernel.logs_dir%/rollbar.log'
            allow_exec: true
            endpoint: 'https://api.rollbar.com/api/1/'
            base_api_url: 'https://api.rollbar.com/api/1/'
            branch: 'master'
            capture_error_stacktraces: true
            checkIgnore: null
            code_version: ''
            enable_utf8_sanitization': true
            environment: 'production'
            custom: []
            error_sample_rates: []
            exception_sample_rates: []
            fluent_host: '127.0.0.1'
            fluent_port: 24224
            fluent_tag: 'rollbar'
            handler: 'blocking'
            host: null
            include_error_code_context: false
            include_exception_code_context: false
            included_errno: E_ERROR | E_WARNING | E_PARSE | E_CORE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR
            logger: null
            person: []
            person_fn: null
            root: '%kernel.root_dir%'
            scrub_fields: ['passwd', 'password', 'secret', 'confirm_password', 'password_confirmation', 'auth_token', 'csrf_token']
            scrub_whitelist: null
            shift_function': true
            timeout: 3
            report_suppressed: false
            use_error_reporting: false
            proxy: null
            send_message_trace: false
            include_raw_request_body: false
            local_vars_dump: false
        rollbar_js:
            enabled: true
            accessToken: 'some-public-token'
            captureUncaught: true
            uncaughtErrorLevel: 'error'
            captureUnhandledRejections: true
            payload:
                environment: environment: '%kernel.environment%'
            ignoredMessages: []
            verbose: false
            async: true
            autoInstrument:
                network: true
                log: true
                dom: true
                navigation: true
                connectivity: true
            itemsPerMinute: 60
            maxItems: 0
            scrubFields: ['passwd', 'password', 'secret', 'confirm_password', 'password_confirmation', 'auth_token', 'csrf_token']

Bundle configuration
--------------------

``enable``: It's possible to enable or disable handling of errors and exceptions.  Default: ``true``

``exclude``: List exceptions that should be excluded from notification

``rollbar``: Configuration parameters for Rollbar instance. Full list of options can be found
in `official documentation`_ for Rollbar PHP lib.

.. _`official documentation`: https://rollbar.com/docs/notifier/rollbar-php/

RollBar - Settings
------------------

Here you can description of some important configuration options for RollBar.

``access_token``: Your project access token.

``agent_log_location``: Path to the directory where agent relay log files should be written. Should not include final slash. Only used when handler is agent. Default: ```%kernel.logs_dir%/rollbar.log```

``environment``: Environment name, e.g. 'production' or 'development'. Default: ``production``
 
``root``: Path to your project's root dir. Default ``%kernel.root_dir%``


RollBar - Person Tracking
-------------------------
Rollbar `can track`_ which of your People (users) are affected by each error. There is one of the options:

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

.. _`can track`: https://rollbar.com/docs/person-tracking/
.. _`call_user_func()`: http://php.net/call_user_func

RollBarJS - Integration
-----------------------
It's possible to use `Rollbar for JavaScript`_ integration in your project. The basic configuration is assailable in configuration for current bundle.

Inject following ``{{ rollbarJs() }}`` code into the <head> of every page you want to monitor. It should be as high as possible, before any other <script> tags.

.. _`Rollbar for JavaScript`: https://rollbar.com/docs/notifier/rollbar.js/
