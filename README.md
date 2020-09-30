Elesq(亿乐社区)
===============
## 介绍
亿乐社区是通过协议/接口来完成空间秒赞，业务签到，活动领取等操作
## 安装
>环境要求
~~~
PHP >= 7.1
Swoole >= 4.5
~~~
> 配置Wss代理
~~~
location /wss {
    proxy_pass http://127.0.0.1:9501;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
}
~~~
##演示地址
[亿乐社区](https://www.elesq.cn)
##交流群
更新计划及规划 [亿乐云](https://jq.qq.com/?_wv=1027&k=5F72NRX)
##鸣谢
Thinkphp 顶想科技