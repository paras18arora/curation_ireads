<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/java_tw.jpg?1461609356/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Java and the JVM (Java's virtual machine) are widely used and required for many kinds of software. This article will guide you through the process of installing and managing different versions of Java using <code>apt-get</code>.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need:</p>

<ul>
<li><p>One Ubuntu 16.04 Droplet.</p></li>
<li><p>A sudo non-root user, which you can set up by following <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">the Ubuntu 16.04 initial server setup guide</a>.</p></li>
</ul>

<h2 id="installing-the-default-jre-jdk">Installing the Default JRE/JDK</h2>

<p>The easiest option for installing Java is using the version packaged with Ubuntu. Specifically, this will install OpenJDK 8, the latest and recommended version.</p>

<p>First, update the package index.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Next, install Java. Specifically, this command will install the Java Runtime Environment (JRE).</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install default-jre
</li></ul></code></pre>
<p>There is another default Java installation called the JDK (Java Development Kit). The JDK is usually only needed if you are going to compile Java programs or if the software that will use Java specifically requires it.</p>

<p>The JDK does contain the JRE, so there are no disadvantages if you install the JDK instead of the JRE, except for the larger file size.</p>

<p>You can install the JDK with the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install default-jdk
</li></ul></code></pre>
<h2 id="installing-the-oracle-jdk">Installing the Oracle JDK</h2>

<p>If you want to install the Oracle JDK, which is the official version distributed by Oracle, you will need to follow a few more steps. If you need Java 6 or 7, which are not available in the default Ubuntu 16.04 repositories (not recommended), this installation method is also available.</p>

<p>First, add Oracle's PPA, then update your package repository.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo add-apt-repository ppa:webupd8team/java
</li><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Then, depending on the version you want to install, execute one of the following commands:</p>

<h3 id="oracle-jdk-6-or-7">Oracle JDK 6 or 7</h3>

<p>These are very old versions of Java which reached end of life in February 2013 and April 2015 respectively. It's not recommended to use them, but they might still be required for some programs.</p>

<p>To install JDK 6, use the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install oracle-java6-installer
</li></ul></code></pre>
<p>To install JDK 7, use the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install oracle-java7-installer
</li></ul></code></pre>
<h3 id="oracle-jdk-8">Oracle JDK 8</h3>

<p>This is the latest stable version of Java at time of writing, and the recommended version to install. You can do so using the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install oracle-java8-installer
</li></ul></code></pre>
<h3 id="oracle-jdk-9">Oracle JDK 9</h3>

<p>This is a developer preview and the general release is scheduled for March 2017. It's not recommended that you use this version because there may still be security issues and bugs. There is more information about Java 9 on the <a href="https://jdk9.java.net/">official JDK 9 website</a>.</p>

<p>To install JDK 9, use the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install oracle-java9-installer
</li></ul></code></pre>
<h2 id="managing-java">Managing Java</h2>

<p>There can be multiple Java installations on one server. You can configure which version is the default for use in the command line by using <code>update-alternatives</code>, which manages which symbolic links are used for different commands.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo update-alternatives --config java
</li></ul></code></pre>
<p>The output will look something like the following. In this case, this is what the output will look like with all Java versions mentioned above installed.</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">There are 5 choices for the alternative java (providing /usr/bin/java).

  Selection    Path                                            Priority   Status
------------------------------------------------------------
* 0            /usr/lib/jvm/java-8-openjdk-amd64/jre/bin/java   1081      auto mode
  1            /usr/lib/jvm/java-6-oracle/jre/bin/java          1         manual mode
  2            /usr/lib/jvm/java-7-oracle/jre/bin/java          2         manual mode
  3            /usr/lib/jvm/java-8-openjdk-amd64/jre/bin/java   1081      manual mode
  4            /usr/lib/jvm/java-8-oracle/jre/bin/java          3         manual mode
  5            /usr/lib/jvm/java-9-oracle/bin/java              4         manual mode

Press <enter> to keep the current choice[*], or type selection number:
</code></pre>
<p>You can now choose the number to use as a default. This can also be done for other Java commands, such as the compiler (<code>javac</code>), the documentation generator (<code>javadoc</code>), the JAR signing tool (<code>jarsigner</code>), and more. You can use the following command, filling in the command you want to customize.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo update-alternatives --config <span class="highlight">command</span>
</li></ul></code></pre>
<h2 id="setting-the-java_home-environment-variable">Setting the JAVA_HOME Environment Variable</h2>

<p>Many programs, such as Java servers, use the <code>JAVA_HOME</code> environment variable to determine the Java installation location. To set this environment variable, we will first need to find out where Java is installed. You can do this by executing the same command as in the previous section:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo update-alternatives --config java
</li></ul></code></pre>
<p>Copy the path from your preferred installation and then open <code>/etc/environment</code> using <code>nano</code> or your favorite text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/environment
</li></ul></code></pre>
<p>At the end of this file, add the following line, making sure to replace the highlighted path with your own copied path.</p>
<div class="code-label " title="/etc/environment">/etc/environment</div><pre class="code-pre "><code langs="">JAVA_HOME="<span class="highlight">/usr/lib/jvm/java-8-oracle</span>"
</code></pre>
<p>Save and exit the file, and reload it.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">source /etc/environment
</li></ul></code></pre>
<p>You can now test whether the environment variable has been set by executing the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo $JAVA_HOME
</li></ul></code></pre>
<p>This will return the path you just set.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You have now installed Java and know how to manage different versions of it. You can now install software which runs on Java, such as Tomcat, Jetty, Glassfish, Cassandra, or Jenkins.</p>

    