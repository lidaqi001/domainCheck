<?php

namespace domainCheck\src;

// @Title       DomainForCheckService
// @author      liqi 2019/5/23
// @Description 域名检测

class DomainForCheckService
{
    // 重试次数
    public $again = 3;

    // 微信域名状态查询api地址
    public $apiUrl = 'http://wz5.tkc8.com/manage/api/check?token=81b5d6225dec44943f641305d711d052&url=';

    // 钉钉机器人api地址
//    public $webhook = 'https://oapi.dingtalk.com/robot/send?access_token=02c99aabecc74ded32d89952d052aa0c42ed05f2abb428fd367139ffef1cc475';
    // 测试
    public $webhook = 'https://oapi.dingtalk.com/robot/send?access_token=61c2d3d70e4846406808ec4f0d6cd371bd192cada633191e88da4740c05de050';

    // 检测域名
    public function check($url)
    {
        $result = self::getCheckApiResult($this->apiUrl . $url, 2, $this->again);

        // 检测不到code 或 返回值不等于9900  ==  域名被封禁
        if (!isset($result['code']) || $result['code'] != 9900)
            return false;

        return true;
    }

    // 钉钉机器人
    public function dingtalk($data)
    {
        // 测试数据
//        $webhook = 'https://oapi.dingtalk.com/robot/send?access_token=61c2d3d70e4846406808ec4f0d6cd371bd192cada633191e88da4740c05de050';

//        $message = "我就是我, 是不一样的烟火";
//        $data = array('msgtype' => 'text', 'text' => array('content' => $message));

//        $markdown = "#### 杭州天气 \n> 9度，西北风1级，空气良89，相对温度73%\n\n> ![screenshot](https://gw.alipayobjects.com/zos/skylark-tools/public/files/84111bbeba74743d2771ed4f062d1f25.png)\n> ###### 10点20分发布 [天气](http://www.thinkpage.cn/) \n";
//        $data = array('msgtype' => 'markdown', 'markdown' => array('title' => '测试', 'text' => $markdown));

        $data_string = json_encode($data);
        $result = self::httpRequest($this->webhook, [
            'method' => 'post',
            'post_string' => $data_string
        ]);
        var_dump($result);
    }

    private static function getCheckApiResult($search_url, $shorten_timeout, $again)
    {
        $timeout = 5;
        for ($i = 0; $i < $again; $i++) {
            $result = json_decode(
                self::httpRequest($search_url, [
                    'timeout' => $timeout
                ]),
                true);
            // 检测存在code，跳出循环
            if (isset($result['code']))
                break;
            // 未检测到返回的code，缩短请求超时时间，重试
            $timeout = $shorten_timeout;
        }
        return $result;
    }

    private static function httpRequest($url, $config = [])
    {
        $default = [
            'timeout' => 5,
            'method' => 'get',
            'post_string' => '',
        ];
        $config = count($config) ? array_merge($default, $config) : $default;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);            // 返回response头部信息
        curl_setopt($ch, CURLOPT_TIMEOUT, $config['timeout']);        // 设置请求超时时间
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);        // TRUE 时追踪句柄的请求字符串，从 PHP 5.1.3 开始可用。这个很关键，就是允许你查看请求header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     // TRUE 将curl_exec()获取的信息以字符串返回，而不是直接输出。
        switch ($config['method']) {
            case 'get':
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
            case 'post':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $config['post_string']);
                break;
        }
        // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
//         curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
