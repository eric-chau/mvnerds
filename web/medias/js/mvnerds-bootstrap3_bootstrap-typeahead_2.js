!function(a){var b=function(d,c){this.$element=a(d);this.options=a.extend({},a.fn.typeahead.defaults,c);this.matcher=this.options.matcher||this.matcher;this.sorter=this.options.sorter||this.sorter;this.highlighter=this.options.highlighter||this.highlighter;this.updater=this.options.updater||this.updater;this.$menu=a(this.options.menu).appendTo("body");this.source=this.options.source;this.shown=false;this.listen()};b.prototype={constructor:b,select:function(){var c=this.$menu.find(".active").attr("data-value");this.$element.val(this.updater(c)).change();return this.hide()},updater:function(c){return c},show:function(){var c=a.extend({},this.$element.offset(),{height:this.$element[0].offsetHeight});this.$menu.css({top:c.top+c.height,left:c.left});this.$menu.show();this.shown=true;return this},hide:function(){this.$menu.hide();this.shown=false;return this},lookup:function(d){var c;this.query=this.$element.val();if(!this.query||this.query.length<this.options.minLength){return this.shown?this.hide():this}c=a.isFunction(this.source)?this.source(this.query,a.proxy(this.process,this)):this.source;return c?this.process(c):this},process:function(c){var d=this;c=a.grep(c,function(e){return d.matcher(e)});c=this.sorter(c);if(!c.length){return this.shown?this.hide():this}return this.render(c.slice(0,this.options.items)).show()},matcher:function(c){return ~c.toLowerCase().indexOf(this.query.toLowerCase())},sorter:function(e){var f=[],d=[],c=[],g;while(g=e.shift()){if(!g.toLowerCase().indexOf(this.query.toLowerCase())){f.push(g)}else{if(~g.indexOf(this.query)){d.push(g)}else{c.push(g)}}}return f.concat(d,c)},highlighter:function(c){var d=this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g,"\\$&");return c.replace(new RegExp("("+d+")","ig"),function(e,f){return"<strong>"+f+"</strong>"})},render:function(c){var d=this;c=a(c).map(function(e,f){e=a(d.options.item).attr("data-value",f);e.find("a").html(d.highlighter(f));return e[0]});c.first().addClass("active");this.$menu.html(c);return this},next:function(d){var e=this.$menu.find(".active").removeClass("active"),c=e.next();if(!c.length){c=a(this.$menu.find("li")[0])}c.addClass("active")},prev:function(d){var e=this.$menu.find(".active").removeClass("active"),c=e.prev();if(!c.length){c=this.$menu.find("li").last()}c.addClass("active")},listen:function(){this.$element.on("blur",a.proxy(this.blur,this)).on("keypress",a.proxy(this.keypress,this)).on("keyup",a.proxy(this.keyup,this));if(a.browser.chrome||a.browser.webkit||a.browser.msie){this.$element.on("keydown",a.proxy(this.keydown,this))}this.$menu.on("click",a.proxy(this.click,this)).on("mouseenter","li",a.proxy(this.mouseenter,this))},move:function(c){if(!this.shown){return}switch(c.keyCode){case 9:case 13:case 27:c.preventDefault();break;case 38:c.preventDefault();this.prev();break;case 40:c.preventDefault();this.next();break}c.stopPropagation()},keydown:function(c){this.suppressKeyPressRepeat=!~a.inArray(c.keyCode,[40,38,9,13,27]);this.move(c)},keypress:function(c){if(this.suppressKeyPressRepeat){return}this.move(c)},keyup:function(c){switch(c.keyCode){case 40:case 38:break;case 9:case 13:if(!this.shown){return}this.select();break;case 27:if(!this.shown){return}this.hide();break;default:this.lookup()}c.stopPropagation();c.preventDefault()},blur:function(d){var c=this;setTimeout(function(){c.hide()},150)},click:function(c){c.stopPropagation();c.preventDefault();this.select()},mouseenter:function(c){this.$menu.find(".active").removeClass("active");a(c.currentTarget).addClass("active")}};a.fn.typeahead=function(c){return this.each(function(){var f=a(this),e=f.data("typeahead"),d=typeof c=="object"&&c;if(!e){f.data("typeahead",(e=new b(this,d)))}if(typeof c=="string"){e[c]()}})};a.fn.typeahead.defaults={source:[],items:8,menu:'<ul class="typeahead dropdown-menu"></ul>',item:'<li><a href="#"></a></li>',minLength:1};a.fn.typeahead.Constructor=b;a(function(){a("body").on("focus.typeahead.data-api",'[data-provide="typeahead"]',function(d){var c=a(this);if(c.data("typeahead")){return}d.preventDefault();c.typeahead(c.data())})})}(window.jQuery);