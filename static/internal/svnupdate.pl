#!/usr/bin/perl

my $svn = '/usr/local/bin/svn';
my $path = '/www/data.hackthissite.org/www';
my $user = 'htsdataserver';
my $pass = 'A7n4bC-3.eciF3f2kxz!nfI439c*fKe3c,s.3f83md1';

print "Content-type: text/html\n\n";

my @svnreturn = `$svn update $path --username="$user" --password="$pass" --non-interactive`;

if ($svnreturn[$#svnreturn] =~ /At revision ([0-9]+)/ || $svnreturn[$#svnreturn] =~ /Updated to revision ([0-9]+)/) {
  print "SUCCESS\n".$1;
} else {
  print "FAIL\n".join('', @svnreturn);
}
