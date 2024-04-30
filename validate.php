<?php
/* --------------------------------------------------------------------------------
Name:         validate.php
Purpose:      A set of functions to validate each value submitted from the main
              Pyromaker form.  These validation functions correspond to the
              functions in field.php.
Author:       Alan O'Neill
Release Date: December 28, 2012
-------------------------------------------------------------------------------- */

include_once('library.php');
include_once('option.php');
include_once('constant.php');

function validate_addError(&$errors, &$msg, $message) {
	// $errors - will be set to the number of errors found
	// $msg[#] - will be set to each error message

	$errors ++;
	$msg[$errors] = $message;
}

function validate_traceType(&$errors, &$msg, $value) {
	// $errors - will be set to the number of errors found
	// $msg[#] - will be set to each error message
	// value - the value to validate

	option_traceType($options);
	if (! array_key_exists($value, $options)) {
		validate_addError($errors, $msg, 'Trace type is invalid.');
	}
}

function validate_limitOfDetection(&$errors, &$msg, $value) {
	// $errors - will be set to the number of errors found
	// $msg[#] - will be set to each error message
	// value - the value to validate

	if (! lib_intOkay($value, 0, 99)) {
		validate_addError($errors, $msg, 'Limit of detection must be an integer from 0 to 99.');
	}
}

function validate_tumorPercent(&$errors, &$msg, $value) {
	// $errors - will be set to the number of errors found
	// $msg[#] - will be set to each error message
	// value - the value to validate

	if (! lib_intOkay($value, 0, 99) or strlen($value) == 0) {
		validate_addError($errors, $msg, 'Tumor percent must be an integer from 0 to 99.');
	}
}

function validate_separateTraces(&$errors, &$msg, $value) {
	// $errors - will be set to the number of errors found
	// $msg[#] - will be set to each error message
	// value - the value to validate

	option_separateTraces($options);
	if (! array_key_exists($value, $options)) {
		validate_addError($errors, $msg, 'Separate traces is invalid.');
	}
}

function validate_dispensation(&$errors, &$msg, $value) {
	// $errors - will be set to the number of errors found
	// $msg[#] - will be set to each error message
	// value - the value to validate

	if (! lib_sequenceOkay($value)) {
		validate_addError($errors, $msg, 'Dispensation may contain only the letters G, A, T, and C.');
	} elseif (stripos($value, 'G') === false or stripos($value, 'A') === false or stripos($value, 'T') === false or stripos($value, 'C') === false) {
		validate_addError($errors, $msg, 'Dispensation must contain at least one each of the letters G, A, T, and C.');
	}
}

function validate_sequenceName(&$errors, &$msg, $seqno, $value) {
	// $errors - will be set to the number of errors found
	// $msg[#] - will be set to each error message
	// seqno - the sequence number
	// value - the value to validate

	$pattern = '/[0-9A-Za-z]{1,' . MAXSEQNAMELEN . '}/';
	if (preg_match($pattern, $value) == 0) {
		validate_addError($errors, $msg, 'Sequence name ' . $seqno . ' must be 1 to ' . MAXSEQNAMELEN . ' alphanumeric characters.');
	}
}

function validate_sequence(&$errors, &$msg, $seqno, $value) {
	// $errors - will be set to the number of errors found
	// $msg[#] - will be set to each error message
	// seqno - the sequence number
	// value - the value to validate

	if (! lib_sequenceOkay($value)) {
		validate_addError($errors, $msg, 'Sequence ' . $seqno . ' contains letters other than G, A, T, and C.');
	}
}

function validate_mutantPercent(&$errors, &$msg, $seqno, $value) {
	// $errors - will be set to the number of errors found
	// $msg[#] - will be set to each error message
	// seqno - the sequence number
	// value - the value to validate

	if ($seqno > 1) {
		if (! lib_intOkay($value, 1, 100) or strlen($value) == 0) {
			validate_addError($errors, $msg, 'Mutant percent for sequence ' . $seqno . ' must be an integer from 1 to 100.');
		}
	}
}

function validate_chromosomeStatus(&$errors, &$msg, $seqno, $value) {
	// $errors - will be set to the number of errors found
	// $msg[#] - will be set to each error message
	// seqno - the sequence number
	// value - the value to validate

	if ($seqno > 1) {
		option_chromosomeStatus($options);
		if (! array_key_exists($value, $options)) {
			validate_addError($errors, $msg, 'Chromosome status for sequence ' . $seqno . ' is invalid.');
		}
	}
}

function validate_plotColor(&$errors, &$msg, $seqno, $value) {
	// $errors - will be set to the number of errors found
	// $msg[#] - will be set to each error message
	// seqno - the sequence number
	// value - the value to validate

	option_plotColor($options);
	if (! array_key_exists($value, $options)) {
		validate_addError($errors, $msg, 'Plot color for sequence ' . $seqno . ' is invalid.');
	}
}

function validate_sequences(&$errors, &$msg, &$sequences, $traceType) {
	if (strlen($sequences[1]['sequence']) == 0) {
		validate_addError($errors, $msg, 'The wild sequence (#1) is required.');
	}
	lib_getMaxValues($traceType, $maxseq, $maxseqlen);
	for ($seqno = 1; $seqno <= $maxseq; $seqno ++) {
		// Truncate the sequence if it's too long
		$sequences[$seqno]['sequence'] = substr($sequences[$seqno]['sequence'], 0, $maxseqlen);
		if (strlen($sequences[$seqno]['sequence']) > 0) {
			validate_sequenceName($errors, $msg, $seqno, $sequences[$seqno]['sequenceName']);
			validate_sequence($errors, $msg, $seqno, $sequences[$seqno]['sequence']);
			// Sequence #1 is wild, which has no mutant percent or chromosome status
			if ($seqno > 1) {
				validate_mutantPercent($errors, $msg, $seqno, $sequences[$seqno]['mutantPercent']);
				validate_chromosomeStatus($errors, $msg, $seqno, $sequences[$seqno]['chromosomeStatus']);
			}
			validate_plotColor($errors, $msg, $seqno, $sequences[$seqno]['plotColor']);
		}
	}
}

function validate(&$data, &$errors, &$msg) {
	// Validate the values and return errors in the msg[] array
	// data[] - the array of data to validate
	// $errors - will be set to the number of errors found
	// $msg[#] - will be set to each error message
	// Return the number of errors that were encountered

	$errors = 0;

	validate_traceType($errors, $msg, $data['traceType']);
	validate_limitOfDetection($errors, $msg, $data['limitOfDetection']);
	validate_tumorPercent($errors, $msg, $data['tumorPercent']);
	validate_separateTraces($errors, $msg, $data['separateTraces']);
	// When trace type is Sanger, dispensation is not used.
	if ($data['traceType'] == 'PYRO') {
		validate_dispensation($errors, $msg, $data['dispensation']);
	}
	validate_sequences($errors, $msg, $data['sequences'], $data['traceType']);

	return $errors;
}
?>
