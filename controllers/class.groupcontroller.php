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
 * MessagesController handles displaying lists of conversations and conversation messages.
 */
class GroupController extends GroupsController {
   
   public $Uses = array('Form', 'GroupModel', 'GroupConversationsModel');
	public $Filter = 'all';
	public $Sort = 'recent';
	public $Version = '0'; // The version of Vanilla to filter to (0 is no filter)
   
   public function Initialize() {
      parent::Initialize();
      if ($this->Head) {
         $this->AddJsFile('jquery.js');
         $this->AddJsFile('jquery.livequery.js');
         $this->AddJsFile('jquery.form.js');
         $this->AddJsFile('jquery.popup.js');
         $this->AddJsFile('jquery.gardenhandleajaxform.js');
         $this->AddJsFile('global.js');
      }
   }
   
   public function NotFound() {
      $this->Render();
   }


   /**
    * Home Page
    */
   public function Index($GroupID = '', $GroupName = '', $Page = '') {
      list($Offset, $Limit) = OffsetLimit($Page, Gdn::Config('Garden.Search.PerPage', 20));
      if ($GroupID != '') {
         if (!is_numeric($Limit) || $Limit < 0)
            $Limit = 50;
         
         $this->Offset = $Offset;   
         if ($this->Offset < 0)
            $this->Offset = 0;
         
         $this->Group = $this->GroupModel->GetID($GroupID);
         if (!is_object($this->Group)) {
            $this->View = 'NotFound';
         } else {
            $this->AddCssFile('popup.css');
            $this->AddCssFile('fancyzoom.css');
            $this->AddJsFile('fancyzoom.js');
   			$this->AddJsFile('/js/library/jquery.gardenmorepager.js');
            $this->AddJsFile('group.js');
            $PictureModel = new Gdn_Model('GroupPicture');
            $this->PictureData = $PictureModel->GetWhere(array('GroupID' => $GroupID));
				$DiscussionModel = new DiscussionModel();
				$DiscussionModel->GroupID = $GroupID; // Let the model know we want to filter to a particular addon (we then hook into the model in the addons hooks file).
				$this->DiscussionData = $DiscussionModel->Get(0, 50);
            
            $this->View = 'group';
				$this->Title($this->Group->Name.' '.$this->Group->Version.' by '.$this->Group->InsertName);
         }
      } else {
			$this->View = 'browse';
			$this->Browse();
			return;
		/*
         $this->ApprovedData = $this->AddonModel->GetWhere(array('DateReviewed is not null' => ''), 'DateUpdated', 'desc', 5);
         $ApprovedIDs = ConsolidateArrayValuesByKey($this->ApprovedData->ResultArray(), 'AddonID');
         if (count($ApprovedIDs) > 0)
            $this->AddonModel->SQL->WhereNotIn('a.AddonID', $ApprovedIDs);
            
         $this->NewData = $this->AddonModel->GetWhere(FALSE, 'DateUpdated', 'desc', 5);
		*/
      }
  		$this->AddModule('GroupHelpModule');
		$this->Render();
   }
   
   /**
    * Add a new addon
    */
   public function Add() {
		$this->Permission('Groups.Group.Add');
		$this->AddJsFile('/js/library/jquery.autogrow.js');
		$this->AddJsFile('forms.js');
      
      $this->Form->SetModel($this->GroupModel);
      $GroupTypeModel = new Gdn_Model('GroupType');
      $this->TypeData = $GroupTypeModel->GetWhere(array('Visible' => '1'));
      
      if ($this->Form->AuthenticatedPostBack()) {         
         // If there were no errors, save the addon
         if ($this->Form->ErrorCount() == 0) {
            // Save the addon
            if ($GroupID !== FALSE) {
               // Redirect to the new addon
               $Name = $this->Form->GetFormValue('Name', '');
               Redirect('group/'.$GroupID.'/'.Gdn_Format::Url($Name));
            }
         }
      }
      $this->Render();      
   }
   
