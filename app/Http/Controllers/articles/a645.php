<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h2 id="introduction">Introduction</h2>

<p>In this tutorial we will create a master snapshot image with our software and configuration and then use the IndiaReads API automate deployment of droplets using this image.  The examples in this tutorial  will be using the official IndiaReads API client for Ruby <a href="https://github.com/digitalocean/droplet_kit">DropletKit</a>.  </p>

<h2 id="prerequisites">Prerequisites</h2>

<ul>
<li><a href="https://www.ruby-lang.org/">Ruby</a> should be installed on the computer you will be using to connect to the API.</li>
<li>The <a href="https://github.com/digitalocean/droplet_kit">DropletKit</a> gem should be installed in this environment as well.</li>
<li>You should have <a href="https://indiareads/community/tutorials/how-to-use-the-digitalocean-api-v2">generated an API token</a> for your scripts to use.</li>
</ul>

<h2 id="step-one-create-your-master-image">Step One: Create Your Master Image</h2>

<p>In this tutorial we will create a master image based on the <a href="https://indiareads/features/one-click-apps/lamp/">LAMP One-Click Image</a>, set up our default configuration and then use it to create a snapshot image.  We will then be able to deploy multiple instances of our customized LAMP stack using the IndiaReads API.</p>

<h3 id="create-a-new-droplet">Create a new droplet</h3>

<p>We will start by creating a new droplet named <code>lamp-master</code> from the control panel selecting the LAMP image in the Applications tab.  This image will provide us with a pre-built Ubuntu 14.04 server with Apache, MySQL and PHP.</p>

<p>When creating the droplet we will use to generate our master snapshot image it is important to select the smallest droplet plan we can.  Once we create our snapshot it can only be used to create droplets on the same plan or a larger one.  For example, if we create our master snapshot using a 1GB droplet we could then use it to launch droplets on the 1GB, 2GB or other larger plans but we would not be able to launch a droplet with 512MB RAM from this snapshot.</p>

<p>Once our new droplet has been created, use an SSH client to connect to it.</p>

<h3 id="initial-configuration">Initial Configuration</h3>

<p>Now that we are connected to our new droplet we can configure any settings or install any packages that we want to have on all droplets deployed from our master image.  In this case we will install two extra php modules; curl and Imagemagick.</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install php5-curl php5-imagick
</code></pre>
<h3 id="creating-the-snapshot">Creating the Snapshot</h3>

<p>Now that we have added the additional software we want we can power off our droplet and create our snapshot.</p>
<pre class="code-pre "><code langs="">sudo poweroff
</code></pre>
<p>While we could create our snapshot from the control panel, for the purposes of this tutorial we will use the API from this point forward to work with our IndiaReads account.  These examples can be run with interactive Ruby (<code>irb</code>) or added to a script and run with the <code>ruby</code> command.  The first step will be to include the DropletKit client.</p>
<pre class="code-pre "><code langs="">require 'droplet_kit'
token='<span class="highlight">[your api token]</span>'
client = DropletKit::Client.new(access_token: token)
</code></pre>
<p>In order to create a snapshot from the API we will need to get the id for our master droplet.  We can do this by making a call to the droplets endpoint of the API.</p>
<pre class="code-pre "><code langs="">droplets = client.droplets.all
droplets.each do |droplet|
  if droplet.name == "lamp-master"
    puts droplet.id
  end
end
</code></pre>
<p>This snippet of code will make a call to the droplets endpoint of the API and loop through the droplets in our account looking for one with the name <code>lamp-master</code>.  When it finds it, the script will then display the ID number for this droplet.</p>

<p>Now that we have our droplet ID number we can tell the API to create a snapshot of this droplet by passing the droplet ID to the snapshot action of the droplet endpoint.  In addition to the droplet ID we will also pass a snapshot name which will be used for our new image.  In this case we have decided to name our snapshot <code>lamp-image</code>.</p>
<pre class="code-pre "><code langs="">client.droplet_actions.snapshot(droplet_id: '<span class="highlight">1234567</span>', name: 'lamp-image')
</code></pre>
<p>The snapshot request we made will return an event ID number which can be used to track the status of the snapshot process.  <a href="https://indiareads/community/tutorials/how-to-use-and-understand-action-objects-and-the-digitalocean-api">This tutorial</a> will provide more information on using event IDs.</p>

<h2 id="step-two-deploying-droplets-from-our-snapshot">Step Two: Deploying Droplets from our Snapshot</h2>

<p>We have now created a master snapshot image we can use to deploy droplets with our configuration.  As we did with our droplet, we will now need to query the API to get the image ID for our new snapshot.</p>
<pre class="code-pre "><code langs="">images = client.images.all(public:false)
images.each do |image|
  if image.name == "lamp-image"
    puts image.id
  end
end
</code></pre>
<p>As with our droplet identification example above this code will loop through the snapshot and backup images on our account and display the ID for the image named <code>lamp-image</code>.</p>

<p>Now that we have our image's ID number we can start deploying droplets. The following code will create a new 2GB droplet using our master snapshot in the New York 3 region.</p>

