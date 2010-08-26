<?php if (!defined('APPLICATION')) exit();
/*
Copyright 2008, 2009 Vanilla Forums Inc.
This file is part of Garden.
Garden is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
Garden is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with Garden.  If not, see <http://www.gnu.org/licenses/>.
Contact Vanilla Forums Inc. at support [at] vanillaforums [dot] com
*/

// @ADDON: Use this file to construct tables and views necessary for your application.
// @ADDON: There are some examples below to get you started.

if (!isset($Drop))
   $Drop = FALSE;
   
if (!isset($Explicit))
   $Explicit = TRUE;

$SQL = $Database->SQL();
$Construct = $Database->Structure();

$Construct->Table('GroupType')
   ->PrimaryKey('GroupTypeID')
   ->Column('Label', 'varchar(50)')
   ->Column('Visible', 'tinyint(1)', '1')
   ->Set($Explicit, $Drop);

   
// Group Types
if ($SQL->Select()->From('GroupType')->Get()->NumRows() == 0) {
   $SQL->Insert('GroupType', array('Label' => 'Clan', 'Visible' => '1'));
   $SQL->Insert('GroupType', array('Label' => 'Guild', 'Visible' => '1'));
   $SQL->Insert('GroupType', array('Label' => 'Group', 'Visible' => '0'));
   $SQL->Insert('GroupType', array('Label' => 'GFX Organization', 'Visible' => '0'));
   $SQL->Insert('GroupType', array('Label' => 'Community', 'Visible' => '1'));
}

$Construct->Table('Group')
   ->PrimaryKey('GroupID')
   ->Column('GroupTypeID', 'int', FALSE, 'key')
   ->Column('OwnerUserID', 'int', FALSE, 'key')
   ->Column('Name', 'varchar(100)')
   ->Column('Icon', 'varchar(200)', TRUE)
   ->Column('Description', 'text', TRUE)
   ->Column('Requirements', 'text', FALSE)
   ->Column('UseRanks', 'tinyint(1)', '0')
   ->Column('GroupComments', 'int', '0')
   ->Column('GroupMembers', 'int', '1')
   ->Column('GroupFollows', 'int', '0')
   ->Column('Visible', 'tinyint(1)', '1')
   ->Column('Public', 'tinyint(1)', '1')
   ->Column('DateCreated', 'datetime')
   ->Set($Explicit, $Drop);

/*
$Construct->Table('AddonComment')
   ->PrimaryKey('AddonCommentID')
   ->Column('AddonID', 'int', FALSE, 'key')
   ->Column('InsertUserID', 'int', FALSE, 'key')
   ->Column('Body', 'text')
   ->Column('Format', 'varchar(20)', TRUE)
   ->Column('DateInserted', 'datetime')
   ->Set($Explicit, $Drop);
*/

$Construct->Table('GroupMembers')
   ->Column('GroupID', 'int', FALSE, 'key')
   ->Column('MemberCount', 'int', '1', 'key')
   ->Column('FolowerCount','int', '0', 'key')
   ->Column('Members', 'text', TRUE)
   ->Column('Followers',  'text', TRUE)
   ->Column('Ranks', 'text', TRUE)
   ->Set($Explicit, $Drop);

$Construct->Table('GroupPicture')
   ->PrimaryKey('GroupPictureID')
   ->Column('GroupID', 'int', FALSE, 'key')
   ->Column('File', 'varchar(200)')
   ->Column('DateInserted', 'datetime')
   ->Set($Explicit, $Drop);

$Construct->Table('Join')
   ->PrimaryKey('JoinID')
   ->Column('GroupID', 'int', FALSE, 'key')
   ->Column('DateJoined', 'datetime')
   ->Column('RemoteIp', 'varchar(50)', TRUE)
   ->Set($Explicit, $Drop);


   
$PermissionModel = Gdn::PermissionModel();
$PermissionModel->Database = $Database;
$PermissionModel->SQL = $SQL;

// @ADDON: Define some global addon permissions.
$PermissionModel->Define(array(
   'Groups.Group.Add',
   'Groups.Group.Manage',
   'Groups.Comments.Manage'
   ));

// @ADDON: Set the intial member permissions.
$PermissionModel->Save(array(
   'RoleID' => 8,
   'Groups.Group.Add' => 1
   ));
   
// @ADDON: Set the initial administrator permissions.
$PermissionModel->Save(array(
   'RoleID' => 16,
   'Groups.Group.Add' => 1,
   'Groups.Details.Manage' => 1,
	'Groups.Members.Manage' => 1,
   'Groups.Comments.Manage' => 1
   ));
   
// @FIXME check used RoleIDs
   $PermissionModel->Save(array(
   'RoleID' => 17,
   'Groups.Comment.Manage' => 1
   ));

// @ADDON: Make sure that User.Permissions is blank so new permissions for users get applied.
$SQL->Update('User', array('Permissions' => ''))->Put();

