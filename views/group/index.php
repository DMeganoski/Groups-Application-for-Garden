<?php if (!defined('APPLICATION')) exit();
include($this->FetchViewLocation('helper_functions'));
if ($this->DeliveryType() == DELIVERY_TYPE_ALL)
	echo $this->FetchView('head');
   
if ($this->ApprovedData->NumRows() > 0) {
?>
<h2>Vanilla Approved</h2>
<ul class="DataList Addons">
	<?php
	$Alt = '';
	foreach ($this->ApprovedData->Result() as $Group) {
		$Alt = $Alt == ' Alt' ? '' : ' Alt';
		WriteGroup($Group, $Alt);
	}
	?>
</ul>
<?php
}

if ($this->NewData->NumRows() > 0) {
?>
<h2>Recently Uploaded &amp; Updated</h2>
<ul class="DataList Addons">
	<?php
	$Alt = '';
	foreach ($this->NewData->Result() as $Group) {
		$Alt = $Alt == ' Alt' ? '' : ' Alt';
		WriteGroup($Group, $Alt);
	}
	?>
</ul>
<?php
echo Anchor('More', '/group/browse', array('class' => 'More'));
}

