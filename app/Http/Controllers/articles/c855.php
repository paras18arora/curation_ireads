<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">How to print a variable name in C?</h1>
				
			
			<p>How to print and store a variable name in  string variable?<span id="more-19045"></span></p>
<p><strong>We strongly recommend you to minimize your browser and try this yourself first</strong></p>
<p>In C, there's a # directive, also called ‘Stringizing Operator', which does this magic. Basically # directive converts its argument in a string.  </p>
<pre class="brush: cpp; highlight: [2,7]; title: ; notranslate" title="">
#include <stdio.h>
#define getName(var)  #var

int main()
{
	int myVar;
	printf("%s", getName(myVar));
	return 0;
} 
</pre>
<p>Output:
</p><pre>
MyVar</pre>
<p></p>
<p>We can also store variable name in a string using <a>sprintf() in C</a>.</p>
<pre class="brush: cpp; highlight: [2,8]; title: ; notranslate" title="">
# include <stdio.h>
# define getName(var, str)  sprintf(str, "%s", #var) 

int main()
{
	int myVar;
	char str[20];
	getName(myVar, str);
	printf("%s", str);
	return 0;
} 
</pre>
<p>Output:
</p><pre>
MyVar</pre>
<p>This article is contributed by <strong>Abhay Rathi</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			

<!-- GQBottom -->


		