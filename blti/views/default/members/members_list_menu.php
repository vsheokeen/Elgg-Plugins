<?php

/**
 * A simple view to provide the user with group filters and the number of group on the site
 **/

// Get plugin settings
$values = GetPluginSettings();
$userprovision = $values['userprovision'];

$page_owner = elgg_get_page_owner_entity();

$members = $vars['count'];

if(!$num_groups)
    $num_groups = 0;

$filter = $vars['filter'];

//url
$url = $vars['url'] . elgg_get_config('ltiname') . '/sync/' . $page_owner->getGUID() . '/';

?>
<div id="elgg_horizontal_tabbed_nav">
<ul>
    <?php $selected = false; ?>
    <?php if (sizeof(unserialize($_SESSION['added_members'])) > 0) {?>
    <li <?php if($filter == 'added' || $filter == 'sync') echo "class='selected'"; $selected = true; ?>><a href="<?php echo $url; ?>added"><?php echo elgg_echo('LTI:members:label:added'); ?></a></li>
    <?php } ?>

    <?php if (sizeof(unserialize($_SESSION['user_provision'])) > 0 && !$userprovision) {?>
    <li <?php if($filter == 'pr_off' || !$selected) echo "class='selected'"; $selected = true; ?>><a href="<?php echo $url; ?>pr_off"><?php echo elgg_echo('LTI:members:label:pr_off'); ?></a></li>
    <?php } ?>

    <?php if (sizeof(unserialize($_SESSION['user_provision'])) > 0 && $userprovision) {?>
    <li <?php if($filter == 'pr_on' || !$selected) echo "class='selected'"; $selected = true; ?>><a href="<?php echo $url; ?>pr_on"><?php echo elgg_echo('LTI:members:label:pr_on'); ?></a></li>
    <?php } ?>

    <?php if (sizeof(unserialize($_SESSION['deleted_members'])) > 0) {?>
    <li <?php if($filter == 'deleted' || !$selected) echo "class='selected'"; $selected = true; ?>><a href="<?php echo $url; ?>deleted"><?php echo elgg_echo('LTI:members:label:deleted'); ?></a></li>
    <?php } ?>

    <?php if (sizeof(unserialize($_SESSION['changed_members'])) > 0) {?>
    <li <?php if($filter == 'changed' || !$selected) echo "class='selected'"; $selected = true; ?>><a href="<?php echo $url; ?>changed"><?php echo elgg_echo('LTI:members:label:changed'); ?></a></li>
    <?php } ?>

    <?php if (sizeof(unserialize($_SESSION['role_changed_del'])) > 0) {?>
    <li <?php if($filter == 'role_del' || !$selected) echo "class='selected'"; $selected = true; ?>><a href="<?php echo $url; ?>role_del"><?php echo elgg_echo('LTI:members:label:role_del'); ?></a></li>
    <?php } ?>

    <?php if (sizeof(unserialize($_SESSION['role_changed_add'])) > 0) {?>
    <li <?php if($filter == 'role_add' || !$selected) echo "class='selected'"; $selected = true; ?>><a href="<?php echo $url; ?>role_add"><?php echo elgg_echo('LTI:members:label:role_add'); ?></a></li>
    <?php } ?>

</ul>
</div>

<div class="group_count">
    <?php
        echo $members . ' ' . elgg_echo('LTI:members:active');
        if ($filter == 'pr_off') echo ' (' . sprintf(elgg_echo('LTI:member:wouldbe'), sizeof(unserialize($_SESSION['user_provision']))) . ')';
        if ($filter == 'pr_on') echo ' (' . sprintf(elgg_echo('LTI:member:added'), sizeof(unserialize($_SESSION['user_provision']))) . ')';
        if (!$selected) echo ' - Group up-to-date';
    ?>
</div>