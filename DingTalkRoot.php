<?php
/**
 * Created by PhpStorm.
 * User: foreverzjz
 * Date: 2018/7/31
 * Time: 上午10:29
 */

namespace Business;

class DingTalkRoot
{
    private $_requestUrl = "https://oapi.dingtalk.com/robot/send";
    private $_accessToken;
    private $_msgType;
    private $_content;
    private $_atMobiles = [];
    private $_isAtAll = false;
    private $_title;
    private $_picUrl = "";
    private $_messageUrl = "";
    private $_hideAvatar = 0;
    private $_btnOrientation = 1;
    private $_singleTitle;
    private $_singleURL = "";
    private $_buttons = [];
    private $_showBtn = 0;
    private $_feedCardLinks = [];

    private $_msgTypeOption = [
        'text',
        'link',
        'markdown',
        'actionCard',
        'feedCard'
    ];

    public function __construct($accessToken)
    {
        $this->_accessToken = $accessToken;
    }

    public function accessToken($accessToken)
    {
        $this->_accessToken = $accessToken;
        return $this;
    }

    public function text()
    {
        $this->_msgType = $this->_msgTypeOption[0];
        return $this->send();
    }

    public function link()
    {
        $this->_msgType = $this->_msgTypeOption[1];
        return $this->send();
    }

    public function markdown()
    {
        $this->_msgType = $this->_msgTypeOption[2];
        return $this->send();
    }

    public function actionCard()
    {
        $this->_msgType = $this->_msgTypeOption[3];
        return $this->send();
    }

    public function feedCard()
    {
        $this->_msgType = $this->_msgTypeOption[4];
        return $this->send();
    }

    public function atAll()
    {
        $this->_isAtAll = true;
        return $this;
    }

    public function content($content)
    {
        $this->_content = $content;
        return $this;
    }

    public function atPerson($mobile)
    {
        if(is_array($mobile)){
            $this->_atMobiles = array_merge($this->_atMobiles, $mobile);
        }else if(is_string($mobile)){
            $mobiles = explode(',', $mobile);
            $this->_atMobiles = array_merge($this->_atMobiles, $mobiles);
        }
        return $this;
    }

    public function title($title)
    {
        $this->_title = $title;
        return $this;
    }

    public function picUrl($picUrl)
    {
        $this->_picUrl = $picUrl;
        return $this;
    }

    public function messageUrl($messageUrl)
    {
        $this->_messageUrl = $messageUrl;
        return $this;
    }

    public function hideAvatar()
    {
        $this->_hideAvatar = 1;
        return $this;
    }

    public function columnBtn()
    {
        $this->_btnOrientation = 0;
        return $this;
    }

    public function singleTitle($singleTitle)
    {
        $this->_singleTitle = $singleTitle;
        return $this;
    }

    public function singleUrl($singleUrl)
    {
        $this->_singleURL = $singleUrl;
        return $this;
    }

    public function addBtn($title, $url)
    {
        $this->_buttons[] = [
            'title' => $title,
            'actionURL' => $url
        ];
        $this->_showBtn = 1;
        return $this;
    }

    public function addLinks($title, $messageUrl, $picUrl)
    {
        $this->_feedCardLinks[] = [
            'title' => $title,
            'messageURL' => $messageUrl,
            'picURL' => $picUrl
        ];
        return $this;
    }

    private function send()
    {
        $requestUrl = $this->_requestUrl."?access_token=".$this->_accessToken;
        $requestData = [
            'msgtype'=>$this->_msgType
        ];
        switch($this->_msgType){
            case $this->_msgTypeOption[0]:
                $requestData['text']['content'] = $this->_content;
                $requestData['at'] = [
                    'atMobiles' => $this->_atMobiles,
                    'isAtAll' => $this->_isAtAll
                ];
                break;
            case $this->_msgTypeOption[1]:
                $requestData['link'] = [
                    'text' => $this->_content,
                    'title' => $this->_title,
                    'picUrl' => $this->_picUrl,
                    'messageUrl' => $this->_messageUrl
                ];
                break;
            case $this->_msgTypeOption[2]:
                $requestData['markdown'] = [
                    'title' => $this->_title,
                    'text' => $this->_content
                ];
                $requestData['at'] = [
                    'atMobiles' => $this->_atMobiles,
                    'isAtAll' => $this->_isAtAll
                ];
                break;
            case $this->_msgTypeOption[3]:
                $requestData['actionCard'] = [
                    'title' => $this->_title,
                    'text' => $this->_content,
                    'hideAvatar' => $this->_hideAvatar,
                    'btnOrientation' => $this->_btnOrientation,
                ];
                if($this->_showBtn){
                    $requestData['actionCard']['btns'] = $this->_buttons;
                }else{
                    $requestData['actionCard']['singleTitle'] = $this->_singleTitle;
                    $requestData['actionCard']['singleURL'] = $this->_singleURL;
                }
                break;
            case $this->_msgTypeOption[4]:
                $requestData['feedCard']['links'] = $this->_feedCardLinks;
                break;
        }
        $result = $this->requestUrl($requestUrl, $requestData);
        if(!$result || $result['errcode'] != 0){
            //ErrorHandler::setErrorInfo($result['errmsg'], $result['errcode']);
            //错误信息返回['errmsg'=>"name is null", 'errcode'=>400042]
            return false;
        }
        return true;
    }

    private function requestUrl($requestUrl, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return json_decode($data, true);
    }
}