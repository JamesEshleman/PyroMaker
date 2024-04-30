<?php
/* --------------------------------------------------------------------------------
Name:         logger.php
Purpose:      If logging is turned on (see constant.php), this function logs all
              the values from $_SERVER and $_REQUEST into a log file.  The location
              of the log file is set in constant.php.  These log files can be used
              to regenerate a specific graph (see index.php).
Author:       Alan O'Neill
Release Date: June 24, 2011
-------------------------------------------------------------------------------- */

include_once('constant.php');
include_once('library.php');

function log_add($key, $value) {
	// Add an entry to the log that will be saved when log_save is called
	// key - the key for the entry in the log (must be unique)
	// value - the value of the key in the log

	$GLOBALS['log'][$key] = $value;
}

function log_save($oldlogkey) {
	// Create a log file containing PHP code that, when executed, sets the values originally
	// submitted in $_SERVER into $SERVER and those from $_REQUEST into $REQUEST.
	// oldlogkey - sent if a previous run key was used
	// Return the name of the log file that was created.

	$repeat = (strlen($oldlogkey) > 0);

	$file = lib_filebase();
	if ($repeat) {
		$file .= 'r-';
	}
	$file = tempnam(LOGDIR, $file);

	$fp = fopen($file, 'w');
	chmod($file, 0644);
	if ($fp) {
		fwrite($fp, "<?php\n\n");

		if (array_key_exists('log', $GLOBALS)) {
			foreach ($GLOBALS['log'] as $key => $value) {
				fwrite ($fp, '$LOG[\'' . $key . '\'] = \'' . $value . '\';' . "\n");
			}
		}

		fwrite($fp, '$SERVER = ');
		fwrite($fp, var_export($_SERVER, true));
		fwrite($fp, ";\n\n");

		if ($repeat) {
			fwrite($fp, '// Repeat of run ' . $oldlogkey . "\n");
			fwrite($fp, '$REQUEST["runkey"] = ');
			fwrite($fp, var_export($oldlogkey, true));
			$newlogkey = $oldlogkey;
		} else {
			fwrite($fp, '$REQUEST = ');
			fwrite($fp, var_export($_REQUEST, true));
			$newlogkey = basename($file);
		}
		fwrite($fp, ";\n\n");

		if (array_key_exists('log', $GLOBALS)) {
			foreach ($GLOBALS['log'] as $key => $value) {
				fwrite($fp, '$LOG[\'' . $key . '\'] = \'' . $value . '\';' . "\n");
			}
		}
		fwrite($fp, "\n");

		fwrite($fp, '?>');

		fclose($fp);
	}

	return $newlogkey;
}
?>
