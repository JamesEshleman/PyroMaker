<?php
/* --------------------------------------------------------------------------------
Name:         value.php
Purpose:      Set default values; get form values; get values from a previous run
Author:       Alan O'Neill
Release Date: January 2, 2013
-------------------------------------------------------------------------------- */

include_once('library.php');
include_once('constant.php');
include_once('option.php');

function value_loadDefaults(&$data) {
	// Set defaults for all prompts (internal format)
	// $data - the array to hold the values

	option_plotColor($options);
	// Number the colors to use them below
	$num = 0;
	foreach($options as $key => $value) {
		$num ++;
		$color[$num] = $key;
	}

	$data['traceType'] = 'PYRO';
	$data['limitOfDetection'] = '';
	$data['tumorPercent'] = '';
	$data['separateTraces'] = 'FALSE';
	$data['annotation'] = 'FALSE';
	$data['dispensation'] = '';
	for ($seqno = 1; $seqno <= MAXSANGERSEQUENCES; $seqno ++) {
		$data['sequences'][$seqno]['sequenceName'] = '';
		$data['sequences'][$seqno]['sequence'] = '';
		$data['sequences'][$seqno]['mutantPercent'] = '';
		$data['sequences'][$seqno]['chromosomeStatus'] = 'HETEROZYGOUS';
		if ($seqno <= MAXCOLORS) {
			$data['sequences'][$seqno]['plotColor'] = $color[$seqno];
		} else {
			$data['sequences'][$seqno]['plotColor'] = '';
		}
	}
	$data['sequences'][1]['sequenceName'] = 'wild';
	$data['sequences'][2]['sequenceName'] = 'mutant';
	$data['sequences'][2]['mutantPercent'] = 100;
}

function value_loadRequest(&$request, &$data) {
	// Load values from $request into $data
	// request - the array holding the values either from the submitted form or from a log file
	// data - the array to hold the values

	$data['traceType'] = $request['traceType'];
	$data['limitOfDetection'] = $request['limitOfDetection'];
	if (strlen($data['limitOfDetection']) > 0) {
		$data['limitOfDetection'] = intval($data['limitOfDetection']);
	}
	$data['tumorPercent'] = $request['tumorPercent'];
	if (strlen($data['tumorPercent']) > 0) {
		$data['tumorPercent'] = intval($data['tumorPercent']);
	}
	$data['separateTraces'] = $request['separateTraces'];
	$data['annotation'] = $request['annotation'];
	$data['dispensation'] = strtoupper($request['dispensation']);
	lib_getMaxValues($data['traceType'], $maxseq, $maxseqlen);
	for ($seqno = 1; $seqno <= $maxseq; $seqno ++) {
		$data['sequences'][$seqno]['sequenceName'] = $request['sequenceName_' . $seqno];
		$data['sequences'][$seqno]['sequence'] = strtoupper($request['sequence_' . $seqno]);
		// The first one is 'wild', which has no mutant percent or chromosome status
		if ($seqno > 1) {
			$data['sequences'][$seqno]['mutantPercent'] = $request['mutantPercent_' . $seqno];
			if (strlen($data['sequences'][$seqno]['mutantPercent']) > 0) {
				$data['sequences'][$seqno]['mutantPercent'] = intval($data['sequences'][$seqno]['mutantPercent']);
			}
			$data['sequences'][$seqno]['chromosomeStatus'] = $request['chromosomeStatus_' . $seqno];
		}
		if ($seqno <= MAXCOLORS) {
			$data['sequences'][$seqno]['plotColor'] = $request['plotColor_' . $seqno];
		}
	}
}

function value_loadForm(&$data) {
	// Grab the values from the form
	// Obtain the values from the submitted form
	// $data - the array to hold the values

	value_loadRequest($_REQUEST, $data);
}

function value_loadRun($runkey, &$data) {
	// Load values from a previous request
	// runkey = the name of the file in the ./log directory
	// data - the array to hold the values
	// Return the run key that was loaded or '' if the data could not be loaded

	$file = LOGDIR . '/' . $runkey;
	if (file_exists($file)) {
		include($file);
		if (array_key_exists('runkey', $REQUEST)) {
			// Previous run -- redirect to original
			$runkey = value_loadRun($REQUEST['runkey'], $data);
		} else {
			value_loadRequest($REQUEST, $data);
		}
	} else {
		$runkey = '';
	}

	return $runkey;
}
?>
