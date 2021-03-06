<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2015 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2015. All rights reserved".
     ********************************************************************************/

    /**
     * Helper class to help with user interface manipulation for FileModel related actions.
     */
    class FileModelDisplayUtil
    {
        /**
         * Given a file size in bytes, convert to a human readable form.
         * @param integer $size
         * @return string $content
         */
        public static function convertSizeToHumanReadableAndGet($size)
        {
            assert('is_numeric($size)');
            if ($size == 0)
            {
                return '0';
            }
            if ($size < 1048576)
            {
                return round($size / 1024, 2) . 'KB';
            }
            elseif ($size < 1073741824)
            {
                return round($size / 1048576, 2) . 'MB';
            }
            else
            {
                return round($size / 1073741824, 2) . 'GB';
            }
        }

        /**
         * @param $model
         * @param $fileModel
         * @return string
         */
        public static function renderDownloadLinkContentByRelationModelAndFileModel($model, $fileModel)
        {
            assert('$model instanceof RedBeanModel');
            assert('$fileModel instanceof FileModel');
            $content = null;
            $content .= '<span class="ui-icon ui-icon-document" style="display:inline-block;"></span>';
            $content .= ZurmoHtml::link(
                    Yii::app()->format->text($fileModel->name),
                    static::resolveDownloadUrlByRelationModelIdAndRelationModelClassNameAndFileIdAndFileName($model->id,
                                                                                                        get_class($model),
                                                                                                        $fileModel->id));
            return $content;
        }

        /**
         * @param $modelId
         * @param $modelClass
         * @param $fileId
         * @return mixed
         */
        public static function resolveDownloadUrlByRelationModelIdAndRelationModelClassNameAndFileIdAndFileName($modelId, $modelClass, $fileId)
        {
            return Yii::app()->createUrl('zurmo/fileModel/download/', array('modelId' => $modelId,
                                                                            'modelClassName' => $modelClass,
                                                                            'id' => $fileId));
        }

        /**
         * @param $model
         * @param string $filesRelationName
         * @param bool $showHeaderLabel
         * @return null|string
         */
        public static function renderFileDataDetailsWithDownloadLinksContent($model, $filesRelationName, $showHeaderLabel = false)
        {
            $content = null;
            if ($model->{$filesRelationName}->count() > 0)
            {
                $content .= '<ul class="attachments">';
                if ($showHeaderLabel)
                {
                    $content .= '<li><strong>' . Zurmo::t('ZurmoModule', 'Attachments'). '</strong></li>';
                }
                foreach ($model->{$filesRelationName} as $fileModel)
                {
                    $content .= '<li><span class="icon-attachment"></span>' .
                                FileModelDisplayUtil::renderDownloadLinkContentByRelationModelAndFileModel($model, $fileModel) . '</li>';
                }
                $content .= '</ul>';
            }
            return $content;
        }
    }
?>