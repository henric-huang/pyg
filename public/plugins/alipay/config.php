<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2016101700707421",

		//商户私钥
		'merchant_private_key' => "MIICXQIBAAKBgQCNNvgkCb0R+SZlLtRLEz8l5Wh7MJhEZS6ON50IzOQtCna8uOw3anrRmzF4GRAz5fGKOqgNayvd6jzEuPDVVF4Cw2O5qaLomOZs9YDnCWCtFusoehdgNjMycwheUlE59YcYwAphBMD8HwcjXN6BCxNMBlupnuPOUKh+5IyzZdB4vwIDAQABAoGAU2jijLbL3K/jg+RzNJz7sbCdVBZR+iQhqZMjyCztocUKeVJpBBp6zQ/z2lJDhcQONW2MEdD5qixPFIuAhSE+HVWOAcitSyynHL335xwsNhgXlcdwlsJWcJIviYxWsmRtkAVQIMocoXkDFIUBpoHR8vdQlQaTnbFvB0GNmQ97+PECQQDV0+But9CB9MYNVQ1PNgfUzpthfDSScQyazegoBJVXB9n4vT3oYBtSZ8qZbZaW6IH67Al+isn3G22/8L3nLT2jAkEAqRDgw8eU73lY47kQNPdnctO6AbjFKtwXoptwwaLSjCpT6KT8vTDUK5lD/0bikwIqT/qiZMcWLBmODP6Bh8HSNQJBAMNCdeHlDQx/PxQRpNO+nSwhdxZwW3mWrlH20ZcpiE4vJ0bTWOMIUCrSCNpOjoND0t4WCR17E68JZxEtf9zJHU0CQFgwj65Qi6YnfHC9dnDKpVHGk/6V7XOf/0w5HSZE0uN+qOpwNc5SjDp55nhg94uL05qIuPEs0KoMYobws4ynVAkCQQDSac0sI85qKyk0TXmtESnyrYx/Jwk3T6leTZn/HkLv0HRMnJCfJPBbVUzD/l5u3wDe4GfdVDwFa8CczDxJN3ML",
		
		//异步通知地址
		'notify_url' => "http://www.pyg.com/home/order/notify",
		
		//同步跳转
		'return_url' => "http://www.pyg.com/home/order/callback",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
//		'sign_type'=>"RSA2",
		'sign_type'=>"RSA",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDIgHnOn7LLILlKETd6BFRJ0GqgS2Y3mn1wMQmyh9zEyWlz5p1zrahRahbXAfCfSqshSNfqOmAQzSHRVjCqjsAw1jyqrXaPdKBmr90DIpIxmIyKXv4GGAkPyJ/6FTFY99uhpiq0qadD/uSzQsefWo0aTvP/65zi3eof7TcZ32oWpwIDAQAB",
);