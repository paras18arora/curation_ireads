<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/graphite_tw_720.png?1429025216/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Gathering information about your systems and applications can give you the information you need to make informed decisions about your infrastructure, servers, and software.</p>

<p>There are many different ways to acquire this kind of information, and display it in a way that allows for easy comprehension.  One of these applications is called <strong>Graphite</strong>.</p>

<p>Graphite is an excellent tool for organizing and rendering visual representations of data gathered from your system.  It is highly flexible and can be configured so that you can gain the benefits of both detailed representation and broad overviews of the performance and health of the metrics you are tracking.</p>

<p>In a previous guide, we looked at <a href="https://indiareads/community/articles/an-introduction-to-tracking-statistics-with-graphite-statsd-and-collectd">an overview of graphing and stats gathering applications</a> that you can string together to create a robust system to display stats.  In this guide, we'll show you how to get set up with Graphite on your Ubuntu 14.04 server.  In a future guide, we'll talk about how to feed Graphite stats from <a href="https://indiareads/community/articles/how-to-configure-collectd-to-gather-system-metrics-for-graphite-on-ubuntu-14-04">collectd</a> and <a href="https://indiareads/community/articles/how-to-configure-statsd-to-collect-arbitrary-stats-for-graphite-on-ubuntu-14-04">Statsd</a>.</p>

<h2 id="install-graphite">Install Graphite</h2>

<p>To get started, we need to download and install the Graphite components.  If you looked at our introduction to graphing software, you will have noticed that Graphite is made of several components: the web application, a storage backend called Carbon, and the database library called whisper.</p>

<p>Graphite used to be fairly difficult to install.  Luckily, in Ubuntu 14.04, all of the components that we need can be found in the default repositories.</p>

<p>Let's update our local package index and then install the necessary packages:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install graphite-web graphite-carbon
</code></pre>
<p>During the installation, you will be asked whether you want Carbon to remove the database files if you ever decide to purge the installation.  Choose "No" here so that you will not destroy your stats.  If you need to start fresh, you can always manually remove the files (kept in <code>var/lib/graphite/whisper</code>).</p>

<p>When the installation is complete, Graphite will be installed.  We need to do some additional configuration though to get everything off the ground and running.</p>

<h2 id="configure-a-database-for-django">Configure a Database for Django</h2>

<p>Although the Graphite data itself is handled by Carbon and the whisper database library, the web application is a Django Python application, and needs to store its data somewhere.</p>

<p>By default, this is configured to use SQLite3 database files.  However, these aren't as robust as a full-fledged relational database management system, so we will be configuring our app to use PostgreSQL instead.  PostgreSQL is much stricter with data typing and will catch exceptions that might lead to problems down the road.</p>

<h3 id="install-postgresql-components">Install PostgreSQL Components</h3>

<p>We can install the database software and the helper packages we need by typing:</p>
<pre class="code-pre "><code langs="">sudo apt-get install postgresql libpq-dev python-psycopg2
</code></pre>
<p>This will install the database software, as well as the Python libraries that Graphite will use to connect to and communicate with the database.</p>

<h3 id="create-a-database-user-and-a-database">Create a Database User and a Database</h3>

<p>After our database software is installed, we'll need to create a PostgreSQL user and database for Graphite to use.</p>

<p>We can sign into an interactive PostgreSQL prompt by using the <code>psql</code> command as the <code>postgres</code> system user:</p>
<pre class="code-pre "><code langs="">sudo -u postgres psql
</code></pre>
<p>Now, we need to create a database user account that Django will use to operate on our database.  We will call the user <code>graphite</code>.  Select a secure password for this user:</p>

<pre>
CREATE USER graphite WITH PASSWORD '<span class="highlight">password</span>';
</pre>

<p>Now, we can create a database and give our new user ownership of it.  We are going to call the database <code>graphite</code> as well to make it easy to recognize their association:</p>
<pre class="code-pre "><code langs="">CREATE DATABASE graphite WITH OWNER graphite;
</code></pre>
<p>When you are finished, we can exit out of the PostgreSQL session:</p>
<pre class="code-pre "><code langs="">\q
</code></pre>
<p>You may see a message that says that Postgres could not save the file history.  This is not a problem for us, so we can continue.</p>

<h2 id="configure-the-graphite-web-application">Configure the Graphite Web Application</h2>

<p>Now, we have our database and user ready to go.  However, we still need to modify Graphite's settings to use the components we just configured.  There are also some other settings that we should take a look at.</p>

<p>Open the Graphite web app configuration file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/graphite/local_settings.py
</code></pre>
<p>First, we should set the secret key that will be used as a salt when creating hashes.  Uncomment the <code>SECRET_KEY</code> parameter and change the value to something long and unique.</p>

