<div class="container jumbotron reader">
	<h3>{{$title}}</h3>
	
	<?php 
      
	if($val=='medium')
	{
    $post = json_decode($post,true);
    $lines = count($post["payload"]["value"]["content"]["bodyModel"]["paragraphs"]);
      for($k=0;$k<$lines;$k++)
      {
                $href="";
                $para = $post["payload"]["value"]["content"]["bodyModel"]["paragraphs"][$k];

                if ($para["type"] == 1) //code present
                {
                    echo "<p>".$para["text"]."</p>";
                    echo "<br>";
                } 
                elseif($para["type"] == 2) //title of the post
                {
                   echo "<h2>" .$para["text"]. "</h2>";
                }
                elseif($para["type"] == 4) //image
                 {
                    $img_link = "https://cdn-images-1.medium.com/max/800/" .$para["metadata"]["id"] ;
                    echo "<img class='center' src=".$img_link."/>";
                }
                elseif ($para["type"] == 3) //heading present
                {   
                    $flag=0;
                    if($para["markups"]  != NULL)
                    {
                        if(isset($para["markups"]["0"]["href"]))
                        $href = $para["markups"]["0"]["href"];
                        else
                        $href="";
                         echo $href;
                        $flag=1;
                    }
                    if($flag==1)
                        echo "<a href=".$href." target='_blank'><h4>Heading Link -" .$para["text"]. "</h4></a>";
                    else
                        echo "<h4>" .$para["text"]. "</h4>";
                }
                elseif ($para["type"] == 8) //code present
                { 
                    echo "<pre>".$para["text"]."</pre>";
                    echo "<br>";
                } 
                elseif ($para["type"] == 13) //light heading
                {
                    echo "<h4>" .$para["text"]. "</h4>";
                    echo "<br>";
                }
                else
                {
                    echo "<p>" . $para["text"]. "</p>";
                }              
         }
     }

   else
    echo $file;


 
    
    ?>
</div>