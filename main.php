<?php
error_reporting(E_ALL);
ini_set('display_errors', 'Off'); 
ini_set('log_errors', 'On');
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/logs/php-errors.log');

include_once "class.php";


// Добавить кнопки в чат
//$params['reply_markup']=$this->buttons([$this->button("Мой номер телефона",1),$this->button("Мои координаты",2),$this->button("php",3)]);
// Зарезервированные метода

//contact(["phone_number"=>"","first_name"=>"","user_id"=>""]) - Если отправлен контакт

//webApp(param) - Если данные передали из app приложения

Class Handler extends TeleBot{


    private $userslist = [];

    function hi(){ 
        
        $this->send("his");
    }
    
    function start(){
        
        $params['text']="Для начало работы, необходимо авторизоваться\n Нажмите 'Отправить номер телефона'";
        $params['reply_markup']=$this->keybord_v([$this->btnPhone("Отправить номер телефона")]);
       // ;
      //    $params['reply_markup']=$this->keybord_v([$this->btnApp("https://bot.z3x.ru/b23.html","Открыть приложение1")]);
        $this->send($params);
    }
    
     function appopenpage(){
        
        $params['text']="Нажмите на `Открыть приложение` под полем сообщения!";
        $params['reply_markup']=$this->keybord_v([$this->btnApp("https://bot.z3x.ru/telegram/GeoLocPB_bot/app/dist/","Открыть приложение")]);
        
        $this->send($params);
    }
    
    // вызывается, если отправили контакт
    function contact($contact){
     
        $users_arr = read_json_to_arr("./db/users.json");
        $access = false;
        
        foreach ($users_arr as $phone=>$data){
            if($contact['phone_number'] == trim($phone)){
                $users_arr[$phone]['chat_id']=$contact['user_id'];
                $access = $users_arr[$phone]['name'];
            }
        }
        
        addToJsonDB("./db/users.json",$users_arr,true);
        
        if($access){
        
            $this->sendHTML("<b>".$access."</b>, Вы успено авторизованы! \n  Следуйте инструкции ниже!");
            sleep(2);
            $this->appopenpage();
        }else{
           
            $this->sendHTML("<b>Я Вас не знаю!</b>! \n  Всего хорошего!");
        }
    }
    
    // Вызывается при отправке данных с веб - приложения
    public function webApp($answer)
    {

        switch(trim($answer['button_text'])){
            case 'Открыть приложение':
                $this->webAppStatus($answer['data']);
                break;
            
            default:
                $this->sendHTML("Отравленные данные не обратотаны. Обратитесь к разработчику!");
            break;
        }
    }
    
    
    private function webAppStatus($data)
    { 
        $this->id2phone();
        $data = json_decode($data,true);
        $data['user_id'] = $this->chat_id;
        $data['phone'] = $this->userslist[$this->chat_id]['phone'];
        $data['user'] = $this->userslist[$this->chat_id]['user'];

        addToJsonDB("./db/data.json",$data);
        $this->sendHTML("Данные переданы в 1С");
    }
    
    function usrlst(){
        $params['text']="Открыть таблицу";
        $params['reply_markup']=$this->buttons([$this->buttonApp("Пользователи","https://bot.z3x.ru/telegram/GeoLocPB_bot/users/index.html")]);
        $this->send($params);
    }
    
    
    
    private function id2phone(){
          $users_arr = read_json_to_arr("./db/users.json");
          
        foreach ($users_arr as $phone=>$data){
                $users_arrs[$data['chat_id']]['phone']=$phone;
                $users_arrs[$data['chat_id']]['user']=$data['name'];
        }
        
        return $this->userslist = $users_arrs;
    } 

    
}


  function addToJsonDB($db="db.json",$data,$rw=false){
    	if(!is_array($data)) return false;
    	if(empty($data)) return false;
    	
    	if(file_exists($db)){
    	    if($rw==false){ // Полностью перезаписать 
    		    $data_arr = json_decode(file_get_contents($db),true);
    		    array_unshift($data_arr,$data);
    	        file_put_contents($db,json_encode($data_arr),LOCK_EX);
    	    }else{
    	        file_put_contents($db,json_encode($data),LOCK_EX);
    	    }
    		
    	}else{
    		file_put_contents($db,json_encode([$data]),LOCK_EX);
    	}
    }

function read_json_to_arr($json="db.json"){
    if(file_exists($json)){
       return  json_decode(file_get_contents($json),true);
    }else{
        echo "Файл ".$json." не нейден.";
    }
    
}


$bot = new Handler(file_get_contents('php://input'),"6512374789:AAEqxudxeb1NG0zSrSSJku_JM153v9_M-4Y");
//$bot->hook("https://bot.z3x.ru/telegram/GeoLocPB_bot/main.php");


echo '<span style="writing-mode: tb-rl;">(:{)</span>';