<pre>
SECRET_KEY = '<span class="highlight">a_salty_string</span>'
</pre>

<p>Next, we should specify the timezone.  This will affect the time displayed on our graphs, so it is important to set.  Set it to your time zone as specified by the "TZ" column <a href="http://en.wikipedia.org/wiki/List_of_tz_database_time_zones">in this list</a>.</p>

<pre>
TIME_ZONE = '<span class="highlight">America/New_York</span>'
</pre>

<p>We also want to configure authentication for saving graph data.  When we sync the database, we'll be able to create a user account, but we need to enable authentication by uncommenting this line:</p>
<pre class="code-pre "><code langs="">USE_REMOTE_USER_AUTHENTICATION = True
</code></pre>
<p>Next, look for the <code>DATABASES</code> dictionary definition.  We want to change the values to reflect our Postgres information.  You should change the <code>NAME</code>, <code>ENGINE</code>, <code>USER</code>, <code>PASSWORD</code>, and <code>HOST</code> keys.</p>

<p>When you are finished, it should look something like this:</p>

<pre>
DATABASES = {
    'default': {
        'NAME': '<span class="highlight">graphite</span>',
        'ENGINE': 'django.db.backends.<span class="highlight">postgresql_psycopg2</span>',
        'USER': '<span class="highlight">graphite</span>',
        'PASSWORD': '<span class="highlight">password</span>',
        'HOST': '<span class="highlight">127.0.0.1</span>',
        'PORT': ''
    }
}
</pre>

<p>The areas in red are values you need to change.  Make sure that you modify the password to the one you selected for the <code>graphite</code> user in Postgres.</p>

<p>Also, make sure that you set the <code>HOST</code> parameter.  If you leave this blank, Postgres will think you are trying to connect using peer authentication, which will not authenticate correctly in our case.</p>

<p>Save and close the file when you are finished.</p>

<h2 id="sync-the-database">Sync the Database</h2>

<p>Now that we have our database section filled out, we can sync the database to create the correct structure.</p>

<p>You can do this by typing:</p>
<pre class="code-pre "><code langs="">sudo graphite-manage syncdb
</code></pre>
<p>You will be asked to create a superuser account for the database.  Create a new user so that you can sign into the interface.  You can call this whatever you want.  This will allow you to save your graphs and modify the interface.</p>

<h2 id="configure-carbon">Configure Carbon</h2>

<p>Now that we have a database, we can start to configure Carbon, the Graphite storage backend.</p>

<p>First, let's enable the carbon service to start at boot.  We can do this by opening the service configuration file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/default/graphite-carbon
</code></pre>
<p>This only has one parameter, which dictates whether the service will start on boot.  Change the value to "true":</p>
<pre class="code-pre "><code langs="">    CARBON_CACHE_ENABLED=true
</code></pre>
<p>Save and close the file.</p>

<p>Next, open the Carbon configuration file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/carbon/carbon.conf
</code></pre>
<p>Most of this file is already configured correctly for our purposes.  However, we will make a small change.</p>

<p>Turn on log rotation by adjusting setting this directive to true:</p>
<pre class="code-pre "><code langs="">ENABLE_LOGROTATION = True
</code></pre>
<p>Save and close the file.</p>

<h3 id="configuring-storage-schemas">Configuring Storage Schemas</h3>

<p>Now, open the storage schema file.  This tells Carbon how long to store values and how detailed these values should be:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/carbon/storage-schemas.conf
</code></pre>
<p>Inside, you will find entries that look like this:</p>

<pre>
[carbon]
pattern = ^carbon\.
retentions = 60:90d

[default_1min_for_1day]
pattern = .*
retentions = 60s:1d
</pre>

<p>The file currently has two sections defined.  The first one is for deciding what to do with data coming from Carbon itself.  Carbon is actually configured to store some metrics of its own performance.  The bottom definition is a catch-all that is designed to apply to any data that hasn't been matched by another section.  It defines a default policy.</p>

<p>The words in the brackets are the section headers that are used to define a new definition.  Under each section, there is a pattern definition and a retentions policy.</p>

<p>The pattern definition is a regular expression that is used to match any information sent to Carbon.  Information sent to Carbon includes a metric name, which is what this checks.  In the first example, the pattern checks whether the metric in question starts with the string "<code>carbon.</code>".</p>

<p>The retention policy is defined by sets of numbers.  Each set consists of a metric interval (how often a metric is recorded), followed by a colon and then the length of time to store those values.  You can define multiple sets of numbers separated by commas.</p>

