<?php
/* --------------------------------------------------------------------------------
Name:         r.php
Purpose:      Build the parameter file and call the R script to generate the graph
Author:       Alan O'Neill
Release Date: January 2, 2013
-------------------------------------------------------------------------------- */

include_once('constant.php');
include_once('library.php');
include_once('logger.php');

function r_list($fp, $label, $field, $skip1, &$sequences) {
	// Output the values for the specified field
	// fp - pointer to the output file
	// label - the label required for the field in the parameter file (e.g., 'sequence names')
	// field - the name of the field to output (e.g., sequenceName)
	// skip1 - true to skip the first entry
	// sequences - the sequences portion of the data array

	fwrite($fp, "$label\t");
	$some = false;
	$first = $skip1 ? 2 : 1;
	for ($seqno = $first; $seqno <= MAXSANGERSEQUENCES; $seqno ++) {
		$sequence = isset($sequences[$seqno]['sequence']) ? $sequences[$seqno]['sequence'] : '';
		if (strlen($sequence) > 0) {
			$value = $sequences[$seqno][$field];
			if ($some) {
				fwrite($fp, ',');
			}
			fwrite($fp, $value);
			$some = true;
		}
	}
	fwrite($fp, "\n");
}

function r_run(&$data) {
	// Create the parameter file for the R script and run the script
	// data - the array holding the parameters
	// Return:  the name of the image file on success; "" on failure

	$parmfile = tempnam(RDIR, lib_filebase());
	if ($parmfile) {
		chmod($parmfile, 0644);
	}
	log_add('parmfile', $parmfile);
	$imagefile = '';
	$fp = fopen($parmfile, 'w');
	if ($fp) {
		$imagefile = $parmfile . ".png";

		fwrite($fp, "names\tvalues\n");
		r_list($fp, 'sequences', 'sequence', false, $data['sequences']);
		r_list($fp, 'sequence names', 'sequenceName', false, $data['sequences']);
		fwrite($fp, "tumor percent\t" . $data['tumorPercent'] . "\n");
		r_list($fp, 'mutation percent', 'mutantPercent', true, $data['sequences']);
		r_list($fp, 'chromosome status', 'chromosomeStatus', true, $data['sequences']);
		fwrite($fp, "dispensation\t" . $data['dispensation'] . "\n");
		fwrite($fp, "dispensation type\t" . "FIXED" . "\n");
		fwrite($fp, "separate traces\t" . $data['separateTraces'] . "\n");
		fwrite($fp, "add annotation\t" . $data['annotation'] . "\n");
		if ($data['traceType'] == 'PYRO' and $data['separateTraces'] == "TRUE") {
			r_list($fp, 'sequence colors', 'plotColor', false, $data['sequences']);
		}
		fwrite($fp, "limit of detectcion\t" . $data['limitOfDetection'] . "\n");
		fwrite($fp, "trace type\t" . $data['traceType'] . "\n");
		fwrite($fp, "correction factor\t\n");
		fwrite($fp, "yaxis increment\t\n");
		fwrite($fp, "peak width\t\n");
		fwrite($fp, "points per base\t\n");
		fwrite($fp, "bases per line\t\n");
		fwrite($fp, "lines per page\t\n");
		fwrite($fp, "save to file\tTRUE\n");
		fwrite($fp, "directory\t" . dirname($imagefile) . "\n");
		fwrite($fp, "file path\t$imagefile\n");

		fclose($fp);

		// Run the R script
		$script = 'pyromaker.r';
		exec('R --slave --silent --no-save --args ' . $parmfile . ' < ' . $script);

		if (! file_exists($imagefile)) {
			$imagefile = '';
		} else {
			$imagefile = RDIR . '/' . basename($imagefile);
		}
	}

	return $imagefile;
}
?>
