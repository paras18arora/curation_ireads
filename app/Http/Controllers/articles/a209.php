<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h3>Getting Started</h3>
You will need to open an SSH connection on your cloud server as the root user or an SSH connection to a user with sudo access. This guide assumes a user with sudo access. However you can set things up using root just by stripping the 'sudo' from the start of each command. If your system is running on Linux or Mac, you can use SSH with the Terminal program.  If you are using Windows, you can use SSH with <a href="https://indiareads/community/articles/how-to-log-into-your-droplet-with-putty-for-windows-users">PuTTY</a>. Once you have the Terminal opened, assuming you're using a Linux/Mac system, you can login by typing the following command:
<pre>ssh <i>username</i>@<i>ipaddress</i></pre><br />

Enter the password when you're asked to, and you're ready to start setting up OpenVPN.

<h2>Install OpenVPN and generate necessary files</h2><hr />

Before we start installing OpenVPN and its prerequisites, we should make sure all of the packages on our system are up to date. We can do that with the following command:
<pre>sudo apt-get update</pre><br />

This should have apt, Debian's package manager. Download all the updates for any packages that have them.
<pre>sudo apt-get upgrade</pre><br />

After our system has downloaded all its updates, we can finally install OpenVPN.
<pre>sudo apt-get install openvpn udev</pre><br />

Once the installation is done, you are ready to begin configuring OpenVPN. To begin, you should copy all the files for encryption from their default directory into the directory they should be in for the cloud server to read them.
<pre>sudo cp -r /usr/share/doc/openvpn/examples/easy-rsa /etc/openvpn</pre><br />

Now that you've done that, you can begin generating the RSA algorithm files for your VPN. You will be asked to provide various values when you're generating these keys. You can set these to whatever you would like to, but bear in mind that they will be included in the certificates you generate.<br /><br />

To begin, access into the following directory:
<pre>cd /etc/openvpn/easy-rsa/2.0/</pre><br />

Then generate the RSA files:
<pre>source ./vars<br />
sudo ./clean-all<br />
sudo ./build-ca</pre><br />

After the certificate is generated, you can make the private key for the server. To do this, type the following command, and change 'server' to what you'd like the name of your OpenVPN server to be. This script will also ask you for information.

<pre>sudo . /etc/openvpn/easy-rsa/2.0/build-key-server server</pre><br />

Generate the Diffie Hellman key exchange parameters.

<pre>sudo . /etc/openvpn/easy-rsa/2.0/build-dh</pre><br />

Now generate the keys for each client this installation of OpenVPN will host. You should do this step for each client this installation will host, making sure each client's key identifier is unique. 

<pre>sudo . /etc/openvpn/easy-rsa/2.0/build-key client</pre><br />

Move the files for the server certificates and keys to the /etc/openvpn directory now. Replace server.crt and server.key with the file names that you used.

<pre>sudo cp /etc/openvpn/easy-rsa/2.0/keys/ca.crt /etc/openvpn<br />
sudo cp /etc/openvpn/easy-rsa/2.0/keys/ca.key /etc/openvpn<br />
sudo cp /etc/openvpn/easy-rsa/2.0/keys/dh1024.pem /etc/openvpn<br />
sudo cp /etc/openvpn/easy-rsa/2.0/keys/server.crt /etc/openvpn<br />
sudo cp /etc/openvpn/easy-rsa/2.0/keys/server.key /etc/openvpn</pre><br />

If you need to remove someone's access to the VPN, just send the following two commands. Replacing 'client' with the name of the client to be removed.

<pre>sudo . /etc/openvpn/easy-rsa/2.0/vars
sudo . /etc/openvpn/easy-rsa/2.0/revoke-full client1</pre>

<h2>Configure OpenVPN</h2><hr />

Now that you have generated the files for our configuration, you can go ahead and configure your OpenVPN server and client. To retrieve the files, execute the following commands:

<pre>sudo gunzip -d /usr/share/doc/openvpn/examples/sample-config-files/server.conf.gz<br />
sudo cp /usr/share/doc/openvpn/examples/sample-config-files/server.conf /etc/openvpn<br />
sudo cp /usr/share/doc/openvpn/examples/sample-config-files/client.conf ~/<br />
cd</pre><br />

You should modify the client configuration file to match what you'd like it to do. You can also modify several values in the following file to match what you'd like. In order to do this, you first change the 'remote' option so it can connect to your cloud server's IP address on whichever port you configured your OpenVPN to run on. Then change the 'cert' and 'key' values to reflect the names of your own certificate and key. After these values have been edited you can save the file by typing in Ctrl+X, type 'y', then hit Enter.<br /><br />

Now copy the client configuration file, along with the client keys and certificates located in /etc/openvpn/easy-rsa/2.0/keys to the local machines of the clients.
<pre>nano ~/client.conf</pre><br />

After you've done this, you just need to make a few changes to your server configuration file before we finalize. Change the files that the 'cert' and 'key' options point to in the following file to match the certificate and key that your server is using.
<pre>sudo nano /etc/openvpn/server.conf</pre><br />

After that's finished, you're ready to go! Just restart OpenVPN and you've got a working OpenVPN installation on Debian 6!
<pre>sudo /etc/init.d/openvpn restart</pre></div>
    