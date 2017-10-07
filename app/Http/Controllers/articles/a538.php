<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/geoip_and_elk_tw.jpg?1428528793/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>IP Geolocation, the process used to determine the physical location of an IP address, can be leveraged for a variety of purposes, such as content personalization and traffic analysis. Traffic analysis by geolocation can provide invaluable insight into your user base as it allows you to easily see where they users are coming from, which can help you make informed decisions about the ideal geographical location(s) of your application servers and who your current audience is. In this tutorial, we will show you how to create a visual geo-mapping of the IP addresses of your application's users, by using a GeoIP database with Elasticsearch, Logstash, and Kibana.</p>

<p>Here's a short explanation of how it all works. Logstash uses a GeoIP database to convert IP addresses into latitude and longitude coordinate pair, i.e. the approximate physical location of an IP address. The coordinate data is stored in Elasticsearch in <code>geo_point</code> fields, and also converted into a <code>geohash</code> string. Kibana can then read the Geohash strings and draw them as points on a map of earth, known in Kibana 4 as a Tile Map visualization.</p>

<p>Let's take a look at the prerequisites now.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you must have a working ELK stack. Additionally, you must have logs that contain IP addresses that can be filtered into a field, like web server access logs. If you don't already have these two things, you can follow the first two tutorials in this series. The first tutorial will set up an ELK stack, and second one will show you how to gather and filter Nginx or Apache access logs:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-4-on-ubuntu-14-04">How To Install Elasticsearch, Logstash, and Kibana 4 on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/adding-logstash-filters-to-improve-centralized-logging">Adding Logstash Filters To Improve Centralized Logging</a></li>
</ul>

<h3 id="add-geo_point-mapping-to-filebeat-index">Add geo_point Mapping to Filebeat Index</h3>

<p>Assuming you followed the prerequisite tutorials, you have already done this. However, we are including this step again in case you skipped it because the TileMap visualization requires that your GeoIP coordinates are stored in Elasticsearch as a <code>geo_point</code> type.</p>

<p>On the server that Elasticsearch is installed, download the Filebeat index template to your home directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">curl -O https://gist.githubusercontent.com/thisismitch/3429023e8438cc25b86c/raw/d8c479e2a1adcea8b1fe86570e42abab0f10f364/filebeat-index-template.json
</li></ul></code></pre>
<p>Then load the template with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -XPUT 'http://localhost:9200/_template/filebeat?pretty' -d@filebeat-index-template.json
</li></ul></code></pre>
<h2 id="download-latest-geoip-database">Download Latest GeoIP Database</h2>

<p>MaxMind provides free and paid GeoIP databases—the paid versions are more accurate. Logstash also ships with a copy of the free GeoIP City database, GeoLite City. In this tutorial, we will download the latest GeoLite City database, but feel free to use a different GeoIP database if you wish.</p>

<p>Let's download the latest GeoLite City database gzip archive into the <code>/etc/logstash</code> directory. Do so by running these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /etc/logstash
</li><li class="line" prefix="$">sudo curl -O "http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz"
</li></ul></code></pre>
<p>Now let's unarchive it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo gunzip GeoLiteCity.dat.gz
</li></ul></code></pre>
<p>This will extract the GeoLite City database to <code>/etc/logstash/GeoLiteCity.dat</code>, which we will specify in our Logstash configuration.</p>

<p>Note that the GeoLite databases are updated by MaxMind on the first Tuesday of each month. Therefore, if you want to always have the latest database, you should set up a cron job that will download the database once a month.</p>

<p>Now we're ready to configure Logstash to use the GeoIP database.</p>

<h2 id="configure-logstash-to-use-geoip">Configure Logstash to use GeoIP</h2>

<p>To get Logstash to store GeoIP coordinates, you need to identify an application that generates logs that contain an public IP address that you can filter as a discrete field. A fairly ubiquitous application that generates logs with this information is a web server, such as Nginx or Apache, so we will use Nginx access logs as the example. If you're using different logs, just make the necessary adjustments to the example.</p>

<p>In the <a href="https://indiareads/community/tutorials/adding-logstash-filters-to-improve-centralized-logging">Adding Filters to Logstash</a> tutorial, the Nginx filter is stored in a file called <code>11-nginx-filter.conf</code>. If your filter is located elsewhere, just edit that file instead.</p>

<p>Let's edit the Nginx filter now:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/logstash/conf.d/<span class="highlight">11-nginx-filter.conf</span>
</li></ul></code></pre>
<p>Under the <code>grok</code> section (in the <code>if [type]...</code> block), add these lines:</p>
<div class="code-label " title="11-nginx-filter.conf excerpt">11-nginx-filter.conf excerpt</div><pre class="code-pre "><code langs="">    geoip {
      source => "<span class="highlight">clientip</span>"
      target => "geoip"
      database => "/etc/logstash/GeoLiteCity.dat"
      add_field => [ "[geoip][coordinates]", "%{[geoip][longitude]}" ]
      add_field => [ "[geoip][coordinates]", "%{[geoip][latitude]}"  ]
    }
    mutate {
      convert => [ "[geoip][coordinates]", "float"]
    }
