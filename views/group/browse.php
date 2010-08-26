<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::Session();
include($this->FetchViewLocation('helper_functions'));

if ($this->DeliveryType() == DELIVERY_TYPE_ALL) {
   echo $this->FetchView('head');
?>
   <h2>Browse <?php
      if ($this->Filter == 'clans')
         echo 'Clans';
      elseif ($this->Filter == 'guilds')
         echo 'Guilds';
      elseif ($this->Filter == 'communities')
         echo 'Communities';
      else
         echo 'Groups';
   ?></h2>
   <ul class="DataList Addons">
      <?php
      if ($this->SearchResults->NumRows() == 0)
         echo '<li><div class="Empty">There were no groups matching your search criteria.</div></li>';
}            
$Alt = '';
foreach ($this->SearchResults->Result() as $Group) {
   $Alt = $Alt == ' Alt' ? '' : ' Alt';
   WriteGroup($Group, $Alt);
}
if ($this->DeliveryType() == DELIVERY_TYPE_ALL) {
?>
   </ul>
   <?php
   echo $this->Pager->ToString('more');
}
