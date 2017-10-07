<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>If you are planning to run a CoreOS cluster in a network environment outside of your control, such as within a shared datacenter or across the public internet, you may have noticed that <code>etcd</code> communicates by making unencrypted HTTP requests.  It's possible to mitigate the risks of that behavior by configuring an IPTables firewall on each node in the cluster, but a complete solution would ideally use an encrypted transport layer.</p>

<p>Fortunately, <code>etcd</code> supports peer-to-peer TLS/SSL connections, so that each member of a cluster is authenticated and all communication is encrypted.  In this guide, we'll begin by provisioning a simple cluster with three members, then configure HTTPS endpoints and a basic firewall on each machine.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This guide builds heavily on concepts discussed in <a href="https://indiareads/community/tutorials/an-introduction-to-coreos-system-components">this introduction to CoreOS system components</a> and <a href="https://indiareads/community/tutorials/how-to-set-up-a-coreos-cluster-on-digitalocean">this guide to setting up a CoreOS cluster on IndiaReads</a>.</p>

<p>You should be familiar with the basics of <code>etcd</code>, <code>fleetctl</code>, <code>cloud-config</code> files, and generating a discovery URL.</p>

<p>In order to create and access the machines in your cluster, you'll need an SSH public key associated with your IndiaReads account.  For detailed information about using SSH keys with IndiaReads, <a href="https://indiareads/community/tutorials/how-to-use-ssh-keys-with-digitalocean-droplets">see here</a>.</p>

<p>If you want to use the IndiaReads API to create your CoreOS machines, refer to <a href="https://indiareads/community/tutorials/how-to-use-the-digitalocean-api-v2#how-to-generate-a-personal-access-token">this tutorial</a> for information on how to generate and use a Personal Access Token with write permissions.  Use of the API is optional, but may save you time in the long run, particularly if you anticipate building larger clusters.</p>

<h2 id="generate-a-new-discovery-url">Generate a New Discovery URL</h2>

<p>Retrieve a new discovery URL from discovery.etcd.io, either by visiting <a href="https://discovery.etcd.io/new?size=3">https://discovery.etcd.io/new?size=3</a> in your browser and copying the URL displayed, or by using <code>curl</code> from the terminal on your local machine:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">curl -w "\n" "https://discovery.etcd.io/new?size=3"
</li></ul></code></pre>
<p>Save the returned URL; we'll use it in our <code>cloud-config</code> shortly.</p>

<h2 id="write-a-cloud-config-file-including-https-configuration">Write a Cloud-Config File Including HTTPS Configuration</h2>

<p>We'll start by writing a <code>cloud-config</code>.  The <code>cloud-config</code> will be supplied as <strong>user data</strong> when initializing each server, defining important configuration details for the cluster.  This file will be long, but shouldn't wind up much more complicated than the version in the <a href="https://indiareads/community/tutorials/how-to-set-up-a-coreos-cluster-on-digitalocean">basic cluster guide</a>.  We'll tell <code>fleet</code> explicitly to use HTTPS endpoints, enable a service called <code>iptables-restore</code> for our firewall, and write out configuration files telling <code>etcd</code> and <code>fleet</code> where to find SSL certificates.</p>

<p>Open a terminal on your local machine, make sure you're in your home directory, and use <code>nano</code> (or your favorite text editor) to create and open <code>~/cloud-config.yml</code>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">cd ~
</li><li class="line" prefix="local$">nano cloud-config.yml
</li></ul></code></pre>
<p>Paste the following, then change <code><span class="highlight">https://discovery.etcd.io/token</span></code> in the <code>etcd2</code> section to the discovery URL you claimed in the last section.</p>

<p>You can also remove the <code>iptables-restore</code> section, if you don't want to enable a firewall.</p>

<p>Be careful with indentation when pasting.  The <code>cloud-config</code> is written in YAML, which is sensitive to whitespace.  See comments within the file for info on specific lines, then we'll go over some important sections in greater detail.</p>
<div class="code-label " title="~/cloud-config.yml">~/cloud-config.yml</div><pre class="code-pre "><code langs="">#cloud-config

