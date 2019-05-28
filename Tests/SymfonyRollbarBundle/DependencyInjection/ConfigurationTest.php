<?php

namespace SymfonyRollbarBundle\Tests\DependencyInjection;

use Rollbar\Config;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use SymfonyRollbarBundle\DependencyInjection\Configuration;
use SymfonyRollbarBundle\DependencyInjection\SymfonyRollbarExtension;

/**
 * Class ConfigurationTest
 *
 * @package SymfonyRollbarBundle\Tests\DependencyInjection
 */
class ConfigurationTest extends KernelTestCase
{
    public function testParameters()
    {
        static::bootKernel();
        $container = static::$kernel->getContainer();

        $config           = $container->getParameter(SymfonyRollbarExtension::ALIAS . '.config');
        $defaultErrorMask = E_ERROR | E_WARNING | E_PARSE | E_CORE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;

        $exclude   = Configuration::$exclude;
        $exclude[] = '\Symfony\Component\Debug\Exception\FatalErrorException';
        $exclude[] = '\SymfonyRollbarBundle\Tests\Fixtures\MyAwesomeException';
        $exclude[] = '\ParseError';
        $exclude[] = '\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface';

        $errorRates = [
            'E_NOTICE'      => 0.1,
            'E_USER_ERROR'  => 0.5,
            'E_USER_NOTICE' => 0.1,
        ];

        $exceptionRates = [
            '\Symfony\Component\Security\Core\Exception\AccessDeniedException'                      => 0.1,
            '\Symfony\Component\HttpKernel\Exception\NotFoundHttpException'                         => 0.5,
            '\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException'                     => 0.5,
            '\Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException' => 1,
        ];

        $custom = [
            'hello' => 'world',
            'key'   => 'value',
        ];

        $root = \method_exists(static::$kernel, 'getProjectDir')
            ? static::$kernel->getProjectDir()
            : static::$kernel->getRootDir();

        $default = [
            'enable'     => true,
            'exclude'    => $exclude,
            'rollbar'    => [
                'access_token'                   => 'SOME_ROLLBAR_ACCESS_TOKEN_123456',
                'agent_log_location'             => static::$kernel->getLogDir() . '/rollbar.log',
                'base_api_url'                   => Configuration::API_ENDPOINT,
                'branch'                         => Configuration::BRANCH,
                'autodetect_branch'              => false,
                'capture_error_stacktraces'      => true,
                'check_ignore'                   => '\SymfonyRollbarBundle\Tests\Fixtures\CheckIgnoreProvider',
                'code_version'                   => '',
                'environment'                    => static::$kernel->getEnvironment(),
                'error_sample_rates'             => $errorRates,
                'handler'                        => Configuration::HANDLER_BLOCKING,
                'include_error_code_context'     => false,
                'include_exception_code_context' => false,
                'included_errno'                 => $defaultErrorMask,
                'logger'                         => null,
                'person'                         => [],
                'person_fn'                      => '\SymfonyRollbarBundle\Tests\Fixtures\PersonProvider',
                'root'                           => $root,
                'scrub_fields'                   => Configuration::$scrubFieldsDefault,
                'timeout'                        => 3,
                'report_suppressed'              => false,
                'use_error_reporting'            => false,
                'proxy'                          => null,
                'allow_exec'                     => true,
                'endpoint'                       => Configuration::API_ENDPOINT,
                'custom'                         => $custom,
                'exception_sample_rates'         => $exceptionRates,
                'fluent_host'                    => '127.0.0.1',
                'fluent_port'                    => 24224,
                'fluent_tag'                     => 'rollbar',
                'host'                           => null,
                'scrub_whitelist'                => null,
                'send_message_trace'             => false,
                'include_raw_request_body'       => false,
                'local_vars_dump'                => true,
                'capture_email'                  => false,
                'capture_ip'                     => true,
                'capture_username'               => false,
                'custom_data_method'             => null,
                'custom_truncation'              => null,
                'ca_cert_path'                   => null,
                'transformer'                    => null,
                'max_nesting_depth'              => -1,
                'transmit'                       => false,
                'max_items'                      => Configuration::PHP_MAX_ITEMS,
                'log_payload'                    => false,
                'minimum_level'                  => Configuration::MIN_OCCURRENCES_LEVEL,
                'raise_on_error'                 => false,
                'verbose'                        => Configuration::VERBOSE,
            ],
            'rollbar_js' => [
                'access_token'                 => 'SOME_ROLLBAR_ACCESS_TOKEN_654321',
                'payload'                      => ['environment' => static::$kernel->getEnvironment()],
                'enabled'                      => true,
                'capture_uncaught'             => true,
                'uncaught_error_level'         => Configuration::JS_UNCAUGHT_LEVEL,
                'capture_unhandled_rejections' => true,
                'ignored_messages'             => [],
                'verbose'                      => false,
                'async'                        => true,
                'auto_instrument'              => Configuration::$autoInstrument,
                'items_per_minute'             => Configuration::JS_ITEMS_PER_MINUTE,
                'max_items'                    => Configuration::JS_MAX_ITEMS,
                'scrub_fields'                 => Configuration::$scrubFieldsDefault,
            ],
        ];

        $this->assertNotEmpty($config);
        $this->assertEquals($default, $config);
    }

    public function testEmptyConfiguration()
    {
        static::bootKernel(['environment' => 'test_empty']);

        $container = static::$kernel->getContainer();
        $config    = $container->getParameter(SymfonyRollbarExtension::ALIAS . '.config');

        $this->assertArrayHasKey('enable', $config);
        $this->assertArrayHasKey('exclude', $config);
        $this->assertArrayHasKey('rollbar', $config);
    }

    public function testCompareRollbarDefaults()
    {
        static::bootKernel();
        $container = static::$kernel->getContainer();

        $config       = $container->getParameter(SymfonyRollbarExtension::ALIAS . '.config');
        $rbConfig     = Config::listOptions();
        $bundleConfig = \array_keys($config['rollbar']);

        // add options that are not in list of options for rollbar native config, but they are in use
        $rbConfig[] = 'ca_cert_path';
        $rbConfig[] = 'logger';
        $rbConfig[] = 'transformer';

        // bundle config does not handle 'enabled' config property
        $bundleConfig[] = 'enabled';
        // @TODO: add support of next config property
        $bundleConfig[] = 'log_payload_logger';
        $bundleConfig[] = 'verbose_logger';

        asort($rbConfig);
        asort($bundleConfig);

        $this->assertEquals(\array_values($rbConfig), \array_values($bundleConfig));
    }
}
