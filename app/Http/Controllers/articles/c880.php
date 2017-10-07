<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Functions that are executed before and after main() in C</h1>
				
			
			<p>With GCC family of C compilers, we can mark some functions to execute before and after main(). <span id="more-14538"></span>So some startup code can be executed before main() starts, and some cleanup code can be executed after main() ends. <!--more--> For example, in the following program, myStartupFun() is called before main() and myCleanupFun() is called after main().</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>

/* Apply the constructor attribute to myStartupFun() so that it
    is executed before main() */
void myStartupFun (void) __attribute__ ((constructor));


/* Apply the destructor attribute to myCleanupFun() so that it
   is executed after main() */
void myCleanupFun (void) __attribute__ ((destructor));


/* implementation of myStartupFun */
void myStartupFun (void)
{
    printf ("startup code before main()\n");
}

/* implementation of myCleanupFun */
void myCleanupFun (void)
{
    printf ("cleanup code after main()\n");
}

int main (void)
{
    printf ("hello\n");
    return 0;
}
</pre>
<p>Output:</p>
<pre>
startup code before main()
hello
cleanup code after main()
</pre>
<p>Like the above feature, GCC has added many other interesting features to standard C language. See <a>this </a> for more details.</p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		