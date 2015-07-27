<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * Plugin settings
 ------------------------------------------------------------------*/

$userprovision = $vars['entity']->userprovision;
if (!$userprovision) $userprovision = 'no';

$groupprovision = $vars['entity']->groupprovision;
if (!$groupprovision) $groupprovision = 'no';

$allowinstructor = $vars['entity']->allowinstructor;
if (!$allowinstructor) $allowinstructor = 'no';

$testmode = $vars['entity']->testmode;
if (!$testmode) $testmode = 'yes';
?>

<p>
<div class="example">
<?php echo elgg_echo('LTI:userprovisioning'); ?>
</div>

<?php echo elgg_view('input/select', array('name' => 'params[userprovision]',
                                             'options_values' => array('no'  => elgg_echo('LTI:provisionoff'),
                                                                       'yes' => elgg_echo('LTI:provisionon'),
                                                                      ),
                                             'value' => $userprovision
                                            )
                    );
?>

<?php if (elgg_is_active_plugin('groups')) {
echo '<div class="example">';
echo elgg_echo('LTI:groupprovisioning');
echo '</div>';

echo elgg_view('input/select', array('name' => 'params[groupprovision]',
                                             'options_values' => array('no'  => elgg_echo('LTI:provisionoff'),
                                                                       'yes' => elgg_echo('LTI:provisionon'),
                                                                      ),
                                             'value' => $groupprovision
                                            )
                    );

} else {
echo '<div class="example">';
echo elgg_echo('LTI:groupprovisioningoff');
echo '</div>';

echo elgg_view('input/select', array('name' => 'params[groupprovision]',
                                             'options_values' => array('no'  => elgg_echo('LTI:provisionoff')
                                                                      ),
                                             'value' => $groupprovision
                                            )
                    );

} ?>

<div class="example">
<?php echo elgg_echo('LTI:userdetails'); ?>
</div>

<?php echo elgg_view('input/select', array('name' => 'params[allowinstructor]',
                                             'options_values' => array('no'  => elgg_echo('LTI:provisionoff'),
                                                                       'yes' => elgg_echo('LTI:provisionon'),
                                                                      ),
                                             'value' => $allowinstructor
                                            )
                    );

?>

<div class="example">
<?php echo elgg_echo('LTI:test'); ?>
</div>

<?php echo elgg_view('input/select', array('name' => 'params[testmode]',
                                             'options_values' => array('no'  => elgg_echo('LTI:provisionoff'),
                                                                       'yes' => elgg_echo('LTI:provisionon'),
                                                                      ),
                                             'value' => $testmode
                                            )
                    );
?>
</p>