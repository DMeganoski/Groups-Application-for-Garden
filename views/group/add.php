<?php if (!defined('APPLICATION')) exit();
$this->HideSearch = TRUE;
if ($this->DeliveryType() == DELIVERY_TYPE_ALL)
	echo $this->FetchView('head');

?>
<h1><?php echo T('Create a new Group'); ?></h1>
<?php
echo $this->Form->Open(array('enctype' => 'multipart/form-data'));
echo $this->Form->Errors();
?>
<ul>
	<li>
		<?php
			echo $this->Form->CheckBox('Vanilla2', 'This Group is for Vanilla 2', array('value' => '1'));
		?>
	</li>
	<li>
		<?php
			echo $this->Form->Label('Type of Group', 'GroupTypeID');
			echo $this->Form->DropDown(
				'GroupTypeID',
				$this->TypeData,
				array(
					'ValueField' => 'GroupTypeID',
					'TextField' => 'Label',
					'IncludeNull' => TRUE
				));
		?>
	</li>
	<li>
		<?php
			echo $this->Form->Label('Name', 'Name');
			echo $this->Form->TextBox('Name');
		?>
	</li>
	<li>
		<?php
			echo $this->Form->Label('Version', 'Version');
			echo $this->Form->TextBox('Version');
		?>
	</li>
	<li>
		<div class="Info"><?php echo T('Describe your group in as much detail as possible. Html allowed.'); ?></div>
		<?php
			echo $this->Form->Label('Description', 'Description');
			echo $this->Form->TextBox('Description', array('multiline' => TRUE));
		?>
	</li>
	<li>
		<div class="Info"><?php echo T('Specify any requirements your group has for joining, if none, specify none.'); ?></div>
		<?php
			echo $this->Form->Label('Requirements', 'Requirements');
			echo $this->Form->TextBox('Requirements', array('multiline' => TRUE));
		?>
	</li>
	<!--
	<li>
		<div class="Info"><?php echo T('Specify which versions you have tested your group with: PHP, MySQL, jQuery, etc'); ?></div>
		<?php
			echo $this->Form->Label('Testing', 'TestedWith');
			echo $this->Form->TextBox('TestedWith', array('multiline' => TRUE));
		?>
	</li>
	-->
	<li>
		<div class="Info"><?php echo T('By uploading a file you certify that you have the right to distribute the file and that it does not violate the Terms of Service.'); ?></div>
	</li>
</ul>
<?php echo $this->Form->Close('Save');
