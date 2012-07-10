Certificate Authentication
==========================
_(This page pertains only to HackThisSite version 5.)_

Certificate authentication allows you to login to your account faster than ever, and keeps your account more secure than any password system out there.  HackThisSite's PKI is based off of the paper 
[Public key  encryption for web site authentication and identification](http://www.phoenix-web.us/compinfo/pubkey/index.html) by David Pacheco.

First, users must generate a private key and certificate signing request.  On most Unix systems this can be done by:
```
openssl genrsa -out client.key 1024
openssl req -new -key client.key -out client.cs
```

Then you must paste the contents of _client.csr_ into the **Request Certificate** text box on your **Settings** page.  You will then be given a certificate which you should save to the file _client.crt_.  After that, you can generate the PKCS #12 file for your browser by running the command:
```
openssl pkcs12 -export -clcerts -in client.crt -inkey client.key -out client.p12
```
Once that has finished, you can import the PKCS #12 file into your browser and login to HackThisSite by any of the methods described below.


Authentication Methods
----------------------
In increasing order of security:

1. **Password Authentication** - Typical method of authentication; user must enter a username and password in order to start a session.
2. **Automatic Authentication** - You will be automatically authenticated if you are found to have a valid certificate.  _Note:  This makes it impossible to logout!  Also, if this is your only method of authentication, it makes it impossible for us to tell you that you are banned._
3. **Certificate Authentication** - Requires the user to have a valid certificate to use.  To login with this method, connect to the site over SSL with your PCKCS #12 file and click the login button.  You will be automatically authenticated.
4. **Certificate and Password Authentication** - This is a combination of two of the methods above.  Instead of using a blank login like Certificate Authentication, the user needs to type in their username and password as well as have a valid certificate.

**Important:**  Should you ever loose access to your 
account due to certificate errors, your account can be automatically 
restored by going through the Password Recovery process.

In the future we hope to add the ability to create certificates through HTML5's `<keygen>` element, however support for this element is so sparse in browsers and programming APIs, we're unable to at this point in time.
