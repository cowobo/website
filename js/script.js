//VARIABLES//
var winx;
var overscroller;
var overslide;
var overlightbox;
var scroller;
var rooturl;
var geocoder;
var nextposts;
var mapdata = {};
var xmid = 640;
var ymid = 320;
var loading = false;

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
		var hasharray = newhash.split('#');
		var catid = 0;
		for (var x = 1; x < hasharray.length; x++) {
			var part = hasharray[x].split('=');
			mapdata[part[0]] = part[1];
		}
		jQuery('#'+mapdata.post).fadeIn();
		
		loadlightbox(mapdata.post);
	}

	//load lightbox when hash changes
	jQuery(window).hashchange(function(){
		var newhash = window.location.hash;
		var hasharray = newhash.split('#');
		var prevmap;

		//find the layer if has already been loaded
		jQuery('.maplayer').each(function(){
			if(jQuery(this).data('hash') === newhash) {
				prevmap = jQuery(this);
			}
		});

		//if map has already loaded update mapdata and fade it in, else load the new map
		if(typeof(prevmap) != 'undefined') {
			jQuery.each(prevmap.data(),function(key, value) {mapdata[key] = value;});
			prevmap.insertAfter(jQuery('.maplayer:last')).fadeIn( function() {
				jQuery('.maplayer').not(this).hide();
			});
			jQuery('.large').fadeOut();
			jQuery('#'+mapdata.post).fadeIn();
		} else {
			for (var x = 1; x < hasharray.length; x++) {
				var part = hasharray[x].split('=');
				mapdata[part[0]] = part[1];
			}
			if(jQuery('#'+mapdata.post).length>0){
				jQuery('.large').fadeOut();
				jQuery('#'+mapdata.post).fadeIn();
			}
			loadlightbox(mapdata.post, 0);
		}
	})

	//SHARETHIS KEY//
	/* var switchTo5x=true;
	if (typeof(stLight)!= 'undefined') stLight.options({publisher:'4a45176a-73d4-4e42-8be9-c015a589c031'});*/
});



//LISTENER FUNCTIONS//
//SIDEBAR//
function cowobo_sidebar_listeners() {
	//check if the mouse is over a scrollable div
	jQuery('.scroller').hover(function() {overscroller = 1}, function () {overscroller = 0});

	//animate the submenus with intent
	jQuery('.menu li').hover(
		function() {
			var child = jQuery(this).children('ul');
			var t = setTimeout(function() {child.slideDown()}, 200);
			jQuery(this).data('timeout', t);
		},
		function() {
			var child = jQuery(this).children('ul');
			clearTimeout(jQuery(this).data('timeout'));
			child.slideUp();
		}
	);

	jQuery('.homebutton').click(function() {
		if(window.location.hash != '') {
			window.location.hash = '';
		} else {
			window.location = rooturl;
		}
	});

	//search address from menubar
	jQuery('.address').live('click', function(event) {
		event.preventDefault();
		var keywords = jQuery(this).siblings('.searchform').val();
		searchaddress(keywords);
	});

	//profile button
	jQuery('.profile').click( function() {
		var postid = jQuery(this).attr('class').split(' ')[1];
        window.location.hash = '#post='+postid;
    });

	//add horizontal scroll with mousewheel (requires horscroll.js)
	jQuery(".scroller").mousewheel(function(event, delta) {
		jQuery(this).scrollLeft(jQuery(this).scrollLeft()+delta * -30);
		event.preventDefault();
		removeMarkers();
	});

	//scroll posts
	jQuery(".scrollarrow").click(function(){
		jQuery('.page').animate({scrollLeft: jQuery('.page').scrollLeft()+500}, 'slow');
	})

	// listerners for thumbs in sidebar
	jQuery('.medium').click(function(event) {
		var postid = jQuery(this).attr('id').split('-')[1];
		window.location.hash = '#post='+postid;
		jQuery('.large').fadeOut();
		jQuery('#'+postid).fadeIn();
	});

	jQuery('.catrss').click(function(){
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
		//window.location.hash += '#show-0'
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
			newlayer.children('.streetholder').append('<img src="'+url+'" alt="" width="50%">');
		}
		jQuery(this).parent().append(newlayer);
		return false; //to prevent default action
	});

	jQuery('.postrss').live('click', function() {
		//todo load related feed posts icons via ajax
		jQuery("#rss").fadeIn();
	});


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

