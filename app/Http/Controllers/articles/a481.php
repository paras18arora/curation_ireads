<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>When setting up a web site or application under your own domain, it is likely that you will also want a mail server to handle the domain's incoming and outgoing email. While it is possible to run your own mail server, it is often not the best option for a variety of reasons. This guide will cover many of the reasons that you may not want to run your own mail server, and offer a few alternatives.</p>

<p>If you do not want to read the entire article, here is a quick summary: setting up and maintaining your own mail server is complicated and time-consuming, and there are several affordable alternatives—most people will get more value, in the form of saved time, out of using a paid mail service. With that said, read on if you want more details.</p>

<h2 id="mail-servers-are-complex">Mail Servers Are Complex</h2>

<p>A typical mail server consists of many software components that provide a specific function. Each component must be configured and tuned to work nicely together and provide a fully-functioning mail server. Because they have so many moving parts, mail servers can become complex and difficult to set up.</p>

<p>Here is a list of required components in a mail server:</p>

<ul>
<li>Mail Transfer Agent</li>
<li>Mail Delivery Agent</li>
<li>IMAP and/or POP3 Server</li>
</ul>

<p>In addition to the the required components, you will probably want to add these components:</p>

<ul>
<li>Spam Filter</li>
<li>AntiVirus</li>
<li>Webmail</li>
</ul>

<p>While some software packages include the functionality of multiple components, the choice of each component is often left up to you. In addition to the software components, mail servers need a domain name, the appropriate DNS records, and an SSL certificate.</p>

<p>Let's take a look at each component in more detail.</p>

<h3 id="mail-transfer-agent">Mail Transfer Agent</h3>

<p>A Mail Transfer Agent (MTA), which handles Simple Mail Transfer Protocol (SMTP) traffic, has two responsibilities:</p>

<ol>
<li>To send mail from your users to an external MTA (another mail server)</li>
<li>To receive mail from an external MTA</li>
</ol>

<p>Examples of MTA software: Postfix, Exim, and Sendmail.</p>

<h3 id="mail-delivery-agent">Mail Delivery Agent</h3>

<p>A Mail Delivery Agent (MDA), which is sometimes referred to as the Local Delivery Agent (LDA), retrieves mail from a MTA and places it in the appropriate mail user's mailbox.</p>

<p>There are a variety of mailbox formats, such as <em>mbox</em> and <em>Maildir</em>. Each MDA supports specific mailbox formats. The choice of mailbox format determines how the messages are actually stored on the mail server which, in turn, affects disk usage and mailbox access performance.</p>

<p>Examples of MDA software: Postfix and Dovecot.</p>

<h3 id="imap-and-or-pop3-server">IMAP and/or POP3 Server</h3>

<p>IMAP and POP3 are protocols that are used by mail clients, i.e. any software that is used to read email, for mail retrieval. Each protocol has its own intricacies but we will highlight some key differences here.</p>

<p>IMAP is the more complex protocol that allows, among other things, multiple clients to connect to an individual mailbox simultaneously. The email messages are copied to the client, and the original message is left on the mail server.</p>

<p>POP3 is simpler, and moves email messages to the mail client's computer, typically the user's local computer, by default.</p>

<p>Examples of software that provide IMAP and/or POP3 server functionality: Courier, Dovecot, Zimbra.</p>

<h3 id="spam-filter">Spam Filter</h3>

<p>The purpose of a spam filter is to reduce the amount of incoming spam, or junk mail, that reaches user's mailboxes. Spam filters accomplish this by applying spam detection rules--which consider a variety of factors such as the server that sent the message, the message content, and so forth--to incoming mail. If a message's "spam level" reaches a certain threshold, it is marked and treated as spam.</p>

<p>Spam filters can also be applied to outgoing mail. This can be useful if a user's mail account is compromised, to reduce the amount of spam that can be sent using your mail server.</p>

<p>SpamAssassin is a popular open source spam filter.</p>

<h3 id="antivirus">Antivirus</h3>

<p>Antivirus is used to detect viruses, trojans, malware, and other threats in incoming and outgoing mail. ClamAV is a popular open source antivirus engine.</p>

<h3 id="webmail">Webmail</h3>

<p>Many users expect their email service to provide webmail access. Webmail, in the context of running a mail server, is basically mail client that can be accessed by users via a web browser--Gmail is probably the most well-known example of this. The webmail component, which requires a web server such as Nginx or Apache, can run on the mail server itself.</p>

