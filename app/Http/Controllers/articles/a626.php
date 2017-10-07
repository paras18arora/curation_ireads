<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/solr-twitter.png?1429195623/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Solr is a search engine platform based on Apache Lucene. It is written in Java and uses the Lucene library to implement indexing. It can be accessed using a variety of REST APIs (e.g. XML and JSON). This is the feature list from <a href="https://lucene.apache.org/solr/">their website</a>:</p>

<ul>
<li>Advanced Full-Text Search Capabilities</li>
<li>Optimized for High Volume Web Traffic</li>
<li>Standards Based Open Interfaces - XML, JSON and HTTP</li>
<li>Comprehensive HTML Administration Interfaces</li>
<li>Server statistics exposed over JMX for monitoring</li>
<li>Linearly scalable, auto index replication, auto failover and recovery</li>
<li>Near Real-time indexing</li>
<li>Flexible and Adaptable with XML configuration</li>
<li>Extensible Plugin Architecture</li>
</ul>

<p>In this article, I will show you how to install Solr on Ubuntu using two different methods. The first one will be the simple method and the second the more advanced method. I recommend the second method because it installs a newer version of Solr on all Ubuntu versions, even in the most recent version 14.04 at time of writing.</p>

<h2 id="installing-solr-using-apt-get-easy-way">Installing Solr using apt-get (easy way)</h2>

<p>If you want to install Solr the easy way, you should use this section of the article. Solr doesn't work alone; it needs a Java servlet container such as Tomcat or Jetty. In this article, we'll use Jetty, although Tomcat is just as easy. First, we should install the Java JDK. If you want to install a custom version, please see <a href="https://digitalocean.com/community/articles/how-to-install-java-on-ubuntu-with-apt-get">this article</a>. If you want a simple installation, execute the following commands:</p>
<pre class="code-pre "><code langs="">sudo apt-get -y install openjdk-7-jdk
mkdir /usr/java
ln -s /usr/lib/jvm/java-7-openjdk-amd64 /usr/java/default
</code></pre>
<p>Ubuntu provides 3 Solr packages by default: <code>solr-common</code>, the package that contains the actual Solr code; <code>solr-tomcat</code>, Solr integrated with Tomcat; and <code>solr-jetty</code>, which is just like <code>solr-tomcat</code> but with the Jetty web server. In this article, we will install <code>solr-tomcat</code>, so execute the following command:</p>
<pre class="code-pre "><code langs="">sudo apt-get -y install solr-tomcat
</code></pre>
<p>Your Solr instance should now be available at <code>http://YOUR_IP:8080/solr</code>. Skip the next section on installing manually if you want to configure Solr.</p>

<h2 id="installing-solr-manually">Installing Solr Manually</h2>

<p>To install Solr manually, you will need a little more time. First, we should install the Java JDK. If you want to install a custom version, please see <a href="https://digitalocean.com/community/articles/how-to-install-java-on-ubuntu-with-apt-get">this article</a>. For this section, we will be using Jetty instead of Tomcat. If you want a simple installation, execute the following command:</p>
<pre class="code-pre "><code langs="">sudo apt-get -y install openjdk-7-jdk
mkdir /usr/java
ln -s /usr/lib/jvm/java-7-openjdk-amd64 /usr/java/default
</code></pre>
<p>We can now start the real installation of Solr. First, download all files and uncompress them:</p>
<pre class="code-pre "><code langs="">cd /opt
wget http://archive.apache.org/dist/lucene/solr/4.7.2/solr-4.7.2.tgz
tar -xvf solr-4.7.2.tgz
cp -R solr-4.7.2/example /opt/solr
cd /opt/solr
java -jar start.jar
</code></pre>
<p>Check if it works by visiting <code>http://YOUR_IP:8983/solr</code>. When it works, go back into your SSH session and close the window with Ctrl+C. Then open the <code>/etc/default/jetty</code> file (<code>nano /etc/default/jetty</code>) and paste this into it:</p>
<pre class="code-pre "><code langs="">NO_START=0 # Start on boot
JAVA_OPTIONS="-Dsolr.solr.home=/opt/solr/solr $JAVA_OPTIONS"
JAVA_HOME=/usr/java/default
JETTY_HOME=/opt/solr
JETTY_USER=solr
JETTY_LOGS=/opt/solr/logs
</code></pre>
<p>Save it and open the file <code>/opt/solr/etc/jetty-logging.xml</code> (<code>nano /opt/solr/etc/jetty-logging.xml</code>) and paste this into it:</p>

