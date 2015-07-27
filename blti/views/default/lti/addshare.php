<script type="text/javascript" src="<?php  echo elgg_get_config('wwwltipath') . 'ajax/ajax.js'; ?>"></script>

<div>
<div id="LTI_newshare">
<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * Write out sharing information
 ------------------------------------------------------------------*/

echo elgg_view_title(elgg_echo('LTI:share:key:title'));

$formbody = '<div id="myDiv"></div>';

echo elgg_view('page/elements/body', array('body' => $formbody));

?>
</div>

<input id="LTI_shownewshare" type="submit" value="<?php echo elgg_echo('LTI:share:submit') ?>" />

<script type="text/javascript">
    $(document).ready(function() {
      $("#LTI_newshare").hide();
    });

    $("#LTI_shownewshare").click(function(event) {
      event.preventDefault();
      $("#LTI_formarea").slideUp("slow");
      $("#LTI_shownewshare").slideUp("slow");
      $("#LTI_newshare").slideDown("slow");
<?php
     echo 'doGenerateKey(\'' . elgg_get_config('wwwltipath') . 'lib/generate.php\');';
  ?>
    });

</script>
</div>