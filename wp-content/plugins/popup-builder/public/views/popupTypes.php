<?php
use sgpb\AdminHelper;
use sgpb\PopupType;
$popupTypes = sgpb\SGPopup::getPopupTypes();
global $SGPB_POPUP_TYPES;
$labels = $SGPB_POPUP_TYPES['typeLabels'];
?>
<div class="sgpb sgpb-main-page sgpb-padding-x-20">
	<div class='sgpb-heading sgpb-margin-y-50  sgpb-display-flex sgpb-justify-content-between'>
		<h1 class="sgpb-header-h1 "><?php _e('Add New Popup', SG_POPUP_TEXT_DOMAIN); ?></h1>
        <button class="sgpb-btn sgpb-btn--rounded sgpb-btn-more_extensions sgpb-display-flex sgpb-justify-content-between sgpb-align-item-center" onclick="window.open('<?php echo SG_POPUP_ALL_EXTENSIONS_URL ;?>', '_blank')">
            <span class="sgpb-cubes sgpb-margin-right-10">
                <span class="sgpb-cubes-mini"></span>
                <span class="sgpb-cubes-mini"></span>
                <span class="sgpb-cubes-mini"></span>
                <span class="sgpb-cubes-mini sgpb-cubes-mini_little"></span>
            </span>
            <span class="sgpb-button__span"><?php _e('Get More Extensions', SG_POPUP_TEXT_DOMAIN) ?></span>
        </button>
	</div>
	<div class="sgpb-addPopup sgpb-margin-y-50 sgpb-display-flex">
		<?php foreach ($popupTypes as $popupType): ?>
			<?php $type = $popupType->getName(); ?>
			<?php
			$isAvaliable = $popupType->isAvailable();
			if (!$isAvaliable) {
				continue;
			}
			?>
			<div class="sgpb-box sgpb-box-active sgpb-margin-bottom-30 sgpb-position-relative" data-redirect-url="<?php echo AdminHelper::buildCreatePopupUrl($popupType); ?>">
<!--				<div class="sgpb-_blue --><?php //echo AdminHelper::getPopupThumbClass($popupType); ?><!--">-->
					<?php if (defined('SGPB_SUBSCRIPTION_PLUS_CLASSES_PATH') && $type == 'subscription'): ?>
						<img src="<?php echo SG_POPUP_PUBLIC_URL.'icons/white/subscriptionPlus.svg' ;?>" class="sgpb-box-img">
					<?php else: ?>
						<img src="<?php echo SG_POPUP_PUBLIC_URL.'icons/white/'.$type.'.svg' ;?>" class="sgpb-box-img">
					<?php endif; ?>
<!--				</div>-->
				    <p class="sgpb-box-text"><?php echo $labels[$type]; ?></p>
                    <span class="sgpb-box-plus" >L</span>
                </div>
		<?php endforeach; ?>
			<div class="sgpb-box sgpb-margin-bottom-30 sgpb-box-default" data-redirect-url="<?php echo SG_POPUP_ALL_EXTENSIONS_URL; ?>">
                <img src="<?php echo SG_POPUP_PUBLIC_URL.'icons/Black/moreIdeas.svg' ;?>" class="sgpb-box-img">
				<p class="sgpb-box-text"><?php _e('More Ideas?', SG_POPUP_TEXT_DOMAIN); ?></p>
			</div>
	</div>
<?php
$extensions = AdminHelper::getAllExtensions();
if (!empty($extensions['noActive'])) : ?>
    <h1 class="sgpb-header-h1 sgpb-margin-y-50"><?php _e('Pro Extensions', SG_POPUP_TEXT_DOMAIN); ?></h1>
	<div class="sgpb sgpb-pro-extensions sgpb-margin-y-50 sgpb-display-flex">
		<?php foreach ($extensions['noActive'] as $extension) : ?>
			<?php if (isset($extension['availability']) && $extension['availability'] == 'free'): ?>
				<?php continue; ?>
			<?php endif; ?>
			<?php
			$URL = '';
			if (!empty($extension['url'])) {
				$URL = $extension['url'];
			}
			$type = $extension['key'];
			?>
			<div class="sgpb-box sgpb-box-default sgpb-margin-bottom-30" data-redirect-url="<?php echo esc_attr($URL); ?>">
                <img src="<?php echo SG_POPUP_PUBLIC_URL.'icons/Black/'.$type.'.svg' ;?>" class="sgpb-box-img">
				<p class="sgpb-box-text"><?php echo ucfirst($extension['label']); ?></p>
			</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</div>

<script type="text/javascript">
	jQuery(document).ready(function() {
	    jQuery('.sgpb-box').on('click', function () {
            const currentLink = jQuery(this).attr('data-redirect-url');
            if (jQuery(this).hasClass('sgpb-box-default')) {
                window.open(currentLink, '_blank');
            }
            else {
                window.location.href = currentLink;
            }
        });
	});
</script>
