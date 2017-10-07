<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">How to print % using printf()?</h1>
				
			
			<p>Asked by Tanuj</p>
<p>Here is the standard prototype of printf function in C.<br />
<span id="more-1300"></span></p>
<pre>
          int printf(const char *format, ...);
</pre>
<p>The format string is composed of zero or more   directives:  ordinary  characters  (not  %),  which  are  copied  unchanged to the output stream; and conversion specifications, each  of  argument (and it is an  error  if  insufficiently  many  arguments  are  given).</p>
<p>The character % is followed by one of the following characters.</p>
<p>The flag character<br />
The field width<br />
The precision<br />
The length modifier<br />
The conversion specifier: </p>
<p>See <a>http://swoolley.org/man.cgi/3/printf</a> for details of all the above characters. The main thing to note in the standard is the below line about conversion specifier.</p>
<pre>
A `%' is written. No argument is converted. The complete conversion specification is`%%'.
</pre>
<p>So we can print "%" using "%%"</p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* Program to print %*/
#include<stdio.h>
/* Program to print %*/
int main()
{
   printf("%%");
   getchar();
   return 0;
}
</pre>
<p>We can also print "%" using below.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
   printf("%c", '%');
   printf("%s", "%");
</pre>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		