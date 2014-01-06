function nuOBJECTCopy(i,s){

        this.id                = i;

        this.group           = s.group;                         //-- Group it belongs to.
        this.section         = s.section;                         //-- Header or Footer.
        this.objectType      = s.objectType;
        this.left            = s.left + 3;
        this.top             = s.top;                         //-- relative to the Section it belongs to.
        this.height          = s.height;
        this.width           = s.width;
        this.backgroundColor = s.backgroundColor;
        this.borderColor     = s.borderColor;
        this.borderWidth     = s.borderWidth;
        this.fieldName       = s.fieldName;
        this.fontColor       = s.fontColor;
        this.fontFamily      = s.fontFamily;
        this.fontSize        = s.fontSize;
        this.fontWeight      = s.fontWeight;
        this.format          = s.format;
        this.textAlign       = s.textAlign;
        this.phpCall         = s.phpCall;
        this.zIndex          = s.zIndex + 1;
        this.minRows         = s.minRows;
        this.maxRows         = s.maxRows;
        this.selected        = 0;
        this.toselect        = 1;
        this.name            = i;

}
function nuCopyObjects(){


    for(var  g = 0 ; g < REPORT.groups.length ; g ++){    //-- unselect all Objects
        for(var  s = 0 ; s < REPORT.groups[g].sections.length ; s ++){
            for(var  ob = 0 ; ob < REPORT.groups[g].sections[s].objects.length ; ob ++){
                if(REPORT.groups[g].sections[s].objects[ob].selected == 1){
                    REPORT.groups[g].sections[s].objects[ob].tocopy = 1;
                } else {
                    REPORT.groups[g].sections[s].objects[ob].tocopy = 0;
                }
            }
        }
    }
}



function nuCloneObjects(paste){

    for(var  g = 0 ; g < REPORT.groups.length ; g ++){    //-- unselect all Objects
        for(var  s = 0 ; s < REPORT.groups[g].sections.length ; s ++){
            for(var  ob = 0 ; ob < REPORT.groups[g].sections[s].objects.length ; ob ++){
                if (paste) tocopy = REPORT.groups[g].sections[s].objects[ob].tocopy
                else tocopy = REPORT.groups[g].sections[s].objects[ob].selected;
                if(tocopy == 1){
                
                    var st            = parseInt(REPORT.groups[g].sections[s].top);
                    var m             = parseInt(REPORT.groups[g].sections[s].margins);

                    var i             = nuNextID();
                    var o             = new nuCopyObject(REPORT.groups[g].sections[s].objects[ob]);
                    o.id              = 'object' + i;
                    o.left            = REPORT.groups[g].sections[s].objects[ob].left + 3;
                    o.zIndex          = REPORT.groups[g].sections[s].objects[ob].zIndex + 1;
                    o.minRows         = REPORT.groups[g].sections[s].objects[ob].minRows;
                    o.maxRows         = REPORT.groups[g].sections[s].objects[ob].maxRows;
                    o.selected        = 0;
                    o.toselect        = 1;
                    o.name            = o.id;

                    window.REPORT.groups[g].sections[s].objects.push(o);

                    var d = document.createElement('div');
                    d.setAttribute('id', 'object' + i);
                    d.setAttribute('ondblclick',  'nuShowObjectProperties(this)');
                    d.setAttribute('onMousedown', 'nuHighlightObject(this, event)');
                    d.setAttribute('onmouseup',   'nuMouseUp(this,event)');
                    d.setAttribute('onmousemove', 'nuMouseMove(event)');

                    $('#nuSectionIndex20').after(d);
                    $('#' + d.id).css( 'cursor',  'cell');
                    $('#' + d.id).css( 'overflow',  'hidden');
                    $('#' + d.id).addClass( 'nuReportObject');
                    $('#' + d.id).css( 'position', 'absolute');

                    $('#' + d.id).css( 'z-index', o.zIndex);
                    $('#' + d.id).css( 'height', o.height);
                    $('#' + d.id).css( 'left',  o.left + 30);
                    $('#' + d.id).css( 'top',   o.top + st + m);
                    $('#' + d.id).css( 'width', o.width);
                    $('#' + d.id).css( 'border-style',  'solid');
                    $('#' + d.id).css( 'border-width',  o.borderWidth);
                    $('#' + d.id).css( 'font-family',  o.fontFamily);
                    $('#' + d.id).css( 'font-size',  o.fontSize + 'px');

                    $('#' + d.id).css( 'color',  o.fontColor);
                    $('#' + d.id).css( 'background-color',  o.backgroundColor);
                    $('#' + d.id).css( 'text-align',  o.textAlign);

                    switch(o.fontWeight) {
                        case "I":
                            $('#' + o.id).css('font-style', 'italic');
                            break;
                        case "B":
                            $('#' + o.id).css('font-weight', 'bold');
                            break;
                        default:
                            $('#' + o.id).css('font-style', 'normal');
                            break;
                    }
                    
                    $('#' + d.id).html( $("#"+REPORT.groups[g].sections[s].objects[ob].id).html());

                    $('#' + d.id).draggable({

                        start: function( event, ui ){
                            nuSetOffset(this.id);
                        },

                        drag: function( event, ui ){
                            nuMoveSelected(this.id);
                        },

                        stop: function( event, ui ){
                            nuReadjustSections();                    //-- make Sections Bigger if an Object overlaps it
                            nuMoveAllObjects();
                            nuReopenSelectObjects();
                            nuSaveReport();
                        }

                    });

                    nuReadjustSections();                    //-- make Sections Bigger if an Object overlaps it
                    nuMoveAllObjects();

                }
            }
        }
    }

    for(var  g = 0 ; g < REPORT.groups.length ; g ++){    //-- unselect all Objects
        for(var  s = 0 ; s < REPORT.groups[g].sections.length ; s ++){
            for(var  ob = 0 ; ob < REPORT.groups[g].sections[s].objects.length ; ob ++){
                if(REPORT.groups[g].sections[s].objects[ob].toselect == 1){
                    REPORT.groups[g].sections[s].objects[ob].toselect = 0;
                    REPORT.groups[g].sections[s].objects[ob].selected = 1;
                    $("#"+REPORT.groups[g].sections[s].objects[ob].id).addClass("nuSelected");
                } else {
                    REPORT.groups[g].sections[s].objects[ob].selected = 0;
                    $("#"+REPORT.groups[g].sections[s].objects[ob].id).removeClass("nuSelected");
                }
            }
        }
    }

    nuReopenSelectObjects();
    nuSaveReport();

}


