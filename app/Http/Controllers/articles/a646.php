<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h2 id="introduction">Introduction</h2>

<p>In version two of the IndiaReads API, each event that occurs creates <a href="https://developers.digitalocean.com/documentation/v2/#actions">an "Action" object</a>. These serve both as records of events that have occurred in the past and as a way to check the progress of an on-going event. From creating a new Droplet to transferring an image to a new region, an Action object will provide you with useful information about the event.</p>

<p>This article will explain Action objects and show how they can be used in practice via DropletKit, <a href="https://rubygems.org/gems/droplet_kit">the official Ruby gem for the IndiaReads API</a>.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This article assumes a basic understanding of the IndiaReads API. To learn more about the API including how to obtain an access token which will be needed to complete this tutorial, see the following resources:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-use-the-digitalocean-api-v2">How To Use the IndiaReads API v2</a></li>
<li><a href="https://developers.digitalocean.com/documentation/v2/">IndiaReads APIv2 Documentation</a></li>
</ul>

<h2 id="understanding-action-objects">Understanding Action Objects</h2>

<p>When initiating an event with the API, an Action object will be returned in the response. This object will contain information about the event including its  status, the timestamps for when it was started and completed, and the associated resource type and ID. For instance if we where to take a snapshot of the Droplet with the ID 3164450:</p>
<pre class="code-pre "><code langs="">curl -X POST -H 'Content-Type: application/json' \
    -H 'Authorization: Bearer '<span class="highlight">$TOKEN</span>'' \
    -d '{"type":"snapshot","name":"Nifty New Snapshot"}' \
    "https://api.digitalocean.com/v2/droplets/<span class="highlight">3164450</span>/actions" 
</code></pre>
<p>we would receive this in response:</p>
<pre class="code-pre "><code class="code-highlight language-json">{
  "action": {
    "id": 36805022,
    "status": "in-progress",
    "type": "snapshot",
    "started_at": "2014-11-14T16:34:39Z",
    "completed_at": null,
    "resource_id": 3164450,
    "resource_type": "droplet",
    "region": "nyc3"
  }
}
</code></pre>
<p>Note that the <code>resource_type</code> is <code>droplet</code> and the <code>resource_id</code> is the ID of the Droplet. The <code>status</code> is <code>in-progress</code>. This will change to <code>completed</code> once the event is finished. In order to check on the status of an Action, you can query the API for that Action directly.</p>
<pre class="code-pre "><code langs="">curl -X GET -H 'Content-Type: application/json' \
    -H 'Authorization: Bearer '<span class="highlight">$TOKEN</span>'' \
    "https://api.digitalocean.com/v2/actions/<span class="highlight">36805022</span>" 
</code></pre>
<p>This will return the requested action object:</p>
<pre class="code-pre "><code class="code-highlight language-json">{
  "action": {
    "id": 36805022,
    "status": "completed",
    "type": "snapshot",
    "started_at": "2014-11-14T16:34:39Z",
    "completed_at": "2014-11-14T16:38:52Z",
    "resource_id": 3164450,
    "resource_type": "droplet",
    "region": "nyc3"
  }
}
</code></pre>
<p>Notice how now that the <code>status</code> is <code>completed</code>, there is a timestamp for <code>completed_at</code> as well as <code>started_at</code>.</p>

<p>You can also access <a href="https://developers.digitalocean.com/v2/#list-all-actions">a complete history of all Actions</a> taken on your account at the <code>/actions</code> endpoint.</p>
<pre class="code-pre "><code langs="">curl -X GET -H 'Content-Type: application/json' \
    -H 'Authorization: Bearer '<span class="highlight">$TOKEN</span>'' \
    "https://api.digitalocean.com/v2/actions"
</code></pre>
<h2 id="using-actions-objects-in-practice">Using Actions Objects in Practice</h2>

<p>While listing all Action objects may be interesting in order to audit your history, in practice you will mostly use this endpoint in order check on the status of a process. We'll be using <code>droplet_kit</code>, <a href="https://rubygems.org/gems/droplet_kit">the official Ruby gem for the IndiaReads API</a>, for these examples. It can be installed with:</p>
<pre class="code-pre "><code langs="">gem install droplet_kit
</code></pre>
<p>To get started, enter the Ruby shell by running the command <code>irb</code> Then import the <code>droplet_kit</code> gem and set up your client using your API token:</p>
<pre class="code-pre "><code langs="">irb(main):> require 'droplet_kit'
 => true 
