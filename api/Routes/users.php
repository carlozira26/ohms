<?php
$app->post('/users/login', 'UsersController:UserAuth');
$app->post('/users/confirm/{id}', 'UsersController:ConfirmPassword');
$app->post('/users/change/{id}', 'UsersController:ChangeInformation');
$app->post('/users/doctor/create', 'UsersController:CreateAccount');
$app->post('/users/activate/{id}', 'UsersController:AccountActivate');
$app->post('/users/schedule','UsersController:DoctorSchedule');
$app->get('/users/account/{id}', 'UsersController:UserAccount');
$app->get('/users/doctor', 'UsersController:DoctorsList');
$app->get('/users/doctor/account', 'UsersController:DoctorsListAccount');
$app->get('/users/specializations', 'UsersController:SpecializationList');
$app->get('/users/schedule/fetch/{id}', 'UsersController:FetchDoctorSchedule');
$app->get('/users/profile', 'UsersController:DoctorsProfile');