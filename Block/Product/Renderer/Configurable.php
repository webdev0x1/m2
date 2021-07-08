<?php
declare(strict_types = 1);
namespace Ocacia\Swatches\Block\Product\Renderer;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Store\Model\ScopeInterface;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\Swatch;
use Magento\Framework\App\ObjectManager;
use Magento\Swatches\Model\SwatchAttributesProvider;

/**
 * Swatch renderer block
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
    const XML_CONFIG_PATH_ENABLED = 'ocaciaswatches/general/enable';
    const XML_CONFIG_PATH_OPTION_SWATCHES = 'ocaciaswatches/ocacia_swatch_images/swatch_images';

    protected $_storeManager;
    protected $scopeConfig;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param EncoderInterface $jsonEncoder
     * @param Data $helper
     * @param CatalogProduct $catalogProduct
     * @param CurrentCustomer $currentCustomer
     * @param PriceCurrencyInterface $priceCurrency
     * @param ConfigurableAttributeData $configurableAttributeData
     * @param SwatchData $swatchHelper
     * @param Media $swatchMediaHelper
     * @param array $data
     * @param SwatchAttributesProvider|null $swatchAttributesProvider
     * @param UrlBuilder|null $imageUrlBuilder
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        Data $helper,
        CatalogProduct $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        SwatchData $swatchHelper,
	Media $swatchMediaHelper,
	SerializerInterface $serializer,
	\Magento\Store\Model\StoreManagerInterface $storeManager,
	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = [],
        SwatchAttributesProvider $swatchAttributesProvider = null,
        UrlBuilder $imageUrlBuilder = null
    ) {
	$this->scopeConfig = $scopeConfig;
	$this->_storeManager = $storeManager;
	$this->serializer = $serializer;
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
	    $configurableAttributeData,
	    $swatchHelper,
	    $swatchMediaHelper,
            $data
        );
	//$this->setTemplate('Ocacia_Swatches::product/view/renderer.phtml');
	//self::SWATCH_RENDERER_TEMPLATE = 'Ocacia_Swatches::product/view/renderer.phtml';
    }

    public function getStoreid()
    {
        return $this->_storeManager->getStore()->getId();
    }

/*    protected function getRendererTemplate() {
        return $this->isProductHasSwatchAttribute ?
                'Ocacia_Swatches::product/view/renderer.phtml' : self::CONFIGURABLE_RENDERER_TEMPLATE;
}*/
    /**
     * Return renderer template
     *
     * Template for product with swatches is different from product without swatches
     *
     * @return string
     */
    protected function getRendererTemplate()
    {
        return $this->isProductHasSwatchAttribute() ?
            'Ocacia_Swatches::product/view/renderer.phtml' : 'Magento_ConfigurableProduct::product/view/type/options/configurable.phtml';
    }

    public function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
  
    public function isEnabled()
    {
        return $this->getConfig(self::XML_CONFIG_PATH_ENABLED, $this->_storeManager->getStore()->getId()); 
    }

    public function optionSwatches() {
	$swatchImages = $this->getConfig(self::XML_CONFIG_PATH_OPTION_SWATCHES, $this->_storeManager->getStore()->getId());
        if($swatchImages == '' || $swatchImages == null)
		return;
	 //$swatchImages = $this->serializer->unserialize($swatchImages);

        return $swatchImages; //$this->getConfig(self::XML_CONFIG_PATH_OPTION_SWATCHES, $this->_storeManager->getStore()->getId());
    }

    public function getMediaUrl() {
	return $mediaUrl = $this ->_storeManager-> getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );	
    }
}
