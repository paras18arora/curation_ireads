<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h3>About CakePHP</h3>

<p><a href="http://cakephp.org/" target="_blank">CakePHP</a> is a powerful and robust PHP framework built around the Model-View-Controller (MVC) programming paradigm. In addition to the flexible way you can use it to build your application, it provides a basic structure for organizing files and database table names - keeping everything consistent and logical.</p> 

<p>In the last tutorial, we started to create a small application that would perform some basic CRUD (create, read, update, delete) operations on our database. So far we managed to read the data (the posts) using a Model (<strong>Post</strong>), request it using a Controller (<strong>PostsController</strong>) and created a couple of Views to display the information. In this tutorial we will finish the application by implementing the other CRUD operations, namely create, update, and delete. For this, we will mainly work inside our existing Controller and use the Model we created to access our data.</p> 

<p>Please go through the previous two tutorials if you have not done so yet before following along this one:</p>

<ul>
 <li><a href="https://indiareads/community/articles/how-to-install-cakephp-on-an-ubuntu-12-04-vps">How To Install CakePHP On An Ubuntu 12.04 VPS</a></li>
<li> <a href="https://indiareads/community/articles/how-to-create-a-small-web-application-with-cakephp-on-a-vps-part-1">How To Create a Small Web Application with CakePHP on a VPS (Part 1)</a></li>
</ul>

<h2>Adding Data</h2>

<p>After seeing how we can read the posts in the table, let’s see how we can add new posts. Before creating our Controller method, let’s first make sure the Controller has all the components we need. In the last tutorial we’ve included only the <strong>Form</strong> helper:</p>

<pre>public $helpers = array('Form');</pre>

<p>Let’s add now the HTML and Session helpers as well by adding to the array:</p>

<pre>public $helpers = array('Form', 'Html', 'Session');</pre>

<p>Additionally, let’s include the Session component. Below the line we just edited, add the following:</p>

<pre>public $components = array('Session');</pre>

<p>Now let’s go ahead and create a View that will house our form to add a new post. This will use the Form helper we just included to make things much easier. So create a file in the <em>app/View/Posts/</em> folder called <strong>add.ctp</strong>. Inside, paste the following code:</p>

<pre><h1>Add Post</h1>
<?php
echo $this->Form->create('Post');
echo $this->Form->input('title');
echo $this->Form->input('body', array('rows' => '3'));
echo $this->Form->end('Save');
?>
</pre>

<p>As you can see, we can access the Form helper straight from the View and it allows us to quickly draw our Form. One important thing here is that if the <strong>create()</strong> method is not passed any parameters, it will assume that the form submits to itself (the Controller method loading this View - that we will create in a second). The <strong>input()</strong> method as you can see is quite self-explanatory but one cool thing is that it will generate the form elements that match our data in the table. So let’s save the file and create our method in the <strong>PostsController</strong> to take care of this.</p> 

<p>Add the following code right below the <strong>view()</strong> method we created earlier in the PostsController:</p>

<pre>public function add() {
        if ($this->request->is('post')) {
            $this->Post->create();
            $post_data = $this->request->data;
            if ($this->Post->save($post_data)<WBR />) {
                $this->Session->setFlash(__('<WBR />New post saved successfully to the database'));
                return $this->redirect(array('action' => 'index'));
            }
            $this->Session->setFlash(__('<WBR />Unable to save the post to the database.'));
        }
}</pre>

<p>This function (named as the View it corresponds to) first checks if the there is a request of the type POST sent to it and tries to insert the new data into the table using the <strong>Post</strong> model if it is. If successful, it will set a flashdata to the current user session and redirect to the <strong>index()</strong> method (the one displaying all the posts) which will then display a positive confirmation message as well. If not, it will set an error message instead. You can go ahead and test it out at <strong><a href="http://www.example.com/project/posts/add" target="_blank">www.example.com/project/posts/<WBR />add</a></strong>. Fill in the form and it should save the new post, redirect you to the posts index page and display a confirmation message.</p> 

