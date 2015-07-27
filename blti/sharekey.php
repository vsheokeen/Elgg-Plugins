<?php

/*-------------------------------------------------------------------
 * Elgg LTI
 *
 * Create a share key --- includes all the workflow text
 ------------------------------------------------------------------*/

elgg_set_context('groups');

// Must be logged in to use this page
gatekeeper();

// Get group details
$group_guid = (int) get_input('group_guid');
$group = get_entity($group_guid);
elgg_set_page_owner_guid($group_guid);

$area2 = elgg_view_title(elgg_echo('LTI:share:key'));
$area2 .= elgg_view('page/elements/body', array('body' => elgg_echo('LTI:share:explain')));
$area2 .= elgg_view('page/elements/body', array('body' => elgg_echo('LTI:share:workflow')));

// Is this context shared?

$consumer_instance = new LTI_Tool_Consumer_Instance(get_input('LTIconsumerguid'), elgg_get_config('dbprefix'));
$context = new LTI_Context($consumer_instance, $group->context_id);
$shares = $context->getShares();
if (!empty($shares)) {
    $area2 .= elgg_view('page/elements/body', array('body' => elgg_echo('LTI:share:shared')));
} else {
    $area2 .= elgg_view('page/elements/body', array('body' => elgg_echo('LTI:share:notshared')));
}

// Life dropdown
$formbody = '<div id="LTI_formarea"><table><tr><td>';
$formbody .= elgg_echo('LTI:share:life');
$formbody .= elgg_view('input/select',
                     array('name' => 'life',
                           'options_values' => array( '1'   =>  '1 hr',
                                                      '2'   =>  '2 hr',
                                                     '12'   => '12 hr',
                                                     '24'   => '1 day',
                                                     '48'   => '2 day',
                                                     '72'   => '3 day',
                                                     '96'   => '4 day',
                                                     '120'  => '5 day',
                                                     '168'  => '1 week'
                                                    ),
                           'value' => '1 hr'
                          )
                     );
$formbody .= '</td><td>&nbsp;</td><td>';

// Pre-approve Checkbox
$formbody .= elgg_echo('LTI:share:preapprove');
$formbody .= '</td><td>';
$formbody .=  elgg_view('input/checkboxes',
                        array('name' => 'auto_approve',
                              'options' => array('' => 'yes')
                             )
                        );
// Email
$formbody .= '</td></tr><tr><td>';
$formbody .= elgg_echo('LTI:share:emailaddress');
$formbody .= elgg_view('input/text',
                        array('name' => 'email',
                             'value' => 'test@no-reply.com'
                             )
                      );
$formbody .= '</td></tr></table></div>';

$formbody .= elgg_view('input/hidden',
                      array('name' => 'consumer_guid',
                           'value' => $group->consumer_key
                           )
                      );

$formbody .= elgg_view('input/hidden',
                      array('name' => 'context_id',
                           'value' => $group->context_id
                           )
                      );

$user = elgg_get_logged_in_user_entity();
$formbody .= elgg_view('input/hidden',
                      array('name' => 'fromemail',
                            'value' => $user->email
                           )
                      );
$form = elgg_view('input/form',
                 array('action' => elgg_get_config('wwwroot') . 'action/' . elgg_get_config('ltiname') . '/createshare',
                        'body' => $formbody
                      )
                 );

$form .= elgg_view('lti/addshare');

$area2 .= elgg_view('page/elements/body', array('body' => $form));

$body = elgg_view_layout('two_column_left_sidebar', array('title' => '', 'content' => $area2));

// Finally draw the page
echo elgg_view_page($title, $body);

?>