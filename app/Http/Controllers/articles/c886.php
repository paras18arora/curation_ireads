<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">What is the purpose of a function prototype?</h1>
				
			
			<p>The Function prototype serves the following purposes –<br />
<span id="more-1061"></span><br />
        1) It tells the return type of the data that the function will return.<br />
        2) It tells the number of arguments passed to the function.<br />
        3) It tells the data types of the each of the passed arguments.<br />
        4) Also it tells the order in which the arguments are passed to the function.</p>
<p>        Therefore essentially, function prototype specifies the input/output interlace to the function i.e. what to give to the function and what to expect from the function.</p>
<p>        Prototype of a function is also called signature of the function.</p>
<p><strong>What if one doesn't specify the function prototype?</strong><br />
Output of below kind of programs is generally asked at many places.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
   foo();
   getchar();
   return 0;
}
void foo()
{
   printf("foo called");
}
</pre>
<p>If one doesn't specify the function prototype, the behavior is specific to C standard (either C90 or C99) that the compilers implement. Up to C90 standard, C compilers assumed the return type of the omitted function prototype as int. And this assumption at compiler side may lead to unspecified program behavior.</p>
<p>Later C99 standard specified that compilers can no longer assume return type as int. Therefore, C99 became more restrict in type checking of function prototype. But to make C99 standard backward compatible, in practice, compilers throw the warning saying that the return type is assumed as int. But they go ahead with compilation. Thus, it becomes the responsibility of programmers to make sure that the assumed function prototype and the actual function type matches.</p>
<p>To avoid all this implementation specifics of C standards, it is best to have function prototype.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		