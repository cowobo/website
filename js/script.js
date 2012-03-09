//VARIABLES//
var winx;
var overmenu;
var overscroller;
var overslide;
var scroller;
var overlightbox;
var markersvar;

jQuery(document).mousemove(function(e) {
	winx = jQuery(window).width();
    window.ex = e.clientX;
	window.ey = e.clientY;
});

jQuery(window).resize(function() {
	winx = jQuery(window).width();
});

//setup listeners after document loads
jQuery(document).ready(function() {
	winx = jQuery(window).width();
	setInterval(mousemov, 10);

	//load maps and listners
	initialize();
	cowobo_sidebar_listeners();
	cowobo_jquery_ui_listeners();
	cowobo_lightbox_listeners();
	cowobo_editpost_listeners();
	cowobo_map_listeners();
    cowobo_messenger_listeners();

	//update scrollbars if single post is present
	if(jQuery('.single').length>0){
		update_scrollbars(jQuery('.single').attr('id'));
	}

	//SHARETHIS KEY//
	/* var switchTo5x=true;
	if (typeof(stLight)!= 'undefined') stLight.options({publisher:'4a45176a-73d4-4e42-8be9-c015a589c031'});*/

});

//LISTENER FUNCTIONS//
//SIDEBAR//
function cowobo_sidebar_listeners() {
	//check if the mouse is over a scrollable div
	jQuery('#menubar').hover(function() {overmenu = 1}, function () {overmenu = 0;});
	jQuery('#scroller').hover(function() {overscroller = 1}, function () {overscroller = 0});

	//animate the menu
	jQuery('#menu li').click(function(event) {
		//prevent page from closing
		event.stopPropagation();
		var child = jQuery(this).children('ul');
		var parent = jQuery(this).parent('ul')
		var childwidth = child.get(0).scrollWidth;
		if(child.width()>1) {
			child.animate({width: 1});
			parent.animate({width: parent.width()-childwidth});
		} else {
			child.animate({width: childwidth});
			parent.animate({width: childwidth + parent.width()});
		}
		jQuery(this).siblings('li').children('ul').animate({width : 1});
	});

	//fade back previous maps and otherwise navigate back one page
	jQuery('#backbutton').click(function() {
		if(jQuery('.maplayer').length>1) {
			jQuery('.maplayer:last').fadeOut('slow', function(){jQuery(this).remove()});
			jQuery('.large').fadeOut('slow');
		} else if(document.referrer) {
			window.open(document.referrer,'_self');
		} else {
			history.go(-1);
		}
		return false;
	});

	//add horizontal scroll with mousewheel (requires horscroll.js)
	jQuery("#scroller").mousewheel(function(event, delta) {
		jQuery(this).scrollLeft(jQuery(this).scrollLeft()+delta * -30);
		event.preventDefault();
		removeMarkers();
	});

	//toggle display of menubar
	jQuery('#menubar').click(function() {
		jQuery('.large').fadeOut();
		jQuery('#scroller').slideToggle();
  	});

	// listerners for thumbs in sidebar
	jQuery('.medium').click(function(event) {
		var postid = jQuery(this).attr('id').split('-')[1];
		var coordinates = jQuery(this).find('.markerdata').val();
		jQuery('#scroller').slideToggle();
		jQuery('#'+postid).fadeIn();
		update_scrollbars(postid);
		loadlightbox(postid, postid);

		//load new map if marker has coordinates
		if(typeof(coordinates) != 'undefined' && coordinates.length>0) {
			var markerpos = coordinates.split(',');
			if(typeof(jQuery('.maplayer:last')).data('map') !='undefined'){
				var type = jQuery('.maplayer:last').data('map').type
				loadNewMap(markerpos[0], markerpos[1], 15, jQuery(this).find('.markerdata'), type);
			}
		}

		// Load awesome-count
		jQuery('#'+postid).find('.like').html( jQuery('#like_small' + postid ).html() );

	});

	jQuery('.medium').hover(
		function() {jQuery(this).animate({opacity: 1},'fast');},
		function() {jQuery(this).animate({opacity: 0.7},'fast');}
	);

	jQuery('.newprofile').click(function(){

	})
}

