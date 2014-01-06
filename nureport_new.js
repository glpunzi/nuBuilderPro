    

    function nuNextID(){
        window.ID = window.ID + 1;
        return window.ID;
    }
  
    function nuCreateObject(o, sectionTop){

        var d = document.createElement('div');  
        var g = 0;
        var s = 0;

        d.setAttribute('ondblclick',  'nuShowObjectProperties(this)');
        d.setAttribute('onmousedown', 'nuHighlightObject(this, event)');
        d.setAttribute('onmouseup',   'nuStopResize(this)');
        d.setAttribute('onmousemove', 'nuMouseMove(event)');
 
        
        if(arguments.length == 0){                                              //-- new Object
            
            var i = nuNextID();

            d.setAttribute('id', 'object' + i);
 
            $('#nuSectionIndex20').after(d);
            $('#' + d.id).css( 'position', 'absolute');
            $('#' + d.id).css( 'height', '20px');
            $('#' + d.id).css( 'left',  '100px');
            $('#' + d.id).css( 'top',   '50px');
            $('#' + d.id).css( 'width', '100px');
            $('#' + d.id).css( 'z-index', '100');
            $('#' + d.id).css( 'border-style',  'solid');
            $('#' + d.id).css( 'border-width',  '0px');
            $('#' + d.id).css( 'font-family',  'helvetica');
            $('#' + d.id).css( 'font-size',  '14px');
            $('#' + d.id).css( 'font-weight',  'normal');
            $('#' + d.id).css( 'color',  'black');
            $('#' + d.id).css( 'background-color',  'white');
            $('#' + d.id).css( 'text-align',  'left');
            $('#' + d.id).css( 'cursor',  'cell');
            $('#' + d.id).css( 'overflow',  'hidden');
            $('#' + d.id).addClass( 'nuReportObject');

            var o     = new nuOBJECT('object' + i);
            
        }else{                                                                  //-- load Object

            d.setAttribute('id', o.id);
            window.ID = Math.max(window.ID, Number(o.id.substr(6)) + 1);
            o.top     = nuGetSectionValue(window.GRP[o.group].sections[o.section].sectionID, 'top');
 
            $('#nuSectionIndex20').after(d);
            $('#' + d.id).css( 'position', 'absolute');
            $('#' + d.id).css( 'height', o.height);
            $('#' + d.id).css( 'left',  o.left + 30);
            $('#' + d.id).css( 'top', o.top);
            $('#' + d.id).css( 'width', o.width);
            $('#' + d.id).css( 'z-index', o.zIndex);
            $('#' + d.id).css( 'border-style',  o.borderStyle);
            $('#' + d.id).css( 'border-width',  o.borderWidth);
            $('#' + d.id).css( 'font-family',  o.familyFont);
            $('#' + d.id).css( 'font-size',  o.fontSize);
            $('#' + d.id).css( 'font-weight',  o.fontWeight);
            $('#' + d.id).css( 'color',  o.color);
            $('#' + d.id).css( 'background-color',  o.backgroundColor);
            $('#' + d.id).css( 'text-align',  o.textAlign);
            $('#' + d.id).css( 'cursor',  'cell');
            $('#' + d.id).css( 'overflow',  'hidden');
            $('#' + d.id).addClass( 'nuReportObject');
            
            g = o.group;
            s = o.section;

        }

        window.REPORT.groups[g].sections[s].objects.push(o);
        
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

        if(arguments.length == 0){                                              //-- new Object
            
            nuReadjustSections();                    //-- make Sections Bigger if an Object overlaps it
            nuMoveAllObjects();
            nuReopenSelectObjects();
            nuSaveReport();
            
        }
    }
  

