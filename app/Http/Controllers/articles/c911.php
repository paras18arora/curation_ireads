<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">How does free() know the size of memory to be deallocated?</h1>
				
			
			<p>Consider the following prototype of <em><a>free()</a></em> function which is used to free memory allocated using <em>malloc() </em>or <em>calloc()</em> or <em>realloc()</em>.<span id="more-13219"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
  void free(void *ptr);
</pre>
<p>Note that the free function does not accept size as a parameter. How does free() function know how much memory to free given just a pointer?</p>
<p>Following is the most common way to store size of memory so that free() knows the size of memory to be deallocated.<br />
<em>When memory allocation is done, the actual heap space allocated is one word larger than the requested memory. The extra word is used to store the size of the allocation and is later used by free( )</em></p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>
<p>References:<br />
<a>http://www.cs.cmu.edu/afs/cs/academic/class/15213-f10/www/lectures/17-allocation-basic.pptx</a><br />
<a>http://en.wikipedia.org/wiki/Malloc</a></p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		