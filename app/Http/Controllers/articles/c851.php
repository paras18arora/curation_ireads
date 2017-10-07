<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Branch prediction macros in GCC</h1>
				
			
			<p>One of the most used optimization techniques in the Linux kernel is " __builtin_expect". When working with conditional code (if-else statements), we often know which branch is true and which is not. <span id="more-26836"></span>If compiler knows this information in advance, it can generate most optimized code.</p>
<p>Let us see macro definition of "likely()" and "unlikely()" macros from linux kernel code "<a>http://lxr.linux.no/linux+v3.6.5/include/linux/compiler.h</a>" [line no 146 and 147].</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#define likely(x)      __builtin_expect(!!(x), 1)
#define unlikely(x)    __builtin_expect(!!(x), 0)
</pre>
<p>In the following example, we are marking branch as likely true:</p>
<pre class="brush: cpp; title: ; notranslate" title="">
const char *home_dir ;

home_dir = getenv("HOME");
if (likely(home_dir)) 
	printf("home directory: %s\n", home_dir);
else
	perror("getenv");
</pre>
<p>For above example, we have marked "if" condition as "likely()" true, so compiler will put true code immediately after branch, and false code within the branch instruction. In this way compiler can achieve optimization. But don't use "likely()" and "unlikely()" macros blindly. If prediction is correct, it means there is zero cycle of jump instruction, but if prediction is wrong, then it will take several cycles, because processor needs to flush it's pipeline which is worst than no prediction.</p>
<p>Accessing memory is the slowest CPU operation as compared to other CPU operations. To avoid this limitation, CPU uses "CPU caches" e.g L1-cache, L2-cache etc. The idea behind cache is, copy some part of memory into CPU itself. We can access cache memory much faster than any other memory. But the problem is, limited size of "cache memory", we can't copy entire memory into cache. So, the CPU has to guess which memory is going to be used in the near future and load that memory into the CPU cache and above macros are hint to load memory into the CPU cache.</p>
<p>This article is compiled by <strong>Narendra Kangralkar</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		