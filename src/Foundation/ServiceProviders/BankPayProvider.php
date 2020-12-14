<?php

namespace Kagami10074\LianLianPay\Foundation\ServiceProviders;

use Kagami10074\LianLianPay\BankPay;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class BankPayProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['bankPay'] = function ($pimple) {
            return new BankPay\BankPay($pimple['config']);
        };
    }
}