<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">What is use of %n in printf() ?</h1>
				
			
			<p>In C printf(), %n is a special format specifier which instead of printing something causes printf() to load the variable pointed by the  corresponding argument with a value equal to the number of characters that have been printed by printf() before the occurrence of %n.<span id="more-8608"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>

int main()
{
  int c;
  printf("geeks for %ngeeks ", &c);
  printf("%d", c);
  getchar();
  return 0;
}
</pre>
<p>The above program prints "geeks for geeks 10″. The first printf() prints "geeks for geeks".  The second printf() prints 10 as there are 10 characters printed (the 10 characters are "geeks for ") before %n in first printf() and c is set to 10 by first printf().</p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		