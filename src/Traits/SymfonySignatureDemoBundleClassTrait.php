<?php

namespace Wexample\SymfonySignatureDemo\Traits;

use Wexample\SymfonyHelpers\Traits\BundleClassTrait;
use Wexample\SymfonySignatureDemo\WexampleSymfonySignatureDemoBundle;

trait SymfonySignatureDemoBundleClassTrait
{
    use BundleClassTrait;

    public static function getBundleClassName(): string
    {
        return WexampleSymfonySignatureDemoBundle::class;
    }
}
