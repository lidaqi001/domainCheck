# domainCheck

> 该项目为公司自用

	use domainCheck\src\DomainForCheckService;
		
	$service = new DomainForCheckService();

	// 检测域名
	$service->apiUrl = '域名检测api地址';
	$service->check(url); 	// true / false

	// 发送钉钉消息
	$service->webhook = '钉钉机器人api地址';
	$service->dingtalk(link);