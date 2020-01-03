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

  class fp_manufacturers {
    public $code;
    public $group;
    public $title;
    public $description;
    public $sort_order;
    public $enabled = false;

    public function __construct() {
      $this->code = get_class($this);
      $this->group = basename(__DIR__);

      $this->title = CLICSHOPPING::getDef('module_front_page_manufacturers_title');
      $this->description = CLICSHOPPING::getDef('module_front_page_manufacturers_description');

      if (defined('MODULE_FRONT_PAGE_MANUFACTURERS_STATUS')) {
        $this->sort_order = MODULE_FRONT_PAGE_MANUFACTURERS_SORT_ORDER;
        $this->enabled = (MODULE_FRONT_PAGE_MANUFACTURERS_STATUS == 'True');
      }
    }

    public function execute() {
      $CLICSHOPPING_Db = Registry::get('Db');
      $CLICSHOPPING_Template = Registry::get('Template');
      $CLICSHOPPING_Category = Registry::get('Category');
      $CLICSHOPPING_Manufacturers = Registry::get('Manufacturers');

      if (CLICSHOPPING::getBaseNameIndex() && !$CLICSHOPPING_Category->getPath())  {
// nbr of column to display  boostrap
        $bootstrap_column = (int)MODULE_FRONT_PAGE_MANUFACTURERS_COLUMNS;

        if (!isset($_GET['manufacturers_id'])) {

          $Qmanufacturer = $CLICSHOPPING_Db->prepare('select manufacturers_id,
                                                             manufacturers_image,
                                                             manufacturers_name
                                                       from :table_manufacturers
                                                       where manufacturers_status = 0
                                                       order by manufacturers_name
                                                       limit :limit
                                                      ');
          $Qmanufacturer->bindInt(':limit', MODULE_FRONT_PAGE_MANUFACTURERS_LIMIT);

          $Qmanufacturer->execute();

        } else {

          $Qmanufacturer = $CLICSHOPPING_Db->prepare('select distinct m.manufacturers_id,
                                                                       m.manufacturers_image,
                                                                       m.manufacturers_name
                                                       from :table_manufacturers m,
                                                            :table_products p,
                                                            :table_products_to_categories p2c,
                                                            :table_categories c
                                                       where m.manufacturers_status = 0
                                                       and p.manufacturers_id = :manufacturers_id
                                                       and p.products_id = p2c.products_id
                                                       and p2c.categories_id = c.categories_id
                                                       and c.status = 1
                                                       order by m.manufacturers_name
                                                       limit :limit
                                                      ');
          $Qmanufacturer->bindInt(':limit', MODULE_FRONT_PAGE_MANUFACTURERS_LIMIT);
          $Qmanufacturer->bindInt(':manufacturers_id', $_GET['manufacturers_id']);

          $Qmanufacturer->execute();
        }

        if ($Qmanufacturer->rowCount() > 0) {
          $manufacturers_content = '<!-- manufacturer front page start -->' . "\n";
          $manufacturers_content .= '<div class="clearfix"></div>';
          $manufacturers_content .= '<div class="separator"></div>';
          $manufacturers_content .= '<div class="d-flex flex-wrap  text-md-center">';

          while ($Qmanufacturer->fetch() ) {
            $manufacturer_url = $CLICSHOPPING_Manufacturers->getManufacturerUrlRewrited()->getManufacturerUrl($Qmanufacturer->valueInt('manufacturers_id'));

            $image = HTML::link($manufacturer_url, HTML::image($CLICSHOPPING_Template->getDirectoryTemplateImages() .  $Qmanufacturer->value('manufacturers_image'), HTML::outputProtected($Qmanufacturer->value('manufacturers_name')), MODULE_FRONT_PAGE_MANUFACTURERS_WIDTH, MODULE_FRONT_PAGE_MANUFACTURERS_HEIGHT, null, true));
            $manufacturer = HTML::link($manufacturer_url, $Qmanufacturer->value('manufacturers_name'));

            ob_start();
            require($CLICSHOPPING_Template->getTemplateModules($this->group . '/content/manufacturers'));
            $manufacturers_content .= ob_get_clean();
           }

           $manufacturers_content .= '</div>' . "\n";
           $manufacturers_content .= '<!-- manufacturer front page end -->' . "\n";

           $CLICSHOPPING_Template->addBlock($manufacturers_content, $this->group);
        } // end count
      }
    } // public function execute

    public function isEnabled() {
      return $this->enabled;
    }

    public function check() {
      return defined('MODULE_FRONT_PAGE_MANUFACTURERS_STATUS');
    }

    public function install() {
      $CLICSHOPPING_Db = Registry::get('Db');


      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Souhaitez-vous activer ce module ?',
          'configuration_key' => 'MODULE_FRONT_PAGE_MANUFACTURERS_STATUS',
          'configuration_value' => 'True',
          'configuration_description' => 'Souhaitez vous activer ce module à votre boutique ?',
          'configuration_group_id' => '6',
          'sort_order' => '1',
          'set_function' => 'clic_cfg_set_boolean_value(array(\'True\', \'False\'))',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Do you want to display the names of the manufacturer models ?',
          'configuration_key' => 'MODULE_FRONT_PAGE_MANUFACTURERS_DISPLAY_TITLE',
          'configuration_value' => 'True',
          'configuration_description' => 'Displays the model name of the manufacturers',
          'configuration_group_id' => '6',
          'sort_order' => '2',
          'set_function' => 'clic_cfg_set_boolean_value(array(\'True\', \'False\'))',
          'date_added' => 'now()'
        ]
      );


      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Please indicate the maximum number of manufacturer models to display',
          'configuration_key' => 'MODULE_FRONT_PAGE_MANUFACTURERS_LIMIT',
          'configuration_value' => '10',
          'configuration_description' => 'Displays a specified number of manufacturers',
          'configuration_group_id' => '6',
          'sort_order' => '6',
          'set_function' => '',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Would you like to display images ?',
          'configuration_key' => 'MODULE_FRONT_PAGE_MANUFACTURERS_DISPLAY',
          'configuration_value' => 'True',
          'configuration_description' => 'Affiche les petites images des modèles de fabriquant<br><br><i>(Valeur True = Oui - Valeur False = Non)</i>',
          'configuration_group_id' => '6',
          'sort_order' => '3',
          'set_function' => 'clic_cfg_set_boolean_value(array(\'True\', \'False\'))',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Veuillez indiquer le nombre de colonnes que vous souhaitez voir affiché  ?',
          'configuration_key' => 'MODULE_FRONT_PAGE_MANUFACTURERS_COLUMNS',
          'configuration_value' => '6',
          'configuration_description' => 'Veuillez indiquer le nombre de colonnesà afficher par ligne.<br /><br />Note:<br /><br />- Entre 1 et 12',
          'configuration_group_id' => '6',
          'sort_order' => '6',
          'set_function' => 'clic_cfg_set_content_module_width_pull_down',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Please indicate the width of the image',
          'configuration_key' => 'MODULE_FRONT_PAGE_MANUFACTURERS_WIDTH',
          'configuration_value' => '200',
          'configuration_description' => 'Displays a size delimited in width (resizing)',
          'configuration_group_id' => '6',
          'sort_order' => '5',
          'set_function' => '',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Please indicate the height of the image',
          'configuration_key' => 'MODULE_FRONT_PAGE_MANUFACTURERS_HEIGHT',
          'configuration_value' => '200',
          'configuration_description' => 'Displays a size delimited in height (resizing)',
          'configuration_group_id' => '6',
          'sort_order' => '6',
          'set_function' => '',
          'date_added' => 'now()'
        ]
      );


      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Ordre de tri d\'affichage',
          'configuration_key' => 'MODULE_FRONT_PAGE_MANUFACTURERS_SORT_ORDER',
          'configuration_value' => '100',
          'configuration_description' => 'Ordre de tri pour l\'affichage (Le plus petit nombre est montré en premier)',
          'configuration_group_id' => '6',
          'sort_order' => '7',
          'set_function' => '',
          'date_added' => 'now()'
        ]
      );

      return $CLICSHOPPING_Db->save('configuration', ['configuration_value' => '1'],
                                               ['configuration_key' => 'WEBSITE_MODULE_INSTALLED']
      );

    }

    public function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    public function keys() {
      return array ('MODULE_FRONT_PAGE_MANUFACTURERS_STATUS',
                    'MODULE_FRONT_PAGE_MANUFACTURERS_DISPLAY_TITLE',
                    'MODULE_FRONT_PAGE_MANUFACTURERS_LIMIT',
                    'MODULE_FRONT_PAGE_MANUFACTURERS_DISPLAY',
                    'MODULE_FRONT_PAGE_MANUFACTURERS_COLUMNS',
                    'MODULE_FRONT_PAGE_MANUFACTURERS_WIDTH',
                    'MODULE_FRONT_PAGE_MANUFACTURERS_HEIGHT',
                    'MODULE_FRONT_PAGE_MANUFACTURERS_SORT_ORDER'
                  );
    }
  }

