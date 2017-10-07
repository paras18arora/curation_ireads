<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Difference between ++*p, *p++ and *++p</h1>
				
			
			<p>Predict the output of following C programs.<span id="more-125812"></span></p>
<pre class="brush: cpp; highlight: [7]; title: ; notranslate" title="">
// PROGRAM 1
#include <stdio.h>
int main(void)
{
    int arr[] = {10, 20};
    int *p = arr;
    ++*p;
    printf("arr[0] = %d, arr[1] = %d, *p = %d", arr[0], arr[1], *p);
    return 0;
}
</pre>
<pre class="brush: cpp; highlight: [7]; title: ; notranslate" title="">
// PROGRAM 2
#include <stdio.h>
int main(void)
{
    int arr[] = {10, 20};
    int *p = arr;
    *p++;
    printf("arr[0] = %d, arr[1] = %d, *p = %d", arr[0], arr[1], *p);
    return 0;
}
</pre>
<pre class="brush: cpp; highlight: [7]; title: ; notranslate" title="">
// PROGRAM 3
#include <stdio.h>
int main(void)
{
    int arr[] = {10, 20};
    int *p = arr;
    *++p;
    printf("arr[0] = %d, arr[1] = %d, *p = %d", arr[0], arr[1], *p);
    return 0;
}
</pre>
<p>The output of above programs and all such programs can be easily guessed by remembering following simple rules about postfix ++, prefix ++ and * (dereference) operators<br />
<strong>1)</strong> Precedence of prefix ++ and * is same. Associativity of both is right to left.<br />
<strong>2)</strong> Precedence of postfix ++ is higher than both * and prefix ++.  Associativity of postfix ++ is left to right.</p>
<p>(Refer: <a target="_blank">Precedence Table</a>)</p>
<p>The expression <strong>++*p</strong> has two operators of same precedence, so compiler looks for assoiativity. Associativity of operators is right to left. Therefore the expression is treated as <em><strong>++(*p)</strong></em>. Therefore the output of first program is "<em>arr[0] = 11, arr[1] = 20, *p = 11</em>".</p>
<p>The expression <strong>*p++</strong> is treated as<em><strong> *(p++)</strong> </em>as the precedence of postfix ++ is higher than *. Therefore the output of second program is "<em>arr[0] = 10, arr[1] = 20, *p = 20</em>".</p>
<p>The expression <strong>*++p</strong> has two operators of same precedence, so compiler looks for assoiativity. Associativity of operators is right to left. Therefore the expression is treated as <em><strong>*(++p)</strong></em>. Therefore the output of second program is "<em>arr[0] = 10, arr[1] = 20, *p = 20</em>".</p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		