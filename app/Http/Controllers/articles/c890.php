<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">What happens when a function is called before its declaration in C?</h1>
				
			
			<p>In C, if a function is called before its declaration, the <strong>compiler assumes return type of the function as int</strong>.<span id="more-22065"></span></p>
<p>For example, the following program fails in compilation.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
int main(void)
{
    // Note that fun() is not declared 
    printf("%d\n", fun());
    return 0;
}

char fun()
{
   return 'G';
}
</pre>
<p>The following program compiles and run fine because return type of fun() is changed to int.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
int main(void)
{
    printf("%d\n", fun());
    return 0;
}

int fun()
{
   return 10;
}
</pre>
<p><strong>What about parameters?</strong> compiler assumes nothing about parameters. Therefore, the compiler will not be able to perform compile-time checking of argument types and arity when the function is applied to some arguments. This can cause problems. For example, the following program compiled fine in GCC and produced garbage value as output.  </p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

int main (void)
{
    printf("%d", sum(10, 5));
    return 0;
}
int sum (int b, int c, int a)
{
    return (a+b+c);
}
</pre>
<p>There is this misconception that the compiler assumes input parameters also int. Had compiler assumed input parameters int, the above program would have failed in compilation.</p>
<p>It is always recommended to declare a function before its use so that we don't see any surprises when the program is run (See <a>this </a>for more details).</p>
<p><strong>Source: </strong><br />
<a>http://en.wikipedia.org/wiki/Function_prototype#Uses</a></p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		