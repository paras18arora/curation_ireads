<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Difference between “int main()” and “int main(void)” in C/C++?</h1>
				
			
			<p>Consider the following two definitions of main().<span id="more-125254"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
   /*  */
   return 0;
}</pre>
<p>and</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main(void)
{
   /*  */
   return 0;
}</pre>
<p>What is the difference?</p>
<p>In C++, there is no difference, both are same.</p>
<p>Both definitions work in C also, but the second definition with void is considered technically better as it clearly specifies that main can only be called without any parameter.<br />
In C, if a function signature doesn't specify any argument, it means that the function can be called with any number of parameters or without any parameters.  For example, try to compile and run following two C programs (remember to save your files as .c).  Note the difference between two signatures of fun().</p>
<pre class="brush: cpp; highlight: [2]; title: ; notranslate" title="">
// Program 1 (Compiles and runs fine in C, but not in C++)
void fun() {  } 
int main(void)
{
    fun(10, "GfG", "GQ");
    return 0;
}
</pre>
<p>The above program compiles and runs fine (See <a target="_blank">this</a>), but the following program fails in compilation (see <a target="_blank">this</a>)</p>
<pre class="brush: cpp; highlight: [2]; title: ; notranslate" title="">
// Program 2 (Fails in compilation in both C and C++)
void fun(void) {  }
int main(void)
{
    fun(10, "GfG", "GQ");
    return 0;
}
</pre>
<p>Unlike C, in C++, both of the above programs fails in compilation.  In C++, both fun() and fun(void) are same.</p>
<p>So the difference is, in C, <em>int main()</em> can be called with any number of arguments, but <em>int main(void)</em> can only be called without any argument. Although it doesn't make any difference most of the times, using "int main(void)" is a recommended practice in C.</p>
<p><strong>Exercise:</strong><br />
Predict the output of following <strong>C</strong> programs.</p>
<p><strong>Question 1</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
int main()
{
    static int i = 5;
    if (--i){
        printf("%d ", i);
        main(10);
    }
}
</pre>
<p><strong>Question 2</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
int main(void)
{
    static int i = 5;
    if (--i){
        printf("%d ", i);
        main(10);
    }
}
</pre>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		