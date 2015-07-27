<style>
.textwidth{width:40px;}
.formerror{border:1px solid red;}
</style>

<?php

/**
 * Userpoints for for manually adding points
 */

$result=elgg_get_entities(array('type' => 'object','subtype' => 'poins_scaling'));
$guid = $result[0]->guid;

$Values = elgg_get_metadata(array('guid' => $guid));

$array = array();

foreach($Values as $data)	
{
	$array[$data->name] = $data->value;
}

$classA1 = isset($array['classA1']) ? $array['classA1'] : '';
$classA2 = isset($array['classA2']) ? $array['classA2'] : '';
$classB1 = isset($array['classB1']) ? $array['classB1'] : '';
$classB2 = isset($array['classB2']) ? $array['classB2'] : '';
$classC1 = isset($array['classC1']) ? $array['classC1'] : '';
$classC2 = isset($array['classC2']) ? $array['classC2'] : '';
$classD1 = isset($array['classD1']) ? $array['classD1'] : '';
$classD2 = isset($array['classD2']) ? $array['classD2'] : '';
$classE1 = isset($array['classE1']) ? $array['classE1'] : '';
$classE2 = isset($array['classE2']) ? $array['classE2'] : '';

$action = elgg_get_site_url() . 'action/elggx_groupuserpoints/scaling';

$form = "<br><b>" . 'Range format should be (min-max)' . "</b><br>";

$form .= "<br><b>" . 'Class A :' . "</b><br>";
$form .= elgg_view('input/text', array('name' => "params[classA1]", 'id' => "classA1", 'class' => "textwidth", 'value' => $classA1));
$form .= elgg_view('input/text', array('name' => "params[classA2]", 'id' => "classA2", 'class' => "textwidth", 'value' => $classA2));
$form .= "<br><br>";

$form .= "<b>" . 'Class B :' . "</b><br>";
$form .= elgg_view('input/text', array('name' => "params[classB1]", 'id' => "classB1", 'class' => "textwidth", 'value' => $classB1));
$form .= elgg_view('input/text', array('name' => "params[classB2]", 'id' => "classB2", 'class' => "textwidth", 'value' => $classB2));
$form .= "<br><br>";

$form .= "<b>" . 'Class C :' . "</b><br>";
$form .= elgg_view('input/text', array('name' => "params[classC1]", 'id' => "classC1", 'class' => "textwidth", 'value' => $classC1));
$form .= elgg_view('input/text', array('name' => "params[classC2]", 'id' => "classC2", 'class' => "textwidth", 'value' => $classC2));
$form .= "<br><br>";

$form .= "<b>" . 'Class D :' . "</b><br>";
$form .= elgg_view('input/text', array('name' => "params[classD1]", 'id' => "classD1", 'class' => "textwidth", 'value' => $classD1));
$form .= elgg_view('input/text', array('name' => "params[classD2]", 'id' => "classD2", 'class' => "textwidth", 'value' => $classD2));
$form .= "<br><br>";

$form .= "<b>" . 'Class E :' . "</b><br>";
$form .= elgg_view('input/text', array('name' => "params[classE1]", 'id' => "classE1", 'class' => "textwidth", 'value' => $classE1));
$form .= elgg_view('input/text', array('name' => "params[classE2]", 'id' => "classE2", 'class' => "textwidth", 'value' => $classE2));
$form .= "<br><br>";

$form .= elgg_view("input/hidden", array('name' => "guid", 'value' => $guid));

$form .= elgg_view("input/securitytoken");

$form .= elgg_view('input/submit', array('value' => elgg_echo("save")));
echo elgg_view('input/form', array('action' => $action, 'id' => 'scaling_form', 'body' => $form));

?>

