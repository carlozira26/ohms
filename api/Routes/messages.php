<?php
$app->post('/messages/fetch/{receiverid}','ChatController:getMessages');
$app->post('/messages/submit','ChatController:submitMessage');
$app->get('/messages/list','ChatController:getMessageList');
$app->get('/messages/reminder/cron', 'ChatController:patientReminderCron');