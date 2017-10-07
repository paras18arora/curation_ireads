<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">What’s difference between header files “stdio.h” and “stdlib.h” ?</h1>
				
			
			<p style="text-align: justify;"><span style="font-weight: 400;">These are two important header files used in C programming. While "<stdio.h>" is header file for <strong>St</strong>andar<strong>d</strong> <strong>I</strong>nput <strong>O</strong>utput, "<stdlib.h>" is header file for <strong>St</strong>andar<strong>d</strong> <strong>Lib</strong>rary. One easy way to differentiate these two header files is that "<stdio.h>" contains declaration of <em>printf()</em> and <em>scanf()</em> while "<stdlib.h>" contains declaration of <em>malloc() </em>and <em>free()</em>. In that sense, the main difference in these two header files can considered that, while "<stdio.h>" contains header information for ‘File related Input/Output' functions, "<stdlib.h>" contains header information for ‘Memory Allocation/Freeing' functions. </span></p>
<p style="text-align: justify;"><span style="font-weight: 400;">Wait a minute, you said "<stdio.h>" is for file related IO but <em>printf()</em> and <em>scanf()</em> don't deal with files… or are they? As  a basic principle, in C (due to its association with UNIX history), keyboard and display are also treated as ‘files'! In fact keyboard input is the default <em>stdin</em> file stream while display output is the default <em>stdout</em> file stream. Also, please note that, though "<stdlib.h>" contains declaration of other types of functions as well that aren't related to memory such as <em>atoi()</em>, <em>exit()</em>, <em>rand()</em> etc. yet for our purpose and simplicity, we can remember <em>malloc()</em> and <em>free()</em> for "<stdlib.h>".</span></p>
<p style="text-align: justify;"><span style="font-weight: 400;">It should be noted that a header file can contain not only function declaration but definition of constants and variables as well. Even macros and definition of new data types can also be added in a header file. </span></p>
<p style="text-align: justify;"><span style="font-weight: 400;">Please do Like/Tweet/G+1 if you find the above useful. Also, please do leave us comment for further clarification or info. We would love to help and learn <img src="http://d30wr2otswzun8.cloudfront.net/wp-includes/images/smilies/simple-smile.png" alt=":)" class="wp-smiley" style="height: 1em; max-height: 1em;" /></span></p>

			

<!-- GQBottom -->


		