   public function Edit($GroupID = '') {
		$this->Permission('Groups.Group.Add');
		
		$this->AddJsFile('/js/library/jquery.autogrow.js');
		$this->AddJsFile('forms.js');
      
		$Session = Gdn::Session();
      $Group = $this->GroupModel->GetID($GroupID);
      if (!$Group)
         Redirect('dashboard/home/filenotfound');
         
      if ($Group->InsertUserID != $Session->UserID)
         $this->Permission('Groups.Group.Manage');
         
      $this->Form->SetModel($this->GroupModel);
      $this->Form->AddHidden('GroupID', $GroupID);
      $GroupTypeModel = new Gdn_Model('GroupType');
      $this->TypeData = $GroupTypeModel->GetWhere(array('Visible' => '1'));
      
      if ($this->Form->AuthenticatedPostBack() === FALSE) {
         $this->Form->SetData($Group);
      } else {
         if ($this->Form->Save() !== FALSE) {
            $Group = $this->AddonModel->GetID($GroupID);
            $this->StatusMessage = T("Your changes have been saved successfully.");
            $this->RedirectUrl = Url('/addon/'.$GroupID.'/'.Gdn_Format::Url($Group->Name));
         }
      }
      
      $this->Render();
   }
   
   public function NewVersion($GroupID = '') {
		$Session = Gdn::Session();
      $Group = $this->GroupModel->GetID($GroupID);
      if (!$Group)
         Redirect('dashboard/home/filenotfound');
         
      if ($Group->InsertUserID != $Group->UserID)
         $this->Permission('Groups.Group.Manage');

      $GroupVersionModel = new Gdn_Model('GroupVersion');
      $this->Form->SetModel($GroupVersionModel);
      $this->Form->AddHidden('GroupID', $GroupID);
      
      if ($this->Form->AuthenticatedPostBack()) {
         $Upload = new Gdn_Upload();
         try {
            // Validate the upload
            $TmpFile = $Upload->ValidateUpload('File');
            $Extension = pathinfo($Upload->GetUploadedFileName(), PATHINFO_EXTENSION);
            
            // Generate the target name
            $TargetFile = $Upload->GenerateTargetName(PATH_ROOT . DS . 'uploads', $Extension);
            $FileBaseName = pathinfo($TargetFile, PATHINFO_BASENAME);
            
            // Save the uploaded file
            $Upload->SaveAs(
               $TmpFile,
               PATH_ROOT . DS . 'uploads' . DS . $FileBaseName
            );
            $this->Form->SetFormValue('File', $FileBaseName);
				$this->Form->SetFormValue('TestedWith', 'Blank');

         } catch (Exception $ex) {
            $this->Form->AddError($ex->getMessage());
         }
         
         // If there were no errors, save the addonversion
         if ($this->Form->ErrorCount() == 0) {
            $NewVersionID = $this->Form->Save();
            if ($NewVersionID) {
               $this->AddonModel->Save(array('GroupID' => $GroupID, 'CurrentGroupVersionID' => $NewVersionID));
               $this->StatusMessage = T("New version saved successfully.");
               $this->RedirectUrl = Url('/group/'.$GroupID.'/'.Gdn_Format::Url($Group->Name));
            }
         }
      }
      $this->Render();      
   }   
   
   public function Approve($GroupID = '') {
      $this->Permission('Groups.Group.Manage');
      $Session = Gdn::Session();
      $Group = $this->Group = $this->GroupModel->GetID($GroupID);
      $VersionModel = new Gdn_Model('GroupVersion');
      if ($Group->DateReviewed == '') {
         $VersionModel->Save(array('GroupVersionID' => $Group->GroupVersionID, 'DateReviewed' => Gdn_Format::ToDateTime()));
      } else {
         $VersionModel->Update(array('DateReviewed' => null), array('GroupVersionID' => $Group->GroupVersionID));
      }
      
      Redirect('group/'.$GroupID.'/'.Gdn_Format::Url($Group->Name));
  }

