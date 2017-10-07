<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Java-Keytool_twitter.png?1426699746/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Java Keytool is a key and certificate management tool that is used to manipulate Java Keystores, and is included with Java. A Java Keystore is a container for authorization certificates or public key certificates, and is often used by Java-based applications for encryption, authentication, and serving over HTTPS. Its entries are protected by a keystore password. A keystore entry is identified by an <em>alias</em>, and it consists of keys and certificates that form a trust chain.</p>

<p>This cheat sheet-style guide provides a quick reference to <code>keytool</code> commands that are commonly useful when working with Java Keystores. This includes creating and modifying Java Keystores so they can be used with your Java applications.</p>

<p><strong>How to Use This Guide:</strong></p>

<ul>
<li>If you are not familiar with certificate signing requests (CSRs), read the CSR section of our <a href="https://indiareads/community/tutorials/openssl-essentials-working-with-ssl-certificates-private-keys-and-csrs/">OpenSSL cheat sheet</a></li>
<li>This guide is in a simple, cheat sheet format--self-contained command line snippets</li>
<li>Jump to any section that is relevant to the task you are trying to complete (Hint: use the <em>Contents</em> menu on the bottom-left or your browser's <em>Find</em> function)</li>
<li>Most of the commands are one-liners that have been expanded to multiple lines (using the <code>\</code> symbol) for clarity</li>
</ul>

<h2 id="creating-and-importing-keystore-entries">Creating and Importing Keystore Entries</h2>

<p>This section covers Java Keytool commands that are related to generating key pairs and certificates, and importing certificates.</p>

<h3 id="generate-keys-in-new-existing-keystore">Generate Keys in New/Existing Keystore</h3>

<p>Use this method if you want to use HTTP (HTTP over TLS) to secure your Java application. This will create a new key pair in a new or existing Java Keystore, which can be used to create a CSR, and obtain an SSL certificate from a Certificate Authority.</p>

<p>This command generates a 2048-bit RSA key pair, under the specified alias (<code>domain</code>), in the specified keystore file (<code>keystore.jks</code>):</p>
<pre class="code-pre "><code langs="">keytool -genkeypair \
        -alias <span class="highlight">domain</span> \
        -keyalg RSA \
        -keystore <span class="highlight">keystore.jks</span>
</code></pre>
<p>If the specified keystore does not already exist, it will be created after the requested information is supplied. This will prompt for the keystore password (new or existing), followed by a Distinguished Name prompt (for the private key), then the desired private key password.</p>

<h3 id="generate-csr-for-existing-private-key">Generate CSR For Existing Private Key</h3>

<p>Use this method if you want to generate an CSR that you can send to a CA to request the issuance of a CA-signed SSL certificate. It requires that the keystore and alias already exist; you can use the previous command to ensure this.</p>

<p>This command creates a CSR (<code>domain.csr</code>) signed by the private key identified by the alias (<code>domain</code>) in the  (<code>keystore.jks</code>) keystore:</p>
<pre class="code-pre "><code langs="">keytool -certreq \
        -alias <span class="highlight">domain</span> \
        -file <span class="highlight">domain.csr</span> \
        -keystore <span class="highlight">keystore.jks</span>
</code></pre>
<p>After entering the keystore's password, the CSR will be generated.</p>

<h3 id="import-signed-root-intermediate-certificate">Import Signed/Root/Intermediate Certificate</h3>

<p>Use this method if you want to import a signed certificate, e.g. a certificate signed by a CA, into your keystore; it must match the private key that exists in the specified alias. You may also use this same command to import <em>root</em> or <em>intermediate</em> certificates that your CA may require to complete a chain of trust. Simply specify a unique alias, such as <code>root</code> instead of <code>domain</code>, and the certificate that you want to import.</p>

<p>This command imports the certificate (<code>domain.crt</code>) into the keystore (<code>keystore.jks</code>), under the specified alias (<code>domain</code>). If you are importing a signed certificate, it must correspond to the private key in the specified alias:</p>
<pre class="code-pre "><code langs="">keytool -importcert \
        -trustcacerts -file <span class="highlight">domain.crt</span> \
        -alias <span class="highlight">domain</span> \
        -keystore <span class="highlight">keystore.jks</span>
</code></pre>
<p>You will be prompted for the keystore password, then for a confirmation of the import action.</p>

<p><strong>Note:</strong> You may also use the command to import a CA's certificates into your Java truststore, which is typically located in <code>$JAVA_HOME/jre/lib/security/cacerts</code> assuming <code>$JAVA_HOME</code> is where your JRE or JDK is installed.</p>

<h3 id="generate-self-signed-certificate-in-new-existing-keystore">Generate Self-Signed Certificate in New/Existing Keystore</h3>

<p>Use this command if you want to generate a self-signed certificate for your Java applications. This is actually the same command that is used to create a new key pair, but with the validity lifetime specified in days.</p>

<p>This command generates a 2048-bit RSA key pair, valid for <code>365</code> days, under the specified alias (<code>domain</code>), in the specified keystore file (<code>keystore.jks</code>):</p>
<pre class="code-pre "><code langs="">keytool -genkey \
        -alias <span class="highlight">domain</span> \
        -keyalg RSA \
        -validity <span class="highlight">365</span> \
        -keystore <span class="highlight">keystore.jks</span>
</code></pre>
<p>If the specified keystore does not already exist, it will be created after the requested information is supplied. This will prompt for the keystore password (new or existing), followed by a Distinguished Name prompt (for the private key), then the desired private key password.</p>

<h2 id="viewing-keystore-entries">Viewing Keystore Entries</h2>

<p>This section covers listing the contents of a Java Keystore, such as viewing certificate information or exporting certificates.</p>

<h3 id="list-keystore-certificate-fingerprints">List Keystore Certificate Fingerprints</h3>

<p>This command lists the SHA fingerprints of all of the certificates in the keystore (<code>keystore.jks</code>), under their respective aliases:</p>
<pre class="code-pre "><code langs="">keytool -list \
        -keystore <span class="highlight">keystore.jks</span>
</code></pre>
<p>You will be prompted for the keystore's password. You may also restrict the output to a specific alias by using the <code>-alias domain</code> option, where "domain" is the alias name.</p>

<h3 id="list-verbose-keystore-contents">List Verbose Keystore Contents</h3>

<p>This command lists verbose information about the entries a keystore (<code>keystore.jks</code>) contains, including certificate chain length, fingerprint of certificates in the chain, distinguished names, serial number, and creation/expiration date, under their respective aliases:</p>
<pre class="code-pre "><code langs="">keytool -list -v \
        -keystore <span class="highlight">keystore.jks</span>
</code></pre>
<p>You will be prompted for the keystore's password. You may also restrict the output to a specific alias by using the <code>-alias domain</code> option, where "domain" is the alias name.</p>

<p><strong>Note:</strong> You may also use this command to view which certificates are in your Java truststore, which is typically located in <code>$JAVA_HOME/jre/lib/security/cacerts</code> assuming <code>$JAVA_HOME</code> is where your JRE or JDK is installed.</p>

<h3 id="use-keytool-to-view-certificate-information">Use Keytool to View Certificate Information</h3>

<p>This command prints verbose information about a certificate file (<code>certificate.crt</code>), including its fingerprints, distinguished name of owner and issuer, and the time period of its validity:</p>
<pre class="code-pre "><code langs="">keytool -printcert \
        -file <span class="highlight">domain.crt</span>
</code></pre>
<p>You will be prompted for the keystore password.</p>

<h3 id="export-certificate">Export Certificate</h3>

<p>This command exports a binary DER-encoded certificate (<code>domain.der</code>), that is associated with the alias (<code>domain</code>), in the keystore (<code>keystore.jks</code>):</p>
<pre class="code-pre "><code langs="">keytool -exportcert
        -alias <span class="highlight">domain</span>
        -file <span class="highlight">domain.der</span>
        -keystore <span class="highlight">keystore.jks</span>
</code></pre>
<p>You will be prompted for the keystore password. If you want to convert the DER-encoded certificate to PEM-encoding, follow our <a href="https://indiareads/community/tutorials/openssl-essentials-working-with-ssl-certificates-private-keys-and-csrs#convert-certificate-formats">OpenSSL cheat sheet</a>.</p>

<h2 id="modifying-keystore">Modifying Keystore</h2>

<p>This section covers the modification of Java Keystore entries, such as deleting or renaming aliases.</p>

<h3 id="change-keystore-password">Change Keystore Password</h3>

<p>This command is used to change the password of a keystore (<code>keystore.jks</code>):</p>
<pre class="code-pre "><code langs="">keytool -storepasswd \
        -keystore <span class="highlight">keystore.jks</span>
</code></pre>
<p>You will be prompted for the current password, then the new password. You may also specify the new password in the command by using the <code>-new newpass</code> option, where "newpass" is the password.</p>

<h3 id="delete-alias">Delete Alias</h3>

<p>This command is used to delete an alias (<code>domain</code>) in a keystore (<code>keystore.jks</code>):</p>
<pre class="code-pre "><code langs="">keytool -delete \
        -alias domain \
        -keystore <span class="highlight">keystore.jks</span>
</code></pre>
<p>You will be prompted for the keystore password.</p>

<h3 id="rename-alias">Rename Alias</h3>

<p>This command will rename the alias (<code>domain</code>) to the destination alias (<code>newdomain</code>) in the keystore (<code>keystore.jks</code>):</p>
<pre class="code-pre "><code langs="">keytool -changealias \
        -alias <span class="highlight">domain</span> \
        -destalias <span class="highlight">newdomain</span> \
        -keystore <span class="highlight">keystore.jks</span>
</code></pre>
<p>You will be prompted for the keystore password.</p>

<h2 id="conclusion">Conclusion</h2>

<p>That should cover how most people use Java Keytool to manipulate their Java Keystores. It has many other uses that were not covered here, so feel free to ask or suggest other uses in the comments.</p>

<p>This tutorial is based on the version of keystore that ships with Java 1.7.0 update 65. For help installing Java on Ubuntu, follow <a href="https://indiareads/community/tutorials/how-to-install-java-on-ubuntu-with-apt-get">this guide</a>.</p>

    