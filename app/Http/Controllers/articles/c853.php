<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">A C Programming Language Puzzle</h1>
				
			
			<p>Give a = 12 and b = 36 write a C function/macro that returns 3612 without using arithmetic, strings  and predefined functions.<span id="more-135103"></span></p>
<p><strong>We strongly recommend you to minimize your browser and try this yourself first.</strong></p>
<p>Below is one solution that uses String <a>Token-Pasting Operator</a> (##) of C macros.  For example, the expression "a##b" prints concatenation of ‘a' and ‘b'.  </p>
<p>Below is a working C code.</p>
<pre class="brush: cpp; highlight: [2]; title: ; notranslate" title="">
#include <stdio.h>
#define merge(a, b) b##a
int main(void)
{
    printf("%d ", merge(12, 36));
    return 0;
}
</pre>
<p>Output:
</p><pre>3612</pre>
<p>Thanks to an anonymous user to suggest this solution <a>here</a>.</p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		