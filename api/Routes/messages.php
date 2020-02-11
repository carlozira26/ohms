<?php
$app->post('/messages/update/{patientid}','ChatController:getDoctorChat');
$app->post('/sms/{number}', 'ChatController:messageSend');