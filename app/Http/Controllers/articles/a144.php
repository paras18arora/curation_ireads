<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h1 id="how-to-set-up-the-unbound-caching-dns-resolver-on-freebsd-10-1-or-10-2">How To Set Up the Unbound Caching DNS Resolver on FreeBSD 10.1 or 10.2</h1>

<h3 id="introduction">Introduction</h3>

<p>The system of domain name servers (DNS) is a global hierarchy of databases dedicated to the simple but essential task of looking up host names like <code>indiareads</code> and turning them into one or more IP addresses. Whenever an email is sent or a connection to a host is initiated by its name, the DNS system is used. There is a good <a href="https://indiareads/community/tutorials/an-introduction-to-dns-terminology-components-and-concepts">introduction to the DNS system</a> available from the IndiaReads community.</p>

<p>Such an essential and fundamental component of Internet infrastructure gets a lot of use. It is not uncommon for a busy system to make hundreds of name lookups per second or more. If services running on your server perform much work at all behind the scenes then it is likely that security and performance will benefit from verifying and caching within your own systems the name lookups that your service performs to conduct its operations.</p>

<p>In this tutorial, you will learn how to set up a FreeBSD server to remember all DNS lookups in a system-wide cache. Information will automatically expire from this cache, honoring each looked-up domain's individual policy for rechecking.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>In order to follow this tutorial, you will need:</p>

<ul>
<li>One FreeBSD 10.1 Droplet</li>
</ul>

<h2 id="step-1-—-enabling-unbound">Step 1 — Enabling Unbound</h2>

<p>FreeBSD 10.1 includes the verifying caching resolver Unbound (version 1.4.22) as part of the base system; FreeBSD 10.2 includes version 1.5.3. Both are considered secure and ready to be put into production use.</p>

<p>Once you are logged into your server via SSH, enabling FreeBSD's included resolver is as simple as issuing the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo sysrc local_unbound_enable=YES
</li></ul></code></pre>
<p>Your Droplet is now configured to start Unbound at the next system reboot.</p>

<h2 id="step-2-—-starting-unbound">Step 2 — Starting Unbound</h2>

<p>You can fire up the resolver immediately without performing a full system restart.</p>

<p>To start the resolver:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service local_unbound start
</li></ul></code></pre>
<p>If Unbound starts successfully you should see output similar to the following:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">Performing initial setup.
Extracting forwarders from /etc/resolv.conf.
/var/unbound/forward.conf created
/var/unbound/lan-zones.conf created
/var/unbound/unbound.conf created
/etc/resolvconf.conf created
original /etc/resolv.conf saved as /etc/resolv.conf.20150812.184225
Starting local_unbound.
</code></pre>
<p>You are now running the Unbound verifying caching name resolver but not all of your currently running software is guaranteed to notice and pick up the modification.</p>

<h2 id="step-3-—-preserving-this-setup-through-droplet-restoration">Step 3 — Preserving This Setup Through Droplet Restoration</h2>

<p>Actions like restoring a backup image or using a snapshot image as the basis for a new Droplet would normally clobber the configuration we've done so far. This is due to a minor bug in the OpenStack driver for FreeBSD. Luckily this bug has been fixed in the upcoming release. We will individually apply this particular patch to the current release now in order to ensure Unbound's proper operation with IndiaReads's backup and snapshotting facilities.</p>

<p>Download the patch from the official repository for BSD-CloudInit, (the FreeBSD OpenStack driver):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">fetch https://github.com/pellaeon/bsd-cloudinit/commit/a7ee246c23.diff
</li></ul></code></pre>
<p>Apply the patch to the proper file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo patch -N -F3 /usr/local/bsd-cloudinit/cloudbaseinit/osutils/freebsd.py < a7ee246c23.diff
</li></ul></code></pre>
<p>You should see output that ends with the following, indicating the patch applied successfully:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">. . .
Patching file /usr/local/bsd-cloudinit/cloudbaseinit/osutils/freebsd.py using Plan A...
Hunk #1 succeeded at 4 with fuzz 2 (offset 1 line).
Hunk #2 succeeded at 83 with fuzz 3 (offset 4 lines).
done
</code></pre>
<p>You no longer need the patch file and may remove it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rm a7ee246c23.diff
</li></ul></code></pre>
<p>Your system is now configured to use Unbound through system backups and restorations, or after being cloned to an entirely new server.</p>

<h2 id="step-4-—-restarting-affected-services">Step 4 — Restarting Affected Services</h2>

<p>The simplest way to ensure all of your software is using the new resolver is to restart the Droplet entirely.</p>

<p>You can delay doing this until it least impacts the service your Droplet provides. The running software will use either the old resolver or the new one, rather than malfunction; any software that is able to pick up the transition in the meantime will do so gracefully. and there should be no ill effects from both being potentially in use side by side temporarily.</p>

<p>When you are ready, restart your Droplet:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo shutdown -r now
</li></ul></code></pre>
<p>That's all there is to it!</p>

<h2 id="conclusion">Conclusion</h2>

<p>In this tutorial you learned how to cache host name and domain name lookups on your system and why you might want to do so. You can learn more about FreeBSD's caching resolver at the <a href="https://www.unbound.net/">homepage for the Unbound project</a>.</p>

    