   public function Delete($GroupID = '') {
      $Session = Gdn::Session();
      if (!$Session->IsValid())
         $this->Form->AddError('You must be authenticated in order to use this form.');

      $Group = $this->GroupModel->GetID($GroupID);
      if (!$Group)
         Redirect('dashboard/home/filenotfound');

      if ($Session->UserID != $Group->InsertUserID)
			$this->Permission('Groups.Group.Manage');

      $Session = Gdn::Session();
      if (is_numeric($GroupID)) 
         $this->GroupModel->Delete($GroupID);

      if ($this->_DeliveryType === DELIVERY_TYPE_ALL)
         Redirect(GetIncomingValue('Target', Gdn_Url::WebRoot()));

      $this->View = 'index';
      $this->Render();
   }

   /**
    * Add a comment to an addon
    */
   public function AddComment($GroupID = '') {
      $Render = TRUE;
      $this->Form->SetModel($this->GroupConversationsModel);
      $GroupID = $this->Form->GetFormValue('GroupID', $GroupID);

      if (is_numeric($GroupID) && $GroupID > 0)
         $this->Form->AddHidden('GroupID', $GroupID);
      
      if ($this->Form->AuthenticatedPostBack()) {
         $NewCommentID = $this->Form->Save();
         // Comment not saving for some reason - no errors reported
         if ($NewCommentID > 0) {
            // Update the Comment count
            $this->GroupModel->SetProperty($GroupID, 'CountComments', $this->GroupCommentModel->GetCount(array('GroupID' => $GroupID)));
            if ($this->DeliveryType() == DELIVERY_TYPE_ALL)
               Redirect('group/'.$GroupID.'/#Comment_'.$NewCommentID);
               
            $this->SetJson('CommentID', $NewCommentID);
            // If this was not a full-page delivery type, return the partial response
            // Load all new messages that the user hasn't seen yet (including theirs)
            $LastCommentID = $this->Form->GetFormValue('LastCommentID');
            if (!is_numeric($LastCommentID))
               $LastCommentID = $NewCommentID - 1;
            
            $Session = Gdn::Session();
            $this->Group = $this->GroupModel->GetID($GroupID);   
            $this->CommentData = $this->GroupConversationsModel->GetNew($GroupID, $LastCommentID);
            $this->View = 'comments';
         } else {
            // Handle ajax based errors...
            if ($this->DeliveryType() != DELIVERY_TYPE_ALL) {
               $this->StatusMessage = $this->Form->Errors();
            } else {
               $Render = FALSE;
               $this->Index($GroupID);
            }
         }
      }

      if ($Render)
         $this->Render();      
   }
   
   public function DeleteComment($CommentID = '') {
      $this->Permission('Groups.Comments.Manage');
      $Session = Gdn::Session();
      if (is_numeric($CommentID))
         $this->GroupConversationModel->Delete($CommentID);

      if ($this->_DeliveryType === DELIVERY_TYPE_ALL) {
         Redirect(Url(GetIncomingValue('Return', ''), TRUE));
      }
         
      $this->View = 'notfound';
      $this->Render();
   }
   