</code></pre>
<p>This configures this filter to convert an IP address stored in the <code><span class="highlight">clientip</span></code> field (specified in <strong>source</strong>), using the GeoLite City database that we downloaded earlier. We are specifying the <strong>source</strong> as "clientip" because that is the name of the field that the Nginx user IP address is being stored in—be sure to change this value if you are storing the IP address information in a different field.</p>

<p>Just to be clear of what the filter should look like after you add the , here are the contents of the complete <code>11-nginx-filter.conf</code> file:</p>
<div class="code-label " title="11-nginx-filter.conf — updated">11-nginx-filter.conf — updated</div><pre class="code-pre "><code langs="">filter {
  if [type] == "nginx-access" {
    grok {
      match => { "message" => "%{NGINXACCESS}" }
    }
    geoip {
      source => "<span class="highlight">clientip</span>"
      target => "geoip"
      database => "/etc/logstash/GeoLiteCity.dat"
      add_field => [ "[geoip][coordinates]", "%{[geoip][longitude]}" ]
      add_field => [ "[geoip][coordinates]", "%{[geoip][latitude]}"  ]
    }
    mutate {
      convert => [ "[geoip][coordinates]", "float"]
    }
  }
}
</code></pre>
<p>Save and exit.</p>

<p>To put the changes into effect let's restart Logstash.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service logstash restart
</li></ul></code></pre>
<p>If everything was configured correctly, Logstash should now be storing the GeoIP coordinates with your Nginx access logs (or whichever application is generating the logs). Note that this change is <strong>not</strong> retroactive, so your previously gathered logs will not have GeoIP information added.</p>

<p>Let's verify that the GeoIP functionality is working properly in Kibana.</p>

<h2 id="connect-to-kibana">Connect to Kibana</h2>

<p>The easiest way to verify if Logstash was configured correctly, with GeoIP enabled, is to open Kibana in a web browser. Do that now.</p>

<p>Find a log message that your application generated since you enabled the GeoIP module in Logstash. Following the Nginx example, we can search Kibana for <code>type: "<span class="highlight">nginx-access</span>"</code> to narrow the log selection.</p>

<p>Then expand one of the messages to look at the table of fields. You should see some new <code>geoip</code> fields that contain information about how the IP address was mapped to a real geographical location. For example:</p>

<p><img src="https://assets.digitalocean.com/articles/elk/geoip_kibana/geoip_fields.png" alt="Example GeoIP Fields" /></p>

<p><strong>Note:</strong> If you don't see any logs, generate some by accessing your application, and ensure that your time filter is set to a recent time. If you don't see any GeoIP information (or if it's incorrect), you probably did not configure Logstash properly.</p>

<p>If you see proper GeoIP information in this view, you are ready to create your map visualization.</p>

<h2 id="create-tile-map-visualization">Create Tile Map Visualization</h2>

<p><strong>Note:</strong> If you haven't used Kibana visualizations yet, check out the <a href="https://indiareads/community/tutorials/how-to-use-kibana-dashboards-and-visualizations">Kibana Dashboards and Visualizations Tutorial</a>.</p>

<p>To map out the IP addresses in Kibana, let's create a Tile Map visualization.</p>

<p>Click <strong>Visualize</strong> in the main menu.</p>

<p>Under <strong>Create a new visualization</strong>, select <strong>Tile map</strong>.</p>

<p>Under <strong>Select a search source</strong> you may select either option. If you have a saved search that will find that log messages that you want to map, feel free to select that search.</p>

<p>Under <strong>Select buckets type</strong>, select <strong>Geo Coordinates</strong>.</p>

<p>In the <strong>Aggregation</strong> drop-down, select <strong>Geohash</strong>.</p>

<p>In the <strong>Field</strong> drop-down, select <strong>geoip.location</strong>.</p>

<p>Now click the green <strong>Apply</strong> button.</p>

<p><img src="https://assets.digitalocean.com/articles/elk/geoip_kibana/geoip_map.png" alt="Example GeoMap" /></p>

<p>If any logs from your selection (your search and time filter) contain GeoIP information, they will be drawn on the map, as in the screenshot above.</p>

<p>Be sure to play with the <strong>Precision</strong> slider, and the items under <strong>view options</strong> to adjust the visualization to your liking. The <strong>Precision</strong> slider can be adjusted to modify the length of the Geohash string that is being used to map the location.</p>

<p>When you are satisfied with your visualization, be sure to save it.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that you have your GeoIP information mapped out in Kibana, you should be set. By itself, it should give you a rough idea of the geographical location of your users. It can be even more useful if you correlate it with your other logs by adding it to a dashboard.</p>

<p>Good luck!</p>

    