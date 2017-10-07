<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Use of bool in C</h1>
				
			
			<p>The <a>C99 standard for C language</a> supports bool variables. <span id="more-22756"></span> Unlike C++, where no header file is needed to use bool, a header file "stdbool.h" must be included to use bool in C. If we save the below program as .c, it will not compile, but if we save it as .cpp, it will work fine.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
  bool arr[2] = {true, false};
  return 0;
}
</pre>
<p>If we include the header file "stdbool.h" in the above program, it will work fine as a C  program.       </p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdbool.h>
int main()
{
  bool arr[2] = {true, false};
  return 0;
}
</pre>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		