	public function Browse($FilterToType = '', $Sort = '', $VanillaVersion = '', $Page = '') {
		// Implement user prefs
		$Session = Gdn::Session();
		if ($Session->IsValid()) {
			if ($FilterToType != '') {
				$Session->SetPreference('Groups.FilterType', $FilterToType);
			}
			if ($VanillaVersion != '')
				$Session->SetPreference('Groups.FilterVanilla', $VanillaVersion);
			if ($Sort != '')
				$Session->SetPreference('Groups.Sort', $Sort);
			
			$FilterToType = $Session->GetPreference('Groups.FilterType', 'all');
			$PublicView = $Session->GetPreference('Groups.FilterPublic', '2');
			$Sort = $Session->GetPreference('Groups.Sort', 'recent');
		}
		
		if (!in_array($FilterToType, array('all', 'clans', 'guilds', 'communities')))
			$FilterToType = 'all';
		
		if ($Sort != 'popular')
			$Sort = 'recent';
		
		if (!in_array($PublicView, array('1', '2')))
			$PublicView = '2';
		
		$this->Version = $VanillaVersion;
			
		$this->Sort = $Sort;
		$this->AddJsFile('/js/library/jquery.gardenmorepager.js');
		$this->AddJsFile('browse.js');

      list($Offset, $Limit) = OffsetLimit($Page, Gdn::Config('Garden.Search.PerPage', 20));
		
      $this->Filter = $FilterToType;
		$Search = GetIncomingValue('Form/Keywords', '');
		$this->_BuildBrowseWheres($Search);
				
		$SortField = $Sort == 'recent' ? 'DateUpdated' : 'CountDownloads';
		$ResultSet = $this->GroupModel->GetWhere(FALSE, $SortField, 'desc', $Limit, $Offset);
		$this->SetData('SearchResults', $ResultSet, TRUE);
		$this->_BuildBrowseWheres($Search);
		$NumResults = $this->GroupModel->GetCount(FALSE);
		
		// Build a pager
		$PagerFactory = new Gdn_PagerFactory();
		$Pager = $PagerFactory->GetPager('Pager', $this);
		$Pager->MoreCode = '›';
		$Pager->LessCode = '‹';
		$Pager->ClientID = 'Pager';
		$Pager->Configure(
			$Offset,
			$Limit,
			$NumResults,
			'group/browse/'.$FilterToType.'/'.$Sort.'/'.$this->Version.'/%1$s/?Form/Keywords='.Gdn_Format::Url($Search)
		);
		$this->SetData('Pager', $Pager, TRUE);
      
      if ($this->_DeliveryType != DELIVERY_TYPE_ALL)
         $this->SetJson('MoreRow', $Pager->ToString('more'));
      
		$this->AddModule('GroupHelpModule');
		
		$this->Render();
	}
	
	private function _BuildBrowseWheres($Search = '') {
      if ($Search != '') {
         $this->GroupModel
            ->SQL
            ->BeginWhereGroup()
            ->Like('g.Name', $Search)
            ->OrLike('g.Description', $Search)
            ->EndWhereGroup();
		}
		
		if ($this->Version != 0)
			$this->GroupModel
				->SQL
				->Where('g.Vanilla2', $this->Version == '1' ? '0' : '1');
      
      if (in_array($this->Filter, array('clans', 'guilds', 'communities')))
			$this->GroupModel
				->SQL
				->Where('t.Label', substr($this->Filter, 0, -1));
	}
   
   public function AddPicture($GroupID = '') {
      $Session = Gdn::Session();
      if (!$Session->IsValid())
         $this->Form->AddError('You must be authenticated in order to use this form.');

      $Group = $this->GroupModel->GetID($GroupID);
      if (!$Group)
         Redirect('dashboard/home/filenotfound');

      if ($Session->UserID != $Group->InsertUserID)
			$this->Permission('Groups.Group.Manage');
         
      $GroupPictureModel = new Gdn_Model('GroupPicture');
      $this->Form->SetModel($GroupPictureModel);
      $this->Form->AddHidden('GroupID', $GroupID);
      if ($this->Form->AuthenticatedPostBack() === TRUE) {
         $UploadImage = new Gdn_UploadImage();
         try {
            // Validate the upload
            $TmpImage = $UploadImage->ValidateUpload('Picture');
            
            // Generate the target image name
            $TargetImage = $UploadImage->GenerateTargetName(PATH_ROOT . DS . 'uploads');
            $ImageBaseName = pathinfo($TargetImage, PATHINFO_BASENAME);
            
            // Save the uploaded image in large size
            $UploadImage->SaveImageAs(
               $TmpImage,
               PATH_ROOT . DS . 'uploads' . DS . 'ao'.$ImageBaseName,
               1000,
               700
            );

            // Save the uploaded image in thumbnail size
            $ThumbSize = 150;
            $UploadImage->SaveImageAs(
               $TmpImage,
               PATH_ROOT . DS . 'uploads' . DS . 'at'.$ImageBaseName,
               $ThumbSize,
               $ThumbSize,
               TRUE
            );
            
         } catch (Exception $ex) {
            $this->Form->AddError($ex->getMessage());
         }
         // If there were no errors, insert the picture
         if ($this->Form->ErrorCount() == 0) {
            $GroupPictureModel = new Gdn_Model('GroupPicture');
            $GroupPictureID = $GroupPictureModel->Insert(array('GroupID' => $GroupID, 'File' => $ImageBaseName));
         }

         // If there were no problems, redirect back to the addon
         if ($this->Form->ErrorCount() == 0) 
				Redirect('group/'.$GroupID);
      }
      $this->Render();
   }
   
