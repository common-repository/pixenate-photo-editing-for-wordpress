function exit_bubble_mode()
{
    PXN8.listener.remove(PXN8.ON_SELECTION_CHANGE,disable_selection);
    jQuery("#bubble_div").hide("slow");
    jQuery("#toolbar").show("slow");
}

function disable_selection()
{
    var sel = PXN8.getSelection();
    if (sel.width > 0){
        PXN8.unselect();
    }
}

function bubble_mode()
{
    jQuery("#toolbar").hide();
    jQuery("#bubble_div").show("slow");

    PXN8.listener.add(PXN8.ON_SELECTION_CHANGE,disable_selection);

    jQuery("ul.horizontal img").draggable({
            helper: 'clone',
                revert: true,
                containment: '#pxn8_canvas',
                stop: create_bubble
                });

    try {
        document.execCommand('BackgroundImageCache', false, true);
    } catch(e) {}
}
// ========================================================================

function add_bubbles(){
    var bubbles = jQuery("div.bp");
	 var cb = PXN8.dom.eb("pxn8_canvas");
	 var ops = [];
	 for (var i = 0;i < bubbles.length; i++){
	     var bubble = bubbles[i];
        var id = bubble.id;
		  var img = jQuery("#" + id + " img.bubble")[0];
		  var ib = PXN8.dom.eb(img);
		  var canvasX = ib.x - cb.x;
		  var canvasY = ib.y - cb.y;
		  var overlayOp = {operation: "overlay", url: img.src, top: canvasY, left: canvasX, width: img.width, height: img.height, opacity: 70};

		  ops.push(overlayOp);

        var ta = jQuery("#" + id + " textarea")[0];

        var textOp = {operation: "add_text",
                      gravity: "Center",
                      text: ta.value,
                      font: "Arial"
        };

        var bubbleCenterX = canvasX + (img.width / 2);
        var bubbleCenterY = canvasY + (img.height / 2);

        var photoCenterX = cb.width / 2;
        var photoCenterY = cb.height / 2;

        var offsetX = bubbleCenterX - photoCenterX;
        var offsetY = bubbleCenterY - photoCenterY;

        textOp.x = offsetX;
        textOp.y = offsetY;

        ops.push(textOp);

    }
    jQuery("div.bp").remove();

	 PXN8.tools.updateImage(ops);

}
// ========================================================================
gBubbleCounter = 0;

// ========================================================================
function Bubble(img,pos){

    this.id = gBubbleCounter++;

    var divHTML = "<div class='bp' id='bp_" + this.id + "'>";
	 divHTML += "<img class='bubble' src='" + img.src + "' width='" +
        img.width + "' height='" + img.height + "'/>";
    divHTML += "<textarea wrap='off'>Type text here</textarea></div>";

	 var el = jQuery(divHTML).appendTo('body');

    {
        var firstClick = true;
        var ta = jQuery("#bp_" + this.id + " textarea");
        /*
        setTimeout(function(){

                ta.click(function(e){
                        if (firstClick){
                            ta[0].value = "";
                            firstClick = false;
                        }
                    });
            },100);

        */
    }

    this.element = el[0];

    var self = this;

    var delHtml = "<a class='delete' href='javascript:void(0);'><img src='delete.png'/></a>";
    var del = jQuery(delHtml).appendTo(el);
    del.click(function(){document.body.removeChild(self.element);});


    el.draggable({
            containment: 'parent',
                dragPrevention: 'textarea',
                stop: function(){
                on_canvas( self.element,
                           null,
                           function(){ document.body.removeChild(self.element); }
                           )
                    }
        }
        );


    el.resizable({
            proportionallyResize: [jQuery("#bp_" + self.id + " img.bubble")],
                autohide: true,
                stop: function(e,ui)
            {
                var b = PXN8.dom.eb(jQuery("#bp_" + self.id + " img.bubble")[0]);
                b.left = b.x;
                b.top = b.y;
                self.setBounds(b);
            }
        });

    el.hover(
             function(){
                 jQuery("#bp_" + self.id + " textarea").addClass("bordered");
                 jQuery("#bp_" + self.id + " a.delete").addClass("visible");

             },
             function(){
                 jQuery("#bp_" + self.id + " textarea").removeClass("bordered");
                 jQuery("#bp_" + self.id + " a.delete").removeClass("visible");

             });

	 this.setBounds({top: pos.top, left: pos.left, width: img.width, height: img.height});
}

// ========================================================================
Bubble.prototype.setBounds = function(bounds){
    var cb = PXN8.dom.eb("pxn8_canvas");

    var el = jQuery('#bp_' + this.id);

    el.css('left',bounds.left + "px");
    el.css('top',bounds.top + "px");

    var ta = jQuery('#bp_' + this.id + ' textarea');
    ta.css('left',(bounds.width / 6) + 'px');
    ta.css('top',(bounds.height / 20 * 3) + 'px');
    ta.css('width',(bounds.width / 3 * 2) + 'px');
    ta.css('height',(bounds.height / 10 * 7) + 'px');
}

// ========================================================================

function on_canvas(el,fOn,fOff)
{
    var cb = PXN8.dom.eb("pxn8_canvas");

    var eb = PXN8.dom.eb(el);

    var on = eb.x > cb.x &&
        eb.y > cb.y &&
        eb.x < (cb.x + cb.width) &&
        eb.y < (cb.y + cb.height);

    if (on){
        if (fOn){
            fOn();
        }
    }else{
        if (fOff){
            fOff();
        }
    }
}

function create_bubble(e,ui)
{
    var cb = PXN8.dom.eb("pxn8_canvas");
    if (ui.position.left > cb.x &&
        ui.position.top > cb.y &&
        ui.position.left < (cb.x + cb.width) &&
        ui.position.top < (cb.y + cb.height))
    {
        var b = new Bubble(ui.helper,ui.position);
    }
}

