<?php
/* --------------------------------------------------------------------------------
Name:         index.php
Purpose:      The main Pyromaker page
              This page is used for four purposes:
              1) If $_REQUEST['runkey'] is defined, then Pyromaker will attempt to
                 find the previous run in the log directory (see constant.php).
                 If the key is found, those values are used to generate the graph.
              2) Otherwise, if $_POST['submit'] is not defined, then a blank form
                 is presented to the user to allow data entry.
              3) Otherwise, if one or more $_POST values fails validation, the
                 erorrs are listed, and the user is allowed to correct them and
                 resubmit the form.
              4) Otherwise, the R script is called to generate the graph, and the
                 data entered along with the resulting graph are presented to the
                 user.
Author:       Alan O'Neill
Release Date: July 1, 2011
-------------------------------------------------------------------------------- */

function main_say(&$data, $oldkey) {
	// Display the form and the graph, and log the session if logging is turned on
	// If the graph could not be generated, log the sesion (even if logging is turned off) and send an e-mail
	// data - the array holding the request values
	// oldkey - sent if a previous run key was used

	if (! field_say($data)) {
		// Even if logging is turned off, an error occurred, so log the request and then send an e-mail
		$runkey = log_save($oldkey);
		$from = "Alan O'Neill <aoneill5@jhmi.edu>";
		$to[1] = "Alan O'Neill <aoneill5@jhmi.edu>";
		$to[2] = 'Matthew Olson <olson.matthew3@mayo.edu>';
		$body = '<p>Pyromaker was unable to generate an image based on the values supplied.  Please check <strong>' . LOGDIR . '/' . $runkey . '</strong> on <strong>' . exec("hostname -f") . '</strong> for details.</p>';
		lib_email($from, $to, 'Pyromaker Image Generation Failed', $body, 'H');
	} elseif (LOGGING) {
		// All went well; if logging is turned on, log the request
		$runkey = log_save($oldkey);
	}
}

include_once('constant.php');
include('head.html');
if (MAINT) {
	include('maint.php');
} else {
	include_once('field.php');
	include_once('library.php');
	include_once('logger.php');
	include_once('r.php');
	include_once('validate.php');
	include_once('value.php');

	if (array_key_exists('runkey', $_REQUEST)) {
		$runkey = value_loadRun($_REQUEST['runkey'], $data);
		if (strlen($runkey) > 0) {
			main_say($data, $runkey);
		} else {
			echo '<p>Error: The specified run key does not exist.</p>';
		}
	} elseif (! array_key_exists('submit', $_POST)) {
		value_loadDefaults($data);
		field_get($data);
	} else {
		value_loadForm($data);
		if (validate($data, $errors, $msg) > 0) {
			echo '<p>The following errors occurred while processing your request.</p>';
			echo '<ul>';
			for ($errno = 1; $errno <= $errors; $errno ++) {
				echo '<li>', $msg[$errno], '</li>';
			}
			echo '</ul>';
			echo '<p>Please correct them and resubmit your request.</p>';
			echo '<hr>';
			field_get($data);
		} else {
			main_say($data, "");
			echo '<p>You may use your back button to edit the values and try again, ';
			echo 'or you may <a href="', $_SERVER['PHP_SELF'], '">generate another graph.</a></p>';
		}
	}
}
include('foot.html');
?>
