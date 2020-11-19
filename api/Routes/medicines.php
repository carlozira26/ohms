<?php
$app->post('/medicine/addedit/{type}', 'MedicinesController:addEditMedicine');
$app->post('/medicine/delete/{id}', 'MedicinesController:MedicineDelete');
$app->post('/medicine/newvalue','MedicinesController:newMedicineVal');
$app->post('/medicine/list/submit', 'MedicinesController:submitPatientMedicine');

$app->get('/medicine/list', 'MedicinesController:MedicineList');
$app->get('/medicine/details/{id}', 'MedicinesController:MedicineDetails');
$app->get('/medicine/get', 'MedicinesController:getMedicineList');
$app->get('/medicine/patient/list','MedicinesController:getPatientMedicine');
$app->get('/medicine/patient/schedule','MedicinesController:getSchedule');
$app->get('/medicine/instructions','MedicinesController:medicineInstructions');
$app->get('/medicine/logs/reason','MedicinesController:checkReason');
$app->get('/medicine/healthcare/monitoring','MedicinesController:getChartData');