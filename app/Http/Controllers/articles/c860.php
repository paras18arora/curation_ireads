<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Are array members deeply copied?</h1>
				
			
			<p>In C/C++, we can assign a struct (or class in C++ only) variable to another variable of same type. <span id="more-13899"></span> When we assign a struct variable to another, all members of the variable are copied to the other struct variable.  But what happens when the structure contains pointer to dynamically allocated memory and what if it contains an array?</p>
<p>In the following C++ program, struct variable st1 contains pointer to dynamically allocated memory. When we assign st1 to st2, str pointer of st2 also start pointing to same memory location.  This kind of copying is called <a>Shallow Copy</a>.</p>
<pre class="brush: cpp; title: ; notranslate" title=""># include <iostream>
# include <string.h>

using namespace std;

struct test
{
  char *str;
};

int main()
{
  struct test st1, st2;

  st1.str = new char[20];
  strcpy(st1.str, "GeeksforGeeks");

  st2 = st1;

  st1.str[0] = 'X';
  st1.str[1] = 'Y';

  /* Since copy was shallow, both strings are same */
  cout << "st1's str = " << st1.str << endl;
  cout << "st2's str = " << st2.str << endl;

  return 0;
}</pre>
<p>Output:<br />
st1's str = XYeksforGeeks<br />
st2's str = XYeksforGeeks</p>
<p>Now, what about arrays?  <em>The point to note is that the array members are not shallow copied, compiler automatically performs <a>Deep Copy</a> for array members.</em>.  In the following program, struct test contains array member str[].  When we assign st1 to st2, st2 has a new copy of the array. So st2 is not changed when we change str[] of st1.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
# include <iostream>
# include <string.h>

using namespace std;

struct test
{
  char str[20];
};

int main()
{
  struct test st1, st2;

  strcpy(st1.str, "GeeksforGeeks");

  st2 = st1;

  st1.str[0] = 'X';
  st1.str[1] = 'Y';

  /* Since copy was Deep, both arrays are different */
  cout << "st1's str = " << st1.str << endl;
  cout << "st2's str = " << st2.str << endl;

  return 0;
}
</pre>
<p>Output:<br />
st1's str = XYeksforGeeks<br />
st2's str = GeeksforGeeks</p>
<p>Therefore, for C++ classes, we don't need to write our own copy constructor and assignment operator for array members as the default behavior is Deep copy for arrays.</p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		