coreos:
  etcd2:
    # generate a new token for each unique cluster from https://discovery.etcd.io/new:
    discovery: <span class="highlight">https://discovery.etcd.io/token</span>
    # multi-region deployments, multi-cloud deployments, and Droplets without
    # private networking need to use $public_ipv4:
    advertise-client-urls: https://$private_ipv4:2379,https://$private_ipv4:4001
    initial-advertise-peer-urls: https://$private_ipv4:2380
    # listen on the official ports 2379, 2380 and one legacy port 4001:
    listen-client-urls: https://0.0.0.0:2379,https://0.0.0.0:4001
    listen-peer-urls: https://$private_ipv4:2380
  fleet:
    # fleet defaults to plain HTTP - explicitly tell it to use HTTPS on port 4001:
    etcd_servers: https://$private_ipv4:4001
    public-ip: $private_ipv4   # used for fleetctl ssh command
  units:
    - name: etcd2.service
      command: start
    - name: fleet.service
      command: start
    <span class="highlight"># enable and start iptables-restore</span>
    <span class="highlight">- name: iptables-restore.service</span>
      <span class="highlight">enable: true</span>
      <span class="highlight">command: start</span>
write_files:
  # tell etcd2 and fleet where our certificates are going to live:
  - path: /run/systemd/system/etcd2.service.d/30-certificates.conf
    permissions: 0644
    content: |
      [Service]
      # client environment variables
      Environment=ETCD_CA_FILE=/home/core/ca.pem
      Environment=ETCD_CERT_FILE=/home/core/coreos.pem
      Environment=ETCD_KEY_FILE=/home/core/coreos-key.pem
      # peer environment variables
      Environment=ETCD_PEER_CA_FILE=/home/core/ca.pem
      Environment=ETCD_PEER_CERT_FILE=/home/core/coreos.pem
      Environment=ETCD_PEER_KEY_FILE=/home/core/coreos-key.pem
  - path: /run/systemd/system/fleet.service.d/30-certificates.conf
    permissions: 0644
    content: |
      [Service]
      # client auth certs
      Environment=FLEET_ETCD_CAFILE=/home/core/ca.pem
      Environment=FLEET_ETCD_CERTFILE=/home/core/coreos.pem
      Environment=FLEET_ETCD_KEYFILE=/home/core/coreos-key.pem

</code></pre>
<p>As an optional step, you can paste your <code>cloud-config</code> into <a href="https://coreos.com/validate/">the official CoreOS Cloud Config Validator</a> and press <strong>Validate Cloud-Config</strong>.</p>

<p>Save the file and exit.  In <code>nano</code>, you can accomplish this with <strong>Ctrl-X</strong> to exit, <strong>y</strong> to confirm writing the file, and <strong>Enter</strong> to confirm the filename to save.</p>

<p>Let's look at a handful of specific blocks from <code>cloud-init.yml</code>.  First, the <code>fleet</code> values:</p>
<pre class="code-pre "><code langs="">  fleet:
    # fleet defaults to plain HTTP - explicitly tell it to use HTTPS:
    etcd_servers: https://$private_ipv4:4001
    public-ip: $private_ipv4   # used for fleetctl ssh command
</code></pre>
<p>Notice that <code>etcd_servers</code> is set to an <code>https</code> URL.  For plain HTTP operation, this value doesn't need to be set.  Without explicit configuration, however, HTTPS will fail.  (<code>$private_ipv4</code> is a variable understood by the CoreOS initialization process, not one you need to change.)</p>

<p>Next we come to the <code>write_files</code> block.  Values are broken into a filesystem <code>path</code>, <code>permissions</code> mask, and <code>content</code>, which contains the desired contents of a file.  Here, we specify that <code>systemd</code> unit files for the <code>etcd2</code> and <code>fleet</code> services should set up environment variables pointing to the TLS/SSL certificates we'll be generating:</p>
<pre class="code-pre "><code langs="">write_files:
  # tell etcd2 and fleet where our certificates are going to live:
  - path: /run/systemd/system/etcd2.service.d/30-certificates.conf
    permissions: 0644
    content: |
      [Service]
      # client environment variables
      Environment=ETCD_CA_FILE=/home/core/ca.pem
      ...
  - path: /run/systemd/system/fleet.service.d/30-certificates.conf
    permissions: 0644
    content: |
      [Service]
      # client auth certs
      Environment=FLEET_ETCD_CAFILE=/home/core/ca.pem
      ...
