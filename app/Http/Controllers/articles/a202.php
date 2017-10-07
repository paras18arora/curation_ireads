<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/OpenVPN-Twitter.V2.png?1426699813/> <br> 
      <h2 id="introduction">Introduction</h2>

<p>This tutorial will explain how to set up and run an <a href="http://openvpn.net/index.php/open-source">OpenVPN</a> container with the help of <a href="https://docker.com">Docker</a>.</p>

<p>OpenVPN provides a way to create virtual private networks (VPNs) using TLS (evolution of SSL) encryption. OpenVPN protects the network traffic from eavesdropping and man-in-the-middle (MITM) attacks. The private network can be used to securely connect a device, such as a laptop or mobile phone running on an insecure WiFi network, to a remote server that then relays the traffic to the Internet. Private networks can also be used to securely connect devices to each other over the Internet.</p>

<p>Docker provides a way to encapsulate the OpenVPN server process and configuration data so that it is more easily managed. The Docker OpenVPN <em>image</em> is prebuilt and includes all of the necessary dependencies to run the server in a sane and stable environment. Scripts are included to significantly automate the standard use case, but still allow for full manual configuration if desired. A Docker <em>volume container</em> is used to hold the configuration and EasyRSA PKI certificate data as well.</p>

<p><a href="https://registry.hub.docker.com/">Docker Registry</a> is a central repository for both official and user developed Docker images. The image used in this tutorial is a user contributed image available at <a href="https://registry.hub.docker.com/u/kylemanna/openvpn">kylemanna/openvpn</a>. The image is assembled on Docker Registry's cloud build servers using the source from the <a href="https://github.com/kylemanna/docker-openvpn">GitHub project</a> repository. The cloud server build linked to Github adds the ability to audit the Docker image so that users can review the source Dockerfile and related code, called a <a href="http://blog.docker.com/2013/11/introducing-trusted-builds/">Trusted Build</a>. When the code is updated in the GitHub repository, a new Docker image is built and published on the Docker Registry.</p>

<h3 id="example-use-cases">Example Use Cases</h3>

<ul>
<li>Securely route to the Internet when on an untrusted public (WiFi) networks</li>
<li>Private network to connect a mobile laptop, office computer, home PC, or mobile phone</li>
<li>Private network for secure services behind NAT routers that don't have NAT traversal capabilities</li>
</ul>

<h3 id="goals">Goals</h3>

<ul>
<li>Set up the Docker daemon on Ubuntu 14.04 LTS</li>
<li>Set up a <a href="https://docs.docker.com/userguide/dockervolumes/#creating-and-mounting-a-data-volume-container">Docker volume container</a> to hold the configuration data</li>
<li>Generate a EasyRSA PKI certificate authority (CA)</li>
<li>Extract auto-generated client configuration files</li>
<li>Configure a select number of OpenVPN clients</li>
<li>Handle starting the Docker container on boot</li>
<li>Introduce advanced topics</li>
</ul>

<h3 id="prerequisites">Prerequisites</h3>

<ul>
<li>Linux shell knowledge. This guide largely assumes that the user is capable of setting up and running Linux daemons in the traditional sense</li>
<li>Root access on a remote server

<ul>
<li>A <a href="https://indiareads/?refcode=d19f7fe88c94">IndiaReads 1 CPU / 512 MB RAM Droplet</a> running Ubuntu 14.04 is assumed for this tutorial. Docker makes running the image on any host Linux distribution easy</li>
<li>Any virtual host will work as long as the host is running QEMU/KVM or Xen virtualization technology; <strong>OpenVZ will not work</strong></li>
<li>You will need root access on the server. This guide assumes the user is running as an unprivileged user with sudo enabled. Review the <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">Digital Ocean tutorial about user management on Ubuntu 14.04</a> if needed</li>
</ul></li>
<li>A local client device such as an Android phone, laptop, or PC. Almost all operating systems are supported via various OpenVPN clients</li>
</ul>

