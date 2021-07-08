<?php
namespace Ocacia\Swatches\Model\Plugin;

/**
 * Class FilterRenderer
 */
class FilterRenderer
{
    const XML_CONFIG_PATH_SWATCH_ATTRIBUTES = 'ocaciaswatches/general/swatch_attributes';
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * Path to RenderLayered Block
     *
     * @var string
     */
    protected $block = \Magento\Swatches\Block\LayeredNavigation\RenderLayered::class;

    /**
     * @var \Magento\Swatches\Helper\Data
     */
    protected $swatchHelper;

    protected $scopeConfig;
    protected $_storeManager;

    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     */
    public function __construct(
	\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Swatches\Helper\Data $swatchHelper
    ) {
        $this->layout = $layout;
	$this->swatchHelper = $swatchHelper;
	$this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \Magento\LayeredNavigation\Block\Navigation\FilterRenderer $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundRender(
        \Magento\LayeredNavigation\Block\Navigation\FilterRenderer $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter
    ) {
	    $nonSwatchAttributes = $this->getNonSwatchAttributes();
	    $nonSwatchAttributes = explode(",",$nonSwatchAttributes);
	    //print_r($filter->getAttributeModel()->getAttributeCode());
	    $sw = [];
	    foreach($nonSwatchAttributes as $nsw) {
		$sw[] = trim(strtolower($nsw));
	    }
	    if(!empty($sw) && $filter->getAttributeModel()->getAttributeCode() != 'price' && in_array($filter->getAttributeModel()->getAttributeCode(), $sw)) {
		   if ($filter->hasAttributeModel()) {
                        return $this->layout
                            ->createBlock($this->block)
                            ->setSwatchFilter($filter)
                            ->toHtml();
		   }
	    } else {
		    if ($filter->hasAttributeModel()) {
                    if ($this->swatchHelper->isSwatchAttribute($filter->getAttributeModel())) {
                        return $this->layout
                            ->createBlock($this->block)
                            ->setSwatchFilter($filter)
                            ->toHtml();
                        }
                }
	    }
        return $proceed($filter);
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
        return $this->getConfig(self::XML_CONFIG_PATH_SWATCH_ATTRIBUTES, $this->_storeManager->getStore()->getId());
    }

}
