Configuration
=============

Simple configuration of bundle:

.. code-block:: yaml

    symfony_rollbar:
        enable: true
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

Bundle configuration
--------------------

``enable``: It's possible to enable or disable handling of errors and exceptions.  Default: ``true``

``rollbar``: Configuration parameters for Rollbar instance. Full list of options can be found
in `official documentation`_ for Rollbar PHP lib.

.. _`official documentation`: https://rollbar.com/docs/notifier/rollbar-php/

RollBar settings
--------------------

Here you can description of some important configuration options for RollBar.

``access_token``: Your project access token.

``agent_log_location``: Path to the directory where agent relay log files should be written. Should not include final slash. Only used when handler is agent. Default: ```%kernel.logs_dir%/rollbar.log```

``environment``: Environment name, e.g. 'production' or 'development'. Default: ``production``
 
``root``: Path to your project's root dir. Default ``%kernel.root_dir%``
