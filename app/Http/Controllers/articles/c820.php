<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Scansets in C</h1>
				
			
			<p>scanf family functions support scanset specifiers which are represented by %[]. Inside scanset, we can specify single character or range of characters.<span id="more-19140"></span> While processing scanset, scanf will process only those characters which are part of scanset. We can define scanset by putting characters inside squre brackets. Please note that the scansets are case-sensitive.</p>
<p>Let us see with example.  Below example will store only capital letters to character array ‘str', any other character will not be stored inside character array.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* A simple scanset example */
#include <stdio.h>

int main(void)
{
    char str[128];

    printf("Enter a string: ");
    scanf("%[A-Z]s", str);

    printf("You entered: %s\n", str);

    return 0;
}
</pre>
<pre>
  [root@centos-6 C]# ./scan-set 
  Enter a string: GEEKs_for_geeks
  You entered: GEEK
</pre>
<p>If first character of scanset is ‘^', then the specifier will stop reading after first occurence of that character. For example, given below scanset will read all characters but stops after first occurence of ‘o'</p>
<pre class="brush: cpp; title: ; notranslate" title="">
	scanf("%[^o]s", str);
</pre>
<p>Let us see with example.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* Another scanset example with ^ */
#include <stdio.h>

int main(void)
{
    char str[128];

    printf("Enter a string: ");
    scanf("%[^o]s", str);

    printf("You entered: %s\n", str);

    return 0;
}
</pre>
<pre>
  [root@centos-6 C]# ./scan-set 
  Enter a string: http://geeks for geeks
  You entered: http://geeks f
  [root@centos-6 C]# 
</pre>
<p>Let us implement gets() function by using scan set. gets() fucntion reads a line from stdin into the buffer pointed to by s until either a terminating newline or EOF found.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* implementation of gets() function using scanset */
#include <stdio.h>

int main(void)
{
    char str[128];

    printf("Enter a string with spaces: ");
    scanf("%[^\n]s", str);

    printf("You entered: %s\n", str);

    return 0;
}
</pre>
<pre>
  [root@centos-6 C]# ./gets 
  Enter a string with spaces: Geeks For Geeks
  You entered: Geeks For Geeks
  [root@centos-6 C]# 
</pre>
<p>As a side note, using gets() may not be a good indea in general. Check below note from Linux man page.</p>
<p><em>Never  use  gets(). Because it is impossible to tell without knowing the data in advance how many characters gets() will read, and because gets() will continue to store characters past the end of the buffer, it is extremely dangerous to use. It has  been  used  to  break  computer security. Use fgets() instead. </em>Also see <a>this </a>post.</p>
<p>This article is compiled by "Narendra Kangralkar" and reviewed by GeeksforGeeks team. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		