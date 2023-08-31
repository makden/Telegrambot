<?php
error_reporting(E_ALL);
ini_set('display_errors', 'Off'); 
ini_set('log_errors', 'On');
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/logs/php-errors.log');

include_once "class.php";


Class Handler extends TeleBot{


    function hi()
    { 
        
        $this->send("his");
    }
    
    function phone(){
        
        $params['text']="Выберите пункт из меню ниже!";
        $params['reply_markup']=$this->keybord_v([$this->btnPhone("Мой номер телефона"),$this->btnGeo("Мои координаты"),$this->btnApp("https://bot.z3x.ru/telegram/GeoLocPB_bot/app.php")]);
        
        $this->send($params);
    }
    
     function select(){
        
        $params['text']="Выберите пункт из меню ниже!";
        $params['reply_markup']=$this->buttons([$this->button("Мой номер телефона",1),$this->button("Мои координаты",2),$this->button("php",3)]);
        
        $this->send($params);
    }
    
}



$bot = new Handler(file_get_contents('php://input'),"6512374789:AAEqxudxeb1NG0zSrSSJku_JM153v9_M-4Y");
//$bot->hook("https://bot.z3x.ru/telegram/GeoLocPB_bot/main.php");


echo "<hr>end";