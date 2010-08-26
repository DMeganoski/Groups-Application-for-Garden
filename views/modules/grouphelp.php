<?php if (!defined('APPLICATION')) exit(); ?>

<div class="Box">
	<h4>Make Your Own Groups!</h4>
	<ul>
	<?php
		$Session = Gdn::Session();
		echo '<li>'.Anchor('Quick-Start Guide', '/page/AddonQuickStart').'</li>';
		if ($Session->IsValid()) {
			echo '<li>'.Anchor('Start a new Group', '/group/add', array('class' => 'Popup')).'</li>';
		} else {
			echo '<li>'.Anchor('Sign In', '/entry/?Return=/addons', 'SignInPopup').'</li>';
		}
	?>
	</ul>
</div>

<div class="Box What">
	<h4>What are these groups for?</h4>
	<p>Groups are intended for small gaming communities to create a connection to other small gaming communitites.</p>
</div>
<div class="Box Work">
	<h4>How does it do that?</h4>
	<p>Groups allow users to post discussions specific to that group. In future versions</p>
	<p>If your Community's forums are built on <a href="http://vanillaforums.com">Vanilla Forums</a>, the future API will allow 2 way discussion updates between Real Gamers Use PC and your vanilla community.</p>
</div>
	
<div class="Box Approved">
	<h4>Real Gamers Approved?</h4>
	<p>We respect what it takes to run a successful community. Real Gamers approves groups that go above and beyond.</p>
</div>

<div class="Box DownloadPanelBox">
	<h4>Don't have Vanilla yet?</h4>
	<?php echo Anchor('Get Vanilla Now', 'http://vanillaforums.org/download', 'BigButton'); ?>
</div>
