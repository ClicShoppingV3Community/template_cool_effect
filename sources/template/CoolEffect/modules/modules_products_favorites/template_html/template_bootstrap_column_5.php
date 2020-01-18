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
?>
<div class="col-md-<?php echo $bootstrap_column; ?> col-md-<?php echo $bootstrap_column; ?>">
  <div class="separator"></div>
  <div class="card-deck-wrapper">
    <div class="card-deck">
      <div class="card">
        <div class="hovereffect">
          <div class="card-block">
            <div class="separator"></div>
              <div class="card-img-top ModulesProductsFavoritesBoostrapColumn5Image">
              <?php echo $products_image . $ticker; ?>
              <div class="overlay">
                <h2><?php echo HTML::link($products_name_url, $products_name .  ' ' . $product_price); ?></h2>
              </div>
            </div>
          </div>
        </div>
      </div>
     </div>
  </div>
  <div class="row">
    <div class="col-md-4">
     <?php
        //echo $avg_reviews;
        echo HTML::stars('4');
    ?>
    </div>
    <div class="col-md-8">
      <span><?php echo $product_price; ?> </span>
      <span class="float-md-right text-md-right">
        <?php echo $form . $input_quantity . '  ' . $buy_button . $endform . ' ' .  $button_small_view_details; ?>
      </span>
    </div>
  </div>
</div>
<?php echo $jsonLtd; ?>