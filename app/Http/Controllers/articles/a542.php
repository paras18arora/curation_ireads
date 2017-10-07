<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/kibana_tw.jpg?1429134717/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Kibana 4 is an analytics and visualization platform that builds on Elasticsearch to give you a better understanding of your data. In this tutorial, we will get you started with Kibana, by showing you how to use its interface to filter and visualize log messages gathered by an Elasticsearch ELK stack. We will cover the main interface components, and demonstrate how to create searches, visualizations, and dashboards.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This tutorial is the third part in the <strong>Centralized Logging with Logstash and Kibana</strong> series.</p>

<p>It assumes that you have a working ELK setup. The examples assume that you are gathering syslog and Nginx access logs. If you are not gathering these types of logs, you should be able to modify the demonstrations to work with your own log messages.</p>

<p>If you want to follow this tutorial exactly as presented, you should have the following setup, by following the first two tutorials in this series:</p>

<ul>
<li>An ELK Stack gathering syslogs: <a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-4-on-ubuntu-14-04">How To Install Elasticsearch, Logstash, and Kibana 4 on Ubuntu 14.04</a></li>
<li>Nginx access logs and filters: <a href="https://indiareads/community/tutorials/adding-logstash-filters-to-improve-centralized-logging">Adding Logstash Filters To Improve Centralized Logging</a></li>
</ul>

<p>When you are ready to move on, let's look at an overview of the Kibana interface.</p>

<h2 id="kibana-interface-overview">Kibana Interface Overview</h2>

<p>The Kibana interface is divided into four main sections:</p>

<ul>
<li>Discover</li>
<li>Visualize</li>
<li>Dashboard</li>
<li>Settings</li>
</ul>

<p>We will go over the basics of each section, in the listed order, and demonstrate how each piece of the interface can be used.</p>

<h2 id="kibana-discover">Kibana Discover</h2>

<p>When you first connect to Kibana 4, you will be taken to the Discover page. By default, this page will display all of your ELK stack's most recently received logs.  Here, you can filter through and find specific log messages based on <strong>Search Queries</strong>, then narrow the search results to a specific time range with the <strong>Time Filter</strong>.</p>

<p>Here is a breakdown of the Kibana Discover interface elements:</p>

<ul>
<li><strong>Search Bar:</strong> Directly under the main navigation menu. Use this to search specific fields and/or entire messages</li>
<li><strong>Time Filter:</strong> Top-right (clock icon). Use this to filter logs based on various relative and absolute time ranges</li>
<li><strong>Field Selector:</strong> Left, under the search bar. Select fields to modify which ones are displayed in the <em>Log View</em></li>
<li><strong>Date Histogram:</strong> Bar graph under the search bar. By default, this shows the count of all logs, versus time (x-axis), matched by the search and time filter. You can click on bars, or click-and-drag, to narrow the time filter</li>
<li><strong>Log View:</strong> Bottom-right. Use this to look at individual log messages, and display log data filtered by <em>fields</em>. If no fields are selected, entire log messages are displayed</li>
</ul>

<p>This animation demonstrates a few of the main features of the Discover page:</p>

<p><img src="https://assets.digitalocean.com/articles/elk/kibana/1-discover.gif" alt="How To Use Kibana Discover" /></p>

<p>Here is a step-by-step description of what is being performed:</p>

<ol>
<li>Selected the "type" field, which limits what is displayed for each log record (bottom-right)—by default, the entire log message is displayed</li>
<li>Searched for <code>type: "nginx-access"</code>, which only matches Nginx access logs</li>
<li>Expanded the most recent Nginx access log to look at it in more detail</li>
</ol>

<p>Note that the results are being limited to the "Last 15 minutes". If you are not getting any results, be sure that there were logs, that match your search query, generated in the time period specified.</p>

<p>The log messages that are gathered and filtered are dependent on your Logstash and Logstash Forwarder configurations. In our example, we are gathering the syslog and Nginx access logs, and filtering them by "type". If you are gathering log messages but not filtering the data into distinct fields, querying against them will be more difficult as you will be unable to query specific fields.</p>

<h3 id="search-syntax">Search Syntax</h3>

<p>The search provides an easy and powerful way to select a specific subset of log messages. The search syntax is pretty self-explanatory, and allows boolean operators, wildcards, and field filtering. For example, if you want to find Nginx access logs that were generated by Google Chrome users, you can search for <code>type: "nginx-access" AND agent: "chrome"</code>. You could also search by specific hosts or client IP address ranges, or any other data that is contained in your logs.</p>

<p>When you have created a search query that you want to keep, you can do that by clicking the <strong>Save Search</strong> icon then the <strong>Save</strong> button, like in this animation:</p>

