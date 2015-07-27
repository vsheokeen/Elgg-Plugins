README.TXT

LTI v1.1 plugin
----------------
Installation Steps :

Elgg-Configuration

*** Just expand in the mod directory (creating a directory blti) and then go to the admin panel and activate.
*** The plugin allows the administrator to control provisioning of users and groups. Both are set to on to allow the plugin to create users and, under certain conditions, groups. If you plan to provision in some alternative way then switch off using the settings for the tool in the admin panel.
*** The details of the key and secret need to be agreed with the consumer in some out-of-band way (email, phone) and then entered into the appropriate place in Elgg (see the admin panel for the additional option LTI consumers)

Moodle-Configuration

*** Choose external tools from add an activity option in course.
*** Create external tool type with tool base url http://<your Elgg address>/blti/
*** also use the same i.e http://<your Elgg address>/blti/ for launch url
*** To set up Elgg as a Basic LTI consumer two items of information are required: a key and secret. (its require to use same key n secret we used for elgg )
*** allow launch container to open in new window N done.


