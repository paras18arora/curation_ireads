<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">How to declare a pointer to a function?</h1>
				
			
			<p>Well, we assume that you know what does it mean by pointer in C. So how do we create a pointer to an integer in C?<br />
Huh..it is pretty simple..<span id="more-1319"></span></p>
<pre>
int * ptrInteger; /*We have put a * operator between int 
                    and ptrInteger to create a pointer.*/
</pre>
<p>Here ptrInteger is a pointer to integer. If you understand this, then logically we should not have any problem in declaring a  pointer to a function <img src="http://d1gjlxt8vb0knt.cloudfront.net//wp-includes/images/smilies/simple-smile.png" alt=":)" class="wp-smiley" style="height: 1em; max-height: 1em;" /></p>
<p>So let us first see ..how do we declare a function? For example,</p>
<pre>
int foo(int);
</pre>
<p>Here foo is a function that returns int and takes one argument of int type. So as a logical guy will think, by putting a * operator between int and foo(int) should create a pointer to a function i.e.</p>
<pre>
int * foo(int);
</pre>
<p>But Oops..C operator precedence also plays role here ..so in this case, operator () will take priority over operator *. And the above declaration will mean – a function foo with one argument of int type and return value of int * i.e. integer pointer. So it did something that we didn't want to do. <img src="http://d1gjlxt8vb0knt.cloudfront.net//wp-includes/images/smilies/frownie.png" alt=":(" class="wp-smiley" style="height: 1em; max-height: 1em;" /></p>
<p>So as a next logical step, we have to bind operator * with foo somehow. And for this, we would change the default precedence of C operators using () operator.</p>
<pre>
int (*foo)(int);
</pre>
<p>That's it. Here * operator is with foo which is a function name. And it did the same that we wanted to do.</p>
<p>So that wasn't as difficult as we thought earlier! </p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		