<p><img src="https://assets.digitalocean.com/articles/elk/kibana/2-save-search.gif" alt="How To Save a Kibana Search" /></p>

<p>Saved searches can be opened at any time by clicking the <strong>Load Saved Search</strong> icon, and they can also be used when creating visualizations.</p>

<p>We will save the <code>type: "nginx-access"</code> search as "type nginx access", and use it to create a visualization.</p>

<h2 id="kibana-visualize">Kibana Visualize</h2>

<p>The Kibana Visualize page is where you can create, modify, and view your own custom visualizations. There are several different types of visualizations, ranging from <em>Vertical bar</em> and <em>Pie</em> charts to <em>Tile maps</em> (for displaying data on a map) and <em>Data tables</em>. Visualizations can also be shared with other users who have access to your Kibana instance.</p>

<p>If this is your first time using Kibana visualizations, you must reload your field list before proceeding. Instructions to do this are covered in the <strong>Reload Field Data</strong> subsection, under the <a href="https://indiareads/community/tutorials/how-to-use-kibana-dashboards-and-visualizations#kibana-settings">Kibana Settings</a> section.</p>

<h3 id="create-vertical-bar-chart">Create Vertical Bar Chart</h3>

<p>To create a visualization, first, click the <strong>Visualize</strong> menu item.</p>

<p>Decide which type of visualization you want, and select it. We will create a <strong>Vertical bar chart</strong>, which is a good starting point.</p>

<p>Now you must select a search source. You may either create a new search or use a saved search. We will go with the latter method, and select the <strong>type nginx access</strong> search that we created earlier.</p>

<p>At first, the preview graph, on the right side, will be a solid bar (assuming that your search found log messages) because it consists only of a Y-axis of "Count". That is, it is simply displaying the number of logs that were found with the specified search query.</p>

<p>To make the visualization more useful, let's add some new <strong>buckets</strong> to it.</p>

<p>First, add an <strong>X-axis</strong> bucket, then click the <strong>Aggregation</strong> drop-down menu and select "Date Histogram". If you click the <strong>Apply</strong> button, the single bar will split into several bars along the X-axis. Now the Count is displayed as multiple bars, divided into intervals of time (which can be modified by selecting an interval from the drop-down)—similar to what you would see on the Discover page.</p>

<p>If we want to make the graph a little more interesting, we can click the <strong>Add Sub Aggregation</strong> button. Select the <strong>Split Bars</strong> bucket type. Click the <strong>Sub Aggregation</strong> drop-down menu and select "Significant Terms", then click the <strong>Field</strong> drop-down menu and select "clientip.raw", then click the <strong>Size</strong> field and enter "10". Click the <strong>Apply</strong> button to create the new graph.</p>

<p>Here is a screenshot of what you should see at this point:</p>

<p><img src="https://assets.digitalocean.com/articles/elk/kibana/visualize-nginx-access.png" alt="Kibana Visualization Settings" /></p>

<p>If the logs being visualized were generated by multiple IP addresses (i.e. more than one person is accessing your site), you will see that each bar will be divided into colored segments. Each colored segment represents the Count of logs generated by a specific IP address (i.e. a particular visitor to your site), and the graph will show the up to 10 different segments (because of the Size setting). You can mouseover and click any of the items in the graph to drill down to specific log messages.</p>

<p>When you are ready to save your visualization, click the <strong>Save Visualization</strong> icon, near the top, then name it and click the <strong>Save</strong> button.</p>

<h3 id="create-another-visualization">Create Another Visualization</h3>

<p>Before continuing to the next section, where we will demonstrate how to create a dashboard, you should create at least one more visualization. Try and explore the various visualization types.</p>

<p>For example, you could create a pie chart of your top 5 (highest count) log "types". To do this, click <strong>Visualize</strong> then select <strong>Pie chart</strong>. Then use a <strong>new search</strong>, and leave the search as "<em>" (i.e. all of your logs). Then select *</em>Split Slices** bucket. Click the <strong>Aggregation</strong> drop-down and select "Significant Terms", click the <strong>Field</strong> drop-down and select "type.raw", then click the <strong>Size</strong> field and enter "5". Now click the <strong>Apply</strong> button and save the visualization as "Top 5".</p>

<p>Here is a screenshot of the settings that were just described:</p>

<p><img src="https://assets.digitalocean.com/articles/elk/kibana/pie-settings.png" alt="Pie chart settings" /></p>

<p>Because, in our example, we're only collecting syslogs and Nginx access logs, there will only be two slices in the pie chart.</p>

