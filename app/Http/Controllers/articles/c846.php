<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Write a C macro PRINT(x) which prints x</h1>
				
			
			<p>At the first look, it seems that writing a C macro which prints its argument is child's play. <span id="more-1107"></span> Following program should work i.e. it should print <em>x</em></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#define PRINT(x) (x)
int main()
{ 
  printf("%s",PRINT(x));
  return 0;
}
</pre>
<p>But it would issue compile error because the data type of <em>x</em>, which is taken as variable by the compiler, is unknown. Now it doesn't look so obvious. Isn't it? Guess what, the followings also won't work </p>
<pre class="brush: cpp; title: ; notranslate" title="">
#define PRINT(x) ('x')
#define PRINT(x) ("x")
</pre>
<p>But if we know one of lesser known traits of C language, writing such a macro is really a child's play. <img src="http://d1gjlxt8vb0knt.cloudfront.net//wp-includes/images/smilies/simple-smile.png" alt=":)" class="wp-smiley" style="height: 1em; max-height: 1em;" /> In C, there's a # directive, also called ‘Stringizing Operator', which does this magic. Basically # directive converts its argument in a string. Voila! it is so simple to do the rest. So the above program can be modified as below.</p>
<pre class="brush: cpp; highlight: [1]; title: ; notranslate" title="">
#define PRINT(x) (#x)
int main()
{ 
  printf("%s",PRINT(x));
  return 0;
}
</pre>
<p>Now if the input is <em>PRINT(x)</em>, it would print <em>x</em>. In fact, if the input is <em>PRINT(geeks)</em>, it would print <em>geeks</em>.</p>
<p>You may find the details of this directive from Microsoft portal <a><strong>here</strong></a>.<br />
<em></em></p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		