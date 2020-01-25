<?php
$app->post('/users/login', 'UsersController:UserAuth');
$app->post('/users/confirm/{id}', 'UsersController:ConfirmPassword');
$app->post('/users/change/{id}', 'UsersController:ChangeInformation');
$app->post('/users/doctor/create', 'UsersController:CreateAccount');
$app->get('/users/account/{id}', 'UsersController:UserAccount');
$app->get('/users/doctor', 'UsersController:DoctorsList');
$app->get('/users/doctor/account', 'UsersController:DoctorsListAccount');