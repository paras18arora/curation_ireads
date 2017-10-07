<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Initialization of static variables in C</h1>
				
			
			<p>In C, static variables can only be initialized using constant literals. For example, following program fails in compilation.<span id="more-10302"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int initializer(void)
{
    return 50;
}

int main()
{
    static int i = initializer();
    printf(" value of i = %d", i);
    getchar();
    return 0;
}
</pre>
<p>If we change the program to following, then it works without any error.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
    static int i = 50;
    printf(" value of i = %d", i);
    getchar();
    return 0;
}
</pre>
<p>The reason for this is simple: All objects with static storage duration must be initialized (set to their initial values) before execution of main() starts.  So a value which is not known at translation time cannot be used for initialization of static variables.</p>
<p>Thanks to <a>Venki and Prateek </a>for their contribution.</p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		