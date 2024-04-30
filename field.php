<?php
/* --------------------------------------------------------------------------------
Name:         field.php
Purpose:      Display form elements -- Depending on the value of the $ask
              parameter, the element will either simply be displayed as text only
              or it will be displayed as an input box, allowing the user to update
              the value.
Author:       Alan O'Neill
Release Date: January 2, 2013
-------------------------------------------------------------------------------- */

include_once('library.php');
include_once('option.php');
include_once('constant.php');

function field_traceType($ask, $value) {
	// ask - 'true' to display the form element or 'false' to display the answer only
	// value - the default or actual response

	option_traceType($options);
	echo '<p><label>Trace Type: </label>';
	if ($ask) {
		echo '<select id="traceType" name="traceType">';
		lib_expandOptions($options, $value);
		echo '</select>';
	} else {
		lib_showOptionValue($options, $value);
	}
	echo '</p>';
}

function field_limitOfDetection($ask, $value) {
	// ask - 'true' to display the form element or 'false' to display the answer only
	// value - the default or actual response

	echo '<p><label>Limit of Detection: </label>';
	if ($ask) {
		echo '<input id="limitOfDetection" name="limitOfDetection" type="text" size="2" maxlength="2" value="', $value, '" />';
		echo ' May be blank (instrumental default) or an integer from 0 to 99.';
	} else {
		if (strlen($value) == 0) {
			echo "Instrumental default";
		} else {
			echo $value;
		}
	}
	echo '</p>';
}

function field_tumorPercent($ask, $value) {
	// ask - 'true' to display the form element or 'false' to display the answer only
	// value - the default or actual response

	echo '<p><label>% Mutation-Containing Cells: </label>';
	if ($ask) {
		echo '<input id="tumorPercent" name="tumorPercent" type="text" size="2" maxlength="2" value="', $value, '" />';
		echo '</p>';
		echo '<p class="nojs">Tumor percent must be an integer from 0 to 99.</p>';
	} else {
		echo $value, "</p>";
	}
}

function field_separateTraces($ask, $value) {
	// ask - 'true' to display the form element or 'false' to display the answer only
	// value - the default or actual response

	option_separateTraces($options);
	echo '<p class="pyroSpecificParagraph"><label>Separate Traces: </label>';
	if ($ask) {
		echo '<select id="separateTraces" name="separateTraces">';
		lib_expandOptions($options, $value);
		echo '</select>';
		echo '<p class="nojs">When trace type is Sanger, separate traces are not used.</p>';
	} else {
		lib_showOptionValue($options, $value);
	}
	echo '</p>';
}

function field_dispensation($ask, $value) {
	// ask - 'true' to display the form element or 'false' to display the answer only
	// value - the default or actual response

	echo '<p class="pyroSpecificParagraph"><label>Dispensation: </label>';
	if ($ask) {
		echo '<input id="dispensation" name="dispensation" type="text" size="50" maxlength="', MAXDISPENSATION, '" value="', $value, '"/>';
		echo '</p>';
		echo '<p class="nojs">When trace type is Sanger, dispensation is not used.</p>';
		echo '<p class="nojs">When trace is Pyro:';
		echo '<ul class="nojs">';
		echo '<li>Dispensation must be at least 4 characters long.</li>';
		echo '<li>It must contain at least one each of G, A, T, and C.</li>';
		echo '<li>It may not contain characters other than G, A, T, and C.</li>';
		echo '</ul>';
		echo '</p>';
	} else {
		echo $value;
	}
}

function field_annotation($ask, $value) {
	// ask - 'true' to display the form element or 'false' to display the answer only
	// value - the default or actual response

	option_annotation($options);
	echo '<p><label>Add Annotation: </label>';
	if ($ask) {
		echo '<select id="annotation" name="annotation">';
		lib_expandOptions($options, $value);
		echo '</select>';
	} else {
		lib_showOptionValue($options, $value);
	}
	echo '</p>';
}

function field_sequenceEnumeration($seqno, $ask, $value) {
	// seqno - the sequence number
	// ask - 'true' to display the form element or 'false' to display the answer only
	// value - the default or actual response (not used)

	echo '<td class="enumerationCell">', $value, "</td>";
}

function field_sequenceName($seqno, $ask, $value) {
	// seqno - the sequence number
	// ask - 'true' to display the form element or 'false' to display the answer only
	// value - the default or actual response

	echo '<td>';
	if ($ask) {
		echo '<input id="sequenceName_', $seqno, '" name="sequenceName_', $seqno, '" type="text" size="', MAXSEQNAMELEN, '" maxlength="', MAXSEQNAMELEN, '" value="', $value, '" /></td>';
	} else {
		echo $value, "<br />";
	}
}

