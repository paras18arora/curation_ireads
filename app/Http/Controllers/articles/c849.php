<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">CRASH() macro – interpretation</h1>
				
			
			<p>Given below a small piece of code from an open source project, <span id="more-10521"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#ifndef __cplusplus

typedef enum BoolenTag
{
   false,
   true
} bool;

#endif

#define CRASH() do { \
      ((void(*)())0)(); \
   } while(false)

int main()
{
   CRASH();
   return 0;
}
</pre>
<p>Can you interpret above code?</p>
<p>It is simple, a step by step approach is given below,</p>
<p>The statement <em>while(false)</em> is meant only for testing purpose. Consider the following operation,</p>
<pre>((void(*)())0)();</pre>
<p>It can be achieved as follows,</p>
<pre><span style="font-family: Consolas, Monaco, 'Courier New', Courier, monospace;font-size: 12px;line-height: 18px">0;                      /* literal zero */</span>
<span style="font-family: Consolas, Monaco, 'Courier New', Courier, monospace;font-size: 12px;line-height: 18px">(0); </span><span style="font-family: Consolas, Monaco, 'Courier New', Courier, monospace;font-size: 12px;line-height: 18px">( ()0 );                /* 0 being casted to some type */</span>
<span style="font-family: Consolas, Monaco, 'Courier New', Courier, monospace;font-size: 12px;line-height: 18px">( (*) 0 );              /* 0 casted some pointer type */</span>
<span style="font-family: Consolas, Monaco, 'Courier New', Courier, monospace;font-size: 12px;line-height: 18px">( (*)() 0 );            /* 0 casted as pointer to some function */</span>
<span style="font-family: Consolas, Monaco, 'Courier New', Courier, monospace;font-size: 12px;line-height: 18px">( void (*)(void) 0 );   /* Interpret 0 as address of function </span>
<span style="font-family: Consolas, Monaco, 'Courier New', Courier, monospace;font-size: 12px;line-height: 18px"> taking nothing and returning nothing */</span>
<span style="font-family: Consolas, Monaco, 'Courier New', Courier, monospace;font-size: 12px;line-height: 18px">( void (*)(void) 0 )(); /* Invoke the function */</span></pre>
<p>So the given code is invoking the function whose code is stored at location zero, in other words, trying to execute an instruction stored at location zero. On systems with memory protection (MMU) the OS will throw an exception (segmentation fault) and on systems without such protection (small embedded systems), it will execute and error will propagate further.</p>
<p>— <a target="_blank"><strong>Venki</strong></a>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		