<p>To demonstrate, we will define a new schema that will match a test value that we'll use later on.</p>

<p>Before the default section, add another section for our test values.  Make it look like this:</p>

<pre>
[test]
pattern = ^test\.
retentions = 10s:10m,1m:1h,10m:1d
</pre>

<p>This will match any metrics beginning with "<code>test.</code>".  It will store the data it collects three times, in varying detail.  The first archive definition (<code>10s:10m</code>) will create a data point every ten seconds.  It will store the values for only ten minutes.</p>

<p>The second archive (<code>1m:1h</code>) will create a data point every minute.  It will gather all of the data from the past minute (six points, since the previous archive creates a point every ten seconds) and aggregate it to create the point.  By default, it does this by averaging the points, but we can adjust this later.  It stores the data at this level of detail for one hour.</p>

<p>The last archive that will be created (<code>10m:1d</code>) will make a data point every 10 minutes, aggregating the data in the same way as the second archive.  It will store the data for one day.</p>

<p>When we request information from Graphite, it will return information from the most detailed archive that measures the time frame we're asking for.  So if we ask for metrics from the past five minutes, information from the first archive will be returned.  If we ask for a graph of the past 50 minutes, the data will be taken from the second archive.</p>

<p>Save and close the file when you are finished.</p>

<h3 id="about-storage-aggregation-methods">About Storage Aggregation Methods</h3>

<p>The way that Carbon decides to aggregate data when crunching more detailed information into a generalized number is very important to understand if you want accurate metrics.  This applies every time that Graphite makes a less detailed version of a metric, like in the second and third archives in the test schema we created above.</p>

<p>As we mentioned above, the default behavior is to take the average when aggregating.  This means that, other than the most detailed archive, Carbon will average the data points it received to create the number.</p>

<p>This is not always desirable though.  For instance, if we want the total number of times that an event occurred over various time periods, we would want to add up the data points to create our generalized data point instead of averaging them.</p>

<p>We can define the way we want aggregation to occur in a file called <code>storage-aggregation.conf</code>.  Copy the file from the Carbon examples directory into our Carbon configuration directory:</p>
<pre class="code-pre "><code langs="">sudo cp /usr/share/doc/graphite-carbon/examples/storage-aggregation.conf.example /etc/carbon/storage-aggregation.conf
</code></pre>
<p>Open the file in your text editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/carbon/storage-aggregation.conf
</code></pre>
<p>This looks a bit similar to the last file.  You will find entries that look like this:</p>

<pre>
[min]
pattern = \.min$
xFilesFactor = 0.1
aggregationMethod = min
</pre>

<p>The section name and pattern are exactly the same as the storage-schemas file.  It is just an arbitrary name and a pattern to match the metrics you are defining.</p>

<p>The <code>XFilesFactor</code> is an interesting parameter in that it allows you to specify the minimum percentage of values that Carbon should have to do the aggregation.  By default, all values are set to <code>0.5</code>, meaning that 50% of the more detailed data points must be available if an aggregated point is to be created. </p>

<p>This can be used to ensure that you're not creating data points that might misrepresent the actual situation.  For instance, if 70% of your data is being dropped because of network problems, you might not want to create a point that only truthfully represents 30% of the data.</p>

<p>The aggregation method is defined next.  Possible values are average, sum, last, max and min.  They are fairly self explanatory, but very important.  Choosing the wrong value will cause your data to be recorded in an incorrect way.  The correct selection depends entirely on what the metric is that you're actually tracking.</p>

<p><strong>Note</strong>: It is important to realize that if you send Graphite data points more frequently than the shortest archive interval length, some of your data <strong>will be lost!</strong></p>

<p>This is because Graphite only applies aggregation when going from detailed archives to generalized archives.  When creating the detailed data point, it only writes the most recent data sent to it when the interval has passed.  We will discuss <strong>StatsD</strong> in another guide, which can help alleviate this problem by caching and aggregating data that comes in at a more frequent interval.</p>

<p>Save and close the file.</p>

<p>When you are finished, you can start Carbon by typing:</p>
<pre class="code-pre "><code langs="">sudo service carbon-cache start
</code></pre>
<h2 id="install-and-configure-apache">Install and Configure Apache</h2>

<p>In order to use the web interface, we are going to install and configure the Apache web server.  Graphite includes configuration files for Apache, so the choice is pretty easy.</p>

