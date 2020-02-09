<?php
$app->post('/medicine/addedit/{type}', 'MedicinesController:addEditMedicine');
$app->post('/medicine/delete/{id}', 'MedicinesController:MedicineDelete');
$app->get('/medicine/list', 'MedicinesController:MedicineList');
$app->get('/medicine/details/{id}', 'MedicinesController:MedicineDetails');
$app->get('/medicine/get', 'MedicinesController:getMedicineList');
$app->post('/medicine/list/submit/{id}', 'MedicinesController:submitPatientMedicine');
$app->get('/medicine/patient/list/{id}','MedicinesController:getPatientMedicine');
$app->get('/medicine/patient/schedule/{id}','MedicinesController:getPatientMedicineSchedule');
$app->get('/medicine/value','MedicinesController:getMedicineVal');
$app->post('/medicine/newvalue','MedicinesController:newMedicineVal');