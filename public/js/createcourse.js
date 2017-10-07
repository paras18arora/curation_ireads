
 var number=2;

    function removetutorial()
    {
       
       var remove= document.getElementById('article'+number);
      
       document.getElementById('course').removeChild(document.getElementById('course').lastChild);
       document.getElementById('course').removeChild(document.getElementById('course').lastChild);
       number--;
    }
    
    function submittutorial(token)
    {
      var totalfiledata=[];
       for(var i=1;i<number;i++){
       var save= document.getElementById('article'+i);
       totalfiledata.push($(save).froalaEditor('html.get'));
       console.log($(save).froalaEditor('html.get'));

     }
     
  
      $.get('/savetutorial', {
          _token: token,
          totalfiledata:totalfiledata,
          title:document.getElementById('title').value,
          description:document.getElementById('description').value,
          tags:$("#tags").tagsinput('items')
        }
        )
        .done(function(data) {
          $('#myModal1').modal('show');

        })

        .fail(function() {
          alert( "error" );
        });
    }