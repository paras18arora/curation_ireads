<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Variable length arguments for Macros</h1>
				
			
			<p>Like functions, we can also pass variable length arguments to macros. For this we will use the following preprocessor identifiers.<span id="more-22027"></span></p>
<p>To support variable length arguments in macro, we must include ellipses (…) in macro definition. There is also "__VA_ARGS__" preprocessing identifier which takes care of variable length argument substitutions which are provided to macro. Concatenation operator ## (aka paste operator) is used to concatenate variable arguments. </p>
<p>Let us see with example. Below macro takes variable length argument like "printf()" function. This macro is for error logging.  The macro prints filename followed by line number, and finally it prints info/error message. First arguments "prio" determines the priority of message, i.e. whether it is information message or error, "stream" may be "standard output" or "standard error".   It displays INFO messages on stdout and ERROR messages on stderr stream. </p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

#define INFO 	1
#define ERR	2
#define STD_OUT	stdout
#define STD_ERR	stderr

#define LOG_MESSAGE(prio, stream, msg, ...) do {\
						char *str;\
						if (prio == INFO)\
							str = "INFO";\
						else if (prio == ERR)\
							str = "ERR";\
						fprintf(stream, "[%s] : %s : %d : "msg" \n", \
                                str, __FILE__, __LINE__, ##__VA_ARGS__);\
					} while (0)

int main(void)
{
	char *s = "Hello";

        /* display normal message */
	LOG_MESSAGE(ERR, STD_ERR, "Failed to open file");

	/* provide string as argument */
	LOG_MESSAGE(INFO, STD_OUT, "%s Geeks for Geeks", s);

	/* provide integer as arguments */
	LOG_MESSAGE(INFO, STD_OUT, "%d + %d = %d", 10, 20, (10 + 20));

	return 0;
}
</pre>
<p>Compile and run the above program, it produces below result.</p>
<pre>
  [narendra@/media/partition/GFG]$ ./variable_length 
  [ERR] : variable_length.c : 26 : Failed to open file 
  [INFO] : variable_length.c : 27 : Hello Geeks for Geeks 
  [INFO] : variable_length.c : 28 : 10 + 20 = 30 
  [narendra@/media/partition/GFG]$
</pre>
<p>This article is compiled by <strong>Narendra Kangralkar</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		