<p>Since <strong>PostsController</strong> was created by extending the default CakePHP Controller, we have access to a lot of goodies, such as the request object. Using that, we can check what kind of HTTP request is being made and get access to the POST data as well. Additionally, we get access to the <strong>redirect()</strong> method by which we can quickly redirect the user to another method or Controller. </p>

<h2>Validating Data</h2>

<p>As I am sure you don’t want posts being submitted without any information, let’s set up a quick rule in our <strong>Post</strong> Model to make sure it forces the user to set a title when submitting a new post. Inside the <strong>Post</strong> Model, add the following property:</p> 

<pre>public $validate = array('title' => array('rule' => 'notEmpty'));</pre>

<p>This will make sure that the title field cannot be empty. Save the Model file and try adding a new post without filling up the title field. You’ll notice that now you are required to fill the title field but not necessarily the body field.</p> 

<h2>HTML Helper?</h2>

<p>The reason we included the <strong>HTML helper</strong> into the <strong>PostsController</strong> is to show you how to put a link on a page in the CakePHP way. So let’s open the <strong>index.ctp</strong> View located in the <em>app/View/Posts/</em> folder and let’s add the following code after the H1 tag:</p>

<pre><?php echo $this->Html->link(
    'Add Post',
    array('controller' => 'posts', 'action' => 'add')
); ?>
</pre>

<p>This will output a link with the anchor <strong>Add Post</strong> that will go to the <strong>add()</strong> method of the <strong>PostsController</strong>. If you want, you can also further edit the file and using the same technique, turn the post titles on this page into links to their respective pages. So instead of:</p>

<pre><?php echo $post['Post']['title']; ?></pre>

<p>You can put:</p>

<pre><?php echo $this->Html->link($post['Post'<WBR />]['title'], array('controller' => 'posts', 'action' => 'view', $post['Post']['id'])); ?></pre>

<p>More information about the HTML helper you can find <a href="http://book.cakephp.org/2.0/en/core-libraries/helpers/html.html" target="_blank">here</a>.</p>

<h2>Editing Data</h2>

<p>Now that we saw how to create new posts, let’s see how to edit existing ones. Let’s create the View again next to where we placed the <strong>add.ctp</strong> View and call it <strong>edit.ctp</strong>. Inside, paste the following:</p>

<pre><h1>Edit Post</h1>
<?php
    echo $this->Form->create('Post');
    echo $this->Form->input('title');
    echo $this->Form->input('body', array('rows' => '3'));
    echo $this->Form->input('id', array('type' => 'hidden'));
    echo $this->Form->end('Save');
?></pre>

<p>The main difference between the <strong>edit.ctp</strong> and <strong>add.ctp</strong> Views is that in the former, we also included the ID of the post as a hidden input so that CakePHP knows you want to edit and not add a new post. Save the file and exit. Next, we create the <strong>edit()</strong> method in the PostsController:</p>

<pre>public function edit($id = null) {
    if (!$id) {
        throw new NotFoundException(__('Post is not valid!'));
    }

    $post = $this->Post->findById($id);
    if (!$post) {
        throw new NotFoundException(__('Post is not valid!'));
    }

    if ($this->request->is('post') || $this->request->is('put')) {
        $this->Post->id = $id;
        $post_data = $this->request->data;
        if ($this->Post->save($post_data)<WBR />) {
            $this->Session->setFlash(__('<WBR />Your post has been updated.'));
            return $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('<WBR />Unable to update your post.'));
    }

    if (!$this->request->data) {
        $this->request->data = $post;
    }
}
</pre>

<p>This action first makes sure that the user is trying to access a valid post by checking the ID for validity and whether it exists in the database. Like in the <strong>add()</strong> method, it checks whether the request is POST and tries to update the post in the database if it is. If no data is present in the request object, it will fill the form elements with the data existing in the database. And as in the <strong>add()</strong> action, it then redirects the user to the <strong>index()</strong> method and displays a confirmation message. So go ahead and try it out.</p> 

<p>You can also modify the <strong>index.ctp</strong> View and add a link to edit individual posts. Add the following code after the <em>Created</em> field:</p>