//jQuery UI
function cowobo_jquery_ui_listeners() {
	jQuery('.large').draggable({cancel:'.content'});
	jQuery('#cloud1, #cloud2,').draggable();
	jQuery("#scroller, #map, #cloud1, #cloud2, .slider").disableSelection();
	jQuery("#scroller").sortable();

}

//LIGHTBOX//
function cowobo_lightbox_listeners() {
	//fadeout lightboxes when clicked outside holder
	jQuery('#map, .shadowclick, #cloud1, #cloud2').live('click', function() {
		jQuery('.large').fadeOut();
  	});

	//switch between slides in gallery
	jQuery('.next, .prev').live('click', function() {
		var gallery = jQuery(this).parents('.gallery');
		var first = gallery.children('.slide').first();
		var last = gallery.children('.slide').last();
		var direction = jQuery(this).attr('class');
		if (direction == 'next') last.fadeOut('slow', function(){last.insertBefore(first).show();});
		else first.hide().insertAfter(last).fadeIn('slow');
		return false;
	});

	//add streetview to gallery
	jQuery('.streetview').live('click', function() {
		var panoid = jQuery(this).attr('id');
		var baseurl = 'http://cbk0.google.com/cbk?output=tile&panoid='+panoid+'&zoom=1';
		var newlayer = jQuery('<div class="slide"><div class="streetholder"></div></div>');
		for (var x = 0; x <= 1; x++) {
			var url = baseurl + '&x=' + x + '&y=0';
			newlayer.children().append('<img src="'+url+'" alt="" class="streettiles" width="50%">');
		}
		jQuery(this).parent().append(newlayer);
		return false; //to prevent default action
	});

	//todo: vertical scrollwheel on lightbox
	//jQuery('.holder').bind('mousewheel');

	//intercept submit of comment form
	jQuery(".commentform").live("submit", function() {
		var newform = jQuery(this);
		var commentlist = newform.parents('.commentbox');
		var message = jQuery('<span class="errormessage"></span>').appendTo(newform);
		jQuery.ajax({
        		beforeSend:function(msg){
            		msg.setRequestHeader("If-Modified-Since","0");
					newform.find('.loading').show();
         		},
         		type:'post',
         		url:jQuery(this).attr('action'),
         		data:jQuery(this).serialize(),
         		dataType:'html',
         		error:function(msg){
             		if(msg.status==500)
			 			message.empty().append(msg.responseText.split('<p>')[1].split('</p>')[0]);
             		else if(msg.status=='timeout')
               			message.empty().append(('Error: Server time out, try again!'));
             		else
               			message.empty().append(('Please slow down, you are posting to fast!'));
         		},
         		success:function(data){
            		var newComList = jQuery(data).find('.commentbox');
					commentlist.replaceWith(newComList);
					update_scrollbars(commentlist.parents('.large').attr('id'));
         		}
       		});
      	return false;
	});

	//resize text area to fit content (requires autoresize.js)
   	jQuery(".commenttext").autoResize({
    	onResize : function() {jQuery(this).css({opacity:0.8});},
    	animateCallback : function() {jQuery(this).css({opacity:1});},
    	animateDuration : 300,
    	extraSpace : 20
	});

}

//MESSENGER//
function cowobo_messenger_listeners() {
    jQuery('.messenger').click( function() {
       var type = jQuery(this).attr('class').split(' ')[1];
       if ( type == 'new_profile') {
           jQuery('.speechbubble').fadeIn('slow');
       } else if ( type == 'create_new_profile') {
           alert ('Create new profile!');
       } else if ( type == 'edit_profile') {
           alert ('Edit profile!')
       }
    });
}

