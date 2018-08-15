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

    const API_ENDPOINT = 'https://api.rollbar.com/api/1/';

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
                ->booleanNode('enable')->defaultTrue()->end()
                ->arrayNode('exclude')
                    ->treatNullLike([])
                    ->prototype('scalar')->end()
                    ->defaultValue(static::$exclude)
                    ->end()
                ->arrayNode('rollbar')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('access_token')->defaultValue('')->end()
                        ->scalarNode('agent_log_location')
                            ->defaultValue('%kernel.logs_dir%/rollbar.log')
                        ->end()
                        ->booleanNode('allow_exec')->defaultTrue()->end()
                        ->scalarNode('endpoint')->defaultValue(static::API_ENDPOINT)->end()
                        ->scalarNode('base_api_url')->defaultValue(static::API_ENDPOINT)->end()
                        ->scalarNode('branch')->defaultValue(static::BRANCH)->end()
                        ->booleanNode('capture_email')->defaultFalse()->end()
                        ->booleanNode('capture_ip')->defaultTrue()->end()
                        ->booleanNode('capture_username')->defaultFalse()->end()
                        ->booleanNode('capture_error_stacktraces')->defaultTrue()->end()
                        ->scalarNode('check_ignore')->defaultNull()->end()
                        ->scalarNode('code_version')->defaultValue('')->end()
                        ->booleanNode('enable_utf8_sanitization')->defaultTrue()->end()
                        ->scalarNode('environment')->defaultValue(static::ENVIRONMENT)->end()
                        ->arrayNode('custom')
                            ->treatNullLike([])
                            ->useAttributeAsKey('key')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('key')->end()
                                    ->scalarNode('value')->end()
                                ->end()
                            ->end()
                            ->defaultValue([])
                        ->end()
                        ->scalarNode('custom_data_method')->defaultNull()->end()
                        ->scalarNode('custom_truncation')->defaultNull()->end()
                        ->scalarNode('ca_cert_path')->defaultNull()->end()
                        ->arrayNode('error_sample_rates')
                            ->treatNullLike([])
                            ->useAttributeAsKey('key')
                            ->prototype('array')
                                ->children()
                                    ->integerNode('key')->end()
                                    ->floatNode('value')->end()
                                ->end()
                            ->end()
                            ->defaultValue([])
                        ->end()
                        ->arrayNode('exception_sample_rates')
                            ->treatNullLike([])
                            ->useAttributeAsKey('key')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('key')->end()
                                    ->floatNode('value')->end()
                                ->end()
                            ->end()
                            ->defaultValue([])
                        ->end()
                        ->scalarNode('fluent_host')->defaultValue(static::FLUENT_HOST)->end()
                        ->scalarNode('fluent_port')->defaultValue(static::FLUENT_PORT)->end()
                        ->scalarNode('fluent_tag')->defaultValue(static::FLUENT_TAG)->end()
                        ->scalarNode('handler')->defaultValue(static::HANDLER_BLOCKING)->end()
                        ->scalarNode('host')->defaultNull()->end()
                        ->booleanNode('include_error_code_context')->defaultFalse()->end()
                        ->booleanNode('include_exception_code_context')->defaultFalse()->end()
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
                        ->scalarNode('transformer')->defaultNull()->end()
                        ->scalarNode('verbosity')->defaultValue(\Psr\Log\LogLevel::ERROR)->end()
                        ->booleanNode('shift_function')->defaultTrue()->end()
                        ->scalarNode('timeout')->defaultValue(static::TIMEOUT)->end()
                        ->booleanNode('report_suppressed')->defaultFalse()->end()
                        ->booleanNode('use_error_reporting')->defaultFalse()->end()
                        ->scalarNode('proxy')->defaultNull()->end()
                        ->booleanNode('send_message_trace')->defaultFalse()->end()
                        ->booleanNode('include_raw_request_body')->defaultFalse()->end()
                        ->booleanNode('local_vars_dump')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('rollbar_js')->children()
                    ->booleanNode('enabled')->defaultTrue()->end()
                    ->scalarNode('access_token')->defaultValue('')->end()
                    ->booleanNode('capture_uncaught')->defaultTrue()->end()
                    ->scalarNode('uncaught_error_level')->defaultValue(static::JS_UNCAUGHT_LEVEL)->end()
                    ->booleanNode('capture_unhandled_rejections')->defaultTrue()->end()
                    ->arrayNode('payload')
                        ->treatNullLike([])
                        ->prototype('scalar')->end()
                        ->defaultValue(["environment" => static::ENVIRONMENT])
                        ->end()
                    ->arrayNode('ignored_messages')
                        ->treatNullLike([])
                        ->prototype('scalar')->end()
                        ->defaultValue([])
                        ->end()
                    ->booleanNode('verbose')->defaultFalse()->end()
                    ->booleanNode('async')->defaultTrue()->end()
                    ->arrayNode('auto_instrument')
                        ->treatNullLike([])
                        ->prototype('scalar')->end()
                        ->defaultValue(static::$autoInstrument)
                        ->end()
                    ->scalarNode('items_per_minute')->defaultValue(static::JS_ITEMS_PER_MINUTE)->end()
                    ->scalarNode('max_items')->defaultValue(static::JS_MAX_ITEMS)->end()
                    ->arrayNode('scrub_fields')
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
