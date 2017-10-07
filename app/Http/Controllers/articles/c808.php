<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Comparison of a float with a value in C</h1>
				
			
			<p>Predict the output of following C program.<span id="more-130369"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
    float x = 0.1;
    if (x == 0.1)
        printf("IF");
    else if (x == 0.1f)
        printf("ELSE IF");
    else
        printf("ELSE");
}
</pre>
<p>The output of above program is "<em><strong>ELSE IF</strong></em>" which means the expression "x == 0.1″ returns false and expression "x == 0.1f" returns true.  </p>
<p>Let consider the of following program to understand the reason behind the above output.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
   float x = 0.1;
   printf("%d %d %d", sizeof(x), sizeof(0.1), sizeof(0.1f));
   return 0;
}
</pre>
<p>The output of above program is "<em><strong>4 8 4</strong></em>" on a typical C compiler.  It actually prints size of float, size of double and size of float.</p>
<p>The values used in an expression are considered as double (<a target="_blank">double precision floating point format</a>) unless a ‘f' is specified at the end.  So the expression "x==0.1″ has a double on right side and float which are stored in a <a target="_blank">single precision floating point format</a> on left side.  In such situations float is promoted to double (see <a target="_blank">this</a>). The double precision format uses uses more bits for precision than single precision format.<br />
Note that the promotion of float to double can only cause mismatch when a value (like 0.1) uses more precision bits than the bits of single precision.  For example, the following C program prints "IF".</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
    float x = 0.5;
    if (x == 0.5)
        printf("IF");
    else if (x == 0.5f)
        printf("ELSE IF");
    else
        printf("ELSE");
}
</pre>
<p>Output:
</p><pre>IF</pre>
<p>You can refer <a target="_blank">Floating Point Representation – Basics</a> for representation of floating point numbers.</p>
<p>This article is contributed by <strong>Abhay Rathi</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		