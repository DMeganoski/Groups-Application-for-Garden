<?php if (!defined('APPLICATION')) exit();
/*
Copyright 2008, 2009 Vanilla Forums Inc.
This file is part of Garden.
Garden is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
Garden is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with Garden.  If not, see <http://www.gnu.org/licenses/>.
Contact Vanilla Forums Inc. at support [at] vanillaforums [dot] com
*/

class GroupsController extends Gdn_Controller {
   
   public function __construct() {
      parent::__construct();
   }
   
   public function Initialize() {
      if ($this->DeliveryType() == DELIVERY_TYPE_ALL) {
         $this->Head = new HeadModule($this);
         $this->Head->AddTag('meta', array(
            'name' => 'description',
            'content' => "Real Gamers Use PC is a central PC gaming community that is intended to be the center and voice for all PC gamers."
         ));
      }
         
      $this->AddCssFile('style.css');
      $this->AddCssFile('groups.css');
      parent::Initialize();
   }
}