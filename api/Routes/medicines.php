<?php
$app->post('/medicine/addedit/{type}', 'MedicinesController:addEditMedicine');
$app->post('/medicine/delete/{id}', 'MedicinesController:MedicineDelete');
$app->post('/medicine/newvalue','MedicinesController:newMedicineVal');
$app->post('/medicine/list/submit/{id}', 'MedicinesController:submitPatientMedicine');

$app->get('/medicine/list', 'MedicinesController:MedicineList');
$app->get('/medicine/details/{id}', 'MedicinesController:MedicineDetails');
$app->get('/medicine/get', 'MedicinesController:getMedicineList');
$app->get('/medicine/patient/list','MedicinesController:getPatientMedicine');
$app->get('/medicine/patient/schedule/{id}','MedicinesController:getPatientMedicineSchedule');
$app->get('/medicine/value','MedicinesController:getMedicineVal');
$app->get('/medicine/instructions','MedicinesController:medicineInstructions');
$app->get('/medicine/logs/reason','MedicinesController:checkReason');
