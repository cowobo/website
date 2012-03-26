//VARIABLES//
var winx;
var overmenu;
var overscroller;
var overslide;
var scroller;
var overlightbox;
var markersvar;
var rooturl;
var oldhash;
var geocoder;
var xmid = 640;
var ymid = 320;

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
	//setup basic parameters
	rooturl = jQuery('meta[name=rooturl]').attr("content");
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
	
	//setup geocoder if script has been loaded
	if(typeof(google)!="undefined"){
		geocoder =  new google.maps.Geocoder();
	} 
	
	//check if there is a hash onload
	if(window.location.hash != '') {
		var newhash = window.location.hash;
		var postid = newhash.split('#')[1];
		loadlightbox(postid, 0);
		oldhash = newhash;
	}
	
	//load lightbox when hash changes
	jQuery(window).hashchange(function(){
		var newhash = window.location.hash;
		var newid = newhash.split('#')[1];
		if(typeof(oldhash)!='undefined') {
			var oldid = oldhash.split('#')[1];
			jQuery('#'+oldid).fadeOut();
			jQuery('.map'+oldid).fadeOut();
		}
		if(typeof(newid) != "undefined") {
			loadlightbox(newid, 0);
		}
		oldhash = newhash;
	})

	//SHARETHIS KEY//
	/* var switchTo5x=true;
	if (typeof(stLight)!= 'undefined') stLight.options({publisher:'4a45176a-73d4-4e42-8be9-c015a589c031'});*/

});

//LISTENER FUNCTIONS//
//SIDEBAR//
function cowobo_sidebar_listeners() {
	//check if the mouse is over a scrollable div
	jQuery('.bottommenu').hover(function() {overmenu = 1}, function () {overmenu = 0;});
	jQuery('.scroller').hover(function() {overscroller = 1}, function () {overscroller = 0});

	//animate the submenus
	jQuery('.leftmenu li').click(function() {
		var subclass = '.'+jQuery(this).attr('class').split(' ')[0]+'menu';
		if(jQuery(subclass).length>0){
			if(jQuery(subclass).is(":visible")){
				jQuery(this).removeClass('menuselect');
				jQuery('.bottommenu').slideUp();
			} else {
				jQuery(this).siblings('li').removeClass('menuselect');
				jQuery(this).addClass('menuselect');
				jQuery('.bottommenu').slideUp(function() {
					jQuery(this).children('ul').hide();
					jQuery(subclass).show();	
					jQuery(this).slideDown();
				});	
			}
		}	
	});

	//ajax search for address
	jQuery('.address').live('click', function(event) {
		event.preventDefault();
		var address = jQuery(this).siblings('.searchform').val();
		if (geocoder) {
			geocoder.geocode({ 'address': address }, function (results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				var latlng = results[0].geometry.location;
				loadNewMap(latlng.lat(), latlng.lng(), 17, jQuery('.markerdata'), 'satellite', 0);
				}
			else {
				alert("We couldn't locate that address, please try fewer keywords");
        	}
			});
   		}    
	});
	
	//add horizontal scroll with mousewheel (requires horscroll.js)
	jQuery(".scroller").mousewheel(function(event, delta) {
		jQuery(this).scrollLeft(jQuery(this).scrollLeft()+delta * -30);
		event.preventDefault();
		removeMarkers();
	});

	// listerners for thumbs in sidebar
	jQuery('.medium').click(function(event) {
		var postid = jQuery(this).attr('id').split('-')[1];
		var catid = jQuery('.pagetitle').attr('id');
		if(window.location.hash == '#'+postid) {
			loadlightbox(postid, catid);
		} else {
			window.location.hash = postid;
		}
	});

	jQuery('.largerss, .rss').click(function(){
		jQuery("#rss").fadeIn();
	});
}

//jQuery UI
function cowobo_jquery_ui_listeners() {
	jQuery('.large').draggable({cancel:'.content'});
	jQuery(".scroller, .map, .slider").disableSelection();
}