<pre>
<?xml version="1.0"?>
  <!DOCTYPE Configure PUBLIC "-//Mort Bay Consulting//DTD Configure//EN" "http://jetty.mortbay.org/configure.dtd">
  <!-- =============================================================== -->
  <!-- Configure stderr and stdout to a Jetty rollover log file -->
  <!-- this configuration file should be used in combination with -->
  <!-- other configuration files.  e.g. -->
  <!--    java -jar start.jar etc/jetty-logging.xml etc/jetty.xml -->
  <!-- =============================================================== -->
  <Configure id="Server" class="org.mortbay.jetty.Server">

      <New id="ServerLog" class="java.io.PrintStream">
        <Arg>
          <New class="org.mortbay.util.RolloverFileOutputStream">
            <Arg><SystemProperty name="jetty.logs" default="."/>/yyyy_mm_dd.stderrout.log</Arg>
            <Arg type="boolean">false</Arg>
            <Arg type="int">90</Arg>
            <Arg><Call class="java.util.TimeZone" name="getTimeZone"><Arg>GMT</Arg></Call></Arg>
            <Get id="ServerLogName" name="datedFilename"/>
          </New>
        </Arg>
      </New>

      <Call class="org.mortbay.log.Log" name="info"><Arg>Redirecting stderr/stdout to <Ref id="ServerLogName"/></Arg></Call>
      <Call class="java.lang.System" name="setErr"><Arg><Ref id="ServerLog"/></Arg></Call>
      <Call class="java.lang.System" name="setOut"><Arg><Ref id="ServerLog"/></Arg></Call></Configure>
</pre>
    

<p>Then, create the Solr user and grant it permissions:</p>
<pre class="code-pre "><code langs="">sudo useradd -d /opt/solr -s /sbin/false solr
sudo chown solr:solr -R /opt/solr
</code></pre>
<p>After that, download the start file and set it to automatically start up if it hasn't been done already:</p>
<pre class="code-pre "><code langs="">sudo wget -O /etc/init.d/jetty http://dev.eclipse.org/svnroot/rt/org.eclipse.jetty/jetty/trunk/jetty-distribution/src/main/resources/bin/jetty.sh
sudo chmod a+x /etc/init.d/jetty
sudo update-rc.d jetty defaults
</code></pre>
<p>Finally start Jetty/Solr:</p>
<pre class="code-pre "><code langs="">sudo /etc/init.d/jetty start
</code></pre>
<p>You can now access your installation just as before at <code>http://YOUR_IP:8983/solr</code>.</p>

<h2 id="configuring-a-schema-xml-for-solr">Configuring a schema.xml for Solr</h2>

<p>First, rename the <code>/opt/solr/solr/collection1</code> to an understandable name like apples (use whatever name you'd like). (<strong>This can be skipped if you installed it using <code>apt-get</code>.</strong> In that case, you can execute the following command instead: <code>cd /usr/share/solr</code>):</p>
<pre class="code-pre "><code langs="">cd /opt/solr/solr
mv collection1 apples
cd apples
</code></pre>
<p>Also, if you installed Solr manually, open the file core.properties (<code>nano core.properties</code>) and change the name to the same name.</p>

<p>Then, remove the <code>data</code> directory and change the schema.xml:</p>
<pre class="code-pre "><code langs="">rm -R data
nano conf/schema.xml
</code></pre>
<p>Paste your own schema.xml in here. There is a very advanced schema.xml in the <a href="http://svn.apache.org/viewvc/lucene/dev/trunk/solr/example/solr/collection1/conf/schema.xml?view=markup">Solr Repository</a>. You can probably find a lot more of them on the internet, but I won't go into depth about that. Restart Jetty/Tomcat:</p>

<p>For the simple installation.</p>
<pre class="code-pre "><code langs="">sudo service tomcat6 restart
</code></pre>
<p>For the advanced installation.</p>
<pre class="code-pre "><code langs="">sudo /etc/init.d/jetty restart
</code></pre>
<p>When you now visit your Solr instance, you should see the Dashboard with the collection somewhere.</p>

<h3 id="conclusion">Conclusion</h3>

<p>You have now successfully installed Solr and can start using it for your own site! If you don't know how to make a schema.xml, find a tutorial on how to do that. Then, find a library for your programming language that connects with Solr.</p>

<div class="author">Submitted by: <a href="http://www.benstechtips.net/go/doarticle">Koen Vlaswinkel</a></div>

    