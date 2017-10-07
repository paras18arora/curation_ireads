<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>DNS, or "Domain Name System", is a naming system that is used to convert a server's host name into an IP address. DNS is what binds a domain name to a web server that is hosting that domain's content.</p>

<p>In this guide, we will bind a domain name to one of our Droplets by changing the domain name servers with the domain's registrar. The instructions will differ slightly depending on the domain registrar that you made your domain purchase through.</p>

<p>Step-by-step guides can be found in this guide for the following registrars:</p>

<ul>
<li>GoDaddy</li>
<li>HostGator</li>
<li>Namecheap</li>
<li>1&1</li>
<li>Name.com</li>
<li>Network Solutions</li>
<li>eNom</li>
<li>Gandi</li>
<li>Register.com</li>
<li>A Small Orange</li>
<li>iwantmyname</li>
<li>Google Domains beta</li>
</ul>

<p>If your domain registrar is not included in the list, let us know in the comments so that we can add more popular registrars to the guide.</p>

<p>To learn more about DNS, including some common terminology and usage, check our our <a href="https://indiareads/community/tutorials/an-introduction-to-dns-terminology-components-and-concepts">introduction to DNS</a>.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin with this guide, there are a few steps that need to be completed first.</p>

<p>You will need to have a Droplet deployed that you want to bind your domain to. If you  haven't done this yet, you can follow this guide: <a href="https://indiareads/community/tutorials/how-to-create-your-first-digitalocean-droplet-virtual-server">How To Create Your First IndiaReads Droplet Virtual Server</a></p>

<p>You will also need to have purchased a domain with one of the many domain registrars available. This may be one of the popular options below, or another company of your choice.</p>

<p>Once you have your Droplet and domain, and have the credentials needed to log in to your domain registrar's account management section, then you can continue with changing the nameservers with your registrar. Look for your domain registrar in the following sections. If your registrar is not present, check the documentation on the registrar's website.</p>

<h2 id="registrar-godaddy">Registrar: GoDaddy</h2>

<p><em>This section of the guide was last updated on October 24, 2014</em></p>

<p>1. Sign in to your GoDaddy Account Manager.</p>

<p>2. Next to <strong>Domains</strong>, click <strong>Launch</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/godaddy_launch_domains.png" alt="Launch Domain Panel" /></p>

<p>3. Select the domain name that you want to use with your Droplet.</p>

<p>4. Under <strong>Nameservers</strong>, click <strong>Manage</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/godaddy_manage_nameservers.png" alt="Manage Nameservers" /></p>

<p>5. Under <strong>Setup type</strong>, select <strong>Custom</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/godaddy_custom.png" alt="Setup Type Custom" /></p>

<p>6. Select <strong>Add Nameserver</strong>.</p>

<p>7. Enter the following nameservers:</p>

<ul>
<li>ns1.digitalocean.com</li>
<li>ns2.digitalocean.com</li>
<li>ns3.digitalocean.com</li>
</ul>

<p><strong>Note:</strong> You'll need to hit <strong>Add Another</strong> for each nameserver. Once you have entered all three, you can hit <strong>Finish</strong> to return to the nameservers menu. Also note that you will not need to add any IP addresses when adding this type of nameserver.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/godaddy_add_nameserver.png" alt="Add Nameservers" /></p>

<p>8. Click <strong>Save</strong> to apply your changes. Now you are ready to move on to connecting the domain with your Droplet in the IndiaReads control panel. Check out the Conclusion section at the end of this article to read on what to do next.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/godaddy_finish.png" alt="Save Nameservers" /></p>

<h2 id="registrar-hostgator">Registrar: HostGator</h2>

<p><em>This section of the guide was last updated on October 24, 2014</em></p>

<p>1. Click on <strong>Domains</strong>, then click on the <strong>Manage Domains</strong> tab.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/hostgator_manage_domains.png" alt="Manage Domains" /></p>

<p>2. Sign in to your HostGator account.</p>

<p>3. Select the domain name that you want to use with your Droplet.</p>

<p>4. Click on the <strong>Name Servers</strong> tab.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/hostgator_tabs.png" alt="Name Servers tab" /></p>