function field_sequence($seqno, $ask, $value) {
	// seqno - the sequence number
	// ask - 'true' to display the form element or 'false' to display the answer only
	// value - the default or actual response

	echo '<td>';
	if ($ask) {
		echo '<textarea id="sequence_', $seqno, '" class="sequence" name="sequence_', $seqno, '" cols="25" rows="2">', $value, '</textarea></td>';
	} else {
		echo $value;
	}
}

function field_mutantPercent($seqno, $ask, $value) {
	// seqno - the sequence number
	// ask - 'true' to display the form element or 'false' to display the answer only
	// value - the default or actual response

	echo '<td>';
	if ($seqno == 1) {
		if ($ask) {
			echo '&nbsp;';
		} else {
			echo 'n/a';
		}
	} else {
		if ($ask) {
			echo '<input id="mutantPercent_', $seqno, '" class="mutantPercent" name="mutantPercent_', $seqno, '" type="text" size="3" maxlength="3" value="', $value, '" />';
		} else {
			echo $value;
		}
	}
	echo '</td>';
}

function field_chromosomeStatus($seqno, $ask, $value) {
	// seqno - the sequence number
	// ask - 'true' to display the form element or 'false' to display the answer only
	// value - the default or actual response

	echo '<td>';
	if ($seqno == 1) {
		if ($ask) {
			echo '&nbsp;';
		} else {
			echo 'n/a';
		}
	} else {
		option_chromosomeStatus($options);
		if ($ask) {
			echo '<select id="chromosomeStatus_', $seqno, '" name="chromosomeStatus_', $seqno, '">';
			lib_expandOptions($options, $value);
			echo '</select>';
		} else {
			lib_showOptionValue($options, $value);
		}
	}
	echo '</td>';
}

function field_plotColor($seqno, $ask, $value) {
	// seqno - the sequence number
	// ask - 'true' to display the form element or 'false' to display the answer only
	// value - the default or actual response

	echo '<td class="userColorCell">';
	if ($seqno <= MAXCOLORS) {
		option_plotColor($options);
		if ($ask) {
			echo '<select id="plotColor_', $seqno, '" name="plotColor_', $seqno, '">';
			lib_expandOptions($options, $value);
			echo '</select>';
		} else {
			lib_showOptionValue($options, $value);
		}
	} else {
		echo '&nbsp;';
	}
	echo '</td>';
}

function field_sequences($ask, &$value, $usecolor) {
	// ask - 'true' to display the form element or 'false' to display the answer only
	// value - the default or actual response
	// usecolor - whether or not the color column should be used
	//    If 'ask' is true, the value of 'usecolor' is not considered

	echo '<p><label>Sequences:</label></p>';
	if ($ask) {
		echo '<p>Pyro sequences may be up to <span id="pyroLength">', MAXPYROLENGTH, '</span> characters, ';
		echo 'and Sanger sequences may be up to <span id="sangerLength">', MAXSANGERLENGTH, '</span> characters.';
		echo '<br />Extra characters will be truncated when the form is submitted.</p>';
		echo '<p class="nojs">Sequences may contain only the letters G, A, T, and C.</p>';
		echo '<p class="nojs">At least one sequence other than wild must be entered.</p>';
		echo '<p class="nojs">When trace type is Pyro,';
		echo '<ul class="nojs">';
		echo '<li>Only the first eight sequences will be used.</li>';
		echo '<li>Each sequence may be up to ', MAXPYROLENGTH, ' characters.</li>';
		echo '<li>If separate traces are requested, each sequence may have its own color.</li>';
		echo '</ul>';
		echo '</p>';
		echo '<p class="nojs">When trace type is Sanger,';
		echo '<ul class="nojs">';
		echo '<li>All ', MAXSEQUENCES, ' sequences may be used.</li>';
		echo '<li>Each sequence may be up to ', MAXSANGERLENGTH, ' characters.</li>';
		echo '<li>Color will not be used.</li>';
		echo '</ul>';
		echo '</p>';
		echo '<p class="nojs">Mutant percent must be an integer from 1 to 100.</p>';
	}
	echo '<table>';
	echo '<thead>';
	echo '<tr>';
	echo '<th class="enumerationCell"><label>#</label></th>';
	echo '<th><label>Sequence<br />Name</label></th>';
	echo '<th><label>Sequence</label></th>';
	echo '<th><label>Mutant<br />Percent</label></th>';
	echo '<th><label>Chromosome<br />Status</label></th>';
	if ($ask or $usecolor) {
		echo '<th class="userColorCell"><label>Plot<br />Color</label></th>';
	}
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	for ($seqno = 1; $seqno <= MAXSEQUENCES; $seqno ++) {
		$seqlen = isset($value[$seqno]['sequence']) ? strlen($value[$seqno]['sequence']) : 0;
		if ($ask or $seqlen > 0) {
			echo $seqno <= MAXCOLORS ? '<tr>' : '<tr class="autoColorRow">';
			field_sequenceEnumeration($seqno, $ask, $seqno);
			// Correct 'Undefined offset' error
			// field_sequenceName($seqno, $ask, $value[$seqno]['sequenceName']);
			field_sequenceName($seqno, $ask, isset($value[$seqno]['sequenceName']) ? $value[$seqno]['sequenceName'] : '');
			if ($ask) {
				// Correct 'Undefined offset' error
				// $sequence = $value[$seqno]['sequence'];
				$sequence = isset($value[$seqno]['sequence']) ? $value[$seqno]['sequence'] : '';
			} else {
				$sequence = '';
				for ($i = 0; $i < strlen($value[$seqno]['sequence']); $i++) {
					$base = substr($value[$seqno]['sequence'], $i, 1);
					if ($base == substr($value[1]['sequence'], $i, 1)) {
						$sequence .= $base;
					} else {
						$sequence .= '<strong>' . $base . '</strong>';
					}
				}
			}
			field_sequence($seqno, $ask, $sequence);
			// Sequence #1 is 'wild', which has no mutant percent or chromosome status
			if ($seqno == 1) {
				field_mutantPercent($seqno, $ask, '');
				field_chromosomeStatus($seqno, $ask, '');
			} else {
				// Correct 'Undefined offset' error
				// field_mutantPercent($seqno, $ask, $value[$seqno]['mutantPercent']);
				// field_chromosomeStatus($seqno, $ask, $value[$seqno]['chromosomeStatus']);
				field_mutantPercent($seqno, $ask, isset($value[$seqno]['mutantPercent']) ? $value[$seqno]['mutantPercent'] : '');
				field_chromosomeStatus($seqno, $ask, isset($value[$seqno]['chromosomeStatus']) ? $value[$seqno]['chromosomeStatus'] : '');
			}
			if ($ask or $usecolor) {
				// Correct 'Undefined offset' error
				// field_plotColor($seqno, $ask, $value[$seqno]['plotColor']);
				field_plotColor($seqno, $ask, isset($value[$seqno]['plotColor']) ? $value[$seqno]['plotColor'] : '');
			}
			echo '</tr>';
		}
	}
	echo '</tbody>';
	echo '</table>';
}

