<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>This tutorial shows how to setup a DNS redirection from your own custom domain (e.g. <a href="http://githubtest.teamerlich.org/">http://githubtest.teamerlich.org/</a>) to point to your GitHub-hosted static website (e.g. <a href="http://agordon.github.io/custom_dns_test">http://agordon.github.io/custom_dns_test</a>) using IndiaReads's DNS control panel.</p>

<p><a href="https://pages.github.com/">Github Pages</a> enable every project hosted on GitHub to have a dedicated static website for the program. Setting up a static website is explained in detail on the their website (and even include an automatic template generator to help one setup a new website).</p>

<p>The default URL for such a website is based on the user's name and the project's name. For example, if the GitHub username is <code>agordon</code> and the project's name is <code>custom_dns_test</code>, the Github repository URL will be <a href="https://github.com/agordon/custom_dns_test">https://github.com/agordon/custom_dns_test</a> and the GitHub-Pages static website will be <a href="http://agordon.github.io/custom_dns_test/">http://agordon.github.io/custom_dns_test/</a>.</p>

<p>Following the directions in this tutorial, you will setup a custom domain name (e.g. <a href="http://githubtest.teamerlich.org/">http://githubtest.teamerlich.org/</a>) which will be an autmatic alias to  <a href="http://agordon.github.io/custom_dns_test/">http://agordon.github.io/custom_dns_test/</a> - that is, users visiting the custom URL will see the content of <a href="http://agordon.github.io/custom_dns_test/">http://agordon.github.io/custom_dns_test/</a> (stored on and served by GitHub's servers) but the URL will be your custom one.</p>

<p>This article follows GitHub's <a href="https://help.github.com/articles/setting-up-a-custom-domain-with-github-pages">Custom Domain with Github Pages</a> tutorial, adapted for DigitanOcean's DNS control panel.</p>

<h2 id="pre-requisites">Pre-requisites</h2>

<p>This tutorial assumes you have the followings:</p>

<ol>
<li><p>A registered domain name (e.g. <code>teamerlich.org</code>) at a domain registrar ( such as <a href="http://www.godaddy.com">godaddy.com</a> ).</p></li>
<li><p>Proper DNS configuration in IndiaReads's nameservers.</p>

<p>See article for <a href="https://indiareads/community/articles/how-to-set-up-a-host-name-with-digitalocean">basic domain name with IndiaReads</a> and <a href="https://indiareads/community/articles/how-to-set-up-and-test-dnssubdomains-with-digitalocean-s-dns-panel">Sub-domains in IndiaReads</a>.</p></li>
<li><p>A user on <a href="http://www.github.com">GitHub</a>.</p>

<p><strong>Example:</strong> If your GitHub user is <code>agordon</code> your Github page will be <a href="https://github.com/agordon/">https://github.com/agordon/</a>.</p></li>
<li><p>A Github project which you own (and can modify).</p>

<p><strong>Example</strong>: if your GitHub project is <code>custom_dns_test</code>, your project's GitHub repository will be <a href="https://github.com/agordon/custom_dns_test">https://github.com/agordon/custom_dns_test</a>.</p></li>
<li><p>In said project, a GitHub-Pages setup using a branch named <code>gh-pages</code>. If you have not yet created a GitHub pages brunch, follow the instruction at <a href="https://pages.github.com/">https://pages.github.com/</a> (which also include an automatic website generator with beautiful templates).</p></li>
</ol>

<p><strong>Example</strong>: If your GitHub project is <code>custom_dns_test</code>, your project's GitHub Pages branch repository will be <a href="https://github.com/agordon/custom_dns_test/tree/gh-pages">https://github.com/agordon/custom_dns_test/tree/gh-pages</a>.</p>

<h2 id="step-1-decide-on-a-subdomain-name">Step 1 - Decide On A Subdomain Name.</h2>

<p>The subdomain name should be alpha-numeric. You could always change the domain name later, by repeating steps 2 & 3 with the new name.</p>

<h2 id="step-2-add-quot-cname-quot-file-to-your-github-project">Step 2 - Add "CNAME" File To Your GitHub Project</h2>

<p>In your GitHub project's <code>gh-pages</code> branch, create (or update) a file called <code>CNAME</code>. The file should contain a single line with the full domain name (e.g. <code>githubtest.teamerlich.org</code>). The name must match the domain name you'll setup in step 3.</p>

<p>Use the following commands on your local workstation to add the <code>CNAME</code> file. Replace the example with your own:</p>
<pre class="code-pre "><code class="code-highlight language-sh">cd [PROJECT-DIRECTORY]
git pull origin
git checkout gh-pages
echo "githubtest.teamerlich.org" > CNAME
git add CNAME
git commit -m "Added CNAME for GitHub Pages"
git push
</code></pre>
<p>The final result should look like the following project (note the <code>CNAME</code> file): <a href="https://github.com/agordon/custom_dns_test/tree/gh-pages">https://github.com/agordon/custom_dns_test/tree/gh-pages</a>.</p>

<p>After uploading a new <code>CNAME</code> file to github, it can take up to ten minutes for GitHub servers to be updated.</p>

<h2 id="step-3-add-dns-record-in-digitalocean-39-s-dns-control-panel">Step 3 - Add DNS Record In IndiaReads's DNS Control Panel</h2>

<p>In your IndiaReads Control Panel, select <strong>Networking</strong>, click on your domain's pencil icon (=Edit), then click <strong>Add Record</strong>:</p>

<p><img src="https://assets.digitalocean.com/articles/DNS_GitHubPages/1.png" alt="" /></p>

<p>Fill in the following items:</p>

<ol>
<li>Choose <strong>CNAME</strong> as the new DNS record</li>
<li>Enter the subdomain name (<strong>without a dot</strong>). The name must match the content of the <code>CNAME</code> file in your GitHub repository from step 2</li>
<li>Enter the domain for your GitHub pages username as the 'alias' (e.g. if your GitHub user is <code>agordon</code>, your GitHub-Pages server will be <code>agordon.github.io</code>). <strong>NOTE</strong>: the period after "io" is required</li>
<li>Click <strong>Create</strong> to add the new record</li>
</ol>

<h2 id="step-4-wait-for-digitalocean-39-s-server-to-update">Step 4 - Wait For IndiaReads's Server To Update</h2>

<p>It will take several minutes for the DNS information to be updated in IndiaReads's DNS. Wait until the zone information (gray area) shows to updated CNAME record pointing to your "github" account:</p>

<p><img src="https://assets.digitalocean.com/articles/DNS_GitHubPages/2.png" alt="" /></p>

<p>After the zone information is updated, it can still take several minutes until the change is updated in other DNS servers (e.g. your ISP's DNS server).</p>

<p>A 20 minutes delay is reasonable (though many times the updated website is available much quicker).</p>

<h2 id="examples-summary">Examples Summary</h2>

<ul>
<li>GitHub Project's Page: <a href="https://github.com/agordon/custom_dns_test">https://github.com/agordon/custom_dns_test</a></li>
<li>GitHub-Pages with github-based URL: <a href="http://agordon.github.io/custom_dns_test/">http://agordon.github.io/custom_dns_test/</a></li>
<li>Same content as above with custom URL <a href="http://githubtest.teamerlich.org/">http://githubtest.teamerlich.org/</a></li>
<li>GitHub project's <code>gh-pages</code> branch: <a href="https://github.com/agordon/custom_dns_test/tree/gh-pages">https://github.com/agordon/custom_dns_test/tree/gh-pages</a></li>
<li><code>CNAME</code> file containing the custom URL: <a href="https://github.com/agordon/custom_dns_test/blob/gh-pages/CNAME">https://github.com/agordon/custom_dns_test/blob/gh-pages/CNAME</a></li>
</ul>

<h2 id="further-information">Further information</h2>

<ul>
<li>GitHub Pages - <a href="https://pages.github.com/">https://pages.github.com/</a></li>
<li>GitHub Pages Custom DNS - <a href="https://help.github.com/articles/setting-up-a-custom-domain-with-github-pages">https://help.github.com/articles/setting-up-a-custom-domain-with-github-pages</a></li>
<li>IndiaReads's DNS basics - <a href="https://indiareads/community/articles/how-to-set-up-a-host-name-with-digitalocean">https://indiareads/community/articles/how-to-set-up-a-host-name-with-digitalocean</a></li>
<li>IndiaReads's DNS subdomains - <a href="https://indiareads/community/articles/how-to-set-up-and-test-dns-subdomains-with-digitalocean-s-dns-panel">https://indiareads/community/articles/how-to-set-up-and-test-dns-subdomains-with-digitalocean-s-dns-panel</a></li>
</ul>

<div class="author">Submitted by: <a href="https://github.com/agordon">Assaf Gordon</a></div>

    