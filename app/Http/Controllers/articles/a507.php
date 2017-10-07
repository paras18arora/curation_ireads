<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>The FreeBSD operating system utilizes the <code>GENERIC</code> kernel by default. This is a default configuration used to support a large variety of hardware out of the box. However, there are many different reasons for compiling a custom kernel, which include security, enhanced functionality, or better performance.</p>

<p>FreeBSD utilizes two branches of code for its operating system: stable and current. Stable is the current code release that is that is production ready. Current is the latest code release from the development team and has some of the latest bleeding edge features but is more prone to bugs and system instability. This guide will utilize the stable branch.</p>

<p>In this tutorial, we will recompile a FreeBSD kernel with a custom configuration.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, all you will need is:</p>

<ul>
<li>One FreeBSD 10.1 Droplet.</li>
</ul>

<p>If you're new to FreeBSD, you can check out the <a href="https://indiareads/community/tutorial_series/getting-started-with-freebsd">Getting Started with FreeBSD</a> series of tutorials.</p>

<h2 id="step-1-—-obtaining-the-source-code">Step 1 — Obtaining the Source Code</h2>

<p>In this step, we will pull the OS source code.</p>

<p>FreeBSD, like many other flavors of UNIX, provides the source code for its operating system for public download and modification. In order to recompile the kernel, first you will need to pull this source code from FreeBSD's version control system.</p>

<p>The FreeBSD foundation utilizes Subversion for its code repositories, so let's first install Subversion's binary port.</p>
<pre class="code-pre "><code langs="">sudo pkg install subversion  
</code></pre>
<p>The default shell for FreeBSD is tcsh, which utilizes an internal hash table for commands in <code>$PATH</code>. After subversion installs, you should rehash the directory tables.</p>
<pre class="code-pre "><code langs="">rehash
</code></pre>
<p>Finally, check out a copy of the latest stable branch to the <code>/usr/src</code> directory.</p>
<pre class="code-pre "><code langs="">sudo svn co https://svn0.us-east.FreeBSD.org/base/stable/10 /usr/src
</code></pre>
<p>You may be prompted to accept a server certificate. Enter <strong>p</strong> to accept it after checking that the fingerprint matches the one toward the bottom of <a href="https://www.freebsd.org/doc/en/books/handbook/svn.html">this page</a>.</p>

<h2 id="step-2-—-creating-your-custom-configuration">Step 2 — Creating Your Custom Configuration</h2>

<p>In this step, we will customize our new kernel configuration.</p>

<p>The standard naming convention for kernel configuration files is the name of the kernel in all caps. This tutorial's configuration will be called <code>EXAMPLE</code>. Kernel configuration files live inside the <code>/usr/src/sys/<span class="highlight">architecture</span>/conf</code> directory; the architecture used at IndiaReads is AMD64. </p>

<p>Change to the configuration directory.</p>
<pre class="code-pre "><code langs="">cd /usr/src/sys/amd64/conf
</code></pre>
<p>Create and open the <code>EXAMPLE</code> file for editing using ee or your favorite text editor.</p>
<pre class="code-pre "><code langs="">sudo ee <span class="highlight">EXAMPLE</span>
</code></pre>
<p>You can find the example configuration located <a href="https://raw.githubusercontent.com/do-community/freebsd-do-kernel/master/EXAMPLE">here</a>. Copy and paste the contents into <code>EXAMPLE</code>, then save and close the file.</p>

<p>This example kernel configuration is for a minimal kernel build tailored for a IndiaReads Droplet. Specifically, the <code>GENERIC</code> kernel configuration has support enabled for a lot of different hardware; <code>EXAMPLE</code> has all legacy and unneeded devices removed, leaving only the required device drivers needed to run a Droplet. There is also support enabled for the packet filter firewall (pf), traffic shaping (altq), file system encryption (geom_eli), and IP security (IPsec).</p>

<p>However, you can read more about the configuration options in the <a href="http://docs.freebsd.org/doc/3.4-RELEASE/usr/share/doc/handbook/kernelconfig-config.html">FreeBSD documentation</a> and experiment on your own!</p>

<h2 id="step-3-—-building-and-installing-your-new-kernel">Step 3 — Building and Installing Your New Kernel</h2>

<p>In this step, we will begin the kernel recompilation.</p>

<p>Change back to the <code>/usr/src</code> directory and issue a <code>make buildkernel</code> utilizing your new configuration file.</p>
<pre class="code-pre "><code langs="">cd /usr/src
sudo make buildkernel KERNCONF=<span class="highlight">EXAMPLE</span>
</code></pre>
<p>This can take some time depending on the amount of resources you utilize for your Droplet. The average time on a 1 GB Droplet is about 90 minutes.</p>

<p>Once your kernel recompilation has finished, it is time to begin the install.</p>
<pre class="code-pre "><code langs="">sudo make installkernel KERNCONF=<span class="highlight">EXAMPLE</span>
</code></pre>
<p>When that completes, reboot your system.</p>
<pre class="code-pre "><code langs="">sudo shutdown -r now
</code></pre>
<p>Your server should now begin to shut down its currently running services, sync its disks, and reboot into your new kernel. You can log in to your Droplet's console to watch the boot process.</p>

<p>Once your server reboots, log back in. You can check that your new kernel config is being used with the following command:</p>
<pre class="code-pre "><code langs="">sysctl kern.conftxt | grep ident
</code></pre>
<p>The output should be:</p>
<pre class="code-pre "><code langs="">ident    <span class="highlight">EXAMPLE</span>
</code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>Congratulations! You have successfully reconfigured and recompiled your kernel.</p>

    