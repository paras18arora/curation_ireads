<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">What should be data type of case labels of switch statement in C?</h1>
				
			
			<p>In C switch statement, the expression of each case label must be an integer constant expression. <span id="more-9040"></span></p>
<p>For example, the following program fails in compilation.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* Using non-const in case label */
#include<stdio.h>
int main()
{
  int i = 10;
  int c = 10;
  switch(c) 
  {
    case i: // not a "const int" expression
         printf("Value of c = %d", c);
         break;
    /*Some more cases */
                   
  }
  getchar();
  return 0;
}
</pre>
<p>Putting <em>const </em>before<em> i </em>makes the above program work.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
  const int i = 10;
  int c = 10;
  switch(c) 
  {
    case i:  // Works fine
         printf("Value of c = %d", c);
         break;
    /*Some more cases */
                   
  }
  getchar();
  return 0;
}
</pre>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		