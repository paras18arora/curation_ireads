<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">exit(), abort() and assert()</h1>
				
			
			<p><strong>exit()</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
void exit ( int status ); 
</pre>
<p>exit() terminates the process normally. <span id="more-6672"></span><br />
status:  Status value returned to the parent process. Generally, a status value of 0 or EXIT_SUCCESS indicates success, and any other value or the constant EXIT_FAILURE is used to indicate an error. exit() performs following operations.<br />
* Flushes unwritten buffered data.<br />
* Closes all open files.<br />
* Removes temporary files.<br />
* Returns an integer exit status to the operating system.</p>
<p>The C standard <a>atexit() </a>function can be used to customize exit() to perform additional actions at program termination.</p>
<p>Example use of exit.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* exit example */
#include <stdio.h>
#include <stdlib.h>
 
int main ()
{
  FILE * pFile;
  pFile = fopen ("myfile.txt", "r");
  if (pFile == NULL)
  {
    printf ("Error opening file");
    exit (1);
  }
  else
  {
    /* file operations here */
  }
  return 0;
}
</pre>
<p><br />
<strong>abort()</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
 void abort ( void );
</pre>
<p>Unlike exit() function, abort() may not close files that are open. It may also not delete temporary files and may not flush stream buffer. Also, it does not call functions registered with <a>atexit()</a>.</p>
<p>This function actually terminates the process by raising a SIGABRT signal, and your program can include a handler to intercept this signal (see <a>this</a>).</p>
<p>So programs like below might not write "Geeks for Geeks" to "tempfile.txt"</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
#include<stdlib.h>
int main()
{
  FILE *fp = fopen("C:\\myfile.txt", "w");
  
  if(fp == NULL)
  {
    printf("\n could not open file ");
    getchar();
    exit(1);
  }  
  
  fprintf(fp, "%s", "Geeks for Geeks");
  
  /* ....... */
  /* ....... */
  /* Something went wrong so terminate here */  
  abort();
  
  getchar();
  return 0;  
}    
</pre>
<p>If we want to make sure that data is written to files and/or buffers are flushed then we should either use exit() or include a signal handler for SIGABRT.</p>
<p><br />
<strong>assert()</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
void assert( int expression );
</pre>
<p>If expression evaluates to 0 (false), then the expression, sourcecode filename, and line number are sent to the standard error, and then  abort()  function is called. If the identifier NDEBUG ("no debug") is defined with #define NDEBUG then the macro assert does nothing.</p>
<p>Common error outputting is in the form:</p>
<p><em>    Assertion failed: expression, file filename, line line-number </em></p>
<pre class="brush: cpp; title: ; notranslate" title="">

#include<assert.h>

void open_record(char *record_name)
{
    assert(record_name != NULL);
    /* Rest of code */
}

int main(void)
{
   open_record(NULL);
}
</pre>
<p>This article is contributed by <strong>Rahul Gupta</strong>. Please write comments if you find anything incorrect in the above article or you want to share more information about the topic discussed above.<br />
References:<br />
<a>http://www.cplusplus.com/reference/clibrary/cstdlib/abort/</a><br />
<a>http://www.cplusplus.com/reference/clibrary/cassert/assert/</a><br />
<a>http://www.acm.uiuc.edu/webmonkeys/book/c_guide/2.1.html</a><br />
<a>https://www.securecoding.cert.org/confluence/display/seccode/ERR06-C.+Understand+the+termination+behavior+of+assert%28%29+and+abort%28%29</a><br />
<a>https://www.securecoding.cert.org/confluence/display/seccode/ERR04-C.+Choose+an+appropriate+termination+strateg</a>y</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		