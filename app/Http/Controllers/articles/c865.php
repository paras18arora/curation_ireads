<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">gets() is risky to use!</h1>
				
			
			<p>Asked by <a>geek4u</a></p>
<p>Consider the below program. <span id="more-5335"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
void read()
{
   char str[20];
   gets(str);
   printf("%s", str);
   return;
}
</pre>
<p>The code looks simple, it reads string from standard input and prints the entered string, but it suffers from <a>Buffer Overflow</a> as gets() doesn't do any array bound testing. gets() keeps on reading until it sees a newline character. </p>
<p>To avoid Buffer Overflow, fgets() should be used instead of gets() as fgets() makes sure that not more than MAX_LIMIT characters are read.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#define MAX_LIMIT 20
void read()
{
   char str[MAX_LIMIT];
   fgets(str, MAX_LIMIT, stdin);
   printf("%s", str);

   getchar();
   return;
}
</pre>
<p>Please write comments if you find anything incorrect in the above article, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		