<p>Note that our snapshot image needs to be present in the region we specify for our droplet creation.  You can transfer an image to additional regions via the control panel or through the API's <a href="https://developers.digitalocean.com/documentation/v2/#transfer-an-image">image endpoint</a>.</p>
<pre class="code-pre "><code langs="">droplet = DropletKit::Droplet.new(name: 'my-lamp-server', region: 'nyc3', size: '2gb', image: '<span class="highlight">1234567</span>')
client.droplets.create(droplet)
</code></pre>
<h2 id="step-three-customization-with-user-data">Step Three: Customization with User-Data</h2>

<p>We can now deploy new droplets with our custom configuration using the API but we may want to further customize our new droplets individually. We can perform additional customization by sending user-data to our droplets when we create them.</p>

<p>For this example we will pre-load a custom index.html file on our new droplet including it's name.</p>
<pre class="code-pre "><code langs="">sitename = "example.org"
userdata = "
#cloud-config

runcmd:
- echo '<html><head><title>Welcome to #{sitename} </title></head><body><h1>This is #{sitename}</h1></body></html>' > /var/www/html/index.html
"
droplet = DropletKit::Droplet.new(name: sitename, region: 'nyc3', size: '2gb', image: '<span class="highlight">1234567</span>', user_data: userdata)
client.droplets.create(droplet)
</code></pre>
<p>In this example we are simply using the <code>echo</code> command inside our new droplet to drop some HTML into an index.html file in the web root.  By using other commands you could choose to configure new virtualhosts directly on your droplet, pull down additional configuration details from a remote server or do just about anything you could do via an ssh connection.  <a href="https://indiareads/company/blog/automating-application-deployments-with-user-data/">You can learn more about user-data here</a>.</p>

<h2 id="step-four-putting-it-together">Step Four: Putting it Together</h2>

<p>Now that we can deploy droplets based on our snapshot image via the API and customize their contents lets take it a step further and create an interactive script to launch new droplets based on our image.  The following script assumes that we have already created our snapshot image and have it's ID available.</p>
<pre class="code-pre "><code langs="">require 'droplet_kit'
token='<span class="highlight">[Your API Token]</span>'
client = DropletKit::Client.new(access_token: token)
region = 'nyc3'
image_id = '<span class="highlight">1234567</span>'
droplet_size = '2gb'

puts "Enter a name for your new droplet:"
sitename = gets.chomp

userdata = "
#cloud-config

runcmd:
- echo '<html><head><title>Welcome to #{sitename} </title></head><body><h1>This is #{sitename}</h1></body></html>' > /var/www/html/index.html
"
sitename.gsub!(/\s/,'-')
droplet = DropletKit::Droplet.new(name: sitename, region: region, size: droplet_size, image: image_id, user_data: userdata)
client.droplets.create(droplet)
</code></pre>
<h3 id="code-breakdown">Code Breakdown</h3>

<p>This script first includes the DropletKit client and initializes a new client connection using the API token you supply.</p>
<pre class="code-pre "><code langs="">require 'droplet_kit'
token='<span class="highlight">[Your API Token]</span>'
client = DropletKit::Client.new(access_token: token)
</code></pre>
<p>We then specify a few options for our droplet including the region, droplet size and the ID for our master snapshot image.</p>
<pre class="code-pre "><code langs="">region = 'nyc3'
image_id = '<span class="highlight">1234567</span>'
droplet_size = '2gb'
</code></pre>
<p>Then we prompt the user to provide a name for the new droplet and include this information in the user-data our script will provide to the creation process.</p>
<pre class="code-pre "><code langs="">puts "Enter a name for your new droplet:"
sitename = gets.chomp

userdata = "
#cloud-config

runcmd:
- echo '<html><head><title>Welcome to #{sitename} </title></head><body><h1>This is #{sitename}</h1></body></html>' > /var/www/html/index.html
"
</code></pre>
<p>Once we have included our site name in our index.html page we need to sanitize it to make sure it can be used as a droplet name.  Since droplet names cannot have spaces in them we will replace any spaces with dashes.</p>
<pre class="code-pre "><code langs="">sitename.gsub!(/\s/,'-')
</code></pre>
<p>Then we bring all these variables together and submit our request to create the new droplet.</p>
<pre class="code-pre "><code langs="">droplet = DropletKit::Droplet.new(name: sitename, region: region, size: droplet_size, image: image_id, user_data: userdata)
client.droplets.create(droplet)
</code></pre>
<h2 id="next-steps">Next Steps</h2>

<p>Using the API we can create custom droplets on demand and include our own settings or files at creation.  You may choose to expand on these basics by adding additional functionality to this script.  Possible improvements include.</p>

<ul>
<li><p>Using the <a href="https://developers.digitalocean.com/documentation/v2/#domains">DNS endpoint</a> of the API to auto-configure DNS records for your new droplets when they are launched.</p></li>
<li><p>Prompting the user for additional inputs such as region or droplet size.</p></li>
<li><p>Using user-data runcmd calls to download web content to your new droplets or to populate MySQL databases.</p></li>
</ul>

    