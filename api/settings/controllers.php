<?php
	$container['UsersController'] = function($container){
		return new \Controllers\UsersController($container);
	};
	$container['PatientsController'] = function($container){
		return new \Controllers\PatientsController($container);
	};
	$container['MedicinesController'] = function($container){
		return new \Controllers\MedicinesController($container);
	};
?>