<h2 id="step-1-—-set-up-and-test-docker">Step 1 — Set Up and Test Docker</h2>

<p>Docker is moving fast and Ubuntu's long term support (LTS) policy doesn't keep up. To work around this we'll install a PPA that will get us the latest version of Docker.</p>

<p>Add the upstream Docker repository package signing key. The <code>apt-key</code> command uses elevated privileges via <code>sudo</code>, so a password prompt for the user's password may appear:</p>
<pre class="code-pre "><code langs="">curl -L https://get.docker.com/gpg | sudo apt-key add -
</code></pre>
<p><strong>Note:</strong> Enter your sudo password at the blinking cursor if necessary.</p>

<p>Add the upstream Docker repository to the system list:</p>
<pre class="code-pre "><code langs="">echo deb http://get.docker.io/ubuntu docker main | sudo tee /etc/apt/sources.list.d/docker.list
</code></pre>
<p>Update the package list and install the Docker package:</p>
<pre class="code-pre "><code langs="">sudo apt-get update && sudo apt-get install -y lxc-docker
</code></pre>
<p>Add your user to the <code>docker</code> group to enable communication with the Docker daemon as a normal user, where <code><span class="highlight">sammy</span></code> is your username. <strong>Exit and log in again for the new group to take effect</strong>:</p>
<pre class="code-pre "><code langs="">sudo usermod -aG docker <span class="highlight">sammy</span>
</code></pre>
<p>After <strong>re-logging in</strong> verify the group membership using the <code>id</code> command. The expected response should include <code>docker</code> like the following example:</p>
<pre class="code-pre "><code langs="">uid=1001(test0) gid=1001(test0) groups=1001(test0),27(sudo),999(docker)
</code></pre>
<p>Optional: Run <code>bash</code> in a simple Debian Docker image (<code>--rm</code> to clean up container after exit and <code>-it</code> for interactive) to verify Docker operation on host:</p>
<pre class="code-pre "><code langs="">docker run --rm -it debian:jessie bash -l
</code></pre>
<p>Expected response from docker as it pulls in the images and sets up the container:</p>
<pre class="code-pre "><code langs="">Unable to find image 'debian:jessie' locally
debian:jessie: The image you are pulling has been verified
511136ea3c5a: Pull complete
36fd425d7d8a: Pull complete
aaabd2b41e22: Pull complete
Status: Downloaded newer image for debian:jessie
root@de8ffd8f82f6:/#
</code></pre>
<p>Once inside the container you'll see the <code>root@<span class="highlight"><container id></span>:/#</code> prompt signifying that the current shell is in a Docker container. To confirm that it's different from the host, check the version of Debian running in the container:</p>
<pre class="code-pre "><code langs="">cat /etc/issue.net
</code></pre>
<p>Expected response for the OpenVPN container at the time of writing:</p>
<pre class="code-pre "><code langs="">Debian GNU/Linux jessie/sid
</code></pre>
<p>If you see a different version of Debian, that's fine.</p>

<p>Exit the container by typing <code>logout</code>, and the host's prompt should appear again.</p>

<h2 id="step-2-—-set-up-the-easyrsa-pki-certificate-store">Step 2 — Set Up the EasyRSA PKI Certificate Store</h2>

<p>This step is usually a headache for those familiar with OpenVPN or any services utilizing PKI. Luckily, Docker and the scripts in the Docker image simplify this step by generating configuration files and all the necessary certificate files for us.</p>

