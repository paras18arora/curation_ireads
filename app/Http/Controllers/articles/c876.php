<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">A nested loop puzzle</h1>
				
			
			<p>Which of the following two code segments is faster?  Assume that compiler makes no optimizations. <span id="more-7754"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* FIRST */
for(i=0;i<10;i++)
  for(j=0;j<100;j++)
    //do somthing
</pre>
<pre class="brush: cpp; title: ; notranslate" title="">
/* SECOND */
for(i=0;i<100;i++)
  for(j=0;j<10;j++)
    //do something
</pre>
<p>Both code segments provide same functionality, and the code inside the two for loops would be executed same number of times in both code segments.<br />
If we take a closer look then we can see that the SECOND does more operations than the FIRST. It executes all three parts (assignment, comparison and increment) of the for loop more times than the corresponding parts of FIRST</p>
<p>a) The SECOND executes assignment operations ( j = 0 or i = 0) 101 times while FIRST executes only 11 times.<br />
b) The SECOND does 101 + 1100  comparisons (i < 100 or j < 10) while the FIRST does 11 + 1010 comparisons (i < 10 or  j < 100).
c) The SECOND executes 1100 increment operations (i++ or j++) while the FIRST executes 1010 increment operation.

Below C++ code counts the number of increment operations executed in FIRST and SECOND, and prints the counts.


</p><pre class="brush: cpp; title: ; notranslate" title="">
/* program to count number of increment operations in FIRST and SECOND */
#include<iostream>

using namespace std;

int main()
{
  int c1 = 0, c2 = 0;
   
  /* FIRST */
  for(int i=0;i<10;i++,c1++)
    for(int j=0;j<100;j++, c1++);
	  //do something

   
  /* SECOND */
  for(int i=0; i<100; i++, c2++)
	  for(int j=0; j<10; j++, c2++);
		//do something

  cout << " Count in FIRST = " <<c1 << endl;
  cout << " Count in SECOND  = " <<c2 << endl;

  getchar();
  return 0;
}
</pre>
<p>Below C++ code counts the number of comparison operations executed by FIRST and SECOND</p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* Program to count the number of comparison operations executed by FIRST and SECOND */
#include<iostream>

using namespace std;

int main()
{
   int c1 = 0, c2 = 0;
    
   /* FIRST */
   for(int i=0; ++c1&&i<10; i++)
      for(int j=0; ++c1&&j<100;j++);
	 //do something

   /* SECOND */
   for(int i=0; ++c2&&i<100; i++)
      for(int j=0; ++c2&&j<10; j++);
  	//do something

   cout << " Count fot FIRST  " <<c1 << endl;
   cout << " Count fot SECOND  " <<c2 << endl;
   getchar();
   return 0;
}
</pre>
<p>Thanks to <a>Dheeraj </a>for suggesting the solution.</p>
<p>Please write comments if you find any of the answers/codes incorrect, or you want to share more information about the topics discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		