irb(main):> client = DropletKit::Client.new(access_token: <span class="highlight">DO_TOKEN</span>)
</code></pre>
<p>Some actions are dependent on others being taken first. For instance, attempting to take a snapshot of a Droplet which is still powered on will lead to an error. A Droplet must be powered off in order to take a snapshot.</p>
<pre class="code-pre "><code class="code-highlight language-ruby">irb(main):> client.droplet_actions.snapshot(droplet_id: 4143310, name: 'Snapshot Name')
=> "{\"id\":\"unprocessable_entity\",\"message\":\"Droplet is currently on. Please power it off to run this event.\"}"
</code></pre>
<p>Attempting to take a snapshot immediately after initiating a shutdown action will also lead to that same error as you must ensure that the shutdown Action has completed before the snapshot can be taken. Actions can not be queued.</p>
<pre class="code-pre "><code class="code-highlight language-ruby">irb(main):> client.droplet_actions.shutdown(droplet_id: 4143310)
=> <DropletKit::Action {:@id=>43918785, :@status=>"in-progress", :@type=>"shutdown", :@started_at=>"2015-02-16T21:22:35Z", :@completed_at=>nil, :@resource_id=>4143310, :@resource_type=>"droplet", :@region=>"nyc3"}>
irb(main):> client.droplet_actions.snapshot(droplet_id: 4143310, name: 'Snapshot Name')
=> "{\"id\":\"unprocessable_entity\",\"message\":\"Droplet is currently on. Please power it off to run this event.\"}"
</code></pre>
<p>Like the <code>curl</code> examples above, <code>droplet_kit</code> also returns the Action object in response to a successfully initiated event. It can be accessed as a normal Ruby object. Saving the response into a variable will allow you to access its attributes directly:</p>
<pre class="code-pre "><code class="code-highlight language-ruby">irb(main):> snapshot = client.droplet_actions.snapshot(droplet_id: 4143310, name: 'Snapshot Name')
=> "{\"id\":\"unprocessable_entity\",\"message\":\"Droplet is currently on. Please power it off to run this event.\"}"
irb(main):> shutdown = client.droplet_actions.shutdown(droplet_id: 4143310)
=> <DropletKit::Action {:@id=>43919195, :@status=>"in-progress", :@type=>"shutdown", :@started_at=>"2015-02-16T21:32:03Z", :@completed_at=>nil, :@resource_id=>4143310, :@resource_type=>"droplet", :@region=>"nyc3"}>
irb(main):> shutdown.status
=> "in-progress"
irb(main):> shutdown.id
=> 43919195
</code></pre>
<p>You can then check the status of the actions:</p>
<pre class="code-pre "><code class="code-highlight language-ruby">irb(main):> action = client.actions.find(id: shutdown.id)
=> <DropletKit::Action {:@id=>43919195, :@status=>"completed", :@type=>"shutdown", :@started_at=>"2015-02-16T21:32:03Z", :@completed_at=>"2015-02-16T21:32:07Z", :@resource_id=>4143310, :@resource_type=>"droplet", :@region=>"nyc3"}>
irb(main):> action.status
=> "completed"
</code></pre>
<p>We can use an <code>until</code> loop in Ruby to check on the progress of an Action until it has completed:</p>
<pre class="code-pre "><code class="code-highlight language-ruby">res = client.droplet_actions.shutdown(droplet_id: id)
until res.status == "completed"
    res = client.actions.find(id: res.id)
    sleep(2)
end
</code></pre>
<h2 id="putting-it-all-together">Putting It All Together</h2>

<p>This Ruby script bellow is an example of how to check on the status of an action in practice. It powers a droplet off and uses the <code>while</code> loop from above to make sure that the action has completed before moving on. Once the shutdown action has completed, it will then take a snapshot of the droplet.</p>
<pre class="code-pre "><code class="code-highlight language-ruby">#!/usr/bin/env ruby

require 'droplet_kit'
require 'json'

token = ENV['DO_TOKEN']
client = DropletKit::Client.new(access_token: token)

droplet_id = ARGV[0]
snapshot_name = ARGV[1] || Time.now.strftime("%b. %d, %Y - %H:%M:%S %Z")

def power_off(client, id)
    res = client.droplet_actions.shutdown(droplet_id: id)
    until res.status == "completed"
        res = client.actions.find(id: res.id)
        sleep(2)
    end
    puts " *   Action status: #{res.status}"
rescue NoMethodError
    puts JSON.parse(res)['message']
end

def take_snapshot(client, id, name)
    res = client.droplet_actions.snapshot(droplet_id: id, name: name)
    puts " *   Action status: #{res.status}"
rescue NameError
    puts JSON.parse(res)['message']
end

unless droplet_id.nil?
    puts "Powering off droplet..."
    power_off(client, droplet_id)
    sleep(2)
    puts "Taking snapshot..."
    take_snapshot(client, droplet_id, snapshot_name)
else
    puts "Power off and snapshot a droplet. Requires a droplet ID and optionally a snapshot name."
    puts "Usage: #{$0} droplet_id ['snapshot name']"
end
</code></pre>
<p>If you save this script as a file named <code>snapshot.rb</code> (or download it from <a href="https://gist.github.com/andrewsomething/d06f57b98ced36042ae6">this GitHub Gist</a>), you can run it from the command line like so:</p>
<pre class="code-pre "><code langs="">DO_TOKEN=<span class="highlight">YOUR_DO_API_TOKEN</span> ruby snapshot.rb <span class="highlight">12345</span> "<span class="highlight">My Snapshot</span>"
</code></pre>
<p>Note that in order to use the script, you must export your API token as a environmental variable with the name <code>DO_TOKEN</code>. The script takes two arguments, the ID of the droplet and optionally a name of the snapshot. If you do not provide a name, it will is the date and time.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Action items are an important part of the DigtialOcean API. Using them to check the status of actions is an important best practice to implement when using the API. Now that you understand how to use them, you are ready to move on to more complex use-cases of the API like:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-automate-the-scaling-of-your-web-application-on-digitalocean">How To Automate the Scaling of Your Web Application on IndiaReads</a></li>
</ul>

<p>Check out the <a href="https://developers.digitalocean.com/guides/">IndiaReads developer's portal</a> for more topics.</p>

    