//LIGHTBOX//
function cowobo_lightbox_listeners() {
	//fadeout lightboxes when clicked outside holder
	jQuery('.map, .shadowclick').live('click', function() {
		var oldhash = window.location.hash;
		//window.location.hash = oldhash + '_show'
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
		var postid = commentlist.parents('.large').attr('id');
		var message = jQuery('<span class="errormessage"></span>').appendTo(newform);
		jQuery.ajax({
        		beforeSend:function(msg){
            		msg.setRequestHeader("If-Modified-Since","0");
					newform.find('.sendingcomment').show();
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
					update_scrollbars(postid);
         		}
       		});
      	return false;
	});

	//resize text areas to fit content (requires autoresize.js)
	jQuery(".commenttext, .editcontent").autoResize({
    	onResize : function() {jQuery(this).css({opacity:0.8});},
    	animateCallback : function() {jQuery(this).css({opacity:1});},
    	animateDuration : 300,
    	extraSpace : 20
	});

}

//MESSENGER//
function cowobo_messenger_listeners() {
	jQuery('.messenger').click( function() {
		var type = jQuery(this).attr('class').split(' ');
		if (type[1] == 'join') {
            jQuery('.large').fadeOut('slow');
			jQuery('#join').fadeIn('slow');
		} else {
            var postid = type[2].split('-')[1];
            jQuery('.large').fadeOut('slow');
			jQuery('#'+postid).fadeIn('slow');
			loadlightbox(postid, 0);
			//loadmap here
		}
    });
}