// Insert some activity types
///  %1 = ActivityName
///  %2 = ActivityName Possessive
///  %3 = RegardingName
///  %4 = RegardingName Possessive
///  %5 = Link to RegardingName's Wall
///  %6 = his/her
///  %7 = he/she
///  %8 = RouteCode & Route

//  X created a group
if ($SQL->GetWhere('ActivityType', array('Name' => 'AddGroup'))->NumRows() == 0)
   $SQL->Insert('ActivityType', array('AllowComments' => '1', 'Name' => 'AddGroup', 'FullHeadline' => '%1$s started a new %8$s.', 'ProfileHeadline' => '%1$s started a new %8$s.', 'RouteCode' => 'group', 'Public' => '1'));
   
//  X joined a group
if ($SQL->GetWhere('ActivityType', array('Name' => 'JoinGroup'))->NumRows() == 0)
   $SQL->Insert('ActivityType', array('AllowComments' => '1', 'Name' => 'JoinGroup', 'FullHeadline' => '%1$s joined a %8$s.', 'ProfileHeadline' => '%1$s joined a %8$s.', 'RouteCode' => 'group', 'Public' => '1'));
   
   //  X followed a group
if ($SQL->GetWhere('ActivityType', array('Name' => 'FollowGroup'))->NumRows() == 0)
   $SQL->Insert('ActivityType', array('AllowComments' => '1', 'Name' => 'FollowGroup', 'FullHeadline' => '%1$s followed a %8$s.', 'ProfileHeadline' => '%1$s followed a %8$s.', 'RouteCode' => 'group', 'Public' => '1'));

/*
// @ADDON: People's comments on addons // made obsolete
if ($SQL->GetWhere('ActivityType', array('Name' => 'AddonComment'))->NumRows() == 0)
   $SQL->Insert('ActivityType', array('AllowComments' => '0', 'Name' => 'AddonComment', 'FullHeadline' => '%1$s commented on %4$s %8$s.', 'ProfileHeadline' => '%1$s commented on %4$s %8$s.', 'RouteCode' => 'addon', 'Notify' => '1', 'Public' => '1'));

// @ADDON: People mentioning others in addon comments // made obsolete
if ($SQL->GetWhere('ActivityType', array('Name' => 'AddonCommentMention'))->NumRows() == 0)
   $SQL->Insert('ActivityType', array('AllowComments' => '0', 'Name' => 'AddonCommentMention', 'FullHeadline' => '%1$s mentioned %3$s in a %8$s.', 'ProfileHeadline' => '%1$s mentioned %3$s in a %8$s.', 'RouteCode' => 'comment', 'Notify' => '1', 'Public' => '0'));
*/
/*
// @ADDON: People adding new language definitions
if ($SQL->GetWhere('ActivityType', array('Name' => 'AddUserLanguage'))->NumRows() == 0)
   $SQL->Insert('ActivityType', array('AllowComments' => '0', 'Name' => 'AddUserLanguage', 'FullHeadline' => '%1$s added a new %8$s.', 'ProfileHeadline' => '%1$s added a new %8$s.', 'RouteCode' => 'language', 'Notify' => '0', 'Public' => '1'));

// @ADDON: People editing language definitions
if ($SQL->GetWhere('ActivityType', array('Name' => 'EditUserLanguage'))->NumRows() == 0)
   $SQL->Insert('ActivityType', array('AllowComments' => '0', 'Name' => 'EditUserLanguage', 'FullHeadline' => '%1$s edited a %8$s.', 'ProfileHeadline' => '%1$s edited a %8$s.', 'RouteCode' => 'language', 'Notify' => '0', 'Public' => '1'));

// @ADDON: Contains list of available languages for translating
$Construct->Table('Language')
   ->PrimaryKey('LanguageID')
   ->Column('Name', 'varchar(255)')
   ->Column('Code', 'varchar(10)')
   ->Column('InsertUserID', 'int', FALSE, 'key')
   ->Column('DateInserted', 'datetime')
   ->Column('UpdateUserID', 'int', TRUE)
   ->Column('DateUpdated', 'datetime', TRUE)
   ->Set($Explicit, $Drop);
   
// @ADDON: Contains relationships of who owns translations and who can edit translations (owner decides who can edit)
$Construct->Table('UserLanguage')
   ->PrimaryKey('UserLanguageID')
   ->Column('UserID', 'int', FALSE, 'key')
   ->Column('LanguageID', 'int', FALSE, 'key')
   ->Column('Owner', 'tinyint(1)', '0')
   ->Column('CountTranslations', 'int', '0') // The number of translations this UserLanguage contains
   ->Column('CountDownloads', 'int', '0')
   ->Column('CountLikes', 'int', '0')
   ->Set($Explicit, $Drop);

// @ADDON: Contains individual translations as well as source codes
$Construct->Table('Translation')
   ->PrimaryKey('TranslationID')
   ->Column('UserLanguageID', 'int', FALSE, 'key')
   ->Column('SourceTranslationID', 'int', TRUE, 'key') // This is the related TranslationID where LanguageID = 1 (the source codes for translations)
   ->Column('Application', 'varchar(100)', TRUE)
   ->Column('Value', 'text')
   ->Column('InsertUserID', 'int', FALSE, 'key')
   ->Column('DateInserted', 'datetime')
   ->Column('UpdateUserID', 'int', TRUE)
   ->Column('DateUpdated', 'datetime', TRUE)
   ->Set($Explicit, $Drop);

// @ADDON: Contains records of when actions were performed on userlanguages (ie. it is
// @ADDON: downloaded or "liked"). These values are aggregated in
// @ADDON: UserLanguage.CountLikes and UserLanguage.CountDownloads for faster querying,
// @ADDON: but saved here for reporting.
$Construct->Table('UserLanguageAction')
   ->PrimaryKey('UserLanguageActionID')
   ->Column('UserLanguageID', 'int', FALSE, 'key')
   ->Column('Action', 'varchar(20)') // The action being performed (ie. "download" or "like")
   ->Column('InsertUserID', 'int', TRUE, 'key') // Allows nulls because you do not need to be authenticated to download a userlanguage
   ->Column('DateInserted', 'datetime')
   ->Set($Explicit, $Drop);

// @ADDON: Make sure the default "source" translation exists
if ($SQL->GetWhere('Language', array('LanguageID' => 1))->NumRows() == 0)
   $SQL->Insert('Language', array('Name' => 'Source Codes', 'Code' => 'SOURCE', 'InsertUserID' => 1, 'DateInserted' => '2009-10-19 12:00:00'));

// @ADDON: Mark (UserID 1) owns the source translation
if ($SQL->GetWhere('UserLanguage', array('LanguageID' => 1, 'UserID' => 1))->NumRows() == 0)
   $SQL->Insert('UserLanguage', array('LanguageID' => 1, 'UserID' => 1, 'Owner' => '1'));
*/

