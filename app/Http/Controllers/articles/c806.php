<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Integer Promotions in C</h1>
				
			
			<p>Some data types like <em>char </em>, <em>short int </em> take less number of bytes than <em>int</em>, these data types are automatically promoted to <em>int </em>or <em>unsigned int</em><span id="more-127427"></span> when an operation is performed on them. This is called integer promotion. For example no arithmetic calculation happens on smaller types like <em>char</em>, <em>short </em>and <em>enum</em>.  They are first converted to <em>int </em>or <em>unsigned int</em>, and then arithmetic is done on them.  If an <em>int </em>can represent all values of the original type, the value is converted to an <em>int </em>. Otherwise, it is converted to an <em>unsigned int.</em></p>
<p>For example see the following program.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h> 
int main()
{
    char a = 30, b = 40, c = 10;
    char d = (a * b) / c;
    printf ("%d ", d); 
    return 0;
}</pre>
<p>Output:
</p><pre>120</pre>
<p>At first look, the expression (a*b)/c seems to cause arithmetic overflow because signed characters can have values only from -128 to 127 (in most of the C compilers), and the value of subexpression ‘(a*b)' is 1200 which is greater than 128.  But integer promotion happens here in arithmetic done on char types and we get the appropriate result without any overflow.</p>
<p>Consider the following program as <strong>another example</strong>.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

int main()
{
    char a = 0xfb;
    unsigned char b = 0xfb;

    printf("a = %c", a);
    printf("\nb = %c", b);

    if (a == b)
      printf("\nSame");
    else
      printf("\nNot Same");
    return 0;
}</pre>
<p>Output:
</p><pre>a = ?
b = ?
Not Same </pre>
<p>When we print ‘a' and ‘b', same character is printed, but when we compare them, we get the output as "Not Same".<br />
‘a' and ‘b' have same binary representation as <em>char</em>. But when comparison operation is performed on ‘a' and ‘b', they are first converted to int.  ‘a' is a signed <em>char</em>, when it is converted to <em>int</em>, its value becomes -5 (signed value of 0xfb).   ‘b' is <em>unsigned char</em>, when it is converted to <em>int</em>, its value becomes 251.  The values -5 and 251 have different representations as <em>int</em>, so we get the output as "Not Same".</p>
<p>We will soon be discussing integer conversion rules between signed and unsigned, int and long int, etc.</p>
<p><strong>References:</strong><br />
<a target="_blank">	 	http://www.tru64unix.compaq.com/docs/base_doc/DOCUMENTATION/V40F_HTML/AQTLTBTE/DOCU_067.HTM</a></p>
<p>This article is contributed by <strong>Abhay Rathi</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		