<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * This is called when administrator chooses to edit a consumer
 ------------------------------------------------------------------*/

// Have to be logged in to get to here at all
admin_gatekeeper();

elgg_set_context('admin');

// Sort out which LTI provider we are editing
$consumer_instance = new LTI_Tool_Consumer_Instance(get_input('LTIconsumerguid'), elgg_get_config('dbprefix'));
$consumer_tool = new LTI_Tool_Consumer($consumer_instance->consumer_guid, elgg_get_config('dbprefix'));

// Check we have LTI object
if ($consumer_instance) {

    $isBasicLTI = ($consumer_instance->state == 'BasicLTI' || ($consumer_instance->consumer_guid == $consumer_instance->guid)) ? true : false;

    $tool_guid = elgg_view('input/hidden',
                           array('name' => 'tool_guid',
                                 'value' => $consumer_tool->guid
                                )
                          );

    // Tile
    //$area2 = elgg_view_title(elgg_echo('LTI:edit:consumer:title'));

    // Name
    $name    = elgg_view('input/text',
                         array('name' => 'name',
                               'value' => $consumer_tool->name
                              )
                        );
    // Enabled
    $option = ($consumer_instance->isEnabled()) ? 'yes' : 'no';
    $enable = elgg_view('input/checkboxes',
                        array('name' => 'enable',
                              'options' => array('' => 'yes'),
                              'value' => $option
                             )
                       );

    if (!$isBasicLTI) {
        // Profile UL
        $profile_url = '<a href="' . $consumer_tool->profile_url . '">' . $consumer_tool->profile_url . '</a>';
        $url = elgg_view('input/hidden',
                         array('name' => 'url',
                               'value' => $consumer_tool->profile_url
                              )
                        );

        // Version
        $version = elgg_view('input/text',
                              array('name' => 'name',
                                    'value' => $consumer_tool->consumer_version
                                   )
                            );
        // System
        $system = $consumer_tool->consumer_name . '&nbsp;(' . $consumer_tool->consumer_version . ')';
        $consumer_name = elgg_view('input/text',
                                   array('name' => 'consumer_name',
                                         'value' => $consumer_tool->consumer_name
                                        )
                                  );
    }
    /*
    // LTI State
    if ($isBasicLTI) {
        $state   = elgg_view('input/select',
                             array('name' => 'state',
                                   'options_values' => array('BasicLTI'     => 'Basic LTI',
                                                             'removed'      => 'Removed'
                                                            ),
                                   'value' => $consumer_instance->state
                                  )
                            );
    } else {
        $state   = elgg_view('input/select',
                             array('name' => 'state',
                                   'options_values' => array('registered'   => 'Registered',
                                                             'available'    => 'Available',
                                                             'reregistered' => 'Reregistered',
                                                             'inactive'     => 'Inactive',
                                                             'removed'      => 'Removed',
                                                             'BasicLTI'     => 'Basic LTI'
                                                            ),
                                   'value' => $consumer_instance->state
                                  )
                            );
    }
    */
    // Secret
    $secret  = elgg_view('input/text',
                         array('name' => 'secret',
                               'value' => $consumer_instance->secret
                              )
                        );

    // Services
    $services = '';
    foreach ($consumer_tool->services as $key => $value) {

        $services .= substr($key, 0, -3) . "\n";

    }
    $services_view = elgg_view('input/longtext',
                               array('name' => 'services',
                                     'value' => $services
                                    )
                              );

    // Pass the LTI GUID via a hidden field
    $guid = elgg_view('input/hidden',
                       array('name' => 'guid',
                             'value' => $consumer_instance->guid
                            )
                     );

    // Submit button
    $submit = elgg_view('input/submit',
                        array('value' => elgg_echo('LTI:edit:submit'))
                       );

    // Write out the form
    $formbody  = '<p><label>' . elgg_echo('LTI:register:consumer:label') . '</label><p>';
    $formbody .= '<table border = "0">';

    if ($isBasicLTI) {
        $formbody .= '<tr><td>'    . elgg_echo('LTI:register:key:label')     .     '</td><td>&nbsp;</td><td>' . elgg_echo($consumer_tool->guid)    . '</td></tr>';
    } else {
        $formbody .= '<tr><td>'    . elgg_echo('LTI:register:name:GUID')     .     '</td><td>&nbsp;</td><td>' . elgg_echo($consumer_tool->guid)    . '</td></tr>';
    }

    $formbody .= '<tr><td>'    . elgg_echo('LTI:register:conname:label') .     '</td><td>&nbsp;</td><td>' . $consumer_tool->consumer_name                         . '</td></tr>';
    $formbody .= '<tr><td><b>' . elgg_echo('LTI:register:name:label')    . '</b></td><td>&nbsp;</td><td>' . $name                                  . '</td></tr>';

    if (!$isBasicLTI) {
        $formbody .= '<tr><td>'    . elgg_echo('LTI:register:URL:label')     .     '</td><td>&nbsp;</td><td>' . $profile_url                           . '</td></tr>';
        $formbody .= '<tr><td>'    . elgg_echo('LTI:register:system:label')  .     '</td><td>&nbsp;</td><td>' . $system                                . '</td></tr>';
    }

    //$formbody .= '<tr><td>'    . elgg_echo('LTI:register:created:label') .     '</td><td>&nbsp;</td><td>' . date('d-M-Y', $consumer_tool->created) . '</td></tr>';
    //$formbody .= '<tr><td>'    . elgg_echo('LTI:register:updated:label') .     '</td><td>&nbsp;</td><td>' . date('d-M-Y', $consumer_tool->updated) . '</td></tr>';

    if (!$isBasicLTI) {
        // Insert space before Services
        $formbody .= '<tr><td></td><td>&nbsp;</td><td>';

        $formbody .= '<div>';
        $formbody .= '<div id="LTI_shownewconregistration">';
        $formbody .= elgg_echo('LTI:register:services:label');
        $formbody .= '</div>';

        $formbody .= '<div id="LTI_newconregistration">';
        $formbody .= sprintf(elgg_echo('LTI:register:services:desc'), $consumer_tool->consumer_name) . $services_view . '</p>';
        $formbody .= '</div>';

        $formbody .= '<script type="text/javascript">';
        $formbody .= '    $(document).ready(function() {';
        $formbody .= '      $("#LTI_newconregistration").hide();';
        $formbody .= '    });';

        $formbody .= '    $("#LTI_shownewconregistration").click(function(event) {';
        $formbody .= '      event.preventDefault();';
        $formbody .= '      $("#LTI_shownewconregistration").slideUp("slow");';
        $formbody .= '      $("#LTI_newconregistration").slideDown("slow");';
        $formbody .= '    });';

        $formbody .= '    $("#LTI_newconregistration").click(function(event) {';
        $formbody .= '      event.preventDefault();';
        $formbody .= '      $("#LTI_shownewconregistration").slideDown("slow");';
        $formbody .= '      $("#LTI_newconregistration").slideUp("slow");';
        $formbody .= '    });';

        $formbody .= '</script>';
        $formbody .= '</div></td></tr>   ';
    }
    //$formbody .= '</table>';
    //$formbody .= '<p><label>' . elgg_echo('LTI:register:consumerinstance:label') . '</label><p>';
    //$formbody .= '<table border = "0">';
    //$formbody .= '<tr><td><b>' . elgg_echo('LTI:register:state:label')   . '</b></td><td>&nbsp;</td><td>' . $state                                     . '</td></tr>';
    $formbody .= '<tr><td><b>' . elgg_echo('LTI:register:secret:label')  . '</b></td><td>&nbsp;</td><td>' . $secret                                    . '</td></tr>';
    $formbody .= '<tr><td><b>' . elgg_echo('LTI:register:enable:label')  . '</b></td><td>&nbsp;</td><td>' . $enable                                . '</td></tr>';

    //$formbody .= '<tr><td>'    . elgg_echo('LTI:register:created:label') .     '</td><td>&nbsp;</td><td>' . date('d-M-Y', $consumer_instance->created) . '</td></tr>';
    //$formbody .= '<tr><td>'    . elgg_echo('LTI:register:updated:label') .     '</td><td>&nbsp;</td><td>' . date('d-M-Y', $consumer_instance->updated) . '</td></tr>';
    $formbody .= '</table>';

    $formbody .= $tool_guid;
    $formbody .= $consumer_name;
    $formbody .= $url;
    $formbody .= $guid;
    $formbody .= $submit;

    // Now push out the form via the Elgg views
    $form = elgg_view('input/form',
                      array('action' => elgg_get_config('wwwroot') . 'action/' . elgg_get_config('ltiname') . '/saveconsumer',
                            'body' => $formbody
                           )
                     );

    echo elgg_view('page/elements/body', array('body' => $form));

    // Format
    //$body = elgg_view_layout("two_column_left_sidebar", array('title' => '', 'content' => $area2));

    // Draw page
	//echo elgg_view_page(elgg_echo('LTI:registered'), $body);
	
	/* echo elgg_view('page/elements/wrapper',
                        array('body' => $area2)
                       ); */

}

//forward($_SERVER['HTTP_REFERER']);

?>