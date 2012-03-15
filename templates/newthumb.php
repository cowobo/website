<?php 
switch ($social->state) :
    case 1: $postid = 'join'; break;
    case 2: $postid = 'welcome'; break;
    case 3:	$postid = $social->profile_id; break;
    case 4: if(is_home()) $postid = 'selecttype'; else $postid = 'new'; break;
endswitch;

if(is_home()):
	$typename = 'New Post'; 
else :
	$typename = $currenttype->name;
endif;?>

<div class="medium" id="medium-<?php echo $postid;?>">
	<div class="holder">
	<div class="content">
		<div class="image newimage">
			<div class="topshadow">
				<h2>Add <?php echo $typename;?></h2>
				<div class="data">Right now!</div>
			</div>
		</div>
		<b>Help expand our site by</b><br/>
		sharing relevant projects, news, forums, wikis, etc
	</div>
	</div>
	<div class="arrow"></div>
</div>