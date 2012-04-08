<center>
	<h2><u>Using Certificate Authentication<br />
	on HackThisSite</u></h2>
</center>

<p>Certificate authentication allows you to login to your account faster 
than ever, keeps your account secure better than any password system.  
HackThisSite's PKI is based off of the paper 
<a href="http://www.phoenix-web.us/compinfo/pubkey/index.html">Public key 
encryption for web site authentication and identification</a> by David 
Pacheco.</p>

<p>
First, users must generate a private key and certificate signing request.  
On most Unix systems this can be done by:</p>
<code>
openssl genrsa -out client.key 1024<br />
openssl req -new -key client.key -out client.csr
</code>

<p>Then you must paste the contents of <i>client.csr</i> to the <b>Request 
Certificate</b> text box on your <b>Settings</b> page.  You will then be 
given a certificate which you should save to the file <i>client.crt</i>.  
After that, you can generate the PKCS #12 file for your browser by running 
the command:
</p>
<code>
openssl pkcs12 -export -clcerts -in client.crt -inkey client.key -out client.p12
</code>
<p>Once that has finished, you can import the PKCS #12 file into your 
browser and login to HackThisSite by either method of your choice.  
HackThisSite can automatically identify you or you can login by typing 
your username and a blank password into the login form.</p><br />

<a name="auths"></a>
<u><h3>Authentication Methods</h3></u>
