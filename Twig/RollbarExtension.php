<?php

namespace SymfonyRollbarBundle\Twig;

use Rollbar\RollbarJsHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyRollbarBundle\DependencyInjection\SymfonyRollbarExtension;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RollbarExtension extends AbstractExtension
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $config;

    /**
     * RollbarExtension constructor.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        try {
            $config = $container->getParameter(SymfonyRollbarExtension::ALIAS . '.config');

            if (!empty($config['rollbar_js']['enabled'])) {
                $this->config = $config;
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        if (empty($this->config)) {
            return [];
        }

        return [
            new TwigFunction('rollbarJs', [$this, 'rollbarJs'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * @return string
     */
    public function rollbarJs()
    {
        $config = $this->getJsConfig();
        $helper = new RollbarJsHelper($config);

        $script = "<script>{{config}};\n{{rollbar-snippet}}</script>";
        $script = strtr($script, [
            '{{config}}'          => $helper->configJsTag(),
            '{{rollbar-snippet}}' => $helper->jsSnippet(),
        ]);

        return $script;
    }

    /**
     * @return mixed
     */
    protected function getJsConfig()
    {
        $config = $this->config['rollbar_js'];

        // we have to map a list of fields
        $map = [
            'access_token'                 => 'accessToken',
            'capture_uncaught'             => 'captureUncaught',
            'uncaught_error_level'         => 'uncaughtErrorLevel',
            'capture_unhandled_rejections' => 'captureUnhandledRejections',
            'ignored_messages'             => 'ignoredMessages',
            'auto_instrument'              => 'autoInstrument',
            'items_per_minute'             => 'itemsPerMinute',
            'max_items'                    => 'maxItems',
            'scrub_fields'                 => 'scrubFields',
        ];

        foreach ($map as $old => $new) {
            if (!isset($config[$old])) {
                continue;
            }

            $value = $config[$old];
            unset($config[$old]);

            $config[$new] = $value;
        }

        return $config;
    }
}
