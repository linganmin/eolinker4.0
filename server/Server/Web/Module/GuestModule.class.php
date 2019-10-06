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

class GuestModule
{
    /**
     * 验证登录状态
     */
    public function checkLogin()
    {
        @session_start();
        if (isset($_SESSION['userID']))
            return $_SESSION['userName'];
        else
            return FALSE;
    }

    /**
     * 用户注册
     * @param $userName string 用户名
     * @param $loginPassword string 登录密码
     * @param $userNickName string 用户昵称
     * @return bool
     */
    public function register(&$userName, &$loginPassword, $userNickName)
    {
        $hashPassword = md5($loginPassword);
        $dao = new GuestDao;
        $nameList = array("独孤求败", "扫地僧", "东方不败", "风清扬", "金轮法王", "令狐冲", "南帝一灯", "无崖子", "灭绝师太", "天山童姥", "奇异种子", "比比鸟", "皮卡丘", "百变怪", "黑胡子", "战国", "鹰眼", "麦哲伦", "蒙其·D·路飞", "鼻屎兽", "触手百合", "七夕青鸟", "吼吼鲸", "巨沼怪", "妮可·罗宾", "白胡子", "八神太一", "圆陆鲨", "念力土偶", "毒蔷薇", "黑暗鸦", "小龙女", "杨过", "张三丰", "王重阳", "李莫愁", "喷火龙", "九尾", "六尾");
        $nickName = (!empty($userNickName)) ? $userNickName : '[随机]' . $nameList[rand(0, 38)];

        if ($userID = $dao->register($userName, $hashPassword, $nickName)) {
            $demoProject = '{"projectInfo":{"projectID":0,"projectType":0,"projectName":"eoinker\u793a\u4f8b","projectUpdateTime":"2016-11-08 23:04:48","projectPassword":"","isLocked":0,"projectVersion":"1.2"},"apiGroupList":[{"groupID":275,"groupName":"demo","projectID":582,"apiList":[{"baseInfo":{"apiID":623,"apiName":"\u793a\u4f8b\u63a5\u53e3","apiURI":"www.eolinker.com","apiProtocol":0,"apiFailureMock":"","apiSuccessMock":"{\"type\":\"demo\",\"status\":\"success\",\"desc\":\"\u8fd9\u662f\u8fd4\u56de\u7ed3\u679c\u7684\u793a\u4f8b\uff0c\u60a8\u53ef\u4ee5\u5728\u6b64\u8bb0\u5f55\u60a8\u7684\u63a5\u53e3\u7684\u8fd4\u56de\u503c\u793a\u4f8b\uff0c\u65b9\u4fbf\u5bf9\u63a5\u4eba\u5458\u67e5\u770b\u3002\u540c\u65f6\u6211\u4eec\u63d0\u4f9b\u4e86\u591a\u79cd\u683c\u5f0f\u6574\u7406\u529f\u80fd\uff0c\u60a8\u53ea\u9700\u8981\u8f7b\u8f7b\u4e00\u70b9\u5373\u53ef\u5f97\u5230\u7f8e\u5316\u7248\u672c\u7684\u8fd4\u56de\u7ed3\u679c\uff0c\u975e\u5e38\u65b9\u4fbf\uff01\"}","apiRequestType":0,"apiStatus":0,"apiUpdateTime":"2016-11-08 23:04:48","groupID":275,"projectID":582,"starred":0,"removed":0,"removeTime":null,"apiNoteType":1,"apiNoteRaw":"####  \u6211\u53ef\u4ee5\u4f7f\u7528\u5bcc\u6587\u672c\u6216\u8005markdown\u7f16\u8f91\u5668\u53bb\u8fdb\u884c\u5907\u6ce8\u7684\u5199\u4f5c\u3002","apiNote":"&lt;h4 id=&quot;h4--markdown-&quot;&gt;&lt;a name=&quot;\u6211\u53ef\u4ee5\u4f7f\u7528\u5bcc\u6587\u672c\u6216\u8005markdown\u7f16\u8f91\u5668\u53bb\u8fdb\u884c\u5907\u6ce8\u7684\u5199\u4f5c\u3002&quot; class=&quot;reference-link&quot;&gt;&lt;\/a&gt;&lt;span class=&quot;header-link octicon octicon-link&quot;&gt;&lt;\/span&gt;\u6211\u53ef\u4ee5\u4f7f\u7528\u5bcc\u6587\u672c\u6216\u8005markdown\u7f16\u8f91\u5668\u53bb\u8fdb\u884c\u5907\u6ce8\u7684\u5199\u4f5c\u3002&lt;\/h4&gt;","apiRequestParamType":0,"apiRequestRaw":"","mockCode":"AbKWQJuEZqQni9Hdpz2wYVEGl9qavDj2"},"headerInfo":[{"headerID":562,"headerName":"Accept-Encoding","headerValue":"compress, gzip"},{"headerID":563,"headerName":"\u8bf7\u6c42\u5934\u90e8\u7684\u6807\u7b7e","headerValue":"\u8fd9\u91cc\u53ef\u4ee5\u8bb0\u5f55\u5b8c\u6574\u7684\u5934\u90e8\u4fe1\u606f"}],"requestInfo":[{"paramID":11288,"paramName":"\u53c2\u6570\u793a\u4f8b1","paramKey":"param1","paramType":0,"paramLimit":"\u6570\u5b57\u3001\u82f1\u6587\u3001\u4e0b\u5212\u7ebf","paramValue":"eoapi_2016","paramNotNull":0,"paramValueList":[]},{"paramID":11289,"paramName":"\u53c2\u6570\u793a\u4f8b2","paramKey":"param2","paramType":0,"paramLimit":"\u8fd9\u91cc\u53ef\u4ee5\u8bb0\u5f55\u53c2\u6570\u7684\u9650\u5236\u6761\u4ef6\uff0c\u6bd4\u59820~3\u7684\u6570\u5b57","paramValue":"0","paramNotNull":1,"paramValueList":[{"valueID":1562,"value":"0","valueDescription":"\u53ef\u80fd1\uff0c\u4ee3\u8868x"},{"valueID":1563,"value":"1","valueDescription":"\u53ef\u80fd2\uff0c\u4ee3\u8868y"},{"valueID":1564,"value":"2","valueDescription":"\u53ef\u80fd3\uff0c\u4ee3\u8868z"},{"valueID":1565,"value":"3","valueDescription":"\u53ef\u80fd4\uff0c\u4ee3\u8868\u03b1"}]},{"paramID":11290,"paramName":"\u4e8c\u7ea7\u53c2\u6570\u793a\u4f8b","paramKey":"param2>>param3","paramType":0,"paramLimit":"","paramValue":"","paramNotNull":0,"paramValueList":[]}],"resultInfo":[{"paramID":10665,"paramKey":"desc","paramName":"\u8bf4\u660e","paramNotNull":1,"paramValueList":[{"valueID":9352,"value":"*","valueDescription":"\u4f60\u6ce8\u610f\u5230\u4e86\u4e48\uff0c\u8fd9\u662f\u4e00\u4e2a\u201c\u975e\u5fc5\u542b\u201d\u7684\u8fd4\u56de\u7ed3\u679c\uff0ceoapi\u751a\u81f3\u53ef\u4ee5\u8bb0\u5f55\u8fd4\u56de\u7ed3\u679c\u662f\u5426\u8fd4\u56de\uff01"}]},{"paramID":10666,"paramKey":"status","paramName":"\u63a5\u53e3\u8bf7\u6c42\u72b6\u6001","paramNotNull":0,"paramValueList":[{"valueID":9353,"value":"error","valueDescription":"\u9519\u8bef"},{"valueID":9354,"value":"failure","valueDescription":"\u5931\u8d25"},{"valueID":9355,"value":"success","valueDescription":"\u6210\u529f\uff0c\u4e0d\u4ec5\u8bf7\u6c42\u7684\u53c2\u6570\u53ef\u4ee5\u8bb0\u5f55\u53ef\u80fd\u6027\uff0c\u8fde\u8fd4\u56de\u503c\u7684\u4e5f\u53ef\u4ee5\u8bb0\u5f55\u53ef\u80fd\u6027\uff01"}]},{"paramID":10667,"paramKey":"type","paramName":"\u63a5\u53e3\u7c7b\u578b","paramNotNull":0,"paramValueList":[{"valueID":9356,"value":"demo","valueDescription":""}]},{"paramID":10668,"paramKey":"type::secondType","paramName":"\u4e8c\u7ea7\u8fd4\u56de\u7ed3\u679c\u793a\u4f8b","paramNotNull":0,"paramValueList":[{"valueID":9357,"value":"0","valueDescription":"success"},{"valueID":9358,"value":"1","valueDescription":"failure"}]}]}]}]}';
            $demoProject = json_decode($demoProject, TRUE);
            $importDao = new ImportDao;
            $importDao->importEoapi($demoProject, $userID);
            return TRUE;
        } else
            return FALSE;
    }

    /**
     * 检查用户名是否存在
     * @param $userName string 用户名
     * @return bool
     */
    public function checkUserNameExist(&$userName)
    {
        $dao = new GuestDao;
        return $dao->checkUserNameExist($userName);
    }

    /**
     * 登录
     * @param $loginName string 登录用户名
     * @param $loginPassword string 登录密码
     * @return bool
     */
    public function login(&$loginName, &$loginPassword)
    {
        $time = time();
        $dao = new GuestDao;
        $userInfo = $dao->getLoginInfo($loginName);
        if (md5($loginPassword) == $userInfo['userPassword']) {
            @session_start();
            $_SESSION['userID'] = $userInfo['userID'];
            $_SESSION['userName'] = $userInfo['userName'];
            $_SESSION['userNickName'] = $userInfo['userNickName'];
            return TRUE;
        } else
            return FALSE;
    }

}

?>