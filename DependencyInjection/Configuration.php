<?php

namespace SymfonyRollbarBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @link https://rollbar.com/docs/notifier/rollbar-php/#configuration-reference
 * @package SymfonyRollbarBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    const HANDLER_BLOCKING = 'blocking';
    const HANDLER_AGENT    = 'agent';
    const HANDLER_FLUENT   = 'fluent';

    const FLUENT_HOST = '127.0.0.1';
    const FLUENT_PORT = 24224;
    const FLUENT_TAG = 'rollbar';

    const BRANCH      = 'master';
    const ENVIRONMENT = 'production';
    const TIMEOUT     = 3;

    const JS_ITEMS_PER_MINUTE = 60;
    const JS_MAX_ITEMS        = 0;
    const JS_UNCAUGHT_LEVEL   = "error";

    public static $scrubFieldsDefault = [
        'passwd',
        'password',
        'secret',
        'confirm_password',
        'password_confirmation',
        'auth_token',
        'csrf_token',
    ];

    public static $autoInstrument = [
        'network'      => true,
        'log'          => true,
        'dom'          => true,
        'navigation'   => true,
        'connectivity' => true,
    ];

    /**
     * List of classes that should be excluded
     *
     * @var array
     */
    public static $exclude = [
        '\Symfony\Component\Debug\Exception\FatalErrorException',
    ];

    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder      = new TreeBuilder();
        $rootNode         = $treeBuilder->root(SymfonyRollbarExtension::ALIAS);
        $defaultErrorMask = E_ERROR | E_WARNING | E_PARSE | E_CORE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;

        $rootNode
            ->children()
                ->scalarNode('enable')->defaultTrue()->end()
                ->arrayNode('exclude')
                    ->treatNullLike([])
                    ->prototype('scalar')->end()
                    ->defaultValue(static::$exclude)
                    ->end()
                ->arrayNode('rollbar')->children()
                    ->scalarNode('access_token')->defaultValue('')->end()
                    ->scalarNode('agent_log_location')->defaultValue('%kernel.logs_dir%/rollbar.log')->end()
                    ->scalarNode('allow_exec')->defaultTrue()->end()
                    ->scalarNode('endpoint')->defaultValue('https://api.rollbar.com/api/1/')->end()
                    ->scalarNode('base_api_url')->defaultValue('https://api.rollbar.com/api/1/')->end()
                    ->scalarNode('branch')->defaultValue(static::BRANCH)->end()
                    ->scalarNode('capture_error_stacktraces')->defaultTrue()->end()
                    ->scalarNode('checkIgnore')->defaultNull()->end()
                    ->scalarNode('code_version')->defaultValue('')->end()
                    ->scalarNode('enable_utf8_sanitization')->defaultTrue()->end()
                    ->scalarNode('environment')->defaultValue(static::ENVIRONMENT)->end()
                    ->arrayNode('custom')
                        ->treatNullLike([])
                        ->prototype('scalar')->end()
                        ->defaultValue([])
                        ->end()
                    ->arrayNode('error_sample_rates')
                        ->treatNullLike([])
                        ->prototype('scalar')->end()
                        ->defaultValue([])
                        ->end()
                    ->arrayNode('exception_sample_rates')
                        ->treatNullLike([])
                        ->prototype('scalar')->end()
                        ->defaultValue([])
                        ->end()
                    ->scalarNode('fluent_host')->defaultValue(static::FLUENT_HOST)->end()
                    ->scalarNode('fluent_port')->defaultValue(static::FLUENT_PORT)->end()
                    ->scalarNode('fluent_tag')->defaultValue(static::FLUENT_TAG)->end()
                    ->scalarNode('handler')->defaultValue(static::HANDLER_BLOCKING)->end()
                    ->scalarNode('host')->defaultNull()->end()
                    ->scalarNode('include_error_code_context')->defaultFalse()->end()
                    ->scalarNode('include_exception_code_context')->defaultFalse()->end()
                    ->scalarNode('included_errno')->defaultValue($defaultErrorMask)->end()
                    ->scalarNode('logger')->defaultNull()->end()
                    ->arrayNode('person')
                        ->treatNullLike([])
                        ->prototype('scalar')->end()
                        ->defaultValue([])
                        ->end()
                    ->scalarNode('person_fn')->defaultNull()->end()
                    ->scalarNode('root')->defaultValue('%kernel.root_dir%')->end()
                    ->arrayNode('scrub_fields')
                        ->treatNullLike([])
                        ->prototype('scalar')->end()
                        ->defaultValue(static::$scrubFieldsDefault)
                        ->end()
                    ->scalarNode('scrub_whitelist')->defaultNull()->end()
                    ->scalarNode('shift_function')->defaultTrue()->end()
                    ->scalarNode('timeout')->defaultValue(static::TIMEOUT)->end()
                    ->scalarNode('report_suppressed')->defaultFalse()->end()
                    ->scalarNode('use_error_reporting')->defaultFalse()->end()
                    ->scalarNode('proxy')->defaultNull()->end()
                    ->scalarNode('send_message_trace')->defaultFalse()->end()
                    ->scalarNode('include_raw_request_body')->defaultFalse()->end()
                    ->scalarNode('local_vars_dump')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('rollbar_js')->children()
                    ->scalarNode('enabled')->defaultTrue()->end()
                    ->scalarNode('accessToken')->defaultValue('')->end()
                    ->scalarNode('captureUncaught')->defaultTrue()->end()
                    ->scalarNode('uncaughtErrorLevel')->defaultValue(static::JS_UNCAUGHT_LEVEL)->end()
                    ->scalarNode('captureUnhandledRejections')->defaultTrue()->end()
                    ->arrayNode('payload')
                        ->treatNullLike([])
                        ->prototype('scalar')->end()
                        ->defaultValue(["environment" => static::ENVIRONMENT])
                        ->end()
                    ->arrayNode('ignoredMessages')
                        ->treatNullLike([])
                        ->prototype('scalar')->end()
                        ->defaultValue([])
                        ->end()
                    ->scalarNode('verbose')->defaultFalse()->end()
                    ->scalarNode('async')->defaultTrue()->end()
                    ->arrayNode('autoInstrument')
                        ->treatNullLike([])
                        ->prototype('scalar')->end()
                        ->defaultValue(static::$autoInstrument)
                        ->end()
                    ->scalarNode('itemsPerMinute')->defaultValue(static::JS_ITEMS_PER_MINUTE)->end()
                    ->scalarNode('maxItems')->defaultValue(static::JS_MAX_ITEMS)->end()
                    ->arrayNode('scrubFields')
                        ->treatNullLike([])
                        ->prototype('scalar')->end()
                        ->defaultValue(static::$scrubFieldsDefault)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
