<?php
namespace Ocacia\Swatches\Block\Product\Renderer\Listing;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\SwatchAttributesProvider;

/**
 * Swatch renderer block in Category page
 *
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configurable extends \Magento\Swatches\Block\Product\Renderer\Listing\Configurable
{
    protected $_storeManager;
    protected $scopeConfig;
	
    const XML_CONFIG_ONLY_SWATCHES = 'ocacia/general/only_swatches';
    const XML_CONFIG_PATH_ENABLED = 'ocaciaswatches/general/enable';
    const XML_CONFIG_PATH_OPTION_SWATCHES = 'ocaciaswatches/ocacia_swatch_images/swatch_images';

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
     * @param \Magento\Framework\Locale\Format|null $localeFormat
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices|null $variationPrices
     * @param Resolver $layerResolver
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
	\Magento\Store\Model\StoreManagerInterface $storeManager,
	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = [],
        SwatchAttributesProvider $swatchAttributesProvider = null,
        \Magento\Framework\Locale\Format $localeFormat = null,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices $variationPrices = null,
        Resolver $layerResolver = null
    ) {
        $this->_storeManager = $storeManager;
	$this->scopeConfig = $scopeConfig;

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
            $data,
            $swatchAttributesProvider
       );
	$this->setTemplate('Ocacia_Swatches::product/listing/renderer.phtml');
    }

    public function getMediaUrl() {
        return $mediaUrl = $this ->_storeManager-> getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
    }

    public function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function optionSwatches() {
        $swatchImages = $this->getConfig(self::XML_CONFIG_PATH_OPTION_SWATCHES, $this->_storeManager->getStore()->getId());

        if($swatchImages == '' || $swatchImages == null)
                return;

        return $swatchImages;
    }

    public function isOnlySwatches()
    {
        return $this->getConfig(self::XML_CONFIG_ONLY_SWATCHES, $this->_storeManager->getStore()->getId());
    }

    public function isEnabled()
    {
        return $this->getConfig(self::XML_CONFIG_PATH_ENABLED, $this->_storeManager->getStore()->getId());
    }
}
