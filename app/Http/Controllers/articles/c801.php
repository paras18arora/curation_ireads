<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
			<pre class="brush: cpp; title: ; notranslate" title="">
</pre>
<div id="mtq_quiz_area-1" class="mtq_quiz_area mtq_color_blue"> 
  <!--Quiz generated using mTouch Quiz Version 3.0.4 by G. Michael Guy (http://gmichaelguy.com/quizplugin/)-->
        
      
  <!-- Shortcode entered mtouchquiz id=65 --> 
  <!-- Shortcode interpreted mtouchquiz id=65 alerts=off singlepage=on hints=on startscreen=off finalscreen=off multiplechances=off showanswers=now show_stamps=on randomq=off randoma=off status=on labels=on title=on proofread=off list=off time=off scoring=off formid= vform=on autoadvance=off autosubmit=off inform=off forcecf=off forcegf=off offset=1 questions=11 firstid=576 lastid=2839 color=blue --> 
  <!--form action="" method="post" class="quiz-form" id="quiz-65"-->
    <div id="mtq_quiztitle-1" class="mtq_quiztitle">
  <h2>Variable Declaration and Scope</h2>
  </div>
      <noscript>
  <div id="mtq_javawarning-1" class="mtq_javawarning">
  Please wait while the activity loads. If this activity does not load, try refreshing your browser. Also, this page requires javascript. Please visit using a browser with javascript enabled.  <div class="mtq_failed_button" onclick="mtq_start_one(1)">
  If loading fails, click here to try again  </div></div>
  </noscript>
    
  <!-- root element for mtqscrollable -->

  <div id="mtq_question_container-1">
  <div>
          <div class="mtq_question mtq_scroll_item-1" id="mtq_question-1-1"><table class="mtq_question_heading_table"><tr><td><div class="mtq_question_label ">Question 1</div><div id="mtq_stamp-1-1" class="mtq_stamp"></div></td></tr></table><div id="mtq_question_text-1-1" class="mtq_question_text">Consider the following two C lines
<pre class="brush: cpp; title: ; notranslate" title="">
int var1;
extern int var2;
</pre>

Which of the following statements is correct</div><table class="mtq_answer_table"><colgroup><col class="mtq_oce_first" /></colgroup><tr id="mtq_row-1-1-1" onclick="mtq_button_click(1,1,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-1-1-1" class="mtq_css_letter_button mtq_letter_button_0" alt="Question 1, Choice 1">A</div><div id="mtq_marker-1-1-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-1-1-1" class="mtq_answer_text">Both statements only declare variables, don't define them.</div></td></tr><tr id="mtq_row-1-2-1" onclick="mtq_button_click(1,2,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-1-2-1" class="mtq_css_letter_button mtq_letter_button_1" alt="Question 1, Choice 2">B</div><div id="mtq_marker-1-2-1" class="mtq_marker mtq_correct_marker" alt="Correct"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-1-2-1" class="mtq_answer_text">First statement declares and defines var1, but second statement only declares var2</div></td></tr><tr id="mtq_row-1-3-1" onclick="mtq_button_click(1,3,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-1-3-1" class="mtq_css_letter_button mtq_letter_button_2" alt="Question 1, Choice 3">C</div><div id="mtq_marker-1-3-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-1-3-1" class="mtq_answer_text">Both statements declare define variables var1 and var2</div></td></tr></table><b><a>Variable Declaration and Scope</a>    </b><br><b><a>Discuss it</a></b></br><br></br><div id="mtq_question_explanation-1-1" class="mtq_explanation"><div class="mtq_explanation-label">Question 1 Explanation: </div><div class="mtq_explanation-text"> See <a target="_blank">Understanding "extern" keyword in C</a></div></div></div><div class="mtq_question mtq_scroll_item-1" id="mtq_question-2-1"><table class="mtq_question_heading_table"><tr><td><div class="mtq_question_label ">Question 2</div><div id="mtq_stamp-2-1" class="mtq_stamp"></div></td></tr></table><div id="mtq_question_text-2-1" class="mtq_question_text">Predict the output

<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
int var = 20;
int main()
{
    int var = var;
    printf("%d ", var);
    return 0;
}
</pre></div><table class="mtq_answer_table"><colgroup><col class="mtq_oce_first" /></colgroup><tr id="mtq_row-2-1-1" onclick="mtq_button_click(2,1,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-2-1-1" class="mtq_css_letter_button mtq_letter_button_0" alt="Question 2, Choice 1">A</div><div id="mtq_marker-2-1-1" class="mtq_marker mtq_correct_marker" alt="Correct"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-2-1-1" class="mtq_answer_text">Garbage Value</div></td></tr><tr id="mtq_row-2-2-1" onclick="mtq_button_click(2,2,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-2-2-1" class="mtq_css_letter_button mtq_letter_button_1" alt="Question 2, Choice 2">B</div><div id="mtq_marker-2-2-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-2-2-1" class="mtq_answer_text">20</div></td></tr><tr id="mtq_row-2-3-1" onclick="mtq_button_click(2,3,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-2-3-1" class="mtq_css_letter_button mtq_letter_button_2" alt="Question 2, Choice 3">C</div><div id="mtq_marker-2-3-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-2-3-1" class="mtq_answer_text">Compiler Error</div></td></tr></table><b><a>Variable Declaration and Scope</a>    </b><br><b><a>Discuss it</a></b></br><br></br><div id="mtq_question_explanation-2-1" class="mtq_explanation"><div class="mtq_explanation-label">Question 2 Explanation: </div><div class="mtq_explanation-text"> First var is declared, then value is assigned to it.  As soon as var is declared as a local variable, it hides the global variable var.</div></div></div><div class="mtq_question mtq_scroll_item-1" id="mtq_question-3-1"><table class="mtq_question_heading_table"><tr><td><div class="mtq_question_label ">Question 3</div><div id="mtq_stamp-3-1" class="mtq_stamp"></div></td></tr></table><div id="mtq_question_text-3-1" class="mtq_question_text"><pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
extern int var;
int main()
{
    var = 10;
    printf("%d ", var);
    return 0;
}
</pre></div><table class="mtq_answer_table"><colgroup><col class="mtq_oce_first" /></colgroup><tr id="mtq_row-3-1-1" onclick="mtq_button_click(3,1,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-3-1-1" class="mtq_css_letter_button mtq_letter_button_0" alt="Question 3, Choice 1">A</div><div id="mtq_marker-3-1-1" class="mtq_marker mtq_correct_marker" alt="Correct"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-3-1-1" class="mtq_answer_text">Compiler Error: var is not defined</div></td></tr><tr id="mtq_row-3-2-1" onclick="mtq_button_click(3,2,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-3-2-1" class="mtq_css_letter_button mtq_letter_button_1" alt="Question 3, Choice 2">B</div><div id="mtq_marker-3-2-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-3-2-1" class="mtq_answer_text">20</div></td></tr><tr id="mtq_row-3-3-1" onclick="mtq_button_click(3,3,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-3-3-1" class="mtq_css_letter_button mtq_letter_button_2" alt="Question 3, Choice 3">C</div><div id="mtq_marker-3-3-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-3-3-1" class="mtq_answer_text">0</div></td></tr></table><b><a>Variable Declaration and Scope</a>    </b><br><b><a>Discuss it</a></b></br><br></br><div id="mtq_question_explanation-3-1" class="mtq_explanation"><div class="mtq_explanation-label">Question 3 Explanation: </div><div class="mtq_explanation-text"> var is only declared and not defined (no memory allocated for it)

Refer:<a target="_blank"> Understanding "extern" keyword in C</a></div></div></div><div class="mtq_question mtq_scroll_item-1" id="mtq_question-4-1"><table class="mtq_question_heading_table"><tr><td><div class="mtq_question_label ">Question 4</div><div id="mtq_stamp-4-1" class="mtq_stamp"></div></td></tr></table><div id="mtq_question_text-4-1" class="mtq_question_text"><pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
extern int var = 0;
int main()
{
    var = 10;
    printf("%d ", var);
    return 0;
}
</pre></div><table class="mtq_answer_table"><colgroup><col class="mtq_oce_first" /></colgroup><tr id="mtq_row-4-1-1" onclick="mtq_button_click(4,1,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-4-1-1" class="mtq_css_letter_button mtq_letter_button_0" alt="Question 4, Choice 1">A</div><div id="mtq_marker-4-1-1" class="mtq_marker mtq_correct_marker" alt="Correct"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-4-1-1" class="mtq_answer_text">10</div></td></tr><tr id="mtq_row-4-2-1" onclick="mtq_button_click(4,2,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-4-2-1" class="mtq_css_letter_button mtq_letter_button_1" alt="Question 4, Choice 2">B</div><div id="mtq_marker-4-2-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-4-2-1" class="mtq_answer_text">Compiler Error: var is not defined</div></td></tr><tr id="mtq_row-4-3-1" onclick="mtq_button_click(4,3,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-4-3-1" class="mtq_css_letter_button mtq_letter_button_2" alt="Question 4, Choice 3">C</div><div id="mtq_marker-4-3-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-4-3-1" class="mtq_answer_text">0</div></td></tr></table><b><a>Variable Declaration and Scope</a>    </b><br><b><a>Discuss it</a></b></br><br></br><div id="mtq_question_explanation-4-1" class="mtq_explanation"><div class="mtq_explanation-label">Question 4 Explanation: </div><div class="mtq_explanation-text"> If a variable is only declared and an initializer is also provided with that declaration, then the memory for that variable will be allocated i.e. that variable will be considered as defined.

Refer: <a target="_blank">Understanding "extern" keyword in C</a>
</div></div></div><div class="mtq_question mtq_scroll_item-1" id="mtq_question-5-1"><table class="mtq_question_heading_table"><tr><td><div class="mtq_question_label ">Question 5</div><div id="mtq_stamp-5-1" class="mtq_stamp"></div></td></tr></table><div id="mtq_question_text-5-1" class="mtq_question_text">Output?
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
  {
      int var = 10;
  }
  {
      printf("%d", var);  
  }
  return 0;
}
</pre></div><table class="mtq_answer_table"><colgroup><col class="mtq_oce_first" /></colgroup><tr id="mtq_row-5-1-1" onclick="mtq_button_click(5,1,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-5-1-1" class="mtq_css_letter_button mtq_letter_button_0" alt="Question 5, Choice 1">A</div><div id="mtq_marker-5-1-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-5-1-1" class="mtq_answer_text">10</div></td></tr><tr id="mtq_row-5-2-1" onclick="mtq_button_click(5,2,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-5-2-1" class="mtq_css_letter_button mtq_letter_button_1" alt="Question 5, Choice 2">B</div><div id="mtq_marker-5-2-1" class="mtq_marker mtq_correct_marker" alt="Correct"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-5-2-1" class="mtq_answer_text">Compiler Errror</div></td></tr><tr id="mtq_row-5-3-1" onclick="mtq_button_click(5,3,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-5-3-1" class="mtq_css_letter_button mtq_letter_button_2" alt="Question 5, Choice 3">C</div><div id="mtq_marker-5-3-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-5-3-1" class="mtq_answer_text">Garbage Value</div></td></tr></table><b><a>Variable Declaration and Scope</a>    </b><br><b><a>Discuss it</a></b></br><br></br><div id="mtq_question_explanation-5-1" class="mtq_explanation"><div class="mtq_explanation-label">Question 5 Explanation: </div><div class="mtq_explanation-text"> x is not accessible.

The curly brackets define a block of scope.  Anything declared between curly brackets goes out of scope after the closing bracket.</div></div></div><div class="mtq_question mtq_scroll_item-1" id="mtq_question-6-1"><table class="mtq_question_heading_table"><tr><td><div class="mtq_question_label ">Question 6</div><div id="mtq_stamp-6-1" class="mtq_stamp"></div></td></tr></table><div id="mtq_question_text-6-1" class="mtq_question_text">Output? 
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
int main()
{
  int x = 1, y = 2, z = 3;
  printf(" x = %d, y = %d, z = %d \n", x, y, z);
  {
       int x = 10;
       float y = 20;
       printf(" x = %d, y = %f, z = %d \n", x, y, z);
       {
             int z = 100;
             printf(" x = %d, y = %f, z = %d \n", x, y, z);
       }
  }
  return 0;
}
</pre></div><table class="mtq_answer_table"><colgroup><col class="mtq_oce_first" /></colgroup><tr id="mtq_row-6-1-1" onclick="mtq_button_click(6,1,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-6-1-1" class="mtq_css_letter_button mtq_letter_button_0" alt="Question 6, Choice 1">A</div><div id="mtq_marker-6-1-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-6-1-1" class="mtq_answer_text"><pre> x = 1, y = 2, z = 3
 x = 10, y = 20.000000, z = 3
 x = 1, y = 2, z = 100</pre>


</div></td></tr><tr id="mtq_row-6-2-1" onclick="mtq_button_click(6,2,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-6-2-1" class="mtq_css_letter_button mtq_letter_button_1" alt="Question 6, Choice 2">B</div><div id="mtq_marker-6-2-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-6-2-1" class="mtq_answer_text">Compiler Error</div></td></tr><tr id="mtq_row-6-3-1" onclick="mtq_button_click(6,3,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-6-3-1" class="mtq_css_letter_button mtq_letter_button_2" alt="Question 6, Choice 3">C</div><div id="mtq_marker-6-3-1" class="mtq_marker mtq_correct_marker" alt="Correct"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-6-3-1" class="mtq_answer_text"><pre> x = 1, y = 2, z = 3
 x = 10, y = 20.000000, z = 3
 x = 10, y = 20.000000, z = 100 </pre></div></td></tr><tr id="mtq_row-6-4-1" onclick="mtq_button_click(6,4,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-6-4-1" class="mtq_css_letter_button mtq_letter_button_3" alt="Question 6, Choice 4">D</div><div id="mtq_marker-6-4-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-6-4-1" class="mtq_answer_text"><pre> x = 1, y = 2, z = 3
 x = 1, y = 2, z = 3
 x = 1, y = 2, z = 3</pre></div></td></tr></table><b><a>Variable Declaration and Scope</a>    </b><br><b><a>Discuss it</a></b></br><br></br><div id="mtq_question_explanation-6-1" class="mtq_explanation"><div class="mtq_explanation-label">Question 6 Explanation: </div><div class="mtq_explanation-text"> See <a target="_blank">Scope rules in C</a></div></div></div><div class="mtq_question mtq_scroll_item-1" id="mtq_question-7-1"><table class="mtq_question_heading_table"><tr><td><div class="mtq_question_label ">Question 7</div><div id="mtq_stamp-7-1" class="mtq_stamp"></div></td></tr></table><div id="mtq_question_text-7-1" class="mtq_question_text"><pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
  int x = 032;
  printf("%d", x);
  return 0;
}
</pre></div><table class="mtq_answer_table"><colgroup><col class="mtq_oce_first" /></colgroup><tr id="mtq_row-7-1-1" onclick="mtq_button_click(7,1,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-7-1-1" class="mtq_css_letter_button mtq_letter_button_0" alt="Question 7, Choice 1">A</div><div id="mtq_marker-7-1-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-7-1-1" class="mtq_answer_text">32</div></td></tr><tr id="mtq_row-7-2-1" onclick="mtq_button_click(7,2,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-7-2-1" class="mtq_css_letter_button mtq_letter_button_1" alt="Question 7, Choice 2">B</div><div id="mtq_marker-7-2-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-7-2-1" class="mtq_answer_text">0</div></td></tr><tr id="mtq_row-7-3-1" onclick="mtq_button_click(7,3,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-7-3-1" class="mtq_css_letter_button mtq_letter_button_2" alt="Question 7, Choice 3">C</div><div id="mtq_marker-7-3-1" class="mtq_marker mtq_correct_marker" alt="Correct"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-7-3-1" class="mtq_answer_text">26</div></td></tr><tr id="mtq_row-7-4-1" onclick="mtq_button_click(7,4,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-7-4-1" class="mtq_css_letter_button mtq_letter_button_3" alt="Question 7, Choice 4">D</div><div id="mtq_marker-7-4-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-7-4-1" class="mtq_answer_text">50</div></td></tr></table><b><a>Variable Declaration and Scope</a>    </b><br><b><a>Discuss it</a></b></br><br></br><div id="mtq_question_explanation-7-1" class="mtq_explanation"><div class="mtq_explanation-label">Question 7 Explanation: </div><div class="mtq_explanation-text"> When a constant value starts with 0, it is considered as octal number.  Therefore the value of x is 3*8 + 2 = 26</div></div></div><div class="mtq_question mtq_scroll_item-1" id="mtq_question-8-1"><table class="mtq_question_heading_table"><tr><td><div class="mtq_question_label ">Question 8</div><div id="mtq_stamp-8-1" class="mtq_stamp"></div></td></tr></table><div id="mtq_question_text-8-1" class="mtq_question_text">Consider the following C program, which variable has the longest scope?

<pre class="brush: cpp; title: ; notranslate" title="">
int a;
int main()
{
   int b;
   // ..
   // ..
}
int c;
</pre></div><table class="mtq_answer_table"><colgroup><col class="mtq_oce_first" /></colgroup><tr id="mtq_row-8-1-1" onclick="mtq_button_click(8,1,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-8-1-1" class="mtq_css_letter_button mtq_letter_button_0" alt="Question 8, Choice 1">A</div><div id="mtq_marker-8-1-1" class="mtq_marker mtq_correct_marker" alt="Correct"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-8-1-1" class="mtq_answer_text">a</div></td></tr><tr id="mtq_row-8-2-1" onclick="mtq_button_click(8,2,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-8-2-1" class="mtq_css_letter_button mtq_letter_button_1" alt="Question 8, Choice 2">B</div><div id="mtq_marker-8-2-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-8-2-1" class="mtq_answer_text">b</div></td></tr><tr id="mtq_row-8-3-1" onclick="mtq_button_click(8,3,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-8-3-1" class="mtq_css_letter_button mtq_letter_button_2" alt="Question 8, Choice 3">C</div><div id="mtq_marker-8-3-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-8-3-1" class="mtq_answer_text">c</div></td></tr><tr id="mtq_row-8-4-1" onclick="mtq_button_click(8,4,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-8-4-1" class="mtq_css_letter_button mtq_letter_button_3" alt="Question 8, Choice 4">D</div><div id="mtq_marker-8-4-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-8-4-1" class="mtq_answer_text">All have same scope</div></td></tr></table><b><a>Variable Declaration and Scope</a>    </b><br><b><a>Discuss it</a></b></br><br></br><div id="mtq_question_explanation-8-1" class="mtq_explanation"><div class="mtq_explanation-label">Question 8 Explanation: </div><div class="mtq_explanation-text"> a is accessible everywhere.

b is limited to main()

c is accessible only after its declaration.</div></div></div><div class="mtq_question mtq_scroll_item-1" id="mtq_question-9-1"><table class="mtq_question_heading_table"><tr><td><div class="mtq_question_label ">Question 9</div><div id="mtq_stamp-9-1" class="mtq_stamp"></div></td></tr></table><div id="mtq_question_text-9-1" class="mtq_question_text">Consider the following variable declarations and definitions in C

<pre class="brush: cpp; title: ; notranslate" title="">
i) int var_9 = 1;
ii) int 9_var = 2;
iii) int _ = 3;
</pre>

Choose the correct statement w.r.t. above variables.</div><table class="mtq_answer_table"><colgroup><col class="mtq_oce_first" /></colgroup><tr id="mtq_row-9-1-1" onclick="mtq_button_click(9,1,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-9-1-1" class="mtq_css_letter_button mtq_letter_button_0" alt="Question 9, Choice 1">A</div><div id="mtq_marker-9-1-1" class="mtq_marker mtq_correct_marker" alt="Correct"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-9-1-1" class="mtq_answer_text">Both i) and iii) are valid.</div></td></tr><tr id="mtq_row-9-2-1" onclick="mtq_button_click(9,2,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-9-2-1" class="mtq_css_letter_button mtq_letter_button_1" alt="Question 9, Choice 2">B</div><div id="mtq_marker-9-2-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-9-2-1" class="mtq_answer_text">Only i) is valid.</div></td></tr><tr id="mtq_row-9-3-1" onclick="mtq_button_click(9,3,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-9-3-1" class="mtq_css_letter_button mtq_letter_button_2" alt="Question 9, Choice 3">C</div><div id="mtq_marker-9-3-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-9-3-1" class="mtq_answer_text">Both i) and ii) are valid.</div></td></tr><tr id="mtq_row-9-4-1" onclick="mtq_button_click(9,4,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-9-4-1" class="mtq_css_letter_button mtq_letter_button_3" alt="Question 9, Choice 4">D</div><div id="mtq_marker-9-4-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-9-4-1" class="mtq_answer_text">All are valid.</div></td></tr></table><b><a>Variable Declaration and Scope</a>    </b><b><a>C Quiz - 101</a>    </b><br><b><a>Discuss it</a></b></br><br></br><div id="mtq_question_explanation-9-1" class="mtq_explanation"><div class="mtq_explanation-label">Question 9 Explanation: </div><div class="mtq_explanation-text"> In C language, a variable name can consists of letters, digits and underscore i.e. _ . But a variable name has to start with either letter or underscore. It can't start with a digit. So valid variables are var_9 and _ from the above question. Even two back to back underscore i.e. __ is also a valid variable name. Even _9 is a valid variable. But 9var and 9_ are invalid variables in C. This will be caught at the time of compilation itself. That's why the correct answer is A).

 </div></div></div><div class="mtq_question mtq_scroll_item-1" id="mtq_question-10-1"><table class="mtq_question_heading_table"><tr><td><div class="mtq_question_label ">Question 10</div><div id="mtq_stamp-10-1" class="mtq_stamp"></div></td></tr></table><div id="mtq_question_text-10-1" class="mtq_question_text">Find out the correct statement for the following program.
<pre class="brush: cpp; title: ; notranslate" title="">
#include "stdio.h"

int * gPtr;

int main()
{
 int * lPtr = NULL;

 if(gPtr == lPtr)
 {
   printf("Equal!");
 }
 else
 {
  printf("Not Equal");
 }

 return 0;
}
</pre></div><table class="mtq_answer_table"><colgroup><col class="mtq_oce_first" /></colgroup><tr id="mtq_row-10-1-1" onclick="mtq_button_click(10,1,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-10-1-1" class="mtq_css_letter_button mtq_letter_button_0" alt="Question 10, Choice 1">A</div><div id="mtq_marker-10-1-1" class="mtq_marker mtq_correct_marker" alt="Correct"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-10-1-1" class="mtq_answer_text">It'll always print Equal.</div></td></tr><tr id="mtq_row-10-2-1" onclick="mtq_button_click(10,2,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-10-2-1" class="mtq_css_letter_button mtq_letter_button_1" alt="Question 10, Choice 2">B</div><div id="mtq_marker-10-2-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-10-2-1" class="mtq_answer_text">It'll always print Not Equal.</div></td></tr><tr id="mtq_row-10-3-1" onclick="mtq_button_click(10,3,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-10-3-1" class="mtq_css_letter_button mtq_letter_button_2" alt="Question 10, Choice 3">C</div><div id="mtq_marker-10-3-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-10-3-1" class="mtq_answer_text">Since gPtr isn't initialized in the program, it'll print sometimes Equal and at other times Not Equal.</div></td></tr></table><b><a>Variable Declaration and Scope</a>    </b><b><a>C Quiz - 109</a>    </b><br><b><a>Discuss it</a></b></br><br></br><div id="mtq_question_explanation-10-1" class="mtq_explanation"><div class="mtq_explanation-label">Question 10 Explanation: </div><div class="mtq_explanation-text"> It should be noted that global variables such gPtr (which is a global pointer to int) are initialized to ZERO. That's why gPtr (which is a global pointer and initialized implicitly) and lPtr (which a is local pointer and initialized explicitly) would have same value i.e. correct answer is a.</div></div></div><div class="mtq_question mtq_scroll_item-1" id="mtq_question-11-1"><table class="mtq_question_heading_table"><tr><td><div class="mtq_question_label ">Question 11</div><div id="mtq_stamp-11-1" class="mtq_stamp"></div></td></tr></table><div id="mtq_question_text-11-1" class="mtq_question_text">Consider the program below in a hypothetical language which allows global variable and a choice of call by reference or call by value methods of parameter passing.
<pre class="brush: cpp; title: ; notranslate" title="">
 int i ;
program main ()
{
    int j = 60;
    i = 50;
    call f (i, j);
    print i, j;
}
procedure f (x, y)
{           
    i = 100;
    x = 10;
    y = y + i ;
}
</pre>
Which one of the following options represents the correct output of the program for the two parameter passing mechanisms?</div><table class="mtq_answer_table"><colgroup><col class="mtq_oce_first" /></colgroup><tr id="mtq_row-11-1-1" onclick="mtq_button_click(11,1,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-11-1-1" class="mtq_css_letter_button mtq_letter_button_0" alt="Question 11, Choice 1">A</div><div id="mtq_marker-11-1-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-11-1-1" class="mtq_answer_text">Call by value : i = 70, j = 10; Call by reference : i = 60, j = 70</div></td></tr><tr id="mtq_row-11-2-1" onclick="mtq_button_click(11,2,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-11-2-1" class="mtq_css_letter_button mtq_letter_button_1" alt="Question 11, Choice 2">B</div><div id="mtq_marker-11-2-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-11-2-1" class="mtq_answer_text">Call by value : i = 50, j = 60; Call by reference : i = 50, j = 70</div></td></tr><tr id="mtq_row-11-3-1" onclick="mtq_button_click(11,3,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-11-3-1" class="mtq_css_letter_button mtq_letter_button_2" alt="Question 11, Choice 3">C</div><div id="mtq_marker-11-3-1" class="mtq_marker mtq_wrong_marker" alt="Wrong"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-11-3-1" class="mtq_answer_text">Call by value : i = 10, j = 70; Call by reference : i = 100, j = 60</div></td></tr><tr id="mtq_row-11-4-1" onclick="mtq_button_click(11,4,1)" class="mtq_clickable"><td class="mtq_letter_button_td"><div id="mtq_button-11-4-1" class="mtq_css_letter_button mtq_letter_button_3" alt="Question 11, Choice 4">D</div><div id="mtq_marker-11-4-1" class="mtq_marker mtq_correct_marker" alt="Correct"></div></td><td class="mtq_answer_td"><div id="mtq_answer_text-11-4-1" class="mtq_answer_text">Call by value : i = 100, j = 60; Call by reference : i = 10, j = 70</div></td></tr></table><b><a>Variable Declaration and Scope</a>    </b><b><a>C Quiz - 113</a>    </b><b><a>Gate IT 2007</a>    </b><br><b><a>Discuss it</a></b></br><br></br><div id="mtq_question_explanation-11-1" class="mtq_explanation"><div class="mtq_explanation-label">Question 11 Explanation: </div><div class="mtq_explanation-text"> Call by value: A copy of parameters will be passed and whatever updations are performed will be valid only for that copy, leaving original values intact.
<br />Call by reference: A link to original variables will be passed, by allowing the function to manipulate the original variables.
</div></div></div>                <!--End of mtqscrollable items--> 
    
  </div>
  <!--End of mtqscrollable--> 
  <!--mtq_status-->
    <div id="mtq_quiz_status-1" class="mtq_quiz_status">
  There are 11 questions to complete.  </div>
      </div> 
  <!--Holds all questions-->
    <div id="mtq_variables" class="mtq_preload" style="display:none"> <input type="hidden" id="mtq_id-1" name="mtq_id_value" value="1" /><input type="hidden" name="question_id[]" value="576" /><input type="hidden" id="mtq_is_answered-1-1" value="0" /><input type="hidden" id="mtq_is_correct-1-1" value="0" /><input type="hidden" id="mtq_is_worth-1-1" value="100" /><input type="hidden" id="mtq_num_attempts-1-1" value="0" /><input type="hidden" id="mtq_points_awarded-1-1" value="0" /><input type="hidden" id="mtq_is_correct-1-1-1" value="0" /><input type="hidden" id="mtq_was_selected-1-1-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-1-1-1" value="0" /><input type="hidden" id="mtq_has_hint-1-1-1" value="0" /><input type="hidden" id="mtq_is_correct-1-2-1" value="1" /><input type="hidden" id="mtq_was_selected-1-2-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-1-2-1" value="0" /><input type="hidden" id="mtq_has_hint-1-2-1" value="0" /><input type="hidden" id="mtq_is_correct-1-3-1" value="0" /><input type="hidden" id="mtq_was_selected-1-3-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-1-3-1" value="0" /><input type="hidden" id="mtq_has_hint-1-3-1" value="0" /><input type="hidden" id="mtq_num_ans-1-1" value="3" /><input type="hidden" id="mtq_num_correct-1-1" value="1" /><input type="hidden" name="question_id[]" value="577" /><input type="hidden" id="mtq_is_answered-2-1" value="0" /><input type="hidden" id="mtq_is_correct-2-1" value="0" /><input type="hidden" id="mtq_is_worth-2-1" value="100" /><input type="hidden" id="mtq_num_attempts-2-1" value="0" /><input type="hidden" id="mtq_points_awarded-2-1" value="0" /><input type="hidden" id="mtq_is_correct-2-1-1" value="1" /><input type="hidden" id="mtq_was_selected-2-1-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-2-1-1" value="0" /><input type="hidden" id="mtq_has_hint-2-1-1" value="0" /><input type="hidden" id="mtq_is_correct-2-2-1" value="0" /><input type="hidden" id="mtq_was_selected-2-2-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-2-2-1" value="0" /><input type="hidden" id="mtq_has_hint-2-2-1" value="0" /><input type="hidden" id="mtq_is_correct-2-3-1" value="0" /><input type="hidden" id="mtq_was_selected-2-3-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-2-3-1" value="0" /><input type="hidden" id="mtq_has_hint-2-3-1" value="0" /><input type="hidden" id="mtq_num_ans-2-1" value="3" /><input type="hidden" id="mtq_num_correct-2-1" value="1" /><input type="hidden" name="question_id[]" value="578" /><input type="hidden" id="mtq_is_answered-3-1" value="0" /><input type="hidden" id="mtq_is_correct-3-1" value="0" /><input type="hidden" id="mtq_is_worth-3-1" value="100" /><input type="hidden" id="mtq_num_attempts-3-1" value="0" /><input type="hidden" id="mtq_points_awarded-3-1" value="0" /><input type="hidden" id="mtq_is_correct-3-1-1" value="1" /><input type="hidden" id="mtq_was_selected-3-1-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-3-1-1" value="0" /><input type="hidden" id="mtq_has_hint-3-1-1" value="0" /><input type="hidden" id="mtq_is_correct-3-2-1" value="0" /><input type="hidden" id="mtq_was_selected-3-2-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-3-2-1" value="0" /><input type="hidden" id="mtq_has_hint-3-2-1" value="0" /><input type="hidden" id="mtq_is_correct-3-3-1" value="0" /><input type="hidden" id="mtq_was_selected-3-3-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-3-3-1" value="0" /><input type="hidden" id="mtq_has_hint-3-3-1" value="0" /><input type="hidden" id="mtq_num_ans-3-1" value="3" /><input type="hidden" id="mtq_num_correct-3-1" value="1" /><input type="hidden" name="question_id[]" value="579" /><input type="hidden" id="mtq_is_answered-4-1" value="0" /><input type="hidden" id="mtq_is_correct-4-1" value="0" /><input type="hidden" id="mtq_is_worth-4-1" value="100" /><input type="hidden" id="mtq_num_attempts-4-1" value="0" /><input type="hidden" id="mtq_points_awarded-4-1" value="0" /><input type="hidden" id="mtq_is_correct-4-1-1" value="1" /><input type="hidden" id="mtq_was_selected-4-1-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-4-1-1" value="0" /><input type="hidden" id="mtq_has_hint-4-1-1" value="0" /><input type="hidden" id="mtq_is_correct-4-2-1" value="0" /><input type="hidden" id="mtq_was_selected-4-2-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-4-2-1" value="0" /><input type="hidden" id="mtq_has_hint-4-2-1" value="0" /><input type="hidden" id="mtq_is_correct-4-3-1" value="0" /><input type="hidden" id="mtq_was_selected-4-3-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-4-3-1" value="0" /><input type="hidden" id="mtq_has_hint-4-3-1" value="0" /><input type="hidden" id="mtq_num_ans-4-1" value="3" /><input type="hidden" id="mtq_num_correct-4-1" value="1" /><input type="hidden" name="question_id[]" value="580" /><input type="hidden" id="mtq_is_answered-5-1" value="0" /><input type="hidden" id="mtq_is_correct-5-1" value="0" /><input type="hidden" id="mtq_is_worth-5-1" value="100" /><input type="hidden" id="mtq_num_attempts-5-1" value="0" /><input type="hidden" id="mtq_points_awarded-5-1" value="0" /><input type="hidden" id="mtq_is_correct-5-1-1" value="0" /><input type="hidden" id="mtq_was_selected-5-1-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-5-1-1" value="0" /><input type="hidden" id="mtq_has_hint-5-1-1" value="0" /><input type="hidden" id="mtq_is_correct-5-2-1" value="1" /><input type="hidden" id="mtq_was_selected-5-2-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-5-2-1" value="0" /><input type="hidden" id="mtq_has_hint-5-2-1" value="0" /><input type="hidden" id="mtq_is_correct-5-3-1" value="0" /><input type="hidden" id="mtq_was_selected-5-3-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-5-3-1" value="0" /><input type="hidden" id="mtq_has_hint-5-3-1" value="0" /><input type="hidden" id="mtq_num_ans-5-1" value="3" /><input type="hidden" id="mtq_num_correct-5-1" value="1" /><input type="hidden" name="question_id[]" value="581" /><input type="hidden" id="mtq_is_answered-6-1" value="0" /><input type="hidden" id="mtq_is_correct-6-1" value="0" /><input type="hidden" id="mtq_is_worth-6-1" value="100" /><input type="hidden" id="mtq_num_attempts-6-1" value="0" /><input type="hidden" id="mtq_points_awarded-6-1" value="0" /><input type="hidden" id="mtq_is_correct-6-1-1" value="0" /><input type="hidden" id="mtq_was_selected-6-1-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-6-1-1" value="0" /><input type="hidden" id="mtq_has_hint-6-1-1" value="0" /><input type="hidden" id="mtq_is_correct-6-2-1" value="0" /><input type="hidden" id="mtq_was_selected-6-2-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-6-2-1" value="0" /><input type="hidden" id="mtq_has_hint-6-2-1" value="0" /><input type="hidden" id="mtq_is_correct-6-3-1" value="1" /><input type="hidden" id="mtq_was_selected-6-3-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-6-3-1" value="0" /><input type="hidden" id="mtq_has_hint-6-3-1" value="0" /><input type="hidden" id="mtq_is_correct-6-4-1" value="0" /><input type="hidden" id="mtq_was_selected-6-4-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-6-4-1" value="0" /><input type="hidden" id="mtq_has_hint-6-4-1" value="0" /><input type="hidden" id="mtq_num_ans-6-1" value="4" /><input type="hidden" id="mtq_num_correct-6-1" value="1" /><input type="hidden" name="question_id[]" value="954" /><input type="hidden" id="mtq_is_answered-7-1" value="0" /><input type="hidden" id="mtq_is_correct-7-1" value="0" /><input type="hidden" id="mtq_is_worth-7-1" value="100" /><input type="hidden" id="mtq_num_attempts-7-1" value="0" /><input type="hidden" id="mtq_points_awarded-7-1" value="0" /><input type="hidden" id="mtq_is_correct-7-1-1" value="0" /><input type="hidden" id="mtq_was_selected-7-1-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-7-1-1" value="0" /><input type="hidden" id="mtq_has_hint-7-1-1" value="0" /><input type="hidden" id="mtq_is_correct-7-2-1" value="0" /><input type="hidden" id="mtq_was_selected-7-2-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-7-2-1" value="0" /><input type="hidden" id="mtq_has_hint-7-2-1" value="0" /><input type="hidden" id="mtq_is_correct-7-3-1" value="1" /><input type="hidden" id="mtq_was_selected-7-3-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-7-3-1" value="0" /><input type="hidden" id="mtq_has_hint-7-3-1" value="0" /><input type="hidden" id="mtq_is_correct-7-4-1" value="0" /><input type="hidden" id="mtq_was_selected-7-4-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-7-4-1" value="0" /><input type="hidden" id="mtq_has_hint-7-4-1" value="0" /><input type="hidden" id="mtq_num_ans-7-1" value="4" /><input type="hidden" id="mtq_num_correct-7-1" value="1" /><input type="hidden" name="question_id[]" value="1411" /><input type="hidden" id="mtq_is_answered-8-1" value="0" /><input type="hidden" id="mtq_is_correct-8-1" value="0" /><input type="hidden" id="mtq_is_worth-8-1" value="100" /><input type="hidden" id="mtq_num_attempts-8-1" value="0" /><input type="hidden" id="mtq_points_awarded-8-1" value="0" /><input type="hidden" id="mtq_is_correct-8-1-1" value="1" /><input type="hidden" id="mtq_was_selected-8-1-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-8-1-1" value="0" /><input type="hidden" id="mtq_has_hint-8-1-1" value="0" /><input type="hidden" id="mtq_is_correct-8-2-1" value="0" /><input type="hidden" id="mtq_was_selected-8-2-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-8-2-1" value="0" /><input type="hidden" id="mtq_has_hint-8-2-1" value="0" /><input type="hidden" id="mtq_is_correct-8-3-1" value="0" /><input type="hidden" id="mtq_was_selected-8-3-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-8-3-1" value="0" /><input type="hidden" id="mtq_has_hint-8-3-1" value="0" /><input type="hidden" id="mtq_is_correct-8-4-1" value="0" /><input type="hidden" id="mtq_was_selected-8-4-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-8-4-1" value="0" /><input type="hidden" id="mtq_has_hint-8-4-1" value="0" /><input type="hidden" id="mtq_num_ans-8-1" value="4" /><input type="hidden" id="mtq_num_correct-8-1" value="1" /><input type="hidden" name="question_id[]" value="2141" /><input type="hidden" id="mtq_is_answered-9-1" value="0" /><input type="hidden" id="mtq_is_correct-9-1" value="0" /><input type="hidden" id="mtq_is_worth-9-1" value="100" /><input type="hidden" id="mtq_num_attempts-9-1" value="0" /><input type="hidden" id="mtq_points_awarded-9-1" value="0" /><input type="hidden" id="mtq_is_correct-9-1-1" value="1" /><input type="hidden" id="mtq_was_selected-9-1-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-9-1-1" value="0" /><input type="hidden" id="mtq_has_hint-9-1-1" value="0" /><input type="hidden" id="mtq_is_correct-9-2-1" value="0" /><input type="hidden" id="mtq_was_selected-9-2-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-9-2-1" value="0" /><input type="hidden" id="mtq_has_hint-9-2-1" value="0" /><input type="hidden" id="mtq_is_correct-9-3-1" value="0" /><input type="hidden" id="mtq_was_selected-9-3-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-9-3-1" value="0" /><input type="hidden" id="mtq_has_hint-9-3-1" value="0" /><input type="hidden" id="mtq_is_correct-9-4-1" value="0" /><input type="hidden" id="mtq_was_selected-9-4-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-9-4-1" value="0" /><input type="hidden" id="mtq_has_hint-9-4-1" value="0" /><input type="hidden" id="mtq_num_ans-9-1" value="4" /><input type="hidden" id="mtq_num_correct-9-1" value="1" /><input type="hidden" name="question_id[]" value="2214" /><input type="hidden" id="mtq_is_answered-10-1" value="0" /><input type="hidden" id="mtq_is_correct-10-1" value="0" /><input type="hidden" id="mtq_is_worth-10-1" value="100" /><input type="hidden" id="mtq_num_attempts-10-1" value="0" /><input type="hidden" id="mtq_points_awarded-10-1" value="0" /><input type="hidden" id="mtq_is_correct-10-1-1" value="1" /><input type="hidden" id="mtq_was_selected-10-1-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-10-1-1" value="0" /><input type="hidden" id="mtq_has_hint-10-1-1" value="0" /><input type="hidden" id="mtq_is_correct-10-2-1" value="0" /><input type="hidden" id="mtq_was_selected-10-2-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-10-2-1" value="0" /><input type="hidden" id="mtq_has_hint-10-2-1" value="0" /><input type="hidden" id="mtq_is_correct-10-3-1" value="0" /><input type="hidden" id="mtq_was_selected-10-3-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-10-3-1" value="0" /><input type="hidden" id="mtq_has_hint-10-3-1" value="0" /><input type="hidden" id="mtq_num_ans-10-1" value="3" /><input type="hidden" id="mtq_num_correct-10-1" value="1" /><input type="hidden" name="question_id[]" value="2839" /><input type="hidden" id="mtq_is_answered-11-1" value="0" /><input type="hidden" id="mtq_is_correct-11-1" value="0" /><input type="hidden" id="mtq_is_worth-11-1" value="100" /><input type="hidden" id="mtq_num_attempts-11-1" value="0" /><input type="hidden" id="mtq_points_awarded-11-1" value="0" /><input type="hidden" id="mtq_is_correct-11-1-1" value="0" /><input type="hidden" id="mtq_was_selected-11-1-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-11-1-1" value="0" /><input type="hidden" id="mtq_has_hint-11-1-1" value="0" /><input type="hidden" id="mtq_is_correct-11-2-1" value="0" /><input type="hidden" id="mtq_was_selected-11-2-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-11-2-1" value="0" /><input type="hidden" id="mtq_has_hint-11-2-1" value="0" /><input type="hidden" id="mtq_is_correct-11-3-1" value="0" /><input type="hidden" id="mtq_was_selected-11-3-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-11-3-1" value="0" /><input type="hidden" id="mtq_has_hint-11-3-1" value="0" /><input type="hidden" id="mtq_is_correct-11-4-1" value="1" /><input type="hidden" id="mtq_was_selected-11-4-1" value="0" /><input type="hidden" id="mtq_was_ever_selected-11-4-1" value="0" /><input type="hidden" id="mtq_has_hint-11-4-1" value="0" /><input type="hidden" id="mtq_num_ans-11-1" value="4" /><input type="hidden" id="mtq_num_correct-11-1" value="1" /> <div id="mtq_have_completed_string" class="mtq_preload"><b>
    You have completed    </b></div> <div id="mtq_questions_string" class="mtq_preload">
    questions    </div> <div id="mtq_question_string" class="mtq_preload">
    question    </div> <div id="mtq_your_score_is_string" class="mtq_preload">
    Your score is    </div> <div id="mtq_correct_string" class="mtq_preload">
    Correct    </div> <div id="mtq_wrong_string" class="mtq_preload">
    Wrong    </div> <div id="mtq_partial_string" class="mtq_preload">
    Partial-Credit    </div> <div id="mtq_exit_warning_string" class="mtq_preload">
    You have not finished your quiz. If you leave this page, your progress will be lost.    </div> <div id="mtq_correct_answer_string" class="mtq_preload">
    Correct Answer    </div> <div id="mtq_you_selected_string" class="mtq_preload">
    You Selected    </div> <div id="mtq_not_attempted_string" class="mtq_preload">
    Not Attempted    </div> <div id="mtq_final_score_on_quiz_string" class="mtq_preload">
    Final Score on Quiz    </div> <div id="mtq_attempted_questions_correct_string" class="mtq_preload">
    Attempted Questions Correct    </div> <div id="mtq_attempted_questions_wrong_string" class="mtq_preload">
    Attempted Questions Wrong    </div> <div id="mtq_questions_not_attempted_string" class="mtq_preload">
    Questions Not Attempted    </div> <div id="mtq_total_questions_on_quiz_string" class="mtq_preload">
    Total Questions on Quiz    </div> <div id="mtq_question_details_string" class="mtq_preload">
    Question Details    </div> <div id="mtq_quiz_results_string" class="mtq_preload">
    Results    </div> <div id="mtq_date_string" class="mtq_preload">
    Date    </div> <div id="mtq_score_string" class="mtq_preload">
    Score    </div> <div id="mtq_hint_string" class="mtq_preload">
    Hint    </div>
    <div id="mtq_time_allowed_string" class="mtq_preload">Time allowed</div>
<div id="mtq_minutes_string" class="mtq_preload">minutes</div>
<div id="mtq_seconds_string" class="mtq_preload">seconds</div>
<div id="mtq_time_used_string" class="mtq_preload">Time used</div>
<div id="mtq_answer_choices_selected_string" class="mtq_preload">Answer Choice(s) Selected</div>
<div id="mtq_question_text_string" class="mtq_preload">Question Text</div>


    <input type="hidden" id="mtq_answer_display-1" value="2" />
    <input type="hidden" id="mtq_autoadvance-1" value="0" />
    <input type="hidden" id="mtq_autosubmit-1" value="0" />
    <input type="hidden" id="mtq_single_page-1" value="1" />
    <input type="hidden" id="mtq_show_hints-1" value="1" />
    <input type="hidden" id="mtq_show_start-1" value="0" />
    <input type="hidden" id="mtq_show_final-1" value="0" />
    <input type="hidden" id="mtq_show_alerts-1" value="0" />
    <input type="hidden" id="mtq_multiple_chances-1" value="0" />
    <input type="hidden" id="mtq_proofread-1" value="0" />
    <input type="hidden" id="mtq_scoring-1" value="0" />
    <input type="hidden" id="mtq_vform-1" value="1" />
    <input type="hidden" name="quiz_id" id="quiz_id-1" value="65" />
    <input type="hidden" name="mtq_total_questions" id="mtq_total_questions-1" value="11" />
    <input type="hidden" name="mtq_current_score" id="mtq_current_score-1" value="0" />
    <input type="hidden" name="mtq_max_score" id="mtq_max_score-1" value="0" />
    <input type="hidden" name="mtq_questions_attempted" id="mtq_questions_attempted-1" value="0" />
    <input type="hidden" name="mtq_questions_correct" id="mtq_questions_correct-1" value="0" />
    <input type="hidden" name="mtq_questions_wrong" id="mtq_questions_wrong-1" value="0" />
    <input type="hidden" name="mtq_questions_not_attempted" id="mtq_questions_not_attempted-1" value="0" />
    <input type="hidden" id="mtq_display_number-1" value="1" />
    <input type="hidden" id="mtq_show_list_option-1" value="0" />
    <input type="hidden" id="mtq_show_stamps-1" value="1" />
    <input type="hidden" id="mtq_num_ratings-1" value="6" /><input type="hidden" id="mtq_ratingval-1-1" value="-1" /><div id="mtq_rating-1-1" class="mtq_preload">All done</div><input type="hidden" id="mtq_ratingval-2-1" value="0" /><div id="mtq_rating-2-1" class="mtq_preload">Need more practice!</div><input type="hidden" id="mtq_ratingval-3-1" value="40" /><div id="mtq_rating-3-1" class="mtq_preload">Keep trying!</div><input type="hidden" id="mtq_ratingval-4-1" value="60" /><div id="mtq_rating-4-1" class="mtq_preload">Not bad!</div><input type="hidden" id="mtq_ratingval-5-1" value="80" /><div id="mtq_rating-5-1" class="mtq_preload">Good work!</div><input type="hidden" id="mtq_ratingval-6-1" value="100" /><div id="mtq_rating-6-1" class="mtq_preload">Perfect!</div>    <input type="hidden" id="mtq_gf_present-1" value="0" />
    <input type="hidden" id="mtq_cf7_present-1" value="0" />
    <input type="hidden" id="mtq_quiz_in_form-1" value="0" />
    <input type="hidden" id="mtq_gf_formid_number-1" value="" />

  </div>
  
  <!--Variables Div--> 
  <!--/form--> 
</div>
<!--Quiz area div-->




			


<!-- GQBottom -->


		