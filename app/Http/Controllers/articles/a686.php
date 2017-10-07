<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h2 id="introduction">Introduction</h2>

<p>Push notifications let your Android application notify a user of an event, even when the user is not using your app. The goal of this tutorial is to send a simple push notification to your app. We'll use <strong>Ubuntu 14.04</strong> and <strong>Python 2.7</strong> on the server, and <strong>Google Cloud Messaging</strong> as the push notification service.</p>

<p>We'll use the term <em>server</em> to refer to the instance spun up with IndiaReads. We'll use <em>GCM</em> to refer to Google's server, the one that is between the Android device and your server.</p>

<h3 id="prerequisites">Prerequisites</h3>

<p>You'll need these things before you start the tutorial:</p>

<ul>
<li>An <strong>Android application</strong>; see <a href="http://developer.android.com/training/basics/firstapp/index.html">developer.android.com</a></li>
<li>A <strong>Ubuntu 14.04</strong> Droplet</li>
<li>Your Droplet's IP address</li>
</ul>

<h3 id="about-push-notifications">About Push Notifications</h3>

<p>Google-provided GCM Connection Servers take messages from a third-party application server, such as your Droplet, and send these messages to a GCM-enabled Android application (the <em>client app</em>) running on a device. Currently, Google provides connection servers for HTTP and XMPP.</p>

<p><img src="https://assets.digitalocean.com/articles/push_notifications_gcm/1.png" alt="The GCM Connection Servers send data between your third-party server and the client apps." /></p>

<p>In other words, you need your own server to communicate with Google's server in order to send the notifications. Your server sends a message to a GCM (Google Cloud Messaging) Connection Server, then the connection server queues and stores the message, and then sends it to the Android device when the device is online.</p>

<h2 id="step-one-—-create-a-google-api-project">Step One — Create a Google API Project</h2>

<p>We need to create a Google API project to enable GCM for our app.</p>

<p>Visit the <a href="https://console.developers.google.com">Google Developers Console</a>.</p>

<p>If you've never created a developer account there, you may need to fill out a few details.</p>

<p>Click <strong>Create Project</strong>.</p>

<p>Enter a project name, then click <strong>Create</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/push_notifications_gcm/2.png" alt="New Google API project" /></p>

<p>Wait a few seconds for the new project to be created. Then, view your <strong>Project ID</strong> and <strong>Project Number</strong> on the upper left of the project page.</p>

<p><img src="https://assets.digitalocean.com/articles/push_notifications_gcm/3.png" alt="Project ID and number" /></p>

<p>Make a note of the <strong>Project Number</strong>. You'll use it in your Android app client.</p>

<h2 id="step-two-enable-gcm-for-your-project">Step Two - Enable GCM for Your Project</h2>

<p>Make sure your project is still selected in the <a href="https://console.developers.google.com">Google Developers Console</a>.</p>

<p>In the sidebar on the left, select <strong>APIs & auth</strong>.</p>

<p>Choose <strong>APIs</strong>.</p>

<p>In the displayed list of APIs, turn the <span class="highlight">Google Cloud Messaging for Android</span> toggle to <strong>ON</strong>. Accept the terms of service.</p>

<p><strong>Google Cloud Messaging for Android</strong> should now be in the list of enabled APIs for this project.</p>

<p><img src="https://assets.digitalocean.com/articles/push_notifications_gcm/4.jpg" alt="Google Cloud Messaging for Android enabled" /></p>

<p>In the sidebar on the left, select <strong>APIs & auth</strong>.</p>

<p>Choose <strong>Credentials</strong>.</p>

<p>Under <strong>Public API access</strong>, click <strong>Create new Key</strong>.</p>

<p>Choose <strong>Server key</strong>.</p>

<p>Enter your server's IP address. </p>

<p><img src="https://assets.digitalocean.com/articles/push_notifications_gcm/5.png" alt="Server key IP" /></p>

<p>Click <strong>Create</strong>.</p>

<p>Copy the <strong>API KEY</strong>. You'll need to enter this on your server later.</p>

<p><img src="https://assets.digitalocean.com/articles/push_notifications_gcm/6.png" alt="API KEY" /></p>

<h2 id="step-three-—-link-android-app">Step Three — Link Android App</h2>

<p>To test the notifications, we need to link our Android app to the Google API project that we made.</p>

<p>If you are new to Android app development, you may want to follow the official guide for <a href="https://developer.android.com/google/gcm/client.html">Implementing GCM Client</a>.</p>

<p>You can get the official source code from the <a href="https://code.google.com/p/gcm/">gcm page</a>.</p>

<p>Note that the sources are not updates, so you'll have to modify the Gradle file:</p>

<p><code>gcm-client/GcmClient/build.gradle</code></p>

<p>Old line:</p>
<pre class="code-pre "><code langs="">compile "com.google.android.gms:play-services:4.0.+"
</code></pre>
<p>Updated line:</p>
<pre class="code-pre "><code langs="">compile "com.google.android.gms:play-services:5.0.89+"
</code></pre>
<p>In the main activity, locate this line:</p>
<pre class="code-pre "><code langs="">String SENDER_ID = "<span class="highlight">YOUR_PROJECT_NUMBER_HERE</span>";
</code></pre>
<p>Replace this with the <strong>Project Number</strong> from your Google API project.</p>

<p>Each time a device registers to GCM it receives a registration ID. We will need this registration ID in order to test the server. To get it easily, just modify these lines in the main file:</p>
<pre class="code-pre "><code langs="">            if (regid.isEmpty()) {
                registerInBackground();
            }else{
                Log.e("==========================","=========================");
                Log.e("regid",regid);
                Log.e("==========================","=========================");
            }
