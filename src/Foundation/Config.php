<?php


namespace Kagami10074\LianLianPay\Foundation;


use Kagami10074\LianLianPay\Support\Collection;

class Config extends Collection
{
    public function getInstantPayPrivateKey()
    {
        return <<<s
-----BEGIN RSA PRIVATE KEY-----
{$this->get('instant_pay.private_key')}
-----END RSA PRIVATE KEY-----
s;
    }

    public function getInstantPayPublicKey()
    {
        return <<<s
-----BEGIN PUBLIC KEY-----
{$this->get('instant_pay.public_key')}
-----END PUBLIC KEY-----
s;
    }

    public function getInstantPayLianLianPublicKey()
    {
        return <<<s
-----BEGIN PUBLIC KEY-----
{$this->get('instant_pay.ll_public_key')}
-----END PUBLIC KEY-----
s;
    }


    public function getPrivateKey()
    {
        return file_get_contents(ROOT_PATH . "rsa_private_key.pem");
    }

    public function getPublicKey()
    {
        return file_get_contents(ROOT_PATH . "rsa_public_key.pem");
    }





}