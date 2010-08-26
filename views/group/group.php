<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::Session();
$VanillaVersion = $this->Group->Vanilla2 == '1' ? '2' : '1';

if ($this->DeliveryType() == DELIVERY_TYPE_ALL) {
	// echo $this->FetchView('head');
	?>
	<h1>
		<div>
			<?php echo T('Found in: ');
			echo Anchor('Group', '/group/browse/');
			?>
			<span>&rarr;</span> <?php echo Anchor($this->Group->Type.'s', '/group/browse/'.strtolower($this->Group->Type).'s'); ?>
		</div>
		<?php echo $this->Group->Name; ?>
		<?php echo $this->Group->Version; ?>
	</h1>
	<?php
	if ($Session->UserID == $this->Group->InsertUserID || $Session->CheckPermission('Groups.Group.Manage')) {
		echo '<div class="AddonOptions">';
		echo Anchor('Edit Details', '/group/edit/'.$this->Group->GroupID, 'Popup');
		echo '|'.Anchor('Upload New Version', '/group/newversion/'.$this->Group->GroupID, 'Popup');
		echo '|'.Anchor('Upload Screen', '/group/addpicture/'.$this->Group->GroupID, 'Popup');
		echo '|'.Anchor('Upload Icon', '/group/icon/'.$this->Group->GroupID, 'Popup');
		if ($Session->CheckPermission('Groups.Group.Approve'))
			echo '|'.Anchor($this->Group->DateReviewed == '' ? 'Approve Version' : 'Unapprove Version', '/group/approve/'.$this->Group->GroupID, 'ApproveGroup');
		
		echo '|'.Anchor('Delete Group', '/group/delete/'.$this->Group->GroupID.'?Target=/group', 'DeleteGroup');
		echo '</div>';
	}
	if ($this->Group->DateReviewed != '')
		echo '<div class="Approved"><strong>Approved!</strong> This group has been reviewed and approved by Real Gamers staff.</div>';

	?>
	<div class="Legal">
		<div class="DownloadPanel">
			<div class="Box DownloadBox">
				<p><?php echo Anchor('Download Now', '/get/'.$this->Group->GroupID, 'BigButton'); ?></p>
				<dl>
					<dt>Author</dt>
					<dd><?php echo Anchor($this->Group->InsertName, '/profile/'.urlencode($this->Group->InsertName)); ?></dd>
					<dt>Version</dt>
					<dd><?php echo $this->Group->Version.'&nbsp;'; ?></dd>
					<dt>Released</dt>
					<dd><?php echo Gdn_Format::Date($this->Group->DateUploaded); ?></dd>
					<dt>Downloads</dt>
					<dd><?php echo number_format($this->Group->CountDownloads); ?></dd>
				</dl>
			</div>
			<div class="Box RequirementBox">
				<h1>Requirements for Joining</h1>
				<?php
				$Requirements = Gdn_Format::Display($this->Group->Requirements);
					echo $Requirements;
				?>
			</div>
		</div>
	<?php
	if ($this->Group->Icon != '')
		echo '<img class="Icon" src="'.Url('uploads/ai'.$this->Group->Icon).'" />';
		
	echo Gdn_Format::Html($this->Group->Description);
	?>
	</div>
	<?php
	if ($this->PictureData->NumRows() > 0) {
		?>
		<div class="PictureBox">
			<?php
			foreach ($this->PictureData->Result() as $Picture) {
				echo '<a rel="popable[gallery]" href="#Pic_'.$Picture->GroupPictureID.'"><img src="'.Url('uploads/at'.$Picture->File).'" /></a>';
				echo '<div id="Pic_'.$Picture->GroupPictureID.'" style="display: none;"><img src="'.Url('uploads/ao'.$Picture->File).'" /></div>';
			}
			?>
		</div>
		<?php
	}
	?>
	<h2 class="Questions">Questions
	<?php
	if ($Session->IsValid()) {
		echo Anchor('Start a group discussion', 'post/discussion?GroupID='.$this->Group->GroupID, 'TabLink');
	} else {
		echo Anchor('Sign In', '/entry/?Target='.urlencode($this->SelfUrl), 'TabLink'.(C('Garden.SignIn.Popup') ? ' SignInPopup' : ''));
	}
	?></h2>
	<?php if (is_object($this->DiscussionData) && $this->DiscussionData->NumRows() > 0) { ?>
	<ul class="DataList Discussions">
		<?php
		$this->ShowOptions = FALSE;
		include($this->FetchViewLocation('discussions', 'DiscussionsController', 'vanilla'));
		?>
	</ul>
	<?php
	} else {
		?>
		<div class="Empty"><?php echo T('No discussions yet.'); ?></div>
		<?php
	}
}