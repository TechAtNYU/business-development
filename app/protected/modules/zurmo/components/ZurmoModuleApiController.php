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
     * Zurmo Modules api controllers
     * should extend this class to provide generic functionality
     * that is applicable to all standard api modules.
     */
    abstract class ZurmoModuleApiController extends ZurmoBaseController
    {
        const RIGHTS_FILTER_PATH = 'application.modules.api.utils.ApiRightsControllerFilter';

        public function filters()
        {
            $filters = array(
                'apiRequest'
            );
            return array_merge($filters, parent::filters());
        }

        public function filterApiRequest($filterChain)
        {
            try
            {
                $filterChain->run();
            }
            catch (Exception $e)
            {
                $resultClassName = Yii::app()->apiRequest->getResultClassName();
                $result = new $resultClassName(ApiResponse::STATUS_FAILURE, null, $e->getMessage(), null);
                Yii::app()->apiHelper->sendResponse($result);
            }
        }

        /**
         * Get model and send response
         * @throws ApiException
         */
        public function actionRead()
        {
            $params = Yii::app()->apiRequest->getParams();
            if (!isset($params['id']))
            {
                $message = Zurmo::t('ZurmoModule', 'The ID specified was invalid.');
                throw new ApiException($message);
            }
            $result    =  $this->processRead((int)$params['id']);
            Yii::app()->apiHelper->sendResponse($result);
        }

        /**
         * Get array or models and send response
         */
        public function actionList()
        {
            $params = Yii::app()->apiRequest->getParams();
            $result    =  $this->processList($params);
            Yii::app()->apiHelper->sendResponse($result);
        }

        /**
         * Get array or models and send response
         */
        public function actionListAttributes()
        {
            $params = Yii::app()->apiRequest->getParams();
            $result    =  $this->processListAttributes($params);
            Yii::app()->apiHelper->sendResponse($result);
        }

        /**
         * Get array or models and send response
         */
        public function actionSearch()
        {
            $params = Yii::app()->apiRequest->getParams();
            $result    =  $this->processSearch($params);
            Yii::app()->apiHelper->sendResponse($result);
        }

        /**
         * Create new model, and send response
         * @throws ApiException
         */
        public function actionCreate()
        {
            $params = Yii::app()->apiRequest->getParams();
            if (!isset($params['data']))
            {
                $message = Zurmo::t('ZurmoModule', 'Please provide data.');
                throw new ApiException($message);
            }
            $result    =  $this->processCreate($params['data']);
            Yii::app()->apiHelper->sendResponse($result);
        }

        /**
         * Update model and send response
         * @throws ApiException
         */
        public function actionUpdate()
        {
            $params = Yii::app()->apiRequest->getParams();
            if (!isset($params['id']))
            {
                $message = Zurmo::t('ZurmoModule', 'The ID specified was invalid.');
                throw new ApiException($message);
            }
            $result    =  $this->processUpdate((int)$params['id'], $params['data']);
            Yii::app()->apiHelper->sendResponse($result);
        }

        /**
         * Delete model and send response
         * @throws ApiException
         */
        public function actionDelete()
        {
            $params = Yii::app()->apiRequest->getParams();
            if (!isset($params['id']))
            {
                $message = Zurmo::t('ZurmoModule', 'The ID specified was invalid.');
                throw new ApiException($message);
            }
            $result    =  $this->processDelete((int)$params['id']);
            Yii::app()->apiHelper->sendResponse($result);
        }

        /**
         * Add related model to model's relations
         */
        public function actionAddRelation()
        {
            $params = Yii::app()->apiRequest->getParams();
            $result    =  $this->processAddRelation($params);
            Yii::app()->apiHelper->sendResponse($result);
        }

        /**
         * Remove related model from model's relations
         */
        public function actionRemoveRelation()
        {
            $params = Yii::app()->apiRequest->getParams();
            $result    =  $this->processRemoveRelation($params);
            Yii::app()->apiHelper->sendResponse($result);
        }

        /**
         * Get module primary model name
         */
        protected function getModelName()
        {
            return $this->getModule()->getPrimaryModelName();
        }

        /**
         * Get model by id
         * @param int $id
         * @throws ApiException
         * @return ApiResult
         */
        protected function processRead($id)
        {
            assert('is_int($id)');
            $modelClassName = $this->getModelName();

            try
            {
                $model = $modelClassName::getById($id);
            }
            catch (NotFoundException $e)
            {
                $message = Zurmo::t('ZurmoModule', 'The ID specified was invalid.');
                throw new ApiException($message);
            }

            try
            {
                ApiControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($model);
            }
            catch (SecurityException $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }

            try
            {
                $data           = static::getModelToApiDataUtilData($model);
                $resultClassName = Yii::app()->apiRequest->getResultClassName();
                $result                    = new $resultClassName(ApiResponse::STATUS_SUCCESS, $data, null, null);
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }

        protected static function getSearchFormClassName()
        {
            return null;
        }

        /**
         * List all models that satisfy provided criteria
         * @param array $params
         * @throws ApiException
         * @return ApiResult
         */
        protected function processList($params)
        {
            $modelClassName = $this->getModelName();
            $searchFormClassName = static::getSearchFormClassName();

            try
            {
                $filterParams = array();

                if (strtolower($_SERVER['REQUEST_METHOD']) != 'post')
                {
                    if (isset($params['filter']) && $params['filter'] != '')
                    {
                        parse_str($params['filter'], $filterParams);
                    }
                }
                else
                {
                    $filterParams = $params['data'];
                }

                $pageSize    = Yii::app()->pagination->getGlobalValueByType('apiListPageSize');

                if (isset($filterParams['pagination']['pageSize']))
                {
                    $pageSize = (int)$filterParams['pagination']['pageSize'];
                }

                if (isset($filterParams['pagination']['page']))
                {
                    $_GET[$modelClassName . '_page'] = (int)$filterParams['pagination']['page'];
                }

                if (isset($filterParams['sort']))
                {
                    $_GET[$modelClassName . '_sort'] = $filterParams['sort'];
                }

                if (isset($filterParams['search']) && isset($searchFormClassName))
                {
                    $_GET[$searchFormClassName] = $filterParams['search'];
                }
                if (isset($filterParams['dynamicSearch']) &&
                    isset($searchFormClassName) &&
                    !empty($filterParams['dynamicSearch']['dynamicClauses']) &&
                    !empty($filterParams['dynamicSearch']['dynamicStructure']))
                {
                    // Convert model ids into item ids, so we can perform dynamic search
                    DynamicSearchUtil::resolveDynamicSearchClausesForModelIdsNeedingToBeItemIds($modelClassName, $filterParams['dynamicSearch']['dynamicClauses']);
                    $_GET[$searchFormClassName]['dynamicClauses'] = $filterParams['dynamicSearch']['dynamicClauses'];
                    $_GET[$searchFormClassName]['dynamicStructure'] = $filterParams['dynamicSearch']['dynamicStructure'];
                }

                $model = new $modelClassName(false);
                if (isset($searchFormClassName))
                {
                    $searchForm = new $searchFormClassName($model);
                }
                else
                {
                    throw new NotSupportedException();
                }
                $stateMetadataAdapterClassName = $this->resolveStateMetadataAdapterClassName();
                $dataProvider = $this->makeRedBeanDataProviderByDataCollection(
                    $searchForm,
                    $pageSize,
                    $stateMetadataAdapterClassName
                );

                if (isset($filterParams['pagination']['page']) && (int)$filterParams['pagination']['page'] > 0)
                {
                    $currentPage = (int)$filterParams['pagination']['page'];
                }
                else
                {
                    $currentPage = 1;
                }

                $totalItems = $dataProvider->getTotalItemCount();
                $data = array();
                $data['totalCount'] = $totalItems;
                $data['currentPage'] = $currentPage;
                if ($totalItems > 0)
                {
                    $formattedData = $dataProvider->getData();
                    foreach ($formattedData as $model)
                    {
                        $data['items'][] = static::getModelToApiDataUtilData($model);
                    }
                    $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                }
                else
                {
                    $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }

        /**
         * List all model attributes
         * @param $params
         * @return ApiResult
         * @throws ApiException
         */
        protected function processListAttributes($params)
        {
            $data = array();
            try
            {
                $modelClassName           = $this->getModelName();
                $model                    = new $modelClassName();
                $adapter                  = new ModelAttributesAdapter($model);
                $customAttributes   = ArrayUtil::subValueSort($adapter->getCustomAttributes(), 'attributeLabel', 'asort');
                $standardAttributes = ArrayUtil::subValueSort($adapter->getStandardAttributes(), 'attributeLabel', 'asort');
                $allAttributes      = array_merge($customAttributes, $standardAttributes);
                $data['items'] = $allAttributes;
                $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }

        /**
         * Search and list all models that satisfy provided criteria
         * @param array $params
         * @throws ApiException
         * @return ApiResult
         */
        protected function processSearch($params)
        {
            try
            {
                $filterParams = array();
                if (strtolower($_SERVER['REQUEST_METHOD']) != 'post')
                {
                    if (isset($params['filter']) && $params['filter'] != '')
                    {
                        parse_str($params['filter'], $filterParams);
                    }
                }
                else
                {
                    $filterParams = $params['data'];
                }
                // Check if modelClassName exist and if it is subclass of RedBeanModel
                if (@class_exists($filterParams['search']['modelClassName']))
                {
                    $modelClassName = $filterParams['search']['modelClassName'];
                    @$modelClass = new $modelClassName();
                    if (!($modelClass instanceof RedBeanModel))
                    {
                        $message = Zurmo::t('ZurmoModule', '{modelClassName} should be subclass of RedBeanModel.',
                            array('{modelClassName}' => $modelClassName));
                        throw new NotSupportedException($message);
                    }
                }
                else
                {
                    $message = Zurmo::t('ZurmoModule', "{modelClassName} class does not exist.",
                        array('{modelClassName}' => $filterParams['search']['modelClassName']));
                    throw new NotSupportedException($message);
                }
                $pageSize    = Yii::app()->pagination->getGlobalValueByType('apiListPageSize');
                if (isset($filterParams['pagination']['pageSize']))
                {
                    $pageSize = (int)$filterParams['pagination']['pageSize'];
                }

                // Get offset. Please note that API client provide page number, and we need to convert it into offset,
                // which is parameter of RedBeanModel::getSubset function
                if (isset($filterParams['pagination']['page']) && (int)$filterParams['pagination']['page'] > 0)
                {
                    $currentPage = (int)$filterParams['pagination']['page'];
                }
                else
                {
                    $currentPage = 1;
                }
                $offset = $this->getOffsetFromCurrentPageAndPageSize($currentPage, $pageSize);
                $sort = null;
                if (isset($filterParams['sort']))
                {
                    $sort = $filterParams['sort'];
                }

                $stateMetadataAdapterClassName = $this->resolveStateMetadataAdapterClassName();
                if ($stateMetadataAdapterClassName != null)
                {
                    $stateMetadataAdapter = new $stateMetadataAdapterClassName($filterParams['search']['searchAttributeData']);
                    $filterParams['search']['searchAttributeData'] = $stateMetadataAdapter->getAdaptedDataProviderMetadata();
                    $filterParams['search']['searchAttributeData']['structure'] = '(' . $filterParams['search']['searchAttributeData']['structure'] . ')';
                }

                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
                $where             = RedBeanModelDataProvider::makeWhere($modelClassName,
                    $filterParams['search']['searchAttributeData'], $joinTablesAdapter);

                $results = $modelClassName::getSubset($joinTablesAdapter,
                    $offset, $pageSize, $where, $sort, $modelClassName, true);
                $totalItems = $modelClassName::getCount($joinTablesAdapter, $where, null, true);

                $data = array();
                $data['totalCount'] = $totalItems;
                $data['currentPage'] = $currentPage;
                if ($totalItems > 0)
                {
                    foreach ($results as $model)
                    {
                        $data['items'][] = static::getModelToApiDataUtilData($model);
                    }
                    $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                }
                else
                {
                    $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }

        /**
         * @param $currentPage
         * @param $pageSize
         * @return integer || null
         */
        protected function getOffsetFromCurrentPageAndPageSize($currentPage, $pageSize)
        {
            $offset = (int)(($currentPage - 1) * $pageSize);
            if ($offset == 0)
            {
                $offset = null;
            }
            return $offset;
        }

        /**
         * Add model relation
         * @param array $params
         * @throws ApiException
         * @return ApiResult
         */
        protected function processAddRelation($params)
        {
            $modelClassName = $this->getModelName();
            try
            {
                $data = array();
                if (isset($params['data']) && $params['data'] != '')
                {
                    parse_str($params['data'], $data);
                }
                $relationName = $data['relationName'];
                $modelId = $data['id'];
                $relatedId = $data['relatedId'];
                $model = $modelClassName::getById(intval($modelId));
                $relatedModelClassName = $model->getRelationModelClassName($relationName);
                $relatedModel = $relatedModelClassName::getById(intval($relatedId));

                if ($model->getRelationType($relationName) == RedBeanModel::HAS_MANY ||
                    $model->getRelationType($relationName) == RedBeanModel::MANY_MANY)
                {
                    $model->{$relationName}->add($relatedModel);

                    if ($model->save())
                    {
                        $result = new ApiResult(ApiResponse::STATUS_SUCCESS, null);
                    }
                    else
                    {
                        $message = Zurmo::t('ZurmoModule', 'Could not save relation.');
                        throw new ApiException($message);
                    }
                }
                else
                {
                    $message = Zurmo::t('ZurmoModule', 'Could not use this API call for HAS_ONE relationships.');
                    throw new ApiException($message);
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }

        /**
         * Remove model relation
         * @param array $params
         * @throws ApiException
         * @return ApiResult
         */
        protected function processRemoveRelation($params)
        {
            $modelClassName = $this->getModelName();
            try
            {
                $data = array();
                if (isset($params['data']) && $params['data'] != '')
                {
                    parse_str($params['data'], $data);
                }
                $relationName = $data['relationName'];
                $modelId = $data['id'];
                $relatedId = $data['relatedId'];

                $model = $modelClassName::getById(intval($modelId));
                $relatedModelClassName = $model->getRelationModelClassName($relationName);
                $relatedModel = $relatedModelClassName::getById(intval($relatedId));
                if ($model->getRelationType($relationName) == RedBeanModel::HAS_MANY ||
                    $model->getRelationType($relationName) == RedBeanModel::MANY_MANY)
                {
                    $model->{$relationName}->remove($relatedModel);
                    if ($model->save())
                    {
                        $result = new ApiResult(ApiResponse::STATUS_SUCCESS, null);
                    }
                    else
                    {
                        $message = Zurmo::t('ZurmoModule', 'Could not remove relation.');
                        throw new ApiException($message);
                    }
                }
                else
                {
                    $message = Zurmo::t('ZurmoModule', 'Could not use this API call for HAS_ONE relationships.');
                    throw new ApiException($message);
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }

        /**
         * Create new model
         * @param $data
         * @return ApiResult
         * @throws ApiException
         */
        protected function processCreate($data)
        {
            $modelClassName = $this->getModelName();
            try
            {
                if (isset($data['modelRelations']))
                {
                    $modelRelations = $data['modelRelations'];
                    unset($data['modelRelations']);
                }
                $model = new $modelClassName();
                $this->setModelScenarioFromData($model, $data);
                $model = $this->attemptToSaveModelFromData($model, $data, null, false);
                $id = $model->id;
                $model->forget();
                if (!count($model->getErrors()))
                {
                    if (isset($modelRelations) && count($modelRelations))
                    {
                        try
                        {
                            $this->manageModelRelations($model, $modelRelations);
                            $model->save();
                        }
                        catch (Exception $e)
                        {
                            $model->delete();
                            $message = $e->getMessage();
                            throw new ApiException($message);
                        }
                    }
                    $model  = $modelClassName::getById($id);
                    $data   = static::getModelToApiDataUtilData($model);
                    $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                }
                else
                {
                    $errors = $model->getErrors();
                    $message = Zurmo::t('ZurmoModule', 'Model was not created.');
                    $result = new ApiResult(ApiResponse::STATUS_FAILURE, null, $message, $errors);
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }

        /**
         * Update model
         * @param int $id
         * @param array $data
         * @throws ApiException
         * @return ApiResult
         */
        protected function processUpdate($id, $data)
        {
            assert('is_int($id)');
            $modelClassName = $this->getModelName();

            if (isset($data['modelRelations']))
            {
                $modelRelations = $data['modelRelations'];
                unset($data['modelRelations']);
            }

            try
            {
                $model = $modelClassName::getById($id);
                $this->setModelScenarioFromData($model, $data);
            }
            catch (NotFoundException $e)
            {
                $message = Zurmo::t('ZurmoModule', 'The ID specified was invalid.');
                throw new ApiException($message);
            }

            try
            {
                ApiControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($model);
            }
            catch (SecurityException $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }

            try
            {
                $model = $this->attemptToSaveModelFromData($model, $data, null, false);
                $id = $model->id;
                if (!count($model->getErrors()))
                {
                    if (isset($modelRelations) && count($modelRelations))
                    {
                        try
                        {
                            $this->manageModelRelations($model, $modelRelations);
                            $model->save();
                        }
                        catch (Exception $e)
                        {
                            $message = Zurmo::t('ZurmoModule', 'Model was updated, but there were issues with relations.');
                            $message .= ' ' . $e->getMessage();
                            throw new ApiException($message);
                        }
                    }

                    $model = $modelClassName::getById($id);
                    $data  = static::getModelToApiDataUtilData($model);
                    $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                }
                else
                {
                    $errors = $model->getErrors();
                    $message = Zurmo::t('ZurmoModule', 'Model was not updated.');
                    // To-Do: How to pass $errors and $message to exception
                    //throw new ApiException($message);
                    $result = new ApiResult(ApiResponse::STATUS_FAILURE, null, $message, $errors);
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }

        /**
         * Resolve model scenario from data
         * @param array $data
         * @return null
         */
        protected function resolveModelScenario(array & $data)
        {
            if (isset($data['modelScenario']) && $data['modelScenario'] != '')
            {
                $scenarioName = $data['modelScenario'];
                unset($data['modelScenario']);
                return $scenarioName;
            }
            return null;
        }

        /**
         * Set model scenario
         * @param RedBeanModel $model
         * @param array $data
         */
        protected function setModelScenarioFromData(RedBeanModel $model, array & $data)
        {
            $scenarioName = $this->resolveModelScenario($data);
            if (isset($scenarioName) && $scenarioName != '')
            {
                $model->setScenario($scenarioName);
            }
        }

        /**
         * @param RedBeanModel $model
         * @param array $modelRelations
         * @return bool
         * @throws ApiException
         */
        protected function manageModelRelations($model, $modelRelations)
        {
            try
            {
                if (isset($modelRelations) && !empty($modelRelations))
                {
                    foreach ($modelRelations as $modelRelation => $relations)
                    {
                        if ($model->isAttribute($modelRelation) &&
                            ($model->getRelationType($modelRelation) == RedBeanModel::HAS_MANY ||
                            $model->getRelationType($modelRelation) == RedBeanModel::MANY_MANY))
                        {
                            foreach ($relations as $relation)
                            {
                                $relatedModelClassName = $relation['modelClassName'];
                                try
                                {
                                    $relatedModel = $relatedModelClassName::getById(intval($relation['modelId']));
                                }
                                catch (Exception $e)
                                {
                                    $message = Zurmo::t('ZurmoModule', 'The related model ID specified was invalid.');
                                    throw new NotFoundException($message);
                                }

                                if ($relation['action'] == 'add')
                                {
                                    $model->{$modelRelation}->add($relatedModel);
                                }
                                elseif ($relation['action'] == 'remove')
                                {
                                    $model->{$modelRelation}->remove($relatedModel);
                                }
                                else
                                {
                                    $message = Zurmo::t('ZurmoModule', 'Unsupported action.');
                                    throw new NotSupportedException($message);
                                }
                            }
                        }
                        else
                        {
                            $message = Zurmo::t('ZurmoModule', 'You can add relations only for HAS_MANY and MANY_MANY relations.');
                            throw new NotSupportedException($message);
                        }
                    }
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return true;
        }

        /**
         * Delete model
         * @param int $id
         * @throws ApiException
         * @return ApiResult
         */
        protected function processDelete($id)
        {
            assert('is_int($id)');
            $modelClassName = $this->getModelName();

            try
            {
                $model = $modelClassName::getById($id);
            }
            catch (NotFoundException $e)
            {
                $message = Zurmo::t('ZurmoModule', 'The ID specified was invalid.');
                throw new ApiException($message);
            }

            try
            {
                ApiControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($model);
            }
            catch (SecurityException $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }

            try
            {
                $model->delete();
                $result = new ApiResult(ApiResponse::STATUS_SUCCESS, null);
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }

        /**
         * Instead of saving from post, we are saving from the API data.
         * @see attemptToSaveModelFromPost
         */
        protected function attemptToSaveModelFromData($model, $data, $redirectUrlParams = null, $redirect = true)
        {
            assert('is_array($data)');
            assert('$redirectUrlParams == null || is_array($redirectUrlParams) || is_string($redirectUrlParams)');
            $savedSucessfully   = false;
            $modelToStringValue = null;

            if (isset($data))
            {
                $this->preAttemptToSaveModelFromDataHook($model, $data);
                $controllerUtil   = new ZurmoControllerUtil();
                $model            = $controllerUtil->saveModelFromSanitizedData($data, $model, $savedSucessfully,
                                                                                            $modelToStringValue, false);
            }
            if ($savedSucessfully && $redirect)
            {
                $this->actionAfterSuccessfulModelSave($model, $modelToStringValue, $redirectUrlParams);
            }
            return $model;
        }

        /**
         * Hook to alter $model or $data before we attempt to save it.
         * @param RedBeanModel $model
         * @param array $data
         */
        protected function preAttemptToSaveModelFromDataHook(RedBeanModel $model, array & $data)
        {
        }

        /**
         * Util used to convert model to array
         * @return string
         */
        protected static function getModelToApiDataUtil()
        {
            return 'RedBeanModelToApiDataUtil';
        }

        /**
         * Returns data array for provided model using getModelToApiDataUtil
         * @param RedBeanModel $model
         * @return array
         */
        protected static function getModelToApiDataUtilData(RedBeanModel $model)
        {
            $dataUtil                   = static::getModelToApiDataUtil();
            $redBeanModelToApiDataUtil  = new $dataUtil($model);
            $data                       = $redBeanModelToApiDataUtil->getData();
            static::resolveIncludingAdditionalData($data);
            return $data;
        }

        /**
         * Override if you need to include additional data in API response
         * @param array $data
         * @return array
         */
        protected static function resolveIncludingAdditionalData(Array & $data)
        {
        }

        /**
         * Resolve StateMetadataAdapterClassName
         * @return mixed
         */
        protected function resolveStateMetadataAdapterClassName()
        {
            // In case of ContactState model, we can't use Module::getStateMetadataAdapterClassName() function,
            // because it references to Contact model, so we defined new function
            // ContactsContactStateApiController::getStateMetadataAdapterClassName() which return null.
            if (method_exists($this, 'getStateMetadataAdapterClassName'))
            {
                $stateMetadataAdapterClassName = $this->getStateMetadataAdapterClassName();
            }
            else
            {
                $stateMetadataAdapterClassName = $this->getModule()->getStateMetadataAdapterClassName();
            }
            return $stateMetadataAdapterClassName;
        }

        /**
         * Get array of deleted items since beginning or since datetime in past
         * @param array $params
         * @return ApiResult
         * @throws ApiException
         */
        protected function processGetDeletedItems($params)
        {
            try
            {
                $modelClassName = $this->getModelName();
                $stateMetadataAdapterClassName = $this->resolveStateMetadataAdapterClassName();

                if (!isset($params['sinceDateTime']))
                {
                    $sinceTimestamp = 0;
                }
                else
                {
                    if (DateTimeUtil::isValidDbFormattedDateTime($params['sinceDateTime']))
                    {
                        $sinceTimestamp = DateTimeUtil::convertDbFormatDateTimeToTimestamp($params['sinceDateTime']);
                    }
                    else
                    {
                        $message = 'sinceDateTime format is not correct. sinceDateTime should be in "YYYY-MM-DD HH:MM:SS" format';
                        throw new ApiException($message);
                    }
                }

                $pageSize    = Yii::app()->pagination->getGlobalValueByType('apiListPageSize');
                if (isset($params['pagination']['pageSize']))
                {
                    $pageSize = (int)$params['pagination']['pageSize'];
                }

                // Get offset. Please note that API client provide page number, and we need to convert it into offset,
                // which is parameter of RedBeanModel::getSubset function
                if (isset($params['pagination']['page']) && (int)$params['pagination']['page'] > 0)
                {
                    $currentPage = (int)$params['pagination']['page'];
                }
                else
                {
                    $currentPage = 1;
                }
                $offset = $this->getOffsetFromCurrentPageAndPageSize($currentPage, $pageSize);

                $modelIds = ModelStateChangesSubscriptionUtil::getDeletedModelIds('API', $modelClassName, $pageSize, $offset, $sinceTimestamp, $stateMetadataAdapterClassName);
                $totalItems = ModelStateChangesSubscriptionUtil::getDeletedModelsCount('API', $modelClassName, $sinceTimestamp, $stateMetadataAdapterClassName);

                $data = array(
                    'totalCount' => $totalItems,
                    'pageSize' => $pageSize,
                    'currentPage' => $currentPage
                );

                if ($totalItems > 0 && is_array($modelIds) && !empty($modelIds))
                {
                    foreach ($modelIds as $modelId)
                    {
                        $data['items'][] = $modelId;
                    }
                    $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                }
                else
                {
                    $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }

        /**
         * Get array of newly created items since beginning or since datetime in past
         * @param $params
         * @return ApiResult
         * @throws ApiException
         */
        protected function processGetCreatedItems($params)
        {
            try
            {
                $modelClassName = $this->getModelName();
                $stateMetadataAdapterClassName = $this->resolveStateMetadataAdapterClassName();

                if (!isset($params['sinceDateTime']))
                {
                    $sinceTimestamp = 0;
                }
                else
                {
                    if (DateTimeUtil::isValidDbFormattedDateTime($params['sinceDateTime']))
                    {
                        $sinceTimestamp = DateTimeUtil::convertDbFormatDateTimeToTimestamp($params['sinceDateTime']);
                    }
                    else
                    {
                        $message = 'sinceDateTime format is not correct. sinceDateTime should be in "YYYY-MM-DD HH:MM:SS" format';
                        throw new ApiException($message);
                    }
                }

                $pageSize    = Yii::app()->pagination->getGlobalValueByType('apiListPageSize');
                if (isset($params['pagination']['pageSize']))
                {
                    $pageSize = (int)$params['pagination']['pageSize'];
                }

                // Get offset. Please note that API client provide page number, and we need to convert it into offset,
                // which is parameter of RedBeanModel::getSubset function
                if (isset($params['pagination']['page']) && (int)$params['pagination']['page'] > 0)
                {
                    $currentPage = (int)$params['pagination']['page'];
                }
                else
                {
                    $currentPage = 1;
                }
                $offset = $this->getOffsetFromCurrentPageAndPageSize($currentPage, $pageSize);

                $models = ModelStateChangesSubscriptionUtil::getCreatedModels('API', $modelClassName, $pageSize, $offset, $sinceTimestamp, $stateMetadataAdapterClassName, Yii::app()->user->userModel);
                $totalItems = ModelStateChangesSubscriptionUtil::getCreatedModelsCount('API', $modelClassName, $sinceTimestamp, $stateMetadataAdapterClassName, Yii::app()->user->userModel);
                $data = array(
                    'totalCount' => $totalItems,
                    'pageSize' => $pageSize,
                    'currentPage' => $currentPage
                );

                if (is_array($models) && !empty($models))
                {
                    foreach ($models as $model)
                    {
                        $data['items'][] = static::getModelToApiDataUtilData($model);
                    }
                    $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                }
                else
                {
                    $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }

        /**
         * Get array of modified items since beginning or since datetime in past
         * @param $params
         * @return ApiResult
         * @throws ApiException
         */
        public function processGetModifiedItems($params)
        {
            try
            {
                $modelClassName = $this->getModelName();
                $stateMetadataAdapterClassName = $this->resolveStateMetadataAdapterClassName();

                if (!isset($params['sinceDateTime']))
                {
                    $sinceTimestamp = 0;
                }
                else
                {
                    if (DateTimeUtil::isValidDbFormattedDateTime($params['sinceDateTime']))
                    {
                        $sinceTimestamp = DateTimeUtil::convertDbFormatDateTimeToTimestamp($params['sinceDateTime']);
                    }
                    else
                    {
                        $message = 'sinceDateTime format is not correct. sinceDateTime should be in "YYYY-MM-DD HH:MM:SS" format';
                        throw new ApiException($message);
                    }
                }

                $pageSize    = Yii::app()->pagination->getGlobalValueByType('apiListPageSize');
                if (isset($params['pagination']['pageSize']))
                {
                    $pageSize = (int)$params['pagination']['pageSize'];
                }

                // Get offset. Please note that API client provide page number, and we need to convert it into offset,
                // which is parameter of RedBeanModel::getSubset function
                if (isset($params['pagination']['page']) && (int)$params['pagination']['page'] > 0)
                {
                    $currentPage = (int)$params['pagination']['page'];
                }
                else
                {
                    $currentPage = 1;
                }
                $offset = $this->getOffsetFromCurrentPageAndPageSize($currentPage, $pageSize);
                $models = ModelStateChangesSubscriptionUtil::getUpdatedModels($modelClassName, $pageSize, $offset, $sinceTimestamp, $stateMetadataAdapterClassName, Yii::app()->user->userModel);
                $totalItems = ModelStateChangesSubscriptionUtil::getUpdatedModelsCount($modelClassName, $sinceTimestamp, $stateMetadataAdapterClassName, Yii::app()->user->userModel);
                $data = array(
                    'totalCount' => $totalItems,
                    'pageSize' => $pageSize,
                    'currentPage' => $currentPage
                );

                if (is_array($models) && !empty($models))
                {
                    foreach ($models as $model)
                    {
                        $data['items'][] = static::getModelToApiDataUtilData($model);
                    }
                    $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                }
                else
                {
                    $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }

        public function processGetManyManyRelationshipModels($params)
        {
            try
            {
                $modelId        = $params['id'];
                $relationName   = $params['relationName'];
                $modelClassName = $params['modelClassName'];

                if (!class_exists($modelClassName, false))
                {
                    $message = Zurmo::t('ZurmoModule', 'The specified class name was invalid.');
                    throw new ApiException($message);
                }
                try
                {
                    $model = $modelClassName::getById(intval($modelId));
                }
                catch (NotFoundException $e)
                {
                    $message = Zurmo::t('ZurmoModule', 'The ID specified was invalid.');
                    throw new ApiException($message);
                }

                $relatedModelClassName = $model->getRelationModelClassName($relationName);
                if ($model->isRelation($relationName) &&
                    $model->getRelationType($relationName) == RedBeanModel::MANY_MANY)
                {
                    $data = array();
                    foreach ($model->{$relationName} as $item)
                    {
                        $data[$relationName][] = array('class' => $relatedModelClassName, 'id' => $item->id);
                    }
                    $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                }
                else
                {
                    $message = Zurmo::t('ZurmoModule', 'The specified relationship name does not exist or is not MANY_MANY type.');
                    throw new ApiException($message);
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
            return $result;
        }
    }
?>