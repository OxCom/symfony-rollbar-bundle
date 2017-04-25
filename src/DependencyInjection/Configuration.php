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

    const BATCH_SIZE  = 50;
    const BRANCH      = 'master';
    const ENVIRONMENT = 'production';
    const TIMEOUT     = 3;

    static $scrubFieldsDefault = [
        'passwd',
        'password',
        'secret',
        'confirm_password',
        'password_confirmation',
        'auth_token',
        'csrf_token',
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
                ->arrayNode('rollbar')->children()
                    ->scalarNode('access_token')->defaultValue('')->end()
                    ->scalarNode('agent_log_location')->defaultValue('%kernel.logs_dir%/rollbar.log')->end()
                    ->scalarNode('base_api_url')->defaultValue('https://api.rollbar.com/api/1/')->end()
                    ->scalarNode('batch_size')->defaultValue(static::BATCH_SIZE)->end()
                    ->scalarNode('batched')->defaultTrue()->end()
                    ->scalarNode('branch')->defaultValue(static::BRANCH)->end()
                    ->scalarNode('capture_error_stacktraces')->defaultTrue()->end()
                    ->scalarNode('checkIgnore')->defaultNull()->end()
                    ->scalarNode('code_version')->defaultValue('')->end()
                    ->scalarNode('enable_utf8_sanitization')->defaultTrue()->end()
                    ->scalarNode('environment')->defaultValue(static::ENVIRONMENT)->end()
                    ->scalarNode('error_sample_rates')->defaultValue([])->end()
                    ->scalarNode('handler')->defaultValue(static::HANDLER_BLOCKING)->end()
                    ->scalarNode('blocking')->defaultNull()->end()
                    ->scalarNode('include_error_code_context')->defaultFalse()->end()
                    ->scalarNode('include_exception_code_context')->defaultFalse()->end()
                    ->scalarNode('included_errno')->defaultValue($defaultErrorMask)->end()
                    ->scalarNode('logger')->defaultNull()->end()
                    ->scalarNode('person')->defaultValue([])->end()
                    ->scalarNode('person_fn')->defaultNull()->end()
                    ->scalarNode('root')->defaultValue('%kernel.root_dir%')->end()
                    ->scalarNode('scrub_fields')->defaultValue(static::$scrubFieldsDefault)->end()
                    ->scalarNode('shift_function')->defaultTrue()->end()
                    ->scalarNode('timeout')->defaultValue(static::TIMEOUT)->end()
                    ->scalarNode('report_suppressed')->defaultFalse()->end()
                    ->scalarNode('use_error_reporting')->defaultFalse()->end()
                    ->scalarNode('proxy')->defaultNull()->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
