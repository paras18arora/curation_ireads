<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">fseek() vs rewind() in C</h1>
				
			
			<p>In C,  fseek() should be preferred over rewind().<span id="more-11265"></span></p>
<p>Note the following text C99 standard:<br />
<em>The rewind function sets the file position indicator for the stream pointed to by stream to the beginning of the file. It is equivalent to </em>
</p><pre class="brush: cpp; title: ; notranslate" title="">     (void)fseek(stream, 0L, SEEK_SET) </pre>
<p><em>except that the error indicator for the stream is also cleared.</em></p>
<p>This following code example sets the file position indicator of an input stream back to the beginning using rewind().  But there is no way to check whether the rewind() was successful.</p>
<pre class="brush: cpp; highlight: [11]; title: ; notranslate" title="">
int main()
{
  FILE *fp = fopen("test.txt", "r");

  if ( fp == NULL ) {
    /* Handle open error */
  }

  /* Do some processing with file*/

  rewind(fp);  /* no way to check if rewind is successful */

  /* Do some more precessing with file */

  return 0;
}
</pre>
<p><strong>In the above code, fseek() can be used instead of rewind() to see if the operation succeeded. Following lines of code can be used in place of rewind(fp); </strong> </p>
<pre class="brush: cpp; highlight: [1,2,3]; title: ; notranslate" title="">
if ( fseek(fp, 0L, SEEK_SET) != 0 ) {
  /* Handle repositioning error */
}
</pre>
<p><strong>Source:</strong><a><br />
https://www.securecoding.cert.org/confluence/display/seccode/FIO07-C.+Prefer+fseek%28%29+to+rewind%28%29</a></p>
<p>This article is contributed by <strong>Rahul Gupta</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		