function cowobo_map_listeners() {
	jQuery('.showlabels').click(function(){
		var zoom = jQuery('.maplayer:last').data('map').zoom;
		var lat = jQuery('.maplayer:last').data('map').lat;
		var lng = jQuery('.maplayer:last').data('map').lng;
		var type = jQuery('.maplayer:last').data('map').type
		if(type=='satellite') type = 'hybrid'; else type = 'satellite';
		loadNewMap(lat, lng, zoom, markersvar, type);
	});

	jQuery('.zoomin').click(function(){
		var zoom = jQuery('.maplayer:last').data('map').zoom;
		if(zoom<18) {
			jQuery('.maplayer:last').animate({width:'200%', height:'200%', marginLeft:'-50%', marginTop:'-50%' });
			var lat = jQuery('.maplayer:last').data('map').lat;
			var lng = jQuery('.maplayer:last').data('map').lng;
			var type = jQuery('.maplayer:last').data('map').type
			loadNewMap(lat, lng, zoom+1, markersvar, type);
		}
	});

	jQuery('.zoomout').click(function(){
		var zoom = jQuery('.maplayer:last').data('map').zoom;
		if(zoom>2) {
			var lat = jQuery('.maplayer:last').data('map').lat;
			var lng = jQuery('.maplayer:last').data('map').lng;
			var type = jQuery('.maplayer:last').data('map').type
			loadNewMap(lat, lng, zoom-1, markersvar, type);
		}
	});

	jQuery('#savemarker').click(function(){
		var lat = jQuery('.maplayer:last').data('map').lat;
		var lng = jQuery('.maplayer:last').data('map').lng;
		jQuery('#savemarker').html(lat+','+lng);
	});

	jQuery('#closebutton1, #closebutton2').click(function(){
		jQuery(this).parent().fadeOut();
	});

}

