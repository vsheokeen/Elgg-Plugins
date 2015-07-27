<div>
<div id="LTI_newconregistration">
<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * Register Basic consumer form (admin)
 ------------------------------------------------------------------*/

echo elgg_view_title(elgg_echo('LTI:register:consumer:title'));

$consumer_name = elgg_view('input/text', array('name' => 'consumer_name'));

$key = elgg_view('input/text', array('name' => 'tool_guid'));

$secret = elgg_view('input/text', array('name' => 'secret'));

// Enabled
$enable = elgg_view('input/checkboxes', array('name' => 'enable',
                                              'options' => array('' => 'yes'),
                                              'value' => 'no'
                                             )
                   );

$submit = elgg_view('input/submit', array('value' => elgg_echo('LTI:register:submit')));

$formbody .= '<b>' . elgg_echo('LTI:register:name:label')    . '</b>' . $consumer_name . '<br />';
$formbody .= '<b>' . elgg_echo('LTI:register:key:label')     . '</b>' . $key           . '<br />';
$formbody .= '<b>' . elgg_echo('LTI:register:secret:label')  . '</b>' . $secret        . '<br />';
$formbody .= '<b>' . elgg_echo('LTI:register:enable:label')  . '</b>' . $enable        . '<br />';

$formbody .= $submit;

$form = elgg_view('input/form', array('action' => elgg_get_config('wwwroot') . 'action/' . elgg_get_config('ltiname') . '/createconsumer',
                                      'body' => $formbody)
                 );

echo elgg_view('page/elements/body', array('body' => $form));

?>

</div>
<input id="LTI_shownewconregistration" type="submit" value="<?php echo elgg_echo('LTI:register:show') ?>" />
<script type="text/javascript">
    $(document).ready(function() {
      $("#LTI_newconregistration").hide();
    });

    $("#LTI_shownewconregistration").click(function(event) {
      event.preventDefault();
      $("#LTI_shownewconregistration").slideUp("slow");
      $("#LTI_newconregistration").slideDown("slow");
    });

</script>
</div>