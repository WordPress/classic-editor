#!/bin/bash

# Watch the webserver's `error_log` file inside the lando container, and clean
# up a bunch of noise in the output.

lando logs appserver_1 -f | perl -nwe '
	# no buffering
	select(STDOUT); $| = 1;

	# add colors, but only if stdout is a terminal
	my $acc_begin = "";
	my $err_begin = "";
	my $end = "";
	-t 1 and $acc_begin = "\e[0;32m"; # green
	-t 1 and $err_begin = "\e[1;35m"; # purple
	-t 1 and $end = "\e[0m"; # reset

	# get rid of appserver_1 prefix + ANSI codes
	s/^.{0,8}appserver_1\s+\|.{0,8}\s+//;

	if (/\[:error\]/) {
		# clean up error log entry
		# get rid of unneeded markers
		s/\[:error\] \[pid \d+\] \[client [^]]+\] //;

		# keep and maybe colorize time, remove date and microseconds
		s/^\[... ... \d\d /${err_begin}E /;
		s/\d\d\d 20\d\d\]/${end}/;

		# remove "referer"
		s/, referer: h\S+$//;

		print;

	} elsif (/^[\d.]+ - -/) {
		# clean up access log entry
		s/^/${acc_begin}A /;
		s/ - - \[.*20\d\d:/ /;
		s/ [\d+-]+\]/${end}/;

		# TODO: ignored (not printed)
	}
'
