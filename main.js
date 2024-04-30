/* --------------------------------------------------------------------------------
Name:         main.js
Purpose:      JavaScript required for Pyromaker
Author:       Alan O'Neill
Release Date: June 24, 2011
-------------------------------------------------------------------------------- */

// Constants
var COLS_PYRO = 25; // Number of columns for pyro sequences
var ROWS_PYRO = 2; // Number of rows for pyro sequences
var COLS_SANGER = 50; // Number of columns for sanger sequences
var ROWS_SANGER = 20; // Number of rows for sanger sequences
var ROWS_MIN = 2; // Minimum number of rows for sequences
var LENGTH_ALERT_COLOR = 'yellow'; // Color of cell background when sequence is too long
var LENGTH_OKAY_COLOR = 'white'; // Color of cell background when sequence length is okay

function initialize() {
	// Turn off elements that are not needed if JavaScript is enabled
	$('.nojs').css('display', 'none');
	// Initialize the background color for the sequence text areas
	$('textarea.sequence').css('background-color', LENGTH_OKAY_COLOR);
	// Make sure the form is in line with the data
	$('select#traceType').change();
	$('select#separateTraces').change();
}

function intRange(number, low, high) {
	// Determine if 'number' is an integer between 'low' to 'high,' inclusive
	// Return:
	//   0 = either null or in range
	//   1 = not an integer
	//   2 = out of range

	var err;

	err = 0;
	if (number != '') {
		if (number != parseInt(number)) {
			err = 1;
		} else if (number < low || number > high) {
			err = 2;
		}
	}

	return err;
}

function useColor(yesno) {
	// Color is used for separate traces, in which case only 8 sequences can be entered
	// When separate traces are not used, the color column is not used, and up to 20 sequences can be entered
	if (yesno) {
		$('.userColorCell').css('display', 'table-cell');
		$('.autoColorRow').css('display', 'none');
	} else {
		$('.userColorCell').css('display', 'none');
		$('.autoColorRow').css('display', 'table-row');
	}
}

function enumerate(yesno) {
	if (yesno) {
		$('.enumerationCell').css('display', 'table-cell');
	} else {
		$('.enumerationCell').css('display', 'none');
	}
}

function gatcOnly(sequence) {
	// Convert 'sequence' to upper case and ensure it is composed of the letters G, A, T, and C only
	// Return the updated string

	var c, err, i, result;

	err = false;
	sequence = sequence.toUpperCase();
	result = '';
	for (i = 0; i < sequence.length; i++) {
		c = sequence.substring(i, i + 1);
		if (c == 'G' || c == 'A' || c == 'T' || c == 'C') {
			result += c;
		} else {
			err = true;
		}
	}

	return result
}

function maxSequenceLength() {
	// Return the maximum length allowed for a sequence
	var maxlength;

	if ($('#traceType').val() == 'PYRO') {
		maxlength = $('span#pyroLength').html();
	} else {
		maxlength = $('span#sangerLength').html();
	}

	return maxlength;
}

