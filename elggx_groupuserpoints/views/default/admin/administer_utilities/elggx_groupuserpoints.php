<?php

$tab = get_input('tab') ? get_input('tab') : 'detail';

$params = array(
        'tabs' => array(
                //array('title' => elgg_echo('elggx_groupuserpoints:list'), 'url' => "$url" . '?tab=list', 'selected' => ($tab == 'list')),
                array('title' => elgg_echo('elggx_groupuserpoints:detail'), 'url' => "$url" . '?tab=detail', 'selected' => ($tab == 'detail')),
                array('title' => elgg_echo('elggx_groupuserpoints:moderate'), 'url' => "$url" . '?tab=moderate', 'selected' => ($tab == 'moderate')),
               // array('title' => elgg_echo('elggx_groupuserpoints:add'), 'url' => "$url" . '?tab=add', 'selected' => ($tab == 'add')),
                array('title' => elgg_echo('elggx_groupuserpoints:settings'), 'url' => "$url" . '?tab=settings', 'selected' => ($tab == 'settings')),
                //array('title' => elgg_echo('elggx_groupuserpoints:actions'), 'url' => "$url" . '?tab=actions', 'selected' => ($tab == 'actions')),
				array('title' => elgg_echo('elggx_groupuserpoints:scaling'), 'url' => "$url" . '?tab=scaling', 'selected' => ($tab == 'scaling')),
        )
);

echo elgg_view('navigation/tabs', $params);

        switch($tab) {
                case 'list':
                        echo elgg_view("elggx_groupuserpoints/list");
                        break;
                case 'detail':
                        echo elgg_view("elggx_groupuserpoints/detail");
                        break;
                case 'moderate':
                        echo elgg_view("elggx_groupuserpoints/moderate");
                        break;
                case 'add':
                        echo elgg_view("elggx_groupuserpoints/add");
                        break;
                case 'settings':
                        echo elgg_view("elggx_groupuserpoints/settings");
                        break;
                case 'actions':
                        echo elgg_view("elggx_groupuserpoints/actions");
                        break;
				case 'scaling':
                        echo elgg_view("elggx_groupuserpoints/scaling");
                        break;		
        }
