<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">calloc() versus malloc()</h1>
				
			
			<p>malloc() allocates memory block of given size (in bytes) and returns a pointer to the beginning of the block.<span id="more-5163"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
   void * malloc( size_t size );
</pre>
<p>malloc() doesn't initialize the allocated memory. </p>
<p>calloc() allocates the memory and also initializes the allocates memory to zero.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
  void * calloc( size_t num, size_t size );
</pre>
<p>Unlike malloc(), calloc() takes two arguments: 1) number of blocks to be allocated 2) size of each block.</p>
<p>We can achieve same functionality as calloc() by using malloc() followed by memset(),</p>
<pre class="brush: cpp; title: ; notranslate" title="">
  ptr = malloc(size);
  memset(ptr, 0, size);
</pre>
<p>If we do not want to initialize memory then malloc() is the obvious choice. </p>
<p>Please write comments if you find anything incorrect in the above article or you want to share more information about malloc() and calloc() functions.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		