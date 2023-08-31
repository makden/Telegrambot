<?php
Class TeleBot
{
    
    private $token;
    private $chat_id;
    private $host_api="https://api.telegram.org/bot";
    

    public function __construct($content,$token)
    {
        $this->token = $token;
        $content = json_decode($content,true);

        //file_put_contents("cont.log",print_r($content,true));

        if(isset($content["message"])){
           $this->chat_id =  $content["message"]['from']['id'];
        }elseif(isset($content["callback_query"])){
           $this->chat_id =  $content["callback_query"]['from']['id'];
        }

        if(isset($content["message"]) AND isset($content["message"]["entities"]))
        {
            $command = substr($content["message"]["text"],1);
               
            if(method_exists($this,trim($command))){
               //$res = call_user_func_array([$this,trim($command)]);
                $this->{$command}();
            }else{
                $this->notfindcomamand($command);
            }
        }

       
    }


    private function notfindcomamand($command)
    {
     
        $this->sendHTML("Команда  <b>".$command."</b> не определена!");
    }   
    
    



    public function sendHTML($text)
    {
        $param['text'] = $text;
        $param['parse_mode']="HTML";
        $this->send($param);
    }


    public function send($params)
    {
        if(!is_array($params))
        {
            $text = $params;
            unset($params);
            $params['text'] = $text;
        }

        $params['chat_id']=$this->chat_id;
        $this->request("sendMessage",$params);
    }
    
    public function hook($url)
    {
        $params["url"] =  $url;
        
        echo $this->request("setWebhook",$params);
    }
    
    public function key_bord_h($params){
        foreach ($params as $btn){
            $btns[] = $btn;
        }
        
        return json_encode([
            "keyboard" =>[$btns],
            "resize_keyboard" => true,
            "one_time_keyboard" => true
        ]);
    }
    
    public function btnPhone($val="Номер телефона"){
        return ["text" => $val, "request_contact" => true];
    }
    
    public function btnGeo($val="Мои координаты"){
        return ["text" => $val, "request_location" => true];
    }
    
    public function btnApp($url="",$val="App"){
        if(empty($url)) return $this->send("Кнопка btnApp должна иметь 1-й пар. URL");
        return ["text" => $val, "web_app"=>["url"=>$url]];
    }


    private function request($method, $params = [])
    {

        $ch = curl_init($this->host_api . $this->token . '/' . $method);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result,true);
        if(isset($result['ok'])){
            return $result['description'];
        }else{
            file_put_contents("./error.log", print_r($result,true));
            
        }
       

    }
}