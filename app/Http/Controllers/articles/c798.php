<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">How are variables scoped in C – Static or Dynamic?</h1>
				
			
			<p>In C, variables are always <a>statically (or lexically) scoped</a> i.e., binding of a variable can be determined by  program text and is independent of the run-time function call stack. <span id="more-7412"></span></p>
<p>For example, output for the below program is 0, i.e., the value returned by f() is not dependent on who is calling it.  f() always returns the value of global variable x.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int x = 0;
int f()
{
   return x;
}
int g()
{
   int x = 1;
   return f();
}
int main()
{
  printf("%d", g());
  printf("\n");
  getchar();
}
</pre>
<p>References:<br />
<a>http://en.wikipedia.org/wiki/Scope_%28programming%29</a></p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		