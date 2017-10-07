<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>Many people rightfully have concerns about their personal information and privacy being at the liberty of large companies.  While there are many different projects whose goals are to allow users to reclaim ownership of their data, there are still some areas of normal computing that have been difficult for users to break free from business-controlled products.</p>

<p>Search engines are one area that many privacy-conscious people complain about.  <strong>YaCy</strong> is a project meant to fix the problem of search engine providers using your data for purposes you did not intend.  YaCy is a peer-to-peer search engine, meaning that there is no centralized authority or server where your information is stored.  It works by connecting to a network of people also running YaCy instances and crawling the web to create a distributed index of sites.</p>

<p>In this guide, we will discuss how to get started with YaCy on an Ubuntu 12.04 VPS instance.  You can then use this to either contribute to the global network of search peers, or to create search indexes for your own pages and projects.</p>

<h2 id="download-the-components">Download the Components</h2>

<hr />

<p>YaCy has very few dependencies outside of the package.  Pretty much the only thing required on a modern Linux distribution should be the open Java development kit version 6.</p>

<p>We can get this from the default Ubuntu repositories by typing:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install openjdk-6-jdk
</code></pre>
<p>This will take awhile to download all of the necessary components.</p>

<p>Once that is complete, you can get the latest version of YaCy from the <a href="http://yacy.net/en/index.html">project's website</a>.  On the right-hand side, right-click or control click the link for GNU/Linux and select copy link location:</p>

<p><img src="https://assets.digitalocean.com/articles/yacy/download_link.png" alt="YaCy download link" /></p>

<p>Back on your VPS, change to your user's home directory and download the program using wget:</p>
<pre class="code-pre "><code langs="">cd ~
wget http://yacy.net/release/yacy_v1.68_20140209_9000.tar.gz
</code></pre>
<p>Once this has finished downloading, you can extract the files into its own directory:</p>
<pre class="code-pre "><code langs="">tar xzvf yacy*
</code></pre>
<p>We now have all of the components necessary to run our own search engine.</p>

<h2 id="start-the-yacy-search-engine">Start the YaCy Search Engine</h2>

<hr />

<p>We are almost ready to start utilizing the YaCy search engine.  Before we begin, we need to adjust one parameter.</p>

<p>Change into the YaCy directory.  From here, we will be able to make the necessary changes and then start the service:</p>
<pre class="code-pre "><code langs="">cd ~/yacy
</code></pre>
<p>We need to add an administrator username and password combination to a file so that we can explore the entire interface.  With your text editor, open the YaCy default initialization file:</p>
<pre class="code-pre "><code langs="">nano defaults/yacy.init
</code></pre>
<p>This is a very long configuration file that is well commented.  The parameter that we are looking for is called <code>adminAccount</code>.</p>

<p>Search for the <code>adminAccount</code> parameter.  You will see that it is unset currently:</p>
<pre class="code-pre "><code langs="">adminAccount=
adminAccountBase64MD5=
adminAccountUserName=admin
</code></pre>
<p>You need to set an admin account and password the following format:</p>

<pre>
adminAccount=admin:<span class="highlight">your_password</span>
adminAccountBase64MD5=
adminAccountUserName=admin
</pre>

<p>This will allow you to sign into the administrative sections of the web interface once you start the service.</p>

<p>Save and close the file.</p>

<p>When you are ready, start the service by typing:</p>
<pre class="code-pre "><code langs="">./startYACY.sh
</code></pre>
<p>This will start up the YaCy search engine.</p>

<h2 id="access-the-yacy-web-interface">Access the YaCy Web Interface</h2>

<hr />

<p>We now can access our search engine by navigating to this page with your web browser:</p>

<pre>
http://<span class="highlight">server_ip</span>:8090
</pre>

<p>You should be presented with the main YaCy search page:</p>

<p><img src="https://assets.digitalocean.com/articles/yacy/initial_page.png" alt="YaCy main page" /></p>

<p>As you can see, this is a pretty conventional search engine page.  You can search using the provided search bar without any additional configuration, if you wish.</p>

<p>We will be exploring the administration interface though, because that provides us with a lot more flexibility.  Click on the "Administration" link in the upper-left corner of the page:</p>