<p>Examples of software that provide webmail functionality: Roundcube and Citadel.</p>

<h2 id="maintenance-is-time-consuming">Maintenance is Time-Consuming</h2>

<p>Now that you are familiar with the mail server components that you have to install and configure, let's look at why maintenance can become overly time-consuming. There are the obvious maintenance tasks, such as continuously keeping your antivirus and spam filtering rules, and all of the mail server components up to date, but there are some other things you might have not thought of.</p>

<h3 id="staying-off-blacklists">Staying Off Blacklists</h3>

<p>Another challenge with maintaining a mail server is keeping your server off of the various blacklists, also known as DNSBL, blocklists, or blackhole lists. These lists contain the IP addresses of mail servers that were reported to send spam or junk mail (or for having improperly configured DNS records). Many mail servers subscribe to one or more of these blacklists, and filter incoming messages based on whether the mail server that sent the messages is on the list(s). If your mail server gets listed, your outgoing messages may be filtered and discarded before they reach their intended recipients.</p>

<p>If your mail server gets blacklisted, it is often possible to get it unlisted (or removed from the blacklist). You will want to determine the reason for being blacklisted, and resolve the issue. After this, you will want to look up the blacklist removal process for the particular list that your mail server is on, and follow it.</p>

<h3 id="troubleshooting-is-difficult">Troubleshooting is Difficult</h3>

<p>Although most people use email every day, it is easy to overlook the fact that it is a complex system can be difficult to troubleshoot. For example, if your sent messages are not being received, where do you start to resolve the issue? The issue could be caused by a misconfiguration in one of the many mail server components, such as a poorly tuned outgoing spam filter, or by an external problem, such as being on a blacklist.</p>

<h2 id="easy-alternatives-—-mail-services">Easy Alternatives — Mail Services</h2>

<p>Now that you know why you probably do not want to run your own mail server, here are some alternatives. These mail services will probably meet your needs, and will allow you and your applications to send and receive email from your own domain.</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-gmail-with-your-domain-on-digitalocean">Google Apps</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-zoho-mail-with-a-custom-domain-managed-by-digitalocean-dns">Zoho</a></li>
<li><a href="https://www.fastmail.com/">FastMail</a></li>
<li><a href="https://www.gandi.net/">Gandi</a> (requires that the domain is registered through them)</li>
<li><a href="http://products.office.com/en-us/business/compare-office-365-for-business-plans">Microsoft Office365</a></li>
</ul>

<p>This list doesn't include every mail service; there are many out there, each with their own features and prices. Be sure to choose the one that has the features that you need, at a price that you want.</p>

<h2 id="easy-alternatives-—-postfix-for-outgoing-mail">Easy Alternatives — Postfix for Outgoing Mail</h2>

<p>If you simply need to send outgoing mail from an application on your server, you don't need to set up a complete mail server. You can set up a simple Mail Transfer Agent (MTA) such as Postfix. A tutorial that covers this can be found here: <a href="https://indiareads/community/tutorials/how-to-install-and-setup-postfix-on-ubuntu-14-04">How To Install and Setup Postfix on Ubuntu 14.04</a>.</p>

<p>You then can configure your application to use <code>sendmail</code>, on your server, as the mail transport for its outgoing messages.</p>

<h2 id="not-convinced">Not Convinced?</h2>

<p>If you really want to run your own mail server, we have a few tutorials on the topic. Here are links to a few different setups:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-configure-a-mail-server-using-postfix-dovecot-mysql-and-spamassasin">How To Configure a Mail Server Using Postfix, Dovecot, MySQL, and SpamAssasin</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-a-postfix-e-mail-server-with-dovecot">How To Set Up a Postfix E-Mail Server with Dovecot</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-iredmail-on-ubuntu-12-04-x64">How To Install iRedMail On Ubuntu 12.04 x64</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-citadel-groupware-on-an-ubuntu-13-10-vps">How To Install Citadel Groupware on an Ubuntu 13.10 VPS</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-the-send-only-mail-server-exim-on-ubuntu-12-04">How To Install the Send-Only Mail Server "Exim" on Ubuntu 12.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-and-utilize-virtualmin-on-a-vps">VirtualMin</a></li>
</ul>

<p>Good luck!</p>

    