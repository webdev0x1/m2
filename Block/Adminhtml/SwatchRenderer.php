<?php
namespace Ocacia\Swatches\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class SwatchRenderer extends \Magento\Framework\View\Element\AbstractBlock
{
   private $_storeManager;
   /**
    * @param \Magento\Backend\Block\Context $context
    * @param array $data
    */
   public function __construct(\Magento\Backend\Block\Context $context, 
	   	StoreManagerInterface $storemanager, 
		\Ocacia\Swatches\Model\ImageUploader $imageUploader,
		array $data = [])
   {
       $this->_storeManager = $storemanager;
       parent::__construct($context, $data);
       $this->imageUploader = $imageUploader;
       $this->_authorization = $context->getAuthorization();
   }
   /**
    * Renders grid column
    *
    * @param Obj $row
    * @return  string
    */
   public function render(DataObject $row)
   {
       $mediaDirectory = $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
    );
       $imageUrl = $mediaDirectory.'ocacia_swatches/'.$this->_getValue($row);
       return '<img src="'.$imageUrl.'" width="50"/>';
   }

   /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
   
    public function setInputId($value)
    {
        return $this->setId($value);
    }


    /**
     * Set element's HTML ID
     *
     * @param string $elementId ID
     * @return $this
     */
    public function setId($elementId)
    {
        $this->setData('id', $elementId);
        return $this;
    }

    /**
     * Set element's CSS class
     *
     * @param string $class Class
     * @return $this
     */
    public function setClass($class)
    {
        $this->setData('class', $class);
        return $this;
    }

    /**
     * Set element's HTML title
     *
     * @param string $title Title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->setData('title', $title);
        return $this;
    }

    /**
     * HTML ID of the element
     *
     * @return string
     */
    public function getId()
    {
        return $this->getData('id');
    }

    /**
     * CSS class of the element
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getData('class');
    }

    /**
     * Returns HTML title of the element
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('title');
    }
    /**
     * Render HTML
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _toHtml()
    {
        if (!$this->_beforeToHtml()) {
            return '';
	}

        $html = '<input type="file" name="' .
            $this->getName() .
            '" id="' .
            $this->getId() .
            '" class="' .
            $this->getClass() .
            '" title="' .
            $this->escapeHtml($this->getTitle()) .
            '" ' .
            $this->getExtraParams() .
            '/>';

        return $html;
    }

    /**
     * Return option HTML node
     *
     * @param array $option
     * @param boolean $selected
     * @return string
     */
    protected function _optionToHtml($option, $selected = false)
    {
/*        $selectedHtml = $selected ? ' selected="selected"' : '';
        if ($this->getIsRenderToJsTemplate() === true) {
            $selectedHtml .= ' <%= option_extra_attrs.option_' . self::calcOptionHash($option['value']) . ' %>';
        }

        $params = '';
        if (!empty($option['params']) && is_array($option['params'])) {
            foreach ($option['params'] as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $keyMulti => $valueMulti) {
                        $params .= sprintf(' %s="%s" ', $keyMulti, $this->escapeHtml($valueMulti));
                    }
                } else {
                    $params .= sprintf(' %s="%s" ', $key, $this->escapeHtml($value));
                }
            }
        }

        return sprintf(
            '<option value="%s"%s %s>%s</option>',
            $this->escapeHtml($option['value']),
            $selectedHtml,
            $params,
            $this->escapeHtml($option['label'])
    );*/
    }

    /**
     * Alias for toHtml()
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->toHtml();
    }

    /**
     * Calculate CRC32 hash for option value
     *
     * @param string $optionValue Value of the option
     * @return string
     */
    public function calcOptionHash($optionValue)
    {
        return sprintf('%u', crc32($this->getName() . $this->getId() . $optionValue));
    }
}
