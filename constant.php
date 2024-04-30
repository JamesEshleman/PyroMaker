<?php
/* --------------------------------------------------------------------------------
Name:         constant.php
Purpose:      Provides constants for Pyromaker
Author:       Alan O'Neill
Release Date: July 1, 2011
-------------------------------------------------------------------------------- */

// Whether (true) or not (false) the site is in maintenance mode
// In maintenance mode, the user cannot use Pyromaker.  Instead, they see the
// contents of the maint.html page.  The system administrator may wish to edit
// the contents of main.html before putting the site into maintenance mode.
define('MAINT', false);

// The maximum number of colors available
// Be sure this number matches the number of colors set in options.php
define('MAXCOLORS', 8);

// The maximum length of a Pyro sequence
define('MAXPYROLENGTH', 50);

// The maximum length of a Sanger sequence
define('MAXSANGERLENGTH', 1000);

// The maximum length of the dispensation
define('MAXDISPENSATION', 200);

// The maximum length of a sequence name
define('MAXSEQNAMELEN', 10);

// The maximum number of Pyro sequences that can be entered
// This number should match the number of colors (above)
define('MAXPYROSEQUENCES', 8);

// The maximum number of Sanger sequences that can be entered
define('MAXSANGERSEQUENCES', 20);

// The maximum number of sequences
// This value must be the larger of Pyro and Sanger from above
define('MAXSEQUENCES', 20);

// Whether (true) or not (false) logging should be turned on
// When logging is turned off, log files are not placed into the log directory (above)
define('LOGGING', true);

// The directory where log files are kept
// Make sure the directory has the permissions drwxrwsr-x
// and that it has the same group as the web server (e.g., www-data)
define('LOGDIR', '/mnt/wh/linux/var/local/pyromaker/log');

// The working directory for the R script
// Make sure the directory has the permissions drwxrwsr-x
// and that it has the same group as the web server (e.g., www-data)
define('RDIR', './r');
?>
