<?php if (!defined('APPLICATION')) exit();
/*
Copyright 2008, 2009 Vanilla Forums Inc.
This file is part of Garden.
Garden is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
Garden is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with Garden.  If not, see <http://www.gnu.org/licenses/>.
Contact Vanilla Forums Inc. at support [at] vanillaforums [dot] com
*/

class GroupModel extends Gdn_Model {
   public function __construct() {
      parent::__construct('Group');
   }
   
   
   public function GroupQuery() {
      $this->SQL
         ->Select('g.*')
         ->Select('t.Label', '', 'Type')
         ->Select('m.MemberCount', '', 'NumMembers')
         ->Select('m.FollowCount', '', 'NumFollows')
         ->Select('ou.Name', '', 'OwnerName')
         ->From('Group g')
         ->Join('GroupMembers m', 'g.GroupID = m.GroupID')
         ->Join('GroupType t', 'g.GroupTypeID = t.GroupTypeID')
         ->Join('User u', 'g.OwnerUserID = u.UserID')
         ->Where('g.Visible', '1');
   }
   
   public function Get($Offset = '0', $Limit = '', $Wheres = '') {
      if ($Limit == '') 
         $Limit = Gdn::Config('Vanilla.Discussions.PerPage', 50);

      $Offset = !is_numeric($Offset) || $Offset < 0 ? 0 : $Offset;
      
      $this->GroupQuery();
      
      if (is_array($Wheres))
         $this->SQL->Where($Wheres);

      return $this->SQL
         ->Limit($Limit, $Offset)
         ->Get();
   }
   
   public function GetWhere($Where = FALSE, $OrderFields = '', $OrderDirection = 'asc', $Limit = FALSE, $Offset = FALSE) {
      $this->GroupQuery();
      
      if ($Where !== FALSE)
         $this->SQL->Where($Where);

      if ($OrderFields != '')
         $this->SQL->OrderBy($OrderFields, $OrderDirection);

      if ($Limit !== FALSE) {
         if ($Offset == FALSE || $Offset < 0)
            $Offset = 0;

         $this->SQL->Limit($Limit, $Offset);
      }

      return $this->SQL->Get();
   }
   
   public function GetCount($Wheres = '') {
      if (!is_array($Wheres))
         $Wheres = array();
         
      $Wheres['g.Visible'] = '1';
      return $this->SQL
         ->Select('g.GroupID', 'count', 'CountGroups')
         ->From('Group g')
         ->Join('GroupType t', 'g.GroupTypeID = t.GroupTypeID')
         ->Where($Wheres)
         ->Get()
         ->FirstRow()
         ->CountGroups;
   }

   public function GetID($GroupID, $Wheres = '') {
      $this->GroupQuery();
      if (is_array($Wheres))
         $this->SQL->Where($Wheres);

      return $this->SQL
         ->Where('g.GroupID', $GroupID)
         ->Get()
         ->FirstRow();
   }
   
   public function Save($FormPostValues, $FileName = '') {
      $Session = Gdn::Session();
      
      // Define the primary key in this model's table.
      $this->DefineSchema();
      
      // Add & apply any extra validation rules:
      if (array_key_exists('Description', $FormPostValues))
         $this->Validation->ApplyRule('Description', 'Required');

      if (array_key_exists('Version', $FormPostValues))
         $this->Validation->ApplyRule('Version', 'Required');
/*
      if (array_key_exists('TestedWith', $FormPostValues))
         $this->Validation->ApplyRule('TestedWith', 'Required');
*/      
      // Get the ID from the form so we know if we are inserting or updating.
      $GroupID = ArrayValue('GroupID', $FormPostValues, '');
      $Insert = $GroupID == '' ? TRUE : FALSE;
      
      if ($Insert) {
         if(!array_key_exists('Vanilla2', $FormPostValues))
            $FormPostValues['Vanilla2'] = '0';
         
         unset($FormPostValues['GroupID']);
         $this->AddInsertFields($FormPostValues);
      } else if (!array_key_exists('Vanilla2', $FormPostValues)) {
         $Tmp = $this->GetID($GroupID);
         $FormPostValues['Vanilla2'] = $Tmp->Vanilla2 ? '1' : '0';
      }
      $this->AddUpdateFields($FormPostValues);
      // Validate the form posted values
      if ($this->Validate($FormPostValues, $Insert)) {
         $Fields = $this->Validation->SchemaValidationFields(); // All fields on the form that relate to the schema
         $GroupID = intval(ArrayValue('GroupID', $Fields, 0));
         $Fields = RemoveKeyFromArray($Fields, 'GroupID'); // Remove the primary key from the fields for saving
         $Group = FALSE;
         $Activity = 'EditAddon';
         if ($GroupID > 0) {
            $this->SQL->Put($this->Name, $Fields, array($this->PrimaryKey => $GroupID));
         } else {
            $GroupID = $this->SQL->Insert($this->Name, $Fields);
            $Activity = 'AddAddon';
         }
         // Save the version
         if ($GroupID > 0 && $FileName != '') {
            // Save the addon file & version
            $GroupVersionModel = new Gdn_Model('GroupVersion');
            $GroupVersionID = $GroupVersionModel->Save(array(
               'GroupID' => $GroupID,
               'File' => $FileName,
               'Version' => ArrayValue('Version', $FormPostValues, ''),
               'TestedWith' => ArrayValue('TestedWith', $FormPostValues, 'Empty'),
               'DateInserted' => Gdn_Format::ToDateTime()
            ));
            // Mark the new addon file & version as the current version
            $this->SQL->Put($this->Name, array('CurrentGroupVersionID' => $GroupVersionID), array($this->PrimaryKey => $GroupID));
         }
         
         if ($GroupID > 0) {
            $Group = $this->GetID($GroupID);

            // Record Activity
            AddActivity(
               $Session->UserID,
               $Activity,
               '',
               '',
               '/Group/'.$GroupID.'/'.Gdn_Format::Url($Group->Name)
            );
         }
      }
      if (!is_numeric($GroupID))
         $GroupID = FALSE;
         
      return count($this->ValidationResults()) > 0 ? FALSE : $GroupID;
   }
   
   public function SetProperty($GroupID, $Property, $ForceValue = FALSE) {
      if ($ForceValue !== FALSE) {
         $Value = $ForceValue;
      } else {
         $Value = '1';
         $Group = $this->GetID($GroupID);
         $Value = ($Group->$Property == '1' ? '0' : '1');
      }
      $this->SQL
         ->Update('Group')
         ->Set($Property, $Value)
         ->Where('GroupID', $GroupID)
         ->Put();
      return $Value;
   }
      
   public function Delete($GroupID) {
      $this->SetProperty($GroupID, 'Visible', '0');
   }
}