<p>5. Enter the following nameservers:</p>

<ul>
<li>ns1.digitalocean.com</li>
<li>ns2.digitalocean.com</li>
<li>ns3.digitalocean.com</li>
</ul>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/hostgator_nameservers.png" alt="Hostgator Nameservers" /></p>

<p>6. Click <strong>Save Name Servers</strong> to apply your changes. Now you are ready to move on to connecting the domain with your Droplet in the IndiaReads control panel. Check out the Conclusion section at the end of this article to read on what to do next.</p>

<h2 id="registrar-namecheap">Registrar: Namecheap</h2>

<p><em>This section of the guide was last updated on October 24, 2014</em></p>

<p>1. Sign in to your Namecheap account and go to <strong>Manage Domains</strong>.</p>

<p>2. Select the domain name that you want to use with your Droplet.</p>

<p>3. Click on <strong>Transfer DNS to Webhost</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/namecheap_domain_menu.png" alt="Transfer DNS to Webhost" /></p>

<p>4. Select <strong>Specify Custom DNS Servers</strong>.</p>

<p>5. Enter the following nameservers:</p>

<ul>
<li>ns1.digitalocean.com</li>
<li>ns2.digitalocean.com</li>
<li>ns3.digitalocean.com</li>
</ul>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/namecheap_nameservers.png" alt="Enter IndiaReads Nameservers" /></p>

<p>6. Click <strong>Save changes</strong> to apply your changes. Now you are ready to move on to connecting the domain with your Droplet in the IndiaReads control panel. Check out the Conclusion section at the end of this article to read on what to do next.</p>

<h2 id="registrar-1-amp-1">Registrar: 1&1</h2>

<p><em>This section of the guide was last updated on October 24, 2014</em></p>

<p>1. Sign in to your 1&1 account and go to <strong>Domain Center</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/1and1_domain_center.png" alt="1&1 Domain Center" /></p>

<p>2. Select the domain name that you want to use with your Droplet.</p>

<p>3. Select the <strong>DNS Settings</strong> tab, then select <strong>Edit</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/1and1_dns_settings.png" alt="1&1 DNS Settings" /></p>

<p>4. Under <strong>Name servers</strong>, select <strong>Other name servers</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/1and1_other_name_servers.png" alt="Other name servers" /></p>

<p>5.Enter the following nameservers:</p>

<ul>
<li>ns1.digitalocean.com</li>
<li>ns2.digitalocean.com</li>
<li>ns3.digitalocean.com</li>
</ul>

<p><strong>Note:</strong> You'll need to select <strong>My secondary name servers</strong> under <strong>Additional name servers</strong> for the additional nameserver lines to appear.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/1and1_name_server_settings.png" alt="Name Server Settings" /></p>

<p>6. Click <strong>Save</strong> to apply your changes. Now you are ready to move on to connecting the domain with your Droplet in the IndiaReads control panel. Check out the Conclusion section at the end of this article to read on what to do next.</p>

<h2 id="registrar-name-com">Registrar: Name.com</h2>

<p><em>This section of the guide was last updated on October 24, 2014</em></p>

<p>1. Sign in to your Name.com account.</p>

<p>2. Select the domain name that you want to use with your Droplet.</p>

<p>3. Select the <strong>Nameservers</strong> tab, then select <strong>Delete All</strong> to remove the nameservers that are currently in place.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/name_dotcom_default_nameservers.png" alt="Current Nameservers" /></p>

<p>4. Enter the following nameservers:</p>

<ul>
<li>ns1.digitalocean.com</li>
<li>ns2.digitalocean.com</li>
<li>ns3.digitalocean.com</li>
</ul>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/name_dotcom_add_nameservers.png" alt="Add Nameservers" /></p>

<p>5. Click <strong>Apply Changes</strong>. Now you are ready to move on to connecting the domain with your Droplet in the IndiaReads control panel. Check out the Conclusion section at the end of this article to read on what to do next.</p>

<h2 id="registrar-network-solutions">Registrar: Network Solutions</h2>

<p><em>This section of the guide was last updated on October 27, 2014</em></p>

