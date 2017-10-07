<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <p>The OpenSSH project today reported a client side issue affecting OpenSSH versions 5.4 - 7.1. This issue could allow an SSH client to leak key information, potentially exposing users to man-in-the-middle attacks.</p>

<h2 id="what-does-this-mean">What does this mean?</h2>

<p>A key exchange is initiated when an SSH client connects to a server.  A new "roaming" feature included in the OpenSSH client can be exploited and a malicious server could use this issue to leak client memory to the server, including private client user keys.</p>

<h2 id="who-is-affected">Who is affected?</h2>

<p>This issue affects the OpenSSH client (not server) on most modern operating systems including Linux, FreeBSD and Mac OSX.  This issue may also affect users running OpenSSH for Windows but does not affect users using PuTTY on Windows.</p>

<p>That means you don't have to update OpenSSH on your Droplet (the server side), but you should update the OpenSSH client on your local computer. If you want to cover all your bases, you could generate new key pairs and upload the new public keys to your servers (see the second-to-last section for details).</p>

<h2 id="how-to-fix-the-isssue">How to fix the isssue</h2>

<p>While patches and updates are being rolled out for affected distributions, the feature causing this security issue can be disabled manually in order to resolve the issue.  On OS X, Linux and BSD variants this can be done by adding a line to your SSH configuration.</p>

<h3 id="on-linux-and-freebsd">On Linux and FreeBSD</h3>

<p>Run the following command to add the new line to your configuration:</p>
<pre class="code-pre "><code langs="">echo 'UseRoaming no' | sudo tee -a /etc/ssh/ssh_config
</code></pre>
<h3 id="on-mac-osx">On Mac OSX</h3>

<p>Run the following command to add the new line to your configuration:</p>
<pre class="code-pre "><code langs="">echo "UseRoaming no" >> ~/.ssh/config
</code></pre>
<h2 id="close-and-reopen-sessions">Close and Reopen Sessions</h2>

<p>Once you have done this you should close any open SSH sessions in order for the change to be effective.</p>

<h2 id="for-the-security-conscious-regenerate-all-your-key-pairs">For the Security-Conscious: Regenerate All Your Key Pairs</h2>

<p>If you think someone gained access to your private keys using this vulnerability, or if you want to cover your bases "just in case," you should regenerate all of your key pairs and upload the new public keys to your servers.</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-ssh-keys--2">How To Set Up SSH Keys</a></li>
</ul>

<h2 id="learn-more">Learn More</h2>

<p><a href="http://undeadly.org/cgi?action=article&sid=20160114142733">OpenSSH: client bug CVE-0216-0777 and CVE-0216-0778 </a><br />
<a href="http://www.ubuntu.com/usn/usn-2869-1/">Ubuntu - USN-2869-1: OpenSSH vulnerabilities</a></p>

    