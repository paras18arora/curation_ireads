<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/DDOS_attack_tw.png?1442601109/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>CloudFlare is a company that provides content delivery network (CDN) and distributed DNS services by acting as a reverse proxy for websites. CloudFlare's free and paid services can be used to improve the security, speed, and availability of a website in a variety of ways. In this tutorial, we will show you how to use CloudFlare's free tier service to protect your web servers against ongoing HTTP-based DDoS attacks by enabling "I'm Under Attack Mode". This security mode can mitigate DDoS attacks by presenting an interstitial page to verify the legitimacy of a connection before passing it to your web server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This tutorial assumes that you have the following:</p>

<ul>
<li>A web server </li>
<li>A registered domain that points to your web server</li>
<li>Access to the control panel of the domain registrar that issued the domain</li>
</ul>

<p>You must also sign up for a CloudFlare account before continuing. Note that this tutorial will require the use of CloudFlare's nameservers.</p>

<h2 id="configure-your-domain-to-use-cloudflare">Configure Your Domain to Use CloudFlare</h2>

<p>Before using any of CloudFlare's features, you must configure your domain to use CloudFlare's DNS.</p>

<p>If you haven't already done so, log in to CloudFlare.</p>

<h3 id="add-a-website-and-scan-dns-records">Add a Website and Scan DNS Records</h3>

<p>After logging in, you will be taken to the <strong>Get Started with CloudFlare</strong> page. Here, you must add your website to CloudFlare:</p>

<p><img src="https://assets.digitalocean.com/articles/cloudflare/ddos/2-add-website.png" alt="Add a website" /></p>

<p>Enter the domain name that you want to use CloudFlare with and click the <strong>Begin Scan</strong> button. You should be taken to a page that looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/cloudflare/ddos/3-scanning-dns-records.png" alt="Scanning your DNS records" /></p>

<p>This takes about a minute. When it is complete, click the <strong>Continue</strong> button.</p>

<p>The next page shows the results of the DNS record scan. Be sure that all of your existing DNS records are present, as these are the records that CloudFlare will use to resolve requests to your domain. In our example, we used <code>cockroach.nyc</code> as the domain:</p>

<p><img src="https://assets.digitalocean.com/articles/cloudflare/ddos/4-add-dns-records.png" alt="Add DNS Records" /></p>

<p>Note that, for your A and CNAME records that point to your web server(s), the <strong>Status</strong> column should have an orange cloud with an arrow going through it. This indicates that the traffic will flow through CloudFlare's reverse proxy before hitting your server(s).</p>

<p>Next, select your CloudFlare plan. In this tutorial, we will select the <strong>Free plan</strong> option. If you want to pay for a different plan because you want additional CloudFlare features, feel free to do so:</p>

<p><img src="https://assets.digitalocean.com/articles/cloudflare/ddos/5-select-cloudflare-plan.png" alt="Select CloudFlare Plan" /></p>

<h3 id="change-your-nameservers">Change Your Nameservers</h3>

<p>The next page will display a table of your domain's current nameservers and what they should be changed to. Two of them should be changed to CloudFlare nameservers, and the remaining entries should be removed. Here is an example of what the page might look like if your domain is using the IndiaReads nameservers:</p>

<p><img src="https://assets.digitalocean.com/articles/cloudflare/ddos/6-change-your-nameservers.png" alt="Change your nameservers" /></p>

<p>To change your domain's nameservers, log in to your domain registrar control panel and make the DNS changes that CloudFlare presented. For example, if you purchased your domain through a registrar like GoDaddy or NameCheap, you will need to log into appropriate registrar's control panel and make the changes there.</p>

<p>The process varies based on your particular domain registrar. If you can't figure out how to do this, it is similar to the process described in <a href="https://indiareads/community/tutorials/how-to-point-to-digitalocean-nameservers-from-common-domain-registrars">How to Point to IndiaReads Nameservers From Common Domain Registrars</a> except you will use the CloudFlare nameservers instead of IndiaReads's.</p>

<p>In the example case, the domain is using IndiaReads's nameservers and we need to update it to use CloudFlare's DNS. The domain was registered through NameCheap so that's where we should go to update the nameservers.</p>

<p>When you are finished changing your nameservers, click the <strong>Continue</strong> button. It can take up to 24 hours for the nameservers to switch but it usually only takes several minutes.</p>

<h3 id="wait-for-nameservers-to-update">Wait for Nameservers to Update</h3>

<p>Because updating nameservers takes an unpredictable amount of time, it is likely that you will see this page next:</p>

<p><img src="https://assets.digitalocean.com/articles/cloudflare/ddos/7-pending-nameservers.png" alt="Pending nameservers" /></p>

<p>The <strong>Pending</strong> status means that CloudFlare is waiting for the nameservers to update to the ones that it prescribed (e.g. <code>olga.ns.cloudflare.com</code> and <code>rob.ns.cloudflare.com</code>). If you changed your domain's nameservers, all you have to do is wait and check back later for an <strong>Active</strong> status. If you click the <strong>Recheck Nameservers</strong> button or navigate to the CloudFlare dashboard, it will check if the nameservers have updated.</p>

