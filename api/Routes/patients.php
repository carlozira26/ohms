<?php
$app->post('/patients/addedit', 'PatientsController:addEditPatient');
$app->post('/patients/assign', 'PatientsController:DoctorAssign');
$app->post('/patients/status/{id}', 'PatientsController:changePatientStatus');
$app->post('/patient/result','PatientsController:uploadResult');
$app->post('/patient/profile/submit', 'PatientsController:PatientProfileSubmit');
$app->post('/patients/message/submit', 'PatientsController:MessagePatients');

$app->get('/patients/list', 'PatientsController:patientList');
$app->get('/patients/lastid', 'PatientsController:getLastID');
$app->get('/patients/doctor/{id}', 'PatientsController:fetchDoctor');
$app->get('/patients/diagnostic', 'PatientsController:fetchDiagnosticResults');
$app->get('/patients/diagnostic/file', 'PatientsController:fetchPatientFile');
$app->get('/patients/outcomes', 'PatientsController:fetchOutcomes');
$app->get('/patient/laboratory/{id}', 'PatientsController:checkLaboratory');
$app->get('/patient/count', 'PatientsController:countPatients');
$app->get('/patient/medicinelogs', 'PatientsController:fetchIntakeLogs');
$app->get('/patient/profile','PatientsController:PatientProfile');
$app->get('/patients/outcomes/fetch','PatientsController:fetchPatientList');
$app->get('/patients/app/list', 'PatientsController:patientAppList');
$app->get('/patient/app/medicine', 'PatientsController:PatientAppMedicine');
$app->get('/patients/check', 'PatientsController:checkPatient');
$app->get('/patients/message/count', 'PatientsController:countPatientForMessage');
$app->get('/patients/logstat','PatientsController:logStatus');
$app->get('/patient/count/intake', 'PatientsController:countIntake');