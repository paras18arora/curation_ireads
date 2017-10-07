<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Difference between  getc(), getchar(), getch() and getche()</h1>
				
			
			<p>All of these functions read a character from input and return an integer value.  The integer is returned to accommodate a special value used to indicate failure. <span id="more-15743"></span> The value EOF is generally used for this purpose.</p>
<p><u><strong>getc():</strong></u><br />
It reads a single character from a given input stream and returns the corresponding integer value (typically ASCII value of read character) on success. It returns EOF on failure.</p>
<p>Syntax:
</p><pre>int getc(FILE *stream); </pre>
<p>Example:</p>
<pre class="brush: cpp; title: ; notranslate" title="">
// Example for getc() in C
#include <stdio.h>
int main()
{
   printf("%c", getc(stdin));
   return(0);
}
</pre>
<pre>
Input: g (press enter key)
Output: g </pre>
<p><u><strong>getchar():</strong></u><br />
The difference between getc() and getchar() is getc() can read from any input stream, but getchar() reads from standard input.  So getchar() is equivalent to getc(stdin).  </p>
<p>Syntax:
</p><pre>int getchar(void); </pre>
<p>Example:</p>
<pre class="brush: cpp; title: ; notranslate" title="">
// Example for getchar() in C
#include <stdio.h>
int main()
{
   printf("%c", getchar());
   return 0;
}
</pre>
<pre>
Input: g(press enter key)
Output: g </pre>
<p><u><strong>getch():</strong></u><br />
getch() is a nonstandard function and is present in conio.h header file which is mostly used by MS-DOS compilers like Turbo C. It is not part of the C standard library or ISO C, nor is it defined by POSIX (Source: http://en.wikipedia.org/wiki/Conio.h)<br />
Like above functions, it reads also a single character from keyboard. But it does not use any buffer, so the entered character is immediately returned without waiting for the enter key.<br />
Syntax:
</p><pre>int getch();</pre>
<p>Example:</p>
<pre class="brush: cpp; title: ; notranslate" title="">
// Example for getch() in C
#include <stdio.h>
#include <conio.h>
int main()
{
   printf("%c", getch());   
   return 0;
}</pre>
<pre>
Input:  g (Without enter key)
Output: Program terminates immediately.
        But when you use DOS shell in Turbo C, 
        it shows a single g, i.e., 'g'</pre>
<p><u><strong>getche()</strong></u><br />
Like getch(), this is also a non-standard function present in conio.h.  It reads a single character from the keyboard and displays immediately on output screen without waiting for enter key.</p>
<p>Syntax:
</p><pre>int getche(void); </pre>
<p>Example:</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
#include <conio.h>
// Example for getche() in C
int main()
{
  printf("%c", getche());
  return 0;
}</pre>
<pre>
Input: g(without enter key as it is not buffered)
Output: Program terminates immediately.
        But when you use DOS shell in Turbo C, 
        double g, i.e., 'gg'</pre>
<p>This article is contributed by<strong> Vankayala Karunakar</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			

<!-- GQBottom -->


		