<p>Install the components by typing:</p>
<pre class="code-pre "><code langs="">sudo apt-get install apache2 libapache2-mod-wsgi
</code></pre>
<p>When the installation is complete, we should disable the default virtual host file, since it conflicts with our new file:</p>
<pre class="code-pre "><code langs="">sudo a2dissite 000-default
</code></pre>
<p>Next, copy the Graphite Apache virtual host file into the available sites directory:</p>
<pre class="code-pre "><code langs="">sudo cp /usr/share/graphite-web/apache2-graphite.conf /etc/apache2/sites-available
</code></pre>
<p>We can then enable the virtual host file by typing:</p>
<pre class="code-pre "><code langs="">sudo a2ensite apache2-graphite
</code></pre>
<p>Reload the service to implement the changes:</p>
<pre class="code-pre "><code langs="">sudo service apache2 reload
</code></pre>
<h2 id="checking-out-the-web-interface">Checking out the Web Interface</h2>

<p>Now that we have everything configured, we can check out the web interface.</p>

<p>In your web browser, visit your server's domain name or IP address:</p>

<pre>
http://<span class="highlight">server_domain_name_or_IP</span>
</pre>

<p>You should see a screen that looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/graphite_install/default_screen.png" alt="Graphite default screen" /></p>

<p>Before you go any further, you should log in so that you can save any graph settings you might make.  Click on the "Login" button on the top menu bar and enter the username and password you configured when syncing the Django database.</p>

<p>Next, If you open the tree for <code>Graphite</code> in the left-pane, you should see an entry for Carbon.  This is where you can find graphs of data that Carbon recorded about itself.  Click on a few of the options.  Here, I'm graphing the metrics received and update operations metrics:</p>

<p><img src="https://assets.digitalocean.com/articles/graphite_install/carbon_data.png" alt="Graphite Carbon data" /></p>

<p>Now, let's try to send some data to Graphite.  As you go through these steps, be aware that you almost never send stats to Graphite like this.  There are much better ways of doing this, but this will help to demonstrate what is going on in the background and will also help you understand the limitations of the way that Graphite handles data.  We will talk about how to get around these with companion services later.</p>

<p>Metric messages need to contain a metric name, a value, and a timestamp.  We can do this in our terminal.  Let's create a value that will match our <code>test</code> storage schema that we created.  We will also match one of the definitions that will add up the values when it aggregates.  We'll use the <code>date</code> command to make our timestamp.  Type:</p>
<pre class="code-pre "><code langs="">echo "test.count 4 `date +%s`" | nc -q0 127.0.0.1 2003
</code></pre>
<p>If you refresh the page and then look in the <code>Graphite</code> tree on the left, you will see our new test metric.  Send the above command a few times, waiting at least 10 seconds in between.  Remember, Graphite throws all but the last value when more than one value is sent in its smallest interval.</p>

<p>Now, in the web interface, tell Graphite to show you the past 8 minutes.  On the graph of the test metric, click on the icon that is a white rectangle with a green arrow.  It will say "Select Recent Data" when you mouse over it:</p>

<p><img src="https://assets.digitalocean.com/articles/graphite_install/recent_data.png" alt="Graphite recent data" /></p>

<p>Select 8 minutes from the pop up window.  Click on the icon that says "Update Graph" to get the most recent data.  You should see a graph with barely any information.  This is because you have only sent it a few values, each of which are "4", so it has no variation.</p>

<p>However, if you view the graph of the past 15 minutes (assuming that you sent the command a few different times, spaced out larger than 10 seconds but less than one minute), you should see something different:</p>

<p><img src="https://assets.digitalocean.com/articles/graphite_install/aggregation.png" alt="Graphite aggregation" /></p>

<p>This is because our first archive does not save data for fifteen minutes, so Graphite looks to our second archive for the rendering data.  It looks different because we sent Graphite a "count" metric, which matches one of our aggregation definitions.</p>

<p>The count aggregation tells Graphite to add up the values that it received over the course of its larger intervals instead of averaging.  As you can see, the aggregation method we choose is very important because it defines how a generalize data point is created from the more detailed points.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You now have Graphite installed and set up, but it is still fairly limited in what it can do.  We don't want to have to manually feed it data all of the time and we want it to not throw away data if we have more than one metric within the smallest interval.  We need companion tools that to help us work around these problems.</p>

<p>In the next guide, we'll discuss how to set up <a href="https://indiareads/community/articles/how-to-configure-collectd-to-gather-system-metrics-for-graphite-on-ubuntu-14-04">collectd</a> a system statistics gathering daemon that can be used to feed Graphite data and work around these limitations.  In future guides, we'll also cover how to configure <a href="https://indiareads/community/articles/how-to-configure-statsd-to-collect-arbitrary-stats-for-graphite-on-ubuntu-14-04">StatsD with Graphite</a> and how to use the Graphite interface more in-depth.</p>

<div class="author">By Justin Ellingwood</div>

    