//CONTRIBUTE//
function cowobo_editpost_listeners() {
	jQuery('.relocate').live('click', function() {
		jQuery('#editmarker').data('postid', jQuery(this).parents('.large').attr('id'));
		jQuery('.large, .marker').fadeOut();
		jQuery('#editmarker').show();
  	});

	jQuery('.savelocation').click(function() {
		if(typeof(jQuery('.maplayer:last')).data('map') !='undefined'){
			var id = jQuery(this).parents('#editmarker').data('postid');
			var lat = jQuery('.maplayer:last').data('map').lat;
			var lng = jQuery('.maplayer:last').data('map').lng;
			var newlatlng = lat+','+lng;
			jQuery('#'+id+', .marker').fadeIn();
			jQuery('#'+id+' .coordinates').html('<li id="'+newlatlng+'">'+newlatlng+'</li><span> (x)</span>');
			jQuery('#editmarker').hide();
		} else {
			alert('Please wait for map to finish loading');
		}
  	});

	jQuery('.cancellocation').click(function() {
		var id = jQuery(this).parents('#editmarker').data('postid');
		jQuery('#'+id+', .marker').fadeIn();
		jQuery('#editmarker').hide();
  	});

	jQuery('.showall').live('click', function() {
		var listbox = jQuery(this).siblings('.listbox');
		var postid = jQuery(this).parents('.large').attr('id');
		if(listbox.height()>125){
			listbox.animate({height: 125});
			jQuery(this).html('Show All &darr;');
		} else {
			listbox.animate({height:listbox.get(0).scrollHeight});
			jQuery(this).html('Hide &darr;');
		}
		update_scrollbars(postid);
  	});

	jQuery('.edit').live('click', function() {
		var selectbox = jQuery(this).siblings('.selectbox').eq(0);
		if(selectbox.is(":visible")){
			jQuery(this).html('+ Add');
		} else {
			jQuery(this).html('- Hide');
		}
		selectbox.slideToggle();
  	});

	//move commentbox back to top
	jQuery('.add').live('click', function() {
		var replylink = jQuery(this);
		var post = replylink.parents('.large');
		var postid = post.attr('id');
		post.find('.commentbox .replybox').slideUp(function(){jQuery(this).insertAfter(replylink).slideDown()});;
		post.find('.comment_parent').val(0);
		post.find('.comment_post_ID').val(postid);
		update_scrollbars(postid);
  	});

	//move commentform to the correct place and add the comment number
	jQuery('.reply').live('click', function(){
		var comment = jQuery(this).parents('.comments');
		var commid = comment.attr('id').split('-')[1];
		var post = comment.parents('.large');
		post.find('.commentbox .replybox').slideUp(function(){jQuery(this).insertAfter(comment).slideDown()});
		post.find('.commentbox .listbox').removeClass('restrict');
		post.find('.comment_parent').val(commid);
		update_scrollbars(post.attr('id'));
	});

	jQuery('.remove').live('click', function() {
		var listbox = jQuery(this).parents('.container').children('.listbox');
		jQuery(this).parents('.listitem').remove();
		listbox.css('height', 'auto');
	});

	jQuery('.verlist li').live('click', function() {
		var id = jQuery(this).attr('class').split(' ')[0];
		var listbox = jQuery(this).parents('.container').children('.listbox');
		if(listbox.children('.'+id).length>0 ){
			alert('You have already added this feed or post');
		} else {
			var listitem = jQuery('<div class="'+ id +' listitem">'
			+ '<div class="thumbnail"></div><div class="text">'
			+ jQuery(this).html()
			+ '<span class="remove button"> (x)</span><br/>Updated just now</div></div>');
			listitem.prependTo(listbox);
			listbox.css('height', 'auto');
		};
	});

	jQuery('.typelist li').live('click', function() {
		var id = jQuery(this).attr('class').split(' ')[0];
		var container = jQuery(this).parents('.container');
		jQuery(this).addClass('selected').siblings().removeClass('selected');
		//empty primary feed list if choosing new feed
		if(jQuery(this).parents('.feeds').length > 0)
			container.children('.listbox').empty();
		jQuery('.selectbox .slide').fadeOut();
		container.find('.cat'+id).fadeIn();
	});

	jQuery('.nextpost, .lastpost').live('click', function() {
		var parent = jQuery(this).parents('.large');
		var postid = parent.attr('id');
		var newid = jQuery(this).attr('id').split('-')[1];
		parent.find('.content').fadeTo('slow', 0.5);
		loadlightbox(newid, postid);
		//var type = jQuery('.maplayer:last').data('map').type
		//loadNewMap(lat, lng, zoom, markers, type);
	});

	jQuery('.save').live('click', function() {
		var posts = new Array(); var feeds = new Array();
		var post = jQuery(this).parents('.large');
		var postid = post.attr('id');
		var newlatlng = post.find('.coordinates li').attr('id');
		post.find('.container').each(function(){
			var cat = cat + 1;
			if (jQuery(this).hasClass('feeds')) {
				jQuery(this).find('.listitem').each(function(){
					feeds.push(jQuery(this).attr('class').split(' ')[0]);
				});
			} else {
				jQuery(this).find('.listitem').each(function(){
					posts.push(jQuery(this).attr('class').split(' ')[0]);
				});
			}
		});
		//make sure the post has a feed and author
		if (feeds.length<0) {
			alert('You must specify atleast one feed');
		} else {
			jQuery.ajax({
				type: "POST",
				url: 'wp-admin/admin-ajax.php',
				data: {action: 'savechanges',
					postid: postid,
					feeds: feeds.join(','),
					posts: posts.join(','),
					coordinates: newlatlng},
				success: function (permalink){
					alert('Your changes have been saved');
					location.href='';
				}
			});
		}
  	});

	jQuery('.delete').live('click', function() {
		var deleteid = jQuery(this).parents('.large').attr('id');
		if(confirm('Are you sure you want to delete this post?'))
			location.href='?deleteid='+deleteid;
  	});

	//open image uploader when clicking on editable images
	jQuery('.editable').live('click', function() {
 		var postid = jQuery(this).parents('.large').attr('id');
		formfield = jQuery('#upload_image').attr('name');
 		tb_show('', 'wp-admin/media-upload.php?post_id='+postid+'&TB_iframe=true');
 		return false;
	});

 	//reload the image gallery after closing media uploader
	window.original_tb_remove = window.tb_remove;
	window.tb_remove = function() {
		var id = jQuery('.large:visible').attr('id');
		jQuery.ajax({
   			type: "POST",
   			url: 'wp-admin/admin-ajax.php',
   			data: {action: 'loadgallery', postid:id},
   			success: function(msg){
				var gallery = jQuery('#'+id).find('.editable');
				gallery.children('.slide').remove();
				gallery.append(jQuery(msg));
			}
		});
		window.original_tb_remove();
	}
}

