<?php

namespace Kagami10074\LianLianPay\Foundation\ServiceProviders;

use Kagami10074\LianLianPay\InstantPay;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class InstantPayProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['instantPay'] = function ($pimple) {
            return new InstantPay\InstantPay($pimple['config']);
        };
    }
}