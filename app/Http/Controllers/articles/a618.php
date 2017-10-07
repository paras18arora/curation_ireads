<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/solr-twitter.png?1436828221/> <br> 
      <h3 id="written-in-collaboration-with-solr">Written in collaboration with <a href="http://lucene.apache.org/solr/">Solr</a></h3>

<h3 id="introduction">Introduction</h3>

<p>Solr is a search engine platform based on Apache Lucene. It is written in Java and uses the Lucene library to implement indexing. It can be accessed using a variety of REST APIs, including XML and JSON. This is the feature list from their website:</p>

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

<p>In this article, we will install Solr using its binary distribution.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need:</p>

<ul>
<li><p>One 1 GB Ubuntu 14.04 Droplet at minimum, but the amount of RAM needed <a href="https://wiki.apache.org/solr/SolrPerformanceProblems">depends highly</a> on your specific situation.</p></li>
<li><p>A <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">sudo non-root user</a>.</p></li>
</ul>

<h2 id="step-1-—-installing-java">Step 1 — Installing Java</h2>

<p>Solr requires Java, so in this step, we will install it.</p>

<p>The complete Java installation process is thoroughly described in <a href="https://digitalocean.com/community/articles/how-to-install-java-on-ubuntu-with-apt-get">this article</a>, but we'll use a slightly different process.</p>

<p>First, use apt-get to install <code>python-software-properties</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install python-software-properties
</li></ul></code></pre>
<p>Instead of using the <code>default-jdk</code> or <code>default-jre</code> packages, we'll install the latest version of Java 8. To do this, add the unofficial Java installer repository:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo add-apt-repository ppa:webupd8team/java
</li></ul></code></pre>
<p>You will need to press <code>ENTER</code> to accept adding the repository to your index.</p>

<p>Then, update the source list:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Last, install Java 8 using apt-get. You will need to agree to the Oracle Binary Code License Agreement for the Java SE Platform Products and JavaFX.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install oracle-java8-installer
</li></ul></code></pre>
<h2 id="step-2-—-installing-solr">Step 2 — Installing Solr</h2>

<p>In this section, we will install Solr 5.2.1. We will begin by downloading the Solr distribution.</p>

<p>First, find a suitable mirror on <a href="http://www.apache.org/dyn/closer.cgi/lucene/solr/5.2.1">this page</a>. Then, copy the link of <code>solr-5.2.1.tgz</code> from the mirror. For example, we'll use <code>http://apache.mirror1.spango.com/lucene/solr/5.2.1/</code>.</p>

<p>Then, download the file in your home directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">wget <span class="highlight">http://apache.mirror1.spango.com/lucene/solr/5.2.1/solr-5.2.1.tgz</span>
</li></ul></code></pre>
<p>Next, extract the service installation file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">tar xzf solr-5.2.1.tgz solr-5.2.1/bin/install_solr_service.sh --strip-components=2
</li></ul></code></pre>
<p>And install Solr as a service using the script:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo bash ./install_solr_service.sh solr-5.2.1.tgz
</li></ul></code></pre>
<p>Finally, check if the server is running:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service solr status
</li></ul></code></pre>
<p>You should see an output that begins with this:</p>
<div class="code-label " title="Solr status output">Solr status output</div><pre class="code-pre "><code langs="">Found 1 Solr nodes: 

Solr process 2750 running on port 8983

. . .
</code></pre>
<h2 id="step-3-—-creating-a-collection">Step 3 — Creating a Collection</h2>

<p>In this section, we will create a simple Solr collection.</p>

<p>Solr can have multiple collections, but for this example, we will only use one. To create a new collection, use the following command. We run it as the Solr user in this case to avoid any permissions errors.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo su - solr -c "/opt/solr/bin/solr create -c gettingstarted -n data_driven_schema_configs"
</li></ul></code></pre>
<p>In this command, <code>gettingstarted</code> is the name of the collection and <code>-n</code> specifies the configset. There are 3 config sets supplied by Solr by default; in this case, we have used one that is schemaless, which means that any field can be supplied, with any name, and the type will be guessed.</p>

<p>You have now added the collection and can start adding data. The default schema has only one required field: <code>id</code>. It has no other default fields, only dynamic fields. If you want to have a look at the schema, where everything is explained clearly, have a look at the file <code>/opt/solr/server/solr/gettingstarted/conf/schema.xml</code>.</p>

<h2 id="step-4-—-adding-and-querying-documents">Step 4 — Adding and Querying Documents</h2>

<p>In this section, we will explore the Solr web interface and add some documents to our collection.</p>

<p>When you visit <code>http://<span class="highlight">your_server_ip</span>:8983/solr</code> using your web browser, the Solr web interface should appear:</p>

<p><img src="https://assets.digitalocean.com/articles/solr/o6dOUlH.png" alt="Solr Web Interface" /></p>

<p>The web interface contains a lot of useful information which can be used to debug any problems you encounter during use.</p>

<p>Collections are divided up into cores, which is why there are a lot of references to cores in the web interface. Right now, the collection <code>gettingstarted</code> only contains one core, named <code>gettingstarted</code>. At the left-hand side, the <strong>Core Selector</strong> pull down menu is visible, in which you'll be able to select <code>gettingstarted</code> to view more information.</p>

<p>After you've selected the <code>gettingstarted</code> core, select <strong>Documents</strong>. Documents store the real data that will be searchable by Solr. Because we have used a schemaless configuration, we can use any field. Let'sl add a single document with the following example <em>JSON</em> representation by copying the below into the <strong>Document(s)</strong> field:</p>
<pre class="code-pre "><code class="code-highlight language-json">{
    "number": 1,
    "president": "George Washington",
    "birth_year": 1732,
    "death_year": 1799,
    "took_office": "1789-04-30",
    "left_office": "1797-03-04",
    "party": "No Party"
}
</code></pre>
<p>Click <strong>Submit document</strong> to add the document to the index. After a few moments, you will see the following:</p>
<div class="code-label " title="Output after adding Document">Output after adding Document</div><pre class="code-pre "><code class="code-highlight language-json">Status: success
Response:
{
  "responseHeader": {
    "status": 0,
    "QTime": 509
  }
}
</code></pre>
<p>You can add more documents, with a similar or a completely different structure, but you can also continue with just one document.</p>

<p>Now, select <strong>Query</strong> on the left to query the document we just added. With the default values in this screen, after clicking on <strong>Execute Query</strong>, you will see 10 documents at most, depending on how many you added:</p>
<div class="code-label " title="Query output">Query output</div><pre class="code-pre "><code class="code-highlight language-json">{
  "responseHeader": {
    "status": 0,
    "QTime": 58,
    "params": {
      "q": "*:*",
      "indent": "true",
      "wt": "json",
      "_": "1436827539345"
    }
  },
  "response": {
    "numFound": 1,
    "start": 0,
    "docs": [
      {
        "number": [
          1
        ],
        "president": [
          "George Washington"
        ],
        "birth_year": [
          1732
        ],
        "death_year": [
          1799
        ],
        "took_office": [
          "1789-04-30T00:00:00Z"
        ],
        "left_office": [
          "1797-03-04T00:00:00Z"
        ],
        "party": [
          "No Party"
        ],
        "id": "1ce12ed2-add9-4c65-aeb4-a3c6efb1c5d1",
        "_version_": 1506622425947701200
      }
    ]
  }
}
</code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>There are many more options available, but you have now successfully installed Solr and can start using it for your own site.</p>

    