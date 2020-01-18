<?php
// Add Eloquent Service to the Slim's Container to be able to access within the app
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db'], 'default');
$capsule->setAsGlobal();
$capsule->bootEloquent();
$container['db'] = function($container) use ($capsule){
    return $capsule;
};

?>
