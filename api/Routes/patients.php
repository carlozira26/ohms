<?php
$app->post('/patients/addedit', 'PatientsController:addEditPatient');
$app->post('/patients/assign', 'PatientsController:DoctorAssign');
$app->post('/patients/status/{id}', 'PatientsController:changePatientStatus');
$app->post('/patient/result','PatientsController:uploadResult');
$app->get('/patients/list', 'PatientsController:patientList');
$app->get('/patients/lastid', 'PatientsController:getLastID');
$app->get('/patients/doctor/{id}', 'PatientsController:fetchDoctor');
$app->get('/patients/diagnostic', 'PatientsController:fetchDiagnosticResults');
