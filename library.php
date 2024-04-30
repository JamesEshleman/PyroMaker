<?php
/* --------------------------------------------------------------------------------
Name:         library.php
Purpose:      Functions used by different parts of Pyromaker
Author:       Alan O'Neill
Release Date: June 24, 2011
-------------------------------------------------------------------------------- */

include_once('constant.php');

function microtime_float() {
	// Return the current Unix timestamp with microseconds

	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function lib_email($from, &$to, $subject, $body, $type) {
	// Send an e-mail
	// from - FROM e-mail address
	// to - single-dimension array of TO e-mail addresses
	// subject - the subject of the e-mail
	// body - an array of lines for the body of the e-mail
	// type - T for plain text or H for HTML

	$headers = "MIME-Version: 1.0";
	$headers .= "\nContent-type: text/html; charset=iso-8859-1";
	$headers .= "\nDate: " . date('D, j M Y H:i:s O');
	$headers .= "\nFrom: " . $from;
	$mailto = "";
	foreach ($to as $key => $value) {
		// Apparently, PHP places the 'To:' portion into the headers, so it's not needed here
		if (strlen($mailto) > 0) {
			$mailto .= "," . $value;
		} else {
			$mailto = $value;
		}
	}
	$headers .= "\nSubject: " . $subject;
	$headers .= "\nX-Mailer: PHP/" . phpversion();

	$prehtml = '<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">';
	$prehtml .= "\n<html>";
	$prehtml .= "\n<head>";
	$prehtml .= "\n<title>" . $subject . "</title>";
	$prehtml .= "\n</head>";
	$prehtml .= "\n<body>";

	$posthtml = "\n</body>";
	$posthtml .= "\n</html>";

	if ($type == 'T') {
		$prehtml .= "\n<pre>";
		$posthtml = "\n</pre>" . $posthtml;
	}

	$message = $prehtml . $body . $posthtml;

	mail($mailto, $subject, $message, $headers);
}

function lib_filebase() {
	// Return the base portion of all files created by Pyromaker in the format
	// YYYYMMDD-IPADDR-
	// where YYYYMMDD is today's date and IPADDR is the remote IP address or
	// 127.0.0.1 if the request was made locally

	if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
		$ip = $_SERVER['REMOTE_ADDR'];
	} else {
		$ip = '127.0.0.1';
	}
	$file = date('Ymd') . '-' . $ip . '-';

	return $file;
}

function lib_expandOptions(&$options, $selected) {
	// Expand the options[] array
	// options[value] = option
	// selected - the 'value' (i.e., internal format) that should be selected by default

	foreach ($options as $value => $option) {
		echo '<option value="', $value, '"';
		if ($value === $selected) {
			echo ' selected="true"';
		}
		echo '>', $option, '</option>';
	}
}

function lib_showOptionValue(&$options, $value) {
	// If the external value is present, display it, otherwise show the key
	// $options[$value] = external value

	if (array_key_exists($value, $options)) {
		echo $options[$value];
	} else {
		echo $value;
	}
}

function lib_intOkay($num, $min, $max) {
	// Validate that the number is an integer in the specified range
	// num - the number to validate
	// min - the minimum value acceptable
	// max - the maximum value acceptable

	if (strlen($num) > 0) {
		if (is_int($num) and $num >= $min and $num <= $max) {
			$okay = true;
		} else {
			$okay = false;
		}
	} else {
		$okay = true;
	}
	return $okay;
}

function lib_sequenceOkay($sequence) {
	// Validate that the sequence is composed of "G", "A", "T", and "C" only
	// sequence - the sequence to validate

	if (preg_match('/^[ACGT]*$/', $sequence) > 0) {
		$okay = true;
	} else {
		$okay = false;
	}
	return $okay;
}

function lib_getMaxValues($traceType, &$maxseq, &$maxseqlen) {
	// Set the maximum number of sequences and the maximum sequence length based on the trace type
	// $traceType - PYRO or SANGER (from the form)
	// $maxseq - the maximum number of sequences to process
	// $maxseqlen - the maximum length for each sequence

	if ($traceType == 'PYRO') {
		$maxseq = MAXPYROSEQUENCES;
		$maxseqlen = MAXPYROLENGTH;
	} else {
		$maxseq = MAXSANGERSEQUENCES;
		$maxseqlen = MAXSANGERLENGTH;
	}
}
?>
