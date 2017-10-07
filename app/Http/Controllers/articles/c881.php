<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">return statement vs exit() in main()</h1>
				
			
			<p>In C++, what is the difference between <em>exit(0)</em> and <em>return 0</em> ?<span id="more-10377"></span></p>
<p>When <em>exit(0)</em> is used to exit from program, destructors for locally scoped non-static objects are not called. But destructors are called if return 0 is used. </p>
<p><br />
<strong>Program 1 – – uses exit(0) to exit</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<iostream>
#include<stdio.h>
#include<stdlib.h>

using namespace std;

class Test {
public:
  Test() {
    printf("Inside Test's Constructor\n");
  }

  ~Test(){
    printf("Inside Test's Destructor");
    getchar();
  }
};

int main() {
  Test t1;

  // using exit(0) to exit from main
  exit(0);
}
</pre>
<p>Output:<br />
<em>Inside Test's Constructor</em></p>
<p><strong>Program 2 – uses return 0 to exit</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<iostream>
#include<stdio.h>
#include<stdlib.h>

using namespace std;

class Test {
public:
  Test() {
    printf("Inside Test's Constructor\n");
  }

  ~Test(){
    printf("Inside Test's Destructor");
  }
};

int main() {
  Test t1;

   // using return 0 to exit from main
  return 0;
}
</pre>
<p>Output:<br />
<em>Inside Test's Constructor<br />
Inside Test's Destructor<br />
</em></p>
<p>Calling destructors is sometimes important, for example, if destructor has code to release resources like closing files.  </p>
<p>Note that static objects will be cleaned up even if we call exit(). For example, see following program.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<iostream>
#include<stdio.h>
#include<stdlib.h>

using namespace std;

class Test {
public:
  Test() {
    printf("Inside Test's Constructor\n");
  }

  ~Test(){
    printf("Inside Test's Destructor");
    getchar();
  }
};

int main() {
  static Test t1;  // Note that t1 is static

  exit(0);
}
</pre>
<p>Output:<br />
<em>Inside Test's Constructor<br />
Inside Test's Destructor<br />
</em></p>
<p>Contributed by <strong>indiarox</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		