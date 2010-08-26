<?php if (!defined('APPLICATION')) exit(); ?>
<h1><?php
if ($this->Group->File == '') {
	echo 'The requested file could not be found';
} else {
	echo 'Downloading: ' . $this->Group->Name . ' version ' . $this->Group->Version;
?></h1>
<div class="Box DownloadInfo">
	<strong>Your download should begin shortly</strong>
	<p>If your download does not begin right away, <a href="<?php echo '/uploads/'.$this->Group->File; ?>">click here to download now</a>.</p>
	
	<strong>Need help installing this group?</strong>
	<p>There should be a readme file in the group with more specific instructions on how to install it. If you are still having problems, <a href="http://www.realgamersusepc.com/discussions">ask for help on the community forums</a>.</p>

	<strong>Note</strong>
	<p>Real Gamers Inc cannot be held liable for issues that arise from the download or use of these groups.</p>
	
	<strong>Now what?</strong>
	<p>Head on back to the <a href="<?php echo Url('/group/'.$this->Group->GroupID); ?>"><?php echo $this->Group->Name; ?> page</a>, search for <a href="http://www.realgamersusepc.com/groups">more groups</a>, or you can <a href="http://www.realgamersusepc.com/docs">learn how to start your own</a>.</p>
</div>
<?php
}