<pre><?php echo $this->Html->link('Edit', array('action' => 'edit', $post['Post']['id'])); ?></pre>

<h2>Deleting Data</h2>

<p>The last thing we need to do is allow users to delete the posts. So let’s add the following action to the PostsController:</p>

<pre>public function delete($id) {
   if ($this->request->is('post')) {
    if ($this->Post->delete($id)) {
      $this->Session->setFlash(__('<WBR />The post number %s has been deleted.', h($id)));
      return $this->redirect(array('action' => 'index'));
    }
  }
}</pre>

<p>This method first throws an exception if the request is of a GET type. Then it uses the <strong>Post</strong> Model like in the actions above but this time deletes the row in the table with the ID supplied in the request. Lastly, it sets a message to the user and redirects to the <strong>index()</strong> method where the message is displayed. </p>

<p>To trigger this <strong>delete()</strong> method, let’s edit the <strong>index.ctp</strong> View and use the <strong>postLink()</strong> function to output a small Form that will send the POST request to delete the table row. It will use javascript to add a confirmation alert box and will then delete the post. Inside the <strong>index.ctp</strong> file after the edit link you can add the following:</p>

<pre><?php echo $this->Form->postLink(
                'Delete',
                array('action' => 'delete', $post['Post']['id']),
                array('confirm' => 'Are you sure you want to delete this post?'));
?></pre>

<p>Save the file and try it out. Now you should be able to delete posts as well.</p>

<p>As a little recap if you followed along, your classes should look like this now:</p>

<h3>PostsController.php - The Controller</h3>

<pre>
class PostsController extends AppController {
  public $helpers = array('Form', 'Html', 'Session');
  public $components = array('Session');
    
  public function index() {
      $this->set('posts', $this->Post->find('all'));
  }
  
  public function view($id = null) {
        $post = $this->Post->findById($id);
        $this->set('post', $post);
  }
  
  public function add() {
        if ($this->request->is('post')) {
            $this->Post->create();
            $post_data = $this->request->data;
            if ($this->Post->save($post_data)<WBR />) {
                $this->Session->setFlash(__('<WBR />New post saved successfully to the database'));
                return $this->redirect(array('action' => 'index'));
            }
            $this->Session->setFlash(__('<WBR />Unable to save the post to the database.'));
        }
  }

  public function edit($id = null) {
    if (!$id) {
        throw new NotFoundException(__('Post is not valid!'));
    }

    $post = $this->Post->findById($id);
    if (!$post) {
        throw new NotFoundException(__('Post is not valid!'));
    }

    if ($this->request->is('post') || $this->request->is('put')) {
        $this->Post->id = $id;
        $post_data = $this->request->data;
        if ($this->Post->save($post_data)<WBR />) {
            $this->Session->setFlash(__('<WBR />Your post has been updated.'));
            return $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('<WBR />Unable to update your post.'));
    }

    if (!$this->request->data) {
        $this->request->data = $post;
    }
  }

 public function delete($id) {
   if ($this->request->is('post')) {
    if ($this->Post->delete($id)) {
      $this->Session->setFlash(__('<WBR />The post number %s has been deleted.', h($id)));
      return $this->redirect(array('action' => 'index'));
    }
   }
  }
}
</pre>

<h3>Post.php - The Model</h3>

<pre>
class Post extends AppModel {
  public $validate = array('title' => array('rule' => 'notEmpty'));
}
</pre>

<h2>Conclusion</h2>

<p>We’ve seen in this short tutorial series how easy it is to work with CakePHP to perform CRUD operations on your data. We learned how to read and display information, how to edit and delete it, and how to add a new one. Furthermore, an important lesson to learn has been that following conventions set in place by CakePHP is highly recommended as it makes your life much easier. I encourage you to take this small application you created and play with it to build something bigger. To do this you should read <a href="http://book.cakephp.org/2.0/en/cakephp-overview.html" target="_blank">more information</a> about CakePHP components and helpers.</p> 

<div class="author"">Article Submitted by: <a href="http://www.webomelette.com/">Danny</a></div></div>
    