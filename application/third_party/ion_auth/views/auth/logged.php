<?php
	$user = $this->ion_auth->user()->row();
?>

<a href="#" class="dropdown-toggle" data-toggle="dropdown">
	<img src="<?=base_url('img/user2-160x160.jpg');?>" class="user-image" alt="User Image">
	<span class="hidden-xs"><?php echo $user->email; ?></span>
</a>
<ul class="dropdown-menu">
	<!-- User image -->
	<li class="user-header">
		<img src="<?=base_url('img/user2-160x160.jpg');?>" class="img-circle" alt="User Image">

		<p>
			<?php echo $user->email; ?>
			<small>Member since Jan. 2019</small>
		</p>
	</li>
	<!-- Menu Body -->
	<!-- Menu Footer-->
	<li class="user-footer">
		<div class="pull-left">
			<a href="#" class="btn btn-default btn-flat">Profile</a>
		</div>
		<div class="pull-right">
			<a href="<?=base_url('auth/logout');?>" class="btn btn-default btn-flat">Sign out</a>
		</div>
	</li>
</ul>