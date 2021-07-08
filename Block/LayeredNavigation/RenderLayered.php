<?php
namespace Ocacia\Swatches\Block\LayeredNavigation;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Swatches\Helper\Data;
use Magento\Swatches\Helper\Media;
use Magento\Theme\Block\Html\Pager;

/**
 * Class RenderLayered Render Swatches at Layered Navigation
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class RenderLayered extends \Magento\Swatches\Block\LayeredNavigation\RenderLayered
{
    const XML_CONFIG_PATH_ENABLED = 'ocaciaswatches/general/enable';
    const XML_CONFIG_ONLY_SWATCHES = 'ocacia/general/only_swatches';
    const XML_CONFIG_PATH_OPTION_SWATCHES = 'ocaciaswatches/ocacia_swatch_images/swatch_images';
    const XML_CONFIG_PATH_SWATCH_ATTRIBUTES = 'ocaciaswatches/general/swatch_attributes';


    protected $scopeConfig;
    protected $_storeManager;
    /**
     * @param Context $context
     * @param Attribute $eavAttribute
     * @param AttributeFactory $layerAttribute
     * @param Data $swatchHelper
     * @param Media $mediaHelper
     * @param array $data
     * @param Pager|null $htmlPagerBlock
     */
    public function __construct(
        Context $context,
	Attribute $eavAttribute,
	SerializerInterface $serializer,
        AttributeFactory $layerAttribute,
        Data $swatchHelper,
	Media $mediaHelper,
	\Magento\Store\Model\StoreManagerInterface $storeManager,
	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = [],
        ?Pager $htmlPagerBlock = null
    ) {
	    $this->serializer = $serializer;
	    $this->_storeManager = $storeManager;
	    $this->scopeConfig = $scopeConfig;
	    parent::__construct($context, $eavAttribute, $layerAttribute, $swatchHelper, $mediaHelper, $data, $htmlPagerBlock);
	    $this->setTemplate('Ocacia_Swatches::product/layered/renderer.phtml');
    }

    public function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    public function getNonSwatchAttributes()
    {
        return explode(",",$this->getConfig(self::XML_CONFIG_PATH_SWATCH_ATTRIBUTES, $this->_storeManager->getStore()->getId()));
    }

    public function isEnabled()
    {
        return $this->getConfig(self::XML_CONFIG_PATH_ENABLED, $this->_storeManager->getStore()->getId());
    }

    public function isOnlySwatches()
    {
        return $this->getConfig(self::XML_CONFIG_ONLY_SWATCHES, $this->_storeManager->getStore()->getId());
    }

    public function getMediaUrl() {
        return $mediaUrl = $this ->_storeManager-> getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
    }

    public function optionSwatches() {
	$swatchImages = $this->getConfig(self::XML_CONFIG_PATH_OPTION_SWATCHES, $this->_storeManager->getStore()->getId());
	
        if($swatchImages == '' || $swatchImages == null)
		return;
	$swatchImages = $this->serializer->unserialize($swatchImages);

        return $swatchImages;
    }
}