</code></pre>
<p>After you run the app, look in the logcat and copy your <strong>regid</strong> so you have it for later. It will look like this:</p>
<pre class="code-pre "><code langs="">=======================================
10-04 17:21:07.102    7550-7550/com.pushnotificationsapp.app E/==========================﹕ APA91bHDRCRNIGHpOfxivgwQt6ZFK3isuW4aTUOFwMI9qJ6MGDpC3MlOWHtEoe8k6PAKo0H_g2gXhETDO1dDKKxgP5LGulZQxTeNZSwva7tsIL3pvfNksgl0wu1xGbHyQxp2CexeZDKEzvugwyB5hywqvT1-UJY0KNqpL4EUXTWOm0RxccxpMk
10-04 17:21:07.102    7550-7550/com.pushnotificationsapp.app E/==========================﹕ =======================================
</code></pre>
<h2 id="step-four-—-deploy-a-droplet">Step Four — Deploy a Droplet</h2>

<p>Deploy a fresh <strong>Ubuntu 14.04</strong> server. We need this to be our third-party application server.</p>

<p>Google's GCM Connection Servers take messages from a third-party application server (our Droplet) and send them to applications on Android devices. While Google provides Connection Servers for HTTP and CCS (XMPP), we're focusing on HTTP for this tutorial. The HTTP server is downstream only: cloud-to-device. This means you can only send messages from the server to the devices. </p>

<p>Roles of our server:</p>

<ul>
<li>Communicates with your client</li>
<li>Fires off properly formatted requests to the GCM server</li>
<li>Handles requests and resends them as needed, using exponential back-off</li>
<li>Stores the API key and client registration IDs. The API key is included in the header of POST requests that send messages</li>
<li>Generates message IDs to uniquely identify each message it sends. Message IDs should be unique per sender ID</li>
</ul>

<p>The client will communicate with your server by sending the registration ID of the device for you to store it and use it when you send the notification. Don't worry now about managing it; it's very simple and GCM provides you with help by giving you error messages in case a registration ID is invalid.</p>

<h2 id="step-five-set-up-python-gcm-simple-server">Step Five - Set Up Python GCM Simple Server</h2>

<p>Log in to your server with a <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">sudo</a> user.</p>

<p>Update your package lists:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Install the Python packages:</p>
<pre class="code-pre "><code langs="">sudo apt-get install python-pip python-dev build-essential
</code></pre>
<p>Install <code>python-gcm</code>. Find out more about python-gcm <a href="https://github.com/geeknam/python-gcm">here</a>.</p>
<pre class="code-pre "><code langs="">sudo pip install python-gcm
</code></pre>
<p>Create a new Python file somewhere on the server. Let's say:</p>
<pre class="code-pre "><code langs="">sudo nano ~/test_push.py
</code></pre>
<p>Add the following information to the file. Replace the variables marked in <span class="highlight">red</span>. The explanation is below.</p>
<pre class="code-pre "><code langs="">from gcm import *

gcm = GCM("<span class="highlight">AIzaSyDejSxmynqJzzBdyrCS-IqMhp0BxiGWL1M</span>")
data = {'the_message': 'You have x new friends', 'param2': 'value2'}

reg_id = '<span class="highlight">APA91bHDRCRNIGHpOfxivgwQt6ZFK3isuW4aTUOFwMI9qJ6MGDpC3MlOWHtEoe8k6PAKo0H_g2gXhETDO1dDKKxgP5LGulZQxTeNZSwva7tsIL3pvfNksgl0wu1xGbHyQxp2CexeZDKEzvugwyB5hywqvT1-UxxxqpL4EUXTWOm0RXE5CrpMk</span>'

gcm.plaintext_request(registration_id=reg_id, data=data)
</code></pre>
<p><strong>Explanation:</strong></p>

<ul>
<li><code>from gcm import *</code>: this imports the Python client for Google Cloud Messaging for Android</li>
<li><code>gcm</code>: add your <strong>API KEY</strong> from the Google API project; make sure your server's IP address is in the allowed IPs</li>
<li><code>reg_id</code>: add your <strong>regid</strong> from your Android application</li>
</ul>

<h2 id="step-six-—-send-a-push-notification">Step Six — Send a Push Notification</h2>

<p>Run this command to send a test notification to your app:</p>
<pre class="code-pre "><code langs="">sudo python ~/test_push.py
</code></pre>
<p>Wait about 10 seconds. You should get a notification on your Android device.</p>

<p><img src="https://assets.digitalocean.com/articles/push_notifications_gcm/7.png" alt="Push notification example" /></p>

<h3 id="troubleshooting">Troubleshooting.</h3>

<p>If the notification does <em>not</em> appear on your device after about 10 seconds, follow these steps:</p>

<ul>
<li>Is your smartphone/tablet connected to the internet?</li>
<li>Do you have the correct project key?</li>
<li>Do you have the correct regid from the app?</li>
<li>Is your server's IP address added for the Google API server key?</li>
<li>Is the server connected to the internet?</li>
</ul>

<p>If you're still not getting the notification, it's probably the app. Check the logcat for some errors.</p>

<h2 id="where-to-go-from-here">Where to Go from Here</h2>

<p>Once you've done this simple test, you'll probably want to send the notifications to all your users. Remember that you have to send them in sets of 1000. Also, if the CGM responds with "invalid ID," you must remove it from your database. </p>

<p>You can adapt the examples in this tutorial to work with your own Android application.</p>

    