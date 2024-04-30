<?php
/* --------------------------------------------------------------------------------
Name:         option.php
Purpose:      Define the options available to all HTML <select> tags.
Author:       Alan O'Neill
Release Date: January 2, 2013
-------------------------------------------------------------------------------- */

function option_traceType(&$options) {
	$options['PYRO'] = 'Pyro';
	$options['SANGER'] = 'Sanger';
}

function option_separateTraces(&$options) {
	$options['FALSE']='No';
	$options['TRUE']='Yes';
}

function option_chromosomeStatus(&$options) {
	$options['HETEROZYGOUS'] = 'Heterozygous';
	$options['HOMOZYGOUS'] = 'Homozygous';
	$options['HEMIZYGOUS'] = 'Hemizygous';
}

function option_plotColor(&$options) {
	// There are values in constant.php that need to be updated if the number of colors is changed.
	$options['BLACK'] = 'Black';
	$options['RED'] = 'Red';
	$options['ORANGE'] = 'Orange';
	$options['YELLOW'] = 'Yellow';
	$options['GREEN'] = 'Green';
	$options['BLUE'] = 'Blue';
	$options['PURPLE'] = 'Purple';
	$options['BROWN'] = 'Brown';
}

function option_annotation(&$options) {
	$options['FALSE']='No';
	$options['TRUE']='Yes';
}
?>
