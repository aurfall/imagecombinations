<?php
/**
* Copyright (c) 2012-2023 Juan Jose Merino Bernabeu

* Permission is hereby granted, free of charge, to any person obtaining
* a copy of this software and associated documentation files (the
* "Software"), to deal in the Software without restriction, including
* without limitation the rights to use, copy, modify, merge, publish,
* distribute, sublicense, and/or sell copies of the Software, and to
* permit persons to whom the Software is furnished to do so, subject to
* the following conditions:

* The above copyright notice and this permission notice shall be
* included in all copies or substantial portions of the Software.

* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
* EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
* NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
* LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
* OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
* WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class Imagecombinations extends Module
{
    public function __construct()
    {
        $this->name = 'imagecombinations';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Juan Jose Merino Bernabeu';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Associate Images to Combinations');
        $this->description = $this->l('Associate images to combinations by attribute group');
    }

    public function getContent()
    {
        $step = Tools::getValue('step', 'start');

        switch ($step) {
            case 'start':
                return $this->renderFormSearch();
                break;
            case 'group':
                $id_product = Tools::getValue('id_product');
                return $this->renderFormGroup($id_product);
                break;
            case 'search':
                $id_product = Tools::getValue('id_product');
                $id_attribute_group = Tools::getValue('id_attribute_group');

                $p = new Product($id_product);
                $product_images = $p->getImages($this->context->language->id);
                // Get image urls
                $images = [];
                foreach ($product_images as &$image) {
                    $link = $this->context->link->getImageLink($p->link_rewrite[$this->context->language->id], $image['id_image'], 'home_default');
                    $images[] = ['id_image' => $image['id_image'], 'link' => $link];
                }

                $product_attributes_ids = Product::getProductAttributesIds($id_product, true);
                $product_attributes = [];
                
                foreach ($product_attributes_ids as $pa_ids) {
                    $attr[]  = Product::getAttributesParams($id_product, $pa_ids['id_product_attribute']);                    
                                        
                    foreach ($attr as $aa) {                        
                        foreach ($aa as $a) {                            
                            if ($a['id_attribute_group'] == $id_attribute_group) {
                                $product_attribute_name = $a['name'];
                                $product_attributes[$a['id_attribute']] = ['id_attribute' => $a['id_attribute'], 'name' => $product_attribute_name];
                            }
                        }                        
                    }
                }

                $this->context->smarty->assign(array(
                    'images' => $images,
                    'product_attributes' => $product_attributes,
                    'id_attribute_group' => $id_attribute_group,
                    'id_product' => $id_product
                ));

                return $this->display(__FILE__, 'views/templates/admin/combinations.tpl');
            case 'assign':
                $id_product = Tools::getValue('id_product');
                $id_attribute_group = Tools::getValue('id_attribute_group');
                $images = Tools::getValue('images');

                $this->assigImages($id_product, $id_attribute_group, $images);
                $html = $this->displayInformation($this->l('Images assigned successfully'));
                return $html.$this->renderFormSearch();
                break;
            default:
                return $this->renderFormSearch();
                break;
        }
    }

    public function hookBackofficeHeader()
    {
        $configure = Tools::getValue('configure', false);

        if ($configure != $this->name) {
            return false;
        }

        $this->context->controller->addJs(_PS_MODULE_DIR_.$this->name.'/views/js/back_customs.js');
        $this->context->controller->addCss(_PS_MODULE_DIR_.$this->name.'/views/css/back_customs.css');

        $filter_params = [];
        $filter_params['ajax'] = 1;
        $filter_params['action'] = 'productsList';
        $filter_params['forceJson'] = 1;
        $filter_params['disableCombination'] = 1;
        $filter_params['exclude_packs'] = 0;
        $filter_params['excludeVirtuals'] = 0;
        $filter_params['limit'] = 20;

        Media::addJsDef(array(
                'pr_customs_route' => $this->context->link->getLegacyAdminLink('AdminProducts', true, $filter_params),
        ));

        $filter_params['default_category'] = Configuration::get('CUSTOMS_DEFAULT_CATEGORY');

        Media::addJsDef(array(
            'customs_route' => $this->context->link->getLegacyAdminLink('AdminProducts', true, $filter_params),
        ));

        $this->context->controller->addjQueryPlugin(['autocomplete']);

        return true;
    }

    public function renderFormSearch()
    {
        $fields_form = ['form' => [
            'legend' => [
                'title' => $this->trans('Feature', [], 'Admin.Catalog.Feature'),
                'icon' => 'icon-info-sign',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->trans('Product'),
                    'name' => 'product',
                    'class' => 'prod_autocomplete',
                ],
                [
                    'type' => 'text',
                    'name' => 'id_product',
                    'class' => 'hidden_field'
                ],
                [
                    'type' => 'text',
                    'name' => 'step',
                    'class' => 'hidden_field'
                ],
            ],
            'submit' => [
                'title' => $this->trans('Next', [], 'Admin.Actions'),
            ]
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        
        $field_values['step'] = 'group';

        $helper->submit_action = 'submitConfig';
        $admin_url = $this->context->link->getAdminLink('AdminModules', false);
        $admin_url .= '&configure='.$this->name.'&tab_module='.$this->tab;
        $helper->currentIndex = $admin_url.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $field_values,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm([$fields_form]);
    }

    public function getAttributeGroupByProduct($id_product)
    {
        $query = new DbQuery();
        $query->select('b.id_attribute_group');
        $query->from('product_attribute_combination', 'a');
        $query->leftJoin('attribute', 'b', 'b.id_attribute = a.id_attribute');
        $query->leftJoin('product_attribute', 'c', 'c.id_product_attribute = a.id_product_attribute');
        $query->where('c.id_product = '.(int) $id_product);
        $query->groupBy('b.id_attribute_group');

        $res = Db::getInstance()->executeS($query);        

        $processed_groups = [];

        foreach ($res as $r) {
            $processed_groups[$r['id_attribute_group']] = true;
        }

        return $processed_groups;
    }

    public function renderFormGroup($id_product)
    {
        $attribute_groups = AttributeGroup::getAttributesGroups($this->context->language->id);
        $processed_groups = $this->getAttributeGroupByProduct($id_product);

        foreach ($attribute_groups as $ag) {
            if (isset($processed_groups[$ag['id_attribute_group']])) {
                $options[] = ['id_option' => $ag['id_attribute_group'], 'name' => $ag['id_attribute_group'].' - '.$ag['name']];
            }
        }

        $fields_form = ['form' => [
            'legend' => [
                'title' => $this->trans('Feature Groups', [], 'Admin.Catalog.Feature'),
                'icon' => 'icon-info-sign',
            ],
            'input' => [
                [
                    'type' => 'select',
                    'label' => $this->l('Attributes Group:'),
                    'name' => 'id_attribute_group',
                    'required' => true,
                    'options' => [
                        'query' => $options,
                        'id' => 'id_option',
                        'name' => 'name'
                    ]
                ],
                [
                    'type' => 'text',
                    'name' => 'id_product',
                    'class' => 'hidden_field'
                ],
                [
                    'type' => 'text',
                    'name' => 'step',
                    'class' => 'hidden_field'
                ],
            ],
            'submit' => [
                'title' => $this->trans('Next', [], 'Admin.Actions'),
            ]
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        
        $field_values['id_product'] = Tools::getValue('id_product');
        $field_values['step'] = 'search';

        $helper->submit_action = 'submitConfig';
        $admin_url = $this->context->link->getAdminLink('AdminModules', false);
        $admin_url .= '&configure='.$this->name.'&tab_module='.$this->tab;
        $helper->currentIndex = $admin_url.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $field_values,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm([$fields_form]);
    }

    public function assigImages($id_product, $id_attribute_group, $images)
    {        
        $product_attributes_ids = Product::getProductAttributesIds($id_product, true);

        foreach ($product_attributes_ids as $pa_ids) {
            Db::getInstance()->delete('product_attribute_image', 'id_product_attribute = '.(int) $pa_ids['id_product_attribute']);
        }

        $update_product_attribute = [];

        foreach ($images as $id_image => $attributes) {
            foreach ($attributes as $id_attribute) {
                foreach ($product_attributes_ids as $pa_ids) {

                    $query = new DbQuery();
                    $query->select('a.id_product_attribute');
                    $query->from('product_attribute_combination', 'a');
                    $query->leftJoin('attribute', 'b', 'b.id_attribute = a.id_attribute');
                    $query->where('a.id_attribute = '.(int) $id_attribute);
                    $query->where('a.id_product_attribute = '.(int) $pa_ids['id_product_attribute']);
                    $query->where('b.id_attribute_group = '.(int) $id_attribute_group);

                    $attr = Db::getInstance()->getValue($query);
                    
                    if ($attr) {
                        $update_product_attribute[$pa_ids['id_product_attribute']] = $id_image;
                    }
                }
            }
        }        

        foreach ($update_product_attribute as $id_product_attribute => $id_image) {
            $data = [];
            $data['id_product_attribute'] = $id_product_attribute;
            $data['id_image'] = $id_image;
            Db::getInstance()->insert('product_attribute_image', $data);
            echo 'Actualizado: '.$id_product_attribute.' - '.$id_image.'<br>';
        }
    }
}
