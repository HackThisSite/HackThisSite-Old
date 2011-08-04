--- HackThisSite Readme ---

HackThisSite uses the robust G-WAN web server to run its C servlets and Node/V8
scripts. In order to run the HackThisSite G-WAN instance, $LD_LIBRARY_PATH must
have the $HTS_PATH/libraries/v8 in it. Under bash, run ( $ export
LD_LIBRARY_PATH=$LD_LIBRARY_PATH:$HTS_PATH/libraries/v8 ). $HTS_PATH is the path
to the HackThisSite directory that contains the `gwan` binary. Rename
$HTS_PATH/127.0.0.01_8080 to 192.168.0.2_80 if you want to listen on address
192.18.0.2 port 80. In your hosts file, set (my.hts.org) & (static.my.hts.org)
to point to 127.0.0.1. Once that's done, you are all set to run the HackThisSite
instance. Install the SysV init script in $HTS_PATH/docs/hackthissite.sysvinit,
just run the `gwan` binary after your current working directory is the
$HTS_PATH, run ( $ ./gwan ) as root.
