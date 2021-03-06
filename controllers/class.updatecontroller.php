<?php if (!defined('APPLICATION')) exit();
/*
Copyright 2008, 2009 Vanilla Forums Inc.
This file is part of Garden.
Garden is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
Garden is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with Garden.  If not, see <http://www.gnu.org/licenses/>.
Contact Vanilla Forums Inc. at support [at] vanillaforums [dot] com
*/

/// <summary>
/// Update Controller
/// </summary>
class UpdateController extends GroupsController {
   public $Uses = array('Database');
   
   /// <summary>
   /// Get the current version of all the requested addons (as referenced by ADDON_TYPE and NAME).
   /// </summary>
   public function Index() {
      $Source = GetIncomingValue('source', '');
      $CountUsers = GetIncomingValue('users', '');
      $CountConversations = GetIncomingValue('conversations', '');
      $CountConversationMessages = GetIncomingValue('messages', '');
      $CountDiscussions = GetIncomingValue('discussions', '');
      $CountComments = GetIncomingValue('comments', '');
      $UpdateChecks = Gdn_Format::Unserialize($this->_GetJsonString('updateChecks'));
      $UpdateCheckID = 0;
      
      // Get the UpdateCheckSourceID
      $SQL = $this->Database->SQL();
      $Data = $SQL->Select('SourceID')
         ->From('UpdateCheckSource')
         ->Where(array('Location' => $Source))
         ->Get()
         ->FirstRow();
      $UpdateCheckSourceID = $Data ? $Data->SourceID : 0;
      if ($UpdateCheckSourceID <= 0)
         $UpdateCheckSourceID = $SQL->Insert('UpdateCheckSource',
            array(
               'Location' => $Source,
               'DateInserted' => Gdn_Format::ToDateTime(),
               'RemoteIp' => @$_SERVER['REMOTE_ADDR']
            )
         );
         
      // Assuming the source was saved successfully
      if ($UpdateCheckSourceID > 0) {
         // Record all of the count information
         $UpdateCheckID = $SQL->Insert('UpdateCheck',
            array(
               'SourceID' => $UpdateCheckSourceID,
               'CountUsers' => intval($CountUsers),
               'CountDiscussions' => intval($CountDiscussions),
               'CountComments' => intval($CountComments),
               'CountConversations' => intval($CountConversations),
               'CountConversationMessages' => intval($CountConversationMessages),
               'DateInserted' => Gdn_Format::ToDateTime(),
               'RemoteIp' => @$_SERVER['REMOTE_ADDR']
            )
         );
      }
      
      // Define a RequiredUpdates array as a response
      $Response = array();

      // If the the updatechecks argument was a serialized array, parse it to
      // see if we have newer versions
      if (is_array($UpdateChecks)) {
         foreach ($UpdateChecks as $Group) {
            if (is_array($Group)) {
               $Name = ArrayValue('Name', $Group, '');
               $Type = ArrayValue('Type', $Group, '');
               $Version = ArrayValue('Type', $Group, '');
            } else {
               $Name = $Group->Name;
               $Type = $Group->Type;
               $Version = $Group->Version;
            }
            $OurGroupID = 0;
            if ($Name != '' && $Type != '' && $Version != '') {
               // Look for a matching AddonID & get it's current Version
               $Data = $SQL
                  ->Select('g.GroupID, v.Version')
                  ->From('Group g')
                  ->Join('GroupVersion v', 'g.CurrentGroupVersionID = v.GroupVersionID')
                  ->Join('GroupType t', 'g.GroupTypeID = t.GroupTypeID')
                  ->Where('g.Name', $Name)
                  ->Where('t.Label', $Type)
                  ->Get()
                  ->FirstRow();
               
               $OurVersion = $Version;
               if ($Data) {
                  $OurGroupID = $Data->GroupID;
                  $OurVersion = $Data->Version;
               }

               // Compare versions, and add to the response if they don't match
               if ($OurGroupID > 0 && $OurVersion != $Version) {
                  $Response[] = array(
                     'Name' => $Name,
                     'Type' => $Type,
                     'Version' => $OurVersion
                  );
               }
            }
               
            if ($UpdateCheckID > 0) {
               // Insert the addon into the UpdateAddon table
               $UpdateGroupID = $SQL->Insert('UpdateGroup', array(
                  'GroupID' => $OurGroupID,
                  'Name' => $Name,
                  'Type' => $Type,
                  'Version' => $Version
               ));
               
               // Insert the relation of this addon to this updatecheck
               if ($UpdateGroupID > 0) {
                  $SQL->Insert('UpdateCheckGroup', array(
                     'UpdateCheckID' => $UpdateCheckID,
                     'UpdateGroupID' => $UpdateGroupID
                  ));
               }
            }
         }
      }

      // Make sure the database connection is closed before exiting.
      $Database = Gdn::Database();
      $Database->CloseConnection();

      // Send messages back to the requesting application
      exit(json_encode(array(
         'messages' => '', // <-- These messages must be an array of GDN_Message table rows in associative array format.
         'response' => Gdn_Format::Serialize($Response)
      )));
      /*
       You can also send back messages to be injected into the remote application's pages. They should be in the following format:
      exit(json_encode(array(
         'messages' => Gdn_Format::Serialize(array(
            array(
               'Content' => '<div class="Info">This is a test!</div>',
               'AllowDismiss' => '1',
               'Enabled' => '1',
               'Application' => 'Dashboard',
               'Controller' => 'Settings',
               'Method' => 'Index',
               'AssetTarget' => 'Content'
            ),
            array(
               'Content' => '<div class="Info">This is another test!</div>',
               'AllowDismiss' => '0',
               'Enabled' => '1',
               'Application' => 'Dashboard',
               'Controller' => 'Base',
               'Method' => '',
               'AssetTarget' => 'Content'
            )
         )), // <-- These messages must be an array of GDN_Message table rows in associative array format.
         'response' => Gdn_Format::Serialize($Response)
      )));
       The Messages will be inserted into the remote databases GDN_Message table like this:
         'Content' => ArrayValue('Content', $Message, ''),
         'AllowDismiss' => ArrayValue('AllowDismiss', $Message, '1'),
         'Enabled' => ArrayValue('Enabled', $Message, '1'),
         'Application' => ArrayValue('Application', $Message, 'Dashboard'),
         'Controller' => ArrayValue('Controller', $Message, 'Settings'),
         'AssetTarget' => ArrayValue('AssetTarget', $Message, 'Content'),
      */
   }
   
   public function Find($GroupName = '') {
      // Find the requested plugin and redirect to it...
      $Data = $this->Database->SQL()
         ->Select('GroupID, Name')
         ->From('Group')
         ->Where('Name', $GroupName)
         ->Get()
         ->FirstRow();
      if ($Data) {
         Redirect('/group/'.$Data->GroupID.'/'.Gdn_Format::Url($Data->Name));
      } else {
         Redirect('/group/notfound/');
      }
   }
   
   private function _GetJsonString($FieldName, $Default = '') {
      $Value = ArrayValue($FieldName, $_POST, '');
      $Value = $Value == '' ? ArrayValue($FieldName, $_GET, '') : $Value;
      if (get_magic_quotes_gpc()) {
         if (is_array($Value)) {
            $Count = count($Value);
            for ($i = 0; $i < $Count; ++$i) {
               $Value[$i] = stripslashes($Value[$i]);
            }
         } else {
            $Value = stripslashes($Value);
         }
      }
      return $Value;     
   }
}