<h3 id="cloudflare-is-active">CloudFlare Is Active</h3>

<p>Once the nameservers update, your domain will be using CloudFlare's DNS and you will see it has an <strong>Active</strong> status, like this:</p>

<p><img src="https://assets.digitalocean.com/articles/cloudflare/ddos/8-active.png" alt="Active Status" /></p>

<p>This means that CloudFlare is acting as a reverse proxy to your website, and you have access to whichever features are available to the pricing tier that you signed up for. If you're using the <strong>free</strong> tier, as we are in this tutorial, you will have access some of the features that can improve your site's security, speed, and availability. We won't cover all of the features in this tutorial, as we are focusing on mitigating ongoing DDoS attacks, but they include CDN, SSL, static content caching, a firewall (before the traffic reaches your server), and traffic analytics tools.</p>

<p>Also note the <strong>Settings Summary</strong>, right below your domain will show your website's current security level (medium by default) and some other information.</p>

<p>Before continuing, to get the most out of CloudFlare, you will want to follow this guide: <a href="https://support.cloudflare.com/hc/en-us/articles/201897700">Recommended First Steps for All CloudFlare Users</a>. This is important to ensure that CloudFlare will allow legitimate connections from services that you want to allow, and so that your web server logs will show the original visitor IP addresses (instead of CloudFlare's reverse proxy IP addresses).</p>

<p>Once you're all set up, let's take a look at the <strong>I'm Under Attack Mode</strong> setting in the CloudFlare firewall.</p>

<h2 id="i-39-m-under-attack-mode">I'm Under Attack Mode</h2>

<p>By default, CloudFlare's firewall security is set to <strong>Medium</strong>. This offers some protection against visitors who are rated as a moderate threat by presenting them with a challenge page before allowing them to continue to your site. However, if your site is the target of a DDoS attack, that may not be enough to keep your site operational. In this case, the <strong>I'm Under Attack Mode</strong> might be appropriate for you.</p>

<p>If you enable this mode, any visitor to your website will be presented with an interstitial page that performs some browser checks and delays the visitor for about 5 seconds before passing them to your server. It will look something like this;</p>

<p><img src="https://assets.digitalocean.com/articles/cloudflare/ddos/11-interstitial-page.png" alt="Interstitial Page" /></p>

<p>If the checks pass, the visitor will be allowed through to your website. The combination of preventing and delaying malicious visitors from connecting to your site is often enough to keep it up and running, even during a DDoS attack.</p>

<p><span class="note"><strong>Note:</strong> Visitors to the site must have JavaScript and Cookies enabled to pass the interstitial page. If this isn't acceptable, consider using the "High" firewall security setting instead.<br /></span></p>

<p>Keep in mind that you only want to have <strong>I'm Under Attack Mode</strong> enabled when your site is the victim of a DDoS attack. Otherwise, it should be turned off so it does not delay normal users from accessing your website for no reason.</p>

<h3 id="how-to-enable-i-39-m-under-attack-mode">How To Enable I'm Under Attack Mode</h3>

<p>If you want enable <strong>I'm Under Attack Mode</strong>, the easiest way is to go to the CloudFlare Overview page (the default page) and select it from the <strong>Quick Actions</strong> menu:</p>

<p><img src="https://assets.digitalocean.com/articles/cloudflare/ddos/9-quick-actions.png" alt="Under Attack Mode action" /></p>

<p>The security settings will immediately switch to <strong>I'm Under Attack</strong> status. Now, any visitors to your site will be presented with the CloudFlare interstitial page that was described above.</p>

<h3 id="how-to-disable-i-39-m-under-attack-mode">How To Disable I'm Under Attack Mode</h3>

<p>As the <strong>I'm Under Attack Mode</strong> should only be used during DDoS emergencies, you should disable it if you aren't under attack. To do so, go to the CloudFlare Overview page, and click the <strong>Disable</strong> button:</p>

<p><img src="https://assets.digitalocean.com/articles/cloudflare/ddos/10-under-attack-status.png" alt="I'm Under Attack enabled" /></p>

<p>Then select the security level that you would like to switch to. The default and generally recommended, mode is <strong>Medium</strong>:</p>

<p><img src="https://assets.digitalocean.com/articles/cloudflare/ddos/12-disable-under-attack.png" alt="Disable I'm Under Attack Mode" /></p>

<p>Your site should revert back to an <strong>Active</strong> status, and the DDoS protection page will be disabled.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that your website is using CloudFlare, you have another tool to easily protect it against HTTP-based DDoS attacks. There are also a variety of other tools that CloudFlare provides that you may be interested in setting up, like free SSL certificates. As such, it is recommended that you explore the options and see what is useful to you.</p>

<p>Good luck!</p>

    