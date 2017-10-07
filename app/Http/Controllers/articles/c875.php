<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">For Versus While</h1>
				
			
			<p><strong>Question:</strong> Is there any example for which the following two loops will not work same way? <span id="more-3850"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
/*Program 1 --> For loop*/
for (<init-stmnt>; <boolean-expr>; <incr-stmnt>) 
{
   <body-statements>
}

/*Program 2 --> While loop*/
<init-stmnt>;
while (<boolean-expr>) 
{
   <body-statements>
   <incr-stmnt>
}
</pre>
<p><strong>Solution:</strong><br />
If the body-statements contains continue, then the two programs will work in different ways</p>
<p>See the below examples: Program 1 will print "loop" 3 times but Program 2 will go in an infinite loop.</p>
<p><strong>Example for program 1</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
  int i = 0;
  for(i = 0; i < 3; i++)
  {
    printf("loop ");
    continue;
  } 
  getchar();
  return 0;
}
</pre>
<p><br />
<strong>Example for program 2</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
  int i = 0;
  while(i < 3)
  {
    printf("loop"); /* printed infinite times */
    continue;
    i++; /*This statement is never executed*/
  } 
  getchar();
  return 0;
}
</pre>
<p><br />
Please write comments if you want to add more solutions for the above question.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		