function cowobo_map_listeners() {
	jQuery('.zoom, .pan').click(function(){
		jQuery('.bottommenu').slideUp();
		if(typeof(jQuery('.maplayer:last')).data('map') !='undefined'){
			var zoom = jQuery('.maplayer:last').data('map').zoom;
			var lat = jQuery('.maplayer:last').data('map').lat;
			var lng = jQuery('.maplayer:last').data('map').lng;
			var type = jQuery('.maplayer:last').data('map').type;
			var postid = jQuery('.maplayer:last').data('map').postid;
			if(jQuery(this).hasClass('labels')) {
				if(type=='satellite') type = 'hybrid'; else type = 'satellite';
				loadNewMap(lat, lng, zoom, markersvar, type, postid);
			}else if(jQuery(this).hasClass('moveleft')) {
				var newlng = adjustLonByPx(lng, xmid*-1, zoom);
				//window.location.hash = 'lng-'+newlng;
				loadNewMap(lat, newlng, zoom, markersvar, type, postid);
			} else if(jQuery(this).hasClass('moveright')) {
				var newlng = adjustLonByPx(lng, xmid*1, zoom);
				//window.location.hash = 'lng-'+newlng;
				loadNewMap(lat, newlng, zoom, markersvar, type, postid);
			} else if(jQuery(this).hasClass('moveup')) {
				var newlat = adjustLatByPx(lat, ymid*-1, zoom);
				//window.location.hash = 'lat-'+newlat;					
				loadNewMap(newlat, lng, zoom, markersvar, type, postid);
			} else if(jQuery(this).hasClass('movedown')) {
				var newlat = adjustLatByPx(lat, ymid*1, zoom);
				//window.location.hash = 'lat-'+newlat;					
				loadNewMap(newlat, lng, zoom, markersvar, type, postid);
			} else if(jQuery(this).hasClass('zoom')) {
				var level = jQuery(this).attr('class').split(' ')[1];
				var amount = level.split('-')[1];
				jQuery('.zoom').removeClass('zoomselect');
				if(amount < 3) {
					amount = parseInt(zoom) + 2;
					jQuery('.level-'+amount).addClass('zoomselect');
				} else {
					jQuery(this).addClass('zoomselect');
				}
				loadNewMap(lat, lng, amount, markersvar, type, postid);
				//window.location.hash = 'zoom-'+zoom+1;										
			}
		} else {
			alert('Please wait for map to finish loading')
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
	//load correct template for Add New post
	jQuery('.choosetype').change(function() {
		var catid = jQuery(this).val();
		jQuery(this).parents('.content').fadeTo('slow', 0.3);
		loadlightbox('new', catid);
	});

	jQuery('.relocate').live('click', function() {
		jQuery('.editmarker').data('postid', jQuery(this).parents('.large').attr('id'));
		jQuery('.large, .marker').fadeOut();
		jQuery('.editmarker').css('top', jQuery('.maplayer:last .mainmap').height()/2).show();
  	});

	jQuery('.savelocation').click(function() {
		if(typeof(jQuery('.maplayer:last')).data('map') !='undefined'){
			var id = jQuery(this).parents('.editmarker').data('postid');
			var lat = jQuery('.maplayer:last').data('map').lat;
			var lng = jQuery('.maplayer:last').data('map').lng;
			var newlatlng = lat+','+lng;
			jQuery('#'+id+', .marker').fadeIn();
			jQuery('#'+id+' .latlng').attr('id',newlatlng).html(newlatlng)
			jQuery('.editmarker').hide();
		} else {
			alert('Please wait for map to finish loading');
		}
  	});

	jQuery('.cancellocation').click(function() {
		var id = jQuery(this).parents('.editmarker').data('postid');
		jQuery('#'+id+', .marker').fadeIn();
		jQuery('.editmarker').hide();
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
			if(jQuery(this).siblings('.listbox').children().length<1){
				jQuery(this).siblings('h3').addClass('empty');
			}
		} else {
			jQuery(this).siblings('h3').removeClass('empty');
			jQuery(this).html('- Hide');
		}
		selectbox.slideToggle();
  	});

	jQuery('.addtag').live('click', function() {
		var tagname = jQuery(this).siblings('.newtag').val();
		var selectbox = jQuery(this).parents('.selectbox');
		var parent = selectbox.attr('id').split('-')[1];
		if(typeof(tagname)== 'undefined') {
			alert('Please enter the name of your tag');
		} else if(selectbox.find('#'+tagname).length>0) {
			alert('Tag already exists');
		} else {
			jQuery.ajax({
   				type: "POST",
   				url: rooturl+'wp-admin/admin-ajax.php',
   				data: {action: 'addtag', tagname:tagname, parent:parent},
   				success: function(msg){
					var listbox = selectbox.siblings('.listbox');
					selectbox.find('input').val('');
					listbox.prepend(msg).css('height', 'auto');
				}
			});
		}
  	});
	
	//this still needs some work
	jQuery('.addlocation').live('click', function() {
		var country = jQuery(this).siblings('.newcountry').val();
		var city = jQuery(this).siblings('.newcity').val();
		var selectbox = jQuery(this).parents('.selectbox');
		var parent = selectbox.attr('id').split('-')[1];
		
		if(typeof(country)== 'undefined') {
			alert('Please enter a Country');
		} else if(typeof(city)== 'undefined') {
			alert('Please enter a City');	
		} else {
			if(geocoder) {
			geocoder.geocode({ 'address': city + ',' + country }, function (results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					var latlng = results[0].geometry.location;
					var coordinates = latlng.lat()+','+latlng.lng();
					alert(coordinates);
					jQuery.ajax({
   						type: "POST",
   						url: rooturl+'wp-admin/admin-ajax.php',
   						data: {
							action: 'addlocation', 
							country:country,
							city:city, 
							parent:parent,
							coordinates:coordinates,
						},
   						success: function(msg){
							var listbox = selectbox.siblings('.listbox');
							selectbox.find('input').val('');
							listbox.html(msg);
						}
					});
				} else {
					alert("Google couldn't find this location, please check your connection and try another town or city.");
        		}
			});
   			} 
		}
  	});


	//move commentbox back to top
	jQuery('.add').live('click', function() {
		var replylink = jQuery(this);
		var post = replylink.parents('.large');
		var postid = post.attr('id');
		replylink.siblings('h3').removeClass('empty');
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
		listbox.css('height', 'auto');
		jQuery(this).parents('.listitem').remove();
	});

	jQuery('.typelist li').live('click', function() {
		var id = jQuery(this).attr('class').split(' ')[0];
		var container = jQuery(this).parents('.container');
		jQuery(this).addClass('selected').siblings().removeClass('selected');
		jQuery('.selectbox .slide').fadeOut();
		container.find('.cat'+id).fadeIn();
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
			if(jQuery(this).parents('.container').hasClass('locations')){
				listbox.html(listitem);	
			} else {
				listitem.prependTo(listbox);
				listbox.css('height', 'auto');
			}
		};
	});

	jQuery('.nextpost, .lastpost').live('click', function() {
		var newid = jQuery(this).attr('id').split('-')[1];
		jQuery(this).parents('.large').find('.content').fadeTo('slow', 0.5);
		loadlightbox(newid, 0);
		//var type = jQuery('.maplayer:last').data('map').type
		//loadNewMap(lat, lng, zoom, markers, type, postid);
	});

	jQuery('.save').live('click', function() {
		var posts = new Array(); var tags = new Array(); var authors = new Array(); var data = {};
		var post = jQuery(this).parents('.large');
		
		// save all new text inputs 
		post.find('.new').each(function(){
			data[jQuery(this).attr('name')] = jQuery(this).val();
		});

		// get all tags and linked posts 
		post.find('.container').each(function(){
			if (jQuery(this).hasClass('tags')) {
				jQuery(this).find('.listitem').each(function(){
					tags.push(jQuery(this).attr('class').split(' ')[0]);
				});
			} else if (jQuery(this).hasClass('authors')){
				jQuery(this).find('.listitem').each(function(){
					authors.push(jQuery(this).attr('class').split(' ')[0]);
				});
			} else {
				jQuery(this).find('.listitem').each(function(){
					posts.push(jQuery(this).attr('class').split(' ')[0]);
				});
			}
		});
	
		//save all data as strings
     	data['action'] = 'savechanges';
		data['postid'] = post.attr('id');
		data['coordinates'] = post.find('.latlng').attr('id');
		data['tags'] = tags.join(',');
		data['authors'] = authors.join(',');
		data['posts'] = posts.join(',');
			
		//make sure the post has a feed and author
		if (tags.length<0) {
			alert('You must specify atleast one feed');
		} else {
			jQuery.ajax({
				type: "POST",
				url: rooturl+'wp-admin/admin-ajax.php',
				data: data,
				success: function (permalink){
					alert('Your changes have been saved');
					document.location.reload(true);
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
 		tb_show('', rooturl+'wp-admin/media-upload.php?post_id='+postid+'&TB_iframe=true');
		//prevent close button from resetting hash
		jQuery('#TB_closeWindowButton').attr('href', 'javascript:void(0)');
 		return false;
	});

 	//reload the image gallery after closing media uploader
	window.original_tb_remove = window.tb_remove;
	window.tb_remove = function() {
		var id = jQuery('.large:visible').attr('id');
		jQuery.ajax({
   			type: "POST",
   			url: rooturl+'wp-admin/admin-ajax.php',
   			data: {action: 'loadgallery', postid:id},
   			success: function(msg){
				var gallery = jQuery('#'+id).find('.editable');
				if(gallery.children('.slide').length >1)
				gallery.children('.slide').remove();
				gallery.append(jQuery(msg));
			}
		});
		window.original_tb_remove();
	}
}

//FUNCTIONS//

function loadlightbox(postid, catid) {
	//if postholder is already loaded
	if(jQuery('#'+postid).length>0) {
		jQuery('#'+postid).fadeIn(); 
		update_scrollbars(postid);	
	}
	
	//if its a joinbox or selectbox then stop here	
	if (postid == 'join' || postid == 'selecttype') return true;
	
	//load new map if lightbox has coordinates
	var latlng = jQuery('#'+postid).find('.coordinates').val();
	if(typeof(latlng) != 'undefined' && latlng.length>0) {
		var markerpos = latlng.split(',');
		loadNewMap(markerpos[0], markerpos[1], 17, markersvar, 'satellite', postid);
	}
	
	//content of postholder if not already loaded
	if(jQuery('#'+postid + '.container').length<1) {
		jQuery.ajax({
   			type: "POST",
   			url: rooturl+'wp-admin/admin-ajax.php',
   			data: {
				action: 'loadlightbox', 
				currentcat:catid, 
				postid:postid
			},
   			success: function(msg){
				var newbox = jQuery(msg);
				var oldbox = jQuery('#'+postid);
				var newid = newbox.children('.large').attr('id');
				var scrollpos = oldbox.find('.content').scrollTop();
				//if oldbox doesnt exist then add it
				if(oldbox.length<1){
					jQuery('.large').hide();
					jQuery('body').append(newbox);
				} else {
					oldbox.replaceWith(newbox);
					newbox.find('.content').scrollTop(scrollpos);
				}
				cowobo_jquery_ui_listeners();
				update_scrollbars(newid);
				loadlike(newid);
				if(typeof(FrontEndEditor) != 'undefined' && newbox.find('.editable').length > 0) {
					newbox.find('.fee-field').each(FrontEndEditor.make_editable);
				}
			}
		});
	}
	
	// Load awesome-count
	jQuery('#'+postid).find('.like').html( jQuery('#like_small' + postid ).html() );
}

function loadlike(postid) {
	// Load social share box
	jQuery.ajax({
		type: "POST",
		url: rooturl+'wp-content/themes/cowobo/lib/ajax-show-share.php',
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
	else if(overmenu>0) var scbar = jQuery('.bottommenu');
	else if(overscroller>0) var scbar = jQuery('.scroller');
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
	var newnumber = Math.floor(jQuery('.scroller').scrollLeft()/(jQuery('.medium').eq(1).width()+10));
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
		url: rooturl+'wp-content/themes/cowobo/lib/ajax-feed-setter.php',
		data: {feed_type:feed_type,feed_id:feed_id,user_id:user_id,add:true},
		success: function(msg){
			angel_talk("This feed is now part of your personal feed.");
		}
	});
}

function reset_feed(user_id) {
	jQuery.ajax({
		type: "POST",
		url: rooturl+'wp-content/themes/cowobo/lib/ajax-feed-setter.php',
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
	//load map centered on africa (this should eventually be replaced with bounds of markers)
	loadNewMap(0.49860809171295, 10.932544165625036, 3, jQuery('.markerdata'), 'satellite', 0);
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

function loadNewMap(lat, lng, zoom, markers, type, postid){
	//show loading div and update markers var
	jQuery('.maploading').fadeIn();
	markersvar = markers;
	//setup static map image urls
	var map = jQuery('.map');
	var tilesize = xmid + 'x'+ ymid *2;
	var buffersize = xmid + 'x'+ ymid;
	var mapurl = 'http://maps.googleapis.com/maps/api/staticmap?maptype='+type+'&sensor=false&size=';
	var bufferurl = mapurl+buffersize+'&format=jpg'+'&zoom='+(zoom-1)+'&center='+lat+','+lng;
	var baseurl = mapurl+tilesize+'&format=jpg-baseline'+'&zoom='+zoom+'&center=';
	var bufferimg = '<img class="buffer" src="'+bufferurl+'" alt="" width="100%" height="100%">';
	var newlayer = jQuery('<div class="maplayer map'+postid+'"><div class="mainmap">'+bufferimg+'</div><div class="reflection">'+bufferimg+'</div></div>');

	map.append(newlayer);
	
	//add high res tiles when buffer has faded in
	newlayer.find('.buffer:first').load(function(){
		jQuery('.maplayer:last').fadeIn(function() {
			jQuery('.maploading').fadeOut();
			jQuery(this).data('map', {zoom:zoom, lat:lat, lng:lng, type:type, postid:postid});
			//reset zoom of buffer
			if(jQuery('.maplayer').length>1)
				jQuery(this).prev().css({width:'100%', height:'100%', margin:0});
		});
		for (var y=-1; y<=1; y+=2) {
			var url = baseurl + lat + ',' + adjustLonByPx(lng, xmid/2*y, zoom);
			newlayer.find('.mainmap').append('<img src="'+url+'" alt="" class="maptiles">');
			newlayer.find('.reflection').append('<img src="'+url+'" alt="" class="maptiles">');
		}
	});
	
	//zoom new layer on click if there are no lightboxes visible
	newlayer.click(function(e){
		if(typeof(jQuery('.maplayer:last')).data('map') !='undefined'){
			if(jQuery('.large :visible').length <1 && zoom < 17) {
				var mousex = Math.round(e.clientX/jQuery('.maptiles').width()*xmid)-xmid;
				var mousey = Math.round(e.clientY/jQuery('.maptiles').height()*2*ymid)-ymid;
				var newlng = adjustLonByPx(lng, mousex, zoom);
				var newlat = adjustLatByPx(lat, mousey, zoom);
				var newzoom = zoom + 2;
				jQuery('.zoom').removeClass('zoomselect');
				jQuery('.level-'+newzoom).addClass('zoomselect');
				loadNewMap(newlat, newlng, newzoom, markers, type, postid);
			}
		} else {
			alert('Please wait for map to finish loading')
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
		var markerid = jQuery(this).attr('id').split('-')[1];
		var markerpos = jQuery(this).val().split(',');
		var markerthumb = jQuery(this).attr('name');
		var markertitle = jQuery(this).attr('title');
		var markerimg = jQuery('.markerimg').val();
		var marker = jQuery('<div class="marker" id="marker'+postid+'"><div class="mcontent"><div class="mtitle"><span>'+markertitle+'</span></div><img src="'+markerthumb+'" alt=""/></div><img src="'+markerimg+'" alt=""/></div>');
  		var delta_x  = (LonToX(markerpos[1]) - LonToX(lng)) >> (21 - zoom);
		var delta_y  = (LatToY(markerpos[0]) - LatToY(lat)) >> (21 - zoom);
   		var marker_x = ((xmid + delta_x)/(xmid*2)*100)+'%';
   		var marker_y = ((ymid + delta_y)/(ymid*2)*100)+'%';
		marker.css({top:marker_y, left: marker_x});
		marker.appendTo(newlayer.find('.mainmap'));
		marker.click(function(event){
			event.stopPropagation();
			if(window.location.hash = '#'+markerid) {
				loadlightbox(markerid, 0);
			} else {
				window.location.hash = markerid;
			}
		});
		marker.hover(
			function() {jQuery(this).animate({opacity: 1},'fast'); jQuery(this).css('z-index', 4);},
			function() {jQuery(this).animate({opacity: 0.7},'fast'); jQuery(this).css('z-index', 3);}
		);
	});
	
	if(zoom>4) var fileurl = rooturl+'allcities.xml'; 
	else if(zoom>2) var fileurl = rooturl+'majorcities.xml';
	else var fileurl = '';
	jQuery.get(fileurl, function(xml) {
    	jQuery(xml).find("marker").each(function(){
      		var mdata = jQuery(this).children('td');
			var markertitle = mdata.eq(0).text();
			var markerpos = new Array(mdata.eq(2).text(), mdata.eq(1).text());
			var marker = jQuery('<div class="citylabel">'+markertitle+'</div>');
  			var delta_x  = (LonToX(markerpos[1]) - LonToX(lng)) >> (21 - zoom);
			var delta_y  = (LatToY(markerpos[0]) - LatToY(lat)) >> (21 - zoom);
   			var marker_x = ((xmid + delta_x)/(xmid*2)*100)+'%';
   			var marker_y = ((ymid + delta_y)/(ymid*2)*100)+'%';
			marker.css({top:marker_y, left: marker_x});
			marker.appendTo(newlayer.find('.mainmap'));
		});
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
});f