<?php

$params = get_input('params');

// foreach($params as $param)
// {
	// if(empty($param))
	// {
	// register_error('Please fill out all the fields');
	// forward(REFERER);
	// }
	// else if(!is_numeric($param))
	// {
	// register_error('Only numeric values are allowed');
	// forward(REFERER);
	// }
// }

/* if(empty(get_input('classA')) || empty(get_input('classB')) || empty(get_input('classC')) || empty(get_input('classD')) || empty(get_input('classE')))
{
	register_error('Please fill out all the fields');
	forward(REFERER);
}

if(!is_numeric(get_input('classA1')) || !is_numeric(get_input('classB1')) || !is_numeric(get_input('classC1')) || !is_numeric(get_input('classD1')) || !is_numeric(get_input('classE1')) || !is_numeric(get_input('classA2')) || !is_numeric(get_input('classB2')) || !is_numeric(get_input('classC2')) || !is_numeric(get_input('classD2')) || !is_numeric(get_input('classE2')))
{
	register_error('Only numeric values are allowed');
	forward(REFERER);
} */

$guid = get_input('guid') ? get_input('guid') : '';

if(!empty($guid))
{
	$scaling = new ElggObject($guid);
	$scaling->guid = $guid;
	
	$message = "Fields updated successfully !!!";
	
}
else
{
	$scaling = new ElggObject();	
	$message = "Fields added successfully !!!";
}


$scaling->subtype = 'poins_scaling';
$scaling->classA1 = $params['classA1'];
$scaling->classA2 = $params['classA2'];
$scaling->classB1 = $params['classB1'];
$scaling->classB2 = $params['classB2'];
$scaling->classC1 = $params['classC1'];
$scaling->classC2 = $params['classC2'];
$scaling->classD1 = $params['classD1'];
$scaling->classD2 = $params['classD2'];
$scaling->classE1 = $params['classE1'];
$scaling->classE2 = $params['classE2'];

$result = $scaling->save();

if(!empty($result))
{
system_message($message);
}

forward(REFERER);
