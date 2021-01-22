<?php

namespace Kagami10074\LianLianPay\Foundation\ServiceProviders;

use Kagami10074\LianLianPay\RefundPay;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class RefundPayProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['refundPay'] = function ($pimple) {
            return new RefundPay\RefundPay($pimple['config']);
        };
    }
}