<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Understanding “register” keyword in C</h1>
				
			
			<p>Registers are faster than memory to access, so the variables which are most frequently used in a C program can be put in registers using <em>register </em>keyword. <span id="more-4346"></span> The keyword <em>register</em> hints to compiler that a given variable can be put in a register. It's compiler's choice to put it in a register or not.  Generally, compilers themselves do optimizations and put the variables in register. </p>
<p>1) If you use & operator with a register variable then compiler may give an error or warning (depending upon the compiler you are using), because when we say a variable is a register, it may be stored in a register instead of memory and accessing address of a register is invalid. Try below program.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
  register int i = 10;
  int *a = &i;
  printf("%d", *a);
  getchar();
  return 0;
}
</pre>
<p>2) <em>register</em> keyword can be used with pointer variables.  Obviously, a register can have address of a memory location. There would not be any problem with the below program.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
  int i = 10;
  register int *a = &i;
  printf("%d", *a);
  getchar();
  return 0;
}
</pre>
<p>3) Register is a storage class, and C doesn't allow multiple storage class specifiers for a variable.  So, <em>register </em> can not be used with <em>static </em>.  Try below program.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
  int i = 10;
  register static int *a = &i;
  printf("%d", *a);
  getchar();
  return 0;
}
</pre>
<p>4) There is no limit on number of register variables in a C program, but the point is compiler may put some variables in register and some not.</p>
<p>Please write comments if you find anything incorrect in the above article or you want to share more information about register keyword. </p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		