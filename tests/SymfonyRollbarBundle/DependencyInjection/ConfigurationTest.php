<?php

namespace Tests\SymfonyRollbarBundle\DependencyInjection;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use SymfonyRollbarBundle\DependencyInjection\Configuration;
use SymfonyRollbarBundle\DependencyInjection\SymfonyRollbarExtension;

/**
 * Class ConfigurationTest
 * @package Tests\SymfonyRollbarBundle\DependencyInjection
 */
class ConfigurationTest extends KernelTestCase
{
    public function testParameters()
    {
        static::bootKernel();
        $container = static::$kernel->getContainer();

        $config           = $container->getParameter(SymfonyRollbarExtension::ALIAS . '.config');
        $defaultErrorMask = E_ERROR | E_WARNING | E_PARSE | E_CORE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;

        $default = [
            'enable' => true,
            'rollbar' => [
                'access_token'                   => null,
                'agent_log_location'             => static::$kernel->getLogDir() . '/rollbar.log',
                'base_api_url'                   => 'https://api.rollbar.com/api/1/',
                'batch_size'                     => Configuration::BATCH_SIZE,
                'batched'                        => false,
                'branch'                         => Configuration::BRANCH,
                'capture_error_stacktraces'      => true,
                'checkIgnore'                    => null,
                'code_version'                   => '',
                'enable_utf8_sanitization'       => true,
                'environment'                    => Configuration::ENVIRONMENT,
                'error_sample_rates'             => [],
                'handler'                        => Configuration::HANDLER_BLOCKING,
                'blocking'                       => null,
                'include_error_code_context'     => false,
                'include_exception_code_context' => false,
                'included_errno'                 => $defaultErrorMask,
                'logger'                         => null,
                'person'                         => [],
                'person_fn'                      => null,
                'root'                           => static::$kernel->getRootDir(),
                'scrub_fields'                   => Configuration::$scrubFieldsDefault,
                'shift_function'                 => true,
                'timeout'                        => 3,
                'report_suppressed'              => false,
                'use_error_reporting'            => false,
                'proxy'                          => null,
            ],
        ];

        $this->assertNotEmpty($config);
        $this->assertEquals($default, $config);
    }
}