//FUNCTIONS//

function loadlightbox(postid , loadid) {
	var cat = jQuery('#page h1').attr('id');
	update_scrollbars(postid);
	//load lightbox contents
	jQuery.ajax({
   		type: "POST",
   		url: 'wp-admin/admin-ajax.php',
   		data: {action: 'loadlightbox', currentcat:cat, postid:postid},
   		success: function(msg){
	    	jQuery('#' + loadid).html(jQuery(msg).html());
			cowobo_jquery_ui_listeners();
			update_scrollbars(loadid);
			loadlike(postid);
			if(typeof(FrontEndEditor) != 'undefined' && jQuery('#'+loadid+' .editable').length > 0) {
				jQuery('#' + loadid + ' .fee-field').each(FrontEndEditor.make_editable);
			}
			if(postid=='new'){
				var id = jQuery('#new .save').attr('id').split('-')[1];
				jQuery('#new').attr('id', id);
			}
		}
	});
}

function loadlike(postid) {
	// Load social share box
	jQuery.ajax({
		type: "POST",
		url: 'wp-content/themes/cowobo/lib/ajax-show-share.php',
		data: {postid:postid},
		success: function ( msg ){
			jQuery('#' + postid).find('.cowobo_social_share').html( msg ).hide();
			// Load Social Buttons
			gapi.plusone.go();
			twttr.widgets.load();
		}
	});

	// Listen for click to expand like interface
	jQuery('.cowobo_social_like').click(function(ev) {
		jQuery('#' + postid).find('.cowobo_social_share').slideToggle();
		ev.preventDefault();
	});
}

//update scrollbars after new content has been added
function update_scrollbars(postid) {
	var mousepos; var startpos; var scrollit;
	var content = jQuery('#'+postid).find('.content');
	var slider = content.siblings('.scrolltrack').children('.slider');
	var contentdim = content.get(0).scrollHeight;
	var sliderdim = content.height() * (content.height()/contentdim);
	slider.css({height: sliderdim});

	content.find('.gallery').hover(
		function() {overslide = 1},
		function () {overslide = 0}
	);

	content.hover(
		function() {var t = setTimeout(function() {overlightbox=true;}, 200);
    		jQuery(this).data('timeout', t);
			scroller = jQuery(this);
  		},
		function() {clearTimeout(jQuery(this).data('timeout'));
			overlightbox=false;
  		}
	);

	jQuery('#'+postid).mousemove(function(e){
		mousepos = e.pageY - jQuery(this).offset().top;
		if(scrollit){
			var sliderpos = mousepos - startpos;
			var gap = content.height()-sliderdim;
			if(sliderpos <= 0) sliderpos = 0;
			if(sliderpos >= gap) sliderpos = gap;
			var scrollerpos = (contentdim - content.height()) * (sliderpos / gap);
			scroller.scrollTop(scrollerpos);
			slider.css('top', sliderpos + "px");
		}
	})

	jQuery('body').mouseup(function(){scrollit = false;});
	slider.mousedown(function(event){scrollit = true;
		event.stopPropagation();
		startpos = mousepos - jQuery(this).position().top;
	});

	//bind mousewheel to new content
	jQuery(".content").mousewheel(function(event, delta) {
		jQuery(this).scrollTop(jQuery(this).scrollTop()+(delta * -30));
	});

}