<p><img src="https://assets.digitalocean.com/articles/yacy/admin_link.png" alt="YaCy administration link" /></p>

<p>You will be taken to the basic configuration page:</p>

<p><img src="https://assets.digitalocean.com/articles/yacy/basic_config.png" alt="YaCy basic configuration" /></p>

<p>This will go over some common options that you may wish to set up right away.</p>

<p>First, it asks about the language preferences.  Change this if one of the other languages listed is more appropriate for your uses.</p>

<p>The second question decides how you want to use this YaCy instance.  The default configuration is to use your computer to join the global search network that crawls and indexes the web.  This is how peer-based searching operates to replace traditional search engines.</p>

<p>This will help allow you to join peers in providing a great search resource, and will allow you to leverage the work that others have already started.</p>

<p>If you don't want to use YaCy as a traditional search engine, you can instead choose to create a search portal for a single site by selecting the second option, or use it to index the local network by selecting the third option.</p>

<p>For now, we will select the first option.</p>

<p>The third setting is to create a unique peer name for this computer.  If you have multiple servers running YaCy, this becomes increasingly important if you want to peer with them exclusively.  Either way, select a unique name here.</p>

<p>For the fourth section, deselect "Configure your router for YaCy" since our search engine is installed on a VPS that is not behind a traditional router.</p>

<p>Click on "Set Configuration" when you are finished.</p>

<h2 id="crawl-sites-to-contribute-to-the-global-index">Crawl Sites to Contribute to the Global Index</h2>

<hr />

<p>You can now search using the indexes kept on your YaCy peers.  The search results will become more and more accurate the more people participate in the system.</p>

<p>We can contribute by crawling sites on our instance of YaCy so that other peers can find the pages we crawled.</p>

<p>To start this process, click on the "Crawler / Harvester" link on the left-hand side under the "Index Production" section.</p>

<p><img src="https://assets.digitalocean.com/articles/yacy/crawler_link.png" alt="YaCy crawler link" /></p>

<p>If you've attempted to search for something and did not get the results you were looking for, consider starting to index the pages on a site with your instance.  It will make your search more accurate for yourself and your peers.</p>

<p>Type in the URL that you want to index in the "Start URL" section:</p>

<p><img src="https://assets.digitalocean.com/articles/yacy/wiki_crawl.png" alt="YaCy wikipedia crawl" /></p>

<p>This should populate a list of links that YaCy found on the URL in question.  You can select either the original URL that you inputted, or choose to use the link list from the page you typed.</p>

<p>Furthermore, you can select whether you would like to index any links within the domain, or whether you would only like to index those that are a sub-path of the given URL.</p>

<p>The difference is that if you typed in <code>http://example.com/about</code>, the first option would index <code>http://example.com/sites</code>, while the second option would only index pages located below the inputted path (<code>http://example.com/about/me</code>).</p>

<p>You can limit the number of documents that your crawl will index.  Click "Start New Crawl" when you are finished to begin crawling the selected site.</p>

<p>Click on the "Creation Monitor" link on the left-hand side to see the progress of the indexing.  You should see something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/yacy/monitor.png" alt="YaCy creation monitor" /></p>

<p>Your server will crawl the URL specified at the rate of 2 requests per second until it has either run out of links chained together or reached the limit you set.</p>

<p>If you then search for a page related to your crawl, the results you indexed should contribute to the results.</p>

<h2 id="using-yacy-for-your-web-site">Using YaCy for your Web Site</h2>

<hr />

<p>One thing that YaCy can be used for is to provide search functionality for your website.  You can configure your site index to operate as a search engine restricted to your domain.</p>

<p>First, select "Admin Console" under the "Peer Control" section in the left-hand side.  In the admin console, go back to the "Basic Configuration" page.</p>

<p>This time, for the second question, choose "Search portal for your own web pages":</p>

<p><img src="https://assets.digitalocean.com/articles/yacy/select_second.png" alt="YaCy basic config again" /></p>

<p>Click "Set Configuration" on the bottom.</p>

<p>Next, you need to crawl your domain to generate the content that will be available through your search tool.  Again, click on the "Crawler / Harvester" link under the "Index Production" section on the left-hand side.</p>

<p>Enter your URL in the "Start URL" field.  Click "Start New Crawl" when you have selected your options:</p>

