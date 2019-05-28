<?php
namespace SymfonyRollbarBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use SymfonyRollbarBundle\DependencyInjection\Configuration;
use SymfonyRollbarBundle\DependencyInjection\SymfonyRollbarExtension;
use SymfonyRollbarBundle\EventListener\ErrorListener;
use SymfonyRollbarBundle\EventListener\ExceptionListener;
use SymfonyRollbarBundle\Payload\Generator;
use SymfonyRollbarBundle\Provider\RollbarHandler;

/**
 * Class SymfonyRollbarExtensionTest
 * @package SymfonyRollbarBundle\Tests\DependencyInjection
 */
class SymfonyRollbarExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @link: https://github.com/matthiasnoback/SymfonyDependencyInjectionTest
     * @return array
     */
    protected function getContainerExtensions()
    {
        return [
            new SymfonyRollbarExtension(),
        ];
    }

    /**
     * @dataProvider generatorConfigVars
     *
     * @param string $var
     * @param mixed  $value
     */
    public function testConfigEnabledVars($var, $value)
    {
        $this->load();

        $this->assertContainerBuilderHasParameter($var, $value);
    }

    public function generatorConfigVars()
    {
        $exclude = Configuration::$exclude;
        $defaultErrorMask = E_ERROR | E_WARNING | E_PARSE | E_CORE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;

        $default = [
            'enable'     => true,
            'exclude'    => $exclude,
            'rollbar'    => [
                'access_token'                   => '',
                'agent_log_location'             => '%kernel.logs_dir%/rollbar.log',
                'base_api_url'                   => 'https://api.rollbar.com/api/1/',
                'branch'                         => Configuration::BRANCH,
                'autodetect_branch'              => false,
                'capture_error_stacktraces'      => true,
                'check_ignore'                    => null,
                'code_version'                   => '',
                'environment'                    => Configuration::ENVIRONMENT,
                'error_sample_rates'             => [],
                'handler'                        => Configuration::HANDLER_BLOCKING,
                'include_error_code_context'     => false,
                'include_exception_code_context' => false,
                'included_errno'                 => $defaultErrorMask,
                'logger'                         => null,
                'person'                         => [],
                'person_fn'                      => null,
                'root'                           => '%kernel.root_dir%',
                'scrub_fields'                   => Configuration::$scrubFieldsDefault,
                'timeout'                        => 3,
                'report_suppressed'              => false,
                'use_error_reporting'            => false,
                'proxy'                          => null,
                'allow_exec'                     => true,
                'endpoint'                       => 'https://api.rollbar.com/api/1/',
                'custom'                         => [],
                'exception_sample_rates'         => [],
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
                'max_items'                      => Configuration::PHP_MAX_ITEMS,
                'log_payload'                    => false,
                'minimum_level'                  => Configuration::MIN_OCCURRENCES_LEVEL,
                'raise_on_error'                 => false,
                'verbose'                        => Configuration::VERBOSE,
                'transmit'                       => true,
            ],
        ];

        return [
            ['symfony_rollbar.event_listener.exception_listener.class', ExceptionListener::class],
            ['symfony_rollbar.event_listener.error_listener.class', ErrorListener::class],
            ['symfony_rollbar.provider.rollbar_handler.class', RollbarHandler::class],
            ['symfony_rollbar.config', $default],
        ];
    }

    /**
     * @dataProvider generatorConfigVars
     *
     * @expectedException \PHPUnit_Framework_ExpectationFailedException
     *
     * @param string $var
     * @param mixed  $value
     */
    public function testConfigDisabledVars($var, $value)
    {
        $this->load(['enable' => false]);

        $this->assertContainerBuilderHasParameter($var, $value);
    }

    public function testAlias()
    {
        $extension = new SymfonyRollbarExtension();
        $this->assertEquals(SymfonyRollbarExtension::ALIAS, $extension->getAlias());
    }
}