// scroll divs depending on position of mouse
function mousemov() {
	var maxspeed = 5;
	//scroll imags in gallery
	if (overslide>0 && jQuery('.slide:visible').length>0 ) {
		var slide = jQuery('.slide:visible').last();
		var mousex = window.ex - slide.offset().left;
		if(mousex < slide.width()/4) {
			var speed = (slide.width()/4)/mousex;
			if (speed > maxspeed) speed = maxspeed;
			slide.scrollLeft(slide.scrollLeft()- speed);
		} else if(mousex > (slide.width()-slide.width()/4)) {
			var speed = (slide.width()/4)/(slide.width()-mousex);
			if (speed > maxspeed) speed = maxspeed;
			slide.scrollLeft(slide.scrollLeft()+ speed);
		}
	}
	var maxspeed = 10;
	//scroll content of lightbox
	if(overlightbox) {
		var mousey = window.ey - scroller.offset().top;
		var contentdim = scroller.get(0).scrollHeight;
		var scrolldim = scroller.siblings('.scrolltrack').height();
		var scrollratio = scroller.scrollTop() / (contentdim-scrolldim);
		var slider = scroller.siblings('.scrolltrack').children('.slider');
		var sliderpos = (scrolldim-slider.height()) * scrollratio;
		if(mousey < scroller.height()/4) {
			var speed = (scroller.height()/4)/mousey;
			if (speed > maxspeed) speed = maxspeed;
			scroller.scrollTop(scroller.scrollTop()- speed);
		} else if(mousey > (scroller.height()-scroller.height()/4)) {
			var speed = (scroller.height()/4)/(scroller.height()-mousey);
			if (speed > maxspeed) speed = maxspeed;
			scroller.scrollTop(scroller.scrollTop()+ speed);
		}
		slider.css('top', sliderpos + "px");
		return;
	}
	else if(overmenu>0) var scbar = jQuery('#menubar');
	else if(overscroller>0) var scbar = jQuery('#scroller');
	else return;

	//horizontal scrolling
	if(window.ex <  winx/3) {
		var speed = (winx/3)/window.ex;
		if (speed > maxspeed) speed = maxspeed;
		scbar.scrollLeft(scbar.scrollLeft()-speed);
		removeMarkers();
	}
	else if(window.ex > winx-winx/3) {
		var speed = (winx/3)/(winx-window.ex)
		if (speed > maxspeed) speed = maxspeed;
		scbar.scrollLeft(scbar.scrollLeft()+speed);
		removeMarkers();
	}
}

var number = 0;
// hide markers on map corresponding to timeline
function removeMarkers() {
	var newnumber = Math.floor(jQuery('#scroller').scrollLeft()/(jQuery('.medium').eq(1).width()+10));
	if(newnumber != number) {number = newnumber;
		jQuery('.medium').each(function(){
		if(jQuery(this).index() <= number) {
			var id = jQuery(this).attr('id');
			jQuery('#marker'+id).fadeOut();
		} else {
			var id = jQuery(this).attr('id');
			jQuery('#marker'+id).fadeIn();
		}
		})
	}
}

// Personal RSS feed Ajax-calls
function add_to_feed(feed_type,feed_id,user_id) {
	jQuery.ajax({
		type: "POST",
		url: 'wp-content/themes/cowobo/lib/ajax-feed-setter.php',
		data: {feed_type:feed_type,feed_id:feed_id,user_id:user_id,add:true},
		success: function(msg){
			angel_talk("This feed is now part of your personal feed.");
		}
	});
}

function reset_feed(user_id) {
	jQuery.ajax({
		type: "POST",
		url: 'wp-content/themes/cowobo/lib/ajax-feed-setter.php',
		data: {user_id:user_id,reset:true},
		success: function(msg){
			angel_talk("Your feed has been succesfully reset.");
		}
	});
}

// Make the angel talk
function angel_talk(msg) {
	if (jQuery('.speechbubble').css('display') == 'block')
		jQuery('.speechbubble').fadeOut(function() {angel_talk(msg); return;});
	else 	{ jQuery('#speech').html(msg);	jQuery('.speechbubble').fadeIn(); }
}


