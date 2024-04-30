<?php
/* --------------------------------------------------------------------------------
Name:         maint.php
Purpose:      Display a message when the site is in maintenance mode
              Be sure to update the message below appropriately before turning on
              maintenance mode in constant.php.
Author:       Alan O'Neill
Release Date: July 1, 2011
-------------------------------------------------------------------------------- */

echo '<p>Unfortunately, Pyromaker is down for maintenance, which we expect to be complete by 1:00 pm.</p>';
if (array_key_exists('submit', $_POST)) {
	echo '<p>I see that you just submitted values.  You can regain these values by using the back button on your browser.  After the maintenace on Pyromaker is complete, you may submit them again.</p>';
}
echo '<p>Thank you for your patience.</p>';
?>
