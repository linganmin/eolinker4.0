<?php
/**
 * @name eolinker open source，eolinker开源版本
 * @link https://www.eolinker.com
 * @package eolinker
 * @author www.eolinker.com 广州银云信息科技有限公司 2015-2018

 * eolinker，业内领先的Api接口管理及测试平台，为您提供最专业便捷的在线接口管理、测试、维护以及各类性能测试方案，帮助您高效开发、安全协作。
 * 如在使用的过程中有任何问题，可通过http://help.eolinker.com寻求帮助
 *
 * 注意！eolinker开源版本遵循GPL V3开源协议，仅供用户下载试用，禁止“一切公开使用于商业用途”或者“以eoLinker开源版本为基础而开发的二次版本”在互联网上流通。
 * 注意！一经发现，我们将立刻启用法律程序进行维权。
 * 再次感谢您的使用，希望我们能够共同维护国内的互联网开源文明和正常商业秩序。
 *
 */

class ProxyModule
{
    /**
     * 转发请求到目的主机
     * @param string $method 请求方法
     * @param string $URL 请求路径
     * @param string $headers 请求头部
     * @param string $param 请求参数
     * @return bool|array
     */
    public function proxyToDesURL($method, $URL, &$headers = NULL, &$param = NULL)
    {
        //初始化请求
        $require = curl_init($URL);

        //判断是否HTTPS
        $isHttps = substr($URL, 0, 8) == "https://" ? TRUE : FALSE;

        //设置请求方式
        switch ($method) {
            case 'GET' :
                curl_setopt($require, CURLOPT_CUSTOMREQUEST, "GET");
                break;
            case 'POST' :
                {
                    curl_setopt($require, CURLOPT_CUSTOMREQUEST, "POST");
                    break;
                }
            case 'DELETE' :
                curl_setopt($require, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'HEAD' :
                curl_setopt($require, CURLOPT_CUSTOMREQUEST, 'HEAD');
                //HEAD请求返回结果不包含BODY
                curl_setopt($require, CURLOPT_NOBODY, TRUE);
                break;
            case 'OPTIONS' :
                curl_setopt($require, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
                break;
            case 'PATCH' :
                curl_setopt($require, CURLOPT_CUSTOMREQUEST, 'PATCH');
                break;
            case 'PUT' :
                curl_setopt($require, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
            default :
                return FALSE;
        }
        if ($param) {
            if (is_array($param)) {
                $str = '';
                foreach ($param as $key => $value) {
                    $str .= $key . '=' . urlencode($value) . '&';
                }
                $param = substr($str, 0, -1);
            }
            curl_setopt($require, CURLOPT_POSTFIELDS, $param);
        }

        if ($isHttps) {
            //跳过证书检查
            curl_setopt($require, CURLOPT_SSL_VERIFYPEER, FALSE);
            //检查证书中是否设置域名
            curl_setopt($require, CURLOPT_SSL_VERIFYHOST, TRUE);
        }

        if ($headers) {
            //设置请求头
            curl_setopt($require, CURLOPT_HTTPHEADER, $headers);
        }

        //返回结果不直接输出
        curl_setopt($require, CURLOPT_RETURNTRANSFER, TRUE);

        //重定向
        //curl_setopt($require, CURLOPT_FOLLOWLOCATION, TRUE);

        //把返回头包含再输出中
        curl_setopt($require, CURLOPT_HEADER, TRUE);

        $time = date("Y-m-d H:i:s", time());

        //发送请求
        $response = $this->curl_redirect_exec($require);

        //获取返回结果状态码
        $httpCode = curl_getinfo($require, CURLINFO_HTTP_CODE);

        //获取传输总耗时
        $deny = curl_getinfo($require, CURLINFO_TOTAL_TIME) * 1000;

        //获取头部长度
        $headerSize = curl_getinfo($require, CURLINFO_HEADER_SIZE);

        if ($response) {
            //返回头部字符串
            $header = substr($response, 0, $headerSize);

            //返回体
            $body = substr($response, $headerSize);

            //过滤隐藏非法字符
            $bodyTemp = json_encode(array(0 => $body));
            $bodyTemp = str_replace('\ufeff', '', $bodyTemp);
            $bodyTemp = json_decode($bodyTemp, TRUE);
            $body = trim($bodyTemp[0]);

            //将返回结果头部转成数组
            $header_rows = array_filter(explode(PHP_EOL, $header), "trim");
            $respondHeaders = array();
            foreach ($header_rows as $row) {
                $keylen = strpos($row, ':');
                if ($keylen) {
                    $respondHeaders[] = array(
                        'key' => substr($row, 0, $keylen),
                        'value' => trim(substr($row, $keylen + 1))
                    );
                }
            }

            //关闭请求
            curl_close($require);
            return array(
                'testTime' => $time,
                'testDeny' => $deny,
                'testHttpCode' => $httpCode,
                'testResult' => array(
                    'headers' => $respondHeaders,
                    'body' => $body
                )
            );
        } else {
            if (curl_errno($require)) {
                $error = curl_error($require);
                //关闭请求
                curl_close($require);
                return array(
                    'testTime' => $time,
                    'testDeny' => $deny,
                    'testHttpCode' => 500,
                    'testResult' => array(
                        'headers' => array(),
                        'body' => $error)
                );
            }
            //关闭请求
            curl_close($require);
            return NULL;
        }
    }

    /**
     * 重定向处理
     * @param $ch
     * @return bool|mixed
     */
    private function curl_redirect_exec($ch)
    {
        static $curl_loops = 0;
        static $curl_max_loops = 20;

        if ($curl_loops++ >= $curl_max_loops) {
            $curl_loops = 0;
            return FALSE;
        }
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        list($header,) = explode("\n\n", $response, 2);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code == 301 || $http_code == 302) {
            $matches = array();
            preg_match('/Location:(.*?)\n/', $header, $matches);
            $url = @parse_url(trim(array_pop($matches)));
            if (!$url) {
                $curl_loops = 0;
                return $response;
            }
            $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query'] ? '?' . $url['query'] : '');
            if ($url['scheme'] == 'https') {
                //跳过证书检查
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                //检查证书中是否设置域名
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE);
            }
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_URL, $new_url);
            return $this->curl_redirect_exec($ch);
        } else {
            $curl_loops = 0;
            return $response;
        }
    }
}

?>