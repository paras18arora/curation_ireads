<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Complicated declarations in C</h1>
				
			
			<p>Most of the times declarations are simple to read, but it is hard to read some declarations which involve pointer to functions.  For example, consider the following declaration from "signal.h".<span id="more-16841"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
 void (*bsd_signal(int, void (*)(int)))(int);
</pre>
<p>Let us see the steps to read complicated declarations.</p>
<p><strong>1)</strong>  Convert C declaration to postfix format and read from left to right.<br />
<strong>2)</strong>  To convert experssion to postfix, start from innermost parenthesis, If innermost parenthesis is not present then start from declarations name and go right first. When first ending parenthesis encounters then go left. Once whole parenthesis is parsed then come out from parenthesis.<br />
<strong>3) </strong> Continue until complete declaration has been parsed.</p>
<p>Let us start with simple example. Below examples are from "K & R"  book.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
1)  int (*fp) ();
</pre>
<p>Let us convert above expression to postfix format. For the above example, there is no innermost parenthesis, that's why, we will print declaration name i.e. "fp".  Next step is, go to right side of expression, but there is nothing on right side of "fp" to parse, that's why go to left side. On left side we found "*", now print "*" and come out of parenthesis. We will get postfix expression as below.</p>
<pre>
  fp  *  ()  int
</pre>
<p>Now read postfix expression from left to right. e.g.  fp is pointer to function returning int</p>
<p>Let us see some more examples.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
2) int (*daytab)[13]
</pre>
<p>Postfix    :  daytab * [13] int<br />
Meaning    :  daytab is pointer to array of 13 integers.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
3) void (*f[10]) (int, int)
</pre>
<p>Postfix    :  f[10] * (int, int) void<br />
Meaning    :  f is an array of 10 of pointer to function(which takes 2 arguments of type int) returning void</p>
<pre class="brush: cpp; title: ; notranslate" title="">
4) char (*(*x())[]) ()
</pre>
<p>Postfix    : x () * [] * () char<br />
Meaning    : x is a function returning pointer to array of pointers to function returnging char</p>
<pre class="brush: cpp; title: ; notranslate" title="">
5) char (*(*x[3])())[5]
</pre>
<p>Postfix    : x[3] * () * [5] char<br />
Meaning    : x is an array of 3 pointers to function returning pointer to array of 5 char's</p>
<pre class="brush: cpp; title: ; notranslate" title="">
6) int *(*(*arr[5])()) ()
</pre>
<p>Postfix    : arr[5] * () * () * int<br />
Meaning    : arr is an array of 5 pointers to functions returning pointer to function returning pointer to integer</p>
<pre class="brush: cpp; title: ; notranslate" title="">
7) void (*bsd_signal(int sig, void (*func)(int)))(int);
</pre>
<p>Postfix    : bsd_signal(int sig, void(*func)(int))  *  (int)  void<br />
Meaning    : bsd_signal is a function that takes integer & a pointer to a function(that takes integer as argument and returns void) and returns pointer to a function(that take integer as argument and returns void)</p>
<p>This article is compiled by "Narendra Kangralkar" and reviewed by GeeksforGeeks team. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		