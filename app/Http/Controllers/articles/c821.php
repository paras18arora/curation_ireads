<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">puts() vs printf() for printing a string</h1>
				
			
			<p>In C, given a string variable <em>str</em>, which of the following two should be preferred to print it to stdout? <span id="more-10032"></span></p>
<pre>
  1)  puts(str);
</pre>
<pre>
  2)  printf(str);
</pre>
<p>puts() can be preferred for printing a string because it is generally less expensive (implementation of puts() is generally simpler than printf()), and if the string has formatting characters like ‘%', then printf() would give unexpected results.  Also, if str is a user input string, then use of printf() might cause security issues (see <a>this </a>for details).<br />
Also note that puts() moves the cursor to next line. If you do not want the cursor to be moved to next line, then you can use following variation of puts().</p>
<pre>
   fputs(str, stdout)
</pre>
<p>You can try following programs for testing the above discussed differences between puts() and printf().</p>
<p><strong>Program 1</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
    puts("Geeksfor");
    puts("Geeks");
    
    getchar();
    return 0;
}
</pre>
<p><strong>Program 2</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
    fputs("Geeksfor", stdout);
    fputs("Geeks", stdout);
    
    getchar();
    return 0;
}
</pre>
<p><strong>Program 3</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
    // % is intentionally put here to show side effects of using printf(str)
    printf("Geek%sforGeek%s");  
    getchar();
    return 0;
}
</pre>
<p><strong>Program 4</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{    
    puts("Geek%sforGeek%s");    
    getchar();
    return 0;
}
</pre>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		