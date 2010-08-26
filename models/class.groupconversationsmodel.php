<?php if (!defined('APPLICATION')) exit();
/*
Copyright 2008, 2009 Vanilla Forums Inc.
This file is part of Garden.
Garden is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
Garden is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with Garden.  If not, see <http://www.gnu.org/licenses/>.
Contact Vanilla Forums Inc. at support [at] vanillaforums [dot] com
*/

class GroupConversationsModel extends Gdn_Model {
   public function __construct() {
      parent::__construct('GroupComment');
   }
   
   public function AddonCommentQuery() {
      $this->SQL->Select('c.*')
         ->Select('iu.Name', '', 'InsertName')
         ->Select('iu.Photo', '', 'InsertPhoto')
         ->From('GroupComment c')
         ->Join('User iu', 'c.InsertUserID = iu.UserID', 'left');
   }
   
   public function Get($GroupID, $Limit, $Offset = 0) {
      $this->GroupCommentQuery();
      $this->FireEvent('BeforeGet');
      return $this->SQL
         ->Where('c.GroupID', $GroupID)
         ->OrderBy('c.DateInserted', 'asc')
         ->Limit($Limit, $Offset)
         ->Get();
   }
   
   public function GetID($GroupCommentID) {
      $this->CommentQuery();
      return $this->SQL
         ->Where('c.GroupCommentID', $GroupCommentID)
         ->Get()
         ->FirstRow();
   }
   
   public function GetNew($GroupID, $LastCommentID) {
      $this->CommentQuery(); 
      return $this->SQL
         ->Where('c.GroupID', $GroupID)
         ->Where('c.GroupCommentID >', $LastCommentID)
         ->OrderBy('c.DateInserted', 'asc')
         ->Get();
   }
   
   /// <summary>
   /// Returns the offset of the specified comment in it's related discussion.
   /// </summary>
   /// <param name="CommentID" type="int">
   /// The comment id for which the offset is being defined.
   /// </param>
   public function GetOffset($GroupCommentID) {
      return $this->SQL
         ->Select('c2.GroupCommentID', 'count', 'CountComments')
         ->From('GroupComment c')
         ->Join('Group g', 'c.GroupID = g.GroupID')
         ->Join('GroupComment c2', 'g.GroupID = c2.GroupID')
         ->Where('c2.GroupCommentID <=', $GroupCommentID)
         ->Where('c.GroupCommentID', $GroupCommentID)
         ->Get()
         ->FirstRow()
         ->CountComments;
   }
   
   public function Save($FormPostValues) {
      $Session = Gdn::Session();
      
      // Define the primary key in this model's table.
      $this->DefineSchema();
      
      // Add & apply any extra validation rules:      
      $this->Validation->ApplyRule('Body', 'Required');
      $MaxCommentLength = Gdn::Config('Vanilla.Comment.MaxLength');
      if (is_numeric($MaxCommentLength) && $MaxCommentLength > 0) {
         $this->Validation->SetSchemaProperty('Body', 'Length', $MaxCommentLength);
         $this->Validation->ApplyRule('Body', 'Length');
      }
      
      $GroupCommentID = ArrayValue('GroupCommentID', $FormPostValues);
      $GroupCommentID = is_numeric($GroupCommentID) && $GroupCommentID > 0 ? $GroupCommentID : FALSE;
      $Insert = $GroupCommentID === FALSE;
      if ($Insert)
         $this->AddInsertFields($FormPostValues);
      else
         $this->AddUpdateFields($FormPostValues);
      
      // Validate the form posted values
      if ($this->Validate($FormPostValues, $Insert)) {
         // If the post is new
         $Fields = $this->Validation->SchemaValidationFields();
         $Fields = RemoveKeyFromArray($Fields, $this->PrimaryKey);
         $GroupID = ArrayValue('GroupID', $Fields);
         if ($Insert === FALSE) {
            $this->SQL->Put($this->Name, $Fields, array('GroupCommentID' => $GroupCommentID));
         } else {
            // Make sure that the comments get formatted in the method defined by Garden
            $Fields['Format'] = Gdn::Config('Garden.InputFormatter', '');
            $GroupCommentID = $this->SQL->Insert($this->Name, $Fields);
            
            // Notify any users who were mentioned in the comment
            $Usernames = GetMentions($Fields['Body']);
            $UserModel = Gdn::UserModel();
            foreach ($Usernames as $Username) {
               $User = $UserModel->GetByUsername($Username);
               if ($User && $User->UserID != $Session->UserID) {
                  AddActivity(
                     $Session->UserID,
                     'AddonCommentMention',
                     '',
                     $User->UserID,
                     'group/'.$GroupID.'/#Comment_'.$GroupCommentID
                  );
               }
            }
         }
         // Record user-comment activity
         if ($GroupID !== FALSE)
            $this->RecordActivity($GroupID, $Session->UserID, $GroupCommentID);
      }
      return $GroupCommentID;
   }
      
   public function RecordActivity($GroupID, $ActivityUserID, $GroupCommentID) {
      // Get the author of the discussion
      $GroupModel = new GroupModel();
      $Group = $GroupModel->GetID($GroupID);
      if ($Group->InsertUserID != $ActivityUserID) 
         AddActivity(
            $ActivityUserID,
            'AddonComment',
            '',
            $Group->InsertUserID,
            'Group/'.$GroupID.'/'.Gdn_Format::Url($Group->Name).'/#Comment_'.$GroupCommentID
         );
   }
   
   public function Delete($GroupCommentID) {
      $this->SQL->Delete('GroupComment', array('GroupCommentID' => $GroupCommentID));
      return TRUE;
   }   
}