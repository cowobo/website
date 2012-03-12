<div class="medium" id="medium-<?php switch ( $social->state ) :
    case 1:
        echo 'join';
        break;
    case 2:
    case 3:
        echo $social->profile_id;
        break;
    case 4:
        echo 'new';
        break;
    endswitch;
    ?>">
	<div class="holder">
	<div class="content">
		<div class="image newimage">
			<div class="topshadow">
				<h2>Add a Post</h2>
				<div class="data">Right now!</div>
			</div>
		</div>
		<b>Help expand our site by</b><br/>
		sharing relevant projects, news, forums, wikis, etc
	</div>
	</div>
	<div class="arrow"></div>
</div>