<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Operations on struct variables in C</h1>
				
			
			<p>In C, the only operation that can be applied to <em>struct </em>variables is assignment. Any other operation (e.g. equality check) is not allowed on <em>struct </em>variables.  <span id="more-9613"></span><br />
For example, program 1 works without any error and program 2 fails in compilation. </p>
<p><strong>Program 1</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

struct Point {
  int x;
  int y;
};

int main()
{
  struct Point p1 = {10, 20};
  struct Point p2 = p1; // works: contents of p1 are copied to p1
  printf(" p2.x = %d, p2.y = %d", p2.x, p2.y);
  getchar();
  return 0;
}
</pre>
<p><br />
<strong>Program 2</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

struct Point {
  int x;
  int y;
};

int main()
{
  struct Point p1 = {10, 20};
  struct Point p2 = p1; // works: contents of p1 are copied to p1
  if (p1 == p2)  // compiler error: cannot do equality check for         
                  // whole structures
  {
    printf("p1 and p2 are same ");
  }
  getchar();
  return 0;
}
</pre>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		