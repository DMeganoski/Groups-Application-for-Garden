<?php if (!defined('APPLICATION')) exit();
/*
Copyright 2008, 2009 Vanilla Forums Inc.
This file is part of Garden.
Garden is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
Garden is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with Garden.  If not, see <http://www.gnu.org/licenses/>.
Contact Vanilla Forums Inc. at support [at] vanillaforums [dot] com
*/

/**
 * @ADDON: MessagesController handles displaying lists of conversations and conversation messages.
 */
class GetController extends GroupsController {
   
   public $Uses = array('Form', 'Database', 'GroupModel');
	
   public function Index($GroupID = '', $JoinGroup = '0') {
		$this->AddJsFile('js/library/jquery.js');
		if ($JoinGroup != '1')
			$this->AddJsFile('get.js');

		// @ADDON: Define the item being downloaded
		if (strtolower($GroupID) == 'vanilla')
			$GroupID = 465;
			
		// @ADDON: Find the requested addon
		$this->Group = $this->GroupModel->GetID($GroupID);
		if (!is_object($this->Group)) {
			$this->Group = new stdClass();
			$this->Group->Name = 'Not Found';
			$this->Group->Version = 'undefined';
			$this->Group->File = '';
		} else {
			if ($JoinGroup == '1') {
				// @TO-DO: Add the user to the Member list
				// @TO-DO: 
				// @ADDON: Record this download
				$this->Database->SQL()->Insert('Download', array(
					'GroupID' => $this->Group->GroupID,
					'DateInserted' => Gdn_Format::ToDateTime(),
					'RemoteIp' => @$_SERVER['REMOTE_ADDR']
				));
				$this->GroupModel->SetProperty($this->Group->GroupID, 'CountDownloads', $this->Group->CountDownloads + 1);
				Gdn_FileSystem::ServeFile('uploads/'.$this->Group->File, Gdn_Format::Url($this->Group->Name.'-'.$this->Group->Version));
			}
		}
		
		$this->AddModule('GroupHelpModule');		
      $this->Render();
   }
}