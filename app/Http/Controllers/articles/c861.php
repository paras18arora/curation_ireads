<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">What is the difference between single quoted and double quoted declaration of char array?</h1>
				
			
			<p>In C/C++, when a character array is initialized with a double quoted string and array size is not specified, compiler automatically allocates one extra space for string terminator ‘\0′.  For example, following program prints 6 as output. <span id="more-9202"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
  char arr[] = "geeks"; // size of arr[] is 6 as it is '\0' terminated
  printf("%d", sizeof(arr));
  getchar();
  return 0;
}
</pre>
<p>If array size is specified as 5 in the above program then the program works without any warning/error and prints 5 in C, but causes compilation error in C++.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
// Works in C, but compilation error in C++
#include<stdio.h>
int main()
{
  char arr[5] = "geeks";  // arr[] is not terminated with '\0'
                                   // and its size is 5
  printf("%d", sizeof(arr));
  getchar();
  return 0;
}
</pre>
<p>When character array is initialized with comma separated list of characters and array size is not specified, compiler doesn't create extra space for string terminator ‘\0′. For example, following program prints 5.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
  char arr[]= {'g', 'e', 'e', 'k', 's'}; // arr[] is not terminated with '\0' and its size is 5
  printf("%d", sizeof(arr));
  getchar();
  return 0;
}
</pre>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		