</code></pre>
<p>While we tell the services where to find certificate files, we can't yet provide the files themselves.  In order to that, we'll need to know the private IP address of each CoreOS machine, which is only available once the machines have been created.</p>

<p><span class="note"><strong>Note:</strong> On CoreOS Droplets, the contents of <code>cloud-config</code> cannot be changed after the Droplet is created, and the file is re-executed on every boot.  You should avoid using the <code>write-files</code> section for any configuration you plan to modify after your cluster is built, since it will be reset the next time the Droplet starts up.<br /></span></p>

<h2 id="provision-droplets">Provision Droplets</h2>

<p>Now that we have a <code>cloud-config.yml</code> defined, we'll use it to provision each member of the cluster.  On IndiaReads, there are two basic approaches we can take:  Via the web-based Control Panel, or making calls to the IndiaReads API using cURL from the command line.</p>

<h3 id="using-the-digitalocean-control-panel">Using the IndiaReads Control Panel</h3>

<p>Create three new CoreOS Droplets within the same datacenter region.  Make sure to check <strong>Private Networking</strong> and <strong>Enable User Data</strong> each time.</p>

<ul>
<li><strong>coreos-1</strong></li>
<li><strong>coreos-2</strong></li>
<li><strong>coreos-3</strong></li>
</ul>

<p>In the <strong>User Data</strong> field, paste the contents of <code>cloud-config.yml</code> from above, making sure you've inserted your discovery URL in the <code>discovery</code> field near the top of the file.</p>

<h3 id="using-the-digitalocean-api">Using the IndiaReads API</h3>

<p>As an alternative approach which may save repetitive pasting into fields, we can write a short Bash script which uses <code>curl</code> to request a new Droplet from the IndiaReads API with our <code>cloud-config</code>, and invoke it once for each Droplet.  Open a new file called <code>makecoreos.sh</code> with <code>nano</code> (or your text editor of choice):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">cd ~
</li><li class="line" prefix="local$">nano makecoreos.sh
</li></ul></code></pre>
<p>Paste and save the following script, adjusting the <code>region</code> and <code>size</code> fields as-desired for your cluster (the defaults of <code>nyc3</code> and <code>512mb</code> are fine for demonstration purposes, but you may want a different region or bigger Droplets for real-world projects):</p>
<div class="code-label " title="~/makecoreos.sh">~/makecoreos.sh</div><pre class="code-pre "><code langs="">#!/usr/bin/env bash

# A basic Droplet create request.
curl -X POST "https://api.digitalocean.com/v2/droplets" \
     -d'{"name":"'"$1"'","region":"<span class="highlight">nyc3</span>","size":"<span class="highlight">512mb</span>","private_networking":true,"image":"coreos-stable","user_data":
"'"$(cat ~/cloud-config.yml)"'",
         "ssh_keys":[ "'$DO_SSH_KEY_FINGERPRINT'" ]}' \
     -H "Authorization: Bearer $TOKEN" \
     -H "Content-Type: application/json"
</code></pre>
<p>Now, let's set the environment variables <code>$DO_SSH_KEY_FINGERPRINT</code> and <code>$TOKEN</code> to the fingerprint of an SSH key associated with your IndiaReads account and your API Personal Access Token, respectively.</p>

<p>For information about getting a Personal Access Token and using the API, refer to <a href="https://indiareads/community/tutorials/how-to-use-the-digitalocean-api-v2">this tutorial</a>.</p>

<p>In order to find the fingerprint of a key associated with your account, check <a href="https://cloud.digitalocean.com/settings/security">the <strong>Security</strong> section of your account settings</a>, under <strong>SSH Keys</strong>.  It will be in the form of a <a href="https://en.wikipedia.org/wiki/Public_key_fingerprint">public key fingerprint</a>, something like <code>43:51:43:a1:b5:fc:8b:b7:0a:3a:a9:b1:0f:66:73:a8</code>.</p>

<p>We use <code>export</code> here so that child processes of the shell, like <code>makecoreos.sh</code>, will be able to access the variables.  Both must be set in the current shell any time the script is used, or the API call will fail:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">export DO_SSH_KEY_FINGERPRINT="<span class="highlight">ssh_key_fingerprint</span>"
</li><li class="line" prefix="local$">export TOKEN="<span class="highlight">your_personal_access_token</span>"
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> If you've just generated a Personal Access Token for the API, remember to keep it handy and secure.  There's no way to retrieve it after it's shown to you on first creation, and anyone with the token can control your IndiaReads account.<br /></span></p>

