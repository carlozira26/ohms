<?php
$app->post('/patients/addedit', 'PatientsController:addEditPatient');
$app->post('/patients/assign', 'PatientsController:DoctorAssign');
$app->post('/patients/status/{id}', 'PatientsController:changePatientStatus');
$app->post('/patient/result','PatientsController:uploadResult');
$app->get('/patients/list', 'PatientsController:patientList');
$app->get('/patients/lastid', 'PatientsController:getLastID');
$app->get('/patients/doctor/{id}', 'PatientsController:fetchDoctor');
$app->get('/patients/diagnostic', 'PatientsController:fetchDiagnosticResults');
$app->get('/patients/diagnostic/file', 'PatientsController:fetchPatientFile');
$app->get('/patients/infected', 'PatientsController:fetchInfected');
$app->get('/patients/outcomes', 'PatientsController:fetchOutcomes');
$app->get('/patient/laboratory/{id}', 'PatientsController:checkLaboratory');
$app->get('/patient/count', 'PatientsController:countPatients');
$app->get('/patient/medicinelogs', 'PatientsController:fetchIntakeLogs');