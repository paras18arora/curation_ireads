<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">EOF, getc() and feof() in C</h1>
				
			
			<p>In C/C++, <a>getc() </a>returns EOF when end of file is reached.  getc() also returns EOF when it fails.  So, only comparing the value returned by getc() with EOF is not sufficient to check for actual end of file. <span id="more-9797"></span> To solve this problem, C provides <a>feof()</a> which returns non-zero value only if end of file has reached, otherwise it returns 0.<br />
For example, consider the following C program to print contents of file <em>test.txt</em> on screen. In the program, returned value of getc() is compared with EOF first, then there is another check using feof(). By putting this check, we make sure that the program prints <em>"End of file reached"</em> only if end of file is reached.  And if getc() returns EOF due to any other reason, then the program prints <em>"Something went wrong"</em></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

int main()
{
  FILE *fp = fopen("test.txt", "r");
  int ch = getc(fp);
  while (ch != EOF) 
  {
    /* display contents of file on screen */ 
    putchar(ch); 

    ch = getc(fp);
  }
  
  if (feof(fp))
     printf("\n End of file reached.");
  else 
     printf("\n Something went wrong.");
  fclose(fp);
    
  getchar();
  return 0;
}
</pre>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		