function field_submit() {
	echo '<p>';
	echo '<input id="submit" name="submit" type="submit" value="Generate Graph" />';
	echo '<input id="reset" name="reset" type="reset" value="Reset and Start Over" />';
	echo '</p>';
}

function field_get(&$data) {
	// Display the form, allowing input
	// data - the array holding the request values

	// echo '<p><a href="Pyrosequencing_Tutorial_MTO-JRE.pdf" target="_blank">Pyromaker Tutorial</a></p>';
	//echo '<form method="POST" action="', $_SERVER['PHP_SELF'], '">';
	echo '<form method="POST" action="index.php">';
	echo '<input type="button" value="Pyromaker Tutorial" onclick="window.open(';
	echo "'http://pyromaker.pathology.jhmi.edu/Pyrosequencing_Tutorial_MTO-JRE.pdf','Tutorial','location=no,menubar=no,resizable=yes,status=no,toolbar=no'";
	echo ');">';
	echo '<p class="nojs"><strong>For a better experience, please turn on JavaScript.</strong></p>';
	field_traceType(true, $data['traceType']);
	field_limitOfDetection(true, $data['limitOfDetection']);
	field_tumorPercent(true, $data['tumorPercent']);
	field_separateTraces(true, $data['separateTraces']);
	field_dispensation(true, $data['dispensation']);
	field_annotation(true, $data['annotation']);
	field_submit();
	field_sequences(true, $data['sequences'], true);
	echo '</form>';
}

function field_say(&$data) {
	// Display the form (without allowing input) and the graph
	// data - the array holding the request values
	// Return whether (true) or not (false) the graph was generated

	field_traceType(false, $data['traceType']);
	field_limitOfDetection(false, $data['limitOfDetection']);
	field_tumorPercent(false, $data['tumorPercent']);
	field_separateTraces(false, $data['separateTraces']);
	field_dispensation(false, $data['dispensation']);
	field_annotation(false, $data['annotation']);
	field_sequences(false, $data['sequences'], $data['separateTraces'] == 'TRUE');

	// Log the start and end time of the run of the R script
	$start = microtime_float();
	log_add('start_r', $start);
	$imagefile = r_run($data);
	$end = microtime_float();
	log_add('end_r', $end);
	$duration = $end - $start;
	echo '<p>Graph generated in ', $duration, ' seconds</p>';

	if (strlen($imagefile) > 0) {
		echo '<p><img src="', $imagefile, '" alt="Pyrosequencing Graph" /></p>';
		$okay = true;
	} else {
		echo '<p><strong>Sorry, an error occurred, and I was unable to generate the graph.</strong></p>';
		$okay = false;
	}

	return $okay;
}
?>
