<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::Session();
if (!property_exists($this, 'HideSearch')) {
?>
<div class="SearchForm">
	<div class="Tabs GroupTabs">
		<?php
		$Suffix = $this->Sort.'/'.$this->Version.'/'.$Query;
		echo '<li>' .Anchor('All Groups', 'group/browse/all/'.$Suffix, 'ShowAll' . ($this->Filter == 'all' ? ' active' : '')). '</li>';
		echo '<li>' .Anchor('Clans', 'group/browse/clans/'.$Suffix, $this->Filter == 'clans' ? ' active' : '').'</li>';
		echo '<li>' .Anchor('Guilds', 'group/browse/guilds/'.$Suffix, $this->Filter == 'guilds' ? ' active' : ''). '</li>';
		echo '<li>' .Anchor('Communities', 'group/browse/communities/'.$Suffix, $this->Filter == 'communities' ? ' active' : ''). '</li>';
		?>
	</div>
	<div class="Tabs OrderOptions">
		<?php
		$Url = '/group/browse/'.$this->Filter.'/';
		$Query = GetIncomingValue('Form/Keywords', '');
		echo $this->Form->Open(array('action' => Url($Url.$this->Sort.'/'.$this->Version)));
		echo $this->Form->Errors();
		echo $this->Form->TextBox('Keywords', array('value' => $Query));
		echo $this->Form->Button('Search Groups');
		if ($Query != '')
		$Query = '?Form/Keywords='.$Query;
		?>
		<strong>↳Show</strong>
		<?php
// $CssClass = $this->Version == '0' ? 'Active' : '';
// echo Anchor('Both Vanilla Versions', $Url.$this->Sort.'/0/'.$Query, $CssClass);
		$CssClass = $this->Version == '2' ? 'active' : '';
		echo '<li>' .Anchor('Vanilla 2', $Url.$this->Sort.'/2/'.$Query, $CssClass). '</li>';
		$CssClass = $this->Version == '1' ? 'active' : '';
		echo '<li>' .Anchor('Vanilla 1', $Url.$this->Sort.'/1/'.$Query, $CssClass). '</li>';
		?>
		<strong>↳Order</strong>
		<?php
		$Suffix = $this->Version.'/'.$Query;
		echo '<li>' .Anchor('Recent', $Url.'recent/'.$Suffix, $this->Sort == 'recent' ? 'active' : ''). '</li>';
		echo '<li>' .Anchor('Popular', $Url.'popular/'.$Suffix, $this->Sort == 'popular' ? 'active' : ''). '</li>';
		?>
	</div>
	<?php
	echo $this->Form->Close();
	?>
</div>
<?php
}