//MAP FUNCTIONS
function initialize() {
	//load map centered on africa (this should be replaced with bounds of markers)
	loadNewMap(0.49860809171295, 10.932544165625036, 3, jQuery('.markerdata'), 'satellite');
}

// map to pixels conversion functions
var offset = 268435456; // center of google map in pixels at max zoom level
function LonToX(lon) {
	return Math.round(offset + (offset / Math.PI) * lon * Math.PI / 180);
}
function LatToY(lat) {
	return Math.round(offset - (offset / Math.PI) * Math.log((1 + Math.sin(lat * Math.PI / 180)) / (1 - Math.sin(lat * Math.PI / 180))) / 2);
}
function XToLon(x) {
	return ((Math.round(x) - offset) / (offset / Math.PI)) * 180/ Math.PI;
}
function YToLat(y) {
	return (Math.PI / 2 - 2 * Math.atan(Math.exp((Math.round(y) - offset) / (offset/ Math.PI)))) * 180 / Math.PI;
}
function adjustLonByPx(lon, amount, zoom) {
	return XToLon(LonToX(lon) + (amount << (21 - zoom)));
}
function adjustLatByPx(lat, amount, zoom) {
	return YToLat(LatToY(lat) + (amount << (21 - zoom)));
}

function loadNewMap(lat, lng, zoom, markers, type){
	//update global variables
	markersvar = markers;
	//setup static map image urls
	var xmid = screen.width/2;
	var ymid = screen.height/2;
	if(xmid>640) xmid = 640;
	if(ymid>640) ymid = 640;
	var map = jQuery('#map');
	var size = xmid + 'x'+ ymid;
	var mapurl = 'http://maps.googleapis.com/maps/api/staticmap?maptype='+type+'&sensor=false&size='+size;
	var bufferurl = mapurl+'&format=jpg'+'&zoom='+(zoom-1)+'&center='+lat+','+lng;
	var baseurl = mapurl+'&format=jpg-baseline'+'&zoom='+zoom+'&center=';
	var newlayer = jQuery('<div class="maplayer"><img class="buffer" src="'+bufferurl+'" alt="" width="100%" height="100%"></div>');

	map.append(newlayer);
	//add high res tiles when buffer has faded in
	jQuery('.maplayer:last .buffer').load(function(){
		jQuery('.maplayer:last').fadeIn(function() {
			jQuery(this).data('map', {zoom:zoom, lat:lat, lng:lng, type:type});
			if(jQuery('.maplayer').length>1)
				jQuery(this).prev().css({width:'100%', height:'100%', margin:0});
		});
		for (var y=-1; y<=1; y+=2) {
			for (var x=-1; x<=1; x+=2) {
				var url = baseurl + adjustLatByPx(lat, ymid/2*y, zoom) + ',' + adjustLonByPx(lng, xmid/2*x, zoom);
				newlayer.append('<img src="'+url+'" alt="" class="maptiles">');
			}
		}
	});

	//zoom new layer on click if there are no lightboxes visible
	newlayer.click(function(e){
		if(jQuery('.large :visible').length <1) {
			var mousex = Math.round(e.clientX/jQuery('.maptiles').width()*xmid)-xmid;
			var mousey = Math.round(e.clientY/jQuery('.maptiles').height()*ymid)-ymid;
			var newlng = adjustLonByPx(lng, mousex, zoom);
			var newlat = adjustLatByPx(lat, mousey, zoom);
			if(zoom<18) {
				var midx = jQuery(window).width()/2;
				var midy = jQuery(window).height()/2;
				//only smooth zoom if map does not go into page
				if(e.clientX > midx/2 && e.clientX < midx*3/2 && e.clientY > midy/2 && e.clientY < midy*3/2) {
					var leftmargin = -(e.clientX*2) + midx + 'px';
					var topmargin =  -(e.clientY*2)+ midy + 'px';
					jQuery('.maplayer:last').animate({width:'200%', height:'200%', marginLeft: leftmargin, marginTop: topmargin });
				}
				loadNewMap(newlat, newlng, zoom+1, markers, type);
			}
		}
	});

	//sort markers by latitude to ensure correct overlapping
	var markerlist = jQuery('<div></div>');
	markers.sort(function(a,b){
		var posa = a.value.split(',');
		var posb = b.value.split(',');
    	return  posb[0] - posa[0];
	}).clone().appendTo(markerlist);

	//append markers to map
	markerlist.children('.markerdata').each(function(){
		var markerpos = jQuery(this).val().split(',');
		var markerthumb = jQuery(this).attr('name');
		var markerid = jQuery(this).attr('id').split('-')[1];
		var markertitle = jQuery(this).attr('title');
		var markerimg = jQuery('.markerimg').val();
		var marker = jQuery('<div class="marker" id="marker'+markerid+'"><div class="mcontent"><div class="mtitle"><span>'+markertitle+'</span></div><img src="'+markerthumb+'" alt=""/></div><img src="'+markerimg+'" alt=""/></div>');
  		var delta_x  = (LonToX(markerpos[1]) - LonToX(lng)) >> (21 - zoom);
		var delta_y  = (LatToY(markerpos[0]) - LatToY(lat)) >> (21 - zoom);
   		var marker_x = ((xmid + delta_x)/(xmid*2)*100)+'%';
   		var marker_y = ((ymid + delta_y)/(ymid*2)*100)+'%';
		marker.css({top:marker_y, left: marker_x});
		marker.appendTo(jQuery('.maplayer:last'));
		marker.click(function(event){
			event.stopPropagation();
			jQuery('#scroller').slideUp();
			jQuery('#'+markerid).fadeIn();
			loadlightbox(markerid, markerid);
			loadNewMap(markerpos[0], markerpos[1], 15, markers, type);
		});
		marker.hover(
			function() {jQuery(this).animate({opacity: 1},'fast'); jQuery(this).css('z-index', 3);},
			function() {jQuery(this).animate({opacity: 0.7},'fast'); jQuery(this).css('z-index', 2);}
		);
	});
}

