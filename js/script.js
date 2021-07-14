jQuery(document).ready( function() {

   jQuery(".change").click( function(e) {
      e.preventDefault(); 
      post_id = jQuery(this).attr("data-post_id")
      
      jQuery.ajax({
         type : "post",
         dataType : "json",
         url : myAjax.ajaxurl,
         data : {action: "do_change", post_id : post_id},
           success: function(response) {
            
               jQuery(post).html(response.restrict)
            }
            
         
      })   

   })

})