<p><img src="https://assets.digitalocean.com/articles/yacy/crawl_own.png" alt="YaCy crawl own domain" /></p>

<p>Next, click on the "Search Integration into External Sites" link under the "Search Design" section on the left-hand side.</p>

<p>There are two separate ways to configure YaCy searching.  We will be using the second one, called "Remote access through selected YaCy Peer".</p>

<p>You will see that YaCy automatically generates the code that you will need to embed within a web page on your site:</p>

<p><img src="https://assets.digitalocean.com/articles/yacy/auto_code.png" alt="YaCy autogenerate html" /></p>

<p>On your site, you need to create a page that has this code inside.  You may have to adjust the IP address and port to match the configuration of the server with YaCy installed.</p>

<p>For my site, I created a <code>search.html</code> page in the document root of my server.  I made a simple html page, and included the code generated by YaCy:</p>

<pre>
<html>
  <head>
    <title>Test</title>
  </head>
  <body>
    <h1>Search page</h1>
    <p>Here we go...</p>
<span class="highlight"><script src="http://111.111.111.111:8090/jquery/js/jquery-1.7.min.js" type="text/javascript" type="text/javascript"></script></span>
<span class="highlight"><script></span>
<span class="highlight">        $(document).ready(function() {</span>
<span class="highlight">                yconf = {</span>
<span class="highlight">                        url      : 'http://111.111.111.111:8090',</span>
<span class="highlight">                        title    : 'YaCy Search Widget',</span>
<span class="highlight">                        logo     : '/yacy/ui/img/yacy-logo.png',</span>
<span class="highlight">                        link     : 'http://www.yacy.net',</span>
<span class="highlight">                        global   : false,</span>
<span class="highlight">                        width    : 500,</span>
<span class="highlight">                        height   : 600,</span>
<span class="highlight">                        position : ['top',30],</span>
<span class="highlight">                        theme    : 'start'</span>
<span class="highlight">                };</span>
<span class="highlight">                $.getScript(yconf.url+'/portalsearch/yacy-portalsearch.js', function(){});</span>
<span class="highlight">        });</span>
<span class="highlight"></script></span>
<span class="highlight"><div id="yacylivesearch"></span>
<span class="highlight">        <form id="ysearch" method="get" accept-charset="UTF-8" action="http://111.111.111.111:8090/yacysearch.html"></span>
<span class="highlight">                Live Search <input name="query" id="yquery" class="fancy" type="text" size="15" maxlength="80" value=""/></span>
<span class="highlight">                <input type="hidden" name="verify" value="cacheonly" /></span>
<span class="highlight">                <input type="hidden" name="maximumRecords" value="20" /></span>
<span class="highlight">                <input type="hidden" name="resource" value="local" /></span>
<span class="highlight">                <input type="hidden" name="urlmaskfilter" value=".*" /></span>
<span class="highlight">                <input type="hidden" name="prefermaskfilter" value="" /></span>
<span class="highlight">                <input type="hidden" name="display" value="2" /></span>
<span class="highlight">                <input type="hidden" name="nav" value="all" /></span>
<span class="highlight">                <input type="submit" name="Enter" value="Search" /></span>
<span class="highlight">        </form></span>
<span class="highlight"></div></span>
  </body>
</html>
</pre>

<p>You can then save the file and access it from your web browser by going to:</p>

<pre>
http://<span class="highlight">your_web_domain</span>/search.html
</pre>

<p>My page looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/yacy/embedded_search.png" alt="YaCy embedded search" /></p>

<p>As you type in terms, you should see pages within your domain that are relevant to the query:</p>

<p><img src="https://assets.digitalocean.com/articles/yacy/example_search.png" alt="YaCy example search" /></p>

<h2 id="conclusion">Conclusion</h2>

<hr />

<p>You can use YaCy in a great number of ways.  If you wish to contribute to the global index in order to create a viable alternative to search engines maintained by corporations, you can easily crawl sites and allow your server to be a peer for other users.</p>

<p>If you need a great search engine for your site, YaCy provides that option as well.  YaCy is very flexible and is an interesting solution to the problem of privacy concerns.</p>

<div class="author">By Justin Ellingwood</div>

    