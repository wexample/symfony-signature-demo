<?php

namespace Wexample\SymfonySignatureDemo\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyActivity\WexampleSymfonyActivityBundle;
use Wexample\SymfonyHelpers\DependencyInjection\AbstractWexampleSymfonyExtension;

class WexampleSymfonySignatureDemoExtension extends AbstractWexampleSymfonyExtension
{
    /**
     * Where symfony-activity is installed, the history of the envelopes is
     * kept: the demo shows it.
     */
    public function prepend(ContainerBuilder $container): void
    {
        parent::prepend($container);

        if (class_exists(WexampleSymfonyActivityBundle::class) && $container->hasExtension('wexample_symfony_activity')) {
            $container->prependExtensionConfig('wexample_symfony_activity', ['categories' => ['signature' => []]]);
        }
    }

    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        $this->loadConfig(
            __DIR__,
            $container
        );
    }
}
