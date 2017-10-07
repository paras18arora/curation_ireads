<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">fopen() for an existing file in write mode</h1>
				
			
			<p>In C, <a target="_blank">fopen()</a> is used to open a file in different modes.  To open a file in write mode, "w" is specified. <span id="more-119363"></span> When mode "w" is specified, it creates an empty file for output operations. </p>
<p><strong>What if the file already exists?</strong><br />
If a file with the same name already exists, its contents are discarded and the file is treated as a new empty file.  For example, in the following program, if "test.txt" already exists, its contents are removed and "GeeksforGeeks" is written to it.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
#include <stdlib.h>

int main()
{
    FILE *fp = fopen("test.txt", "w");
    if (fp == NULL)
    {
        puts("Couldn't open file");
        exit(0);
    }
    else
    {
        fputs("GeeksforGeeks", fp);
        puts("Done");
        fclose(fp);
    }
    return 0;
}  </pre>
<p>The above behavior may lead to unexpected results.  If programmer's intention was to create a new file and a file with same name already exists, the existing file's contents are overwritten.</p>
<p>The latest C standard <a>C11 </a>provides a new mode "x" which is exclusive create-and-open mode. Mode "x" can be used with any "w" specifier, like "wx", "wbx".  <strong>When x is used with w, fopen() returns NULL if file already exists or could not open.</strong>  Following is modified C11 program that doesn't overwrite an existing file. </p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
#include <stdlib.h>

int main()
{
    FILE *fp = fopen("test.txt", "wx");
    if (fp == NULL)
    {
        puts("Couldn't open file or file already exists");
        exit(0);
    }
    else
    {
        fputs("GeeksforGeeks", fp);
        puts("Done");
        fclose(fp);
    }
    return 0;
}  </pre>
<p><strong>References:</strong><br />
<a target="_blank">Do not make assumptions about fopen() and file creation</a><br />
<a>http://en.wikipedia.org/wiki/C11_(C_standard_revision)</a><br />
<a target="_blank">http://www.cplusplus.com/reference/cstdio/freopen/</a></p>
<p>This article is compiled by <strong>Abhay Rathi</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		