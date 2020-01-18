<?php
$app->post('/users/login', 'UsersController:UserAuth');
$app->get('/users/account/{id}', 'UsersController:UserAccount');
$app->post('/users/confirm/{id}', 'UsersController:ConfirmPassword');
$app->post('/users/change/{id}', 'UsersController:ChangeInformation');
$app->get('/users/doctor', 'UsersController:DoctorsList');