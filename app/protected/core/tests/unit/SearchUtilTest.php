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

    class SearchUtilTest extends BaseTest
    {
        public function testResolveSortFromStickyData()
        {
            list($sortAttribute, $sortDescending) = SearchUtil::
                    resolveSortFromStickyData('testing', 'testId');
            $this->assertEquals(null, $sortAttribute);
            $this->assertFalse ($sortDescending);

            $_GET['testing_sort'] = 'name';
            list($sortAttribute, $sortDescending) = SearchUtil::
                    resolveSortFromStickyData('testing', 'testId');
            $this->assertEquals('name', $sortAttribute);
            $this->assertFalse ($sortDescending);

            $_GET = array();
            list($sortAttribute, $sortDescending) = SearchUtil::
                    resolveSortFromStickyData('testing', 'testId');
            $this->assertEquals('name', $sortAttribute);
            $this->assertFalse ($sortDescending);

            $_GET['testing_sort'] = 'other.desc';
            list($sortAttribute, $sortDescending) = SearchUtil::
                    resolveSortFromStickyData('testing', 'testId');
            $this->assertEquals('other', $sortAttribute);
            $this->assertTrue  ($sortDescending);

            $_GET = array();
            list($sortAttribute, $sortDescending) = SearchUtil::
                    resolveSortFromStickyData('testing', 'testId');
            $this->assertEquals('other', $sortAttribute);
            $this->assertTrue  ($sortDescending);
        }

        public function testResolveSearchFormByStickyFilteredByData()
        {
            $searchModel  = new AFilteredBySearchFormTestModel(new A());
            SearchUtil::resolveSearchFormByStickyFilteredByData(array(), $searchModel, array());
            $this->assertEmpty($searchModel->filteredBy);
            SearchUtil::resolveSearchFormByStickyFilteredByData(array(), $searchModel, array('filteredBy' => 'all'));
            $this->assertEquals('all', $searchModel->filteredBy);
        }

        public function testGetSorAttributeFromSortArray()
        {
            $sortAttribute = SearchUtil::getSortAttributeFromSortString('name.desc');
            $this->assertEquals('name', $sortAttribute);
            $sortAttribute = SearchUtil::getSortAttributeFromSortString('name');
            $this->assertEquals('name', $sortAttribute);
            $sortAttribute = SearchUtil::getSortAttributeFromSortString('name.asc');
            $this->assertEquals('name', $sortAttribute);
            $sortAttribute = SearchUtil::getSortAttributeFromSortString('');
            $this->assertEquals('', $sortAttribute);

            $_GET['testing_sort'] = 'name.desc';
            $sortAttribute = SearchUtil::resolveSortAttributeFromArray('testing', $_GET);
            $this->assertEquals('name', $sortAttribute);
            $_GET['testing_sort'] = 'name';
            $sortAttribute = SearchUtil::resolveSortAttributeFromArray('testing', $_GET);
            $this->assertEquals('name', $sortAttribute);
            $_GET['testing_sort'] = 'name.asc';
            $sortAttribute = SearchUtil::resolveSortAttributeFromArray('testing', $_GET);
            $this->assertEquals('name', $sortAttribute);
            $_GET['testing_sort'] = '';
            $sortAttribute = SearchUtil::resolveSortAttributeFromArray('testing', $_GET);
            $this->assertEquals('', $sortAttribute);
        }

        public function testIsSortDescending()
        {
            $sortDescending = SearchUtil::isSortDescending('name.desc');
            $this->assertTrue($sortDescending);
            $sortDescending = SearchUtil::isSortDescending('name');
            $this->assertFalse($sortDescending);
            $sortDescending = SearchUtil::isSortDescending('name.asc');
            $this->assertFalse($sortDescending);

            $_GET['testing_sort'] = 'name.desc';
            $sortDescending = SearchUtil::resolveSortDescendingFromArray('testing', $_GET);
            $this->assertTrue($sortDescending);
            $_GET['testing_sort'] = 'name';
            $sortDescending = SearchUtil::resolveSortDescendingFromArray('testing', $_GET);
            $this->assertFalse($sortDescending);
            $_GET['testing_sort'] = 'name.asc';
            $sortDescending = SearchUtil::resolveSortDescendingFromArray('testing', $_GET);
            $this->assertFalse($sortDescending);
        }

        public function testGetSearchAttributesFromSearchArray()
        {
            $searchArray = array(
                'a' => 'apple',
                'b' => '',
            );
            $testArray = array(
                'a' => 'apple',
                'b' => null,
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals($testArray, $newArray);

            $_GET['testing'] = array(
                'a' => 'apple',
                'b' => '',
            );
            $newArray = SearchUtil::resolveSearchAttributesFromArray('testing', 'AAASearchFormTestModel', $_GET);
            $this->assertEquals($testArray, $newArray);

            //Now test various empty and 0 combinations
            $_GET['testing'] = array(
                'a' => null,
            );
            $newArray = SearchUtil::resolveSearchAttributesFromArray('testing', 'AAASearchFormTestModel', $_GET);
            $this->assertEquals(array('a' => null), $newArray);

            $_GET['testing'] = array(
                'a' => '',
            );
            $newArray = SearchUtil::resolveSearchAttributesFromArray('testing', 'AAASearchFormTestModel', $_GET);
            $this->assertEquals(array('a' => null), $newArray);

            $_GET['testing'] = array(
                'a' => 0,
            );
            $newArray = SearchUtil::resolveSearchAttributesFromArray('testing', 'AAASearchFormTestModel', $_GET);
            $this->assertEquals(array('a' => null), $newArray);

            $_GET['testing'] = array(
                'a' => '0',
            );
            $newArray = SearchUtil::resolveSearchAttributesFromArray('testing', 'AAASearchFormTestModel', $_GET);
            $this->assertEquals(array('a' => '0'), $newArray);
        }

        public function testResolveSearchAttributesFromGetArrayForDynamicSearch()
        {
            $_GET['testing'] = array(
                'a' => '0',
                'dynamicClauses' => array(array('b' => '0')),
                'dynamicStructure' => '1 and 2',
            );
            $newArray = SearchUtil::resolveSearchAttributesFromArray('testing', 'AAASearchFormTestModel', $_GET);
            $this->assertEquals(array('a' => '0'), $newArray);
        }

        public function testResolveSearchAttributesFromGetArrayForAnyMixedAttributeScopeName()
        {
            $_GET['testing'] = array(
                'a' => '0',
                SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME => 'something',
                SearchForm::SELECTED_LIST_ATTRIBUTES        => array('something'),
            );
            $newArray = SearchUtil::resolveSearchAttributesFromArray('testing', 'AAASearchFormTestModel', $_GET);
            $this->assertEquals(array('a' => '0'), $newArray);

            $_GET['testing'] = array(
                'a' => '0',
                SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME => null,
                SearchForm::SELECTED_LIST_ATTRIBUTES        => null,
            );
            $newArray = SearchUtil::resolveSearchAttributesFromArray('testing', 'AAASearchFormTestModel', $_GET);
            $this->assertEquals(array('a' => '0'), $newArray);

            $_GET['testing'] = array(
                'a' => '0',
                SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME => array(),
                SearchForm::SELECTED_LIST_ATTRIBUTES        => array(),
            );
            $newArray = SearchUtil::resolveSearchAttributesFromArray('testing', 'AAASearchFormTestModel', $_GET);
            $this->assertEquals(array('a' => '0'), $newArray);

            $_GET['testing'] = array(
                'a' => '0',
                SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME => array('a' => 'b'),
                SearchForm::SELECTED_LIST_ATTRIBUTES        => array('a' => 'b'),
            );
            $newArray = SearchUtil::resolveSearchAttributesFromArray('testing', 'AAASearchFormTestModel', $_GET);
            $this->assertEquals(array('a' => '0'), $newArray);
        }

        /**
         * This test is for testing the method SearchUtil::changeEmptyArrayValuesToNull.
         * if a value in the search array for multiselect attribute has an empty element it is removed(eliminated).
         */
        public function testGetSearchAttributesFromSearchArrayChangeEmptyArrayValuesToNull()
        {
            $searchArray = array('testMultiSelectDropDown' => array('values' => array(0 => '')));
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals(array(), $newArray);

            $searchArray = array('testMultiSelectDropDown' => array('values' => array(0 => null)));
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals(array(), $newArray);

            $searchArray = array('testMultiSelectDropDown' => array('values' => array(0 => null, 1 => 'xyz')));
            $resultArray = array('testMultiSelectDropDown' => array('values' => array(0 => 'xyz')));
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals($resultArray, $newArray);

            $searchArray = array('testDropDownAsMultiSelectDropDown' => array('value' => array(0 => '')));
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals(array(), $newArray);

            $searchArray = array('testDropDownAsMultiSelectDropDown' => array('value' => array(0 => null)));
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals(array(), $newArray);

            $searchArray = array('testDropDownAsMultiSelectDropDown' => array('value' => array(0 => null, 1 => 'xyz')));
            $resultArray = array('testDropDownAsMultiSelectDropDown' => array('value' => array(0 => 'xyz')));
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals($resultArray, $newArray);

            //Test recursion
            $searchArray = array('testDropDownAsMultiSelectDropDown' =>
                                    array('abc' => array('value' => array(0 => null, 1 => 'xyz'))));
            $resultArray = array('testDropDownAsMultiSelectDropDown' =>
                                    array('abc' => array('value' => array(0 => 'xyz'))));
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals($resultArray, $newArray);

            $searchArray = array('testMultiSelectDropDown' =>
                                    array('abc' => array('values' => array(0 => null, 1 => 'xyz'))));
            $resultArray = array('testMultiSelectDropDown' =>
                                    array('abc' => array('values' => array(0 => 'xyz'))));
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals($resultArray, $newArray);
        }

        public function testGetSearchAttributesFromSearchArrayForSavingExistingSearchCriteria()
        {
            $searchArray = array(
                'a' => 'apple',
                'b' => '',
            );
            $testArray = array(
                'a' => 'apple',
                'b' => null,
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals($testArray, $newArray);

            $searchArray = array(
                'a' => 'apple',
                'b' => '',
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals($testArray, $newArray);

            //Now test various empty and 0 combinations
            $searchArray = array(
                'a' => null,
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals(array('a' => null), $newArray);

            $searchArray = array(
                'a' => '',
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals(array('a' => null), $newArray);

            $searchArray = array(
                'a' => 0,
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals(array('a' => 0), $newArray);

            $searchArray = array(
                'a' => '0',
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals(array('a' => '0'), $newArray);

            $searchArray = array(
                'a' => array('values' => array(0 => '')),
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals(array(), $newArray);

            $searchArray = array(
                'a' => array('value' => array(0 => '')),
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals(array(), $newArray);

            $searchArray = array(
                'a' => array('values' => array(0 => null)),
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals(array(), $newArray);

            $searchArray = array(
                'a' => array('value' => array(0 => null)),
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals(array(), $newArray);

            $searchArray = array(
                'a' => array('value' => array(0 => null, 1 => 'xyz')),
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray);
            $this->assertEquals(array('a' => array('value' => array(0 => 'xyz'))), $newArray);
        }

        public function testAdaptSearchAttributesToSetInRedBeanModel()
        {
            $model = new ASearchFormTestModel(new A(false));
            $searchAttributes = array(
                'differentOperatorB' => array('value' => 'thiswillstay'),
                'a'                  => array('value' => 'thiswillgo'),
                'differentOperatorB' => 'something',
                'name'               => array('value' => 'thiswillstay'),
            );
            $adaptedSearchAttributes = SearchUtil::adaptSearchAttributesToSetInRedBeanModel($searchAttributes, $model);
            $compareData = array(
                'differentOperatorB' => array('value' => 'thiswillstay'),
                'a'                  => 'thiswillgo',
                'differentOperatorB' => 'something',
                'name'               => array('value' => 'thiswillstay'),
            );
            $this->assertEquals($compareData, $adaptedSearchAttributes);
        }

        public function testResolveAnyMixedAttributesScopeForSearchModelFromGetArray()
        {
            $searchModel  = new ASearchFormTestModel(new A());
            $getArrayName = 'someArray';
            SearchUtil::resolveAnyMixedAttributesScopeForSearchModelFromArray($searchModel, $getArrayName, $_GET);
            $this->assertNull($searchModel->getAnyMixedAttributesScope());

            //Test passing a value in the GET
            $_GET['someArray'][SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME] = 'notAnArray';
            SearchUtil::resolveAnyMixedAttributesScopeForSearchModelFromArray($searchModel, $getArrayName, $_GET);
            $this->assertNull($searchModel->getAnyMixedAttributesScope());

            $_GET['someArray'][SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME] = array('All');
            SearchUtil::resolveAnyMixedAttributesScopeForSearchModelFromArray($searchModel, $getArrayName, $_GET);
            $this->assertNull($searchModel->getAnyMixedAttributesScope());

            $_GET['someArray'][SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME] = array('A', 'B', 'C');
            SearchUtil::resolveAnyMixedAttributesScopeForSearchModelFromArray($searchModel, $getArrayName, $_GET);
            $this->assertEquals(array('A', 'B', 'C'), $searchModel->getAnyMixedAttributesScope());
        }

        public function testResolveSelectedListAttributesForSearchModelFromGetArray()
        {
            $searchModel  = new ASearchFormTestModel(new A());
            $listAttributesSelector         = new ListAttributesSelector('AListView', 'TestModule');
            $searchModel->setListAttributesSelector($listAttributesSelector);
            $getArrayName = 'someArray';
            SearchUtil::resolveSelectedListAttributesForSearchModelFromArray($searchModel, $getArrayName, $_GET);
            $this->assertEquals(array('name'), $searchModel->getListAttributesSelector()->getSelected());

            //Test passing a value in the GET
            $_GET['someArray'][SearchForm::SELECTED_LIST_ATTRIBUTES] = array();
            SearchUtil::resolveSelectedListAttributesForSearchModelFromArray($searchModel, $getArrayName, $_GET);
            $this->assertEquals(array('name'), $searchModel->getListAttributesSelector()->getSelected());

            $_GET['someArray'][SearchForm::SELECTED_LIST_ATTRIBUTES] = array('name', 'a');
            SearchUtil::resolveSelectedListAttributesForSearchModelFromArray($searchModel, $getArrayName, $_GET);
            $this->assertEquals(array('name', 'a'), $searchModel->getListAttributesSelector()->getSelected());
        }

        public function testResolveFilterByStarredFromGetArray()
        {
            $searchModel  = new ASearchFormTestModel(new A());
            $getArrayName = 'someArray';
            SearchUtil::resolveFilterByStarredFromArray($searchModel, $getArrayName, $_GET);
            $this->assertNull($searchModel->filterByStarred);

            $_GET['someArray']['filterByStarred'] = true;
            SearchUtil::resolveFilterByStarredFromArray($searchModel, $getArrayName, $_GET);
            $this->assertTrue($searchModel->filterByStarred);

            $_GET['someArray']['filterByStarred'] = false;
            SearchUtil::resolveFilterByStarredFromArray($searchModel, $getArrayName, $_GET);
            $this->assertFalse($searchModel->filterByStarred);
        }

        public function testResolveFilteredByFromGetArray()
        {
            $searchModel  = new AFilteredBySearchFormTestModel(new A());
            $getArrayName = 'someArray';
            SearchUtil::resolveFilteredByFromArray($searchModel, $getArrayName, $_GET);
            $this->assertNull($searchModel->filteredBy);

            $_GET['someArray']['filteredBy'] = 'all';
            SearchUtil::resolveFilteredByFromArray($searchModel, $getArrayName, $_GET);
            $this->assertEquals('all', $searchModel->filteredBy);

            $_GET['someArray']['filteredBy'] = 'none';
            SearchUtil::resolveFilteredByFromArray($searchModel, $getArrayName, $_GET);
            $this->assertEquals('none', $searchModel->filteredBy);
        }

        public function testGetDynamicSearchAttributesFromGetArray()
        {
            //Test without any dynamic search
            $_GET['testing'] = array(
                'a' => null,
            );
            $newArray = SearchUtil::getDynamicSearchAttributesFromArray('testing', $_GET);
            $this->assertNull($newArray);

            //Test with dynamic search
            $_GET['testing'] = array(
                'a' => null,
                'dynamicClauses' => array(array('b' => 'c')),
                'dynamicStructure' => '1 and 2',
            );
            $newArray    = SearchUtil::getDynamicSearchAttributesFromArray('testing', $_GET);
            $compareData = array(array('b' => 'c'));
            $this->assertEquals($compareData, $newArray);

            //Test with dynamic search and an undefined sub-array
            $_GET['testing'] = array(
                'a' => null,
                'dynamicClauses' => array(array('b' => 'c'), 'undefined', array('d' => 'simpleDimple')),
                'dynamicStructure' => '1 and 2',
            );
            $newArray    = SearchUtil::getDynamicSearchAttributesFromArray('testing', $_GET);
            $compareData = array(0 => array('b' => 'c'), 2 => array('d' => 'simpleDimple'));
            $this->assertEquals($compareData, $newArray);

            //Test with an empty value being converted to null, also tests nested empty values
            $_GET['testing'] = array(
                'a' => null,
                'dynamicClauses' => array(array('b' => 'c'), 'undefined', array('d' => ''),
                     array('e' => array('f' => array('g' => ''))),
                     array('e' => array('f' => '')),
                     ),
                'dynamicStructure' => '1 and 2',
            );
            $newArray    = SearchUtil::getDynamicSearchAttributesFromArray('testing', $_GET);
            $compareData = array(0 => array('b' => 'c'), 2 => array('d' => null),
                                      array('e' => array('f' => array('g' => null))),
                                      array('e' => array('f' => null)));
            $this->assertEquals($compareData, $newArray);
            $this->assertTrue($newArray[2]['d'] === null);
            $this->assertTrue($newArray[3]['e']['f']['g'] === null);
            $this->assertTrue($newArray[4]['e']['f'] === null);
        }

        public function testSanitizeDynamicSearchAttributesByDesignerTypeForSavingModel()
        {
            $searchModel = new ASearchFormTestModel(new A());
            //Test without anything special sanitizing
            $dynamicSearchAttributes = array(
                                        0 => array('attributeIndexOrDerivedType' => 'a',
                                                    'structurePosition'          => '1',
                                                    'a'                          => 'something'),
                                        2 => array('attributeIndexOrDerivedType' => 'a',
                                                    'structurePosition'          => '2',
                                                    'a'                          => 'somethingElse'));
            $newArray = SearchUtil::sanitizeDynamicSearchAttributesByDesignerTypeForSavingModel($searchModel,
                                                                                                $dynamicSearchAttributes);
            $this->assertEquals($dynamicSearchAttributes, $newArray);
        }

        public function testSanitizeDynamicSearchAttributesByDesignerTypeForSavingModelWithNestedAttributes()
        {
            $searchModel = new IIISearchFormTestModel(new III());
            //Test without anything special sanitizing
            $dynamicSearchAttributes = array(
                                        0 => array('attributeIndexOrDerivedType' => 'iiiMember',
                                                    'structurePosition'          => '1',
                                                    'iiiMember'                  => 'something'),
                                        1 => array('attributeIndexOrDerivedType' => 'ccc' . FormModelUtil::RELATION_DELIMITER . 'cccMember',
                                                    'structurePosition'          => '2',
                                                    'ccc'                        => array(
                                                        'relatedModelData' => true,
                                                        'cccMember'        => 'somethingElse',
                                                    )),
                                        2 => array('attributeIndexOrDerivedType' => 'ccc' . FormModelUtil::RELATION_DELIMITER .
                                                   'bbb' . FormModelUtil::RELATION_DELIMITER . 'bbbMember',
                                                    'structurePosition'          => '2',
                                                    'ccc'                        => array(
                                                        'relatedModelData' => true,
                                                        'bbb'                        => array(
                                                            'relatedModelData' => true,
                                                            'bbbMember'        => 'bbbValue',
                                                        )
                                                    )));
            $newArray = SearchUtil::sanitizeDynamicSearchAttributesByDesignerTypeForSavingModel($searchModel,
                                                                                                $dynamicSearchAttributes);
            $this->assertEquals($dynamicSearchAttributes, $newArray);
        }

        public function testSanitizeDynamicSearchAttributesByDesignerTypeForSavingModelWithSanitizableItems()
        {
            $language    = Yii::app()->getLanguage();
            $this->assertEquals($language, 'en');
            $searchModel = new IIISearchFormTestModel(new III());
            $dynamicSearchAttributes = array(
                                        0 => array('attributeIndexOrDerivedType' => 'date__Date',
                                                    'structurePosition'          => '1',
                                                    'date__Date'                 =>
                                                        array('firstDate' => '5/4/2011',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER)),
                                        2 => array('attributeIndexOrDerivedType' => 'date2__Date',
                                                    'structurePosition'          => '2',
                                                    'date2__Date'                =>
                                                        array('firstDate' => '5/6/2011',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER)),
                                        3 => array('attributeIndexOrDerivedType' => 'dateTime__DateTime',
                                                    'structurePosition'          => '1',
                                                    'dateTime__DateTime'         =>
                                                        array('firstDate' => '5/7/2011',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER)),
                                        5 => array('attributeIndexOrDerivedType' => 'dateTime2__DateTime',
                                                    'structurePosition'          => '2',
                                                    'dateTime2__DateTime'        =>
                                                        array('firstDate' => '5/8/2011',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER)),
                                        );
            $newArray = SearchUtil::sanitizeDynamicSearchAttributesByDesignerTypeForSavingModel($searchModel,
                                                                                                $dynamicSearchAttributes);
            $compareData = array(
                                        0 => array('attributeIndexOrDerivedType' => 'date__Date',
                                                    'structurePosition'          => '1',
                                                    'date__Date'                 =>
                                                        array('firstDate' => '2011-05-04',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER)),
                                        2 => array('attributeIndexOrDerivedType' => 'date2__Date',
                                                    'structurePosition'          => '2',
                                                    'date2__Date'                =>
                                                        array('firstDate' => '2011-05-06',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER)),
                                        3 => array('attributeIndexOrDerivedType' => 'dateTime__DateTime',
                                                    'structurePosition'          => '1',
                                                    'dateTime__DateTime'         =>
                                                        array('firstDate' => '2011-05-07',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER)),
                                        5 => array('attributeIndexOrDerivedType' => 'dateTime2__DateTime',
                                                    'structurePosition'          => '2',
                                                    'dateTime2__DateTime'        =>
                                                        array('firstDate' => '2011-05-08',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER)),
                                        );
            $this->assertEquals($compareData, $newArray);
        }

        public function testSanitizeDynamicSearchAttributesByDesignerTypeForSavingModelWithSanitizableItemsNestedSingleLevel()
        {
            $language    = Yii::app()->getLanguage();
            $this->assertEquals($language, 'en');
            $searchModel = new CCCSearchFormTestModel(new CCC());
            $dynamicSearchAttributes = array(
                                        0 => array('attributeIndexOrDerivedType' => 'iii' . FormModelUtil::RELATION_DELIMITER . 'date__Date',
                                                    'structurePosition'          => '1',
                                                    'iii'                        => array(
                                                    'relatedModelData'           => true,
                                                    'date__Date'                 =>
                                                        array('firstDate' => '5/4/1011',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER))),
                                        1 => array('attributeIndexOrDerivedType' => 'iii' . FormModelUtil::RELATION_DELIMITER . 'dateTime__DateTime',
                                                    'structurePosition'          => '1',
                                                    'iii'                        => array(
                                                    'relatedModelData'           => true,
                                                    'dateTime__DateTime'         =>
                                                        array('firstDate' => '5/7/2011',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER))),
                                        );

            $newArray = SearchUtil::sanitizeDynamicSearchAttributesByDesignerTypeForSavingModel($searchModel,
                                                                                                $dynamicSearchAttributes);
            $compareData = array(
                                        0 => array('attributeIndexOrDerivedType' => 'iii' . FormModelUtil::RELATION_DELIMITER . 'date__Date',
                                                    'structurePosition'          => '1',
                                                    'iii'                        => array(
                                                    'relatedModelData'           => true,
                                                    'date__Date'                 =>
                                                        array('firstDate' => '1011-05-04',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER))),
                                        1 => array('attributeIndexOrDerivedType' => 'iii' . FormModelUtil::RELATION_DELIMITER . 'dateTime__DateTime',
                                                    'structurePosition'          => '1',
                                                    'iii'                        => array(
                                                    'relatedModelData'           => true,
                                                    'dateTime__DateTime'         =>
                                                        array('firstDate' => '2011-05-07',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER))),
                                        );
            $this->assertEquals($compareData, $newArray);
        }

        public function testSanitizeDynamicSearchAttributesByDesignerTypeForSavingModelWithSanitizableItemsNestedNMultipleLevelsDeep()
        {
            $language    = Yii::app()->getLanguage();
            $this->assertEquals($language, 'en');
            $searchModel = new AAASearchFormTestModel(new AAA());
            $dynamicSearchAttributes = array(
                                        0 => array('attributeIndexOrDerivedType' => 'bbb' .
                                                    FormModelUtil::RELATION_DELIMITER . 'ccc' .
                                                    FormModelUtil::RELATION_DELIMITER . 'iii' .
                                                    FormModelUtil::RELATION_DELIMITER . 'date__Date',
                                                    'structurePosition'          => '1',
                                                    'bbb'                        => array(
                                                    'relatedModelData'           => true,
                                                    'ccc'                        => array(
                                                    'relatedModelData'           => true,
                                                    'iii'                        => array(
                                                    'relatedModelData'           => true,
                                                    'date__Date'                 =>
                                                        array('firstDate' => '5/4/2011',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER))))),
                                        1 => array('attributeIndexOrDerivedType' => 'bbb' .
                                                    FormModelUtil::RELATION_DELIMITER . 'ccc' .
                                                    FormModelUtil::RELATION_DELIMITER . 'iii' .
                                                    FormModelUtil::RELATION_DELIMITER . 'dateTime__DateTime',
                                                    'structurePosition'          => '1',
                                                    'bbb'                        => array(
                                                    'relatedModelData'           => true,
                                                    'ccc'                        => array(
                                                    'relatedModelData'           => true,
                                                    'iii'                        => array(
                                                    'relatedModelData'           => true,
                                                    'date__Date'                 =>
                                                        array('firstDate' => '5/7/2011',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER))))),
                                        );

            $newArray = SearchUtil::sanitizeDynamicSearchAttributesByDesignerTypeForSavingModel($searchModel,
                                                                                                $dynamicSearchAttributes);
            $compareData = array(
                                        0 => array('attributeIndexOrDerivedType' => 'bbb' .
                                                    FormModelUtil::RELATION_DELIMITER . 'ccc' .
                                                    FormModelUtil::RELATION_DELIMITER . 'iii' .
                                                    FormModelUtil::RELATION_DELIMITER . 'date__Date',
                                                    'structurePosition'          => '1',
                                                    'bbb'                        => array(
                                                    'relatedModelData'           => true,
                                                    'ccc'                        => array(
                                                    'relatedModelData'           => true,
                                                    'iii'                        => array(
                                                    'relatedModelData'           => true,
                                                    'date__Date'                 =>
                                                        array('firstDate' => '2011-05-04',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER))))),
                                        1 => array('attributeIndexOrDerivedType' => 'bbb' .
                                                    FormModelUtil::RELATION_DELIMITER . 'ccc' .
                                                    FormModelUtil::RELATION_DELIMITER . 'iii' .
                                                    FormModelUtil::RELATION_DELIMITER . 'dateTime__DateTime',
                                                    'structurePosition'          => '1',
                                                    'bbb'                        => array(
                                                    'relatedModelData'           => true,
                                                    'ccc'                        => array(
                                                    'relatedModelData'           => true,
                                                    'iii'                        => array(
                                                    'relatedModelData'           => true,
                                                    'date__Date'                 =>
                                                        array('firstDate' => '2011-05-07',
                                                              'type'      => MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER))))),
                                        );
            $this->assertEquals($compareData, $newArray);
        }

        public function testGetDynamicSearchStructureFromGetArray()
        {
            $_GET['testing'] = array(
                'a' => '',
            );
            $newString = SearchUtil::getDynamicSearchStructureFromArray('testing', $_GET);
            $this->assertNull($newString);
            $_GET['testing'] = array(
                'a' => null,
                'dynamicStructure' => '1 and 2',
            );
            $newString = SearchUtil::getDynamicSearchStructureFromArray('testing', $_GET);
            $this->assertEquals('1 and 2', $newString);
        }

        /**
         * Checks if the empty values are properly converted to null when nested
         */
        public function testGetSearchAttributesFromSearchArrayWithRecursiveNullResolution()
        {
            $searchArray = array(
                'a' => 'apple',
                'b' => array(
                    'relatedModelData' => true,
                    'bMember' => '',
                ),
                'c' => array(
                    'relatedModelData' => true,
                    'd' => array(
                        'relatedModelData' => true,
                        'dMember' => '',
                    ),
                ),
            );
            $testArray = array(
                'a' => 'apple',
                'b' => array(
                    'relatedModelData' => true,
                    'bMember' => null,
                ),
                'c' => array(
                    'relatedModelData' => true,
                    'd' => array(
                        'relatedModelData' => true,
                        'dMember' => null,
                    ),
                ),
            );
            $newArray = SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            $this->assertEquals($testArray, $newArray);
        }

        public function testGetFilteredByFromArray()
        {
            $getArrayName = 'someArray';
            $result       = SearchUtil::getFilteredByFromArray($getArrayName, $_GET);
            $this->assertNull($result);

            $_GET['someArray']['filteredBy'] = 'all';
            $result       = SearchUtil::getFilteredByFromArray($getArrayName, $_GET);
            $this->assertEquals('all', $result);

            $_GET['someArray']['filteredBy'] = 'none';
            $result       = SearchUtil::getFilteredByFromArray($getArrayName, $_GET);
            $this->assertEquals('none', $result);
        }
    }
?>
