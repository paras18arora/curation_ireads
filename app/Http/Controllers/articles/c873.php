<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Variable Length Arrays in C and C++</h1>
				
			
			<p>Variable length arrays is a feature where we can allocate an auto array (on stack) of variable size. C supports variable sized arrays from C99 standard.  For example, the below program compiles and runs fine in C.<span id="more-134945"></span></p>
<pre class="brush: cpp; highlight: [3]; title: ; notranslate" title="">
void fun(int n)
{
  int arr[n];
  // ......
} 
int main()
{
   fun(6);
}
</pre>
<p>But C++ standard (till <a>C++11</a>) doesn't support variable sized arrays.  The C++11 standard mentions array size as a constant-expression See (See 8.3.4 on page 179 of <a>N3337</a>). So the above program may not be a valid C++ program.  The program may work in GCC compiler, because GCC compiler provides an extension to support them.</p>
<p>As a side note, the latest <a>C++14</a> (See 8.3.4 on page 184 of <a>N3690</a>) mentions array size as a simple expression (not constant-expression).</p>
<p><strong>References:</strong><br />
<a>http://stackoverflow.com/questions/1887097/variable-length-arrays-in-c</a><br />
<a>https://gcc.gnu.org/onlinedocs/gcc/Variable-Length.html</a></p>
<p>This article is contributed by <strong>Abhay Rathi</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		