<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="about-gulp-js">About Gulp.js</h3>

<hr />

<p><a href="http://gulpjs.com/">Gulp.js</a> is a task runner that can help your front-end developing experience greatly. The purpose of a task runner is to automate tedious tasks that you have to do over and over again in the course of a project. Grunt.js is another such popular task runner that many developers are using for automating frontend processes. </p>

<p>Gulp.js uses code over configuration for setting up its tasks and this makes it very easy to maintain for the project. Additionally, it uses Node.js streams which makes it very fast. A key difference between Gulp and Grunt is that instead of setting up an input/output for each plugin, Gulp pipes through all the set up plugins the source files to then produce the destination files. </p>

<p>For a great article on setting up Grunt.js, <a href="https://indiareads/community/articles/how-to-setup-task-automation-with-grunt-and-node-js-on-a-vps">this tutorial</a> is a great read.</p>

<p>In this tutorial, however, we will install Gulp.js on our VPS and set up a small project to illustrate how easy it is to automate tasks. For this I assume you already have Node.js installed together with NPM (Node Package Manager). If you don't already, you can get started with <a href="https://indiareads/community/articles/how-to-install-an-upstream-version-of-node-js-on-ubuntu-12-04">this tutorial</a>.</p>

<h2 id="installation">Installation</h2>

<hr />

<p>Installing Gulp.js is actually quite simple. Since we're using NPM, all you have to do is run the following command:</p>
<pre class="code-pre "><code langs="">npm install gulp -g
</code></pre>
<p>This will install Gulp.js gloablly on your VPS and make it available for you in the command line. It follows that we set up our individual project for which we add Gulp plugins as needed.</p>

<h2 id="our-project">Our project</h2>

<hr />

<p>In order to illustrate the power of Gulp, we will start a small frontend project called <em>Gus</em> and focus on styling issues. Let's thus create our project folder and navigate inside:</p>
<pre class="code-pre "><code langs="">mkdir Gus
cd Gus
</code></pre>
<p>A nice thing about the Node Package Manager is that you can declare what libraries you need for the project in a <code>package.json</code> file that you can then commit to your version controlled repository. You can create this file manually or use a command line utility to do so. We'll go with the second option because it's safer in terms of not making any syntax mistakes. Run the following command and follow the instructions on the screen:</p>
<pre class="code-pre "><code langs="">npm init
</code></pre>
<p>My new <code>package.json</code> file looks like this after following all the defaults and setting a description for my project. </p>
<pre class="code-pre "><code langs="">{
  "name": "Gus",
  "version": "0.0.0",
  "description": "My Gus project",
  "main": "index.js",
  "scripts": {
    "test": "echo \"Error: no test specified\" && exit 1"
  },
  "author": "",
  "license": "BSD-2-Clause"
}
</code></pre>
<p>It can contain less than this but also a lot more (as we will see in a second). </p>

<p>Now that we have our <code>package.json</code> file, we can add our Gulp.js libraries to the project and have them automatically included in this file as <em>devDependencies</em>. But first, what libraries do we want to use? </p>

<p>For the purpose of this tutorial, we will stick to the following task: compile sass files, autoprefix the resulting css files with vendor prefixes, and then minify them. </p>

<p>For Gulp to be able to compile the Sass files, you'll need to first install Ruby, Ruby Gems and Sass on the VPS. You can quickly take care of that with the following commands:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install ruby-full rubygems
sudo gem install sass
</code></pre>
<p>Next, run the following command to install Gulp in our project and save it as a <strong>devDependency</strong>:</p>
<pre class="code-pre "><code langs="">npm install gulp --save-dev
</code></pre>
<p>Next, run the following command to install the libraries needed to perform the tasks I just mentioned:</p>
<pre class="code-pre "><code langs="">npm install gulp-ruby-sass gulp-autoprefixer gulp-minify-css gulp-rename --save-dev
</code></pre>
<p>A couple of things you'll notice after running these commands: (1) there is a new folder in your project root called "node_modules" that contains all the installed packages and (2) the latter are referenced in the <code>package.json</code> file as <em>devDependencies</em>.</p>

<h2 id="setting-up-the-tasks">Setting up the tasks</h2>

<hr />