<p>Create a volume container. This tutorial will use the <code><span class="highlight">$OVPN_DATA</span></code> environmental variable to make it copy-paste friendly. Set this to anything you like. The default <code>ovpn-data</code> value is recommended for single OpenVPN Docker container servers. Setting the variable in the shell leverages string substitution to save the user from manually replacing it for each step in the tutorial:</p>
<pre class="code-pre "><code langs=""><span class="highlight">OVPN_DATA</span>="ovpn-data"
</code></pre>
<p>Create an empty Docker volume container using <code>busybox</code> as a minimal Docker image:</p>
<pre class="code-pre "><code langs="">docker run --name <span class="highlight">$OVPN_DATA</span> -v /etc/openvpn busybox
</code></pre>
<p>Initialize the <code><span class="highlight">$OVPN_DATA</span></code> container that will hold the configuration files and certificates, and replace <code><span class="highlight">vpn.example.com</span></code> with your FQDN. The <code><span class="highlight">vpn.example.com</span></code> value should be the fully-qualified domain name you use to communicate with the server. This assumes the <a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean">DNS settings</a> are already configured. Alternatively, it's possible to use just the IP address of the server, but this is not recommended.</p>
<pre class="code-pre "><code langs="">docker run --volumes-from <span class="highlight">$OVPN_DATA</span> --rm kylemanna/openvpn ovpn_genconfig -u udp://<span class="highlight">vpn.example.com</span>:1194
</code></pre>
<p>Generate the EasyRSA PKI certificate authority. You will be prompted for a passphrase for the CA private key. Pick a good one and remember it; without the passphrase it will be impossible to issue and sign client certificates:</p>
<pre class="code-pre "><code langs="">docker run --volumes-from <span class="highlight">$OVPN_DATA</span> --rm -it kylemanna/openvpn ovpn_initpki
</code></pre>
<p><strong>Note, the security of the <code><span class="highlight">$OVPN_DATA</span></code> container is important.</strong>  It contains all the private keys to impersonate the server and all the client certificates. Keep this in mind and control access as appropriate. The default OpenVPN scripts use a passphrase for the CA key to increase security and prevent issuing bogus certificates.</p>

<p>See the <strong>Conclusion</strong> below for more details on how to back up the certificate store.</p>

<h2 id="step-3-—-launch-the-openvpn-server">Step 3 — Launch the OpenVPN Server</h2>

<p>To autostart the Docker container that runs the OpenVPN server process (see <a href="https://docs.docker.com/articles/host_integration/">Docker Host Integration for more</a>) create an <a href="https://indiareads/community/tutorials/the-upstart-event-system-what-it-is-and-how-to-use-it">Upstart</a> init file using <code>nano</code> or <code>vim</code>:</p>
<pre class="code-pre "><code langs="">sudo vim /etc/init/docker-openvpn.conf
</code></pre>
<p>Contents to place in <code>/etc/init/docker-openvpn.conf</code>:</p>
<pre class="code-pre "><code langs="">description "Docker container for OpenVPN server"
start on filesystem and started docker
stop on runlevel [!2345]
respawn
script
  exec docker run --volumes-from ovpn-data --rm -p 1194:1194/udp --cap-add=NET_ADMIN kylemanna/openvpn
end script
</code></pre>
<p>Start the process using the Upstart init mechanism:</p>
<pre class="code-pre "><code langs="">sudo start docker-openvpn
</code></pre>
<p>Verify that the container started and didn't immediately crash by looking at the <code>STATUS</code> column:</p>
<pre class="code-pre "><code langs="">test0@tutorial0:~$ docker ps
CONTAINER ID        IMAGE                      COMMAND             CREATED             STATUS              PORTS                    NAMES
c3ca41324e1d        kylemanna/openvpn:latest   "ovpn_run"          2 seconds ago       Up 2 seconds        0.0.0.0:1194->1194/udp   focused_mestorf
</code></pre>
<h2 id="step-4-—-generate-client-certificates-and-config-files">Step 4 — Generate Client Certificates and Config Files</h2>

<p>In this section we'll create a client certificate using the PKI CA we created in the last step.</p>

