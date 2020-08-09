<?php
/**
 *
 *  @copyright 2008 - https://www.clicshopping.org
 *  @Brand : ClicShopping(Tm) at Inpi all right Reserved
 *  @Licence GPL 2 & MIT
 *  @licence MIT - Portion of osCommerce 2.4
 *  @Info : https://www.clicshopping.org/forum/trademark/
 *
 */

  use ClicShopping\OM\HTML;
  use ClicShopping\OM\Registry;
  use ClicShopping\OM\CLICSHOPPING;

  class fp_categories_images {
    public $code;
    public $group;
    public $title;
    public $description;
    public $sort_order;
    public $enabled = false;

    public function __construct() {
      $this->code = get_class($this);
      $this->group = basename(__DIR__);

      $this->title = CLICSHOPPING::getDef('module_front_page_categories_images_title');
      $this->description = CLICSHOPPING::getDef('module_front_page_categories_images_description');

      if (defined('MODULE_FRONT_PAGE_CATEGORIES_IMAGES_STATUS')) {
        $this->sort_order = MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SORT_ORDER;
        $this->enabled = (MODULE_FRONT_PAGE_CATEGORIES_IMAGES_STATUS == 'True');
      }
    }

    public function execute() {
      $CLICSHOPPING_Db = Registry::get('Db');
      $CLICSHOPPING_Template = Registry::get('Template');
      $CLICSHOPPING_Language = Registry::get('Language');
      $CLICSHOPPING_Category = Registry::get('Category');
      $CLICSHOPPING_Tree = Registry::get('CategoryTree');

      if (CLICSHOPPING::getBaseNameIndex() && !$CLICSHOPPING_Category->getPath()) {

// nbr of column to display  boostrap
        $bootstrap_column = (int)MODULE_FRONT_PAGE_CATEGORIES_IMAGES_COLUMNS;

        $Qcategories = $CLICSHOPPING_Db->prepare('select c.categories_id,
                                                         c.categories_image,
                                                         cd.categories_name
                                                 from :table_categories_description cd join :table_categories c on c.categories_id = cd.categories_id
                                                 where c.parent_id = 0
                                                 and c.status = 1
                                                 and cd.language_id = :language_id
                                                 and virtual_categories = 0
                                                 order by cd.categories_name
                                                ');

        $Qcategories->bindInt(':language_id', $CLICSHOPPING_Language->getId());

        $Qcategories->execute();

        while ($Qcategories->fetch() ) {
          $categories_id = $Qcategories->valueInt('categories_id');
          $categories_data[$categories_id] = ['id' => $categories_id,
                                              'name' => $Qcategories->value('categories_name'),
                                              'image' => $Qcategories->value('categories_image')
                                              ];
        }


// show only if we have categories in the array
        if (count($categories_data) > 0 && !is_null($categories_data)) {
// show the categories in a fixed grid (# of columns is set in admin)
          $categories_content = '<!-- categories frontpage images start -->' . "\n";
          $categories_content .= '<div class="clearfix"></div>';
          $categories_content .=  '<div class="separator"></div>';

          $categories_content .= '<div class="d-flex flex-wrap text-sm-center" itemscope itemtype="https://schema.org/ItemList">';
          $categories_content .= '<meta itemprop="itemListOrder" content="https://schema.org/ItemListUnordered" />';
          $categories_content .= '<meta itemprop="name" content="' . $Qcategories->value('categories_name')  . '" />';

          foreach ($categories_data as $category) {
            $CLICSHOPPING_Tree->getCategoryTreeTitle($category['name']);

            $categories_url = $CLICSHOPPING_Category->getCategoryImageUrl($category['id']);

            $link_categories_image = HTML::link($categories_url, HTML::image($CLICSHOPPING_Template->getDirectoryTemplateImages() . $category['image'], HTML::outputProtected($category['name']), MODULE_FRONT_PAGE_CATEGORIES_IMAGES_WIDTH, MODULE_FRONT_PAGE_CATEGORIES_IMAGES_HEIGHT, null, true));
            $link_categories = HTML::link($categories_url, $category['name']);

            ob_start();
            require($CLICSHOPPING_Template->getTemplateModules($this->group . '/content/categories_images'));
            $categories_content .= ob_get_clean();

         } //foreach ($categories_data

         $categories_content .= '</div>' . "\n";
         $categories_content .= '<!-- categories frontpage images end -->' . "\n";

         $CLICSHOPPING_Template->addBlock($categories_content, $this->group);

        } // end count
      }
    } // public function execute

    public function isEnabled() {
      return $this->enabled;
    }

    public function check() {
      return defined('MODULE_FRONT_PAGE_CATEGORIES_IMAGES_STATUS');
    }

    public function install() {
      $CLICSHOPPING_Db = Registry::get('Db');


      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Do you want to enable this module ?',
          'configuration_key' => 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_STATUS',
          'configuration_value' => 'True',
          'configuration_description' => 'Do you want to enable this module in your shop ?',
          'configuration_group_id' => '6',
          'sort_order' => '1',
          'set_function' => 'clic_cfg_set_boolean_value(array(\'True\', \'False\'))',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Do you want to display category names?',
          'configuration_key' => 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_DISPLAY_TITLE',
          'configuration_value' => 'True',
          'configuration_description' => 'Displays the category name',
          'configuration_group_id' => '6',
          'sort_order' => '2',
          'set_function' => 'clic_cfg_set_boolean_value(array(\'True\', \'False\'))',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Would you like to display images ?',
          'configuration_key' => 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_DISPLAY',
          'configuration_value' => 'True',
          'configuration_description' => 'Displays small category images',
          'configuration_group_id' => '6',
          'sort_order' => '3',
          'set_function' => 'clic_cfg_set_boolean_value(array(\'True\', \'False\'))',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'How many columns do you want to display ?',
          'configuration_key' => 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_COLUMNS',
          'configuration_value' => '6',
          'configuration_description' => 'Please indicate the number of product columns to display per line',
          'configuration_group_id' => '6',
          'sort_order' => '4',
          'set_function' => 'clic_cfg_set_content_module_width_pull_down',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Please indicate the width of the image',
          'configuration_key' => 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_WIDTH',
          'configuration_value' => '',
          'configuration_description' => 'Displays a size delimited in width (resizing)',
          'configuration_group_id' => '6',
          'sort_order' => '5',
          'set_function' => '',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Please indicate the height of the image',
          'configuration_key' => 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_HEIGHT',
          'configuration_value' => '',
          'configuration_description' => 'Displays a size delimited in height (resizing)',
          'configuration_group_id' => '6',
          'sort_order' => '6',
          'set_function' => '',
          'date_added' => 'now()'
        ]
      );


      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Sort order',
          'configuration_key' => 'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SORT_ORDER',
          'configuration_value' => '100',
          'configuration_description' => 'Sort order of display. Lowest is displayed first. The sort order must be different on every module',
          'configuration_group_id' => '6',
          'sort_order' => '7',
          'set_function' => '',
          'date_added' => 'now()'
        ]
      );
    }

    public function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    public function keys() {
      return array('MODULE_FRONT_PAGE_CATEGORIES_IMAGES_STATUS',
                    'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_DISPLAY_TITLE',
                    'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_DISPLAY',
                    'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_COLUMNS',
                    'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_WIDTH',
                    'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_HEIGHT',
                    'MODULE_FRONT_PAGE_CATEGORIES_IMAGES_SORT_ORDER'
                  );
    }
  }

