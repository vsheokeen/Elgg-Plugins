<?php
/**
 * Groups profile page Userpoints widget for Widget Manager plugin
 *
 */

$count = sanitise_int($vars["entity"]->grouppoints_widget_count, false);
if(empty($count)){
        $count = 5;
}

?>
<div>
        <?php echo elgg_echo("grouppoints_widget:widget:num_members"); ?><br />
        <?php echo elgg_view("input/text", array("name" => "params[grouppoints_widget_count]", "value" => $count, "size" => "4", "maxlength" => "4")); ?>
</div>