$(document).ready(
	function() {
		// If the trace type is 'PYRO'
		//   - turn on Pyro-specific elements and turn off Sanger-specific elements
		//   - restore the previous value of the separate traces field
		// If the trace type is 'SANGER'
		//   - Turn on Sanger-specific elements and turn off Pyro-specific elements
		//   - save the value of the separate traces field
		//   - change the separate traces field to 'FALSE'
		// In both cases
		//   - adjust the rows and columns of the sequence text areas
		//   - warn the user if any sequence exceeds the maximum length (due to the change in trace type)
		$('select#traceType').change(
			function($e) {
				var maxlength;
				maxlength = maxSequenceLength();
				if ($(this).val() == 'PYRO') {
					$('.pyroSpecificParagraph').css('display', 'block');
					enumerate(false);
					$('select#separateTraces').val($('select#separateTraces').data('saveVal'));
					$('select#separateTraces').change();
					$('textarea.sequence').attr('cols', COLS_PYRO);
				} else {
					enumerate(true);
					$('.pyroSpecificParagraph').css('display', 'none');
					$('select#separateTraces').data('saveVal', $('select#separateTraces').val());
					$('select#separateTraces').val('FALSE');
					$('select#separateTraces').change();
					$('textarea.sequence').attr('cols', COLS_SANGER);
				}
				$('textarea.sequence').each(
					function(index) {
						if ($(this).val().length > maxlength) {
							$(this).css('background-color', LENGTH_ALERT_COLOR);
						} else {
							$(this).css('background-color', LENGTH_OKAY_COLOR);
						}
					}
				);
			}
		);

		// If separate traces is 'no' disallow color use, otherwise allow color use
		$('select#separateTraces').change(
			function($e) {
				if ($(this).val() == 'FALSE') {
					useColor(false);
					enumerate(true);
				} else {
					useColor(true);
					enumerate(false);
				}
			}
		);

		// Ensure that the tumor percent is an integer between 1 and 99
		$('input#tumorPercent').blur(
			function($e) {
				if (intRange($(this).val(), 0, 99) != 0) {
					alert('Tumor percent must be an integer in the range of 0 to 99.');
					$(this).val('');
					$(this).focus();
				}
			}
		);

		// Ensure that the mutant percent is between 1 and 100
		$('input.mutantPercent').blur(
			function($e) {
				if (intRange($(this).val(), 1, 100) != 0) {
					alert('Mutant percent must be an integer in the range of 1 to 100.');
					$(this).val('');
					$(this).focus();
				}
			}
		);

		// If the dispensation was provided, ensure that
		//   - it is at least 4 characters  long
		//   - it is composed of the letters G, A, T, and C only
		//   - each of G, A, T, and C appear in the string at least once
		$('input#dispensation').blur(
			function($e) {
				var msg, val;
				msg = '';
				val = $(this).val();
				$(this).val(gatcOnly(val));
				if ($(this).val().length < val.length) {
					msg += 'Characters other than G, A, T, and C have been removed.  ';
				}
				val = $(this).val();
				if (val.length > 0) {
					if (val.length < 4) {
						msg += 'There must be at least 4 characters.  ';
					}
					if (val.indexOf('G') < 0 || val.indexOf('A') < 0 || val.indexOf('T') < 0 || val.indexOf('C') < 0) {
						msg += 'At least one each of G, A, T, and C must be included.';
					}
				}
				if (msg != '') {
					alert('Dispensation: ' + msg);
					$(this).focus();
				}
			}
		);

		// Ensure that limit of detection is an integer from 0 to 99
		$('input#limitOfDetection').blur(
			function($e) {
				if (intRange($(this).val(), 0, 99) != 0) {
					alert('Limit of detection, if supplied, must be an integer in the range of 0 to 99.');
					$(this).val('');
					$(this).focus();
				}
			}
		);

		$('textarea.sequence').focus(
			function($e) {
				var rows;
				rows = $('#traceType').val() == 'PYRO' ? ROWS_PYRO : ROWS_SANGER;
				$(this).attr('rows', rows);
			}
		);

		$('textarea.sequence').blur(
			function($e) {
				var maxlength, msg, sequence;
				msg = '';
				sequence = $(this).val();
				$(this).val(gatcOnly(sequence));
				if ($(this).val().length < sequence.length) {
					msg += 'Characters other than G, A, T, and C have been removed.  ';
					$(this).focus();
				}
				maxlength = maxSequenceLength();
				if ($(this).val().length > maxlength) {
					$(this).css('background-color', LENGTH_ALERT_COLOR);
				} else {
					$(this).css('background-color', LENGTH_OKAY_COLOR);
				}
				if (msg != "") alert(msg);
				$(this).attr('rows', ROWS_MIN);
				
			}
		);

		initialize();
	}
);
