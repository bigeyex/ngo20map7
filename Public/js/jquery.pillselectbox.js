 // the jquery plugin for generating select box
 (function( $ ) {

      $.fn.pillSelectBox = function() {
          this.each(function(){
              var hidden_select = $(this);
              var selectOptions = hidden_select.attr('data-options').split(',');
              var pills = $.map(selectOptions, function(a){
                              return '<li><a href="javascript:void(0)" tval="'+a+'">'+a+'</a></li>';
                          }).join('');
              var trigger_box = $('<div><span class="trigger-box-text"><span class="pill-select-pills-empty"></span></span></div>');
              var dropdown_menu = $('<ul>'+pills+'</ul>');
              var refresh_select = function(){
                  var selected_items = trigger_box.find('.pill-select-pills').map(function(){
                      return $(this).text();
                  });
                  selected_items = $.makeArray(selected_items);
                  hidden_select.val(selected_items.join(','));
              }

              dropdown_menu.appendTo(trigger_box);
              trigger_box.insertAfter(hidden_select).attr('class', hidden_select.attr('class')).addClass('select-pills');

              hidden_select.hide();
              dropdown_menu.hide();
              dropdown_menu.css('position', 'absolute');
              dropdown_menu.css('left',0);
              dropdown_menu.css('top',trigger_box.outerHeight());

              // events
              trigger_box.click(function(e){
                  dropdown_menu.toggle();
                  e.stopPropagation();
              });
              dropdown_menu.click(function(e){
                  e.stopPropagation();
              });
              // set initial values
              var init_values = hidden_select.val().split(',');
              var init_value_dict = {};
              $.each(init_values, function(i, v){
                init_value_dict[v] = true;
              });
              dropdown_menu.find('a').each(function(){
                var text = $(this).text();
                if(text in init_value_dict){  // if the pill should be initially seletced
                    $(this).addClass('selected');
                    var pillDom = $('<span class="pill-select-pills"><i class="pill-close fa fa-times" tval="'+$(this).text()+'"></i>'+$(this).text()+'</span>');
                    trigger_box.find('.trigger-box-text').append(pillDom);
                    this.pillDom = pillDom;
                }
              });
              var lastItem = dropdown_menu.find('a').last().get(0);
              var lastExcluding = trigger_box.hasClass('last-excluding');
              dropdown_menu.find('a').click(function(){
                  function removeItem(item) {
                      if (!item.pillDom) return;
                      item.pillDom.remove();
                      item.pillDom = null;
                      $(item).removeClass('selected');
                  }
                  function removeConfilictItems(selectedItem) {
                      if(selectedItem !== lastItem) {
                          removeItem(lastItem);
                      } else {
                          dropdown_menu.find('a').each(function() {
                              removeItem(this);
                          })
                      }
                  }
                  $(this).toggleClass('selected');
                  if($(this).hasClass('selected')){
                      if(lastExcluding) removeConfilictItems(this);
                    // the item is not selected - select now
                    var pillDom = $('<span class="pill-select-pills"><i class="pill-close fa fa-times" tval="'+$(this).text()+'"></i>'+$(this).text()+'</span>');
                    trigger_box.find('.trigger-box-text').append(pillDom);
                    this.pillDom = pillDom;
                  }
                  else{
                    if(this.pillDom){
                      this.pillDom.remove();
                    }
                    this.pillDom = null;
                  }
                  refresh_select();
              });
              trigger_box.on('click', '.pill-close', function(e){
                var tval = $(this).attr('tval');
                $(this).parent().remove();
                dropdown_menu.find('[tval="'+tval+'"]').removeClass('selected');
                refresh_select();
                e.stopPropagation();
              });
              trigger_box.find('.trigger-box-text').sortable({
                containment: 'parent',
                update: refresh_select
              });
              $(document).click(function(){
                  dropdown_menu.hide();
              });
          });
          
          return this;

      };

  }( jQuery ));