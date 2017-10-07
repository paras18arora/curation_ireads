<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Enumeration (or enum) in C</h1>
				
			
			<p>Enumeration (or enum) is a user defined data type in C.   It is mainly used to assign names to integral constants, the names make a program easy to read and maintain.<span id="more-15303"></span></p>
<p>The keyword ‘enum' is used to declare new enumeration types in C and C++.   Following is an example of enum declaration.</p>
<pre>
enum State {Working = 1, Failed = 0}; </pre>
<p>Following are some interesting facts about initialization of enum.</p>
<p><br />
<strong>1.</strong> Two enum names can have same value. For example, in the following C program both ‘Failed' and ‘Freezed' have same value 0.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
enum State {Working = 1, Failed = 0, Freezed = 0};

int main()
{
   printf("%d, %d, %d", Working, Failed, Freezed);
   return 0;
}</pre>
<p>Output:
</p><pre>1, 0, 0</pre>
<p><br />
<strong>2.</strong> If we do not explicitly assign values to enum names, the compiler by default assigns values starting from 0.  For example, in the following C program, sunday gets value 0, monday gets 1, and so on.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
enum day {sunday, monday, tuesday, wednesday, thursday, friday, saturday};

int main()
{
    enum day d = thursday;
    printf("The day number stored in d is %d", d);
    return 0;
}
</pre>
<p>Output:
</p><pre>The day number stored in d is 4</pre>
<p><br />
<strong>3.</strong> We can assign values to some name in any order. All unassigned names get value as value of previous name plus one.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
enum day {sunday = 1, monday, tuesday = 5,
          wednesday, thursday = 10, friday, saturday};

int main()
{
    printf("%d %d %d %d %d %d %d", sunday, monday, tuesday,
            wednesday, thursday, friday, saturday);
    return 0;
}
</pre>
<p>Output:
</p><pre>1 2 5 6 10 11 12</pre>
<p><br />
<strong>4.</strong> The value assigned to enum names must be some integeral constant, i.e., the value must be in range from minimum possible integer value to maximum possible integer value.</p>
<p><br />
<strong>5.</strong> All enum constants must be unique in their scope.  For example, the following program fails in compilation.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
enum state  {working, failed};
enum result {failed, passed};

int main()  { return 0; }
</pre>
<p>Output:
</p><pre>
Compile Error: 'failed' has a previous declaration as 'state failed'</pre>
<p><br />
<strong>Exercise:</strong><br />
Predict the output of following C programs</p>
<p>Program 1:</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
enum day {sunday = 1, tuesday, wednesday, thursday, friday, saturday};

int main()
{
    enum day d = thursday;
    printf("The day number stored in d is %d", d);
    return 0;
}</pre>
<p><br />
Program 2:</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
enum State {WORKING = 0, FAILED, FREEZED};
enum State currState = 2;

enum State FindState() {
    return currState;
}

int main() {
   (FindState() == WORKING)? printf("WORKING"): printf("NOT WORKING");
   return 0;
}
</pre>
<p><br />
<strong>Enum vs Macro</strong><br />
We can also use macros to define names constants. For example we can define ‘Working' and ‘Failed' using following macro.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#define Working 0
#define Failed 1
#define Freezed 2
</pre>
<p>There are multiple advantages of using enum over macro when many related named constants have integral values.<br />
a) Enums follow scope rules.<br />
b) Enum variables are automatically assigned values.  Following is simpler</p>
<pre class="brush: cpp; title: ; notranslate" title="">
 enum state  {Working, Failed, Freezed};
</pre>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			

<!-- GQBottom -->


		