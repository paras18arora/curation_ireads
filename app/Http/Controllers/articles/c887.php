<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Static functions in C</h1>
				
			
			<p>In C, functions are global by default. The "<em>static</em>" keyword before a function name makes it static. For example, below function <em>fun() </em>is static. <span id="more-7244"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
static int fun(void)
{
  printf("I am a static function ");
}
</pre>
<p>Unlike global functions in C, access to static functions is restricted to the file where they are declared.  Therefore, when we want to restrict access to functions, we make them static.  Another reason for making functions static can be reuse of the same function name in other files.</p>
<p>For example, if we store following program in one file <em>file1.c</em></p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* Inside file1.c */ 
static void fun1(void)
{
  puts("fun1 called");
}
</pre>
<p>And store following program in another file <em>file2.c</em></p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* Iinside file2.c  */ 
int main(void)
{
  fun1(); 
  getchar();
  return 0;  
}
</pre>
<p>Now, if we compile the above code with command "<em>gcc  file2.c file1.c</em>",  we get the error <em>"undefined reference to `fun1'"</em> . This is because <em>fun1()</em> is declared <em>static </em>in <em>file1.c</em> and cannot be used in <em>file2.c</em>.  </p>
<p>Please write comments if you find anything incorrect in the above article, or want to share more information about static functions in C.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		