<p>Once we've set environment variables for each of the required credentials, we can run the script to create each desired Droplet.  <code>makecoreos.sh</code> uses its first parameter to fill out the <code>name</code> field in its call to the API:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">bash makecoreos.sh coreos-1
</li><li class="line" prefix="local$">bash makecoreos.sh coreos-2
</li><li class="line" prefix="local$">bash makecoreos.sh coreos-3
</li></ul></code></pre>
<p>You should see JSON output describing each new Droplet, and all three should appear in your list of Droplets in the Control Panel.  It may take a few seconds for them to finish booting.</p>

<h2 id="log-in-to-coreos-1">Log in to coreos-1</h2>

<p>Whether you used the Control Panel or the API, you should now have three running Droplets.  Now is a good time to make note of their public and private IPs, which are available by clicking on an individual Droplet in the Control Panel, then clicking on the <strong>Settings</strong> link.  The private IP address of each Droplet will be needed when generating certificates and configuring a firewall.</p>

<p>Let's test a Droplet.  Make sure that your SSH key is added to your local SSH agent:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">eval $(ssh-agent)
</li><li class="line" prefix="local$">ssh-add
</li></ul></code></pre>
<p>Find the public IP address of <strong>coreos-1</strong> in the IndiaReads Control Panel, and connect with SSH agent forwarding turned on:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">ssh -A core@<span class="highlight">coreos-1_public_ip</span>
</li></ul></code></pre>
<p>On first login to any member of the cluster, we are likely to receive an error message from <code>systemd</code>:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>CoreOS stable (766.5.0)
Failed Units: 1
  iptables-restore.service
