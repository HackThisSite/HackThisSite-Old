<div class="page-header"><h1>Using Certificate Authentication on HackThisSite</h1></div>

<p>Certificate authentication allows you to login to your account faster 
than ever, keeps your account secure better than any password system.  
HackThisSite's PKI is based off of the paper 
<a href="http://www.phoenix-web.us/compinfo/pubkey/index.html">Public key 
encryption for web site authentication and identification</a> by David 
Pacheco.</p>

<p>
First, users must generate a private key and certificate signing request.  
On most Unix systems this can be done by:</p>
<pre>
openssl genrsa -out client.key 1024
openssl req -new -key client.key -out client.csr
</pre>

<p>Then you must paste the contents of <i>client.csr</i> to the <b>Request 
Certificate</b> text box on your <b>Settings</b> page.  You will then be 
given a certificate which you should save to the file <i>client.crt</i>.  
After that, you can generate the PKCS #12 file for your browser by running 
the command:
</p>
<pre>
openssl pkcs12 -export -clcerts -in client.crt -inkey client.key -out client.p12
</pre>
<p>Once that has finished, you can import the PKCS #12 file into your 
browser and login to HackThisSite by either method of your choice.  
HackThisSite can automatically identify you or you can login by typing 
your username and a blank password into the login form.</p><br />

<a name="auths"></a>
<legend>Authentication Methods</legend>
<p>In increasing order of security:</p>

<ol>
	<li><u>Password Authentication</u> - Typical method of authentication; 
	user must enter a username and password in order to start a 
	session.</li>
	<li><u>Automatic Authentication</u> - You will be automatically 
	authenticated if you are found to have a valid certificate.  
	<em>Note:  This makes it impossible to logout!</em></li>
	<li><u>Certificate Authentication</u> - Requires the user to have a 
	valid certificate to use.  To login with this method, connect to the 
	site over SSL with your PCKCS #12 file and click the login button.  
	You will be automatically authenticated.</li>
	<li><u>Certificate and Password Authentication</u> - This is a combination 
	of the two methods above.  Instead of using a blank login like 
	Certificate Authentication, the user needs to type in their username 
	and password as well as have a valid certificate.</li>
</ol>

<p><strong>Important:  </strong>Should you ever loose access to your 
account due to certificate errors, your account can be automatically 
restored by going through the Password Recovery process.</p>
