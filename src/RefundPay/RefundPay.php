<?php

namespace Kagami10074\LianLianPay\RefundPay;

use Kagami10074\LianLianPay\Core\AbstractAPI;
use Kagami10074\LianLianPay\Exceptions\HttpException;
use Kagami10074\LianLianPay\Exceptions\InvalidArgumentException;
use Kagami10074\LianLianPay\Support\Arr;
use Kagami10074\LianLianPay\Support\Collection;
use Kagami10074\LianLianPay\Support\Log;

class RefundPay extends AbstractAPI
{

    const SIGN_TYPE_RSA = 'RSA';

    protected $baseUrl = 'https://traderapi.lianlianpay.com';



    /**
     * 发起一笔退款申请
     *
     * @param string $no_refund 商户退款流水号
     * @param string $dt_refund 商户退款时间。格式为 YYYYMMddHHmmss
     * @param string $money_refund 退款的金额，单位为元，精确到小数点后两位
     * @param string $no_order 原收款请求中传入的商户订单号
     * @param string $dt_order 原商户订单时间
     * @return Collection|null
     * @throws HttpException
     */
    public function payment($no_refund, $dt_refund, $money_refund, $no_order, $dt_order)
    {
        $url = $this->baseUrl . '/refund.htm';
        $params = [
            "oid_partner" => $this->config['refund_pay.oid_partner'],
            "sign_type" => self::SIGN_TYPE_RSA,
            "no_refund" => $no_refund,
            "dt_refund" => $dt_refund,
            "money_refund" => $money_refund,
            "no_order" => $no_order,
            "dt_order" => $dt_order,
            "notify_url" => $this->config['refund_pay.notify_url'],
        ];

        $params = $this->buildSignatureParams($params);

        return $this->parseJSON('json', [$url, $params]);
    }


    /**
     * 退款状态查询
     *
     * @param string $no_refund 商户退款流水号
     * @param string $dt_refund 商户退款时间。格式为 YYYYMMddHHmmss
     * @return Collection|null
     * @throws InvalidArgumentException|HttpException
     */
    public function queryPayment($no_refund, $dt_refund)
    {
        if (empty($no_refund) && empty($dt_refund)) {
            throw new InvalidArgumentException('no_refund 和 dt_refund 不能都为空');
        }

        $url = $this->baseUrl . '/refundquery.htm';
        $params = [
            "oid_partner" => $this->config['refund_pay.oid_partner'],
            "sign_type" => self::SIGN_TYPE_RSA,
            "no_refund" => $no_refund,
            "dt_refund" => $dt_refund,
        ];

        $params = $this->buildSignatureParams($params);

        return $this->parseJSON('json', [$url, $params]);
    }

    /**
     * 验证签名
     * @param $params
     * @return bool
     */
    public function verifySignature($params)
    {
        if (!isset($params['sign'])) {
            return false;
        }

        $sign = $params['sign'];
        unset($params['sign']);
        $signRaw = $this->httpBuildKSortQuery($params);

        $pubKey = $this->getConfig()->getLianLianPublicKey();
        $res = openssl_get_publickey($pubKey);

        // 调用openssl内置方法验签，返回bool值
        $result = (bool)openssl_verify($signRaw, base64_decode($sign), $res, OPENSSL_ALGO_MD5);

        Log::debug('Verify Signature Result:', compact('result', 'params'));

        // 释放资源
        openssl_free_key($res);
        return $result;
    }

    private function filterNull($params)
    {
        // 过滤空参数
        $params = Arr::where($params, function ($key, $value) {
            return !is_null($value);
        });
        return $params;
    }

    private function httpBuildKSortQuery($params)
    {
        // 排序
        ksort($params);
        return urldecode(http_build_query($params));
    }

    /**
     * @param array $params
     * @return array
     */
    private function buildSignatureParams($params)
    {
        $params = $this->filterNull($params);
        $signRaw = $this->httpBuildKSortQuery($params);
        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥
        $res = openssl_get_privatekey($this->getConfig()->getPrivateKey());
        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($signRaw, $signStr, $res, OPENSSL_ALGO_MD5);
        //释放资源
        openssl_free_key($res);
        //base64编码
        $params['sign'] = base64_encode($signStr);;

        return $params;
    }


}