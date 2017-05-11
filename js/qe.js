jQuery(document).ready(function($){
    ajaxManager.run();
    
    if( $("#frm_qlc #canvas_teacher").length ) {
        $(document).on("change", '#frm_qlc #canvas_teacher', function(e) { 
            
        });
    }
});

function qlc_get_courses() {
    var $ = jQuery;
    var data = {
                action: 'qlc_get_courses',
                term_id: $("#frm_qlc #canvas_term").val(),
                teacher_id: $("#frm_qlc #canvas_teacher").val()
            }
    ajaxManager.addReq({
                type: 'POST',
                url: ajaxurl,
                data: data,
                success: function(response){

                }
    });
}

var ajaxManager = (function() {
     var requests = [];

     return {
        addReq:  function(opt) {
            requests.push(opt);
        },
        removeReq:  function(opt) {
            if( jQuery.inArray(opt, requests) > -1 )
                requests.splice(jQuery.inArray(opt, requests), 1);
        },
        run: function() {
            var self = this,
                oriSuc;

            if( requests.length ) {
                oriSuc = requests[0].complete;

                requests[0].complete = function() {
                     if( typeof(oriSuc) === 'function' ) oriSuc();
                     requests.shift();
                     self.run.apply(self, []);
                };   

                jQuery.ajax(requests[0]);
            } else {
              self.tid = setTimeout(function() {
                 self.run.apply(self, []);
              }, 1000);
            }
        },
        stop:  function() {
            requests = [];
            clearTimeout(this.tid);
        }
     };
}());