<?php

namespace Kagami10074\LianLianPay\BankPay;

use Kagami10074\LianLianPay\Core\AbstractAPI;
use Kagami10074\LianLianPay\Exceptions\HttpException;
use Kagami10074\LianLianPay\Exceptions\InvalidArgumentException;
use Kagami10074\LianLianPay\Support\Arr;
use Kagami10074\LianLianPay\Support\Collection;
use Kagami10074\LianLianPay\Support\Log;

class BankPay extends AbstractAPI
{

    const SIGN_TYPE_RSA = 'RSA';

    protected $baseUrl = 'https://payserverapi.lianlianpay.com';


    /**
     * 生产有效的商户订单号(最好排重)
     * @return string
     */
    public static function findAvailableNoOrder()
    {
        return date('YmdHis') . substr(explode(' ', microtime())[0], 2, 6) . rand(1000, 9999);
    }



    /**
     * 银行卡统一支付创单API
     * @param string $time_stamp yyyyMMddHHmmss HH以24小时为准，如20170309143712
     * @param string $user_id 用户唯一ID
     * @param string $busi_partner 实物商品销售：109001
     * @param string $no_order 唯一商户订单号
     * @param string $dt_order 商户订单时间。格式为 YYYYMMddHHmmss
     * @param string $name_goods 可留空
     * @param string $money_order 付款金额保留小数点后2位,单位元
     * @param string $notify_url 接收异步通知的线上地址
     * @param string $url_return 支付结束后，连连会将消费者重定向至此地址
     * @param string $risk_item 风险控制参数
     * @param string $info_order 订单扩展字段
     * @return Collection|null
     * @throws HttpException
     */

    public function payment($time_stamp, $user_id, $busi_partner, $no_order, $dt_order, $money_order, $risk_item, $notify_url,$name_goods = null, $url_return = null,$info_order = null)
    {
        $url = $this->baseUrl . '/v1/paycreatebill';
        $params = [
            'api_version'=>'1.0',
            "sign_type" => self::SIGN_TYPE_RSA,
            'time_stamp'=>$time_stamp,
            'oid_partner'=>$this->config['bank_pay.oid_partner'],
            'user_id'=>$user_id,
            'busi_partner'=>$busi_partner,
            "no_order" => $no_order ?: $this->findAvailableNoOrder(),
            'dt_order'=>$dt_order,
            'name_goods'=>$name_goods,
            'money_order'=>$money_order,
            'notify_url'=>$this->config['bank_pay.notify_url'],
            'url_return'=>$this->config['bank_pay.url_return'],
            'risk_item'=>$risk_item,
            'flag_pay_product'=>'0',
            'flag_chnl'=>'3',
            'info_order'=>$info_order,
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

        $pubKey = $this->getConfig()->getInstantPayLianLianPublicKey();
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
        $params['sign'] = base64_encode($signStr);
        return $params;
    }




}