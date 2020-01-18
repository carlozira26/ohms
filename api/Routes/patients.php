<?php
$app->post('/patients/addedit', 'PatientsController:addEditPatient');
$app->post('/patients/assign', 'PatientsController:DoctorAssign');
$app->get('/patients/list', 'PatientsController:patientList');
$app->get('/patients/lastid', 'PatientsController:getLastID');
$app->get('/patients/doctor/{id}', 'PatientsController:fetchDoctor');
