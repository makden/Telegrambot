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
        
        $this->addToJsonDB($content);
        //file_put_contents("cont.log",print_r($content,true));
        
        if(isset($content['msg'],$content['chat_id'])){
            $this->chat_id = trim($content['chat_id']);
            $this->sendHTML(trim($content['msg']));
        }

        if(isset($content["message"]))
        {
           $this->chat_id =  $content["message"]['from']['id'];
        }elseif(isset($content["callback_query"])){
           $this->chat_id =  $content["callback_query"]['from']['id'];
        }
        
        // Если это команда
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
        // Если это контакт
        elseif(isset($content["message"]) AND isset($content["message"]["contact"])) // Если передали телефон
        {
            
            $this->contact($content["message"]["contact"]);
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
    
    public function keybord_h($params){
        foreach ($params as $btn){
            $btns[] = $btn;
        }
        
        return json_encode([
            "keyboard" =>[$btns],
            "resize_keyboard" => true,
            "one_time_keyboard" => true
        ]);
    }
    

    
     public function keybord_v($params){
        foreach ($params as $btn){
            $btns[] = [$btn];
        }
        
        return json_encode([
            "keyboard" =>$btns,
            "resize_keyboard" => true,
            "one_time_keyboard" => true
        ]);
    }
    
    public function buttons($buttons)
    {
        foreach ($buttons as $button){
            $arrButton[]=$button;    
        }
        
    	$menu[] = $arrButton;//[["text" => "Ролы", "callback_data" => "1"],["text" => "Пиццу", "callback_data" => "2"]];
        return json_encode(["inline_keyboard" => $menu]);
    }
    
    public function button($title,$val){
        return ["text" => $title, "callback_data" => $val];
    }
    
    public function buttonUrl($title,$val){
        return ["text" => $title, "url" => $val];
    }
    
     public function buttonApp($title,$val){
        return ["text" => $title, "web_app"=>["url" => $val]];
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
    
    
    private  function addToJsonDB($data,$db="./history.json")
    {
    //	if(!is_array($data)) return false;
    //	if(empty($data)) return false;
    	
    	if(file_exists($db)){
    		$data_arr = json_decode(file_get_contents($db),true);
    	
    		array_unshift($data_arr,$data);
    		file_put_contents($db,json_encode($data_arr),LOCK_EX);
    	}else{
    		file_put_contents($db,json_encode([$data]),LOCK_EX);
    	}
    }
 
}