<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Understanding “volatile” qualifier in C</h1>
				
			
			<p>The volatile keyword is intended to prevent the compiler from applying any optimizations on objects that can change in ways that cannot be determined by the compiler.<span id="more-15764"></span> </p>
<p>Objects declared as volatile are omitted from optimization because their values can be changed by code outside the scope of current code at any time. The system always reads the current value of a volatile object from the memory location rather than keeping its value in temporary register at the point it is requested, even if a previous instruction asked for a value from the same object. So the simple question is, how can value of a variable change in such a way that compiler cannot predict. Consider the following cases for answer to this question.<br />
<em><br />
1) Global variables modified by an interrupt service routine outside the scope: </em> For example, a global variable can represent a data port (usually global pointer referred as memory mapped IO) which will be updated dynamically. The code reading data port must be declared as volatile in order to fetch latest data available at the port. Failing to declare variable as volatile, the compiler will optimize the code in such a way that it will read the port only once and keeps using the same value in a temporary register to speed up the program (speed optimization). In general, an ISR used to update these data port when there is an interrupt due to availability of new data</p>
<p><em>2) Global variables within a multi-threaded application:</em> There are multiple ways for threads communication, viz, message passing, shared memory, mail boxes, etc. A global variable is weak form of shared memory. When two threads sharing information via global variable, they need to be qualified with volatile. Since threads run asynchronously, any update of global variable due to one thread should be fetched freshly by another consumer thread. Compiler can read the global variable and can place them in temporary variable of current thread context. To nullify the effect of compiler optimizations, such global variables to be qualified as volatile</p>
<p>If we do not use volatile qualifier, the following problems may arise<br />
1) Code may not work as expected when optimization is turned on.<br />
2) Code may not work as expected when interrupts are enabled and used.</p>
<p>Let us see an example to understand how compilers interpret volatile keyword. Consider below code, we are changing value of const object using pointer and we are compiling code without optimization option. Hence compiler won't do any optimization and will change value of const object.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* Compile code without optimization option */
#include <stdio.h>
int main(void)
{
    const int local = 10;
    int *ptr = (int*) &local;

    printf("Initial value of local : %d \n", local);

    *ptr = 100;

    printf("Modified value of local: %d \n", local);

    return 0;
}
</pre>
<p>When we compile code with "–save-temps" option of gcc it generates 3 output files</p>
<p>1) preprocessed code (having .i extention)<br />
2) assembly code (having .s extention) and<br />
3) object code (having .o option). </p>
<p>We compile code without optimization, that's why the size of assembly code will be larger (which is highlighted in red color below).</p>
<p>Output:</p>
<pre>
  [narendra@ubuntu]$ gcc volatile.c -o volatile –save-temps
  [narendra@ubuntu]$ ./volatile
  Initial value of local : 10
  Modified value of local: 100
  [narendra@ubuntu]$ ls -l volatile.s
  -rw-r–r– 1 narendra narendra <font color="red">731</font> 2016-11-19 16:19 volatile.s
  [narendra@ubuntu]$
</pre>
<p>Let us compile same code with optimization option (i.e. -O option). In thr below code, "local" is declared as const (and non-volatile), GCC compiler does optimization and ignores the instructions which try to change value of const object. Hence value of const object remains same.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* Compile code with optimization option */
#include <stdio.h>

int main(void)
{
    const int local = 10;
    int *ptr = (int*) &local;

    printf("Initial value of local : %d \n", local);

    *ptr = 100;

    printf("Modified value of local: %d \n", local);

    return 0;
}
</pre>
<p>For above code, compiler does optimization, that's why the size of assembly code will reduce.</p>
<p>Output:</p>
<pre>
  [narendra@ubuntu]$ gcc -O3 volatile.c -o volatile –save-temps
  [narendra@ubuntu]$ ./volatile
  Initial value of local : 10
  Modified value of local: 10
  [narendra@ubuntu]$ ls -l volatile.s
  -rw-r–r– 1 narendra narendra <font color="red">626</font> 2016-11-19 16:21 volatile.s
</pre>
<p>Let us declare const object as volatile and compile code with optimization option. Although we compile code with optimization option, value of const object will change, because variable is declared as volatile that means don't do any optimization.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* Compile code with optimization option */
#include <stdio.h>

int main(void)
{
    const volatile int local = 10;
    int *ptr = (int*) &local;

    printf("Initial value of local : %d \n", local);

    *ptr = 100;

    printf("Modified value of local: %d \n", local);

    return 0;
}
</pre>
<p>Output:</p>
<pre>
  [narendra@ubuntu]$ gcc -O3 volatile.c -o volatile –save-temp
  [narendra@ubuntu]$ ./volatile
  Initial value of local : 10
  Modified value of local: 100
  [narendra@ubuntu]$ ls -l volatile.s
  -rw-r–r– 1 narendra narendra <font color="red">711</font> 2016-11-19 16:22 volatile.s
  [narendra@ubuntu]$
</pre>
<p>The above example may not be a good practical example, the purpose was to explain how compilers interpret volatile keyword. As a practical example, think of touch sensor on mobile phones. The driver abstracting touch sensor will read the location of touch and send it to higher level applications. The driver itself should not modify (const-ness) the read location, and make sure it reads the touch input every time fresh (volatile-ness). Such driver must read the touch sensor input in const volatile manner.</p>
<p>Refer following links for more details on volatile keyword:<br />
<a>Volatile: A programmer's best friend</a><br />
<a>Do not use volatile as a synchronization primitive</a></p>
<p>This article is compiled by "Narendra Kangralkar" and reviewed by GeeksforGeeks team. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		