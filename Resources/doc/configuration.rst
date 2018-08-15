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
            check_ignore: null
            code_version: ''
            enable_utf8_sanitization': true
            environment: 'production'
            custom:
                - {key: hello, value: world}
                - {key: key, value: value}
            error_sample_rates:
                - {key: E_NOTICE, value: 0.1}
                - {key: E_USER_ERROR, value: 0.5}
                - {key: E_USER_NOTICE, value: 0.1}
            exception_sample_rates:
                - {key: \Symfony\Component\Security\Core\Exception\AccessDeniedException, value: 0.1}
                - {key: \Symfony\Component\HttpKernel\Exception\NotFoundHttpException, value: 0.5}
                - {key: \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException, value: 0.5}
                - {key: \Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException, value: 1}
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
            local_vars_dump: true
            capture_email: false
            capture_ip: true
            capture_username: false
            custom_data_method: null
            custom_truncation: null
            ca_cert_path: null
            transformer: null
            verbosity: 'error'
        rollbar_js:
            enabled: true
            access_token: 'some-public-token'
            capture_uncaught: true
            uncaught_error_level: 'error'
            capture_unhandled_rejections: true
            payload:
                environment: '%kernel.environment%'
            ignored_messages: []
            verbose: false
            async: true
            auto_instrument:
                network: true
                log: true
                dom: true
                navigation: true
                connectivity: true
            items_per_minute: 60
            max_items: 0
            scrub_fields: ['passwd', 'password', 'secret', 'confirm_password', 'password_confirmation', 'auth_token', 'csrf_token']

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

``check_ignore``: Function called before sending payload to Rollbar, `Example of check ignore`_

``custom_data_method``: Function creating dynamic custom data on runtime during error reporting, `Example of custom data method`_

.. _`Example of check ignore`: check_ignore.rst
.. _`Example of custom data method`: custom_data_method.rst

RollBar - Person Tracking
-------------------------
Rollbar `can track`_ which of your People (users) are affected by each error. `Example of tracking`_

.. _`can track`: https://rollbar.com/docs/person-tracking/
.. _`Example of tracking`: person_tracking.rst

RollBarJS - Integration
-----------------------
It's possible to use `Rollbar for JavaScript`_ integration in your project. The basic configuration is assailable in configuration for current bundle.

Inject following ``{{ rollbarJs() }}`` code into the <head> of every page you want to monitor. It should be as high as possible, before any other <script> tags.

.. _`Rollbar for JavaScript`: https://rollbar.com/docs/notifier/rollbar.js/
