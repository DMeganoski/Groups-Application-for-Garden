<?php if (!defined('APPLICATION')) exit();

function WriteGroup($Group, $Alt) {
	$Url = '/group/'.$Group->GroupID.'/'.Gdn_Format::Url($Group->Name);
	?>
	<li class="Item AddonRow<?php echo $Alt; ?>">
		<div class="ItemContent">
			<?php
			echo Anchor($Group->Name, $Url, 'Title');
			
			if ($Group->Icon != '')
				echo '<a class="Icon" href="'.Url($Url).'"><img src="'.Url('uploads/ai'.$Group->Icon).'" /></a>';
	
			echo Anchor(SliceString(Gdn_Format::Text($Group->Description), 300), $Url);
			?>
			<div class="Meta">
				<span class="<?php echo $Group->Vanilla2 == '1' ? 'Vanilla2' : 'Vanilla1'; ?>"><?php
					echo $Group->Vanilla2 == '1' ? 'Vanilla 2' : 'Vanilla 1'; ?></span>
				<?php
				if ($Group->DateReviewed != '')
					echo '<span class="Approved">Approved</span>';
				?>
				<span class="Type">
					Type
					<span><?php echo $Group->Type; ?></span>
				</span>
				<span class="Author">
					Author
					<span><?php echo $Group->InsertName; ?></span>
				</span>
				<span class="Downloads">
					Downloads
					<span><?php echo number_format($Group->CountDownloads); ?></span>
				</span>
				<span class="Updated">
					Updated
					<span><?php echo Gdn_Format::Date($Group->DateUpdated); ?></span>
				</span>
			</div>
		</div>
	</li>
<?php
}