/*
   @ADDON: Apr 26th, 2010
   @ADDON: Changed all "enum" fields representing "bool" (0 or 1) to be tinyint.
   @ADDON: For some reason mysql makes 0's "2" during this change. Change them back to "0".
*/
if (!$Construct->CaptureOnly) {
	$SQL->Query("update GDN_GroupType set Visible = '0' where Visible = '2'");

	$SQL->Query("update GDN_Group set Visible = '0' where Visible = '2'");
	$SQL->Query("update GDN_Group set Public = '0' where Public = '2'");

	$SQL->Query("update GDN_UserLanguage set Owner = '0' where Owner = '2'");
}

// Add GroupID to discussions allowing for group specific discusisons.
// @ADDON: Add AddonID column to discussion table for allowing discussions on addons.
$Construct->Table('Discussion')
   ->Column('GroupID', 'int', NULL)
   ->Set();

   /*
// @ADDON: Insert all of the existing comments into a new discussion for each addon
$Construct->Table('GroupComment');
$GroupCommentExists = $Construct->TableExists();
$Construct->Reset();

if ($GroupCommentExists) {
   if ($SQL->Query('select GroupCommentID from GDN_GroupComment')->NumRows() > 0) {
      // @ADDON: Create discussions for addons with comments
      $SQL->Query("insert into GDN_Discussion
      (GroupID, InsertUserID, UpdateUserID, LastCommentID, Name, Body, Format,
      CountComments, DateInserted, DateUpdated, DateLastComment, LastCommentUserID)
      select distinct g.GroupID, g.InsertUserID, g.UpdateuserID, 0, g.Name, g.Name,
      gc.Format, g.CountComments, g.DateInserted, g.DateUpdated, g.DateUpdated, 0
      from GDN_Group g join GDN_GroupComment gc on g.GroupID = gc.GroupID");

      // @ADDON: Copy the comments across to the comment table
      $SQL->Query("insert into GDN_Comment
      (DiscussionID, InsertUserID, Body, Format, DateInserted)
      select d.DiscussionID, gc.InsertUserID, gc.Body, gc.Format, gc.DateInserted
      from GDN_Discussion d join GDN_GroupComment gc on d.GroupID = gc.GroupID");

      // @ADDON: Update the LastCommentID
      $SQL->Query("update GDN_Discussion d
         join (
           select DiscussionID, max(CommentID) as LastCommentID
           from GDN_Comment
           group by DiscussionID
         ) c
           on d.DiscussionID = c.DiscussionID
         set d.LastCommentID = c.LastCommentID");
      
      // @ADDON: Update the LastCommentUserID
      $SQL->Query("update GDN_Discussion d
         join GDN_Comment c on d.LastCommentID = c.CommentID
         set d.LastCommentUserID = c.InsertUserID");
      
      
      // @ADDON: Delete the comments from the addon comments table
      $SQL->Query('truncate table GDN_GroupComment');
   }
}
*/