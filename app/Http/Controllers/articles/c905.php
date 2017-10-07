<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Structure Member Alignment, Padding and Data Packing</h1>
				
			
			<p>What do we mean by data alignment, structure packing and padding?</p>
<p>Predict the output of following program.<span id="more-9705"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

// Alignment requirements
// (typical 32 bit machine)

// char         1 byte
// short int    2 bytes
// int          4 bytes
// double       8 bytes

// structure A
typedef struct structa_tag
{
   char        c;
   short int   s;
} structa_t;

// structure B
typedef struct structb_tag
{
   short int   s;
   char        c;
   int         i;
} structb_t;

// structure C
typedef struct structc_tag
{
   char        c;
   double      d;
   int         s;
} structc_t;

// structure D
typedef struct structd_tag
{
   double      d;
   int         s;
   char        c;
} structd_t;

int main()
{
   printf("sizeof(structa_t) = %d\n", sizeof(structa_t));
   printf("sizeof(structb_t) = %d\n", sizeof(structb_t));
   printf("sizeof(structc_t) = %d\n", sizeof(structc_t));
   printf("sizeof(structd_t) = %d\n", sizeof(structd_t));

   return 0;
}
</pre>
<p>Before moving further, write down your answer on a paper, and read on. If you urge to see explanation, you may miss to understand any lacuna in your analogy. Also read the <a target="_blank">post</a> by Kartik.</p>
<p><strong>Data Alignment:</strong></p>
<p>Every data type in C/C++ will have alignment requirement (infact it is mandated by processor architecture, not by language). A processor will have processing word length as that of data bus size. On a 32 bit machine, the processing word size will be 4 bytes.</p>
<p><a><img class="aligncenter size-full wp-image-9731" alt="" src="http://geeksforgeeks.org/wp-content/uploads/MemoryAlignment1.gif" width="590" height="287" /></a></p>
<p><strong> </strong>Historically memory is byte addressable and arranged sequentially. If the memory is arranged as single bank of one byte width, the processor needs to issue 4 memory read cycles to fetch an integer. It is more economical to read all 4 bytes of integer in one memory cycle. To take such advantage, the memory will be arranged as group of 4 banks as shown in the above figure.</p>
<p>The memory addressing still be sequential. If bank 0 occupies an address X, bank 1, bank 2 and bank 3 will be at (X + 1), (X + 2) and (X + 3) addresses. If an integer of 4 bytes is allocated on X address (X is multiple of 4), the processor needs only one memory cycle to read entire integer.</p>
<p>Where as, if the integer is allocated at an address other than multiple of 4, it spans across two rows of the banks as shown in the below figure. Such an integer requires two memory read cycle to fetch the data.</p>
<p><a><img class="aligncenter size-full wp-image-9732" alt="" src="http://geeksforgeeks.org/wp-content/uploads/MemoryAlignment2.gif" width="420" height="123" /></a></p>
<p>A variable's <em><strong>data alignment</strong></em> deals with the way the data stored in these banks. For example, the natural alignment of <em><strong>int</strong></em> on 32-bit machine is 4 bytes. When a data type is naturally aligned, the CPU fetches it in minimum read cycles.</p>
<p>Similarly, the natural alignment of <strong><em>short int</em></strong> is 2 bytes. It means, a <em><strong>short int</strong></em> can be stored in bank 0 – bank 1 pair or bank 2 – bank 3 pair. A <strong><em>double</em></strong> requires 8 bytes, and occupies two rows in the memory banks. Any misalignment of <strong><em>double</em></strong> will force more than two read cycles to fetch <strong><em>double</em></strong> data.</p>
<p>Note that a <strong>double</strong> variable will be allocated on 8 byte boundary on 32 bit machine and requires two memory read cycles. On a 64 bit machine, based on number of banks, <strong>double</strong> variable will be allocated on 8 byte boundary and requires only one memory read cycle.</p>
<p><strong>Structure Padding:</strong></p>
<p><strong> </strong>In C/C++ a structures are used as data pack. It doesn't provide any data encapsulation or data hiding features (C++ case is an exception due to its semantic similarity with classes).</p>
<p>Because of the alignment requirements of various data types, every member of structure should be naturally aligned. The members of structure allocated sequentially increasing order. Let us analyze each struct declared in the above program.</p>
<p><strong>Output of Above Program:</strong></p>
<p><strong><span style="color: #3366ff">For the sake of convenience, assume every structure type variable is allocated on 4 byte boundary (say 0x0000), i.e. the base address of structure is multiple of 4 (need not necessary always, see explanation of structc_t).</span></strong></p>
<p><strong><span style="color: #3366ff">structure A</span></strong></p>
<p><span style="color: #3366ff">The <em>structa_t</em> first element is <em>char</em> which is one byte aligned, followed by <em>short int</em>. short int is 2 byte aligned. If the the short int element is immediately allocated after the char element, it will start at an odd address boundary. The compiler will insert a padding byte after the char to ensure short int will have an address multiple of 2 (i.e. 2 byte aligned). The total size of structa_t will be sizeof(char) + 1 (padding) + sizeof(short), 1 + 1 + 2 = 4 bytes.</span></p>
<p><strong><span style="color: #3366ff">structure B</span></strong></p>
<p><span style="color: #3366ff">The first member of <em>structb_t</em> is short int followed by char. Since char can be on any byte boundary no padding required in between short int and char, on total they occupy 3 bytes. The next member is int. If the int is allocated immediately, it will start at an odd byte boundary. We need 1 byte padding after the char member to make the address of next int member is 4 byte aligned. On total, the <em>structb_t</em> requires 2 + 1 + 1 (padding) + 4 = 8 bytes.</span></p>
<p><span style="color: #808080"><strong><span style="color: #3366ff">structure C – Every structure will also have alignment requirements</span></strong></span></p>
<p><span style="color: #3366ff">Applying same analysis, <em>structc_t</em> needs sizeof(char) + 7 byte padding + sizeof(double) + sizeof(int) = 1 + 7 + 8 + 4 = 20 bytes. However, the sizeof(structc_t) will be 24 bytes. It is because, along with structure members, structure type variables will also have natural alignment. Let us understand it by an example. </span><span style="color: #3366ff">Say, we declared an array of structc_t as shown below</span></p>
<pre><span style="color: #3366ff">structc_t structc_array[3];</span></pre>
<p><span style="color: #3366ff">Assume, the base address of <em>structc_array</em> is 0x0000 for easy calculations. If the structc_t occupies 20 (0x14) bytes as we calculated, the second structc_t array element (indexed at 1) will be at 0x0000 + 0x0014 = 0x0014. It is the start address of index 1 element of array. The double member of this structc_t will be allocated on 0x0014 + 0x1 + 0x7 = 0x001C (decimal 28) which is not multiple of 8 and conflicting with the alignment requirements of double. As we mentioned on the top, the alignment requirement of double is 8 bytes.</span></p>
<p><span style="color: #3366ff">Inorder to avoid such misalignment, compiler will introduce alignment requirement to every structure. It will be as that of the largest member of the structure. In our case alignment of structa_t is 2, structb_t is 4 and structc_t is 8. If we need nested structures, the size of largest inner structure will be the alignment of immediate larger structure.</span></p>
<p><span style="color: #3366ff">In structc_t of the above program, there will be padding of 4 bytes after int member to make the structure size multiple of its alignment. Thus the sizeof (structc_t) is 24 bytes. It guarantees correct alignment even in arrays. You can cross check.</span></p>
<p><strong><span style="color: #3366ff">structure D – How to Reduce Padding?</span></strong></p>
<p><span style="color: #3366ff">By now, it may be clear that padding is unavoidable. There is a way to minimize padding. The programmer should declare the structure members in their increasing/decreasing order of size. An example is structd_t given in our code, whose size is 16 bytes in lieu of 24 bytes of structc_t.</span></p>
<p><strong>What is structure packing?</strong></p>
<p>Some times it is mandatory to avoid padded bytes among the members of structure. For example, reading contents of ELF file header or BMP or JPEG file header. We need to define a structure similar to that of the header layout and map it. However, care should be exercised in accessing such members. Typically reading byte by byte is an option to avoid misaligned exceptions. There will be hit on performance.</p>
<p>Most of the compilers provide non standard extensions to switch off the default padding like pragmas or command line switches. Consult the documentation of respective compiler for more details.</p>
<p><strong>Pointer Mishaps:</strong></p>
<p>There is possibility of potential error while dealing with pointer arithmetic. For example, dereferencing a generic pointer (void *) as shown below can cause misaligned exception,</p>
<pre>// Deferencing a generic pointer (not safe)
// There is no guarantee that pGeneric is integer aligned
*(int *)pGeneric;</pre>
<p>It is possible above type of code in programming. If the pointer <em>pGeneric</em> is not aligned as per the requirements of casted data type, there is possibility to get misaligned exception.</p>
<p>Infact few processors will not have the last two bits of address decoding, and there is no way to access <em>misaligned</em> address. The processor generates misaligned exception, if the programmer tries to access such address.</p>
<p><strong>A note on malloc() returned pointer</strong></p>
<p>The pointer returned by malloc() is <em>void *</em>. It can be converted to any data type as per the need of programmer. The implementer of malloc() should return a pointer that is aligned to maximum size of primitive data types (those defined by compiler). It is usually aligned to 8 byte boundary on 32 bit machines.</p>
<p><strong>Object File Alignment, Section Alignment, Page Alignment</strong></p>
<p>These are specific to operating system implementer, compiler writers and are beyond the scope of this article. Infact, I don't have much information.</p>
<p><strong>General Questions:</strong></p>
<p><span style="color: #0000ff">1. Is alignment applied for stack?</span></p>
<p>Yes. The stack is also memory. The system programmer should load the stack pointer with a memory address that is properly aligned. Generally, the processor won't check stack alignment, it is the programmer's responsibility to ensure proper alignment of stack memory. Any misalignment will cause run time surprises.</p>
<p>For example, if the processor word length is 32 bit, stack pointer also should be aligned to be multiple of 4 bytes.</p>
<p><span style="color: #0000ff">2. If <em>char</em> data is placed in a bank other bank 0, it will be placed on wrong data lines during memory read. How the processor handles <em>char</em> type?</span></p>
<p>Usually, the processor will recognize the data type based on instruction (e.g. LDRB on ARM processor). Depending on the bank it is stored, the processor shifts the byte onto least significant data lines.</p>
<p><span style="color: #0000ff">3. When arguments passed on stack, are they subjected to alignment?</span></p>
<p>Yes. The compiler helps programmer in making proper alignment. For example, if a 16-bit value is pushed onto a 32-bit wide stack, the value is automatically padded with zeros out to 32 bits. Consider the following program.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
void argument_alignment_check( char c1, char c2 )
{
   // Considering downward stack
   // (on upward stack the output will be negative)
   printf("Displacement %d\n", (int)&c2 - (int)&c1);
}
</pre>
<p>The output will be 4 on a 32 bit machine. It is because each character occupies 4 bytes due to alignment requirements.</p>
<p><span style="color: #0000ff">4. What will happen if we try to access a misaligned data?</span></p>
<p>It depends on processor architecture. If the access is misaligned, the processor automatically issues sufficient memory read cycles and packs the data properly onto the data bus. The penalty is on performance. Where as few processors will not have last two address lines, which means there is no-way to access odd byte boundary. Every data access must be aligned (4 bytes) properly. A misaligned access is critical exception on such processors. If the exception is ignored, read data will be incorrect and hence the results.</p>
<p><span style="color: #0000ff">5. Is there any way to query alignment requirements of a data type.</span></p>
<p>Yes. Compilers provide non standard extensions for such needs. For example, __alignof() in Visual Studio helps in getting the alignment requirements of data type. Read MSDN for details.</p>
<p><span style="color: #0000ff">6. When memory reading is efficient in reading 4 bytes at a time on 32 bit machine, why should a <strong>double</strong> type be aligned on 8 byte boundary?</span></p>
<p>It is important to note that most of the processors will have math co-processor, called Floating Point Unit (FPU). Any floating point operation in the code will be translated into FPU instructions. The main processor is nothing to do with floating point execution. All this will be done behind the scenes.</p>
<p>As per standard, double type will occupy 8 bytes. And, every floating point operation performed in FPU will be of 64 bit length. Even float types will be promoted to 64 bit prior to execution.</p>
<p>The 64 bit length of FPU registers forces double type to be allocated on 8 byte boundary. I am assuming (I don't have concrete information) in case of FPU operations, data fetch might be different, I mean the data bus, since it goes to FPU. Hence, the address decoding will be different for double types (which is expected to be on 8 byte boundary). It means, <em>the address decoding circuits of floating point unit will not have last 3 pins</em>.</p>
<p><strong>Answers:</strong></p>
<pre>sizeof(structa_t) = 4
sizeof(structb_t) = 8
sizeof(structc_t) = 24
sizeof(structd_t) = 16</pre>
<p><strong>Update: 1-May-2013</strong></p>
<p>It is observed that on latest processors we are getting size of struct_c as 16 bytes. I yet to read relevant documentation. I will update once I got proper information (written to few experts in hardware).</p>
<p>On older processors (AMD Athlon X2) using same set of tools (GCC 4.7) I got struct_c size as 24 bytes. The size depends on how memory banking organized at the hardware level.</p>
<p>– – – by <strong><a target="_blank">Venki</a></strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		