<script type="text/javascript">
 
    $("#scaling_form").submit(function(event) {
		
	var error = 0;
		
	$("input[type=text]").each(function()
	{
		if(!$(this).val())
		{
			$(this).addClass('formerror');
			$(".elgg-system-messages").html('<li class="elgg-message elgg-state-error" style="opacity: 1;"><p>All fields are mandatory </p></li>');
			error++;
		}
		else if(isNaN($(this).val()))
		{
			$(this).addClass('formerror');
			$(".elgg-system-messages").html('<li class="elgg-message elgg-state-error" style="opacity: 1;"><p>Only numeric values are allowed </p></li>');
			error++;
		}
		else
		{
			$(this).removeClass('formerror');
		}
	
	});

	if(error == 0)
	{
		
		if(parseInt($("#classA1").val()) > parseInt($("#classA2").val()))
		{
			$(".elgg-system-messages").html('<li class="elgg-message elgg-state-error" style="opacity: 1;"><p>First Param Should be less than the second for Class A </p></li>');
			$("#classA1").addClass('formerror');
			$("#classA2").addClass('formerror');
			error++;
		}
		else if(parseInt($("#classB1").val()) > parseInt($("#classB2").val()))
		{
			$(".elgg-system-messages").html('<li class="elgg-message elgg-state-error" style="opacity: 1;"><p>First Param Should be less than the second for Class B </p></li>');
			$("#classB1").addClass('formerror');
			$("#classB2").addClass('formerror');
			error++;
		}
		else if(parseInt($("#classC1").val()) > parseInt($("#classC2").val()))
		{
			$(".elgg-system-messages").html('<li class="elgg-message elgg-state-error" style="opacity: 1;"><p>First Param Should be less than the second for Class C </p></li>');
			$("#classC1").addClass('formerror');
			$("#classC2").addClass('formerror');
			error++;
		}
		else if(parseInt($("#classD1").val()) > parseInt($("#classD2").val()))
		{
			$(".elgg-system-messages").html('<li class="elgg-message elgg-state-error" style="opacity: 1;"><p>First Param Should be less than the second for Class D </p></li>');
			$("#classD1").addClass('formerror');
			$("#classD2").addClass('formerror');
			error++;
		}
		else if(parseInt($("#classE1").val()) > parseInt($("#classE2").val()))
		{
			$(".elgg-system-messages").html('<li class="elgg-message elgg-state-error" style="opacity: 1;"><p>First Param Should be less than the second for Class E </p></li>');
			$("#classE1").addClass('formerror');
			$("#classE2").addClass('formerror');
			error++;
		}
		else if(parseInt($("#classB2").val()) > parseInt($("#classA1").val()))
		{
			$(".elgg-system-messages").html('<li class="elgg-message elgg-state-error" style="opacity: 1;"><p>First Param Of class A Should be greater than the second param of Class B </p></li>');
			$("#classA1").addClass('formerror');
			$("#classB2").addClass('formerror');
			error++;
		}
		else if(parseInt($("#classC2").val()) > parseInt($("#classB1").val()))
		{
			$(".elgg-system-messages").html('<li class="elgg-message elgg-state-error" style="opacity: 1;"><p>First Param Of class B Should be greater than the second param of Class C </p></li>');
			$("#classB1").addClass('formerror');
			$("#classC2").addClass('formerror');
			error++;
		}
		else if(parseInt($("#classD2").val()) > parseInt($("#classC1").val()))
		{
			$(".elgg-system-messages").html('<li class="elgg-message elgg-state-error" style="opacity: 1;"><p>First Param Of class C Should be greater than the second param of Class D </p></li>');
			$("#classC1").addClass('formerror');
			$("#classD2").addClass('formerror');
			error++;
		}
		else if(parseInt($("#classE2").val()) > parseInt($("#classD1").val()))
		{
			$(".elgg-system-messages").html('<li class="elgg-message elgg-state-error" style="opacity: 1;"><p>First Param Of class D Should be greater than the second param of Class E </p></li>');
			$("#classD1").addClass('formerror');
			$("#classE2").addClass('formerror');
			error++;
		}
	
	}
	
	
		if(error > 0)
		{
			event.preventDefault();
		}	
    
    });

</script>