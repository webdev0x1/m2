define(['jquery', 'mage/url', 'mage/translate'], function ($, url, $t) {
    'use strict';

    return function (SwatchRenderer) {
	/**
     * Render tooltips by attributes (only to up).
     * Required element attributes:
     *  - data-option-type (integer, 0-3)
     *  - data-option-label (string)
     *  - data-option-tooltip-thumb
     *  - data-option-tooltip-value
     *  - data-thumb-width
     *  - data-thumb-height
     */
    $.widget('mage.SwatchRendererTooltip', {
        options: {
            delay: 200,                             //how much ms before tooltip to show
            tooltipClass: 'swatch-option-tooltip'  //configurable, but remember about css
        },

        /**
         * @private
         */
        _init: function () {
            var $widget = this,
                $this = this.element,
                $element = $('.' + $widget.options.tooltipClass),
                timer,
                type = parseInt($this.data('option-type'), 10),
                label = $this.data('option-label'),
                thumb = $this.data('option-tooltip-thumb'),
                value = $this.data('option-tooltip-value'),
                width = $this.data('thumb-width'),
                height = $this.data('thumb-height'),
                $image,
                $title,
                $corner;

            if (!$element.length) {
                $element = $('<div class="' +
                    $widget.options.tooltipClass +
                    '"><div class="image"></div><div class="title"></div><div class="corner"></div></div>'
                );
                $('body').append($element);
            }

            $image = $element.find('.image');
            $title = $element.find('.title');
            $corner = $element.find('.corner');

            $this.hover(function () {
                if (!$this.hasClass('disabled')) {
                    timer = setTimeout(
                        function () {
                            var leftOpt = null,
                                leftCorner = 0,
                                left,
                                $window;
		//		type = 2;
                            if (type === 2) {
                                // Image
                                $image.css({
                                    'background': 'url("' + thumb + '") no-repeat center', //Background case
                                    'background-size': 'initial',
                                    'width': width + 'px',
                                    'height': height + 'px'
                                });
                                $image.show();
                            } else if (type === 1) {
				if(thumb) {
				       // Image
	                                $image.css({
        	                            'background': 'url("' + thumb + '") no-repeat center', //Background case
                	                    'background-size': 'initial',
                        	            'width': width + 'px',
                                	    'height': height + 'px'
                                	});
                                $image.show();
				} else {
                                // Color
                                	$image.css({
	                                    background: value
        	                        });
                	                $image.show();
				}
                            } else if (type === 0 || type === 3) {
				 if(thumb) {
                                       // Image
                                        $image.css({
                                            'background': 'url("' + thumb + '") no-repeat center', //Background case
                                            'background-size': 'initial',
                                            'width': width + 'px',
                                            'height': height + 'px'
                                        });
                                $image.show();
                                } else {
                                	// Default
	                                $image.hide();
				}
                            }

                            $title.text(label);

                            leftOpt = $this.offset().left;
                            left = leftOpt + $this.width() / 2 - $element.width() / 2;
                            $window = $(window);

                            // the numbers (5 and 5) is magick constants for offset from left or right page
                            if (left < 0) {
                                left = 5;
                            } else if (left + $element.width() > $window.width()) {
                                left = $window.width() - $element.width() - 5;
                            }

                            // the numbers (6,  3 and 18) is magick constants for offset tooltip
                            leftCorner = 0;

                            if ($element.width() < $this.width()) {
                                leftCorner = $element.width() / 2 - 3;
                            } else {
                                leftCorner = (leftOpt > left ? leftOpt - left : left - leftOpt) + $this.width() / 2 - 6;
                            }

                            $corner.css({
                                left: leftCorner
                            });
                            $element.css({
                                left: left,
                                top: $this.offset().top - $element.height() - $corner.height() - 18
                            }).show();
                        },
                        $widget.options.delay
                    );
                }
            }, function () {
                $element.hide();
                clearTimeout(timer);
            });

            $(document).on('tap', function () {
                $element.hide();
                clearTimeout(timer);
            });

            $this.on('tap', function (event) {
                event.stopPropagation();
            });
        }
    });

        $.widget('mage.SwatchRenderer', $['mage']['SwatchRenderer'], {
	    options: {
            classes: {
                attributeClass: 'swatch-attribute',
                attributeLabelClass: 'swatch-attribute-label',
                attributeSelectedOptionLabelClass: 'swatch-attribute-selected-option',
                attributeOptionsWrapper: 'swatch-attribute-options',
                attributeInput: 'swatch-input',
                optionClass: 'swatch-option',
                selectClass: 'swatch-select',
                moreButton: 'swatch-more',
                loader: 'swatch-option-loading'
            },
            // option's json config
            jsonConfig: {},

            // swatch's json config
            jsonSwatchConfig: {},

            // selector of parental block of prices and swatches (need to know where to seek for price block)
            selectorProduct: '.product-info-main',

            // selector of price wrapper (need to know where set price)
            selectorProductPrice: '[data-role=priceBox]',

            //selector of product images gallery wrapper
            mediaGallerySelector: '[data-gallery-role=gallery-placeholder]',

            // selector of category product tile wrapper
            selectorProductTile: '.product-item',

            // number of controls to show (false or zero = show all)
            numberToShow: false,

            // show only swatch controls
            onlySwatches: false,

            // enable label for control
            enableControlLabel: true,

            // control label id
            controlLabelId: '',

            // text for more button
            moreButtonText: $t('More'),

            // Callback url for media
            mediaCallback: '',

            // Local media cache
            mediaCache: {},

            // Cache for BaseProduct images. Needed when option unset
            mediaGalleryInitial: [{}],

            // Use ajax to get image data
            useAjax: false,

            /**
             * Defines the mechanism of how images of a gallery should be
             * updated when user switches between configurations of a product.
             *
             * As for now value of this option can be either 'replace' or 'prepend'.
             *
             * @type {String}
             */
            gallerySwitchStrategy: 'replace',

            // whether swatches are rendered in product list or on product page
            inProductList: true,

            // sly-old-price block selector
            slyOldPriceSelector: '.sly-old-price',

            // tier prise selectors start
            tierPriceTemplateSelector: '#tier-prices-template',
            tierPriceBlockSelector: '[data-role="tier-price-block"]',
            tierPriceTemplate: '',
            // tier prise selectors end

            // A price label selector
            normalPriceLabelSelector: '.product-info-main .normal-price .price-label'
        },


            _init: function () {
                console.log('getProductSwatchRenderer');
                this._super();
            },

	/**
         * Render select by part of config
         *
         * @param {Object} config
         * @param {String} chooseText
         * @returns {String}
         * @private
         */
        _RenderSwatchSelect: function (config, chooseText) {
            var mediaUrl = this.options.mediaUrl,
		optionSwatches = this.options.optionSwatches,
                isEnabled = this.options.isEnabled,
		moreLimit = parseInt(this.options.numberToShow, 10),
                moreClass = this.options.classes.moreButton,
                moreText = this.options.moreButtonText,
                countAttributes = 0,
		html = '';

	    if(isEnabled) {
/*		html = '';
		// Force image
    	       $.each(config.options, function () {
		       var id,
                    value,
                    thumb,
                    width,
                    height,
                    attr,
                    swatchImageWidth,
                    swatchImageHeight,
		    label = this.label,
		    attr = ' value="' + this.id + '" data-option-id="' + this.id + '"';

                // Add more button
                if (moreLimit === countAttributes++) {
                    html += '<a href="#" class="' + moreClass + '"><span>' + moreText + '</span></a>';
                }

                id = this.id;

                value = this.id;
                thumb = '';
                width = 110;
                height = 90;
                label = this.label;

                attr =
                    ' id="item-' + id + '"' +
                    ' aria-checked="false"' +
                    ' aria-describedby="' + id + '"' +
                    ' tabindex="0"' +
                    ' data-option-id="' + id + '"' +
                    ' data-option-label="' + label + '"' +
                    ' aria-label="' + label + '"' +
                    ' role="option"' +
                    ' data-thumb-width="' + width + '"' +
                    ' data-thumb-height="' + height + '"';

                attr += thumb !== '' ? ' data-option-tooltip-thumb="' + thumb + '"' : '';
                attr += value !== '' ? ' data-option-tooltip-value="' + value + '"' : '';

                swatchImageWidth =  30;
                swatchImageHeight = 20;

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' data-option-empty="true"';
                }
html += '<div class="' + this.options.classes.selectClass + ' ' + config.code + '" ' + attr +
 ' style="background: url() no-repeat center; background-size: initial;width:' +
                        swatchImageWidth + 'px; height:' + swatchImageHeight + 'px">' + '' +
                        '</div>';
                });
		return html;*/

            var html, optionConfig = this.options.jsonSwatchConfig[config.id], classes = this.options.classes.selectClass;

            if (this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                return '';
            }

	    $.each(config.options, function () {
                       var id,
                    value,
                    thumb,
                    width,
                    height,
                    attr,
		    image,
                    swatchImageWidth,
                    swatchImageHeight,
                    label,// = this.label,
                    attr;// = ' value="' + this.id + '" data-option-id="' + this.id + '"';

                // Add more button
                if (moreLimit === countAttributes++) {
                    html += '<a href="#" class="' + moreClass + '"><span>' + moreText + '</span></a>';
                }

                id = this.id;

                value = this.id;
                thumb = '';
                width = 110;
                height = 90;
                label = this.label;
		
                attr =
                    ' id="item-' + id + '"' +
                    ' aria-checked="false"' +
                    ' aria-describedby="' + id + '"' +
                    ' tabindex="0"' +
		    ' data-option-type="' + 2 + '"' +
                    ' data-option-id="' + id + '"' +
                    ' data-option-label="' + label + '"' +
                    ' aria-label="' + label + '"' +
                    ' role="option"' +
                    ' data-thumb-width="' + width + '"' +
                    ' data-thumb-height="' + height + '"';

		 $.each(optionSwatches, function(index, value){
  //                            console.log(index);
                        if(value.swatch_image)
                        $.each(value, function(index, val) {
//                              console.log("Attr name" +value.attribute_name);
//                              console.log("Attr Name lowercase" + value.attribute_name.toLowerCase());
//                              console.log("config " + JSON.stringify(config));
//                              console.log("attr value" + value.attribute_value);
//                              console.log("val" + JSON.stringify(val));
//                              console.log("label" + label);
//                              console.log('+++++++');
                           if(value.attribute_name.toLowerCase() == config.code && value.attribute_value.toLowerCase() == label.toLowerCase()) {
                                console.log("Image" + val.name);
                                   console.log('------');
                                image = mediaUrl + 'ocacia_swatches/' + val.name;
                           }
                        });
                 });

		

                attr += image !== '' ? ' data-option-tooltip-thumb="' + image + '"' : '';
                attr += image !== '' ? ' data-option-tooltip-value="' + image + '"' : '';

                swatchImageWidth =  30;
                swatchImageHeight = 20;

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' data-option-empty="true"';
                }
		if(image) {
html += '<div class="swatch-option select image"' + attr +
 ' style="background: url(' + image  + ') no-repeat center; background-size: initial;width:' +
                        swatchImageWidth + 'px; height:' + swatchImageHeight + 'px">' + '' +
                        '</div>';
	        } else {
			html += '<div class="swatch-option select image"' + attr + '">' + label +
                        '</div>';
		}
		});
		return html;
	    } else {
	     var html;

            if (this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                return '';
            }

            html =
                '<select class="' + this.options.classes.selectClass + ' ' + config.code + '">' +
                '<option value="0" data-option-id="0">' + chooseText + '</option>';
	    

            $.each(config.options, function () {
                var label = this.label,
                    attr = ' value="' + this.id + '" data-option-id="' + this.id + '"';

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' data-option-empty="true"';
                }

                html += '<option ' + attr + '>' + label + '</option>';
            });

            html += '</select>';

            return html;
	    }
        },

	        /**
         * Event for swatch options
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnClick: function ($this, $widget) {
		if($this.hasClass('swatch-option select')) {
			var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
				$wrapper = $this.parents('.' + $widget.options.classes.attributeOptionsWrapper),
				$label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
	                attributeId = $parent.data('attribute-id'),
        	        $input = $parent.find('.' + $widget.options.classes.attributeInput);

            		if ($widget.productForm.length > 0) {
		                $input = $widget.productForm.find(
                	    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                	);
            	}
/*		if($this.hasClass('selected')) {
			$this.removeClass('selected');
		} else {
			$('.swatch-option.select').removeClass('selected');
			$this.addClass('selected');
		}*/

		if ($this.hasClass('selected')) {
                $parent.removeAttr('data-option-selected').find('.selected').removeClass('selected');
                $input.val('');
                $label.text('');
                $this.attr('aria-checked', false);
            } else {
                $parent.attr('data-option-selected', $this.data('option-id')).find('.selected').removeClass('selected');
                $label.text($this.data('option-label'));
                $input.val($this.data('option-id'));
                $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
                $this.addClass('selected');
                $widget._toggleCheckedAttributes($this, $wrapper);
            }


                if ($this.hasClass('selected') && $this.attr('data-option-id') > 0) {
                	$parent.attr('data-option-selected', $this.attr('data-option-id'));
	                $input.val($this.attr('data-option-id'));
                } else {
            	    $parent.removeAttr('data-option-selected');
                	$input.val('');
            	}

	            $widget._Rebuild();
        	    $widget._UpdatePrice();
	            $widget._loadMedia();
        	    $input.trigger('change');


		} else {
  	
			var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                	$wrapper = $this.parents('.' + $widget.options.classes.attributeOptionsWrapper),
	                $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
        	        attributeId = $parent.data('attribute-id'),
                	$input = $parent.find('.' + $widget.options.classes.attributeInput),
	                checkAdditionalData = JSON.parse(this.options.jsonSwatchConfig[attributeId]['additional_data']),
        	        $priceBox = $widget.element.parents($widget.options.selectorProduct)
                	    .find(this.options.selectorProductPrice);

            if ($widget.inProductList) {
	                $input = $widget.productForm.find(
        	            '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                	);
            }

            if ($this.hasClass('disabled')) {
                return;
            }

            if ($this.hasClass('selected')) {
                $parent.removeAttr('data-option-selected').find('.selected').removeClass('selected');
                $input.val('');
                $label.text('');
                $this.attr('aria-checked', false);
            } else {
                $parent.attr('data-option-selected', $this.data('option-id')).find('.selected').removeClass('selected');
                $label.text($this.data('option-label'));
                $input.val($this.data('option-id'));
                $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
                $this.addClass('selected');
                $widget._toggleCheckedAttributes($this, $wrapper);
            }

            $widget._Rebuild();

            if ($priceBox.is(':data(mage-priceBox)')) {
                $widget._UpdatePrice();
            }

            $(document).trigger('updateMsrpPriceBlock',
                [
                    this._getSelectedOptionPriceIndex(),
                    $widget.options.jsonConfig.optionPrices,
                    $priceBox
                ]);

            if (parseInt(checkAdditionalData['update_product_preview_image'], 10) === 1) {
                $widget._loadMedia();
            }

            $input.trigger('change');
		}
        },


	        /**
         * Event for select
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnChange: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                attributeId = $parent.data('attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);

            if ($widget.productForm.length > 0) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }

            if ($this.val() > 0) {
                $parent.attr('data-option-selected', $this.val());
                $input.val($this.val());
            } else {
                $parent.removeAttr('data-option-selected');
                $input.val('');
            }

            $widget._Rebuild();
            $widget._UpdatePrice();
            $widget._loadMedia();
            $input.trigger('change');
        },


	/**
         * Render swatch options by part of config
         *
         * @param {Object} config
         * @param {String} controlId
         * @returns {String}
         * @private
         */
        _RenderSwatchOptions: function (config, controlId) {
            var optionConfig = this.options.jsonSwatchConfig[config.id],
                optionClass = this.options.classes.optionClass,
                sizeConfig = this.options.jsonSwatchImageSizeConfig,
		mediaUrl = this.options.mediaUrl,
		optionSwatches = this.options.optionSwatches,
		isEnabled = this.options.isEnabled,
                moreLimit = parseInt(this.options.numberToShow, 10),
                moreClass = this.options.classes.moreButton,
                moreText = this.options.moreButtonText,
                countAttributes = 0,
                html = '';

            if (!this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                return '';
            }

            $.each(config.options, function (index) {
                var id,
                    type,
                    value,
                    thumb,
                    label,
                    width,
                    height,
		    image,
                    attr,
		    attrcode,
                    swatchImageWidth,
                    swatchImageHeight;

                if (!optionConfig.hasOwnProperty(this.id)) {
                    return '';
                }

		
                // Add more button
                if (moreLimit === countAttributes++) {
                    html += '<a href="#" class="' + moreClass + '"><span>' + moreText + '</span></a>';
                }

                id = this.id;

                type = parseInt(optionConfig[id].type, 10);
                value = optionConfig[id].hasOwnProperty('value') ?
                    $('<i></i>').text(optionConfig[id].value).html() : '';
                thumb = optionConfig[id].hasOwnProperty('thumb') ? optionConfig[id].thumb : '';
                width = _.has(sizeConfig, 'swatchThumb') ? sizeConfig.swatchThumb.width : 110;
                height = _.has(sizeConfig, 'swatchThumb') ? sizeConfig.swatchThumb.height : 90;
                label = this.label ? $('<i></i>').text(this.label).html() : '';

                attr =
                    ' id="' + controlId + '-item-' + id + '"' +
                    ' index="' + index + '"' +
                    ' aria-checked="false"' +
                    ' aria-describedby="' + controlId + '"' +
                    ' tabindex="0"' +
                    ' data-option-type="' + type + '"' +
                    ' data-option-id="' + id + '"' +
                    ' data-option-label="' + label + '"' +
                    ' aria-label="' + label + '"' +
                    ' role="option"' +
                    ' data-thumb-width="' + width + '"' +
                    ' data-thumb-height="' + height + '"';



		    console.log(this);
                 $.each(optionSwatches, function(index, value){
  //                            console.log(index);
                        if(value.swatch_image)
                        $.each(value, function(index, val) {
//                              console.log("Attr name" +value.attribute_name);
//                              console.log("Attr Name lowercase" + value.attribute_name.toLowerCase());
//                              console.log("config " + JSON.stringify(config));
//                              console.log("attr value" + value.attribute_value);
//                              console.log("val" + JSON.stringify(val));
//                              console.log("label" + label);
//                              console.log('+++++++');
                           if(value.attribute_name.toLowerCase() == config.code && value.attribute_value.toLowerCase() == label.toLowerCase()) {
                                console.log("Image" + val.name);
                                   console.log('------');
                                image = mediaUrl + 'ocacia_swatches/' + val.name;
                           }
                        });
                 });

		if(image) {
			attr += image !== '' ? ' data-option-tooltip-thumb="' + image + '"' : '';
        	        attr += image !== '' ? ' data-option-tooltip-value="' + image + '"' : '';
		} else {
			attr += thumb !== '' ? ' data-option-tooltip-thumb="' + thumb + '"' : '';
                        attr += value !== '' ? ' data-option-tooltip-value="' + value + '"' : '';
		}

                swatchImageWidth = _.has(sizeConfig, 'swatchImage') ? sizeConfig.swatchImage.width : 30;
                swatchImageHeight = _.has(sizeConfig, 'swatchImage') ? sizeConfig.swatchImage.height : 20;

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' data-option-empty="true"';
                }

		//type = 2;
                if (type === 0) {
		    if(isEnabled && image) {
			// Force image
			html += '<div class="' + optionClass + ' image testig" ' + attr +
 ' style="background: url(' + image  + ') no-repeat center; background-size: initial;width:' +
                        swatchImageWidth + 'px; height:' + swatchImageHeight + 'px">' + '' +
                        '</div>';
		    } else {
			    console.log(image);
                    	// Text
                    	html += '<div class="' + optionClass + ' text test" ' + attr + '>' + (value ? value : label) +
                        '</div>';
		    }
                } else if (type === 1) {
		    if(isEnabled && image) {
		    //Force image
		    html += '<div class="' + optionClass + ' image testig" ' + attr +
 ' style="background: url(' + image  + ') no-repeat center; background-size: initial;width:' +
                        swatchImageWidth + 'px; height:' + swatchImageHeight + 'px">' + '' +
                        '</div>';
		    } else {
                    // Color
                    html += '<div class="' + optionClass + ' color" ' + attr +
                        ' style="background: ' + value +
                        ' no-repeat center; background-size: initial;">' + '' +
                        '</div>';
		    }
                } else if (type === 2) {
		    if(isEnabled && image) {
		      html += '<div class="' + optionClass + ' image testig" ' + attr +
 ' style="background: url(' + image  + ') no-repeat center; background-size: initial;width:' +
                        swatchImageWidth + 'px; height:' + swatchImageHeight + 'px">' + '' +
                        '</div>';
		    } else {
                    // Image
                    html += '<div class="' + optionClass + ' image testig" ' + attr +
 ' style="background: url(' + value + ') no-repeat center; background-size: initial;width:' +
                        swatchImageWidth + 'px; height:' + swatchImageHeight + 'px">' + '' +
                        '</div>';
		    }
                } else if (type === 3) {
		    if(isEnabled && image) {
                        html += '<div class="' + optionClass + ' image testig" ' + attr +
 ' style="background: url(' + image  + ') no-repeat center; background-size: initial;width:' +
                        swatchImageWidth + 'px; height:' + swatchImageHeight + 'px">' + '' +
                        '</div>';
                    } else {
                    // Clear
                    html += '<div class="' + optionClass + '" ' + attr + '></div>';
		    }
                } else {
		    if(isEnabled && image) {
			html += '<div class="' + optionClass + ' image testig" ' + attr +
 ' style="background: url(' + image  + ') no-repeat center; background-size: initial;width:' +
                        swatchImageWidth + 'px; height:' + swatchImageHeight + 'px">' + '' +
                        '</div>';
		    } else {
                    // Default
                    html += '<div class="' + optionClass + '" ' + attr + '>' + label + '</div>';
		    }
                }
            });

            return html;
        }
        });
        return $['mage']['SwatchRenderer'];
    };
});
