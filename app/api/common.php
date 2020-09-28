<?php
// 这是系统自动生成的公共文件
function rolling_curl($data = [], $post = 0, $referer = 0, $cookie = 0)
{
    $request = [];
    $requestResource = curl_multi_init();
    foreach ($data as $k => $v) {
        //获取的信息以文件流的形式返回，而不是直接输出。
        $option[CURLOPT_RETURNTRANSFER] = true;
        //url
        $option[CURLOPT_URL] = $v;
        //不验证ssl
        $option[CURLOPT_SSL_VERIFYPEER] = false;
        $option[CURLOPT_SSL_VERIFYHOST] = false;
        //超时
        $option[CURLOPT_TIMEOUT] = 5;
        if ($post) {//如果设置了请求参数,则是POST请求
            $option[CURLOPT_POST] = true;
            $option[CURLOPT_POSTFIELDS] = $post;
        }
        if ($referer) {//如果设置了请求参数,则是referer请求
            $option[CURLOPT_REFERER] = $referer;
        }
        if ($cookie) {//如果设置了请求参数
            $option[CURLOPT_COOKIE] = $cookie;
        }
        //启动一个curl会话
        $request[$k] = curl_init();
        //设置请求选项
        curl_setopt_array($request[$k], $option);
        //添加请求句柄
        curl_multi_add_handle($requestResource, $request[$k]);
    }

    $running = null;
    $result = [];
    do {//执行批处理句柄
        //CURLOPT_RETURNTRANSFER如果为0,这里会直接输出获取到的内容.如果为1,后面可以用curl_multi_getcontent获取内容.
        curl_multi_exec($requestResource, $running);
        //阻塞直到cURL批处理连接中有活动连接,不加这个会导致CPU负载超过90%.
        curl_multi_select($requestResource);
    } while ($running > 0);

    foreach ($request as $k => $v) {
        $result[$k] = curl_multi_getcontent($v);
        curl_multi_remove_handle($requestResource, $v);
    }
    curl_multi_close($requestResource);
    return $result;
}