</code></pre>
<p>This indicates that the firewall hasn't yet been configured.  For now, it's safe to ignore this message.  (If you elected not to enable the firewall in your <code>cloud-config</code>, you won't see an error message.  You can always enable the <code>iptables-restore</code> service later.)</p>

<p>Before we worry about the firewall, let's get the <code>etcd2</code> instances on each member of the cluster talking to one another.</p>

<h2 id="use-cfssl-to-generate-self-signed-certificates">Use CFSSL to Generate Self-Signed Certificates</h2>

<p><a href="https://github.com/cloudflare/cfssl">CFSSL</a> is a toolkit for working with TLS/SSL certificates, published by CloudFlare.  At the time of this writing, it's the CoreOS maintainers' chosen tool for generating self-signed certificates, in preference to OpenSSL and the now-deprecated <code>etcd-ca</code>.</p>

<h3 id="install-cfssl-on-your-local-machine">Install CFSSL on Your Local Machine</h3>

<p>CFSSL requires a working Go installation to install from source.  See <a href="https://indiareads/community/tutorials/how-to-install-go-1-5-1-on-ubuntu-14-04">this guide to installing Go</a>.</p>

<p>Make sure your <code>$GOPATH</code> is set correctly and added to your <code>$PATH</code>, then use <code>go get</code> to install the <code>cfssl</code> commands:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">export GOPATH=~/gocode
</li><li class="line" prefix="local$">export PATH=$PATH:$GOPATH/bin
</li><li class="line" prefix="local$">go get -u github.com/cloudflare/cfssl/cmd/cfssl
</li><li class="line" prefix="local$">go get -u github.com/cloudflare/cfssl/...
</li></ul></code></pre>
<p>As an alternative approach, pre-built binaries can be retrieved from <a href="https://pkg.cfssl.org/">pkg.cfssl.org</a>.  First make sure that <code>~/bin</code> exists and is in your path:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">mkdir -p ~/bin
</li><li class="line" prefix="local$">export PATH=$PATH:~/bin
</li></ul></code></pre>
<p>Then use <code>curl</code> to retrieve the latest versions of <code>cfssl</code> and <code>cfssljson</code> for your platform:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">curl -s -L -o ~/bin/cfssl https://pkg.cfssl.org/<span class="highlight">R1.1/cfssl_linux-amd64</span>
</li><li class="line" prefix="local$">curl -s -L -o ~/bin/cfssljson https://pkg.cfssl.org/<span class="highlight">R1.1/cfssljson_linux-amd64</span>
</li></ul></code></pre>
<p>Make sure the <code>cfssl</code> binaries are executable:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">chmod +x ~/bin/cfssl
</li><li class="line" prefix="local$">chmod +x ~/bin/cfssljson
</li></ul></code></pre>
<h3 id="generate-a-certificate-authority">Generate a Certificate Authority</h3>

<p>Now that the <code>cfssl</code> commands are installed, we can use them to generate a custom Certificate Authority which we'll use to sign certificates for each of our CoreOS machines.  Let's start by making and entering a fresh directory to stash these files in:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">mkdir ~/coreos_certs
</li><li class="line" prefix="local$">cd ~/coreos_certs
</li></ul></code></pre>
<p>Now, create and open <code>ca-config.json</code> in <code>nano</code> (or your favorite text editor):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">nano ca-config.json
</li></ul></code></pre>
<p>Paste and save the following, which configures how <code>cfssl</code> will do signing:</p>
<div class="code-label " title="~/coreos_certs/ca-config.json">~/coreos_certs/ca-config.json</div><pre class="code-pre "><code langs="">{
    "signing": {
        "default": {
            "expiry": "43800h"
        },
        "profiles": {
            "client-server": {
                "expiry": "43800h",
                "usages": [
                    "signing",
                    "key encipherment",
                    "server auth",
                    "client auth"
                ]
            }
        }
    }
}
</code></pre>
<p>Of note here are the <code>expiry</code>, currently set to 43800 hours (or 5 years), and the <code>client-server</code> profile, which includes both <code>server auth</code> and <code>client auth</code> usages.  We need both of these for peer-to-peer TLS.</p>

<p>Next, create and open <code>ca-csr.json</code>.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">nano ca-csr.json
</li></ul></code></pre>
<p>Paste the following, adjusting <code>CN</code> and the <code>names</code> array as desired for your location and organization.  It's safe to use fictional values for the <code>hosts</code> entry as well as place and organization names:</p>
<div class="code-label " title="~/coreos_certs/ca-csr.json">~/coreos_certs/ca-csr.json</div><pre class="code-pre "><code langs="">{
    "CN": "My Fake CA",
    "hosts": [
        "example.net",
        "www.example.net"
    ],
    "key": {
        "algo": "rsa",
        "size": 2048
    },
    "names": [
        {
            "C": "US",
            "L": "CO",
            "O": "My Company",
            "ST": "Lyons",
            "OU": "Some Org Unit"
        }
    ]
}
</code></pre>
<span class="note"><p>
If you want to compare these with default values for <code>ca-config.json</code> and <code>ca-csr.json</code>, you can print defaults with <code>cfssl</code>.  For <code>ca-config.json</code>, use:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">cfssl print-defaults config
</li></ul></code></pre>
<p>For <code>ca-csr.json</code>, use:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">cfssl print-defaults csr
</li></ul></code></pre>
<p></p></span>

<p>With <code>ca-csr.json</code> and <code>ca-config.json</code> in place, generate the Certificate Authority:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">cfssl gencert -initca ca-csr.json | cfssljson -bare ca -
</li></ul></code></pre>
<h3 id="generate-and-sign-certificates-for-coreos-machines">Generate and Sign Certificates for CoreOS Machines</h3>

<p>Now that we have a Certificate Authority, we can write defaults for a CoreOS machine:</p>

<p>Create and open <code>coreos-1.json</code>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">nano coreos-1.json
</li></ul></code></pre>
<p>Paste and save the following, adjusting it for the private IP address of <strong>coreos-1</strong> (visible in the IndiaReads Control Panel by clicking on an individual Droplet):</p>
<div class="code-label " title="~/coreos_certs/coreos-1.json">~/coreos_certs/coreos-1.json</div><pre class="code-pre "><code langs="">{
    "CN": "<span class="highlight">coreos-1</span>",
    "hosts": [
        "<span class="highlight">coreos-1</span>",
        "<span class="highlight">coreos-1</span>.local",
        "127.0.0.1",
        "<span class="highlight">coreos-1_private_ip</span>"
    ],
    "key": {
        "algo": "rsa",
        "size": 2048
    },
    "names": [
        {
            "C": "<span class="highlight">US</span>",
            "L": "<span class="highlight">Lyons</span>",
            "ST": "<span class="highlight">Colorado</span>"
        }
    ]
}
</code></pre>
<p>The most important parts are <code>CN</code>, which should be your hostname, and the <code>hosts</code> array, which must contain all of:</p>

<ul>
<li>your local hostname(s)</li>
<li><code>127.0.0.1</code></li>
<li>the CoreOS machine's private IP address (not its public-facing IP)</li>
</ul>

<p>These will be added to the resulting certificate as <strong><a href="https://en.wikipedia.org/wiki/SubjectAltName">subjectAltNames</a></strong>.  <code>etcd</code> connections (including to the local loopback device at <code>127.0.0.1</code>) require the certificate to have a SAN matching the connecting hostname.</p>

<p>You can also change the <code>names</code> array to reflect your location, if desired.  Again, it's safe to use fictional values for placenames.</p>

<p>Repeat this process for each remaining machine, creating a matching <code>coreos-2.json</code> and <code>coreos-3.json</code> with the appropriate <code>hosts</code> entries.</p>

<span class="note"><p>
<strong>Note:</strong> If you'd like to take a look at default values for <code>coreos-1.json</code>, you can use <code>cfssl</code>:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">cfssl print-defaults csr
</li></ul></code></pre>
<p></p></span>

<p>Now, for each CoreOS machine, generate a signed certificate and upload it to the correct machine:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">cfssl gencert -ca=ca.pem -ca-key=ca-key.pem -config=ca-config.json -profile=client-server <span class="highlight">coreos-1.json</span> | cfssljson -bare coreos
</li><li class="line" prefix="local$">chmod 0644 coreos-key.pem
</li><li class="line" prefix="local$">scp ca.pem coreos-key.pem coreos.pem core@<span class="highlight">coreos-1_public_ip</span>:
</li></ul></code></pre>
<p>This will create three files (<code>ca.pem</code>, <code>coreos-key.pem</code>, and <code>coreos.pem</code>), make sure permissions are correct on the keyfile, and copy them via <code>scp</code> to <strong>core</strong>'s home directory on <strong>coreos-1</strong>.</p>

<p>Repeat this process for each of the remaining machines, keeping in mind that each invocation of the command will overwrite the previous set of certificate files:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">cfssl gencert -ca=ca.pem -ca-key=ca-key.pem -config=ca-config.json -profile=client-server <span class="highlight">coreos-2.json</span> | cfssljson -bare coreos
</li><li class="line" prefix="local$">chmod 0644 coreos-key.pem
</li><li class="line" prefix="local$">scp ca.pem coreos-key.pem coreos.pem core@<span class="highlight">coreos-2_public_ip</span>:
</li></ul></code></pre><pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">cfssl gencert -ca=ca.pem -ca-key=ca-key.pem -config=ca-config.json -profile=client-server coreos-3.json | cfssljson -bare coreos
</li><li class="line" prefix="local$">chmod 0644 coreos-key.pem
</li><li class="line" prefix="local$">scp ca.pem coreos-key.pem coreos.pem core@<span class="highlight">coreos-3_public_ip</span>:
</li></ul></code></pre>
<h2 id="check-etcd2-functionality-on-coreos-1">Check etcd2 Functionality on coreos-1</h2>

<p>With certificates in place, we should be able to run <code>fleetctl</code> on <strong>coreos-1</strong>.  First, log in via SSH:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">ssh -A core@<span class="highlight">coreos-1_public_ip</span>
</li></ul></code></pre>
<p>Next, try listing all the machines in the cluster:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">fleetctl list-machines
</li></ul></code></pre>
<p>You should see an identifier for each machine listed along with its private IP address:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>MACHINE     IP      METADATA
7cb57440... 10.132.130.187  -
d91381d4... 10.132.87.87    -
eeb8726f... 10.132.32.222   -
</code></pre>
<p>If <code>fleetctl</code> hangs indefinitely, it may be necessary to restart the cluster. Exit to your local machine:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">exit
</li></ul></code></pre>
<p>Use SSH to send <code>reboot</code> commands to each CoreOS machine:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">ssh core@coreos-1_public_ip 'sudo reboot'
</li><li class="line" prefix="local$">ssh core@coreos-2_public_ip 'sudo reboot'
</li><li class="line" prefix="local$">ssh core@coreos-3_public_ip 'sudo reboot'
</li></ul></code></pre>
<p>Wait a few moments, re-connect to <strong>coreos-1</strong>, and try <code>fleetctl</code> again.</p>

<h2 id="configure-an-iptables-firewall-on-cluster-members">Configure an IPTables Firewall on Cluster Members</h2>

<p>With certificates in place, it should be impossible for other machines on the local network to control your cluster or extract values from <code>etcd2</code>.  Nevertheless, it's a good idea to reduce the available attack surface if possible.  In order to limit our network exposure, we can add some simple firewall rules to each machine, blocking most local network traffic from sources other than peers in the cluster.  </p>

<p>Remember that, if we enabled the <code>iptables-restore</code> service in <code>cloud-config</code>, we'll see a <code>systemd</code> error message when first logging in to a CoreOS machine:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>CoreOS stable (766.5.0)
Failed Units: 1
  iptables-restore.service
</code></pre>
<p>This lets us know that, although the service is enabled, <code>iptables-restore</code> failed to load correctly.  We can diagnose this by using <code>systemctl</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">systemctl status -l iptables-restore
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>● iptables-restore.service - Restore iptables firewall rules
   Loaded: loaded (/usr/lib64/systemd/system/iptables-restore.service; enabled; vendor preset: disabled)
   Active: failed (Result: exit-code) since Wed 2015-11-25 00:01:24 UTC; 27min ago
  Process: 689 ExecStart=/sbin/iptables-restore /var/lib/iptables/rules-save (code=exited, status=1/FAILURE)
 Main PID: 689 (code=exited, status=1/FAILURE)

Nov 25 00:01:24 coreos-2 systemd[1]: Starting Restore iptables firewall rules...
Nov 25 00:01:24 coreos-2 systemd[1]: iptables-restore.service: Main process exited, code=exited, status=1/FAILURE
Nov 25 00:01:24 coreos-2 systemd[1]: Failed to start Restore iptables firewall rules.
<span class="highlight">Nov 25 00:01:24 coreos-2 iptables-restore[689]: Can't open /var/lib/iptables/rules-save: No such file or directory</span>
Nov 25 00:01:24 coreos-2 systemd[1]: iptables-restore.service: Unit entered failed state.
Nov 25 00:01:24 coreos-2 systemd[1]: iptables-restore.service: Failed with result 'exit-code'.
</code></pre>
<p>There's a lot of information here, but the most useful line is the one containing <code>iptables-restore[689]</code>, which is the name of the process <code>systemd</code> attempted to run along with its process id.  This is where we'll often find the actual error output of failed services.</p>

<p>The firewall failed to restore because, while we enabled <code>iptables-restore</code> in <code>cloud-config</code>, we haven't yet provided it with a file containing our desired rules.  We could have done this before we created the Droplets, except that there's no way to know what IP addresses will be allocated to a Droplet before its creation.  Now that we know each private IP, we can write a ruleset.</p>

<p>Open a new file in your editor, paste the following, and replace <code><span class="highlight">coreos-1_private_ip</span></code>, <code><span class="highlight">coreos-2_private_ip</span></code>, and <code><span class="highlight">coreos-3_private_ip</span></code> with the private IP address of each CoreOS machine.  You may also need to adjust the section beneath <code>Accept all TCP/IP traffic...</code> to reflect public services you intend to offer from the cluster, although this version should work well for demonstration purposes.</p>
<div class="code-label " title="/var/lib/iptables/rules-save">/var/lib/iptables/rules-save</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">*filter
</li><li class="line" prefix="2">:INPUT DROP [0:0]
</li><li class="line" prefix="3">:FORWARD DROP [0:0]
</li><li class="line" prefix="4">:OUTPUT ACCEPT [0:0]
</li><li class="line" prefix="5">
</li><li class="line" prefix="6"># Accept all loopback (local) traffic:
</li><li class="line" prefix="7">-A INPUT -i lo -j ACCEPT
</li><li class="line" prefix="8">
</li><li class="line" prefix="9"># Accept all traffic on the local network from other members of
</li><li class="line" prefix="10"># our CoreOS cluster:
</li><li class="line" prefix="11">-A INPUT -i eth1 -p tcp -s <span class="highlight">coreos-1_private_ip</span> -j ACCEPT
</li><li class="line" prefix="12">-A INPUT -i eth1 -p tcp -s <span class="highlight">coreos-2_private_ip</span> -j ACCEPT
</li><li class="line" prefix="13">-A INPUT -i eth1 -p tcp -s <span class="highlight">coreos-3_private_ip</span> -j ACCEPT
</li><li class="line" prefix="14">
</li><li class="line" prefix="15"># Keep existing connections (like our SSH session) alive:
</li><li class="line" prefix="16">-A INPUT -m conntrack --ctstate RELATED,ESTABLISHED -j ACCEPT
</li><li class="line" prefix="17">
</li><li class="line" prefix="18"># Accept all TCP/IP traffic to SSH, HTTP, and HTTPS ports - this should
</li><li class="line" prefix="19"># be customized  for your application:
</li><li class="line" prefix="20"><span class="highlight">-A INPUT -p tcp -m tcp --dport 22 -j ACCEPT</span>
</li><li class="line" prefix="21"><span class="highlight">-A INPUT -p tcp -m tcp --dport 80 -j ACCEPT</span>
</li><li class="line" prefix="22"><span class="highlight">-A INPUT -p tcp -m tcp --dport 443 -j ACCEPT</span>
</li><li class="line" prefix="23">
</li><li class="line" prefix="24"># Accept pings:
</li><li class="line" prefix="25">-A INPUT -p icmp -m icmp --icmp-type 0 -j ACCEPT
</li><li class="line" prefix="26">-A INPUT -p icmp -m icmp --icmp-type 3 -j ACCEPT
</li><li class="line" prefix="27">-A INPUT -p icmp -m icmp --icmp-type 11 -j ACCEPT
</li><li class="line" prefix="28">COMMIT
</li><li class="line" prefix="29">
</li></ul></code></pre>
<p>Copy the above to your clipboard, log in to <strong>coreos-1</strong>, and open <code>rules-save</code> using <a href="https://indiareads/community/tutorials/installing-and-using-the-vim-text-editor-on-a-cloud-server">Vim</a>, the default text editor on CoreOS:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">ssh -A core@<span class="highlight">coreos-1_public_ip</span>
</li></ul></code></pre><pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /var/lib/iptables/rules-save
</li></ul></code></pre>
<p>Once inside the editor, type <code>:set paste</code> and press <strong>Enter</strong> to make sure that auto-indentation is turned off, then press <strong>i</strong> to enter insert mode and paste your firewall rules.  Press <strong>Esc</strong> to leave insert mode and <strong>:wq</strong> to write the file and quit.</p>

<p><span class="warning"><strong>Warning:</strong> Make sure there's a trailing newline on the last line of the file, or IPTables may fail with confusing syntax errors, despite all commands in the file appearing correct.<br /></span></p>

<p>Finally, make sure that the file has appropriate permissions (read and write for user, read-only for group and world):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod 0644 /var/lib/iptables/rules-save
</li></ul></code></pre>
<p>Now we should be ready to try the service again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start iptables-restore
</li></ul></code></pre>
<p>If successful, <code>systemctl</code> will exit silently.  We can check the status of the firewall in two ways.  First, by using <code>systemctl status</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl status -l iptables-restore
</li></ul></code></pre>
<p>And secondly by listing the current <code>iptables</code> rules themselves:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -v -L
</li></ul></code></pre>
<p>We use the <code>-v</code> option to get verbose output, which will let us know what interface a given rule applies to.</p>

<p>Once you're confident that the firewall on <strong>coreos-1</strong> is configured, log out:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">exit
</li></ul></code></pre>
<p>Next, repeat this process to install <code>/var/lib/iptables/rules-save</code> on <strong>coreos-2</strong> and <strong>coreos-3</strong>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>In this guide, we've defined a basic CoreOS cluster with three members, providing each with a TLS/SSL certificate for authentication and transport security, and used a firewall to block connections from other Droplets on the local data center network.  This helps mitigate many of the basic security concerns involved in using CoreOS on a shared network.</p>

<p>From here, you can apply the techniques in the rest of <a href="https://indiareads/community/tutorial_series/getting-started-with-coreos-2">this series on getting started with CoreOS</a> to define and manage services.</p>

    