// ADD MAXCHARS ATTR //
jQuery('.SaveFEE').live('click',function(ev) {
	if ( jQuery('.GENTICS_editable').length == 0 ) return false;
	var maxchars = jQuery('.GENTICS_editable').parent().attr('data-maxchars');

	// No maximum set
	if ( maxchars === undefined ) return true;

	var curlength = jQuery('.GENTICS_editable').text().length;
	if ( curlength > maxchars ) {
		alert ( "This field can't be longer than " + maxchars + " characters. It is now " + curlength + ". Please shorten it accordingly.");
		ev.StopPropagation();
		return false;
	}
	else return true;
});

// Listen to all ventures into edit mode
jQuery('.fee-hover-edit').live('click', function() {
	// Aloha!
	jQuery('.SaveFEE').bind('click',function(ev) {
		if ( jQuery('.GENTICS_editable').length == 0 ) return false;
		var maxchars = jQuery('.GENTICS_editable').parent().attr('data-maxchars');

		// No maximum set
		if ( maxchars === undefined ) return true;

		var curlength = jQuery('.GENTICS_editable').text().length;
		if ( curlength > maxchars ) {
			alert ( "This field can't be longer than " + maxchars + " characters. It is now " + curlength + ". Please shorten it accordingly.");
			ev.stopPropagation();
			return false;
		}
		else return true;
	});
	// todo: Ol' fashion edits
	jQuery('.fee-form-save').bind('click',function(ev) {
		if ( jQuery('.fee-form').length == 0 ) return false;
		var maxchars = jQuery('.fee-form').prev().attr('data-maxchars');

		// No maximum set
		if ( maxchars === undefined ) return true;

		var curlength = jQuery('.fee-form-content').val().length;
		if ( curlength > maxchars ) {
			alert ( "This field can't be longer than " + maxchars + " characters. It is now " + curlength + ". Please shorten it accordingly.");
			ev.stopPropagation();
			return false;
		}
		else return true;
	});
});