<p>Once you are done creating visualizations, let's move on to creating a Kibana dashboard.</p>

<h2 id="kibana-dashboard">Kibana Dashboard</h2>

<p>The Kibana Dashboard page is where you can create, modify, and view your own custom dashboards. With a dashboard, you can combine multiple visualizations onto a single page, then filter them by providing a search query or by selecting filters by clicking elements in the visualization. Dashboards are useful for when you want to get an overview of your logs, and make correlations among various visualizations and logs.</p>

<h3 id="create-dashboard">Create Dashboard</h3>

<p>To create a Kibana dashboard, first, click the <strong>Dashboard</strong> menu item.</p>

<p>If you haven't created a dashboard before, you will see a mostly blank page that says "Ready to get started?". If you don't see this screen (i.e. there are already visualizations on the dashboard), press the <strong>New Dashboard icon</strong> (to the right of the search bar) to get there.</p>

<p>This animation demonstrates how to can add visualizations to your dashboard:</p>

<p><img src="https://assets.digitalocean.com/articles/elk/kibana/5-create-dashboard.gif" alt="Create a Kibana Dashboard" /></p>

<p>Here is a breakdown of the steps that are being performed:</p>

<ol>
<li>Clicked <em>Add Visualization icon</em></li>
<li>Added "Log Counts" pie chart and "Nginx: Top 10 client IP" histogram</li>
<li>Collapsed the <em>Add Visualization menu</em></li>
<li>Rearranged and resized the visualizations on the dashboard</li>
<li>Clicked <em>Save Dashboard</em> icon</li>
</ol>

<p>Choose a name for your dashboard before saving it.</p>

<p>This should give you a good idea of how to create a dashboard. Go ahead and create any dashboards that you think you might want. We'll cover using dashboards next.</p>

<h3 id="use-dashboard">Use Dashboard</h3>

<p>Dashboards can be filtered further by entering a search query, changing the time filter, or clicking on the elements within the visualization. </p>

<p>For example, if you click on a particular color segment in the histogram, Kibana will allow you to filter on the significant term that the segment represents. Here is an example screenshot of applying a filter to a dashboard:</p>

<p><img src="https://assets.digitalocean.com/articles/elk/kibana/filter-dashboard.png" alt="Filter a dashboard" /></p>

<p>Be sure to click the <strong>Apply Now button</strong> to filter the results, and redraw the dashboard's visualizations. Filters can be applied and removed as needed.</p>

<p>The search and time filters work just like they do in the Discover page, except they are only applied to the data subsets that are presented in the dashboard.</p>

<h2 id="kibana-settings">Kibana Settings</h2>

<p>The Kibana Settings page lets you change a variety of things like default values or index patterns. In this tutorial, we will keep it simple and focus on the <strong>Indices</strong> and <strong>Objects</strong> sections. </p>

<h3 id="reload-field-data">Reload Field Data</h3>

<p>When you add new fields to your Logstash data, e.g. if you add a filter for a new log type, you may need to reload your field list. It is necessary to reload the field list if you are unable find filtered fields in Kibana, as this data is only cached periodically.</p>

<p>To do so, click the <strong>Settings</strong> menu item, then click "logstash-*" (under <strong>Index Patterns</strong>):</p>

<p><img src="https://assets.digitalocean.com/articles/elk/kibana/reload-field-list.png" alt="Reload Field List" /></p>

<p>Then click the yellow <strong>Reload Field List</strong> button. Hit the <strong>OK</strong> button to confirm.</p>

<h3 id="edit-saved-objects">Edit Saved Objects</h3>

<p>The Objects section allows you to edit, view, and delete any of your saved dashboards, searches, and visualizations.</p>

<p>To get there, click on the <strong>Settings</strong> menu item, then the <strong>Objects</strong> sub-menu.</p>

<p>Here, you can select from the tabs to find the objects that you want to edit, view, or delete:</p>

<p><img src="https://assets.digitalocean.com/articles/elk/kibana/settings-objects.png" alt="Edit Saved Objects" /></p>

<p>In the screenshot, we have selected a duplicate visualization. It can be edited, viewed, or deleted by clicking on the appropriate button.</p>

<h2 id="conclusion">Conclusion</h2>

<p>If you followed this tutorial, you should have a good understanding of how to use Kibana 4. You should know how to search your log messages, and create visualizations and dashboards.</p>

<p>Be sure to check out the next tutorial in this series, <a href="https://indiareads/community/tutorials/how-to-map-user-location-with-geoip-and-elk-elasticsearch-logstash-and-kibana">How To Map User Location with GeoIP and ELK</a></p>

<p>If you have any questions or suggestions, please leave a comment!</p>

    