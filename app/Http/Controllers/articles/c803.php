<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">G-Fact 19 (Redeclaration of global variable in C)</h1>
				
			
			<p>Consider the below two programs:</p>
<pre class="brush: cpp; title: ; notranslate" title="">
// Program 1
int main()
{
   int x;
   int x = 5;
   printf("%d", x);
   return 0; 
}
</pre>
<p>Output in C:
</p><pre>redeclaration of ‘x' with no linkage</pre>
<pre class="brush: cpp; title: ; notranslate" title="">
// Program 2
int x;
int x = 5;

int main()
{
   printf("%d", x);
   return 0; 
}
</pre>
<p>Output in C:
</p><pre>5</pre>
<p>In C, the first program fails in compilation, but second program works fine.  In C++, both programs fail in compilation.</p>
<p><strong><br />
C allows a global variable to be declared again when first declaration doesn't initialize the variable.</strong></p>
<p>The below program fails in both C also as the global variable is initialized in first declaration itself.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int x = 5;
int x = 10;

int main()
{
   printf("%d", x);
   return 0;
}</pre>
<p>Output:
</p><pre> error: redefinition of ‘x'</pre>
<p>This article is contributed <strong>Abhay Rathi</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		