<p>1. Sign in to your Network Solutions account.</p>

<p>2. Select <strong>My Domain Names</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/network_solutions_my_domain_names.png" alt="My Domain Names" /></p>

<p>3. Find the domain name that you want to use with your Droplet, then select <strong>Change Where Domain Points</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/network_solutions_change_where_domain_points.png" alt="Change Where Domain Points" /></p>

<p>4. Select <strong>Domain Name Server (DNS)</strong>, then select <strong>Continue</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/network_solutions_domain_name_server.png" alt="My Domain Names" /></p>

<p>5. Enter the following nameservers:</p>

<ul>
<li>ns1.digitalocean.com</li>
<li>ns2.digitalocean.com</li>
<li>ns3.digitalocean.com</li>
</ul>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/network_solutions_add_nameservers.png" alt="Add Nameservers" /></p>

<p>6. Select <strong>Continue</strong>, then confirm your changes at the next page by selecting <strong>Apply Changes</strong>. Now you are ready to move on to connecting the domain with your Droplet in the IndiaReads control panel. Check out the Conclusion section at the end of this article to read on what to do next.</p>

<h2 id="registrar-enom">Registrar: eNom</h2>

<p><em>This section of the guide was last updated on October 27, 2014</em></p>

<p>1. Sign in to your eNom account.</p>

<p>2. Under <strong>Domains</strong>, select <strong>Registered</strong>. If you have multiple domains registered with eNom, select the domain name that you want to use with your Droplet.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/enom_registered_domains.png" alt="Registered Domains" /></p>

<p>3. Select <strong>DNS Server Settings</strong>.</p>

<p>4. Under <strong>User our Name Servers?</strong>, select <strong>Custom</strong>.</p>

<p>5. Enter the following nameservers:</p>

<ul>
<li>ns1.digitalocean.com</li>
<li>ns2.digitalocean.com</li>
<li>ns3.digitalocean.com</li>
</ul>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/enom_add_nameservers.png" alt="Add Nameservers" /></p>

<p>6. Select <strong>save</strong>, then confirm your changes in the popup by selecting <strong>OK</strong>. Now you are ready to move on to connecting the domain with your Droplet in the IndiaReads control panel. Check out the Conclusion section at the end of this article to read on what to do next.</p>

<h2 id="registrar-gandi">Registrar: Gandi</h2>

<p><em>This section of the guide was last updated on October 27, 2014</em></p>

<p>1. Sign in to your Gandi account.</p>

<p>2. Select the domain name that you want to use with your Droplet.</p>

<p>3. Under <strong>Name servers</strong>, select <strong>Modify servers</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/gandi_modify_servers.png" alt="Modify servers" /></p>

<p>4. Enter the following nameservers:</p>

<ul>
<li>ns1.digitalocean.com</li>
<li>ns2.digitalocean.com</li>
<li>ns3.digitalocean.com</li>
</ul>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/gandi_add_nameservers.png" alt="Add Nameservers" /></p>

<p>5. Select <strong>Submit</strong> to apply your changes. Now you are ready to move on to connecting the domain with your Droplet in the IndiaReads control panel. Check out the Conclusion section at the end of this article to read on what to do next.</p>

<h2 id="registrar-register-com">Registrar: Register.com</h2>

<p><em>This section of the guide was last updated on October 27, 2014</em></p>

<p>1. Sign in to your Register.com account.</p>

<p>2. Select the domain name that you want to use with your Droplet.</p>

<p>3. Under <strong>DOMAIN NAME SYSTEM SERVERS (DNS SERVERS)</strong>, enter the following nameservers into the <strong>New DNS Server</strong> fields:</p>

<ul>
<li>ns1.digitalocean.com</li>
<li>ns2.digitalocean.com</li>
<li>ns3.digitalocean.com</li>
</ul>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/register_dotcom_add_nameservers.png" alt="Add Nameservers" /></p>

<p>4. Select <strong>Continue</strong>, then confirm your changes at the next page by selecting <strong>Continue</strong>. Now you are ready to move on to connecting the domain with your Droplet in the IndiaReads control panel. Check out the Conclusion section at the end of this article to read on what to do next.</p>

