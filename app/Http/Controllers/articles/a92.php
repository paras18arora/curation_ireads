<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h3>Introduction</h3>

<p>Memcached is a very fast in-memory object caching system that can make Rails run much faster with very few changes.</p>

<b>Prerequisites:</b>

<p>This tutorial assumes you have already installed Ruby on Rails and Memcached.  If not, the tutorials are linked below:</p> 

<ul><li><a href="https://indiareads/community/articles/how-to-install-ruby-on-rails-on-ubuntu-12-04-lts-precise-pangolin-with-rvm">How to Install Ruby on Rails on Ubuntu 12.04 LTS (Precise Pangolin) with RVM | IndiaReads</a>
</li>
<li><a href="https://indiareads/community/articles/how-to-install-and-use-memcache-on-ubuntu-12-04">How to Install and Use Memcache on Ubuntu 12.04 | IndiaReads</a></li></ul>

<p>It also assumes that you have your Rails application up and running and ready to optimize using Memcached.</p>

<h2>Install the Dalli Gem</h2>

<p>The first thing we will have to do is install Mike Perham's <a href="https://github.com/mperham/dalli">Dalli Gem</a>:</p>

<pre>gem install dalli</pre>

<p>If you use Bundler, then add <code>gem 'dalli'</code> to your Gemfile and run <code>bundle install</code>.</p>

<p>This will be our super fast and feature packed way of interacting with Memcached.</p>

<h2>Configure Rails</h2>

<p>The first step to configuring Rails to use memcached is to edit your <code>config/environments/production.rb</code> and add this line to tell Rails to use Dalli:</p>

<pre>config.cache_store = :dalli_store</pre>

<p>Next, we will tell ActionController to perform caching. Add this line to the same file:</p>

<pre>config.action_controller.perform_caching = true</pre>

<p>Now, restart your Rails application as you normally would.</p>

<h2>Change Your Rails Application</h2>

<p>To take advantage of the changes we've just made, the Rails application will need to be updated. There are two major ways to take advantage of the speed up memcached will give you.</p>

<h3>Add Cache Control Headers</h3>

<p>The easiest way to take advantage of memcached is to add a Cache-Control header to one of your actions. This will let Rack::Cache store the result of that action in memcached for you. If you had the following action in <code>app/controllers/slow_controller.rb</code>:</p>

<pre>def slow_action
  sleep 15
  # todo - print something here
end
</pre>

<p>We can add the following line to tell Rack::Cache to store the result for five minutes:</p>

<pre>def slow_action
  expires_in 5.minutes
  sleep 15
  # todo - print something here
end
</pre>

<p>Now, when you execute this action the second time, you'll see that it's significantly faster. Rails only has to execute it once every five minutes to update Rack::Cache.</p>

<p>Please note that this will set the Cache-Control header to public. If you have certain actions that only one user should see, use <code>expires_in 5.minutes, :public => false</code>. You will also have to determine what the appropriate time is to cache your responses, this varies from application to application.</p>

<p>If you would like to learn more about HTTP Caching, check out Mark Nottingham's <a href="http://www.mnot.net/cache_docs/">Caching Tutorial for Web Authors and Webmasters</a>.</p>

<h3>Store Objects in Memcached</h3>

<p>If you have a very expensive operation or object that you must create each time, you can store and retrieve it in memcached. Let's say your action looks like this:</p>

<pre>def slow_action
  slow_object = create_slow_object
end
</pre>

<p>We can store the result in memcached by changing the action like this:</p>

<pre>def slow_action
  slow_object = Rails.cache.fetch(:slow_object) do 
      create_slow_object
  end
end
</pre>

<p>Rails will ask memcached for the object with a key of 'slow_object'; if it doesn't find that object, it will execute the block given and write the object back into it.</p>

<h3>Fragment Caching</h3>

<p>Fragment caching is a Rails feature that lets you choose which parts of your application are the most dynamic and need to be optimized. You can easily cache any part of a view surrounding it in a <code>cache</code> block:</p>

<pre><% # app/views/managers/index.html.erb  %>
<% cache manager do %>
  Manager's Direct Reports:
  <%= render manager.employees %>
<% end %> 

<% # app/views/employees/_employee.html.erb %>
<% cache employee do %>
    Employee Name: <%= employee.name %>
    <%= render employee.incomplete_tasks %>
<% end %>

<% # app/views/tasks/_incomplete_tasks.html.erb %>
<% cache task do %>
    Task: <%= task.title %>
    Due Date: <%= task.due_date %>
<% end %>
</pre>

<p>The above technique is called Russian Doll caching alluding to the <a href="http://en.wikipedia.org/wiki/Matryoshka_doll">traditional Russian nesting dolls</a>. Rails will then cache these fragments to memcached and since we added the model into the <code>cache</code> statement this cache object's key will change when the object changes. The problem this creates though is when a task gets updated:</p>

<pre>@todo.completed!
@todo.save!
</pre>

<p>Since we are nesting cache objects inside of cache objects, Rails won't know to expire the cache fragments that rely on this model. This is where the ActiveRecord <code>touch</code> keyword comes in handy:</p>

<pre>class Employee < ActiveRecord::Base
  belongs_to :manager, touch: true
end

class Todo < ActiveRecord::Base
  belongs_to :employee, touch: true
end
</pre>

<p>Now when a <code>Todo</code> model is updated, it will expire its cache fragments plus notify the <code>Employee</code> model that it should update its fragments too. Then the <code>Employee</code> fragment will notify the <code>Manager</code> model and after this, the cache expiration process is complete.</p>

<p>There is one additional problem that Russian Doll caching creates for us. When deploying a new application, Rails doesn't know when to check that a view template has changed. If we update our task listing view partial:</p>

<pre><% # app/views/tasks/_incomplete_tasks.html.erb %>
<% cache task do %>
    Task: <%= task.title %>
    Due Date: <%= task.due_date %>
    <p><%= task.notes %></p>
<% end %>
</pre>

<p>Rails won't expire the cache fragments that use view partial. Before you would have to add version numbers to your <code>cache</code> statements but now there is a gem called <a href="https://github.com/rails/cache_digests">cache_digests</a> that automatically adds in an MD5 hash of the template file to the cache key. If you update the partial and restart your application, the cache key will no longer match since the MD5 of the view template file has changed and Rails will render that template again. It also handles the dependencies between template files so, in the above example, it will expire all our cache objects up the dependency chain if the <code>_incomplete_tasks.html.erb</code> is updated.</p>

<p>This feature is automatically included in Rails version 4.0. To use this gem in your Rails 3 project, type the following command:</p>

<pre>gem install cache_digests</pre>

<p>Or if you use Bundler, add this line to your Gemfile:</p>

<pre>gem 'cache_digests'</pre>

<h2>Advanced Rails and Memcached Setup</h2>

<p>The Dalli Ruby Gem is very powerful and takes care of spreading keys across a cluster of memcached servers, which distributes the load and increases your memcached capacity. If you have multiple web servers in your web tier, you can install memcached on each of those servers and add them all to your <code>config/environments/production.rb</code>:</p>

<pre>config.cache_store = :dalli_store, 'web1.example.com', 'web2.example.com', 'web3.example.com'</pre>

<p>This will use <a href="http://en.wikipedia.org/wiki/Consistent_hashing">consistent hashing</a> to spread the keys across the available memcached servers.</p>

<div class="author">Article Submitted by: Andrew Williams</div></div>
    