<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Scope rules in C</h1>
				
			
			<p>Scope of an identifier is the part of the program where the identifier may directly be accessible.  In C, all identifiers are <a>lexically (or statically) scoped</a>.  C scope rules can be covered under following two categories.<span id="more-15415"></span></p>
<p><strong>Global Scope:</strong> Can be accessed anywhere in a program. </p>
<pre class="brush: cpp; title: ; notranslate" title="">
// filename: file1.c
int a;
int main(void)
{
   a = 2;
}
</pre>
<pre class="brush: cpp; title: ; notranslate" title="">
// filename: file2.c
// When this file is linked with file1.c, functions of this file can access a
extern int a;
int myfun()
{
   a = 2;
}
</pre>
<p>To restrict access to current file only, global variables can be marked as static.</p>
<p><strong>Block Scope:</strong> A Block is a set of statements enclosed within left and right braces ({ and } respectively). Blocks may be nested in C (a block may contain other blocks inside it).  A variable declared in a block is accessible in the block and all inner blocks of that block, but not accessible outside the block.</p>
<p><em>What if the inner block itself has one variable with the same name?</em><br />
If an inner block declares a variable with the same name as the variable declared by the outer block, then the visibility of the outer block variable ends at the pint of declaration by inner block.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
  {
      int x = 10, y  = 20;
      {
          // The outer block contains declaration of x and y, so 
          // following statement is valid and prints 10 and 20
          printf("x = %d, y = %d\n", x, y);
          {
              // y is declared again, so outer block y is not accessible 
              // in this block
              int y = 40;
   
              x++;  // Changes the outer block variable x to 11
              y++;  // Changes this block's variable y to 41
     
              printf("x = %d, y = %d\n", x, y);
          }

          // This statement accesses only outer block's variables
          printf("x = %d, y = %d\n", x, y);
      }
  }
  return 0;
}
</pre>
<p>Output:</p>
<pre>
x = 10, y = 20
x = 11, y = 41
x = 11, y = 20
</pre>
<p><em>What about functions and parameters passed to functions?</em><br />
A function itself is a block.  Parameters and other local variables of a function follow the same block scope rules.</p>
<p><em>Can variables of block be accessed in another subsequent block?*</em><br />
No, a variabled declared in a block can only be accessed inside the block and all inner blocks of this block.  For example, following program produces compiler error.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
  {
      int x = 10;
  }
  {
      printf("%d", x);  // Error: x is not accessible here
  }
  return 0;
}
</pre>
<p>Output:</p>
<pre>
error: 'x' undeclared (first use in this function)
</pre>
<p>As an exercise, predict the output of following program.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
  int x = 1, y = 2, z = 3;
  printf(" x = %d, y = %d, z = %d \n", x, y, z);
  {
       int x = 10;
       float y = 20;
       printf(" x = %d, y = %f, z = %d \n", x, y, z);
       {
             int z = 100;
             printf(" x = %d, y = %f, z = %d \n", x, y, z);
       }
  }
  return 0;
}
</pre>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		