<p>All the tasks and requirements for them need to be set up in a file called <code>gulpfile.js</code> located in the root of your project. So create it and paste in the following block:</p>
<pre class="code-pre "><code langs="">var gulp = require('gulp'),
    sass = require('gulp-ruby-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    minifycss = require('gulp-minify-css'),
    rename = require('gulp-rename');
</code></pre>
<p>As you can see, we create some variables in which we require all the libraries I mentioned above. You'll notice an additional one called "rename" that will help us rename files. </p>

<p>Below this declaration, we can now create our tasks. Although you can create multiple tasks and even have them run each other, we will now create only one called <code>styles</code>:</p>
<pre class="code-pre "><code langs="">gulp.task('styles', function() {
  return gulp.src('sass/*.scss')
    .pipe(sass({ style: 'expanded' }))
    .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1'))
    .pipe(gulp.dest('css'))
    .pipe(rename({suffix: '.min'}))
    .pipe(minifycss())
    .pipe(gulp.dest('css'));
});
</code></pre>
<p>Above we use <code>gulp.task</code> to generate a new task called "styles". Its callback is "gulp.src" that locates the source files the task will be performed on. In our case the <code>.scss</code> files located in the <code>sass/</code> folder in the project root. These files are in turn piped through Gulp plugins and sent to their destination set by <code>gulp.dest</code>. </p>

<p>In other words, this tasks will take all the <code>.scss</code> files from that folder and compile them to css before running them through autoprefixing. The resulting files will then be placed in the <code>css/</code> folder. Then the same files are copied and renamed with a <strong>.min</strong> suffix at the end and run through the minifying plugin and placed in the same <code>css/</code> folder. </p>

<p>To test this out, create the two folders (sass and css) and create a <code>.scss</code> file in the <code>sass/</code> folder called "styles.scss". Inside, place the following statements:</p>
<pre class="code-pre "><code langs="">$color: #eee;

#box {
  color : $color;
  box-sizing: border-box;
}
</code></pre>
<p>As you can see, this is some basic SASS syntax which contains a CSS property that is not yet prefixed. Let's run our Gulp.js <code>styles</code> task to turn this into what we need. From the project root folder, runt the following command:</p>
<pre class="code-pre "><code langs="">gulp styles
</code></pre>
<p>You should get something like this in the terminal window:</p>
<pre class="code-pre "><code langs="">[gulp] Using file /path/to/project/Gus/gulpfile.js
[gulp] Working directory changed to /path/to/project/Gus
[gulp] Running 'styles'...
[gulp] Finished 'styles' in 224 ms
</code></pre>
<p>Now if you check in the <code>css/</code> folder, you'll notice 2 files: <code>style.css</code> and <code>style.min.css</code>. The second one should contain the minfyed version of the first one which should contain the following css code:</p>
<pre class="code-pre "><code langs="">#box {
  color: #eeeeee;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}
</code></pre>
<p>Readily compiled and prefixed so you don't have to worry about that anymore. And the minifyed version is on standby as well. Now this is already awesome, but let's see how we can have Gulp watch for changes in our <code>.scss</code> files and run the task as the files are being saved. This will essentially save us even the trip to the command line for running the task.</p>

<p>For this, we'll need to create a new task below the existing one:</p>
<pre class="code-pre "><code langs="">gulp.task('watch', function() {

  // Watch the sass files
  gulp.watch('sass/*.scss', ['styles']);

});
</code></pre>
<p>This will essentially be a task that watches over changes to the files in a specified folder and runs tasks when this happens. The task we set to run in our case is <code>styles</code>. Now we need to run the watch task from the command line:</p>
<pre class="code-pre "><code langs="">gulp watch
</code></pre>
<p>And this will keep watching the respective folder for changes and running the <code>styles</code> task until we stop it:</p>
<pre class="code-pre "><code langs="">ctrl + c
</code></pre>
<h3 id="conclusion">Conclusion</h3>

<hr />

<p>In this tutorial, we've seen how to set up Gulp.js in a frontend project and use it to run automated tasks with high convenience. Its powers do not stop here as there are a multitude of <a href="http://gulpjs.com/plugins/">plugins</a> available that you can use in your tasks. You can manipulate javascript files and even images and set up tasks that run other tasks for even more automation. </p>

<div class="author">Article Submitted by: <a href="http://www.webomelette.com/">Danny</a></div>

    