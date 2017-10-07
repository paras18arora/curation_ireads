<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Memory Layout of C Programs</h1>
				
			
			<p>A typical memory representation of C program consists of following sections.<span id="more-14268"></span></p>
<p>1. Text segment<br />
2. Initialized data segment<br />
3. Uninitialized data segment<br />
4. Stack<br />
5. Heap</p>
<p><a><img class="aligncenter size-medium wp-image-14281" title="Memory-Layout" src="http://d1gjlxt8vb0knt.cloudfront.net//wp-content/uploads/Memory-Layout-300x255.gif" alt="" width="400" height="335" /></a><br />
A typical memory layout of a running process</p>
<p><strong>1. Text Segment:</strong><br />
A text segment , also known as a code segment or simply as text, is one of the sections of a program in an object file or in memory, which contains executable instructions.</p>
<p>As a memory region, a text segment may be placed below the heap or stack in order to prevent heaps and stack overflows from overwriting it.</p>
<p>Usually, the text segment is sharable so that only a single copy needs to be in memory for frequently executed programs, such as text editors, the C compiler, the shells, and so on. Also, the text segment is often read-only, to prevent a program from accidentally modifying its instructions.</p>
<p><strong>2. Initialized Data Segment:</strong><br />
Initialized data segment, usually called simply the Data Segment. A data segment is a portion of virtual address space of a program, which contains the global variables and static variables that are initialized by the programmer.</p>
<p>Note that, data segment is not read-only, since the values of the variables can be altered at run time.</p>
<p>This segment can be further classified into initialized read-only area and initialized read-write area.</p>
<p>For instance the global string defined by char s[] = "hello world" in C and a C statement like int debug=1 outside the main (i.e. global) would be stored in initialized read-write area. And a global C statement like const char* string = "hello world" makes the string literal "hello world" to be stored in initialized read-only area and the character pointer variable string in initialized read-write area.</p>
<p>Ex: static int i = 10 will be stored in data segment and global int i = 10 will also be stored in data segment</p>
<p><strong>3. Uninitialized Data Segment:</strong><br />
Uninitialized data segment, often called the "bss" segment, named after an ancient assembler operator that stood for "block started by symbol." Data in this segment is initialized by the kernel to arithmetic 0 before the program starts executing</p>
<p>uninitialized data starts at the end of the data segment and contains all global variables and static variables that are initialized to zero or do not have explicit initialization in source code.</p>
<p>For instance a variable declared static int i; would be contained in the BSS segment.<br />
For instance a global variable declared int j; would be contained in the BSS segment.</p>
<p><strong>4. Stack:</strong><br />
The stack area traditionally adjoined the heap area and grew the opposite direction; when the stack pointer met the heap pointer, free memory was exhausted. (With modern large address spaces and virtual memory techniques they may be placed almost anywhere, but they still typically grow opposite directions.)</p>
<p>The stack area contains the program stack, a LIFO structure, typically located in the higher parts of memory. On the standard PC x86 computer architecture it grows toward address zero; on some other architectures it grows the opposite direction. A "stack pointer" register tracks the top of the stack; it is adjusted each time a value is "pushed" onto the stack. The set of values pushed for one function call is termed a "stack frame"; A stack frame consists at minimum of a return address.</p>
<p>Stack, where automatic variables are stored, along with information that is saved each time a function is called. Each time a function is called, the address of where to return to and certain information about the caller's environment, such as some of the machine registers, are saved on the stack. The newly called function then allocates room on the stack for its automatic and temporary variables. This is how recursive functions in C can work. Each time a recursive function calls itself, a new stack frame is used, so one set of variables doesn't interfere with the variables from another instance of the function.</p>
<p><strong>5. Heap:</strong><br />
Heap is the segment where dynamic memory allocation usually takes place.</p>
<p>The heap area begins at the end of the BSS segment and grows to larger addresses from there.The Heap area is managed by malloc, realloc, and free, which may use the brk and sbrk system calls to adjust its size (note that the use of brk/sbrk and a single "heap area" is not required to fulfill the contract of malloc/realloc/free; they may also be implemented using mmap to reserve potentially non-contiguous regions of virtual memory into the process' virtual address space). The Heap area is shared by all shared libraries and dynamically loaded modules in a process.</p>
<p>Examples.</p>
<p>The size(1) command reports the sizes (in bytes) of the text, data, and bss segments. ( for more details please refer man page of size(1) )</p>
<p>1. Check the following simple C program</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

int main(void)
{
    return 0;
}
</pre>
<pre>
[narendra@CentOS]$ gcc memory-layout.c -o memory-layout
[narendra@CentOS]$ size memory-layout
text       data        bss        dec        hex    filename
960        248          8       1216        4c0    memory-layout</pre>
<p>2. Let us add one global variable in program, now check the size of bss (highlighted in red color).</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

int global; /* Uninitialized variable stored in bss*/

int main(void)
{
    return 0;
}
</pre>
<pre>
[narendra@CentOS]$ gcc memory-layout.c -o memory-layout
[narendra@CentOS]$ size memory-layout
text       data        bss        dec        hex    filename
 960        248         <strong><span style="color: red;">12</span></strong>       1220        4c4    memory-layout</pre>
<p>3. Let us add one static variable which is also stored in bss.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

int global; /* Uninitialized variable stored in bss*/

int main(void)
{
    static int i; /* Uninitialized static variable stored in bss */
    return 0;
}
</pre>
<pre>
[narendra@CentOS]$ gcc memory-layout.c -o memory-layout
[narendra@CentOS]$ size memory-layout
text       data        bss        dec        hex    filename
 960        248         <strong><span style="color: red;">16</span></strong>       1224        4c8    memory-layout</pre>
<p>4. Let us initialize the static variable which will then be stored in Data Segment (DS)</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

int global; /* Uninitialized variable stored in bss*/

int main(void)
{
    static int i = 100; /* Initialized static variable stored in DS*/
    return 0;
}
</pre>
<pre>
[narendra@CentOS]$ gcc memory-layout.c -o memory-layout
[narendra@CentOS]$ size memory-layout
text       data        bss        dec        hex    filename
960         <strong><span style="color: red;">252         12</span></strong>       1224        4c8    memory-layout</pre>
<p>5. Let us initialize the global variable which will then be stored in Data Segment (DS)</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

int global = 10; /* initialized global variable stored in DS*/

int main(void)
{
    static int i = 100; /* Initialized static variable stored in DS*/
    return 0;
}
</pre>
<pre>
[narendra@CentOS]$ gcc memory-layout.c -o memory-layout
[narendra@CentOS]$ size memory-layout
text       data        bss        dec        hex    filename
960         <strong><span style="color: red;">256          8</span></strong>       1224        4c8    memory-layout</pre>
<p>This article is compiled by <strong>Narendra Kangralkar</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>
<p><strong>Source:</strong><br />
<a>http://en.wikipedia.org/wiki/Data_segment</a><br />
<a>http://en.wikipedia.org/wiki/Code_segment</a><br />
<a>http://en.wikipedia.org/wiki/.bss</a><br />
<a>http://www.amazon.com/Advanced-Programming-UNIX-Environment-2nd/dp/0201433079</a></p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		