<p>Be sure to replace <code><span class="highlight">CLIENTNAME</span></code> as appropriate (this doesn't have to be a FQDN). The client name is used to identify the machine the OpenVPN client is running on (e.g., "home-laptop", "work-laptop", "nexus5", etc.).</p>

<p>The <code>easyrsa</code> tool will prompt for the CA password. This is the password we set above during the <code>ovpn_initpki</code> command. Create the client certificate:</p>
<pre class="code-pre "><code langs="">docker run --volumes-from <span class="highlight">$OVPN_DATA</span> --rm -it kylemanna/openvpn easyrsa build-client-full <span class="highlight">CLIENTNAME</span> nopass
</code></pre>
<p>After each client is created, the server is ready to accept connections.</p>

<p>The clients need the certificates and a configuration file to connect. The embedded scripts automate this task and enable the user to write out a configuration to a single file that can then be transfered to the client. Again, replace <code><span class="highlight">CLIENTNAME</span></code> as appropriate:</p>
<pre class="code-pre "><code langs="">docker run --volumes-from <span class="highlight">$OVPN_DATA</span> --rm kylemanna/openvpn ovpn_getclient <span class="highlight">CLIENTNAME</span> > <span class="highlight">CLIENTNAME</span>.ovpn
</code></pre>
<p>The resulting <code><span class="highlight">CLIENTNAME</span>.ovpn</code> file contains the private keys and certificates necessary to connect to the VPN. <strong>Keep these files secure and not lying around.</strong> You'll need to securely transport the <code>*.ovpn</code> files to the clients that will use them. Avoid using public services like email or cloud storage if possible when transferring the files due to security concerns.</p>

<p>Recommend methods of transfer are ssh/scp, HTTPS, USB, and microSD cards where available.</p>

<h2 id="step-5-—-set-up-openvpn-clients">Step 5 — Set Up OpenVPN Clients</h2>

<p>The following are commands or operations run on the clients that will connect to the OpenVPN server configured above.</p>

<h3 id="ubuntu-and-debian-distributions-via-native-openvpn">Ubuntu and Debian Distributions via Native OpenVPN</h3>

<p>On Ubuntu 12.04/14.04 and Debian wheezy/jessie clients (and similar):</p>

<p>Install OpenVPN:</p>
<pre class="code-pre "><code langs="">sudo apt-get install openvpn
</code></pre>
<p>Copy the client configuration file from the server and set secure permissions:</p>
<pre class="code-pre "><code langs="">sudo install -o root -m 400 <span class="highlight">CLIENTNAME</span>.ovpn /etc/openvpn/<span class="highlight">CLIENTNAME</span>.conf
</code></pre>
<p>Configure the init scripts to autostart all configurations matching <code>/etc/openvpn/*.conf</code>:</p>
<pre class="code-pre "><code langs="">echo AUTOSTART=all | sudo tee -a /etc/default/openvpn
</code></pre>
<p>Restart the OpenVPN client's server process:</p>
<pre class="code-pre "><code langs="">sudo /etc/init.d/openvpn restart
</code></pre>
<h3 id="arch-linux-via-native-openvpn">Arch Linux via Native OpenVPN</h3>

<p>Install OpenVPN:</p>
<pre class="code-pre "><code langs="">pacman -Sy openvpn
</code></pre>
<p>Copy the client configuration file from the server and set secure permissions:</p>
<pre class="code-pre "><code langs="">sudo install -o root -m 400 <span class="highlight">CLIENTNAME</span>.ovpn /etc/openvpn/<span class="highlight">CLIENTNAME</span>.conf
</code></pre>
<p>Start OpenVPN client's server process:</p>
<pre class="code-pre "><code langs="">systemctl start openvpn@<span class="highlight">CLIENTNAME</span>
</code></pre>
<p>Optional: configure systemd to start <code>/etc/openvpn/<span class="highlight">CLIENTNAME</span>.conf</code> at boot:</p>
<pre class="code-pre "><code langs="">systemctl enable openvpn@<span class="highlight">CLIENTNAME</span>
</code></pre>
<h3 id="macos-x-via-tunnelblick">MacOS X via TunnelBlick</h3>

<p>Download and install <a href="https://code.google.com/p/tunnelblick/">TunnelBlick</a>.</p>

<p>Copy <code><span class="highlight">CLIENTNAME</span>.ovpn</code> from the server to the Mac.</p>

<p>Import the configuration by double clicking the <code>*.ovpn</code> file copied earlier. TunnelBlick will be invoked and the import the configuration.</p>

<p>Open TunnelBlick, select the configuration, and then select <strong>connect</strong>.</p>

<h3 id="android-via-openvpn-connect">Android via OpenVPN Connect</h3>

<p>Install the <a href="https://play.google.com/store/apps/details?id=net.openvpn.openvpn">OpenVPN Connect App</a> from the Google Play store.</p>

<p>Copy <code><span class="highlight">CLIENTNAME</span>.ovpn</code> from the server to the Android device in a secure manner. USB or microSD cards are safer. Place the file on your SD card to aid in opening it.</p>

<p>Import the configuration: <strong>Menu</strong> -> <strong>Import</strong> -> <strong>Import Profile from SD card</strong></p>

<p>Select <strong>connect</strong>.</p>

<h2 id="step-6-—-verify-operation">Step 6 — Verify Operation</h2>

<p>There are a few ways to verify that traffic is being routed through the VPN.</p>

<h3 id="web-browser">Web Browser</h3>

<p>Visit a website to determine the external IP address. The external IP address should be that of the OpenVPN server.</p>

<p>Try <a href="http://goo.gl/OWYTAK">Google "what is my ip"</a> or <a href="https://icanhazip.com">icanhazip.com</a>.</p>

<h3 id="command-line">Command Line</h3>

<p>From the command line, <code>wget</code> or <code>curl</code> come in handy. Example with <code>curl</code>:</p>
<pre class="code-pre "><code langs="">curl icanhazip.com
</code></pre>
<p>Example with <code>wget</code>:</p>
<pre class="code-pre "><code langs="">wget -qO - icanhazip.com
</code></pre>
<p>The expected response should be the IP address of the OpenVPN server.</p>

<p>Another option is to do a special DNS lookup to a specially configured DNS server just for this purpose using <code>host</code> or <code>dig</code>. Example using <code>host</code>:</p>
<pre class="code-pre "><code langs="">host -t A myip.opendns.com resolver1.opendns.com
</code></pre>
<p>Example with <code>dig</code>:</p>
<pre class="code-pre "><code langs="">dig +short myip.opendns.com @resolver1.opendns.com
</code></pre>
<p>The expected response should be the IP address of the OpenVPN server.</p>

<h3 id="extra-things-to-check">Extra Things to Check</h3>

<p>Review your network interface configuration. On Unix-based operating systems, this is as simple as running <code>ifconfig</code> in a terminal, and looking for OpenVPN's <code>tunX</code> interface when it's connected.</p>

<p>Review logs. On Unix systems check <code>/var/log</code> on old distributions or <code>journalctl</code> on systemd distributions.</p>

<h2 id="conclusion">Conclusion</h2>

<p>The Docker image built to run this is open source and capable of much more than described here.</p>

<p>The <a href="https://github.com/kylemanna/docker-openvpn">docker-openvpn source repository</a> is available for review of the code as well as forking for modifications. Pull requests for general features or bug fixes are welcome.</p>

<p>Advanced topics such as <strong>backup</strong> and <strong>static client IPs</strong> are discussed under the <a href="https://github.com/kylemanna/docker-openvpn/tree/master/docs">docker-openvpn/docs</a> folder.</p>

<p>Report bugs to the <a href="https://github.com/kylemanna/docker-openvpn/issues">docker-openvpn issue tracker</a>.</p>

    