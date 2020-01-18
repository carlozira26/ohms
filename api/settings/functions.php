<?php

$container = $app->getContainer();
$container['GUMP'] = function(){
    return new GUMP();
};

$container['isEmail'] = function($string = ''){
    return function($string = ''){
        $GUMP = new GUMP();
        $GUMP->validation_rules(array( "email" => "required|valid_email" ));
        $GUMP->filter_rules(array( "email" => "trim|sanitize_string" ));
        $run = $GUMP->run(array(
           "email" => $string
        ));
        if($run){
           return true;
        }else{
           return false;
        }
    };
};

$container['generateRandomNumbers'] = function(){
    return function($length = 4){
        return substr(str_shuffle(str_repeat($x='0123456789', ceil($length/strlen($x)) )),1,$length);
    };
};

$container['generateRandomChars'] = function(){
    return function($length = 7){
        $characters = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    };
};

$container['passwordConverter'] = function(){
    return function($password = ''){
        return md5("aMG3n3r4n".$password."Sh1nch4n0");
    };
};

$container['splitFirstnameLastname'] = function(){
    return function($name = ''){
        $removedMiddleInitial = preg_replace('/[A-Za-z][.]$/', "", $name);
        $removedMiddleInitial = trim($removedMiddleInitial);
        $names = explode(" ", $removedMiddleInitial);

        $fullname = "";
        foreach ($names as $key => $value) {
            if($key > 0){
                $fullname .= $value." ";
            }
        }

        $fullname .= $names[0];
        $fullname = str_replace(".", "", $fullname);
         
        $last_name = (strpos($fullname, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $fullname);
        $first_name = trim( preg_replace('#'.$last_name.'#', '', $fullname ) );
        return array($first_name, $last_name);
    };
};
?>