<h2 id="registrar-a-small-orange">Registrar: A Small Orange</h2>

<p><em>This section of the guide was last updated on October 27, 2014</em></p>

<p>1. Sign in to your A Small Orange account and select <strong>My Domains</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/a_small_orange_my_domains.png" alt="My Domains" /></p>

<p>2. Find the domain name that you want to use with your Droplet, then select <strong>Manage Domain</strong> to the right of that domain name.</p>

<p>3. By default, A Small Orange locks your domain to prevent it from being transferred away without your authorization. This means that before we can change the nameservers, we'll need to disable this lock. Select the <strong>Registrar Lock</strong> tab, then select <strong>Disable Registrar Lock</strong>.</p>

<p>4. Select the <strong>Nameservers</strong> tab.</p>

<p>5. Enter the following nameservers:</p>

<ul>
<li>ns1.digitalocean.com</li>
<li>ns2.digitalocean.com</li>
<li>ns3.digitalocean.com</li>
</ul>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/a_small_orange_add_nameservers.png" alt="Add Nameservers" /></p>

<p>6. Select <strong>Change Nameservers</strong> to apply your changes. Now you are ready to move on to connecting the domain with your Droplet in the IndiaReads control panel. Check out the Conclusion section at the end of this article to read on what to do next.</p>

<h2 id="registrar-iwantmyname">Registrar: iwantmyname</h2>

<p><em>This section of the guide was last updated on October 27, 2014</em></p>

<p>1. Sign in to your iwantmyname account and select the <strong>Domains</strong> tab.</p>

<p>2. Select the domain name that you want to use with your Droplet.</p>

<p>3. Under <strong>Nameservers</strong>, select <strong>update nameservers</strong>.</p>

<p>4. Unlike many other domain registrars, iwantmyname features a menu of popular web hosts with preconfigured DNS settings.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/iwantmyname_popular_settings.png" alt="Popular settings menu" /></p>

<p>Choose <strong>IndiaReads (ns1-3.digitalocean.com)</strong> from the dropdown menu, and the fields below will be automatically filled in with the correct settings.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/iwantmyname_add_nameservers.png" alt="Add Nameservers" /></p>

<p>5. Select <strong>Update nameservers</strong> to apply your changes. Now you are ready to move on to connecting the domain with your Droplet in the IndiaReads control panel. Check out the Conclusion section at the end of this article to read on what to do next.</p>

<h2 id="registrar-google-domains-beta">Registrar: Google Domains beta</h2>

<p><em>This section of the guide was last updated on October 27, 2014</em></p>

<p>1. Sign in to your Google Domains account.</p>

<p>2. Select the domain name that you want to use with your Droplet.</p>

<p>3. Select the <strong>Advanced</strong> tab, then select <strong>Use custom name servers</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/google_domains_use_custom_name_servers.png" alt="Use custom name servers" /></p>

<p>4. Enter the following nameservers:</p>

<ul>
<li>ns1.digitalocean.com</li>
<li>ns2.digitalocean.com</li>
<li>ns3.digitalocean.com</li>
</ul>

<p><img src="https://assets.digitalocean.com/articles/point_to_nameservers/google_domains_add_nameservers.png" alt="Add Nameservers" /></p>

<p><strong>Note:</strong> You'll need to hit the <strong>+</strong> to the right of the nameserver field to make more fields visible.</p>

<p>5. Select <strong>Save</strong> to apply your changes. Now you are ready to move on to connecting the domain with your Droplet in the IndiaReads control panel. Check out the Conclusion section below to read on what to do next.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Once you have saved your changes to the nameservers listed with your domain, you will need to wait some time for the domain to propogate. This is when the domain registry communicates the nameserver changes with your Internet Service Provider, so that they can cache the new nameservers to ensure quick site connections. This process usually takes 30-45 minutes, but could take up to a few hours depending on your registry and ISP's communication methods.</p>

<p>Now that your domain has been pointed to IndiaReads's nameservers, you can move on to connecting the domain to your Droplet in the IndiaReads control panel. To do this, you can follow step three in this guide: <a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean">How To Set Up a Host Name with IndiaReads</a></p>

    