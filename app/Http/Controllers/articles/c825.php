<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">What is the difference between printf, sprintf and fprintf?</h1>
				
			
			<p><u><strong>printf:</strong></u><br />
printf function is used to print character stream of data on stdout console.<span id="more-15735"></span></p>
<p>Syntax:
</p><pre>
 int printf(const char* str, ...); </pre>
<p>Example: </p>
<pre class="brush: cpp; title: ; notranslate" title="">
// simple print on stdout 
#include<stdio.h>
int main()
{
   printf("hello geeksquiz");
   return 0;
}</pre>
<p>Output:
</p><pre> hello geeksquiz</pre>
<p><u><strong>sprintf:</strong></u><br />
Syntax:
</p><pre>
int sprintf(char *str, const char *string,...); </pre>
<p>String print function it is stead of printing on console store it on char buffer which are specified in sprintf</p>
<p>Example:
</p><pre class="brush: cpp; title: ; notranslate" title="">
// Example program to demonstrate sprintf()
#include<stdio.h>
int main()
{
    char buffer[50];
    int a = 10, b = 20, c;
    c = a + b;
    sprintf(buffer, "Sum of %d and %d is %d", a, b, c);

    // The string "sum of 10 and 20 is 30" is stored 
    // into buffer instead of printing on stdout
    printf("%s", buffer);

    return 0;
}
</pre>
<p>Output:
</p><pre>Sum of 10 and 20 is 30</pre>
<p><u><strong>fprintf:</strong></u><br />
fprintf is used to print the sting content in file but not on stdout console.</p>
<pre>
int fprintf(FILE *fptr, const char *str, ...);</pre>
<p>Example:
</p><pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
    int i, n=2;
    char str[50];

    //open file sample.txt in write mode
    FILE *fptr = fopen("sample.txt", "w");
    if (fptr == NULL)
    {
        printf("Could not open file");
        return 0;
    }

    for (i=0; i<n; i++)
    {
        puts("Enter a name");
        gets(str);
        fprintf(fptr,"%d.%s\n", i, str);
    }
    fclose(fptr);

    return 0;
}
</pre>
<pre>
Input: GeeksforGeeks
       GeeksQuiz
Output:  sample.txt file now having output as 
0. GeeksforGeeks
1. GeeksQuiz</pre>
<p>Thank you for reading, i will soon update with scanf, fscanf, sscanf keep tuned.</p>
<p>This article is contributed by <strong>Vankayala Karunakar</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			

<!-- GQBottom -->


		