function cowobo_map_listeners() {
	jQuery('.zoom, .pan').click(function(){
		var map = jQuery('.maplayer:last').data('hash');
		var hash = window.location.hash;
		var newvalue = {};

		jQuery('.menu ul').slideUp();
		mapdata['post'] = 0;

		//save new value with corresponding key
		if(typeof(map) !='undefined'){
			if(jQuery(this).hasClass('labels')) {
				newvalue['type']= 'hybrid';
			}else if(jQuery(this).hasClass('moveleft')) {
				newvalue['lng']= adjustLonByPx(mapdata.lng, xmid*-1, mapdata.zoom);
			} else if(jQuery(this).hasClass('moveright')) {
				newvalue['lng']= adjustLonByPx(mapdata.lng, xmid*1, mapdata.zoom);
			} else if(jQuery(this).hasClass('moveup')) {
				newvalue['lat']= adjustLatByPx(mapdata.lat, ymid*-1, mapdata.zoom);
			} else if(jQuery(this).hasClass('movedown')) {
				newvalue['lat']= adjustLatByPx(mapdata.lat, ymid*1, mapdata.zoom);
			} else if(jQuery(this).hasClass('zoom')) {
				var level = jQuery(this).attr('class').split(' ')[1];
				newvalue['zoom']= level.split('-')[1];
			}
			//update hash with new values
			for (key in newvalue) {
				if(hash.indexOf(key) != -1){
    				var vars = hash.split("#");
					for (var i = 0; i < vars.length; i++) {
        				var part = vars[i].split("=");
        				if (part[0] == key) vars[i] = key+"="+newvalue[key];
					}
					window.location.hash = vars.join('#');
				} else {
					window.location.hash = '#'+key+'='+newvalue[key];
				}
			}
		} else {
			alert('Please wait for map to finish loading')
		}
	});

	//zoom new layer on click if there are no lightboxes visible
	jQuery('.maplayer').live('click', function(e){
		var oldlat = jQuery(this).data('lat');
		var oldlng = jQuery(this).data('lng');
		var oldzoom = jQuery(this).data('zoom');
		if(jQuery('.large :visible').length<1 && oldzoom < 17) {
			var mousex = Math.round(e.clientX/jQuery('.mainmap .tiles img:last').width()*xmid)-xmid;
			var mousey = Math.round(e.clientY/jQuery('.mainmap .tiles img:last').height()*ymid*2)-ymid;
			newlat = adjustLatByPx(oldlat, mousey, oldzoom);
			newlng = adjustLonByPx(oldlng, mousex, oldzoom);
			newzoom = parseFloat(oldzoom) + 2;
			window.location.hash = '#lat='+newlat+'#lng='+newlng+'#zoom='+newzoom;
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
		jQuery(this).parents('.large').find('.loading').fadeIn();
		loadlightbox('new-'+catid);
	});

	//relocate marker
	jQuery('.relocate').live('click', function() {
		var keywords = jQuery(this).siblings('.searchform').val();
		if(keywords.length > 0) {
			searchaddress(keywords);
			jQuery('.editmarker').data('postid', jQuery(this).parents('.large').attr('id'));
			jQuery('.large, .marker').fadeOut();
			jQuery('.editmarker').css('top', jQuery('.maplayer:last .mainmap').height()/2).show();
		} else {
			alert('Please enter a town or city')
		}

  	});

	//save location
	jQuery('.savelocation').click(function() {
		if(typeof(jQuery('.maplayer:last')).data('hash') !='undefined'){
			var id = jQuery(this).parents('.editmarker').data('postid');
			var lat = mapdata.lat;
			var lng = mapdata.lng;
			var newlatlng = lat+','+lng;
			jQuery('#'+id+', .marker').fadeIn();
			jQuery('#'+id+' .latlng').val(newlatlng);
			jQuery('.editmarker').hide();
		} else {
			alert('Please wait for map to finish loading');
		}
  	});

	jQuery('.cancellocation').click(function() {
		var id = jQuery(this).parents('.editmarker').data('postid');
		jQuery('#'+id+' .latlng').val('');
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
			jQuery(this).html('+ Link');
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
							coordinates:coordinates
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
		} else if(listbox.children().length>5 ){
			alert('You can only add 5 posts to each section. Please remove some posts and try again');
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
		jQuery(this).addClass('loadicon');
		window.location.hash = '#post='+newid;
	});

	jQuery('.save').live('click', function() {
		var posts = new Array(); var tags = new Array(); var authors = new Array(); var data = {};
		var post = jQuery(this).parents('.large');
		var latlng = post.find('.latlng').val();

		//check coordinates entered into box are correct format
		if(typeof(latlng)!= 'undefined') {
			var testlat = /^[0-9\-\.\,]*$/;
			if(!testlat.test(latlng)) {
				alert('Please enter coordinates in the correct decimal format (ie 33.3242,12.134123) or enter an address and click on the link above');
				return false;
			} else {
				data['coordinates'] = latlng;
			}
		}

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
		data['tags'] = tags.join(',');
		data['authors'] = authors.join(',');
		data['posts'] = posts.join(',');

		//make sure the post has a title and feed
		if(post.find('.edittitle').val().length < 3) {
			alert('You must specify a title');
		} else if (tags.length<1) {
			alert('You must specify atleast one tag');
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

function loadlightbox(postid) {
	//setup additional variables
	var catid = 0;
	if(postid != 0) 
		var type = postid.split('-')[1];
	if(typeof(type)!= 'undefined') {
		catid = type;
		postid = 'newtype';
		jQuery('#new').find('.loading').removeClass('hide');
	}
	
	// get coordinates of post and load corresponding map
	var latlng = jQuery('#'+postid).find('.coordinates').val();
	if(typeof(latlng) != 'undefined' && latlng.length>0) {
		var markerpos = latlng.split(',');
		mapdata['lat'] = markerpos[0];
		mapdata['lng'] = markerpos[1];
		mapdata['zoom'] = 15;
	}
	
	if (postid != 'newtype' && postid != 0 && typeof(postid) != 'undefined') update_scrollbars(postid);
	if (postid == 'new' || postid == 'newprofile' || typeof(postid) == 'undefined') return true;
	else if (postid != 'newtype') loadNewMap(mapdata);

	if(jQuery('#'+postid + '.container').length<1 && postid != 0) {
		jQuery.ajax({
   			type: "POST",
   			url: rooturl+'wp-admin/admin-ajax.php',
   			data: {
				action: 'loadlightbox',
				currentcat:catid,
				postid:postid
			},
   			success: function(msg){
				var newbox = jQuery('<div>'+msg+'</div>');
				var newid = newbox.children('.large').attr('id');
				var oldbox = jQuery('#'+newid);
				var scrollpos = oldbox.find('.content').scrollTop();
				//replace newbox so users can go back to their new post
				if(postid == 'newtype') {
					oldbox = jQuery('#new'); 
					jQuery('#medium-new').attr('id', 'medium-'+newid);
				}
				//if oldbox doesnt exist then add it	;
				if(oldbox.length<1){
					jQuery('.large').hide();
					jQuery('body').append(newbox);
					jQuery('#new').find('.loading').addClass('hide');
				} else {
					if(oldbox.css("display") == "none") newbox.children('.large').hide();
					oldbox.replaceWith(newbox.children('.large'));
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

function searchaddress(address) {
	if (geocoder) {
		geocoder.geocode({ 'address': address }, function (results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				var latlng = results[0].geometry.location;
				mapdata.lat = latlng.lat();
				mapdata.lng = latlng.lng();
				mapdata.zoom = 10;
				loadNewMap(mapdata);
				return latlng;
			} else {
				alert("We couldn't locate that address, please try fewer keywords");
				return false;
        	}
		});
   	}
}

function loadnextposts() {
	var url = jQuery('.nextposts').attr('href');
	nextposts = jQuery.ajax({
		type: "POST",
   		url: url,
		data: jQuery(this).serialize(),
        dataType:'html',
		success: function (msg){
			var newdata = jQuery('<div></div>').append(msg);
			var newboxes = newdata.find('.large');	
			var newthumbs = newdata.find('.medium').not('#medium-join, #medium-selecttype');
			var nextlink = newdata.find('.nextposts');
			if(nextlink.length>0) jQuery('.nextposts').replaceWith(nextlink);
			else jQuery('.nextposts').remove();
			jQuery('.scroller').append(newthumbs);
			jQuery('.page').animate({scrollLeft: jQuery('.page').scrollLeft()+500}, 'slow');
			jQuery('body').append(newboxes);
			jQuery('.scrollarrow').html('<div class="scrollicon"></div>more posts')
			loading = false;
		}
	});
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
	else if(overscroller>0) {
		var scbar = jQuery('.page');
		var scrollpos = scbar.get(0).scrollWidth - scbar.scrollLeft();
		var linkcount = jQuery('.nextposts').length;
		if(scrollpos == scbar.outerWidth()) {
			if(linkcount > 0 && loading == false) {
				jQuery('.scrollarrow').html('<div class="scrollicon"></div><span class="loadicon">loading posts..</span>');
				loadnextposts();	
				loading = true; 
			}
		} else {
			if(loading == true) {
				jQuery('.scrollarrow').html('<div class="scrollicon"></div>older posts');
				nextposts.abort();
				loading = false;
			}
		}
	} else return;

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

// hide markers on map corresponding to timeline
function removeMarkers() {
	jQuery('.medium').each(function(){
		var id = jQuery(this).attr('id').split('-')[1];
		if(jQuery(this).offset().left < 0) {
			jQuery('.marker'+id).hide();
		} else {
			jQuery('.marker'+id).show();
		}
	});
}

// Personal RSS feed Ajax-calls
function add_to_feed(feed_type,feed_id,user_id) {
	jQuery.ajax({
		type: "POST",
		url: rooturl+'wp-content/themes/cowobo/lib/ajax-feed-setter.php',
		data: {feed_type:feed_type,feed_id:feed_id,user_id:user_id,add:true},
		success: function(msg){
			alert("This feed is now part of your personal feed.");
		}
	});
}

function add_to_profile(post_id,user_id) {
	jQuery.ajax({
		type: "POST",
		url: rooturl+'wp-content/themes/cowobo/lib/ajax-feed-setter.php',
		data: {post_id:post_id,user_id:user_id,profile:true},
		success: function(msg){
            console.log(msg);
			alert("Post shared on profile.");
		}
	});
}

function reset_feed(user_id) {
	jQuery.ajax({
		type: "POST",
		url: rooturl+'wp-content/themes/cowobo/lib/ajax-feed-setter.php',
		data: {user_id:user_id,reset:true},
		success: function(msg){
			alert("Your feed has been succesfully reset.");
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
	//load map centered on africa
	mapdata = {'post':0, 'lat':15.49860809171295, 'lng':10.932544165625036, 'markers': jQuery('.markerdata'), 'zoom':3, 'type':'satellite'} // 'markers':markers,
	loadNewMap(mapdata);
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
	var newlon = XToLon(LonToX(lon) + (amount << (21 - zoom)));
	if (newlon < -180) newlon = 360 + newlon;
	else if (newlon > 180) newlon = newlon - 360;
	return newlon;
}
function adjustLatByPx(lat, amount, zoom) {
	var newlat = YToLat(LatToY(lat) + (amount << (21 - zoom)));
	if (newlat < -90) newlat = 180 + newlat;
	else if (newlat > 90) newlat = newlat - 180;
	return newlat;
}

function loadNewMap(data){
	var map = jQuery('.map');
	var tilesize = xmid + 'x'+ ymid *2;
	var buffersize = xmid + 'x'+ ymid;
	var mapurl = 'http://maps.googleapis.com/maps/api/staticmap?maptype='+data.type+'&sensor=false&size=';
	var bufferurl = mapurl+buffersize+'&format=jpg'+'&zoom='+(data.zoom-1)+'&center='+data.lat+','+data.lng;
	var baseurl = mapurl+tilesize+'&format=jpg-baseline'+'&zoom='+data.zoom+'&center=';
	var bufferimg = '<img class="buffer" src="'+bufferurl+'" alt="">'
	var newlayer = jQuery('<div class="maplayer"><div class="mainmap">'+bufferimg+'<div class="tiles"></div></div><div class="reflection">'+bufferimg+'<div class="tiles"></div></div></div>');

	//update menu
	jQuery('.maploading').fadeIn();
	jQuery('.zoom').removeClass('zoomselect');
	jQuery('.level-'+data.zoom).addClass('zoomselect');

	//load new layer
	for (key in data) {
		newlayer.data(key, data[key]);
	}
	newlayer.data('hash', window.location.hash);
	map.append(newlayer);

	//add high res tiles when buffer has faded in
	newlayer.find('.buffer:first').load(function(){
		newlayer.fadeIn(function() {
			jQuery('.maplayer').not(this).hide();
		});
		for (var y=-1; y<=1; y+=2) {
			var url = baseurl + data.lat + ',' + adjustLonByPx(data.lng, xmid/2*y, data.zoom);
			newlayer.find('.mainmap .tiles').append('<img src="'+url+'" alt="">');
			newlayer.find('.reflection .tiles').append('<img src="'+url+'" alt="">');
		}
		newlayer.find('.mainmap .tiles img').load(function(){
			jQuery('.maploading').fadeOut();
		});
	});

	//sort markers by latitude to ensure correct overlapping
	var markerlist = jQuery('<div></div>');
	data.markers.sort(function(a,b){
		var posa = a.value.split(',');
		var posb = b.value.split(',');
    	return  posb[0] - posa[0];
	}).clone().appendTo(markerlist);

	//append markers to map
	markerlist.children('.markerdata').each(function(){
		var postid = jQuery(this).attr('id').split('-')[1];
		var markerpos = jQuery(this).val().split(',');
		var markerthumb = jQuery(this).attr('name');
		var markertitle = jQuery(this).attr('title');
		var markerimg = jQuery('.markerimg').val();
		var marker = jQuery('<div class="marker marker'+postid+'"><div class="mcontent"><div class="mtitle"><span>'+markertitle+'</span></div><img src="'+markerthumb+'" alt=""/></div><img src="'+markerimg+'" alt=""/></div>');
		var delta_x  = (LonToX(markerpos[1]) - LonToX(data.lng)) >> (21 - data.zoom);
		var delta_y  = (LatToY(markerpos[0]) - LatToY(data.lat)) >> (21 - data.zoom);
   		var marker_x = ((xmid + delta_x)/(xmid*2)*100)+'%';
   		var marker_y = ((ymid + delta_y)/(ymid*2)*100)+'%';
		if(markertitle == 'Profiles') marker.append('<div class="mprofile"></div>');
		marker.css({top:marker_y, left: marker_x});
		marker.appendTo(newlayer.find('.mainmap'));
		marker.click(function(event){
			event.stopPropagation();
			window.location.hash = '#post='+postid;
			jQuery('.large').fadeOut();
			jQuery('#'+postid).fadeIn();
		});
		marker.hover(
			function() {jQuery(this).animate({opacity: 0.7},'fast'); jQuery(this).css('z-index', 4);},
			function() {jQuery(this).animate({opacity: 1},'fast'); jQuery(this).css('z-index', 3);}
		);
	});

	if(data.zoom>4) var fileurl = rooturl+'allcities.xml';
	else if(data.zoom>2) var fileurl = rooturl+'majorcities.xml';
	else var fileurl = '';
	jQuery.get(fileurl, function(xml) {
    	jQuery(xml).find("marker").each(function(){
      		var mdata = jQuery(this).children('td');
			var markertitle = mdata.eq(0).text();
			var markerpos = new Array(mdata.eq(2).text(), mdata.eq(1).text());
			var marker = jQuery('<div class="citylabel">'+markertitle+'</div>');
  			var delta_x  = (LonToX(markerpos[1]) - LonToX(data.lng)) >> (21 - data.zoom);
			var delta_y  = (LatToY(markerpos[0]) - LatToY(data.lat)) >> (21 - data.zoom);
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
});