   public function DeletePicture($GroupPictureID = '') {
      $this->Permission('Groups.Group.Manage');
      $GroupPictureModel = new Gdn_Model('GroupPicture');
      $Picture = $GroupPictureModel->GetWhere(array('GroupPictureID' => $GroupPictureID));
      if ($Picture) {
         @unlink(PATH_ROOT . DS . 'uploads' . DS . 'ao'.$Picture->Name);
         @unlink(PATH_ROOT . DS . 'uploads' . DS . 'at'.$Picture->Name);
         @unlink(PATH_ROOT . DS . 'uploads' . DS . 'ai'.$Picture->Name);
         $GroupPictureModel->Delete(array('GroupPictureID' => $GroupPictureID));
      }
      if ($this->_DeliveryType === DELIVERY_TYPE_ALL)
         Redirect(GetIncomingValue('Return', Gdn_Url::WebRoot()));

      $this->ControllerName = 'Home';
      $this->View = 'FileNotFound';
      $this->Render();
   }
   
   public function Icon($GroupID = '') {
      $Session = Gdn::Session();
      if (!$Session->IsValid())
         $this->Form->AddError('You must be authenticated in order to use this form.');

      $Group = $this->GroupModel->GetID($GroupID);
      if (!$Group)
         Redirect('dashboard/home/filenotfound');

      if ($Session->UserID != $Group->InsertUserID)
			$this->Permission('Groups.Group.Manage');

      $this->Form->SetModel($this->GroupModel);
      $this->Form->AddHidden('GroupID', $GroupID);
      if ($this->Form->AuthenticatedPostBack() === TRUE) {
         $UploadImage = new Gdn_UploadImage();
         try {
            // Validate the upload
            $TmpImage = $UploadImage->ValidateUpload('Icon');
            
            // Generate the target image name
            $TargetImage = $UploadImage->GenerateTargetName(PATH_ROOT . DS . 'uploads');
            $ImageBaseName = pathinfo($TargetImage, PATHINFO_BASENAME);
            
            // Save the uploaded icon
            $UploadImage->SaveImageAs(
               $TmpImage,
               PATH_ROOT . DS . 'uploads' . DS . 'ai'.$ImageBaseName,
               50,
               50
            );

         } catch (Exception $ex) {
            $this->Form->AddError($ex->getMessage());
         }
         // If there were no errors, remove the old picture and insert the picture
         if ($this->Form->ErrorCount() == 0) {
            $Group = $this->GroupModel->GetID($GroupID);
            if ($Group->Icon != '')
               @unlink(PATH_ROOT . DS . 'uploads' . DS . 'ai'.$Group->Icon);
               
            $this->GroupModel->Save(array('GroupID' => $GroupID, 'Icon' => $ImageBaseName));
         }

         // If there were no problems, redirect back to the addon
         if ($this->Form->ErrorCount() == 0)
            Redirect('group/'.$GroupID);
      }
      $this->Render();
   }
}