<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">How to write long strings in Multi-lines C/C++?</h1>
				
			
			<p>Image a situation where we want to use or print a long long string in C or C++, how to do do this?</p>
<p>In C/C++, we can break a string at any point in the middle using two double quotes in the middle.  Below is a simple example to demonstrate the same. <span id="more-16597"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
   // We can put two double quotes anywhere in a string
   char *str1  = "geeks""quiz"; 

   // We can put space line break between two double quotes
   char *str2  = "Qeeks"     "Quiz";
   char *str3  = "Qeeks"     
                 "Quiz";

   puts(str1);
   puts(str2);
   puts(str3);

   puts("Geeks"        // Breaking string in multiple lines
        "forGeeks");
   return 0;
}
</pre>
<p>Output:<br />
<em>geeksquiz<br />
GeeksQuiz<br />
GeeksQuiz<br />
GeeksforGeeks</em></p>
<p>Below are few examples with long long strings broken using two double quotes for better readability.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
   char *str = "These are reserved words in C language are int, float, "
               "if, else, for, while etc. An Identifier is a sequence of"
               "letters and digits, but must start with a letter. "
               "Underscore ( _ ) is treated as a letter. Identifiers are "
               "case sensitive. Identifiers are used to name variables,"
               "functions etc.";
   puts(str);
   return 0; 
} 
</pre>
<p>Output: <em>These are reserved words in C language are int, float, if, else, for, while etc. An Identifier is a sequence ofletters and digits, but must start with a letter. Underscore ( _ ) is treated as a letter. Identifiers are case sensitive. Identifiers are used to name variables,functions etc.</em></p>
<p>Similarly, we can write long strings in printf and or cout.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
   char *str = "An Identifier is a sequence of"
               "letters and digits, but must start with a letter. "
               "Underscore ( _ ) is treated as a letter. Identifiers are "
               "case sensitive. Identifiers are used to name variables,"
               "functions etc.";
   printf ("These are reserved words in C language are int, float, "
            "if, else, for, while etc. %s ", str);
   return 0; 
} </pre>
<p>Output: <em>These are reserved words in C language are int, float, if, else, for, while etc. An Identifier is a sequence ofletters and digits, but must start with a letter. Underscore ( _ ) is treated as a letter. Identifiers are case sensitive. Identifiers are used to name variables,functions etc.</em></p>
<p>This article is contributed by <strong>Ayush Jain</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			

<!-- GQBottom -->


		