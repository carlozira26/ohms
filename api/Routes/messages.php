<?php
$app->post('/messages/update/{patientid}','ChatController:getDoctorChat');
$app->get('/messages/reminder/cron', 'ChatController:patientReminderCron');