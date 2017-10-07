<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Results of comparison operations in C and C++</h1>
				
			
			<p>In C, data type of result of comparison operations is int.  For example, see the following program.<span id="more-124209"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
    int x = 10, y = 10;
    printf("%d \n", sizeof(x == y));
    printf("%d \n", sizeof(x < y));
    return 0;
}</pre>
<p>Output:
</p><pre>4
4</pre>
<p>Whereas in C++, type of results of comparison operations is bool. For example, see the following program.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<iostream>
using namespace std;

int main()
{
    int x = 10, y = 10;
    cout << sizeof(x == y) << endl;
    cout << sizeof(x < y);
    return 0;
}
</pre>
<p>Output:
</p><pre>1
1</pre>
<p>This article is contributed by <strong>Rajat</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		