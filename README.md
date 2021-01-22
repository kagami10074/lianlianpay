<h1 align="center"> LianLianPay </h1>

<p align="center"> 连连支付 SDK for PHP..</p>

<p align="center"> 感谢原作者</p>

## 原地址
## [原作者地址](https://github.com/achais/lianlianpay)

## 安装

```shell
$ composer require achais/lianlianpay:dev-master -vvv
```

 
```shell
$ composer require kagami10074/lianlianpay:dev-master -vvv
```



## 使用
配置
```php
use Kagami10074\LianLianPay\LianLianPay;

$config = [
    'debug' => true, // 开启调试

    // 银行卡收款参数
    'bank_pay' => [
        'oid_partner' => '', // 商户号
        'notify_url' => 'http://localhost/', // 付款结果异步回调地址
        'url_return' => 'http://localhost/', // 付款完成后转向到此地址
    ],
    
    // 实时付款参数
    'instant_pay' => [
        'oid_partner' => '', // 商户号
        'notify_url' => 'http://localhost/', // 付款结果异步回调地址
        'url_return' => 'http://localhost/', // 付款完成后转向到此地址
        'production' => false, // 是否生产环境
        'platform' => '', // 来源标识，写上自己的域名
    ],
    
    // 退款参数
    'instant_pay' => [
        'oid_partner' => '', // 商户号
        'notify_url' => 'http://localhost/', // 付款结果异步回调地址
    ],
    

    // 日志
    'log' => [
        'level' => 'debug',
        'permission' => 0777,
        'file' => '/tmp/logs/lianlianpay-' . date('Y-m-d') . '.log', // 日志文件, 你可以自定义
    ],
];

$llp = new LianLianPay($config);
```
> 不管使用什么功能, 配置信息和实例化 LianLianPay 是必须的


#### 银行卡收款
```php
use Kagami10074\LianLianPay\LianLianPay;

$config = []; // 配置信息如上
$llp = new LianLianPay($config);

$time_stamp =  date("YmdHis");
$user_id = '';
//虚拟物品
$busi_partner='101001';
//商户订单号
$no_order = '1607915741';
//商户订单时间
$dt_order = '20201214094610';
$name_goods = '测试商品名';
$money_order ='0.1';

//基础类风控参数
$base_risk_item=[
    //测试商户充值话费
    "frms_ware_category"=>"1010",
    //用户在趣买货中的ID，可与user_id一致
    "user_info_mercht_userno"=>"",
    "user_info_bind_phone"=>"",
    "user_info_dt_register"=>"",
    "goods_name"=>"测试商品名",   
];
//虚拟类风控参数
$high_risk_item=[
    "frms_charge_phone"=>""
];

$risk_item =  json_encode(array_merge($base_risk_item,$high_risk_item));

$ret = $llp->bankPay->payment($time_stamp, $user_id, $busi_partner, $no_order, $dt_order,$money_order,$risk_item, $name_goods);

结果:
array [
    "ret_code" => "4002"
    "ret_msg" => "疑似重复提交订单"
]

```


#### 实时付款
> 更多详情查看原作者说明 [原作者地址](https://github.com/achais/lianlianpay)
```php
use Kagami10074\LianLianPay\LianLianPay;

$config = []; // 配置信息如上
$llp = new LianLianPay($config);

$moneyOrder = '0.02'; // 付款金额
$cardNo ='6212261203****'; // 收款卡号
$acctName = '起风'; // 收款人姓名
$infoOrder = '代付'; // 订单描述。说明付款用途，5W以上必传。
$memo = '余额提现'; // 收款备注。 传递至银行， 一般作为订单摘要展示。

$ret = $llp->instantPay->payment($moneyOrder, $cardNo, $acctName, $infoOrder, $memo); // 付款申请

结果:
Collection {#287 ▼
  #items: array:7 [▼
    "confirm_code" => "836765"
    "no_order" => "202004021****"
    "oid_partner" => "20190902****"
    "ret_code" => "4002"
    "ret_msg" => "疑似重复提交订单"
    "sign" => "W4Y4R6rjyWJYupN508Q5E1****"
    "sign_type" => "RSA"
  ]
}
```



#### 退款
```php
use Kagami10074\LianLianPay\LianLianPay;

$config = []; // 配置信息如上
$llp = new LianLianPay($config);

$no_refund = ''; // 商户退款流水号
$dt_refund =''; // 商户退款时间，格式为 YYYYMMddHHmmss
$money_refund = '19.99'; // 取值范围为 0.01 ~ 99999999
$no_order = ''; // 原收款请求中传入的商户订单号
$dt_order = ''; // 原商户订单时间。格式为 YYYYMMddHHmmss

$ret = $llp->refundPay->payment($no_refund, $dt_refund, $money_refund, $no_order, $dt_order);

结果:
array [
    "ret_code" => "0000"
    "ret_msg" => "交易成功"
]


$no_refund = ''; //商户退款流水号
$dt_refund = ''; // 商户退款时间，格式为 YYYYMMddHHmmss

//退款状态主动查询
$ret = $llp->refundPay->queryPayment($no_refund, $dt_refund);

结果:
array [
    "ret_code" => "0000"
    "ret_msg" => ""
    "sta_refund" => "2"
]

```


